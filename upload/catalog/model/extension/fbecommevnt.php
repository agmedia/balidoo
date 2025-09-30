<?php 
class ModelExtensionfbecommevnt extends Controller { 
   	private $modname = 'fbecommevnt';
   	private $langid = 0;
	private $storeid = 0;
	private $custgrpid = 0;
	
	public function __construct($registry) {
		parent::__construct($registry);
		
		$this->langid = (int)$this->config->get('config_language_id');
		$this->storeid = (int)$this->config->get('config_store_id');
		$this->custgrpid = (int)$this->config->get('config_customer_group_id');
		
		if(substr(VERSION,0,3)>='3.0') { 
			$this->modname = 'module_fbecommevnt';
 		}
 	}
	
	public function index() {
  		$data[$this->modname.'_status'] = $this->config->get($this->modname.'_status');
	}
	public function getmodstatus() {
		return ($this->config->get($this->modname.'_sts'.$this->storeid) && $this->config->get($this->modname.'_status')) ? true : false;
	}
	public function getFBID() {
  		return $this->getmodstatus() ? $this->config->get($this->modname.'_fb_pixel_id'.$this->storeid) : false;
	}
	public function getFBCATALOGID() {
  		return $this->getmodstatus() ? $this->config->get($this->modname.'_fb_product_catalog_id'.$this->storeid) : false;
	}
	public function getlang() {	
		$setting_lang = $this->config->get($this->modname.'_lang');
 		$lang = array('text_atc', 'text_wishlist', 'text_compare', 'text_removecart', 'text_loginevent', 'text_regevent', 'text_chkstp_onename', 'text_chkstp_twoname', 'text_chkstp_threename', 'text_chkstp_fourname', 'text_chkstp_fivename','text_chkstp_sixname');
 		foreach($lang as $lng) {
			$module_lang[$lng] = isset($setting_lang[$lng][$this->langid]) ? $setting_lang[$lng][$this->langid] : $lng;
		} 
 		return $module_lang;
	}
	
	public function getordertax($order_id) {
 		$tax_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'tax'");
		if (isset($tax_query->row['value']) && $tax_query->row['value']) {
			return $tax_query->row['value'];
		} 
		return 0;
	}	
	public function getordershipping($order_id) {
 		$tax_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'shipping'");
		if (isset($tax_query->row['value']) && $tax_query->row['value']) {
			return $tax_query->row['value'];
		} 
		return 0;
	} 	
	public function getOrderProduct($order_id) {
		$query = $this->db->query("SELECT op.*,p.sku,p.tax_class_id, (select m.name from " . DB_PREFIX . "manufacturer m where m.manufacturer_id = p.manufacturer_id) as brandname, (select cd.name from " . DB_PREFIX . "category_description cd inner join " . DB_PREFIX . "product_to_category pc on pc.category_id = cd.category_id where pc.product_id = p.product_id limit 1) AS category FROM " . DB_PREFIX . "order_product op INNER JOIN " . DB_PREFIX . "product p ON p.product_id = op.product_id LEFT JOIN " . DB_PREFIX . "order_option oo ON (oo.order_product_id = op.order_product_id) WHERE op.order_id = '" . (int)$order_id . "' AND oo.order_id IS NULL GROUP BY op.order_product_id");
 		
 		if($query->num_rows) {
			return $query->rows;
		}
		
		return array();
 	}	              
	public function getOrderProductOptions($order_id) {
		$query = $this->db->query("SELECT op.*,p.sku,p.tax_class_id, (select m.name from " . DB_PREFIX . "manufacturer m where m.manufacturer_id = p.manufacturer_id) as brandname, (select cd.name from " . DB_PREFIX . "category_description cd inner join " . DB_PREFIX . "product_to_category pc on pc.category_id = cd.category_id where pc.product_id = p.product_id limit 1) AS category, oo.name as option_name, oo.value,oo.order_product_id,GROUP_CONCAT(DISTINCT oo.name, ': ', oo.value SEPARATOR ' - ') as options_data FROM " . DB_PREFIX . "order_product op INNER JOIN " . DB_PREFIX . "product p ON p.product_id = op.product_id INNER JOIN " . DB_PREFIX . "order_option oo ON op.order_product_id = oo.order_product_id WHERE op.order_id = '" . (int)$order_id . "' AND op.order_product_id = oo.order_product_id GROUP BY oo.order_product_id");
		
 		if($query->num_rows) {
			return $query->rows;
		}
		
		return array();
	}
	public function getProdCatName($product_id) {
		$query = $this->db->query("SELECT name FROM " . DB_PREFIX . "category_description cd INNER JOIN " . DB_PREFIX . "product_to_category pc ON pc.category_id = cd.category_id WHERE 1 AND pc.product_id = '".$product_id."' limit 1");
		return (! empty($query->row['name'])) ? $query->row['name'] : '';
	}
	public function getProdBrandName($product_id) {
		$query = $this->db->query("SELECT name from " . DB_PREFIX . "manufacturer m INNER JOIN " . DB_PREFIX . "product p on m.manufacturer_id = p.manufacturer_id WHERE 1 AND p.product_id = ".$product_id);
		return (! empty($query->row['name'])) ? $query->row['name'] : '';
	}
	
