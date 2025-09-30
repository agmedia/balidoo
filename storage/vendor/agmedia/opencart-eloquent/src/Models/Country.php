<?php


namespace Agmedia\Models;


use Agmedia\Helpers\Log;
use Agmedia\Models\Option\Option;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Country extends Model
{
    
    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var string
     */
    protected $table = 'country';
    
    /**
     * @var string
     */
    protected $primaryKey = 'country_id';
    
    /**
     * @var array
     */
    protected $guarded = [
        'country_id'
    ];
    
    
}