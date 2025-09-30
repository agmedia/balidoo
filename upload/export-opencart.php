<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

##########################################################################################
# 
# Options
# @url_param taxes=on (on,off) 
# @url_param storetaxes=on (on,off) 
# @url_param discount=on (on,off) 
# @url_param add_vat=off (on,off) 
# @url_param vat_value=24 (VAT_VALUE) 
# @url_param shipping=off (on,off) 
# @url_param add_tagging=on (on,off) 
# @url_param tagging_params=&utm_source=nabava.net&utm_medium=cpc&utm_campaign=direct_link (TAGGING_PARAMS) 
# @url_param description=on (on,off) 
# @url_param image=on (on,off) 
# @url_param specialprice=on (on,off) 
# @url_param sef=off (on,off) 
# @url_param on_stock=off (on,off) 
# @url_param forcepath=off (on,off) 
# @url_param forcefolder= (FORCEFOLDER) 
# @url_param language= (LANGUAGE_CODE) 
# @url_param language_id= (LANGUAGE_ID) 
# @url_param currency= (CURRENCY_CODE) 
#
# 
#########################################################################

// Debuging
if (isset($_GET['debug'])) {
	$time_start = microtime(true);
	$time = $time_start;
}

// Current datafeed script version
$script_version = "1.23";

// Print current Script version
if (@$_GET['get'] == "version") {
	echo "<b>Datafeed OpenCart</b><br />";
	echo "script version <b>" . $script_version . "</b><br />";
	exit;
}

// Print URL options
if (@$_GET['get'] == "options") {
	$script_basepath = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	
	echo "<b>Datafeed OpenCart</b><br />";
	echo "script version <b>" . $script_version . "</b><br /><br /><br />";
		
	echo "<b>Opencart 1.4 compatibility mode</b> Use this when you get this error (Error: Unknown column 'store_id' in 'where clause')<br />";
	echo "version=1.4  <a href=\"" . $script_basepath . "?version=1.4" . "\" >" . $script_basepath . "?version=1.4" . "</a><br /><br />";
}

// Set no time limit only if php is not running in Safe Mode
if (!ini_get("safe_mode")) {
    @set_time_limit(0);
	if (((int)substr(ini_get("memory_limit"), 0, -1)) < 256) {
		ini_set("memory_limit", "256M");
	}
}

@ignore_user_abort();
// Display errors
if (@$_GET['errors'] == "off") {
	if (!ini_get("safe_mode")) {
		ini_set('display_errors', 0);
	}
	@error_reporting(0);
}
else {
	@error_reporting(E_ALL^E_NOTICE);
}

$_SVR = array();

##### Include configuration files ################################################

$site_base_path = "./";

if(!file_exists($site_base_path . "config.php")) {
	exit('<HTML><HEAD><TITLE>404 Not Found</TITLE></HEAD><BODY><H1>Not Found</H1>Please ensure that export-opencart.php is in the same folder with config.php file or define the path to config.php file in $site_base_path variable.</BODY></HTML>');
}
else {
	require($site_base_path . "config.php");
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Tax and customer
require_once(DIR_SYSTEM . 'library/cart/customer.php');
require_once(DIR_SYSTEM . 'library/cart/tax.php');

// Registry
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Config
$config = new Config();
$registry->set('config', $config);

// Database 
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);


//  Get site basepath
$script_basepath = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

// Set store
$store_id = (isset($_GET['store']) && ($_GET['store'] >= 0)) ? $_GET['store'] : 0;

// Get list of stores
if ( (isset($_GET['show_stores']) && ($_GET['show_stores'] == 'on')) || @$_GET['mode'] == "debug") {
	print "<b>Stores List</b><br />";
	// Default store
	print "0 : " . " <a href=\"" . $script_basepath . "\" >" . $script_basepath . "</a> (default)<br />";
	
	$query = $db->query("SELECT * FROM " . DB_PREFIX . "store");
	foreach ($query->rows as $store) {
		
		$store_basepath = $store['url'] . "/" . basename(__FILE__, '.php');
		print $store['store_id'] . " : " . " <a href=\"" . $store_basepath . "?store="  . $store['store_id'] . "\" >" . $store_basepath . "?store=" . $store['store_id'] . "</a><br />";
	}
	
	exit;
}
/*
// Check if store_id field exists (version > 1.5)
$settingtableFields = mysql_list_fields(DB_DATABASE, DB_PREFIX . "setting");
$columns_s = mysql_num_fields($settingtableFields);

for ($i = 0; $i < $columns_s; $i++) {
    $s_field_array[] = mysql_field_name($settingtableFields, $i);
}*/

// Determin opencart version
$oc_version = (@$_GET['version'] == "1.4") ? "1.4" : "1.5";

if ($oc_version == "1.4") {
	// Settings
	$query = $db->query("SELECT * FROM " . DB_PREFIX . "setting");
}
else {
	// Settings
	$query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '" . addslashes($store_id) . "' OR store_id = '" . (int)$config->get('config_store_id') . "' ORDER BY store_id ASC");
}


foreach ($query->rows as $setting) {
	if (!@$setting['serialized']) {
		$config->set($setting['key'], $setting['value']);
	} else {
		$config->set($setting['key'], json_decode($setting['value'], true));
	}
}

// Request
$request = new Request();

// Customer
$registry->set('customer', new Cart\Customer($registry));

// Tax
$tax = new Cart\Tax($registry);
$registry->set('tax', $tax);

####################################################################

// Datafeed specific settings
$datafeed_separator = "|"; // Possible options are \t or |


##### Extract params from url ################################################

