<?php


namespace Agmedia\Models\Option;


use Agmedia\Helpers\Log;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    
    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var string
     */
    protected $table = 'option';
    
    /**
     * @var string
     */
    protected $primaryKey = 'option_id';
    
    /**
     * @var array
     */
    protected $guarded = [
        'option_id'
    ];
    
    
    /**
     * @param $options
     *
     * @return mixed
     */
    public function make($options)
    {
        $new_options = $options->map(function ($item) {
            $option = OptionValueDescription::where('name', ltrim($item['IDVELICINA'], '0'))->first();
            
            if (isset($option['option_value_id'])) {
                $item['option_id'] = $option['option_value_id'];
    
                return $item;
            }
            
            $option_value = OptionValue::insertGetId([
                'option_id' => agconf('erp.size_option_id'),
                'image' => 13,
                'sort_order' => 0,
            ]);
            
            for ($i = 1; $i < 4; $i++) {
                OptionValueDescription::insert([
                    'option_value_id' => $option_value,
                    'language_id' => $i,
                    'option_id' => agconf('erp.size_option_id'),
                    'name' => ltrim($item['IDVELICINA'], '0'),
                ]);
            }
            
            $item['option_id'] = $option_value;
            
            return $item;
        });
        
        return $new_options;
    }
}