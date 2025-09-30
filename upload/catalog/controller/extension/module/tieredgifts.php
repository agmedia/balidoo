<?php
class ControllerExtensionModuletieredgifts extends Controller {
	private $moduleName;
    private $moduleVersion;
    private $callModel;
    private $modulePath;
    private $moduleModel;
    private $extensionsLink;
    private $error = array(); 
    private $data = array();
	

	public function __construct($registry) {
        parent::__construct($registry);

        $this->config->load('isenselabs/tieredgifts');

        // Module VERSION
        $this->moduleVersion = $this->config->get('tieredgifts_moduleVersion');        

        /* OC version-specific declarations - Begin */
        $this->moduleName        = $this->config->get('tieredgifts_moduleName');        
        $this->callModel         = $this->config->get('tieredgifts_callModel');
        $this->modulePath        = $this->config->get('tieredgifts_modulePath');
        /* OC version-specific declarations - End */

        // Multi-Store
        $this->load->model('setting/store');
        // Settings
        $this->load->model('setting/setting');
        // Multi-Lingual
        $this->load->model('localisation/language');
        
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');
    }

	public function removeGiftFromCart(){
		if (isset($this->request->get['cart_id'])) {
			$cart_id = $this->request->get['cart_id'];
			$this->cart->remove($cart_id);
		}
	}

	public function getOptions(){

			$this->load->language($this->modulePath);
        	$this->load->model($this->modulePath);
        

			$product_id = 0;
			if (isset($this->request->get['product_id'])) {
				$product_id = $this->request->get['product_id'];
			}

			$this->data['text_select'] = '-- Select -- ';

			$this->data['currentGift'] = $this->{$this->callModel}->getGiftProduct($this->cart->getProducts());

			$this->data['button_cart'] = $this->language->get('tieredgifts_fancybox_add_to_cart_button');

			if($this->data['currentGift'] != false) {
				$this->data['removeGiftFromCart'] = $this->url->link($this->modulePath.'/removeGiftFromCart', '' , 'SSL');

				$this->data['button_cart'] = $this->language->get('tieredgifts_fancybox_change_gift_button');
			}

			$this->load->model('catalog/product');
			$product_info = $this->model_catalog_product->getProduct($product_id);

			if ($product_info['image']) {
				$image = $this->model_tool_image->resize($product_info['image'], 200, 200);
			} else {
				$image = false;
			}
			  
			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency'));
			} else {
				$price = false;
			}
					
			if ((float)$product_info['special']) {
				$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency'));							
			} else {
				$special = false;			
			}
			
			if ($this->config->get('config_review_status')) {
				$rating = (int)$product_info['rating'];
			} else {
				$rating = false;
			}
			
			$product_options = $this->model_catalog_product->getProductOptions($product_info['product_id']);
			$this->data['options'] = array();

