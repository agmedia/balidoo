<?php
class ControllerExtensionModuleSeoStructuredData extends Controller {
	
	/*
	* Event for catalog/view/common/header/before
	* Used for SEO Structured Data
	*/
	public function seoStructuredDataPrepare($eventRoute, &$data) {
		
		if(!$this->config->get('module_seo_structured_data_status')){
			return;
		}
		
		$route = !empty($this->request->get['route']) ? $this->request->get['route'] : '';
		
		$width = 200;
		$height = 200;

		if($this->config->get('module_seo_structured_data_width')) {
			$width = $this->config->get('module_seo_structured_data_width');
		}

		if($this->config->get('module_seo_structured_data_height')) {
			$height = $this->config->get('module_seo_structured_data_height');
		}

		$seo_tags = array();

		// Home
		if ($route == 'common/home' || $route === '') {

			if ($this->request->server['HTTPS']) {
				$store_url = $this->config->get('config_ssl');
			} else {
				$store_url = $this->config->get('config_url');
			}

			if (is_file(DIR_IMAGE . $this->config->get('config_image')) && !empty($store_url)) {
				$store_image = $store_url . 'image/' . $this->config->get('config_image');
			}

			// twitter
			$seo_tags["twitter:card"] = array(
				"type" => "name",
				"content" => "summary"
			);

			$seo_tags["twitter:title"] = array(
				"type" => "name",
				"content" => $this->config->get('config_meta_title')
			);

			$seo_tags["twitter:domain"] = array(
				"type" => "name",
				"content" => $store_url
			);
			
			
			if (!empty($this->config->get('config_meta_description'))) {
				$seo_tags["twitter:description"] = array(
					"type" => "name",
					"content" => $this->config->get('config_meta_description')
				);
			}
			
			if (!empty($store_image)) {
				$seo_tags["twitter:image"] = array(
					"type" => "name",
					"content" => $store_image
				);
			}

			// facebook/opengraph
			$seo_tags["og:type"] = array(
				"type" => "property",
				"content" => "product"
			);

			$seo_tags["og:title"] = array(
				"type" => "property",
				"content" => $this->config->get('config_meta_title')
			);
			
			$seo_tags["og:url"] = array(
				"type" => "property",
				"content" => $store_url
			);

			if (!empty($this->config->get('config_meta_description'))) {
				$seo_tags["og:description"] = array(
					"type" => "property",
					"content" => $this->config->get('config_meta_description')
				);
			}

			if (!empty($store_image)) {
				$seo_tags["og:image"] = array(
					"type" => "property",
					"content" => $store_image
				);

				$seo_tags["og:image:url"] = array(
					"type" => "property",
					"content" => $store_image
				);
			}
		}

		// Product SEO Data
		else if ($route == 'product/product') {
			$product_id = !empty($this->request->get['product_id']) ? $this->request->get['product_id'] : 0;
			if ($product_id) {
				$this->load->model('tool/image');
				$this->load->model('catalog/product');
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {

					$product_image = $this->model_tool_image->resize($product_info['image'], $width, $height);
					
					// twitter
					$seo_tags["twitter:card"] = array(
						"type" => "name",
						"content" => "summary"
					);

					$seo_tags["twitter:title"] = array(
						"type" => "name",
						"content" => $product_info['name']
					);

					$seo_tags["twitter:domain"] = array(
						"type" => "name",
						"content" => $this->url->link('product/product', 'product_id=' . $product_info['product_id'], 'SSL')
					);
					
					
					if (!empty($product_info['meta_description'])) {
						$seo_tags["twitter:description"] = array(
							"type" => "name",
							"content" => $product_info['meta_description']
						);
					}
					
					if ($product_image) {
						$seo_tags["twitter:image"] = array(
							"type" => "name",
							"content" => $product_image
						);

						$seo_tags["twitter:image:width"] = array(
							"type" => "property",
							"content" => $width
						);

						$seo_tags["twitter:image:height"] = array(
							"type" => "property",
							"content" => $height
						);
					}

					// facebook/opengraph
					$seo_tags["og:type"] = array(
						"type" => "property",
						"content" => "product"
					);

					$seo_tags["og:title"] = array(
						"type" => "property",
						"content" => $product_info['name']
					);
					
					$seo_tags["og:url"] = array(
						"type" => "property",
						"content" => $this->url->link('product/product', 'product_id=' . $product_info['product_id'], 'SSL')
					);

					if (!empty($product_info['meta_description'])) {
						$seo_tags["og:description"] = array(
							"type" => "property",
							"content" => $product_info['meta_description']
						);
					}

					if ($product_image) {
						$seo_tags["og:image"] = array(
							"type" => "property",
							"content" => $product_image
						);

						$seo_tags["og:image:url"] = array(
							"type" => "property",
							"content" => $product_image
						);

						$seo_tags["og:image:width"] = array(
							"type" => "property",
							"content" => $width
						);

						$seo_tags["og:image:height"] = array(
							"type" => "property",
							"content" => $height
						);
					}
				}
			}
		}

		// Category SEO Data
		else if ($route == 'product/category') {
			$category_path = !empty($this->request->get['path']) ? $this->request->get['path'] : 0;
			$category_id = 0;

			if ($category_path) {
				$parts = explode('_', (string)$this->request->get['path']);
				$category_id = end($parts); 
			}
		
			if ($category_id) {
				$this->load->model('tool/image');
				$this->load->model('catalog/category');
				$category_info = $this->model_catalog_category->getCategory($category_id);

				if ($category_info) {

					$category_image = $this->model_tool_image->resize($category_info['image'], $width, $height);

					// twitter
					$seo_tags["twitter:card"] = array(
						"type" => "name",
						"content" => "summary"
					);

					$seo_tags["twitter:title"] = array(
						"type" => "name",
						"content" => $category_info['name']
					);

					$seo_tags["twitter:domain"] = array(
						"type" => "name",
						"content" => $this->url->link('product/category', 'path=' . $category_info['category_id'], 'SSL')
					);
					
					if(!empty($category_info['meta_description'])) {
						$seo_tags["twitter:description"] = array(
							"type" => "name",
							"content" => $category_info['meta_description']
						);
					}
					
					if ($category_image) {
						$seo_tags["twitter:image"] = array(
							"type" => "name",
							"content" => $category_image
						);

						$seo_tags["twitter:image:width"] = array(
							"type" => "property",
							"content" => $width
						);

						$seo_tags["twitter:image:height"] = array(
							"type" => "property",
							"content" => $height
						);
					}

					// facebook/opengraph
					$seo_tags["og:type"] = array(
						"type" => "property",
						"content" => "product"
					);

					$seo_tags["og:title"] = array(
						"type" => "property",
						"content" => $category_info['name']
					);
					
					$seo_tags["og:url"] = array(
						"type" => "property",
						"content" => $this->url->link('product/category', 'path=' . $category_info['category_id'], 'SSL')
					);

					if(!empty($category_info['meta_description'])) {
						$seo_tags["og:description"] = array(
							"type" => "property",
							"content" => $category_info['meta_description']
						);
					}

					if ($category_image) {
						$seo_tags["og:image"] = array(
							"type" => "property",
							"content" => $category_image
						);

						$seo_tags["og:image:url"] = array(
							"type" => "property",
							"content" => $category_image
						);

						$seo_tags["og:image:width"] = array(
							"type" => "property",
							"content" => $width
						);

						$seo_tags["og:image:height"] = array(
							"type" => "property",
							"content" => $height
						);
					}
				}
			}
		}


		// Manufacturer SEO Data
		else if ($route == 'product/manufacturer/info') {
			$manufacturer_id = !empty($this->request->get['manufacturer_id']) ? $this->request->get['manufacturer_id'] : 0;
			if ($manufacturer_id) {
				$this->load->model('tool/image');
				$this->load->model('catalog/manufacturer');
				$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);

				if ($manufacturer_info) {

					$manufacturer_image = $this->model_tool_image->resize($manufacturer_info['image'], $width, $height);
					
					// twitter
					$seo_tags["twitter:card"] = array(
						"type" => "name",
						"content" => "summary"
					);

					$seo_tags["twitter:title"] = array(
						"type" => "name",
						"content" => $manufacturer_info['name']
					);

					$seo_tags["twitter:domain"] = array(
						"type" => "name",
						"content" => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id'], 'SSL')
					);
					
					if(!empty($manufacturer_info['meta_description'])) {
						$seo_tags["twitter:description"] = array(
							"type" => "name",
							"content" => $manufacturer_info['meta_description']
						);
					}
					
					if ($manufacturer_image) {
						$seo_tags["twitter:image"] = array(
							"type" => "name",
							"content" => $manufacturer_image
						);

						$seo_tags["twitter:image:width"] = array(
							"type" => "property",
							"content" => $width
						);

						$seo_tags["twitter:image:height"] = array(
							"type" => "property",
							"content" => $height
						);
					}

					// facebook/opengraph
					$seo_tags["og:type"] = array(
						"type" => "property",
						"content" => "product"
					);

					$seo_tags["og:title"] = array(
						"type" => "property",
						"content" => $manufacturer_info['name']
					);
					
					$seo_tags["og:url"] = array(
						"type" => "property",
						"content" => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id'], 'SSL')
					);

					if(!empty($manufacturer_info['meta_description'])) {
						$seo_tags["og:description"] = array(
							"type" => "property",
							"content" => $manufacturer_info['meta_description']
						);
					}

					if ($manufacturer_image) {
						$seo_tags["og:image"] = array(
							"type" => "property",
							"content" => $manufacturer_image
						);

						$seo_tags["og:image:url"] = array(
							"type" => "property",
							"content" => $manufacturer_image
						);

						$seo_tags["og:image:width"] = array(
							"type" => "property",
							"content" => $width
						);

						$seo_tags["og:image:height"] = array(
							"type" => "property",
							"content" => $height
						);
					}
				}
			}
		}

