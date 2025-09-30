<?php


namespace Agmedia\Models\Order;


use Agmedia\Helpers\Log;
use Illuminate\Database\Eloquent\Model;

class OrderTotal extends Model
{
    
    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var string
     */
    protected $table = 'order_total';
    
    /**
     * @var string
     */
    protected $primaryKey = 'order_total_id';
    
    /**
     * @var array
     */
    protected $guarded = [
        'order_total_id'
    ];
    
    
    public static function resolveTotal($totals)
    {
        $free_total = 0;
        
        foreach ($totals as $total) {
            
            if ($total['code'] == 'total') {
                return number_format($total['value'] - $free_total, 2, '.', '');
            }
        }
    }
    
}