			foreach ($this->model_catalog_product->getProductOptions($product_info['product_id']) as $option) { 
				if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox' || $option['type'] == 'image') { 
					$option_value_data = array();
					foreach ($option['product_option_value'] as $option_value) {
						if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
							if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
								$option_price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency'));
							} else {
								$option_price = 0;
							}
												
							$option_value_data[] = array(
								'product_option_value_id' => $option_value['product_option_value_id'],
								'option_value_id'         => $option_value['option_value_id'],
								'name'                    => $option_value['name'],
								'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
								'price'                   => $option_price,
								'price_prefix'            => $option_value['price_prefix']
							);
						}
					}

					$this->data['options'][] = array(
						'product_option_id' => $option['product_option_id'],
						'option_id'         => $option['option_id'],
						'name'              => $option['name'],
						'type'              => $option['type'],
						'option_value'      => $option_value_data,
						'required'          => $option['required']
					);					
				} elseif ($option['type'] == 'text' || $option['type'] == 'textarea' || $option['type'] == 'file' || $option['type'] == 'date' || $option['type'] == 'datetime' || $option['type'] == 'time') {
					$this->data['options'][] = array(
						'product_option_id' => $option['product_option_id'],
						'option_id'         => $option['option_id'],
						'name'              => $option['name'],
						'type'              => $option['type'],
						'option_value'      => $option['value'],
						'required'          => $option['required']
					);						
				}
			}

			$this->data['products'][] = array(
				'product_id' => $product_info['product_id'],
				'thumb'   	 => $image,
				'name'    	 => $product_info['name'],
				'price'   	 => $price,
				'special' 	 => $special,
				'rating'     => $rating,
				'reviews'    => sprintf($this->language->get('tieredgifts_text_reviews'), (int)$product_info['reviews']),
				'href'    	 => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
				'options'    => $this->data['options']
			);

			$this->data['heading_title'] = $product_info['name'];

	        echo $this->load->view($this->modulePath.'_options', $this->data);
	}

	public function changeGift(){	

		$this->load->language($this->modulePath);
        $this->load->model($this->modulePath);

		if(isset($this->request->post['product_id'])) {
           $product_id = $this->request->post['product_id'];
        } else {
        	$product_id = 0;
        }

    	$giftInCart = $this->{$this->callModel}->getGiftProduct($this->cart->getProducts());

    	if($giftInCart !== false){
    		
    		if(version_compare(VERSION, '2.1.0.0', '<')){
    			$this->cart->remove($giftInCart['key']);
    		} else {
    			$this->cart->remove($giftInCart['cart_id']);
    		}
    		
    		$this->cart->add($product_id);
    		$product_info = $this->model_catalog_product->getProduct($product_id);
    		$this->session->data['success'] = sprintf($this->language->get('tieredgifts_changed_gift'), $product_info['name']);
    	}
	}

	public function listing(){

		$this->load->language($this->modulePath);
    	$this->load->model($this->modulePath);

		$data['modulePath'] = $this->modulePath;

		$this->document->addStyle('catalog/view/theme/default/stylesheet/tieredgifts.css');

		$this->document->addScript('catalog/view/javascript/'.$this->moduleName.'/fancybox/jquery.fancybox.pack.js');
		$this->document->addStyle('catalog/view/javascript/'.$this->moduleName.'/fancybox/jquery.fancybox.css');

		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
		
		$this->document->setTitle($this->language->get('tieredgifts_heading_title'));	

		$data['moduleData'] = $this->config->get('tieredgifts');

		$data['giftCategories'] = $this->{$this->callModel}->getGiftCategories();

		if($data['giftCategories']  === false){
			$this->response->redirect($this->url->link('error/not_found'));
		}

		$data['language_id'] = $this->config->get('config_language_id');

		foreach ($data['giftCategories'] as $key => &$gift_categories) {

		    $arr = array();

			$gift_categories['CartValueSign'] = $this->currency->format($gift_categories['CartValue'], $this->session->data['currency']);

			$spendMore = $gift_categories['CartValue'] - $this->{$this->callModel}->getTotalsWithGiftPrices();

			$gift_categories['spendMore'] = $this->currency->format($spendMore, $this->session->data['currency']);

			if($spendMore <= 0) $gift_categories['spendMore'] = false;

			$gift_categories['Suitable'] =  str_replace('{category_value}', $gift_categories['CartValueSign'], $gift_categories['Suitable'][$data['language_id']]);
			$gift_categories['NotSuitable'] = str_replace('{category_value}', $gift_categories['CartValueSign'], $gift_categories['NotSuitable'][$data['language_id']]);
			$gift_categories['NotSuitable'] = str_replace('{spend_remaining}', $gift_categories['spendMore'], $gift_categories['NotSuitable']);

			if(isset($gift_categories['GiftProducts'])){
				foreach ($gift_categories['GiftProducts'] as $key => $value) {
					$product_info = $this->model_catalog_product->getProduct($value);
					
					if($product_info['quantity'] <= 0){
						  continue;
					}

					if ($product_info['image']) {
							$image = $this->model_tool_image->resize($product_info['image'], $data['moduleData']['ImageWidth'], $data['moduleData']['ImageHeight']); //TODO
						} else {
							$image = false;
						}
						  
						if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
							$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$price = false;
						}

						if ((float)$product_info['special']) {
							$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);				
						} else {
							$special = false;
						}
						
						$product_options = $this->model_catalog_product->getProductOptions($product_info['product_id']);
						$productOptions = !empty($product_options) ? true : false;
							$arr[$value] = array(
							'product_id'  => $product_info['product_id'],
							'thumb'       => $image,
							'name'        => $product_info['name'],
							'description' => utf8_substr(trim(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
							'price'       => $price,
							'special'     => $special,
							'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
							'productOptions' =>  $productOptions
						);
						
				}
				$gift_categories['GiftProducts'] = $arr;
			} else {
				$gift_categories['GiftProducts'] = array();
			}

			if(empty($gift_categories['GiftProducts'])){
				$gift_categories['GiftProducts'] = array();
			}
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['cart_total'] = $this->cart->getTotal();

		$data['giftAlreadyAdded'] = $this->{$this->callModel}->getGiftProduct($this->cart->getProducts());

		$data['button_gift'] = $this->language->get('tieredgifts_button_gift');
		$data['button_skip'] = $this->language->get('tieredgifts_button_skip');

		if($data['giftAlreadyAdded']) {
			$data['button_gift'] = $this->language->get('tieredgifts_fancybox_change_gift_button');
		} 

		$data['heading_title'] = $this->language->get('tieredgifts_heading_title');

		$data['h1_title'] = $this->language->get('tieredgifts_h1_title');

		$data['breadcrumbs']						= array();
		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),			
			'separator' => false
		);
		$data['breadcrumbs'][] 	= array(
			'text' => $this->language->get('tieredgifts_text_breadcrumb_cart'),
			'href' => $this->url->link('checkout/cart')
		);
		$data['breadcrumbs'][]	= array(
			'text'      => $this->language->get('tieredgifts_text_breadcrumb'),
			'href'      => $this->url->link($this->modulePath . '/listing'),
			'separator' => $this->language->get('tieredgifts_text_separator')
		);

		$data['buttons'] = false;
		if(isset($this->request->get['button'])){
			$data['buttons'] = true;
			$this->session->data['giftpage'] = true;
		}

		$data['changeGiftLink'] = $this->url->link($this->modulePath . '/changeGift', '', true);

		$data['cart'] = $this->url->link('checkout/cart');

		$data['checkout'] = $this->url->link('checkout/checkout', 'skip=true', true);
		
		$data['column_left']				= $this->load->controller('common/column_left');
		$data['column_right']				= $this->load->controller('common/column_right');
		$data['content_top']				= $this->load->controller('common/content_top');
		$data['content_bottom']				= $this->load->controller('common/content_bottom');
		$data['footer']						= $this->load->controller('common/footer');
		$data['header']						= $this->load->controller('common/header');

		
		$this->response->setOutput($this->load->view($this->modulePath.'_listing', $data));
	}

	public function modifyGiftsPriceAndShowNotification(&$route, &$data, &$template) {

		$this->load->language($this->modulePath);
		$this->load->model($this->modulePath);

		$module_settings = $this->config->get('tieredgifts');

		 if($module_settings['Enabled'] == 'yes') {

		 	// Reset
		 	if ($route == 'checkout/cart') {
				unset($this->session->data['giftpage']);
				unset($this->session->data['giftCheckoutVisit']);
		 	}

		 	 $product = $this->{$this->callModel}->getGiftProduct($this->cart->getProducts());
		 	 if($product) {
                 if(array_key_exists('products',$data)) {
                     foreach ($data['products'] as $key => &$cart_product) {
                         if ($product['cart_id'] == $cart_product['cart_id']) {
                             $cart_product['name'] .= $this->language->get('tieredgifts_product_name_gift');
                             $cart_product['price'] = '<span style="text-decoration:line-through;">' . $cart_product['price'] . '</span> ' . $this->language->get('tieredgifts_free_text');
                             if ($cart_product['quantity'] == 1) {
                                 $cart_product['total'] = '<span style="text-decoration:line-through;">' . $cart_product['total'] . '</span> ' . $this->language->get('tieredgifts_free_text');
                             }
                         }
                     }
                 }
		 	 } else {

		 	 	$old_success_message = '';
		 	 	if(isset($data['success']) && !empty($data['success'])) {
		 	 		$old_success_message = '<button type="button" class="close" data-dismiss="alert">&times;</button></div><div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' . $data['success'];
		 	 	}

		 	 	if ($this->{$this->callModel}->spendMoreForGift()) {
					$data['success'] = sprintf($this->language->get('tieredgifts_spend_more_notification'), $this->{$this->config->get('tieredgifts_callModel')}->spendMoreForGift()) . ' ' . $old_success_message;
				} elseif($this->{$this->callModel}->isSuitableForGift()) {
					$data['success'] = sprintf($this->language->get('tieredgifts_eligible_for_gift'), $this->url->link($this->config->get('tieredgifts_modulePath').'/listing', 'button=true', 'SSL')) . ' ' . $old_success_message;
				}
		 	 }
		 }
	}

	public function redirectToListingIfNeeded(&$route, &$data, &$template) {

		$module_settings = $this->config->get('tieredgifts');
		$this->load->model($this->modulePath);

		if (empty($this->session->data['giftCheckoutVisit'])) {
		if  (isset($module_settings) && $module_settings['Enabled']== 'yes') {

			if($module_settings['BeforeCheckout'] == 'yes' && !isset($this->request->get['skip']) && $this->{$this->callModel}->getGiftProduct($this->cart->getProducts()) === false ){
				 $this->response->redirect($this->url->link($this->config->get('tieredgifts_modulePath').'/listing', 'button=true', 'SSL'));
			} else {
              if($this->{$this->callModel}->isSuitableForGift() && !isset($this->session->data['giftpage'])) {
                  $this->response->redirect($this->url->link($this->config->get('tieredgifts_modulePath').'/listing', 'button=true', 'SSL'));
              }
			}
			unset($this->session->data['giftpage']);

			array_splice($data['breadcrumbs'], 2, 0, array(
				array(
					'text'      => $this->language->get('tieredgifts_text_breadcrumb'),
					'href'      => $this->url->link($this->config->get('tieredgifts_modulePath').'/listing', 'button=true'),
					'separator' => $this->language->get('tieredgifts_text_separator')
				)
			));
		}
		}

		// Once customer visit checkout, do not force to Gift Listing. Reset on cart page.
		$this->session->data['giftCheckoutVisit'] = true;

	}

	/*
    * Event for SEO URLs (1/2)
    * catalog/asterisk/before
    */
     public function customUrlFunctionality($eventRoute) {

			 	$module_settings = $this->config->get('tieredgifts');

        $this->event->unregister("*/before", $this->modulePath . "/customUrlFunctionality");

        if ($this->config->get('config_seo_url')) {
			$this->url->addRewrite($this);
		}

      $this->event->register('controller/error/not_found/before', new Action($this->modulePath . "/seoUrlHandler"));
    }

		public function seoUrlHandler(){
			
			if (isset($this->request->get['_route_'])) {
					$module_settings = $this->config->get('tieredgifts');
					$parts = explode('/', $this->request->get['_route_']);

					$parts = array_filter($parts);

					if($module_settings['Enabled'] == 'yes' && isset($module_settings['SEOURL'])) {
					if (count($parts) == 1 && ($parts[0] == $module_settings['SEOURL'])) {

									$this->request->get['route'] = $this->config->get('tieredgifts_modulePath') . '/listing';

								 return new Action($this->request->get['route']);
							}
					}
			}
		}
    
    /*
    * Event for SEO URLs (1/2)
    * catalog/asterisk/before
    */
    public function rewrite($link) {

    	$module_settings = $this->config->get('tieredgifts');

        $url_info = parse_url(str_replace('&amp;', '&', $link));

        $url = '';

		$data = array();

        if (!empty($url_info['query'])) {
            parse_str($url_info['query'], $data);

            if (isset($data['route']) && $data['route'] == $this->config->get('tieredgifts_modulePath').'/listing') {
                $url .= '/'. $module_settings['SEOURL'];
            }
            
            if ($url) {
                unset($data['route']);

                $query = '';

                if ($data) {
                    foreach ($data as $key => $value) {
                        $query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode((is_array($value) ? http_build_query($value) : (string)$value));
                    }

                    if ($query) {
                        $query = '?' . str_replace('&', '&amp;', trim($query, '&'));
                    }
                }

                return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url . $query;
            } else {
                return $link;
            }
        } else {
            return $link;
        }
    }

    /**
     * Listen trigger: catalog/view/common/menu/before
     */
	public function modifyMenuLink(&$route, &$data, &$template)
	{
		$module_settings = $this->config->get('tieredgifts');

		if (isset($module_settings) && $module_settings['Enabled']== 'yes' && $module_settings['MainMenuLink']== 'yes') {
			array_splice($data['categories'], ($module_settings['SortOrder'] - 1), 0, array(
				array(
					'name'		=> $module_settings['MenuName'][$this->config->get('config_language_id')],
					'href'		=> $this->url->link($this->config->get('tieredgifts_modulePath').'/listing', '', 'SSL'),
					'column'	=> 1,
					'children'	=> array()
				)
			));
		}
	}
}