			if(!empty($seo_tags)) {
			if ($this->config->get('module_seo_structured_data_fb_app_id')) {
				$seo_tags["fb:app_id"] = array(
					"type" => "property",
					"content" => $this->config->get('module_seo_structured_data_fb_app_id')
				);
			}

			if($this->config->get('module_seo_structured_data_twitter_creator')) {
				$seo_tags["twitter:creator"] = array(
					"type" => "name",
					"content" => "@" . $this->config->get('module_seo_structured_data_twitter_creator')
				);
				
				$seo_tags["twitter:site"] = array(
					"type" => "name",
					"content" => "@" . $this->config->get('module_seo_structured_data_twitter_creator')
				);
			}
		}
		
		$data['seo_structured_data'] = $seo_tags;
	}

	/*
	* Event for catalog/view/common/header/after
	* Used for SEO Structured Data
	*/
	public function seoStructuredDataRender($eventRoute, &$data, &$output) {

		if(!$this->config->get('module_seo_structured_data_status')){
			return;
		}

		if (!empty($data['seo_structured_data'])) {
			$content = "";
			foreach($data['seo_structured_data'] as $k=>$v) {
				$content .= '<meta ' . $v['type'] . '="' . $k .  '" content="' . trim($v['content']) . '" />' . PHP_EOL;
			}
			$output = str_replace('</head>', $content . PHP_EOL . '</head>', $output);
		}
	}
}