	public function getcurval($taxprc) {
		if(substr(VERSION,0,3)>='3.0' || substr(VERSION,0,3)=='2.3' || substr(VERSION,0,3)=='2.2') { 
			$taxprc = $this->currency->format($taxprc, $this->session->data['currency'], false, false);
		} else {
			$taxprc = $this->currency->format($taxprc, '', false, false);
		}	
		return $taxprc;
	}
	public function getchksuccess($order_id = 0) {
		if(!empty($order_id) && $this->getFBID()) { 
 			$this->load->model('checkout/order');
			
			$orderdata = $this->model_checkout_order->getOrder($order_id);
			if(!empty($orderdata)) {
				$orderProduct = $this->getOrderProduct($order_id);
				$orderProductOptions = $this->getOrderProductOptions($order_id);
				$order_tax = $this->getordertax($order_id);
				$order_shipping = $this->getordershipping($order_id);
				
				$product_data = array();
				$purchase = array();
  			
				if(!empty($orderProduct)) {
					foreach ($orderProduct as $product_info) { 
						$price = $this->tax->calculate($product_info['price'] , $product_info['tax_class_id'], $this->config->get('config_tax')); 
						$product_data[] = array(
							"product_catalog_id" => $this->getFBCATALOGID(),
							"id" => $product_info['product_id'],
							"quantity" => $product_info['quantity'],
							"item_price" => $this->getcurval($price),
						);
					} 
				}
				
				if(!empty($orderProductOptions)) {
					foreach ($orderProductOptions as $product_info) { 
						$price = $this->tax->calculate($product_info['price'] , $product_info['tax_class_id'], $this->config->get('config_tax')); 
						$product_data[] = array(
							"product_catalog_id" => $this->getFBCATALOGID(),
							"id" => $product_info['product_id'],
							"quantity" => $product_info['quantity'],
							"item_price" => $this->getcurval($price),
						);
					} 
				}
				
				$purchase = array(
					"value" => $this->getcurval($orderdata['total']),
					"currency" => $this->session->data['currency'],
					"content_type" => 'product', 
					"contents" => $product_data
				);
				
 				$leaddata = array(
					"product_catalog_id" => $this->getFBCATALOGID(),
					"content_category" => 'completeorder',
					"content_name" => 'leadtracking',
					"value" => 1,
					"currency" => $this->session->data['currency'], 
				);
  				
$jsonpurchase_data = json_encode($purchase);
$jsonlead_data = json_encode($leaddata);
$returndata = <<<EOF
<script type="text/javascript">
fbq('track', 'Purchase', $jsonpurchase_data);
fbq('track', 'Lead', $jsonlead_data);
</script>
EOF;
return $returndata;
			}
 		} 
	}
    
