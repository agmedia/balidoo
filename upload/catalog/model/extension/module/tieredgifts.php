<?php
class ModelExtensionModuletieredgifts extends Model {

	public function getGiftProduct($cartItems) {

		$tieredgifts = $this->getGiftCategories();

		$cartTotal = $this->getCartTotal();

		$setting = $this->config->get('tieredgifts');

		$GiftProducts = array();
		$giftsInCart = array();

		/*foreach ($tieredgifts as $gift) {
			if(isset($gift['GiftProducts'])){
				foreach ($gift['GiftProducts'] as $giftProduct) {
					$GiftProducts[] = $giftProduct;
				}
			}
		}

		foreach ($cartItems as $productInCart) {
			if(in_array($productInCart['product_id'], $GiftProducts)){
				if($setting['TaxCalc'] == 'yes') {
					$giftsInCart[$productInCart['product_id']] = $this->tax->calculate($productInCart['price'], $productInCart['tax_class_id'], $this->config->get('config_tax'));
				} else {
					$giftsInCart[$productInCart['product_id']] = $productInCart['price'];
				}
			}
		}

		foreach ($tieredgifts as $gift) {
		foreach ($giftsInCart as $id => $price) {
			if($gift['CartValue'] <= $cartTotal - $price){
				if(in_array($id, $gift['GiftProducts'])) {
					foreach ($cartItems as $key => $cart_product) {
						if((int)$cart_product['product_id'] == $id){
							$cart_product['price'] = $giftsInCart[$id];
							return $cart_product;
						}
					}
				}
			}
		}*/
		return false;
	}


	public function getCartTotal() {

		$setting = $this->config->get('tieredgifts');

		if($setting['TaxCalc'] == 'yes'){
			$cartTotal = $this->cart->getTotal();
		} else {
			$cartTotal = $this->cart->getSubTotal();
		}

		return $cartTotal;
	}

	public function getGiftCategories() {
		$setting = $this->config->get('tieredgifts');

		if($setting['Enabled'] != 'yes') return false;

		$tieredgifts = isset($setting['GiftCategory']) ? $setting['GiftCategory'] : array();

		usort($tieredgifts, function($a, $b) {
		    return $a['CartValue'] - $b['CartValue'];
		});

		foreach ($tieredgifts as $key => &$giftCategory) {
			if($giftCategory['Enabled'] == 'no') {
				unset($giftCategory);
			}
		}

		return $tieredgifts;
	}

	public function spendMoreForGift() {

		$tieredgifts = $this->getGiftCategories();

		$cartTotal = $this->getCartTotal();

		reset($tieredgifts);

		if(isset($tieredgifts[0]['CartValue'])) {
			if($tieredgifts[0]['CartValue'] > $cartTotal ) {
				return $this->currency->format($tieredgifts[0]['CartValue'] - $cartTotal, $this->session->data['currency']);
			}
		}

		return false;

	}

	public function getTotalsWithGiftPrices() {

		$setting = $this->config->get('tieredgifts');

		$tieredgifts = $this->getGiftCategories();

		$cartTotal = $this->getCartTotal();

		$GiftProducts = array();
		$giftsInCart = array();

		foreach ($tieredgifts as $gift) {
			if(isset($gift['GiftProducts'])){
				foreach ($gift['GiftProducts'] as $giftProduct) {
					$GiftProducts[] = $giftProduct;
				}
			}
		}

		foreach ($this->cart->getProducts() as $productInCart) {
			if(in_array($productInCart['product_id'], $GiftProducts)){
				if($setting['TaxCalc'] == 'yes') {
					$giftsInCart[$productInCart['product_id']] = $this->tax->calculate($productInCart['price'], $productInCart['tax_class_id'], $this->config->get('config_tax'));
				} else {
					$giftsInCart[$productInCart['product_id']] = $productInCart['price'];
				}
			}
		}

		foreach ($tieredgifts as $gift) {
			foreach ($giftsInCart as $id => $price) {
				if($gift['CartValue'] <= $cartTotal - $price){
					return $cartTotal - $price;
				}
			}
		}

		return $cartTotal;
	}

	public function isSuitableForGift() {
		$setting = $this->config->get('tieredgifts');

		$tieredgifts = $this->getGiftCategories();

		$cartTotal = $this->getCartTotal();

		$GiftProducts = array();
		$giftsInCart = array();

		foreach ($tieredgifts as $gift) {
			if(isset($gift['GiftProducts'])){
				foreach ($gift['GiftProducts'] as $giftProduct) {
					$GiftProducts[] = $giftProduct;
				}
			}
		}

		foreach ($this->cart->getProducts() as $productInCart) {
			if(in_array($productInCart['product_id'], $GiftProducts)){
				if($setting['TaxCalc'] == 'yes') {
					$giftsInCart[$productInCart['product_id']] = $this->tax->calculate($productInCart['price'], $productInCart['tax_class_id'], $this->config->get('config_tax'));
				} else {
					$giftsInCart[$productInCart['product_id']] = $productInCart['price'];
				}
			}
		}

		$suitable = false;
		foreach ($tieredgifts as $gift) {
			if($gift['CartValue'] <= $cartTotal){
				$suitable = true;
			}
		}
		return $suitable;
	}

