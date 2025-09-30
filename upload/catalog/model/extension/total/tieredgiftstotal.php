<?php
class ModelExtensionTotalTieredgiftsTotal extends Model {
	private $totalName;
    private $totalPath;
    private $totalsLink;
    private $moduleModel;
    private $error = array(); 
    private $data = array();
	

	public function __construct($registry) {
        parent::__construct($registry);
        $this->config->load('isenselabs/tieredgifts');

        // Module VERSION
        $this->moduleVersion = $this->config->get('tieredgifts_moduleVersion');        

        $this->totalName        = $this->config->get('tieredgifts_totalName');        
        $this->totalPath        = $this->config->get('tieredgifts_totalPath');

        $this->load->model($this->config->get('tieredgifts_modulePath'));

        $this->moduleModel = $this->{$this->config->get('tieredgifts_callModel')};

        $this->load->language($this->totalPath);
    }

	public function getTotal($totals) {

		if(version_compare(VERSION, '2.2.0.0', '>=')) {
			$total = &$totals['total'];
			$taxes = &$totals['taxes'];
			$total_data = &$totals['totals'];
		}
		
		$grandTotal = 0;
		
		$cartProducts = $this->cart->getProducts();

		$giftProduct = $this->moduleModel->getGiftProduct($cartProducts);

		if($giftProduct === false) return;

		$grandTotal += $giftProduct['price'];

		$tax_rates = $this->tax->getRates((float)$giftProduct['price'], $giftProduct['tax_class_id']);	
		foreach ($tax_rates as $tax_rate) {
			$taxes[$tax_rate['tax_rate_id']] -= $tax_rate['amount'];
		} 
		
		$total_data[] = array(
			'code'       => 'tieredgiftstotal',
			'title'      => $this->language->get('entry_title_fgd') . ' - ' . $giftProduct['name'],
			'text'       => $this->currency->format(-$grandTotal, $this->config->get('config_currency')),
			'value'      => -$grandTotal,
			'sort_order' => $this->config->get($this->totalName. '_sort_order')
		);
		
		$total -= (float)$grandTotal;

		if ($total < 0) {
			$total = 0;
		}
	}
}