    public function viewprod($product_id) {
		if(!empty($product_id) && $this->getFBID()) { 
 			$this->load->model('catalog/product');
            
            $product_data = array();
   			$product_info = $this->model_catalog_product->getProduct($product_id);
            
            if ($product_info) { 
				$price = $product_info['special'] ? $product_info['special'] : $product_info['price'];
 				$price = $this->tax->calculate($price , $product_info['tax_class_id'], $this->config->get('config_tax')); 
 			
				$product_data = array(
					"product_catalog_id" => $this->getFBCATALOGID(),
					"content_ids" => array($product_info['product_id']),
					"content_type" => 'product',
					"content_name" => htmlspecialchars_decode($product_info['name']),
					"content_category" => $this->getProdCatName($product_info['product_id']),
					"value" => $this->getcurval($price),
					"currency" => $this->session->data['currency'], 
				); 
    				
$jsonproduct_data = json_encode($product_data);
$returndata = <<<EOF
<script type="text/javascript">
fbq('track', 'ViewContent', $jsonproduct_data);
</script>
EOF;
return $returndata;
}
 		} 
	}
    
    public function searchproduct() {
		if(isset($this->request->get['search']) && $this->getFBID()) { 
 			$this->load->model('catalog/product');
            
            $product_data = array();
            $filter_data = array( 'filter_name' => $this->request->get['search'], 'start' => 0, 'limit' => 5 );
            $results = $this->model_catalog_product->getProducts($filter_data);
            
            if ($results) { 
				foreach ($results as $product_info) {
                    $price = $this->tax->calculate($product_info['price'] , $product_info['tax_class_id'], $this->config->get('config_tax')); 
                    
                    $product_data[] = array(
                        "product_catalog_id" => $this->getFBCATALOGID(),
                        "id" => $product_info['product_id'],
                        "quantity" => $product_info['quantity'],
                        "item_price" => $this->getcurval($price),
                    );
                }
                
                $searchdata = array(
                    "value" => $this->cart->getTotal() ? $this->getcurval($this->cart->getTotal()) : 1,
                    "currency" => $this->session->data['currency'],
                    "content_type" => 'product', 
                    "content_category" => 'search', 
                    "search_string" => $this->request->get['search'], 
                    "contents" => $product_data,
                );
    				
$jsonsearch_data = json_encode($searchdata);
$returndata = <<<EOF
<script type="text/javascript">
fbq('track', 'Search', $jsonsearch_data);
</script>
EOF;
return $returndata;
}
 		} 
	}
    
    public function begincheckout() {
		if($this->getFBID()) { 
        	$langdata = $this->getlang();
 			$product_data = array();
             
            if ($this->cart->hasProducts()) {
                foreach ($this->cart->getProducts() as $product_info) { 
                    $price = $this->tax->calculate($product_info['price'] , $product_info['tax_class_id'], $this->config->get('config_tax')); 
                    
                    $product_data[] = array(
                        "product_catalog_id" => $this->getFBCATALOGID(),
                        "id" => $product_info['product_id'],
                        "quantity" => $product_info['quantity'],
                        "item_price" => $this->getcurval($price),
                    );
                } 
                
                $cartdata = array(
                    "value" => $this->getcurval($this->cart->getTotal()),
                    "currency" => $this->session->data['currency'],
                    "content_type" => 'product', 
                    "contents" => $product_data
                );			 
   				
$jsoncart_data = json_encode($cartdata);
$fbecommevnt_text_chkstp_onename = $langdata["text_chkstp_onename"];
$returndata = <<<EOF
<script type="text/javascript">
fbq('track', 'InitiateCheckout', $jsoncart_data);
fbq('trackCustom', '$fbecommevnt_text_chkstp_onename', $jsoncart_data);
</script>
EOF;
return $returndata;
}
 		} 
	}
    
