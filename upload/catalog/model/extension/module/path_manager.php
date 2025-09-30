<?php

/*
This file is part of "Path Manager - Breadcrumbs" project and subject to the terms
and conditions defined in file "EULA.txt", which is part of this source
code package and also available on the project page: https://git.io/JfeVl.
*/

class ModelExtensionModulePathManager extends Model {
	// Returns array with product categories ids and its parents
	public function getProductCategories($product_id = 0) {
		$query = $this->db->query(
			'SELECT c.category_id, c.parent_id FROM ' . DB_PREFIX . 'product_to_category p2c LEFT JOIN ' .
			DB_PREFIX . 'category c ON (p2c.category_id = c.category_id) WHERE product_id = "' .
			(int)$product_id . '"'
		);

		return $query->rows;
	}

	// Returns id of parent category
	public function getCategoryParent($category_id) {
		$query = $this->db->query(
			'SELECT category_id, parent_id FROM ' . DB_PREFIX . 'category WHERE category_id = "' . $category_id . '"'
		);

		return $query->row['parent_id'];
	}

	// Returns ordered array of id's of parent categories, for example:
	// Array ([path_id] => 2 )
	// Array ([path_id] => 6 )
	// Array ([path_id] => 8 )
	public function getCategoryPath($category_id) {
		$query = $this->db->query('SELECT path_id FROM ' . DB_PREFIX . "category_path WHERE category_id = '" . (int)$category_id . "' ORDER BY level ASC");

		return $query->rows;
	}

	// Returns id's of all categories linked to product.
	// For examle:
	// Array ( [0] => 2 [1] => 3 )
	// Array ( [0] => 2 [1] => 3 [2] => 5 )
	// Array ( [0] => 2 [1] => 6 [2] => 18 )
	public function getProductLinkedCategories($product_id = 0) {
		$linked_cats = array();

		if ($product_id) {
			$categories = $this->getProductCategories($product_id);

			foreach ($categories as $key => $category) {
				$linked_cats[$key][] = $category['category_id'];

				while ($category['parent_id']) {
					array_unshift($linked_cats[$key], $category['parent_id']);

					$parent_id = $this->getCategoryParent($category['parent_id']);

					if ($parent_id == $category['parent_id']) {
						$this->log->write('[Path Manager - Breadcrumbs]: Recursive Category - id(' . $category['parent_id'] . ')');

						continue 2;
					}

					$category['parent_id'] = $parent_id;
				}
			}

			asort($linked_cats);
		}

		return $linked_cats;
	}
}