	/*public function isSuitableForGift($returnQuery = 'suitable'){
		$setting = $this->config->get('tieredgifts');

		if($setting['Enabled'] != 'yes') return false;

		$tieredgifts = isset($setting['GiftCategory']) ? $setting['GiftCategory'] : array();

		usort($tieredgifts, function($a, $b) {
		    return $b['CartValue'] - $a['CartValue'];
		});

		//var_dump($tieredgifts);exit;

		$cartProducts = $this->cart->getProducts();
		if($setting['TaxCalc'] == 'yes'){
			$cartTotal = $this->cart->getTotal();
		} else {
			$cartTotal = $this->cart->getSubTotal();
		}

		$grandTotal = 0;
		$GiftProducts = array();

		$realTotal = 0;
		$giftsInCart = array();
		$giftsPrices = array();
		$count = 0;

		foreach ($tieredgifts as $gift) {
			if(isset($gift['GiftProducts'])){
				foreach ($gift['GiftProducts'] as $giftProduct) {
					$GiftProducts[] = $giftProduct;
				}
			}
		}

		foreach ($cartProducts as $productInCart) {
			if(in_array($productInCart['product_id'], $GiftProducts)){
				if($setting['TaxCalc'] == 'yes'){
					$giftsInCart[$productInCart['product_id']] = $this->tax->calculate($productInCart['price'], $productInCart['tax_class_id'], $this->config->get('config_tax'));
				} else {
					$giftsInCart[$productInCart['product_id']] = $productInCart['price'];
				}
			} else {
				$realTotal += $productInCart['quantity']*$this->tax->calculate($productInCart['price'], $productInCart['tax_class_id'], $this->config->get('config_tax'));
			}

		}

		arsort($giftsInCart);

		if($returnQuery == 'getGiftProductId'){

			foreach ($tieredgifts as $gift) {

			if($count != 0) break;

				foreach ($giftsInCart as $id => $price) {
					if($gift['CartValue'] <= $cartTotal - $price && $count == 0){
						if(in_array($id, $gift['GiftProducts'])){
							$product = $cartProducts[array_search($id , array_column1($cartProducts, 'product_id'))];
							if($product['quantity'] > 1) return -$product['product_id'];
							return $product['product_id'];
						}
					}
				}

			}
			return 0;
		} else if($returnQuery == 'realTotal'){

			foreach ($tieredgifts as $gift) {
				foreach ($giftsInCart as $id => $price) {
					if($gift['CartValue'] <= $cartTotal - $price){
						return $cartTotal - $price;
					}
				}
			}
			return $cartTotal;

		} else if(!empty($giftsInCart)){
			return false;
		} else {
			$suitable = false;
			foreach ($tieredgifts as $gift) {
				//foreach ($giftsInCart as $id => $price) {
					if($gift['CartValue'] <= $cartTotal && $count == 0){
						$suitable = true;
					}
				//}
			}
			return $suitable;
		}


	}*/



/*	public function isSuitableForGift ($returnQuery = 'suitable') {
		$setting = $this->config->get('tieredgifts');
		$tieredgifts = isset($setting['GiftCategory']) ? $setting['GiftCategory'] : array();

		usort($tieredgifts, function($a, $b) {
		    return $b['CartValue'] - $a['CartValue'];
		});


		$cartProducts = $this->cart->getProducts();
		$GiftProducts = array();

		foreach ($tieredgifts as $gift) {
			if(isset($gift['GiftProducts'])){
				foreach ($gift['GiftProducts'] as $giftProduct) {
					$GiftProducts[] = $giftProduct;
				}
			}
		}

		$realTotal = 0;
		$giftsInCart = array();
		foreach ($cartProducts as $productInCart) {
			if(!in_array($productInCart['product_id'], $GiftProducts)){
				$realTotal += $productInCart['quantity']*$this->tax->calculate($productInCart['price'], $productInCart['tax_class_id'], $this->config->get('config_tax'));
			} else {
				$giftsInCart[] = $productInCart['product_id'];
			}
		}




		if($returnQuery == 'getGiftProductId'){

			$count = 0;

			foreach ($tieredgifts as $gift) {

				if($gift['CartValue'] < $realTotal){
					foreach ($cartProducts as $productInCart) {
						if(in_array($productInCart['product_id'], $giftsInCart) && in_array($productInCart['product_id'], $gift['GiftProducts']) && $count < 1){
							return $productInCart['product_id'];
						}
					}
				}
			}
			return 0;
		} else if($returnQuery == 'realTotal'){
		 	return $realTotal;
		} else if(!empty($giftsInCart)){
			return false;
		} else {
			$suitable = false;
			foreach ($tieredgifts as $gift) {
				if($gift['CartValue'] < $realTotal) $suitable = true;
			}

			return $suitable;
		}
	}

*/
}