$apply_taxes = (@$_GET['taxes'] == "off") ? "off" : "on";
$apply_storetaxes = (@$_GET['storetaxes'] == "off") ? "off" : "on";
$apply_discount = (@$_GET['discount'] == "off") ? "off" : "on";
$add_vat = (@$_GET['add_vat'] == "on") ? "on" : "off";
$vat_value = (@$_GET['vat_value'] > 0) ? ((100 + $_GET['vat_value']) / 100) : 1.24; // default value
$add_shipping = (@$_GET['shipping'] == "on") ? "on" : "off";
$add_availability = (@$_GET['availability'] == "off") ? "off" : "on";
$add_gtin = (@$_GET['gtin'] == "on") ? "on" : "off";
$add_tagging = (@$_GET['add_tagging'] == "off") ? "off" : "on";
$tagging_params = (@$_GET['tagging_params'] != "") ? urldecode($_GET['tagging_params']) : "utm_source=nabava.net&utm_medium=cpc&utm_campaign=direct_link";
$show_description = (@$_GET['description'] == "off") ? "off" : ((@$_GET['description'] == "limited") ? "limited" : "on");
$show_image = (@$_GET['image'] == "off") ? "off" : "on";
$show_specialprice = (@$_GET['specialprice'] == "off") ? "off" : "on";
$customer_group_id = (@$_GET['customer_group_id'] >= 0) ? @$_GET['customer_group_id'] : "";
$sef = (@$_GET['sef'] == "off") ? "off" : ((@$_GET['sef'] == "v2") ? "v2" : "on");
$on_stock_only = (@$_GET['on_stock'] == "on") ? "on" : "off";
$currency = (@$_GET['currency'] != "") ? $_GET['currency'] : "";
$currency_id = (@$_GET['currency_id'] != "") ? $_GET['currency_id'] : "";
$display_currency = (@$_GET['display_currency'] != "") ? $_GET['display_currency'] : "";
$language_code = (@$_GET['language'] != "") ? $_GET['language'] : "";
$language_id = (@$_GET['language_id'] != "") ? $_GET['language_id'] : "";
$force_path = (@$_GET['forcepath'] == "on") ? "on" : "off";
$force_folder = (@$_GET['forcefolder'] != "") ? $_GET['forcefolder'] : "";
$limit = (@$_GET['limit'] > 0) ? $_GET['limit'] : "";
$show_combinations = (@$_GET['combinations'] == "on") ? "on" : "off";
$show_attribute = (@$_GET['attribute'] == "on") ? "on" : "off";

####################################################################

