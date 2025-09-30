<?php


namespace Agmedia\Service;


use Agmedia\Helpers\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class Service
{
    
    /**
     * @var Client
     */
    protected $service;
    
    
    /**
     * Service constructor.
     */
    public function __construct()
    {
        $this->service = new Client();
    }
    
    
    /**
     * @return Collection
     */
    public function getProducts()
    {
        $response = new Collection(
            json_decode($this->service->get(agconf('erp.base_url') . 'sif_roba/getitems' . agconf('erp.url_sufix'))->getBody()->getContents(), true)
        );
        
        return $response->whereNotIn('IDODJEL', ['US', 'PLT', 'REP', 'ROB'])->groupBy('IDODJEL');
    }
    
    
    /**
     * @param $odjel
     *
     * @return Collection
     */
    public function getOptions($odjel)
    {
        return new Collection(
            json_decode($this->service->get(agconf('erp.base_url') . 'sif_roba/getodjel/' . $odjel . agconf('erp.url_sufix'))->getBody()->getContents(), true)
        );
    }
    
    
    /**
     * @param $sku
     *
     * @return Collection
     */
    public function getTraslations($sku)
    {
        $response  = [];
        $languages = ['FRA'];
        
        foreach ($languages as $language) {
            $url = agconf('erp.base_url') . 'sif_roba/getPrijevod/' . $language . '/' . $sku . agconf('erp.url_sufix');
            
            $collection = new Collection(
                json_decode($this->service->get($url)->getBody()->getContents(), true)
            );
           
            $response[$language] = $collection->first();
        }
        
        return new Collection($response);
    }
    
    
    /**
     * @param $odjel
     *
     * @return Collection
     */
    public function getImages($odjel)
    {
        return new Collection(
            json_decode($this->service->get(agconf('erp.base_url') . 'sif_roba/getOdjelSlike/' . $odjel . agconf('erp.url_sufix'))->getBody()->getContents(), true)
        );
    }
    
    
    /**
     * @param $request
     *
     * @return Collection
     */
    public function sendOrder($request, $target = false)
    {
        $path = $target ? '&idfirma=' . $target : '';
        
        //if ($target == 2 || $target == 3) {
         //   $request['narudzba']['VALUTA'] = 978;
            $request['narudzba']['TECAJ'] = Hnb::getCurrencyValue();
       // }

        $res = json_decode($this->service->post(agconf('erp.base_url') . 'narudzba/create' . $path . agconf('erp.url_sufix'), ['body' => json_encode($request)])->getBody()->getContents(), true);

        return new Collection(
            $res
        );
    }
}