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

    public function syncOptions() {
        // OC2.x => token; OC3.x => user_token
        $token_key = isset($this->request->get['user_token']) ? 'user_token' : 'token';
        if (!isset($this->request->get[$token_key]) || !isset($this->request->get['product_id'])) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(['error' => 'Missing token or product_id']));
            return;
        }

        $product_id = (int)$this->request->get['product_id'];

        $this->load->model('catalog/product');

        // 1) Dohvati postojeći proizvod (zbog modela i kasnijeg mergea u editProduct)
        $product_info = $this->model_catalog_product->getProduct($product_id);
        if (!$product_info) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(['error' => 'Product not found']));
            return;
        }

        $model = $product_info['model']; // npr. U7023

        try {
            // 2) Pozovi ERP i izgradi Product/Options preko tvojih klasa
            $service = new \Agmedia\Service\Service();
            $product = new \Agmedia\Models\Product\Product();
            $option  = new \Agmedia\Models\Option\Option();

            // options prema modelu (ovdje je ključno da endpoint prima MODEL kao "odjel")
            $options = $option->make(
                $service->getOptions($model) // => sif_roba/getodjel/{MODEL}&webshop=0
            );

            // buildaj podatke za proizvod (prijevodi + slike po IDODJEL iz options first)
            $new_product = $product->make(
                $options,
                $service->getTraslations($options->first()['IDROBA']),
                $this->storeImages(
                    $service->getImages($options->first()['IDODJEL'])
                )
            );

            // 3) Sastavi FULL $data za editProduct (OC zahtijeva puni payload)
            //    Kreni od postojećih vrijednosti i zamijeni/ubaci ono što dolazi iz $new_product
            $data = $this->model_catalog_product->getProduct($product_id);

            // Obavezni prateći dijelovi (OC editProduct očekuje sve ove ključeve):
            $this->load->model('catalog/option'); // nije nužno, ali ok
            $data['product_description'] = $this->model_catalog_product->getProductDescriptions($product_id);
            $data['product_store']       = $this->model_catalog_product->getProductStores($product_id);
            $data['product_layout']      = $this->model_catalog_product->getProductLayouts($product_id);
            $data['product_image']       = $this->model_catalog_product->getProductImages($product_id);
            $data['product_category']    = $this->model_catalog_product->getProductCategories($product_id);
            $data['product_filter']      = $this->model_catalog_product->getProductFilters($product_id);
            $data['product_related']     = $this->model_catalog_product->getProductRelated($product_id);
            $data['product_reward']      = $this->model_catalog_product->getProductRewards($product_id);
            $data['product_seo_url']     = $this->model_catalog_product->getSeoUrls($product_id);
            $data['product_discount']    = $this->model_catalog_product->getProductDiscounts($product_id);
            $data['product_special']     = $this->model_catalog_product->getProductSpecials($product_id);
            $data['product_attribute']   = $this->model_catalog_product->getProductAttributes($product_id);
            $data['product_recurring']   = $this->model_catalog_product->getRecurrings($product_id);

            // 4) Prebaci nove options i slike iz $new_product u $data
            $ag = $new_product->toArray();

            if (!empty($ag['product_option'])) {
                $data['product_option'] = $ag['product_option']; // zamijeni komplet
            }

            // Ako želiš i glavnu + dodatne slike iz ERP-a
            if (!empty($ag['image'])) {
                $data['image'] = $ag['image'];
            }
            if (!empty($ag['product_image'])) {
                $data['product_image'] = $ag['product_image'];
            }

            // (opcionalno) update price/quantity/weight/title iz ERP-a
            foreach (['price','quantity','weight','sku','upc','ean','jan','isbn','mpn','location','status'] as $k) {
                if (isset($ag[$k])) $data[$k] = $ag[$k];
            }
            if (isset($ag['product_description'])) {
                // Merge prijevoda po jezicima (ili zamijeni kompletno)
                $data['product_description'] = $ag['product_description'];
            }

            // 5) Spremi izmjene
            $this->model_catalog_product->editProduct($product_id, $data);

            \Agmedia\Helpers\Log::write(['product_id'=>$product_id,'model'=>$model], 'erp_sync');

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'message' => 'Options & images synced from ERP.',
                'updated' => [
                    'product_option' => isset($data['product_option']) ? count($data['product_option']) : 0,
                    'product_image'  => isset($data['product_image']) ? count($data['product_image']) : 0
                ]
            ]));
        } catch (\Throwable $e) {
            \Agmedia\Helpers\Log::write($e->getMessage(), 'erp_sync_error');

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]));
        }
    }

}