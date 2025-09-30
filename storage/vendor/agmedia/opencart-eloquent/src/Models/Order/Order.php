<?php


namespace Agmedia\Models\Order;


use Agmedia\Helpers\Log;
use Agmedia\Models\Country;
use Agmedia\Service\Hnb;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Order extends Model
{
    
    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * @var string
     */
    protected $table = 'order';
    
    /**
     * @var string
     */
    protected $primaryKey = 'order_id';
    
    /**
     * @var array
     */
    protected $guarded = [
        'order_id'
    ];
    
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(OrderProduct::class, 'order_id', 'order_id')->with('option');
    }
    
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function totals()
    {
        return $this->hasMany(OrderTotal::class, 'order_id', 'order_id')->orderBy('sort_order');
    }
    
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function status()
    {
        return $this->hasOne(OrderStatus::class, 'order_status_id', 'order_status_id');
    }
    
    
    /**
     * @param $request
     *
     * @return array
     */
    public function makeRequest($request)
    {
        $response = [
            'narudzba' => $this->structure($request),
            'stavke'   => OrderProduct::makeRequest($request)
        ];
        
        Log::info($response, 'order_' . $request['order_id']);
        
        return $response;
    }
    
    
    /**
     * @param $request
     *
     * @return array
     */
    private function structure($request)
    {
        return [
            'CMS_ID'              => agconf('erp.order_prefix') . $request['order_id'],
            'DATUM_VRIJEME'       => Carbon::make($request['date_modified'])->format('d.m.Y H:i:s'),
            //'27.09.2019 13:57:00',
            'NARUCITELJ_CMS_ID'   => '1',
            'NARUCITELJ_EMAIL'    => $request['email'],
            'NARUCITELJ_IME'      => $request['payment_firstname'],
            'NARUCITELJ_PREZIME'  => $request['payment_lastname'],
            'NARUCITELJ_ADRESA'   => $request['payment_address_1'],
            'NARUCITELJ_MJESTO'   => $request['payment_city'],
            'NARUCITELJ_POSTA'    => $request['payment_postcode'],
            'NARUCITELJ_TELEFON'  => $request['telephone'],
            //
            'NARUCITELJ_NAZIV'    => isset($request['custom_field'][2]) ? $request['custom_field'][2] : '',
            'NARUCITELJ_OIB'      => $this->checkOIB($request['custom_field'][1]),
            'NARUCITELJ_VAT'      => $this->checkOIB($request['custom_field'][1], true),
            'NARUCITELJ_ZEMLJA'   => $this->getCountryCode($request['payment_country']),
            //
            'DOSTAVA_IME_PREZIME' => $request['shipping_firstname'] . ' ' . $request['shipping_lastname'],
            'DOSTAVA_ADRESA'      => $request['shipping_address_1'],
            'DOSTAVA_MJESTO'      => $request['shipping_city'],
            'DOSTAVA_POSTA'       => $request['shipping_postcode'],
            'NAPOMENA'            => $request['comment'],
            'VALUTA'              => agconf('erp.order_valuta'),
            'IZNOS_UKUPNO'        => OrderTotal::resolveTotal($request['totals']),
            'TECAJ'               => 1
        ];
    }
    
    
    /**
     * Provjeri je li privatni kupac iz DE ili AT,
     * ako je vrati ID za API request url-query.
     * @npr...route=narudzba/create&idfirma=2
     *
     * @param $request
     *
     * @return bool|int
     */
    public function resolveTarget($request)
    {
        if (empty($request['custom_field']) ||
            $this->checkOIB($request['custom_field'][1]) == '' &&
            $this->checkOIB($request['custom_field'][1], true) == '')
        {
            if ($request['payment_country'] == 'Slovenia') { // Njemačka
                return 0;
            }
            if ($request['payment_country'] == 'Germany') { // Njemačka
                return 3;
            }
            if ($request['payment_country'] == 'Austria') { // Austria
                return 2;
            }
        }
        
        return 1;
    }
    
    
    /**
     * @param      $string
     * @param bool $forign
     *
     * @return false|string
     */
    private function checkOIB($string, $forign = false)
    {
        if (isset($string) && strlen($string) > 3) {
            // If its a forign country
            if ($forign && substr($string, 0, 2) != 'HR') {
                return $string;
            }
    
            // If its a Croatia
            if ( ! $forign && substr($string, 0, 2) == 'HR') {
                return substr($string, 2);
            }
        }
        
        return '';
    }
    
    
    /**
     * @param $country_id
     *
     * @return string
     */
    private function getCountryCode($country)
    {
        $country = Country::where('name', $country)->first();
        
        if ($country) {
            return $country['iso_code_3'];
        }
        
        return '';
    }
    
}