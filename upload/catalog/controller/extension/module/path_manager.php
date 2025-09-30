<?php
/*
This file is part of "Path Manager - Breadcrumbs" project and subject to the terms
and conditions defined in file "EULA.txt", which is part of this source
code package and also available on the project page: https://git.io/JfeVl.
*/

class ControllerExtensionModulePathManager extends Controller {

	// catalog/view/*/before
	public function updateBreadcrumbs(&$route, &$data) {
		if (isset($data['breadcrumbs']) && is_array($data['breadcrumbs'])) {
			if ($this->config->get('module_path_manager_status')) {
				$module_config = $this->config->get('module_path_manager');
				$breadcrumb_options = isset($module_config['breadcrumbs']) ? $module_config['breadcrumbs'] : array();

				if (!empty($breadcrumb_options) && isset($this->request->get['product_id'])) {
					$continue = true;

					if (isset($breadcrumb_options['path']) && $breadcrumb_options['path'] !== 'default') {
						// ok
					} else {
						$continue = false;
					}

					if ($continue && empty($breadcrumb_options['force']) && isset($this->request->get['path'])) {
						$continue = false;
					}

					if ($continue && empty($breadcrumb_options['search']) &&
						(isset($this->request->get['search']) || isset($this->request->get['tag']))
					) {
						$continue = false;
					}

					if ($continue && empty($breadcrumb_options['manufacturer']) && isset($this->request->get['manufacturer_id'])) {
						$continue = false;
					}

					if ($continue) {
						if (isset($breadcrumb_options['level']) && isset($breadcrumb_options['level_negative'])) {
							$breadcrumb_options['level'] = $breadcrumb_options['level_negative']
								? -1 * (int)$breadcrumb_options['level']
								: (int)$breadcrumb_options['level'];
						} else {
							$breadcrumb_options['level'] = 0;
						}

						$data['breadcrumbs'] = $this->getProductBreadcrumbs(
							$breadcrumb_options['path'], $this->request->get, (int)$breadcrumb_options['level']
						);
					}
				}

				// restore full category path even if there is only last id
				if (isset($this->request->get['path']) && !isset($this->request->get['product_id'])) {
					$data['breadcrumbs'] = $this->getCategoryBreadcrumbs($this->request->get);
				}

				if (!empty($breadcrumb_options['json']) && method_exists($this->document, 'addCustomScript')) {
					$data['json_breadcrumbs'] = $this->getBreadcrumbsJson($data['breadcrumbs']);
					$this->document->addCustomScript('header', $route, $data['json_breadcrumbs'], 'application/ld+json');
				}

				if (!empty($breadcrumb_options['nolink'])) {
					$data['breadcrumbs'] = $this->removeLastBreadcrumb($data['breadcrumbs']);
				}
			}
		}
	}

