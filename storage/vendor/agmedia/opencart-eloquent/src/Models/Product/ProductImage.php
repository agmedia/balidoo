<?php


namespace Agmedia\Models\Product;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    
    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var string
     */
    protected $table = 'product_image';
    
    /**
     * @var string
     */
    protected $primaryKey = 'product_image_id';
    
    /**
     * @var array
     */
    protected $guarded = [
        'product_image_id'
    ];
    
}