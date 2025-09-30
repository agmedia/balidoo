var fbecommevnt_pagename = $(document).find("title").text();
var fbecommevnt_pageurl = window.location.href;
var fbecommevntJson = '';
var fbecommevnt = {
	'trackevent': function(product_id, evename, flag) {
 		$.ajax({
			url: 'index.php?route=extension/fbecommevnt/trackevent',
			type: 'post', dataType: 'json', cache: false,
			data: { product_id: product_id },		
			success: function(jsonevent) {
				if(flag == 1) {
					fbq('track', evename, jsonevent['items']); 
				} else {
					fbq('trackCustom', evename, jsonevent['items']);
				}
 			}
		});
 	},
	'checkoutfunnel': function(stepname , jsoncart_data, flag) {
 		setTimeout(function(){
			fbq('trackCustom', stepname, jsoncart_data);
			if(flag == 1) {
				fbq('track', 'AddPaymentInfo', jsoncart_data); 
			}
		 }, 1500);
 	},
	'applyevent': function(json) {
		$("[onclick*='cart.add']").each(function() {
			product_id = $(this).attr('onclick').match(/[0-9]+/);
			func = 'fbecommevnt.trackevent('+product_id+', \'AddToCart\', 1);';
			if($(this).attr('onclick').indexOf('fbecommevnt') == -1) { 
				$(this).attr('onclick', func + $(this).attr('onclick'));
			}
		});
		$("[onclick*='wishlist.add']").each(function() {
			product_id = $(this).attr('onclick').match(/[0-9]+/);
			func = 'fbecommevnt.trackevent('+product_id+', \'AddToWishlist\', 1);';
			if($(this).attr('onclick').indexOf('fbecommevnt') == -1) { 
				$(this).attr('onclick', func + $(this).attr('onclick'));
			}
		});
		$("[onclick*='compare.add']").each(function() {
			product_id = $(this).attr('onclick').match(/[0-9]+/);
			func = 'fbecommevnt.trackevent('+product_id+', \''+json['langdata']['text_compare']+'\', 2);';
			if($(this).attr('onclick').indexOf('fbecommevnt') == -1) { 
				$(this).attr('onclick', func + $(this).attr('onclick'));
			}
		});
		$("[onclick*='cart.remove']").each(function() {
			var product_id = $(this).attr('onclick').match(/[0-9]+/);
			var func = 'fbecommevnt.trackevent('+product_id+', \''+json['langdata']['text_removecart']+'\', 2);';
			if($(this).attr('onclick').indexOf('fbecommevnt') == -1) { 
				$(this).attr('onclick', func + $(this).attr('onclick'));
			}
		});
		var product_id_page = $("input[name='product_id']").val();
		if (typeof product_id_page !== 'undefined') {
			var func = 'fbecommevnt.trackevent('+product_id_page+', \'AddToCart\', 1);';
			$('#button-cart').attr('onclick',func);
		}
	},
	'initjson': function() {
		$.ajax({
			url: 'index.php?route=extension/fbecommevnt/getcache',
			dataType: 'json',
			cache: true,
			success: function(json) {				
				fbecommevntJson = json;
  				fbecommevnt.applyevent(json);
 				$(document).ajaxStop(function(){ fbecommevnt.applyevent(json); });
 			}
		});
	}
}
$(document).ready(function() {
fbecommevnt.initjson();
});