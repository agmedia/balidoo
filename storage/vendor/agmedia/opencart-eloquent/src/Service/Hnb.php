<?php


namespace Agmedia\Service;


use Agmedia\Helpers\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class Hnb
{
    
    /**
     * @param $currency
     *
     * @return mixed
     */
    public static function getCurrencyValue($currency = 'EUR')
    {
        $response = json_decode(file_get_contents('http://api.hnb.hr/tecajn/v1?valuta=' . $currency));
        
        return str_replace(',', '.', $response[0]->{'Srednji za devize'});
    }
}