<?php


namespace Agmedia\Models\Product;


use Agmedia\Helpers\Log;
use Agmedia\Models\Config;
use Agmedia\Models\Option\OptionValue;
use Agmedia\Models\Option\OptionValueDescription;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ProductOption extends Model
{
    
    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var string
     */
    protected $table = 'product_option_value';
    
    /**
     * @var string
     */
    protected $primaryKey = 'product_option_value_id';
    
    /**
     * @var array
     */
    protected $guarded = [
        'product_option_value_id'
    ];
    
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function value()
    {
        return $this->hasOne(OptionValueDescription::class, 'option_value_id', 'option_value_id')->where('language_id', Config::getLanguage());
    }
    
    
    /**
     * @param $product_options
     *
     * @return mixed
     */
    public static function updateStockAndPrices($product_options, $has_default_qty)
    {
        $temp_options  = '';
        $product_price = $product_options->min(agconf('erp.price_tag'));
        
        foreach ($product_options as $option) {
            $price_prefix = '';
            $price        = '';
    
            if ($product_options->min(agconf('erp.price_tag')) < $option[agconf('erp.price_tag')]) {
                $price_prefix = '+';
                $price        = $option[agconf('erp.price_tag')] - $product_options->min(agconf('erp.price_tag'));
            }
            
            if ( ! $price) {
                $price = 0;
            }
    
            $temp_options .= '("' . $option['IDROBA'] . '", ' . ($has_default_qty ? $has_default_qty : $option['ZALIHAK']) . ', "' . $price_prefix . '", ' . $price . '),';
        }
        
        return $temp_options;
    }
}