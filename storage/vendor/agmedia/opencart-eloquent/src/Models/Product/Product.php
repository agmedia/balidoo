<?php


namespace Agmedia\Models\Product;


use Agmedia\Helpers\Log;
use Agmedia\Models\Option\Option;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Product extends Model
{
    
    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var string
     */
    protected $table = 'product';
    
    /**
     * @var string
     */
    protected $primaryKey = 'product_id';
    
    /**
     * @var array
     */
    protected $guarded = [
        'product_id'
    ];
    
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options()
    {
        return $this->hasMany(ProductOption::class, 'product_id', 'product_id')->with('value');
    }
    
    
    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeSkus($query)
    {
        return ProductOption::where('product_id', $this->product_id)->pluck('sku');
    }
    
    
    /**
     * @param $options
     * @param $languages
     * @param $images
     *
     * @return \Illuminate\Support\Collection
     */
    public function make($options, $languages, $images)
    {
        if ($options->first()['IDVELICINA'] == '') {
            return $this->makeWithoutOptions($options, $languages, $images);
        }
        
        return $this->makeWithOptions($options, $languages, $images);
    }
    
    
    /**
     * @param $product
     * @param $languages
     * @param $images
     *
     * @return \Illuminate\Support\Collection
     */
    private function makeWithoutOptions($product, $languages, $images)
    {
        $response = [
            'sku'                 => $product->first()['IDROBA'],
            'upc'                 => '',
            'ean'                 => '',
            'jan'                 => '',
            'isbn'                => '',
            'mpn'                 => '',
            'location'            => '',
            'price'               => $product->first()[agconf('erp.price_tag')],
            'tax_class_id'        => 9,
            'quantity'            => $product->first()['ZALIHAK'],
            'minimum'             => 1,
            'subtract'            => 1,
            'stock_status_id'     => 6,
            'shipping'            => 1,
            'date_available'      => Carbon::now()->subDay()->format('Y-m-d'),
            'length'              => '',
            'width'               => '',
            'height'              => '',
            'length_class_id'     => 1,
            'weight'              => '',
            'weight_class_id'     => 1,
            'status'              => 1,
            'sort_order'          => 0,
            'manufacturer'        => '',
            'manufacturer_id'     => 0,
            'category'            => '',
            'filter'              => '',
            'download'            => '',
            'related'             => '',
            'image'               => $this->getMainImagePath($images),
            'points'              => '',
            'product_store'       => [0 => 0],
            'product_description' => $this->sortDescriptions($product->first(), $languages->toArray()),
            'product_image'       => $this->sortImages($images),
            'product_layout'      => [0 => ''],
            'product_category'    => [0 => agconf('erp.import_category_id')]
        ];
        
        $response['model'] = $product->first()['IDODJEL'];
        
        return collect($response);
    }
    
    
    /**
     * @param $options
     * @param $languages
     * @param $images
     *
     * @return \Illuminate\Support\Collection
     */
    private function makeWithOptions($options, $languages, $images)
    {
        Log::write('$response', 'testing3');
        
        $response = [
            'sku'                 => '',
            'upc'                 => '',
            'ean'                 => '',
            'jan'                 => '',
            'isbn'                => '',
            'mpn'                 => '',
            'location'            => '',
            'price'               => $options->min(agconf('erp.price_tag')),
            'tax_class_id'        => 9,
            'quantity'            => $options->sum('ZALIHAK'),
            'minimum'             => 1,
            'subtract'            => 1,
            'stock_status_id'     => 6,
            'shipping'            => 1,
            'date_available'      => Carbon::now()->subDay()->format('Y-m-d'),
            'length'              => '',
            'width'               => '',
            'height'              => '',
            'length_class_id'     => 1,
            'weight'              => '',
            'weight_class_id'     => 1,
            'status'              => 1,
            'sort_order'          => 0,
            'manufacturer'        => '',
            'manufacturer_id'     => 0,
            'category'            => '',
            'filter'              => '',
            'download'            => '',
            'related'             => '',
            'image'               => $this->getMainImagePath($images),
            'points'              => '',
            'product_store'       => [0 => 0],
            'product_description' => $this->sortDescriptions($options->first(), $languages->toArray()),
            'product_option'      => [0 => $this->sortOptions($options)],
            'product_image'       => $this->sortImages($images),
            'product_layout'      => [0 => ''],
            'product_category'    => [0 => agconf('erp.import_category_id')]
        ];
        
        $response['model'] = $options->first()['IDODJEL'];
    
        Log::write($response, 'testing3');
        
        return collect($response);
    }
    
    
    /**
     * @param $option
     * @param $languages
     *
     * @return array
     */
    private function sortDescriptions($option, $languages)
    {
        $langs = [
            agconf('erp.default_language_id') => $this->getDescriptionArray($option), // hr
        ];
    
        foreach (agconf('erp.additional_languages') as $key => $lang) {
            foreach ($languages as $index => $language) {
                $langs[$key] = $this->getDescriptionArray($language);
            }
        }
        
        return $langs;
    }
    
    
    /**
     * @param $language
     *
     * @return array
     */
    private function getDescriptionArray($language)
    {
        return [
            'name'             => ($language['NAZIV'] == '') ? $language['NAZIV_ODJELA'] : $language['NAZIV'],
            'description'      => ($language['OPIS_ODJEL'] == '') ? 'Opis' : $language['OPIS_ODJEL'],
            'meta_title'       => ($language['NAZIV'] == '') ? $language['NAZIV_ODJELA'] : $language['NAZIV'], // NAZIV_ODJELA
            'custom_alt'       => '',
            'custom_h1'        => '',
            'custom_h2'        => '',
            'custom_imgtitle'  => '',
            'meta_description' => ($language['OPIS_ODJEL'] == '') ? 'Opis' : $language['OPIS_ODJEL'],
            'meta_keyword'     => ($language['OPIS_ODJEL'] == '') ? 'Opis' : $language['OPIS_ODJEL'],
            'tag'              => '',
        ];
    }
    
    
    /**
     * @param $options
     *
     * @return array
     */
    private function sortOptions($options)
    {
        $response = [
            'product_option_id' => '',
            'name'              => 'Veličina',
            'option_id'         => agconf('erp.size_option_id'),
            'type'              => 'select',
            'required'          => 1
        ];
        
        foreach ($options->all() as $option) {
            $price_prefix = '';
            $price        = '';
            
            if ($options->min(agconf('erp.price_tag')) < $option[agconf('erp.price_tag')]) {
                $price_prefix = '+';
                $price        = $option[agconf('erp.price_tag')] - $options->min(agconf('erp.price_tag'));
            }
            
            $options_response[] = [
                'option_value_id'         => $option['option_id'],
                'product_option_value_id' => '',
                'quantity'                => $option['ZALIHAK'],
                'subtract'                => 1,
                'price_prefix'            => $price_prefix,
                'price'                   => $price,
                'points_prefix'           => '',
                'points'                  => '',
                'weight_prefix'           => '',
                'weight'                  => '',
                'sku'                     => $option['IDROBA'],
            ];
        }
        
        $response['product_option_value'] = $options_response;
        
        return $response;
    }
    
    
    /**
     * @param $images
     *
     * @return array
     */
    private function sortImages($images)
    {
        $response = [];
        
        foreach ($images as $image) {
            if ( ! $image['GLAVNA']) {
                $response[] = [
                    'product_id' => '',
                    'image'      => $image['path'],
                    'sort_order' => 0
                ];
            }
        }
        
        return $response;
    }
    
    
    /**
     * @param $images
     *
     * @return mixed
     */
    private function getMainImagePath($images)
    {
        foreach ($images as $image) {
            if ($image['GLAVNA']) {
                return $image['path'];
            }
        }
    }
    
    
    
    
}