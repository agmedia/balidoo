<?php


namespace Agmedia\Helpers;


use Illuminate\Database\Eloquent\Model;

class DHL extends Model
{
    
    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var string
     */
    protected $table = 'hittech_dhl_details_new';
    
    /**
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array
     */
    protected $guarded = [
        'id'
    ];
    
    
    public static function hasLabel($order_id)
    {
        $label = self::where('order_id', $order_id)->first();
        
        if ($label) {
            return true;
        }
        
        return false;
    }
}