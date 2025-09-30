<?php

use Agmedia\Models\Option\Option;
use Agmedia\Models\Product\Product;
use Agmedia\Models\Product\ProductOption;
use Agmedia\Service\Service;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Agmedia\Helpers\Log;

class ControllerExtensionFeedKipos extends Controller
{
    
    /**
     *
     */
    public function updatePrices()
    {
        if (isset($this->request->get['uuid']) && $this->request->get['uuid'] == agconf('erp.uuid')) {
            $products = (new Service())->getProducts();
            
            Log::write($products->count(), 'testing');
            
            $temp_product = '';
            $temp_option = '';
            $temp_options    = [];
    
            foreach ($products as $key => $product) {
                if ($product->first()['IDVELICINA'] == '') {
                    $has_default_qty = $this->checkDefaults($product->first()['IDROBA']);
                    $temp_product .= '("' . $product->first()['IDROBA'] . '", ' . ($has_default_qty ? $has_default_qty : $product->first()['ZALIHAK']) . ', ' . $product->first()[agconf('erp.price_tag')] . '),';
                } else {
                    $has_default_qty = $this->checkDefaults($product->first()['IDODJEL']);
                    $temp_product .= '("' . $product->first()['IDODJEL'] . '", ' . ($has_default_qty ? $has_default_qty : $product->sum('ZALIHAK')) . ', ' . $product->min(agconf('erp.price_tag')) . '),';
                    $temp_options[] = ProductOption::updateStockAndPrices($product, $has_default_qty);
                }
            }
    
            foreach ($temp_options as $option) {
                $temp_option .= $option;
            }
    
            Log::write($temp_product, 'testing');
            Log::write($temp_option, 'testing');
            
            $queries = $this->setQueries($temp_product, $temp_option);
    
            Log::write($queries, 'testing');
            
            for ($i = 0; $i < count($queries); $i++) {
                $this->db->query($queries[$i]);
    
                Log::write($i, 'testing');
            }
    
            Log::write('END', 'testing');
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(['status' => 200]));
        }
        
        return false;
    }
    
    
    /**
     * @param $id
     *
     * @return false|int|string
     */
    private function checkDefaults($id)
    {
        if (is_array(agconf('erp.update.quantity_overwrite'))) {
            foreach (agconf('erp.update.quantity_overwrite') as $key => $list) {
                if (in_array($id, $list)) {
                    return $key;
                }
            }
        }
    
        if (is_string(agconf('erp.update.quantity_overwrite')) && agconf('erp.update.quantity_overwrite') == 'all') {
            return 100;
        }
        
        return false;
    }
    
    
    /**
     * @param $product
     * @param $option
     *
     * @return string[]
     */
    private function setQueries($product, $option)
    {
        return [
            0 => "INSERT INTO " . DB_PREFIX . "product_temp (sifra, kolicina, cijena) VALUES " . substr($product, 0, -1) . ";",
            1 => "INSERT INTO " . DB_PREFIX . "product_temp_option (sifra, kolicina, prefix, cijena) VALUES " . substr($option, 0, -1) . ";",
            2 => "UPDATE " . DB_PREFIX . "product p INNER JOIN " . DB_PREFIX . "product_temp pt ON p.model = pt.sifra SET p.quantity = pt.kolicina, p.price = pt.cijena",
            3 => "UPDATE " . DB_PREFIX . "product_option_value p INNER JOIN " . DB_PREFIX . "product_temp_option pt ON p.sku = pt.sifra SET p.quantity = pt.kolicina, p.price_prefix = pt.prefix, p.price = pt.cijena",
            4 => "TRUNCATE oc_product_temp",
            5 => "TRUNCATE oc_product_temp_option",
        ];
    }
    
}