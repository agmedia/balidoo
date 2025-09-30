<?php
class ControllerInformationInformation extends Controller {
	public function index() {
		$this->load->language('information/information');
		$this->load->model('catalog/information');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$this->document->setTitle($information_info['meta_title']);
			$this->document->setDescription($information_info['meta_description']);
			$this->document->setKeywords($information_info['meta_keyword']);

			$data['breadcrumbs'][] = array(
				'text' => $information_info['title'],
				'href' => $this->url->link('information/information', 'information_id=' .  $information_id)
			);

			$data['heading_title'] = $information_info['title'];
			$data['description']   = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');
			$data['continue']      = $this->url->link('common/home');

			// =======================
			// Efektivni layout + prepoznavanje varijanti
			// =======================
			$this->load->model('design/layout');

			// 1) Polazni layout po route-u information/information
			$route     = 'information/information';
			$layout_id = (int)$this->model_design_layout->getLayout($route);

			// 2) Per-page layout iz information_to_layout (po ovoj info stranici i trenutnom storeu)
			$info_layout_id = 0;
			if (!empty($information_id)) {
				$q = $this->db->query("SELECT layout_id 
									   FROM " . DB_PREFIX . "information_to_layout 
									   WHERE information_id = " . (int)$information_id . " 
									     AND store_id = " . (int)$this->config->get('config_store_id') . " 
									   LIMIT 1");
				if ($q->num_rows) {
					$info_layout_id = (int)$q->row['layout_id'];
				}
			}
			if ($info_layout_id) {
				$layout_id = $info_layout_id;
			}

			// 3) Fallback na default layout iz konfiguracije
			if (!$layout_id) {
				$layout_id = (int)$this->config->get('config_layout_id');
			}

			// 4) Helper: dohvat layout_id po imenu iz oc_layout.name (case-insensitive)
			$getLayoutIdByName = function($name) {
				$lower = function_exists('utf8_strtolower') ? utf8_strtolower($name) : mb_strtolower($name, 'UTF-8');
				$sql = "SELECT layout_id
						FROM " . DB_PREFIX . "layout
						WHERE LOWER(name) = '" . $this->db->escape($lower) . "'
						LIMIT 1";
				$q = $this->db->query($sql);
				return $q->num_rows ? (int)$q->row['layout_id'] : 0;
			};

			// 5) ID-ovi layouta po imenima iz Admin → Design → Layouts → Name
			$home_id       = $getLayoutIdByName('Home');
			$kids_id       = $getLayoutIdByName('Balidoo Kids');
			$underwear_id  = $getLayoutIdByName('Balidoo Underwear');

			$kozo_men_id   = $getLayoutIdByName('Kozo muškarci');
			$kozo_women_id = $getLayoutIdByName('Kozo žene');

			// 6) Flagovi i sažetak za Twig
			$data['effective_layout_id']   = $layout_id;
			$data['is_layout_home']        = ($layout_id && $home_id && $layout_id === $home_id);
			$data['is_layout_kids']        = ($layout_id && $kids_id && $layout_id === $kids_id);
			$data['is_layout_underwear']   = ($layout_id && $underwear_id && $layout_id === $underwear_id);

			$data['is_layout_kozo_men']   = ($layout_id && $kozo_men_id && $layout_id === $kozo_men_id);
			$data['is_layout_kozo_women'] = ($layout_id && $kozo_women_id && $layout_id === $kozo_women_id);


			if     ($data['is_layout_kids'])      { $data['home_variant'] = 'kids'; }
			elseif ($data['is_layout_underwear']) { $data['home_variant'] = 'underwear'; }
			elseif ($data['is_layout_kozo_men'])    { $data['home_variant'] = 'kozo_men'; }
			elseif ($data['is_layout_kozo_women'])  { $data['home_variant'] = 'kozo_women'; }
			elseif ($data['is_layout_home'])      { $data['home_variant'] = 'main'; }
			else                                   { $data['home_variant'] = 'other'; }

			$data['uses_any_home'] = ($data['is_layout_home'] || $data['is_layout_kids'] || $data['is_layout_underwear']);

			// (Opcionalno) Debug podatci za brzu provjeru u Twigu
			$data['__debug'] = array(
				'effective_layout_id' => (int)$layout_id,
				'home_id'             => (int)$home_id,
				'kids_id'             => (int)$kids_id,
				'underwear_id'        => (int)$underwear_id
			);

			// =======================
			// Standardni layout blokovi
			// =======================
			$data['column_left']    = $this->load->controller('common/column_left');
			$data['column_right']   = $this->load->controller('common/column_right');
			$data['content_top']    = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer']         = $this->load->controller('common/footer');
			$data['header']         = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('information/information', $data));
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('information/information', 'information_id=' . $information_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');
			$data['text_error']    = $this->language->get('text_error');
			$data['continue']      = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left']    = $this->load->controller('common/column_left');
			$data['column_right']   = $this->load->controller('common/column_right');
			$data['content_top']    = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer']         = $this->load->controller('common/footer');
			$data['header']         = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function agree() {
		$this->load->model('catalog/information');

		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		$output = '';

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$output .= html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8') . "\n";
		}

		$this->response->setOutput($output);
	}
}
