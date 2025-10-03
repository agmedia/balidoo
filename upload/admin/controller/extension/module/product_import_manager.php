<?php

use Agmedia\Models\Option\Option;
use Agmedia\Models\Product\Product;
use Agmedia\Models\Product\ProductOption;
use Agmedia\Service\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Agmedia\Helpers\Log;

class ControllerExtensionModuleProductImportManager extends Controller
{
    /* ====== tvoje postojeće metode: getProducts, importProducts, storeImages ====== */

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

    /* ====== SINGLE SYNC: s preskokom praznog IDVELICINA + aktivacija statusa ====== */

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
            // 1) ERP podaci
            $service = new \Agmedia\Service\Service();
            $api = $service->getOptions($model); // Collection (getodjel/{MODEL})

            if ($api->isEmpty()) {
                return $this->json(['error' => 'ERP returned empty dataset for model ' . $model]);
            }

            // 2) Build items (SKIP kad je IDVELICINA prazno)
            $minPrice = $api->min('CIJENA_MPC') ?? 0;
            $items = [];
            foreach ($api as $row) {
                $row = (array)$row;
                if (empty($row['IDVELICINA'])) {
                    continue; // SKIP prazne veličine
                }

                $name = self::extractSizeLabel($row);
                $items[] = [
                    'name'         => $name,
                    'quantity'     => (int)($row['ZALIHAK'] ?? 0),
                    'price'        => max(0, (float)($row['CIJENA_MPC'] ?? 0) - (float)$minPrice),
                    'price_prefix' => '+',
                    'weight'       => 0,
                    'weight_prefix'=> '+',
                    'subtract'     => 1,
                    'sort_order'   => 0,
                    'sku'          => (string)($row['IDROBA'] ?? ''),
                ];
            }

            if (empty($items)) {
                return $this->json([
                    'error' => 'No valid sizes (all IDVELICINA empty) for model ' . $model
                ]);
            }

            // 3) Upis opcija (samo size) + sku
            $this->load->model('extension/module/product_import_manager');
            $counts = $this->model_extension_module_product_import_manager
                ->replaceOptionsWithSize($product_id, $items);

            // 4) Aktiviraj proizvod ako ukupna količina > 0
            $totalQty = (int)($counts['total_qty'] ?? array_sum(array_column($items, 'quantity')));
            if ($totalQty > 0) {
                $this->db->query("UPDATE " . DB_PREFIX . "product SET status = 1 WHERE product_id = " . (int)$product_id);
            }

            return $this->json([
                'success'   => true,
                'message'   => 'Options replaced from ERP for model ' . $model,
                'counts'    => $counts,
                'activated' => $totalQty > 0 ? 1 : 0,
                'total_qty' => $totalQty
            ]);
        } catch (\Throwable $e) {
            \Agmedia\Helpers\Log::write($e->getMessage(), 'erp_sync_options_error');
            return $this->json(['error' => $e->getMessage()]);
        }
    }

    /* ====== ALL UPDATE: queue + step (artikl-po-artikl) ====== */

    public function bulkSyncQueue()
    {
        $token_key = isset($this->request->get['user_token']) ? 'user_token' : 'token';
        if (!isset($this->request->get[$token_key])) {
            return $this->json(['error' => 'Missing token']);
        }

        // Po potrebi prilagodi WHERE (npr. samo određene kategorije / brandove, itd.)
        $rows = $this->db->query("
            SELECT product_id 
            FROM " . DB_PREFIX . "product 
            WHERE model <> '' 
            ORDER BY product_id ASC
        ")->rows;

        $ids = array_map(fn($r) => (int)$r['product_id'], $rows);

        return $this->json([
            'ok'    => true,
            'count' => count($ids),
            'ids'   => $ids
        ]);
    }

    public function bulkSyncStep()
    {
        $token_key = isset($this->request->get['user_token']) ? 'user_token' : 'token';
        if (!isset($this->request->get[$token_key]) || !isset($this->request->get['product_id'])) {
            return $this->json(['error' => 'Missing token or product_id']);
        }

        $product_id = (int)$this->request->get['product_id'];

        $this->load->model('catalog/product');
        $product = $this->model_catalog_product->getProduct($product_id);
        if (!$product) {
            return $this->json(['error' => 'Product not found', 'product_id' => $product_id]);
        }

        $model = $product['model'];

        try {
            // 1) ERP podaci
            $service = new \Agmedia\Service\Service();
            $api = $service->getOptions($model);
            if ($api->isEmpty()) {
                return $this->json([
                    'ok'         => true,
                    'product_id' => $product_id,
                    'model'      => $model,
                    'updated'    => false,
                    'reason'     => 'empty'
                ]);
            }

            // 2) Build items (SKIP kad je IDVELICINA prazno)
            $minPrice = $api->min('CIJENA_MPC') ?? 0;
            $items = [];
            foreach ($api as $row) {
                $row = (array)$row;
                if (empty($row['IDVELICINA'])) {
                    continue;
                }

                $name = self::extractSizeLabel($row);
                $items[] = [
                    'name'         => $name,
                    'quantity'     => (int)($row['ZALIHAK'] ?? 0),
                    'price'        => max(0, (float)($row['CIJENA_MPC'] ?? 0) - (float)$minPrice),
                    'price_prefix' => '+',
                    'weight'       => 0,
                    'weight_prefix'=> '+',
                    'subtract'     => 1,
                    'sort_order'   => 0,
                    'sku'          => (string)($row['IDROBA'] ?? ''),
                ];
            }

            if (empty($items)) {
                return $this->json([
                    'ok'         => true,
                    'product_id' => $product_id,
                    'model'      => $model,
                    'updated'    => false,
                    'reason'     => 'no-valid-sizes'
                ]);
            }

            // 3) Upis opcija + sku
            $this->load->model('extension/module/product_import_manager');
            $counts = $this->model_extension_module_product_import_manager
                ->replaceOptionsWithSize($product_id, $items);

            // 4) Aktivacija prema qty
            $totalQty = (int)($counts['total_qty'] ?? array_sum(array_column($items, 'quantity')));
            if ($totalQty > 0) {
                $this->db->query("UPDATE " . DB_PREFIX . "product SET status = 1 WHERE product_id = " . (int)$product_id);
            }

            return $this->json([
                'ok'         => true,
                'product_id' => $product_id,
                'model'      => $model,
                'updated'    => true,
                'counts'     => $counts,
                'activated'  => $totalQty > 0 ? 1 : 0,
                'total_qty'  => $totalQty
            ]);

        } catch (\Throwable $e) {
            \Agmedia\Helpers\Log::write(['pid'=>$product_id,'model'=>$model,'err'=>$e->getMessage()], 'erp_bulk_sync_error');
            return $this->json([
                'error'      => $e->getMessage(),
                'product_id' => $product_id,
                'model'      => $model
            ]);
        }
    }

    /* ====== helperi ====== */

    private static function extractSizeLabel(array $row): string
    {
        // Preferiraj IDVELICINA ako postoji
        if (!empty($row['IDVELICINA'])) {
            return trim($row['IDVELICINA']);
        }

        $label = '';
        if (!empty($row['NAZIV'])) {
            if (preg_match('/VEL\.\s*([0-9A-Za-z]+)/u', $row['NAZIV'], $m)) {
                $label = trim($m[1]);
            }
        }
        if (!$label && !empty($row['IDROBA'])) {
            $label = trim($row['IDROBA']);
        }
        return $label ?: 'N/A';
    }

    private function json($payload)
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($payload));
        return;
    }
}
