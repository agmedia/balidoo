<?php

use Agmedia\Models\Option\Option;
use Agmedia\Models\Product\Product;
use Agmedia\Models\Product\ProductOption;
use Agmedia\Service\Service;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Agmedia\Helpers\Log;

class ControllerExtensionModuleProductImportManager extends Controller
{
    
    /**
     *
     */
    public function getProducts()
    {
        $option_ids = [];
        $json       = new Collection();
        $products   = (new Service())->getProducts();
        
        foreach (Product::all() as $existing_product) {
            array_push($option_ids, $existing_product['model']);
        }
        
        foreach ($products as $key => $product) {
            if ( ! in_array($key, array_unique(Arr::flatten($option_ids)))) {
                $json->put($key, $product);
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'products' => $json->toJson(),
            'count'    => $json->count()
        ]));
    }
    
    
    /**
     *
     */
    public function importProducts()
    {
        $service = new Service();
        $product = new Product();
        $option  = new Option();
        
        $options = $option->make(
            $service->getOptions($this->request->get['data'])
        );
        
        $new_product = $product->make(
            $options,
            $service->getTraslations($options->first()['IDROBA']),
            $this->storeImages(
                $service->getImages($options->first()['IDODJEL'])
            )
        );
        
        $this->load->model('catalog/product');
        $this->model_catalog_product->addProduct($new_product->toArray());

        Log::write($new_product->toArray()['model'], 'testing4');
        Log::write($new_product->toArray(), 'testing4');
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput($new_product->toJson());
    }
    
    
    /**
     * @param $images
     *
     * @return Collection
     */
    private function storeImages($images)
    {
        $response = $images->map(function ($item) {
            $item['NAZIV'] = str_replace(' ', '', $item['NAZIV']);
            $item['URL']   = str_replace(' ', '%20', $item['URL']);
            $dir           = DIR_IMAGE . 'catalog/products/';
            $path          = $dir . $item['IDODJEL'] . '/' . $item['NAZIV'];
            
            if ( ! is_dir($dir)) {
                mkdir($dir);
            }
            if ( ! is_dir($dir . $item['IDODJEL'])) {
                mkdir($dir . $item['IDODJEL']);
            }
            
            if (file_put_contents($path, file_get_contents(/*agconf('erp.image_url') . */$item['URL']))) {
                $item['path'] = 'catalog/products/' . $item['IDODJEL'] . '/' . $item['NAZIV'];
                
                return $item;
            }
        });
        
        return $response;
    }
}