	// catalog/controller/common/header/before
	public function styleBreadcrumbs(&$route, &$data) {
		if ($this->config->get('module_path_manager_status')) {
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/stylesheet/stylesheet.css')) {
				$dir_stylesheet = 'catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/';
			} else {
				$dir_stylesheet = 'catalog/view/theme/default/stylesheet/';
			}

			$module_config = $this->config->get('module_path_manager');
			$breadcrumb_options = isset($module_config['breadcrumbs']) ? $module_config['breadcrumbs'] : array();

			if (!empty($breadcrumb_options['bold'])) {
				$css_bold = $dir_stylesheet . 'breadcrumbs_bold.css';
				if (file_exists($css_bold)) {
					$this->document->addStyle($css_bold);
				}
			}

			if (!empty($breadcrumb_options['nolink'])) {
				$css_nolink = $dir_stylesheet . 'breadcrumbs_nolink.css';
				if (file_exists($css_nolink)) {
					$this->document->addStyle($css_nolink);
				}
			}
		}
	}

	// catalog/view/*/after
	public function addJsonLdScript(&$route, &$data, &$template) {
		if ($this->config->get('module_path_manager_status')) {
			$module_config = $this->config->get('module_path_manager');
			$breadcrumb_options = isset($module_config['breadcrumbs']) ? $module_config['breadcrumbs'] : array();

			if (!empty($breadcrumb_options['json']) && method_exists($this->document, 'getCustomScripts')) {
				$scripts = '';

				if ($this->document->getCustomScripts('header')) {
					foreach ($this->document->getCustomScripts('header') as $script) {
						if ($script['name'] == $route) {
							$scripts .= '<script type="' . $script['type'] . '">' . "\n" .
								$script['script'] . "\n" .
								'</script>' . "\n";
						}
					}
				}

				if ($scripts) {
					$template = preg_replace('/<\/head>/', $scripts . '</head>', $template);
				}
			}
		}
	}

	private function getCategoryBreadcrumbs($get = array()) {
		$breadcrumbs = array();

		$breadcrumbs[] = array(
			'text' => '<i class="fa fa-home"></i> Početna stranica',
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['path'])) {
			$url = '';

			if (isset($get['sort']))  { $url .= '&sort='  . $get['sort']; }
			if (isset($get['order'])) { $url .= '&order=' . $get['order']; }
			if (isset($get['limit'])) { $url .= '&limit=' . $get['limit']; }

			$parts = explode('_', (string)$get['path']);
			$category_id = (int)array_pop($parts);

			$this->load->model('extension/module/path_manager');
			$this->load->model('catalog/category');

			$path = '';

			// --- Balidoo inject (category): pripremi puni path i ubaci čvor odmah iza Home ---
			$full_path_rows = $this->model_extension_module_path_manager->getCategoryPath($category_id);
			$path_ids_full = array();
			foreach ($full_path_rows as $row) { $path_ids_full[] = (int)$row['path_id']; }
			$this->addBalidooBreadcrumb($breadcrumbs, $path_ids_full);
			// --- /Balidoo inject ---

			foreach ($this->model_extension_module_path_manager->getCategoryPath($category_id) as $category) {
				if (!$path) {
					$path = $category['path_id'];
				} else {
					$path .= '_' . $category['path_id'];
				}

				$category_info = $this->model_catalog_category->getCategory($category['path_id']);

				if ($category_info) {
					$breadcrumbs[] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path . $url)
					);
				}
			}
		}

		return $breadcrumbs;
	}

	private function getProductBreadcrumbs($breadcrumbs_path, $get = array(), $level = 0) {
		$breadcrumbs = array();

		if (isset($get['product_id'])) {
			$product_id = (int)$get['product_id'];

			$breadcrumbs[] = array(
				'text' => '<i class="fa fa-home"></i> Početna stranica',
				'href' => $this->url->link('common/home')
			);

			$this->load->model('catalog/product');
			$product_info = $this->model_catalog_product->getProduct($product_id);

			if (isset($get['search']) || isset($get['tag'])) {
				$url = '';
				if (isset($get['search']))       { $url .= '&search=' . $get['search']; }
				if (isset($get['tag']))          { $url .= '&tag=' . $get['tag']; }
				if (isset($get['description']))  { $url .= '&description=' . $get['description']; }
				if (isset($get['category_id']))  { $url .= '&category_id=' . $get['category_id']; }
				if (isset($get['sub_category'])) { $url .= '&sub_category=' . $get['sub_category']; }
				if (isset($get['sort']))         { $url .= '&sort=' . $get['sort']; }
				if (isset($get['order']))        { $url .= '&order=' . $get['order']; }
				if (isset($get['page']))         { $url .= '&page=' . $get['page']; }
				if (isset($get['limit']))        { $url .= '&limit=' . $get['limit']; }

				$breadcrumbs[] = array(
					'text' => $this->language->get('text_search'),
					'href' => $this->url->link('product/search', $url)
				);
			}

			if ($breadcrumbs_path === 'direct') {
				// bez kategorijskog puta
			} elseif (in_array($breadcrumbs_path, array('short', 'long', 'last'))) {
				$url = '';
				if (isset($get['sort']))  { $url .= '&sort='  . $get['sort']; }
				if (isset($get['order'])) { $url .= '&order=' . $get['order']; }
				if (isset($get['page']))  { $url .= '&page='  . $get['page']; }
				if (isset($get['limit'])) { $url .= '&limit=' . $get['limit']; }

				$this->load->model('extension/module/path_manager');

				$linked_categories = $this->model_extension_module_path_manager->getProductLinkedCategories($product_id);

				if ($linked_categories) {
					$category_path = array();

					if ($breadcrumbs_path === 'short') {
						$category_path = reset($linked_categories);
						$category_path = $this->getCategoriesSlice($category_path, $level);
					} elseif ($breadcrumbs_path === 'long') {
						$category_path = end($linked_categories);
						$category_path = $this->getCategoriesSlice($category_path, $level);
					} elseif ($breadcrumbs_path === 'last') {
						$category_path = end($linked_categories);
						$category_path = array(end($category_path));
					}

					$this->load->model('catalog/category');

					$path = '';
					$counter = 1;

					// --- Balidoo inject (product): imamo niz ID-eva (root -> ... -> last) ---
					$path_ids_full = array_map('intval', $category_path);
					$this->addBalidooBreadcrumb($breadcrumbs, $path_ids_full);
					// --- /Balidoo inject ---

					foreach ($category_path as $category_id) {
						if (!$path) {
							$path = $category_id;
						} else {
							$path .= '_' . $category_id;
						}

						$category_info = $this->model_catalog_category->getCategory($category_id);

						if ($category_info) {
							if ($counter == count($category_path)) {
								// Last category breadcrumb
								$breadcrumbs[] = array(
									'text' => $category_info['name'],
									'href' => $this->url->link('product/category', 'path=' . $path . $url)
								);
							} else {
								$breadcrumbs[] = array(
									'text' => $category_info['name'],
									'href' => $this->url->link('product/category', 'path=' . $path)
								);
							}
						}

						$counter++;
					}
				}
			} elseif (isset($get['manufacturer_id']) || $breadcrumbs_path === 'manufacturer') {
				$url = '';
				if (isset($get['sort']))  { $url .= '&sort='  . $get['sort']; }
				if (isset($get['order'])) { $url .= '&order=' . $get['order']; }
				if (isset($get['page']))  { $url .= '&page='  . $get['page']; }
				if (isset($get['limit'])) { $url .= '&limit=' . $get['limit']; }

				$breadcrumbs[] = array(
					'text' => $this->language->get('text_brand'),
					'href' => $this->url->link('product/manufacturer')
				);

				if (isset($get['manufacturer_id'])) {
					$manufacturer_id = (int)$get['manufacturer_id'];
				} elseif (isset($product_info['manufacturer_id'])) {
					$manufacturer_id = (int)$product_info['manufacturer_id'];
				} else {
					$manufacturer_id = 0;
				}

				$this->load->model('catalog/manufacturer');
				$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);

				if ($manufacturer_info) {
					$breadcrumbs[] = array(
						'text' => $manufacturer_info['name'],
						'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_id . $url)
					);
				}
			}

			$url = '';
			if (isset($get['path']))            { $url .= '&path=' . $get['path']; }
			if (isset($get['filter']))          { $url .= '&filter=' . $get['filter']; }
			if (isset($get['manufacturer_id'])) { $url .= '&manufacturer_id=' . $get['manufacturer_id']; }
			if (isset($get['search']))          { $url .= '&search=' . $get['search']; }
			if (isset($get['tag']))             { $url .= '&tag=' . $get['tag']; }
			if (isset($get['description']))     { $url .= '&description=' . $get['description']; }
			if (isset($get['category_id']))     { $url .= '&category_id=' . $get['category_id']; }
			if (isset($get['sub_category']))    { $url .= '&sub_category=' . $get['sub_category']; }
			if (isset($get['sort']))            { $url .= '&sort=' . $get['sort']; }
			if (isset($get['order']))           { $url .= '&order=' . $get['order']; }
			if (isset($get['page']))            { $url .= '&page=' . $get['page']; }
			if (isset($get['limit']))           { $url .= '&limit=' . $get['limit']; }

			$breadcrumbs[] = array(
				'text' => isset($product_info['name']) ? $product_info['name'] : '',
				'href' => $this->url->link('product/product', '&product_id=' . $product_id . $url)
			);
		}

		return $breadcrumbs;
	}

	// Returns categories with the defined depth level
	private function getCategoriesSlice($categories = array(), $level = 0) {
		if ($level && $categories) {
			$offset = 0;
			$length = count($categories); // FIX: ispravno brojanje elemenata

			if ($level < 0 && $length >= abs($level)) {
				$offset = count($categories) + $level; // FIX: koristi $categories
				$length = abs($level);
			}

			$categories = array_slice($categories, $offset, $length);
		}

		return $categories;
	}

	private function getBreadcrumbsJson($breadcrumbs = array()) {
		if (!$breadcrumbs || !is_array($breadcrumbs)) {
			return array();
		}

		$item_list = array();
		$loop = 0;

		foreach ($breadcrumbs as $key => $breadcrumb) {
			$e = array();

			$e['@type'] = 'ListItem';
			$e['position'] = $loop;

			if (0 == $loop) {
				$e['name'] = htmlspecialchars($this->config->get('config_name'));
			} else {
				$e['name'] = htmlspecialchars($breadcrumb['text']);
			}

			$e['item'] = $breadcrumb['href'];

			$item_list[] = $e;

			$loop++;
		}

		$json = array(
			'@context'        => 'http://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $item_list
		);

		return json_encode($json);
	}

	private function removeLastBreadcrumb($breadcrumbs) {
		$count = count($breadcrumbs);

		if ($count > 1) {
			$breadcrumbs[$count - 1]['href'] = '';
		}

		return $breadcrumbs;
	}

	/**
	 * Balidoo: ubaci dodatni breadcrumb odmah iza Home, na temelju prisutnosti root ID-eva u pathu.
	 * Kids ima prioritet nad Underwear; ubacuje se točno JEDAN dodatni čvor.
	 */
	private function addBalidooBreadcrumb(&$breadcrumbs, $path_ids) {
		// PRILAGODI OVE ID-ove:
		$kids_root_ids      = array(70, 79, 80);   // Dječaci, Djevojčice, Bebe
		$underwear_root_ids = array(126, 127);     // Muškarci, Žene

		// Tekst & link (po želji preusmjeri na information/landing)
		$kids_text = 'Balidoo kids';
		$kids_href = $this->url->link('information/information', 'information_id=16');

		$underwear_text = 'Balidoo underwear';
		$underwear_href = $this->url->link('information/information', 'information_id=15');

		if (empty($breadcrumbs) || !is_array($breadcrumbs) || empty($path_ids) || !is_array($path_ids)) {
			return;
		}

		// Guard protiv duplikata
		$texts_lower = array();
		foreach ($breadcrumbs as $bc) {
			if (isset($bc['text'])) {
				$texts_lower[] = strtolower(trim(strip_tags($bc['text'])));
			}
		}

		$hit_kids  = (bool) array_intersect($path_ids, $kids_root_ids);
		$hit_under = (!$hit_kids) && (bool) array_intersect($path_ids, $underwear_root_ids);

		// Umetni točno JEDAN dodatni breadcrumb odmah iza "Home" (index 1),
		// i to samo ako već ne postoji istog teksta.
		if ($hit_kids) {
			if (!in_array(strtolower($kids_text), $texts_lower, true)) {
				array_splice($breadcrumbs, 1, 0, array(array('text' => $kids_text, 'href' => $kids_href)));
			}
		} elseif ($hit_under) {
			if (!in_array(strtolower($underwear_text), $texts_lower, true)) {
				array_splice($breadcrumbs, 1, 0, array(array('text' => $underwear_text, 'href' => $underwear_href)));
			}
		}
	}
}
