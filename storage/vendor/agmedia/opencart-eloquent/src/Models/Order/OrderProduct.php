<?php


namespace Agmedia\Models\Order;


use Agmedia\Helpers\Log;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    
    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var string
     */
    protected $table = 'order_product';
    
    /**
     * @var string
     */
    protected $primaryKey = 'order_product_id';
    
    /**
     * @var array
     */
    protected $guarded = [
        'order_product_id'
    ];
    
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function option()
    {
        return $this->hasOne(OrderOption::class, 'order_product_id', 'order_product_id')->with('optionData');
    }
    
    
    /**
     * @param $request
     *
     * @return array
     */
    public static function makeRequest($request)
    {
        $response = [];
        $free_product = [];
        $has_free_product = false;
        $has_free_product_2 = false;
    
        // check if it has free product
        foreach ($request['totals'] as $total) {
            if ($total['code'] == 'tieredgiftstotal') {
                $has_free_product = str_replace('Free Gifts Discount - ', '', $total['title']);
            }
            if ($total['code'] == 'bgxyfreetotal') {
                $has_free_product_2 = abs($total['value']);
            }
        }
    
        // set free product
        if ($has_free_product) {
            foreach ($request['products'] as $product) {
                if ($has_free_product == $product['name']) {
                    $response[] = [
                        'IDROBA'   => $product['option']['option_data']['sku'],
                        'KOLICINA' => 1,
                        'CIJENA'   => number_format($product['price'], 2, '.', ''),
                        'RABAT'    => 100,
                        'IZNOS'    => number_format($product['total'], 2, '.', '')
                    ];
                }
                if ($has_free_product_2 == $product['price']) {
                    $response[] = [
                        'IDROBA'   => $product['option']['option_data']['sku'],
                        'KOLICINA' => 1,
                        'CIJENA'   => number_format($product['price'], 2, '.', ''),
                        'RABAT'    => 100,
                        'IZNOS'    => number_format($product['total'], 2, '.', '')
                    ];
                }
            }
        }
    
        // set shipping product
        foreach ($request['totals'] as $total) {
            if ($total['code'] == 'shipping') {
                $response[] = [
                    'IDROBA'   => ($total['value'] == '10.0000') ? 'US00000001' : 'US00000203',
                    'KOLICINA' => 1,
                    'CIJENA'   => intval(round($total['value'])),
                    'RABAT'    => 0,
                    'IZNOS'    => intval(round($total['value'])),
                ];
            }
        }
    
        // set other products
        foreach ($request['products'] as $product) {
            $qty = $product['quantity'];
    
            if ($has_free_product && $has_free_product == $product['name']) {
                $qty = $product['quantity'] - 1;
            }
            if ($has_free_product_2 && $has_free_product_2 == $product['price']) {
                $qty = $product['quantity'] - 1;
            }
    
            $model = $product['model'];
    
            if (isset($product['option']['option_data']['sku'])) {
                $model = $product['option']['option_data']['sku'];
            }
            
            $response[] = [
                'IDROBA'   => $model,
                'KOLICINA' => $qty,
                'CIJENA'   => number_format($product['price'], 2, '.', ''),
                'RABAT'    => 0,
                'IZNOS'    => number_format($product['total'], 2, '.', '')
            ];
        }
        
        return $response;
    }
    
}