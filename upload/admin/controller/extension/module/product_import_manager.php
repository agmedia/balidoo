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
    /* ====== postojeće metode: getProducts, importProducts, storeImages ====== */

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

    /* ====== SINGLE SYNC: skip praznog IDVELICINA + brisanje postojeće opcije + aktivacija statusa ====== */

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
                // NEMA valjanih veličina → obriši postojeću “Veličina” opciju s artikla
                $this->load->model('extension/module/product_import_manager');
                if (method_exists($this->model_extension_module_product_import_manager, 'removeSizeOptionForProduct')) {
                    $this->model_extension_module_product_import_manager->removeSizeOptionForProduct($product_id);
                } else {
                    // fallback brisanja ako helper nije dostupan:
                    $this->removeSizeOptionFallback($product_id);
                }

                // Aktiviraj artikl ako ERP ukupna količina > 0
                $totalQtyFromErp = 0;
                foreach ($api as $r) {
                    $r = (array)$r;
                    $totalQtyFromErp += (int)($r['ZALIHAK'] ?? 0);
                }
                if ($totalQtyFromErp > 0) {
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET status = 1 WHERE product_id = " . (int)$product_id);
                }

                return $this->json([
                    'success'     => true,
                    'message'     => 'No valid sizes; removed Size option for model ' . $model,
                    'updated'     => false,
                    'removed'     => true,
                    'activated'   => $totalQtyFromErp > 0 ? 1 : 0,
                    'total_qty'   => $totalQtyFromErp
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
                // NEMA valjanih veličina → obriši postojeću “Veličina” opciju s artikla
                $this->load->model('extension/module/product_import_manager');
                if (method_exists($this->model_extension_module_product_import_manager, 'removeSizeOptionForProduct')) {
                    $this->model_extension_module_product_import_manager->removeSizeOptionForProduct($product_id);
                } else {
                    $this->removeSizeOptionFallback($product_id);
                }

                // Aktiviraj artikl ako ERP ukupna količina > 0
                $totalQtyFromErp = 0;
                foreach ($api as $r) {
                    $r = (array)$r;
                    $totalQtyFromErp += (int)($r['ZALIHAK'] ?? 0);
                }
                if ($totalQtyFromErp > 0) {
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET status = 1 WHERE product_id = " . (int)$product_id);
                }

                return $this->json([
                    'ok'         => true,
                    'product_id' => $product_id,
                    'model'      => $model,
                    'updated'    => false,
                    'removed'    => true,
                    'reason'     => 'no-valid-sizes',
                    'activated'  => $totalQtyFromErp > 0 ? 1 : 0,
                    'total_qty'  => $totalQtyFromErp
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
            return ltrim($row['IDVELICINA'], '0'); // makni sve leading nule
        }

        $label = '';
        if (!empty($row['NAZIV'])) {
            if (preg_match('/VEL\.\s*([0-9A-Za-z]+)/u', $row['NAZIV'], $m)) {
                $label = trim($m[1]);
                $label = ltrim($label, '0'); // isto očisti
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

    /**
     * Fallback brisanja opcije “Veličina” s artikla ako model helper nije dostupan.
     * (koristi isti SQL kao u modelu: deleteOneProductOption)
     */
    private function removeSizeOptionFallback(int $product_id): void
    {
        // pokuša uzeti option_id iz configa ili po imenu
        $option_id = (int)(agconf('erp.size_option_id') ?? 0);

        if (!$option_id) {
            // potraži po imenu u bilo kojem jeziku
            $names = ['Veličina','Velicina','Size'];
            $names_esc = array_map(function($n){ return "'" . $this->db->escape($n) . "'"; }, $names);
            $q = $this->db->query("SELECT o.option_id
                                     FROM " . DB_PREFIX . "option o
                                     JOIN " . DB_PREFIX . "option_description od
                                       ON od.option_id = o.option_id
                                    WHERE od.name IN (" . implode(',', $names_esc) . ")
                                      AND o.type = 'select'
                                    LIMIT 1");
            if ($q->num_rows) {
                $option_id = (int)$q->row['option_id'];
            }
        }

        if (!$option_id) return;

        // pobriši vrijednosti pa product_option
        $this->db->query("DELETE pov FROM " . DB_PREFIX . "product_option_value pov
                          JOIN " . DB_PREFIX . "product_option po 
                            ON po.product_option_id = pov.product_option_id
                         WHERE po.product_id = " . (int)$product_id . "
                           AND po.option_id  = " . (int)$option_id);

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option 
                          WHERE product_id = " . (int)$product_id . "
                            AND option_id  = " . (int)$option_id);
    }
}