if (!defined('HTTP_SERVER')) {
	define('HTTP_SERVER', 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/') . '/');
}
if (!defined('HTTP_IMAGE')) {
	define('HTTP_IMAGE', HTTP_SERVER . 'image/');
}

// Print URL options
if (@$_GET['get'] == "options") {
	$script_basepath = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

	echo "<b>Show stores</b> - possible values on<br />";
	echo "show_stores=on <a href=\"" . $script_basepath . "?show_stores=on" . "\" >" . $script_basepath . "?show_stores=on" . "</a><br /><br />";
	
	echo "<b>Store taxes options</b> - possible values on, off default value on<br />";
	echo "storetaxes = on (on,off) <a href=\"" . $script_basepath . "?storetaxes=off" . "\" >" . $script_basepath . "?storetaxes=off" . "</a><br /><br />";
	
	//echo "<b>Discount options</b> - possible values on, off default value on<br />";
	//echo "discount=on (on,off) <a href=\"" . $script_basepath . "?discount=off" . "\" >" . $script_basepath . "?discount=off" . "</a><br /><br />";
	
	echo "<b>Add VAT to prices</b> - possible values on, off default value off<br />";
	echo "add_vat=off (on,off) <a href=\"" . $script_basepath . "?add_vat=on" . "\" >" . $script_basepath . "?add_vat=on" . "</a><br /><br />";
	
	echo "<b>VAT value</b> - possible values percent value default value 24  - interger or float number ex 19 or 19.5<br />";
	echo "vat_value=24 (VAT_VALUE) <a href=\"" . $script_basepath . "?add_vat=on&vat_value=19" . "\" >" . $script_basepath . "?add_vat=on&vat_value=19" . "</a><br /><br />";
	
	//echo "<b>Add shipping to datafeed</b> - possible values on, off default value off <br />";
	//echo "shipping=off (on,off) <a href=\"" . $script_basepath . "?shipping=on" . "\" >" . $script_basepath . "?shipping=on" . "</a><br /><br />";
	
	echo "<b>Add availability to datafeed</b> - possible values on, off default value on<br />";
	echo "availability=on (on,off) <a href=\"" . $script_basepath . "?availability=off" . "\" >" . $script_basepath . "?availability=off" . "</a><br /><br />";
	
	echo "<b>Add GTIN to datafeed</b> - possible values on, off default value on (versions 1.5 or higher)<br />";
	echo "gtin=off (on,off) <a href=\"" . $script_basepath . "?gtin=on" . "\" >" . $script_basepath . "?gtin=on" . "</a><br /><br />";
	
	echo "<b>Add GA Tagging to product URL</b> - possible values on, off default value on<br />";
	echo "add_tagging=on (on,off) <a href=\"" . $script_basepath . "?add_tagging=off" . "\" >" . $script_basepath . "?add_tagging=off" . "</a><br /><br />";
	
	echo "<b>Add custom Tagging to product URL</b> - possible values url_encode(TAGGING_PARAMS) default value tagging_params=utm_source=nabava.net&utm_medium=cpc&utm_campaign=direct_link<br />";
	echo "tagging_params=utm_source=nabava.net&utm_medium=cpc&utm_campaign=direct_link (TAGGING_PARAMS) <a href=\"" . $script_basepath . "?tagging_params=from%3Dnabava.net" . "\" >" . $script_basepath . "?tagging_params=from%3Dnabava.net" . "</a><br /><br />";
	
	echo "<b>Display Description options</b> - possible values on, off, limited default value on<br />";
	echo "description=on (on,off) <ul><li><a href=\"" . $script_basepath . "?description=off" . "\" >" . $script_basepath . "?description=off" . "</a></li>";
	echo "<li><a href=\"" . $script_basepath . "?description=limited" . "\" >" . $script_basepath . "?description=limited" . "</a></li></ul>";

	echo "<b>Display image options</b> - possible values on, off default value on<br />";
	echo "image=on (on,off) <a href=\"" . $script_basepath . "?image=off" . "\" >" . $script_basepath . "?image=off" . "</a><br /><br />";
	
	echo "Special price options - possible values on, off default value on<br />";
	echo "specialprice=on (on,off) <a href=\"" . $script_basepath . "?specialprice=off" . "\" >" . $script_basepath . "?specialprice=off" . "</a><br /><br />";
	
	echo "<b>Customer group id</b> - possible values 0,1 etc. <br />";
	echo "customer_group_id=default <a href=\"" . $script_basepath . "?customer_group_id=1" . "\" >" . $script_basepath . "?customer_group_id=1" . "</a><br /><br />";
	
	echo "<b>Show only on stock products</b> - possible values on, off default value off<br />";
	echo "on_stock=off (on,off) <a href=\"" . $script_basepath . "?on_stock=on" . "\" >" . $script_basepath . "?on_stock=on" . "</a><br /><br />";
	
	echo "<b>Show SEO friendly url</b> - possible values on, off default value off<br />";
	echo "sef=off (on,off) <a href=\"" . $script_basepath . "?sef=on" . "\" >" . $script_basepath . "?sef=on" . "</a><br />
	<a href=\"" . $script_basepath . "?sef=v2" . "\" >" . $script_basepath . "?sef=v2" . "</a><br /><br /><br />";
	
	echo "<b>Get prices in specified currency</b> - possible values USD,EUR etc. <br />";
	echo "currency=DEFAULT_CURRENCY <a href=\"" . $script_basepath . "?currency=EUR" . "\" >" . $script_basepath . "?currency=EUR" . "</a><br /><br />";
	
	echo "<b>Get prices in specified currency id</b> - possible values 1,2 etc. <br />";
	echo "currency_id=DEFAULT_CURRENCY_ID <a href=\"" . $script_basepath . "?currency_id=1" . "\" >" . $script_basepath . "?currency_id=1" . "</a><br /><br />";
		
	echo "<b>Force displayed currency</b> - possible values USD,EUR etc. <br />";
	echo "display_currency= <a href=\"" . $script_basepath . "?display_currency=EUR" . "\" >" . $script_basepath . "?display_currency=EUR" . "</a><br /><br />";
		
	echo "<b>Get texts in specified language code</b> - possible values en,ro etc. <br />";
	echo "language=DEFAULT_LANGUAGE_CODE <a href=\"" . $script_basepath . "?language=en" . "\" >" . $script_basepath . "?language=en" . "</a><br /><br />";
	
	echo "<b>Get texts in specified language id</b> - possible values 1,2 etc. <br />";
	echo "language_id=DEFAULT_LANGUAGE_ID <a href=\"" . $script_basepath . "?language_id=1" . "\" >" . $script_basepath . "?language_id=1" . "</a><br /><br />";
	
	echo "<b>Get feed paginated</b> - possible values 1,2,..  etc. <br />";
	echo "pg=PAGE <a href=\"" . $script_basepath . "?pg=1" . "\" >" . $script_basepath . "?pg=1" . "</a><br />";
	echo "pg=PAGE&limit=PAGE_SIZE <a href=\"" . $script_basepath . "?pg=1&limit=100" . "\" >" . $script_basepath . "?pg=1&limit=100" . "</a><br /><br />";
	
	echo "<b>Limit displayed products</b> - limit the number of displayed products - possible values (integer) <br />";
	echo "limit=no_limit <a href=\"" . $script_basepath . "?limit=10" . "\" >" . $script_basepath . "?limit=10" . "</a><br /><br />";

	echo "<b>Display product combinations</b> - possible values on, off default value on<br />";
	echo "combinations=off (on,off) <a href=\"" . $script_basepath . "?combinations=on" . "\" >" . $script_basepath . "?combinations=on" . "</a><br /><br />";
	
	echo "<b>Display product attributes</b> - possible values on, off default value off<br />";
	echo "attribute=off (on,off) <a href=\"" . $script_basepath . "?attribute=on" . "\" >" . $script_basepath . "?attribute=on" . "</a><br /><br />";
	
	echo "<b>Show errors</b> - possible values on, off default value on<br />";
	echo "errors=on (on,off) <a href=\"" . $script_basepath . "?errors=off" . "\" >" . $script_basepath . "?errors=off" . "</a><br /><br />";
	
	echo "<br />";
	
	exit;
	
}

##### Extract options from database ################################################

if ($oc_version == "1.4") {
	$store_cond = "";
}
else {
	$store_cond = " AND store_id = '" . addslashes($store_id) . "'";
}

$SETTING = array();

// Get Settings
//$query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE " . DB_PREFIX . "setting.group='config'" . $store_cond);
$query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE " . DB_PREFIX . "setting.key LIKE '%config%'" . $store_cond);

foreach ($query->rows as $setting) {
	if (!$setting['serialized']) {
		$SETTING[$setting['key']] = $setting['value'];
	} else {
		$SETTING[$setting['key']] = json_decode($setting['value'],true);
	}
}

// Rewrite tax settings
$SETTING['config_tax'] = ($apply_storetaxes == "off") ? "false" : @$SETTING['config_tax'];

$SETTING['config_customer_group_id'] = (isset($SETTING['config_customer_group_id'])) ? @$SETTING['config_customer_group_id'] : (($customer_group_id > 0) ? $customer_group_id : 1);

if ($language_id > 0) {
	// Set the main language
	$main_language = $language_id;
}
elseif ($language_code != "") {
	$query = $db->query("SELECT language_id FROM " . DB_PREFIX . "language WHERE code='" . addslashes($language_code) . "' LIMIT 1");
	$language_id = $query->row['language_id'];
	
	// Set the main language
	$main_language = ($language_id > 0) ? $language_id : 1;
}
else {
	// Detect default language code
	$query = $db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE " . DB_PREFIX . "setting.key='config_language'");
	$language_code = $query->row['value'];
	
	// Detect default language ID
	$query = $db->query("SELECT language_id FROM " . DB_PREFIX . "language WHERE code='" . addslashes($language_code) . "' LIMIT 1");
	$main_language = $query->row['language_id'];
	
}

// Get default currency
$query = $db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE " . DB_PREFIX . "setting.key='config_currency'");
$default_datafeed_currency = $query->row['value'];

// Use selected currency id
if ($currency_id > 0) {
	
	$query = $db->query("SELECT currency_id, code, value FROM " . DB_PREFIX . "currency WHERE currency_id = '" . addslashes($currency_id) . "'");

	$CURRENCY['id_currency'] = $query->row['currency_id'];
	$CURRENCY['code'] = $query->row['code'];
	$CURRENCY['value'] = $query->row['value'];
	
}
elseif ($currency != "") {
	$query = $db->query("SELECT currency_id, code, value FROM " . DB_PREFIX . "currency WHERE code = '" . addslashes($currency) . "'");
	
	$CURRENCY['id_currency'] = $query->row['currency_id'];
	$CURRENCY['code'] = $query->row['code'];
	$CURRENCY['value'] = $query->row['value'];
	
}
else {
	$query = $db->query("SELECT currency_id, code, value FROM " . DB_PREFIX . "currency WHERE code = '" . addslashes($default_datafeed_currency) . "'");

	$CURRENCY['id_currency'] = $query->row['currency_id'];
	$CURRENCY['code'] = $query->row['code'];
	
}

$CURRENCY['code'] = (trim(strtolower($CURRENCY['code'])) == "lei" || trim(strtolower($CURRENCY['code'])) == "ro") ? "RON" : $CURRENCY['code'];

// Overite currency with forced value
$CURRENCY['code'] = ($display_currency != "") ? $display_currency : $CURRENCY['code'];

// Overite Sef options with config values
$sef = (@$SETTING['config_seo_url'] == 1 && @$_GET['sef'] == "on") ? "on" : $sef;

// Get categories
$query = $db->query("SELECT * FROM " . DB_PREFIX . "category 
	LEFT JOIN " . DB_PREFIX . "category_description ON (" . DB_PREFIX . "category.category_id = " . DB_PREFIX . "category_description.category_id) 
	WHERE language_id = '" . addslashes($main_language) . "'");
	
if ($query->num_rows) {	
	foreach ($query->rows as $result) {
		$CAT_ARR[$result['category_id']] = $result;
	}
}

$SEO_ARR = array();
if ($sef == "off") {
	// Get SEO links
	$query = $db->query("SELECT SUBSTRING(" . DB_PREFIX . "url_alias.`query`, 12) prod_id, " . DB_PREFIX . "url_alias.* FROM " . DB_PREFIX . "url_alias 
	WHERE SUBSTRING(" . DB_PREFIX . "url_alias.`query`, 1,10) = 'product_id'");
	if ($query->num_rows) {	
		foreach ($query->rows as $result) {
			$SEO_ARR[$result['prod_id']] = $result['keyword'];
		}
	}
}

$SPECIAL_PRICE = array();
// Get special prices
if ($show_specialprice == "on") {
	// Get SEO links
	$query = $db->query("SELECT product_id, price
		FROM " . DB_PREFIX . "product_special
		WHERE (date_start <= DATE_FORMAT(NOW(), '%Y-%m-%d') OR date_start = '0000-00-00') AND (date_end >= DATE_FORMAT(NOW(), '%Y-%m-%d') OR date_end = '0000-00-00') AND customer_group_id = " . addslashes(@$SETTING['config_customer_group_id']) . "
		ORDER BY customer_group_id DESC");

	if ($query->num_rows) {	
		foreach ($query->rows as $result) {
			$SPECIAL_PRICE[$result['product_id']] = $result['price'];
		}
	}
}

$STOCK_MSG = array();

// Get stock statuses
$query = $db->query("SELECT * FROM " . DB_PREFIX . "stock_status 
	WHERE language_id = '" . addslashes($main_language) . "'");

if ($query->num_rows) {	
	foreach ($query->rows as $result) {
		$STOCK_MSG[$result['stock_status_id']] = $result['name'];
	}
}

######################################################################


##### Extract products from database ###############################################

$add_limit = "";

if (isset($_GET['pg']) && @$_GET['pg'] > 0) {
	$_step = ($limit > 0) ? $limit : 1000;
	$_start = ($_GET['pg'] - 1) * $_step;
	$_start = ($_start >= 0) ? $_start : 0;
	$add_limit = " LIMIT " . $_start . "," . $_step;
}

// Build on stock condition
$on_stock_cond = ($on_stock_only == "on") ? "AND " . DB_PREFIX . "product.quantity > 0" : "";

// Add upc field
$upc_field = ($add_gtin == "on") ? DB_PREFIX . "product.upc," : "";

if ($show_description == "limited") {
	$description_field = "SUBSTRING(" . DB_PREFIX . "product_description.description, 1,500) AS prod_desc";
}
elseif($show_description == "on") {
	$description_field = DB_PREFIX . "product_description.description AS prod_desc";
}
else {
	$description_field = "''";
}

// Debuging
if (isset($_GET['debug'])) {

	$query = $db->query("SELECT COUNT(if(p.status = '1', 1, NULL)) AS active_products, COUNT(p.product_id) AS total_products " . " FROM " . DB_PREFIX . "product p");
	
	echo "Active products: " . $query->row['active_products'] . "\n";
	echo "Total products: " . $query->row['total_products'] . "\n\n";
	
	// Init prod count
	$cnt_prod = 0;
	$add_limit = " LIMIT 0,10";
}

if ($oc_version == "1.4") {
	
	$query = $db->query("SELECT " . DB_PREFIX . "product_to_category.category_id, " . DB_PREFIX . "manufacturer.name as prod_manufacturer, " . DB_PREFIX . "product.model as prod_model, " . DB_PREFIX . "product.product_id as prod_id, " . DB_PREFIX . "product.tax_class_id, " . DB_PREFIX . "product_description.name as prod_name, " . $description_field . ", " . DB_PREFIX . "product.image as prod_image, " . DB_PREFIX . "product.price as prod_price, " . DB_PREFIX . "tax_rate.rate, " . DB_PREFIX . "product.stock_status_id, " . $upc_field . DB_PREFIX . "product.quantity
	FROM " . DB_PREFIX . "product
	LEFT JOIN " . DB_PREFIX . "product_description ON (" . DB_PREFIX . "product.product_id = " . DB_PREFIX . "product_description.product_id AND " . DB_PREFIX . "product_description.language_id = " . $main_language . ")
	LEFT JOIN " . DB_PREFIX . "manufacturer ON " . DB_PREFIX . "product.manufacturer_id = " . DB_PREFIX . "manufacturer.manufacturer_id
	LEFT JOIN " . DB_PREFIX . "product_to_category ON " . DB_PREFIX . "product.product_id = " . DB_PREFIX . "product_to_category.product_id
	LEFT JOIN " . DB_PREFIX . "tax_rate ON " . DB_PREFIX . "product.tax_class_id = " . DB_PREFIX . "tax_rate.tax_class_id
	WHERE " . DB_PREFIX . "product.status = '1' " . $on_stock_cond . "
	ORDER BY " . DB_PREFIX . "product.product_id ASC, " . DB_PREFIX . "product_to_category.category_id DESC" . $add_limit);
	
}
else {
	
	$query = $db->query("SELECT " . DB_PREFIX . "product_to_category.category_id, " . DB_PREFIX . "manufacturer.name as prod_manufacturer, " . DB_PREFIX . "product.model as prod_model, " . DB_PREFIX . "product.product_id as prod_id, " . DB_PREFIX . "product.tax_class_id, " . DB_PREFIX . "product_description.name as prod_name, " . $description_field . ", " . DB_PREFIX . "product.image as prod_image, " . DB_PREFIX . "product.price as prod_price, " . DB_PREFIX . "product.stock_status_id, " . $upc_field . DB_PREFIX . "product.quantity
	FROM " . DB_PREFIX . "product
	LEFT JOIN " . DB_PREFIX . "product_to_store ON " . DB_PREFIX . "product.product_id = " . DB_PREFIX . "product_to_store.product_id
	LEFT JOIN " . DB_PREFIX . "product_description ON (" . DB_PREFIX . "product.product_id = " . DB_PREFIX . "product_description.product_id AND " . DB_PREFIX . "product_description.language_id = " . $main_language . ")
	LEFT JOIN " . DB_PREFIX . "manufacturer ON " . DB_PREFIX . "product.manufacturer_id = " . DB_PREFIX . "manufacturer.manufacturer_id
	LEFT JOIN " . DB_PREFIX . "product_to_category ON " . DB_PREFIX . "product.product_id = " . DB_PREFIX . "product_to_category.product_id
	WHERE " . DB_PREFIX . "product.status = '1' " . $on_stock_cond . $store_cond . "
	ORDER BY " . DB_PREFIX . "product.product_id ASC, " . DB_PREFIX . "product_to_category.category_id DESC" . $add_limit);
	
}


###################################################################

##### Print product data ####################################################

$current_id = 0;
$prod_count = 0;

// Go trough all records
if ($query->num_rows) {	
	foreach ($query->rows as $row) {
		// If we've sent this one, skip the rest - this is to ensure that we do not get duplicate products
		$prod_id = $row['prod_id'];
		if ($current_id == $prod_id) {
			continue;
		}
		else {
			$current_id = $prod_id;
		}
		// Show combinations
		if ($show_combinations == "on") {
			$query_comb = $db->query("SELECT * FROM " . DB_PREFIX . "product_option
				LEFT JOIN " . DB_PREFIX . "product_option_value ON " . DB_PREFIX . "product_option.product_option_id = " . DB_PREFIX . "product_option_value.product_option_id
				WHERE " . DB_PREFIX . "product_option.product_id = '" . $prod_id . "'");
			
			$COMB_ARR = array();
			if ($query_comb->num_rows) {	
				foreach ($query_comb->rows as $combination) {
					$COMB_ARR[$combination['product_option_value_id']]['product_option_id'] = $combination['product_option_value_id'];
					$COMB_ARR[$combination['product_option_value_id']]['price'] = $combination['price'];
					$COMB_ARR[$combination['product_option_value_id']]['weight'] = $combination['weight'];
					$COMB_ARR[$combination['product_option_value_id']]['quantity'] = $combination['quantity'];
					$COMB_ARR[$combination['product_option_value_id']]['value'] = $combination['value'];
				}
			}
		}
//		print_r($COMB_ARR);
		// Get category name
		@$category_name = smfeed_get_full_cat($row['category_id'], $CAT_ARR);
		
		// Get manufacturer
		@$prod_manufacturer = $row['prod_manufacturer'];

		// Get product model
		$prod_model = $row['prod_model'];

		// Get name and description for product
		$prod_name = $row['prod_name'];
		
		// Limit description size
		if ($show_description == "limited") {
			$prod_desc = html_entity_decode($row['prod_desc'], ENT_QUOTES, 'UTF-8');
			$prod_desc = strip_tags($prod_desc);
			$prod_desc = substr(trim($prod_desc), 0, 300);
		}
		elseif ($show_description == "on") {
			$prod_desc = html_entity_decode($row['prod_desc'], ENT_QUOTES, 'UTF-8');
		}
		else {
			$prod_desc = "";
		}
		
		// Clean product name (new lines)
		$prod_name = str_replace("\n", "", strip_tags($prod_name));
		$prod_name = str_replace("\r", "", strip_tags($prod_name));
		$prod_name = str_replace("\t", " ", strip_tags($prod_name));

		// Clean product description (Replace new line with <BR>). In order to make sure the code does not contains other HTML code it might be a good ideea to strip_tags()
		$prod_desc = smfeed_replace_not_in_tags("\n", "<BR />", $prod_desc);
		$prod_desc = str_replace("\n", " ", $prod_desc);		
		$prod_desc = str_replace("\r", "", $prod_desc);
		$prod_desc = str_replace("\t", " ", $prod_desc);

		// Clean category product names and descriptions (separators)
		if ($datafeed_separator == "\t") {
			$category_name = str_replace("\t", " ", $category_name);
			// Continue... tabs were already removed
			
		}
		elseif ($datafeed_separator == "|") {
			$prod_name = str_replace("|", " ", strip_tags($prod_name));
			$prod_desc = str_replace("|", " ", $prod_desc);
			$category_name = str_replace("|", " ", $category_name);
		}
		else {
			print "Incorrect columns separator.";
			exit;
		}

		$seo_cat_str = ($CAT_ARR[$row['category_id']]['name'] != "") ? "/" . smfeed_build_str_key($CAT_ARR[$row['category_id']]['name']) . "/" : "";
		
		// Get product url
		$prod_url = smfeed_get_product_url($prod_id, $seo_cat_str);

		// Add GA Tagging parameters to url
		if ($add_tagging == "on") {
			$and_param = (preg_match("/\?/", $prod_url)) ? "&" : "?";
			$prod_url = $prod_url . $and_param . $tagging_params;
		}
		
		// Get product image
		$prod_image = ($row['prod_image'] != "") ? smfeed_get_product_image($row['prod_image']) : "";

		
		// Get product price
		if (@$SPECIAL_PRICE[$prod_id]) {
			$prod_price = $tax->calculate($SPECIAL_PRICE[$prod_id], $row['tax_class_id'], @$SETTING['config_tax']);
		}
		else {
			$prod_price = $tax->calculate($row['prod_price'], $row['tax_class_id'], @$SETTING['config_tax']);
		}

		// Add VAT to prices
		if ($add_vat == "on") {
			$prod_price = $prod_price * $vat_value;
		}
			
		// Get conversion to wanted currency
		if ($currency_id > 0 || $currency != "") {
			$prod_price = $prod_price * $CURRENCY['value'];
		}
		
		// Fromat price
		$prod_price = number_format(round($prod_price, 2), 2, ".", "");
		
		// Build stock conditions
		if ($add_availability == "on") {
			if ($row['quantity'] > 0) {
				$availability = "In stock";
			}
			else {
				$availability = @$STOCK_MSG[$row['stock_status_id']];
			}
		}
		else {
			$availability = "";
		}
		
		// Add Shipping
		$shipping_value = ($add_shipping == "on") ? "" : "";
		
		// Add GTIN code
		$gtin = ($add_gtin == "on") ? @$row['upc'] : "";
	
		// Show product attributs
		if ($show_attribute == "on") {
			$ATTRIBUTE = array();
			$query_attr = $db->query("SELECT * FROM " . DB_PREFIX . "product_attribute
				LEFT JOIN " . DB_PREFIX . "attribute ON " . DB_PREFIX . "product_attribute.attribute_id = " . DB_PREFIX . "attribute.attribute_id
				LEFT JOIN " . DB_PREFIX . "attribute_description ON " . DB_PREFIX . "product_attribute.attribute_id = " . DB_PREFIX . "attribute_description.attribute_id
				WHERE " . DB_PREFIX . "attribute_description.language_id = 1 AND " . DB_PREFIX . "product_attribute.product_id = '" . $prod_id . "'");
			
			if ($query_attr->num_rows) {	
				foreach ($query_attr->rows as $row) {
					$ATTRIBUTE[$row['name']] = $row['text'];
				}
				$tmp = array();
				foreach ($ATTRIBUTE as $i=>$v) {
					$tmp[] = $i . ": " . $v;
				}
				$prod_attribute = join("; ", array_values($tmp));;
			}
			else {
				$prod_attribute = "";
			}
			
		}

		// Output the datafeed content
		// Category, Manufacturer, Model, ProdCode, ProdName, ProdDescription, ProdURL, ImageURL, Price, Currency, Shipping value, Availability, GTIN (UPC/EAN/ISBN) 
		// Display combination products
		if ($show_combinations == "on" && is_array($COMB_ARR) && sizeof($COMB_ARR) > 0) {
			foreach ($COMB_ARR AS $id_comb => $comb) {
				$comb['name'] = $prod_name . ", " . $comb['value'];
				$comb['product_id'] = $prod_id . "_" . $id_comb;
				// Build stock conditions
				if ($add_availability == "on") {
					if ($comb['quantity'] > 0) {
						$availability = "In stock";
					}
					else {
						$availability = "Out of stock";
					}
				}
								
				else {
					if ($comb['quantity'] > 0) {
						$availability = "In stock";
					}
					else {
						$availability = "Out of stock";
					}
				}
				
				// Output the datafeed content
				// Category, Manufacturer, Model, ProdCode, ProdName, ProdDescription, ProdURL, ImageURL, Price, Currency, Shipping value, Availability, GTIN (UPC/EAN/ISBN) 
				print 
				$category_name . $datafeed_separator .
				$prod_manufacturer . $datafeed_separator .
				$prod_model . $datafeed_separator .
				$comb['product_id'] . $datafeed_separator .
				$comb['name'] . $datafeed_separator .
				$prod_desc . $datafeed_separator .
				$prod_url . $datafeed_separator .
				$prod_image . $datafeed_separator .
				$comb['price'] . $datafeed_separator .
				$CURRENCY['code'] . $datafeed_separator .
				$shipping_value . $datafeed_separator .
				$availability . $datafeed_separator . 
				($show_attribute == "on" ? ($gtin . $datafeed_separator . $prod_attribute . "\n") : ($gtin . "\n"));
				$prod_count ++;
			}
		}
		else {
			print 
			$category_name . $datafeed_separator .
			$prod_manufacturer . $datafeed_separator .
			$prod_model . $datafeed_separator .
			$prod_id . $datafeed_separator .
			$prod_name . $datafeed_separator .
			$prod_desc . $datafeed_separator .
			$prod_url . $datafeed_separator .
			$prod_image . $datafeed_separator .
			$prod_price . $datafeed_separator .
			$CURRENCY['code'] . $datafeed_separator .
			$shipping_value . $datafeed_separator .
			$availability . $datafeed_separator . 
			($show_attribute == "on" ? ($gtin . $datafeed_separator . $prod_attribute . "\n") : ($gtin . "\n"));
			$prod_count ++;
		}
		
		// Debuging
		if (isset($_GET['debug'])) {
			$cnt_prod ++;
			echo $cnt_prod . "."; 
			echo "\t" . number_format(microtime(true) - $time, 3) . "s \n"; 
			$time = microtime(true);
			echo "\t" . number_format(memory_get_usage()/1048576, 3) . "Mb\n\n";
		}
		
		// Limit displayed products
		if ($limit > 0 && $prod_count >= $limit && !isset($_GET['pg'])) {
			break;
		}
		
	}
}


###################################################################

// Debuging
if (isset($_GET['debug'])) {
	echo "\npage loaded in " . number_format(microtime(true) - $time_start, 3) . "s \n"; 
	echo "memory limit " . ini_get("memory_limit") . "\n";
	echo "max_execution_time " . ini_get("max_execution_time") . "\n\n";
}

##### Functions ########################################################

function smfeed_get_product_url($prod_id, $seo_cat_str) {
	global $SEO_ARR, $sef;
	
	if ($sef == "on" && (@$SEO_ARR[$prod_id])) {
		return HTTP_SERVER . $SEO_ARR[$prod_id];
	}
	elseif ($sef == "v2" && (@$SEO_ARR[$prod_id])) {
		return HTTP_SERVER . $seo_cat_str . $SEO_ARR[$prod_id];
	}
	else {
		return HTTP_SERVER . "index.php?route=product/product&product_id=" . $prod_id;
	}
}

function smfeed_get_product_image ($prod_image) {
	
	$image_src = "";
	if ($prod_image != "") {
		if (!preg_match("/http\:\/\//", $prod_image)) {
			$image_src = HTTP_IMAGE . $prod_image;
		}
		else {
			$image_src = $prod_image;
		}
	}

	return $image_src;
	
}

function smfeed_replace_not_in_tags($find_str, $replace_str, $string) {
	
	$find = array($find_str);
	$replace = array($replace_str);	
	preg_match_all('#[^>]+(?=<)|[^>]+$#', $string, $matches, PREG_SET_ORDER);	
	foreach ($matches as $val) {	
		if (trim($val[0]) != "") {
			$string = str_replace($val[0], str_replace($find, $replace, $val[0]), $string);
		}
	}	
	return $string;
}

function smfeed_build_str_key($text){
	
	$text = str_replace(" ", "-", trim(smfeed_string_clean_search($text)));
	$text = rawurlencode(strtolower($text));
	
	$text = strtolower(smfeed_clean_accents($text));
	
	return $text;
}

// Function to get category with full path
function smfeed_get_full_cat($cat_id, $CATEGORY_ARR) {

	$item_arr = $CATEGORY_ARR[$cat_id];
	$cat_name = $item_arr['name'];
	
	while (sizeof($CATEGORY_ARR[$item_arr['parent_id']]) > 0 && is_array($CATEGORY_ARR[$item_arr['parent_id']]) ) {
		
		$cat_name = $CATEGORY_ARR[$item_arr['parent_id']]['name'] . " > " . $cat_name;		
		$item_arr = $CATEGORY_ARR[$item_arr['parent_id']];
	}
	
	// Strip html from category name
	$cat_name = smfeed_html_to_text($cat_name);
	
	return $cat_name;
}

function smfeed_html_to_text($string){

	$search = array (
		"'<script[^>]*?>.*?</script>'si",  // Strip out javascript
		"'<[\/\!]*?[^<>]*?>'si",  // Strip out html tags
		"'([\r\n])[\s]+'",  // Strip out white space
		"'&(quot|#34);'i",  // Replace html entities
		"'&(amp|#38);'i",
		"'&(lt|#60);'i",
		"'&(gt|#62);'i",
		"'&(nbsp|#160);'i",
		"'&(iexcl|#161);'i",
		"'&(cent|#162);'i",
		"'&(pound|#163);'i",
		"'&(copy|#169);'i",
		"'&(reg|#174);'i",
		"'&#8482;'i",
		"'&#149;'i",
		"'&#151;'i"
		);  // evaluate as php
	
	$replace = array (
		" ",
		" ",
		"\\1",
		"\"",
		"&",
		"<",
		">",
		" ",
		"&iexcl;",
		"&cent;",
		"&pound;",
		"&copy;",
		"&reg;",
		"<sup><small>TM</small></sup>",
		"&bull;",
		"-",
		);
	
	$text = preg_replace ($search, $replace, $string);
	return $text;
	
}

// Clean accesnts
function smfeed_clean_accents($text){

	$search = array (
		
		"'%C4%99'", #e
		"'%C3%A8'", #e
		"'%C3%A9'", #e
		"'%C3%AA'", #e
		"'%C3%AB'", #e
		"'%C3%89'", #e
		"'%C3%88'", #E
		"'%C3%8A'", #E
		
		"'%C4%85'", #a
		"'%C3%A3'", #a
		"'%C3%A0'", #a
		"'%C3%A4'", #a
		"'%C3%A2'", #a	
		"'%C3%A1'", #a
		"'%C3%A5'", #a
		"'%C3%81'", #A
		"'%C3%82'", #A
		"'%C3%84'", #A
		"'%C3%85'", #A
		"'%C3%80'", #A
		
		"'%C4%87'", #c
		"'%C3%A7'", #c
		"'%C3%87'", #C
		
		"'%C5%9B'", #s
		"'%C5%A1'", #s
		"'%C5%A0'", #S
		"'%C5%9A'", #S
		
		"'%C3%B0'", #d
		"'%C3%B1'", #n
		"'%C3%BD'", #y
		"'%C4%9F'", #g
		
		"'%C4%B1'", #i
		"'%C3%AE'", #i
		"'%C3%AD'", #i
		"'%C3%AF'", #i
		"'%C3%AC'", #i
		"'%C3%8D'", #I
		"'%C3%8E'", #I
		"'%C4%B0'", #I
		
		"'%C5%82'", #l
		
		"'%C5%84'", #n
		
		"'%C3%BA'", #u
		"'%C3%BB'", #u
		"'%C3%B9'", #u
		"'%C3%BC'", #u
		"'%C5%B1'", #u
		
		"'%C3%9C'", #U
		
		"'%C3%B4'", #o
		"'%C3%B3'", #o
		"'%C3%91'", #o
		"'%C3%B6'", #o
		"'%C5%91'", #o
		"'%C3%B2'", #o
		"'%C3%B5'", #o
		"'%C3%93'", #O
		"'%C3%96'", #O
		
		"'%C3%9F'", #ss
		"'%C5%BC'", #z
		"'%C5%BA'", #z
		"'%C5%BB'", #Z
		
		"'%C3%A6'", #ae
		"'%C3%B8'", #oe
		
		"'%C2%AE'",
		"'%C2%B4'",
		"'%E2%84%A2'",
		
	);
	
	$replace = array (
		
		"e",
		"e",
		"e",
		"e",
		"e",
		"e",
		"E",
		"E",
		
		"a",
		"a",
		"a",
		"a",
		"a",
		"a",
		"a",
		"A",
		"A",
		"A",
		"A",
		"A",
		
		"c",
		"c",
		"C",
		
		"s",
		"s",
		"S",
		"S",
		
		"d",
		"n",
		"y",
		"g",
		
		"i",
		"i",
		"i",
		"i",
		"i",
		"I",
		"I",
		"I",
		
		"l",
		
		"n",
		
		"u",
		"u",
		"u",
		"u",
		"u",
		
		"U",
		
		"o",
		"o",
		"o",
		"o",
		"o",
		"o",
		"o",
		"O",
		"O",
		
		"ss",
		"z",
		"z",
		"Z",
		
		"ae",
		"oe",
		
		"",
		"",
		"",
		
	);
	
	$text = preg_replace($search, $replace, $text);
	
	return $text;

}

// Clean strings
function smfeed_string_clean_search($string){
	
	$trans = get_html_translation_table(HTML_ENTITIES);
	$trans = array_flip ($trans);
	$string = strtr($string, $trans);
	
	$search = array (
		"'&quot;'",
		"'&lt;'",
		"'&gt;'",
		"'%&pound;'",
		"'%&euro;'",
		"'-'",
		"'~'",
		"'!'",
		"'\?'",
		"'@'",
		"'#'",
		"'\\$'",
		"'%'",
		"'\^'",
		"'&'",
		"'\*'",
		"'\('",
		"'\)'",
		"'_'",
		"'\+'",
		"'='",
		"'\.'",
		"','",
		"'\''",
		"'\['",
		"'\]'",
		"'{'",
		"'}'",
		"'\|'",
		"'\"'",
		"':'",
		"';'",
		"'/'",
		"'\\\'",
		"'>'",
		"'<'",		
		"'[\r]+'",
		"'[\n]+'",
		"'[\t]+'",
		"'[\s]+'",
	);
	
	$replace = array (
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
		" ",
	);
	
	$string = preg_replace($search, $replace, $string);
	
	return $string;
	
}

###################################################################

exit;

?>