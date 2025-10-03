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



    public function syncOptionsOnly()
    {
        $token_key = isset($this->request->get['user_token']) ? 'user_token' : 'token';
        if (!isset($this->request->get[$token_key]) || !isset($this->request->get['product_id'])) {
            return $this->json(['error' => 'Missing token or product_id']);
        }

        $product_id = (int)$this->request->get['product_id'];

        $this->load->model('catalog/product');
        $product = $this->model_catalog_product->getProduct($product_id);
        if (!$product) {
            return $this->json(['error' => 'Product not found']);
        }

        $model = $product['model']; // npr. G3017A

        try {
            // 1) Povuci ERP podatke za taj model (kao "odjel")
            $service = new \Agmedia\Service\Service();
            $api = $service->getOptions($model); // Collection iz tvog Service-a (getodjel/{MODEL})

            if ($api->isEmpty()) {
                return $this->json(['error' => 'ERP returned empty dataset for model ' . $model]);
            }

            // 2) Izgradi OC option/value strukturu iz API polja
            //    - label opcije: "Veličina" (select)
            //    - option_value name iz NAZIV (parsiranje iza "VEL.") ili IDVELICINA

            // Minimalna baza cijena za price diff
            $minPrice = $api->min('CIJENA_MPC') ?? 0;

            $items = [];
            foreach ($api as $row) {
                $name = self::extractSizeLabel($row); // vidi helper niže
                $items[] = [
                    'name'            => $name,
                    'quantity'        => (int)($row['ZALIHAK'] ?? 0),
                    'price'           => max(0, (float)($row['CIJENA_MPC'] ?? 0) - (float)$minPrice),
                    'price_prefix'    => '+',
                    'weight'          => 0,
                    'weight_prefix'   => '+',
                    'subtract'        => 1,
                    'sort_order'      => 0,
                    'sku'             => (string)($row['IDROBA'] ?? ''),   // ⟵ OVO JE NOVO
                ];
            }

            // 3) Upis: obriši stare opcije → napravi/uzmi option_id za "Veličina" → osiguraj option_value → poveži s proizvodom
            $this->load->model('extension/module/product_import_manager');

            $counts = $this->model_extension_module_product_import_manager
                ->replaceOptionsWithSize($product_id, $items);

            return $this->json([
                'success' => true,
                'message' => 'Options replaced from ERP for model ' . $model,
                'counts'  => $counts
            ]);
        } catch (\Throwable $e) {
            \Agmedia\Helpers\Log::write($e->getMessage(), 'erp_sync_options_error');
            return $this->json(['error' => $e->getMessage()]);
        }
    }

    private function json($payload)
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($payload));
        return;
    }

    /**
     * Pomoćna: izvuci label veličine iz NAZIV-a ili IDVELICINA
     */
    private static function extractSizeLabel(array $row): string
    {
        $label = '';
        if (!empty($row['NAZIV'])) {
            // U NAZIV dolazi npr. "G3017 DJEČJE HLAČE, VEL. 3"
            if (preg_match('/VEL\.\s*([0-9A-Za-z]+)/u', $row['NAZIV'], $m)) {
                $label = trim($m[1]);
            }
        }
        if (!$label && !empty($row['IDVELICINA'])) {
            $label = trim($row['IDVELICINA']);
        }
        if (!$label && !empty($row['IDROBA'])) {
            $label = trim($row['IDROBA']);
        }
        return $label ?: 'N/A';
    }


}