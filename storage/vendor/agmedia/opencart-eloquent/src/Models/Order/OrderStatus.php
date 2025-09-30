<?php


namespace Agmedia\Models\Order;


use Agmedia\Helpers\Log;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    
    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var string
     */
    protected $table = 'order_status';
    
    /**
     * @var string
     */
    protected $primaryKey = 'order_status_id';
    
    /**
     * @var array
     */
    protected $guarded = [
        'order_status_id'
    ];
    
    
}