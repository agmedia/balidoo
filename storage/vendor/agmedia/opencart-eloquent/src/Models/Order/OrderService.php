<?php


namespace Agmedia\Models\Order;


use Agmedia\Helpers\Log;
use Illuminate\Database\Eloquent\Model;

class OrderService extends Model
{
    
    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var string
     */
    protected $table = 'order_service';
    
    /**
     * @var string
     */
    protected $primaryKey = 'order_service_id';
    
    /**
     * @var array
     */
    protected $guarded = [
        'order_service_id'
    ];
    
    
    /**
     * @param $response
     * @param $order_id
     *
     * @return mixed
     */
    public static function storeResponse($response, $order_id)
    {
        $data = $response->toArray();
        
        if (isset($data['ZAPIS'])) {
            return self::insert([
                'order_id' => $order_id,
                'response' => $data['ZAPIS']['BROJDOK'],
                'value'    => $response->toJson()
            ]);
        }
        
        return false;
    }
    
    
    /**
     * @param $order_id
     *
     * @return mixed
     */
    public static function info($order_id)
    {
        $order = self::where('order_id', $order_id)->first();
        
        if ($order) {
            return $order->toArray();
        }
        return false;
    }
    
    
    /**
     * @param $order_id
     *
     * @return bool
     */
    public static function exist($order_id)
    {
        $order = self::where('order_id', $order_id)->first();
    
        if ($order) {
            return true;
        }
        return false;
    }
    
}