    public function checkoutfunnel() {
		if($this->getFBID()) {
        	$langdata = $this->getlang();
 			$product_data = array();
             
            if ($this->cart->hasProducts()) {
                foreach ($this->cart->getProducts() as $product_info) { 
                    $price = $this->tax->calculate($product_info['price'] , $product_info['tax_class_id'], $this->config->get('config_tax')); 
                    
                    $product_data[] = array(
                        "product_catalog_id" => $this->getFBCATALOGID(),
                        "id" => $product_info['product_id'],
                        "quantity" => $product_info['quantity'],
                        "item_price" => $this->getcurval($price),
                    );
                }
                
                $cartdata = array(
                    "value" => $this->getcurval($this->cart->getTotal()),
                    "currency" => $this->session->data['currency'],
                    "content_type" => 'product', 
                    "contents" => $product_data
                ); 
                
$jsoncart_data = json_encode($cartdata);
$fbecommevnt_text_chkstp_onename = $langdata["text_chkstp_onename"];
$fbecommevnt_text_chkstp_twoname = $langdata["text_chkstp_twoname"];
$fbecommevnt_text_chkstp_threename = $langdata["text_chkstp_threename"];
$fbecommevnt_text_chkstp_fourname = $langdata["text_chkstp_fourname"];
$fbecommevnt_text_chkstp_fivename = $langdata["text_chkstp_fivename"];
$fbecommevnt_text_chkstp_sixname = $langdata["text_chkstp_sixname"];
$returndata = <<<EOF
<script type="text/javascript">
$(document).delegate('#button-register', 'click', function() {    
    fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_twoname', $jsoncart_data, 0);
    fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_threename', $jsoncart_data, 0);			
});
$(document).delegate('#button-guest', 'click', function() {    
    fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_twoname', $jsoncart_data, 0);
    fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_threename', $jsoncart_data, 0);		
});
$(document).delegate('#button-payment-address', 'click', function() {
	fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_twoname', $jsoncart_data, 0);
});
$(document).delegate('#button-shipping-address', 'click', function() {
	fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_threename', $jsoncart_data, 0);
});
$(document).delegate('#button-shipping-method', 'click', function() {
	fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_fourname', $jsoncart_data, 0);
});
$(document).delegate('#button-payment-method', 'click', function() {
	fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_fivename', $jsoncart_data, 1);
});
$(document).delegate('#button-confirm', 'click', function() {
	fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_sixname', $jsoncart_data, 0);
});
</script>
EOF;
return $returndata;
}
 		} 
	}
    
    public function contactus() {
		if($this->getFBID()) {
$returndata = <<<EOF
<script type="text/javascript">
fbq('track', 'Contact');
</script>
EOF;
return $returndata;
 		} 
	}
    
    public function loginevent() {
		if($this->getFBID()) {
        $langdata = $this->getlang();
$loginevent = $langdata['text_loginevent'];
$returndata = <<<EOF
<script type="text/javascript">
fbq('trackCustom', '$loginevent');
</script>
EOF;
return $returndata;
 		} 
	}
    
    public function registerevent() {
		if($this->getFBID()) {
        $langdata = $this->getlang();
        $leaddata = array(
            "product_catalog_id" => $this->getFBCATALOGID(),
            "content_category" => 'completesignup',
            "content_name" => 'leadtracking',
            "value" => 1,
            "currency" => $this->session->data['currency'], 
        );
$jsonlead_data = json_encode($leaddata);
$regevent = $langdata['text_regevent'];
$returndata = <<<EOF
<script type="text/javascript">
fbq('trackCustom', '$regevent');
fbq('track', 'CompleteRegistration');
fbq('track', 'Lead', $jsonlead_data);
</script>
EOF;
return $returndata;
 		} 
	}
    
    public function gettrackcode() {
    if($this->getFBID()) { 
    	$fbecommevnt_fb_pixel_id = $this->getFBID();
$returndata = <<<EOF
<script type="text/javascript">
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '$fbecommevnt_fb_pixel_id');
fbq('track', 'PageView');
fbq('track', 'FindLocation');
$('head').after('<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=$fbecommevnt_fb_pixel_id&ev=PageView&noscript=1"/></noscript>');
</script>
EOF;
return $returndata;
		}
	}    
}