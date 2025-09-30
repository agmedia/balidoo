<?php echo $header; ?><?php echo $column_left; ?>
<!--Main Content block start-->

<div id="content">
  <!--Header Start-->
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
		<button type="button" class="btn btn-warning" data-toggle="modal" data-target="#add-template"><i class="fa fa-plus" aria-hidden="true"></i> Add Template</button>
        <a onClick="document.getElementById('form-language').submit();" form="form-latest" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i> SAVE</a>
		<a id="set-samples" class="btn btn-success"><i class="fa fa-flask"></i> Load Samples</a>
		<a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
	  </div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <!--Header End-->
  <!--Container 1 start -->
  <div class="container-fluid">
    <!--Start - Error / Success Message if any -->
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <!--End - Error / Success Message if any -->
	<div id="msgoutput"></div>
    <!--Panel Content Start-->
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $heading_title; ?></h3>
		<?php if ($stores) { ?>
		<div class="pull-right">
		<select id="store">
			<option value="0" <?php echo ($store_id == 0)?'selected':''; ?>>Default Store</option>
			<?php foreach ($stores as $store) { ?>
				<option value="<?php echo $store['store_id']; ?>" <?php echo ($store_id == $store['store_id'])?'selected':''; ?>><?php echo $store['name']; ?></option>
			<?php } ?>
		</select>
		</div>
		<?php } ?>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-language" class="form-horizontal">
			<!--Tabs UL Starts-->
			<ul class="nav nav-tabs" id="tabs">
                <li class="active"><a href="#tab-dashboard" onclick="loadBlock('dashboard');" data-toggle="tab"><i class="fa fa-tachometer" aria-hidden="true"></i> <?php echo $tab_dashboard; ?></a></li>
				<li><a href="#tab-product" 		onclick="loadProLangBlock(<?php echo $first_language; ?>);" data-toggle="tab"><i class="fa fa-newspaper-o" aria-hidden="true"></i> <?php echo $tab_product; ?></a></li>
				<li><a href="#tab-category" 	onclick="loadCatLangBlock(<?php echo $first_language; ?>);"	data-toggle="tab"><i class="fa fa-newspaper-o" aria-hidden="true"></i> <?php echo $tab_category; ?></a></li>
				<li><a href="#tab-brand" 		onclick="loadBraLangBlock(<?php echo $first_language; ?>);"	data-toggle="tab"><i class="fa fa-newspaper-o" aria-hidden="true"></i> <?php echo $tab_brand; ?></a></li>
				<li><a href="#tab-information" 	onclick="loadInfLangBlock(<?php echo $first_language; ?>);" data-toggle="tab"><i class="fa fa-newspaper-o" aria-hidden="true"></i> <?php echo $tab_information; ?></a></li>
				<li><a href="#tab-setting" 										  	data-toggle="tab"><i class="fa fa-cogs" aria-hidden="true"></i> <?php echo $tab_setting; ?></a></li>
				<li><a href="#tab-tools" 		onclick="loadBlock('tools');"	  	data-toggle="tab"><i class="fa fa-magic" aria-hidden="true"></i> Tools</a></li>
				<li><a href="#tab-logs" 		onclick="loadBlock('logs');" 		data-toggle="tab"><i class="fa fa-calendar-plus-o" aria-hidden="true"></i> <?php echo $tab_logs; ?></a></li>
	          </ul>
			  <!--Tabs UL Ends-->
			  <div class="tab-content">

			  	<div class="tab-pane active" id="tab-dashboard">
					<div id="dashboard-block"></div>
				</div>
				
				<!--PRODUCT-->
				<div class="tab-pane" id="tab-product">
					<ul class="nav nav-tabs" id="p-languages">
						<?php foreach ($languages as $language) { ?>
						<li><a href="#p-language<?php echo $language['language_id']; ?>" onclick="loadProLangBlock('<?php echo $language['language_id']; ?>');" data-toggle="tab"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a></li>
						<?php } ?>
					</ul>
					
					<div class="tab-content"> <!-- language tab content -->
						<?php foreach ($languages as $language) { ?>
							<div class="tab-pane" id="p-language<?php echo $language['language_id']; ?>">
								<div id="product-title-block<?php echo $language['language_id']; ?>"></div>
								<div id="product-desc-block<?php echo $language['language_id']; ?>"></div>
								<div id="product-keyword-block<?php echo $language['language_id']; ?>"></div>
								<div id="product-h1-block<?php echo $language['language_id']; ?>"></div>
								<div id="product-h2-block<?php echo $language['language_id']; ?>"></div>
								<div id="product-imgalt-block<?php echo $language['language_id']; ?>"></div>
								<div id="product-imgtitle-block<?php echo $language['language_id']; ?>"></div>	
							</div>
						<?php } ?>
					</div> <!-- language tab content end-->	
				
				</div>
				
				<!--CATEGORY-->
				<div class="tab-pane" id="tab-category">
					<ul class="nav nav-tabs" id="c-languages">
						<?php foreach ($languages as $language) { ?>
						<li><a href="#c-language<?php echo $language['language_id']; ?>" onclick="loadCatLangBlock('<?php echo $language['language_id']; ?>');" data-toggle="tab"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a></li>
						<?php } ?>
					</ul>
					
					<div class="tab-content"> <!-- language tab content -->
						<?php foreach ($languages as $language) { ?>
							<div class="tab-pane" id="c-language<?php echo $language['language_id']; ?>">
								<div id="category-title-block<?php echo $language['language_id']; ?>"></div>
								<div id="category-desc-block<?php echo $language['language_id']; ?>"></div>
								<div id="category-keyword-block<?php echo $language['language_id']; ?>"></div>
								<div id="category-h1-block<?php echo $language['language_id']; ?>"></div>
								<div id="category-h2-block<?php echo $language['language_id']; ?>"></div>
								<div id="category-imgalt-block<?php echo $language['language_id']; ?>"></div>
								<div id="category-imgtitle-block<?php echo $language['language_id']; ?>"></div>	
							</div>
						<?php } ?>
					</div> <!-- language tab content end-->	
				</div>
				
				<!--BRAND-->
				<div class="tab-pane" id="tab-brand">
					<ul class="nav nav-tabs" id="b-languages">
						<?php foreach ($languages as $language) { ?>
						<li><a href="#b-language<?php echo $language['language_id']; ?>" onclick="loadBraLangBlock('<?php echo $language['language_id']; ?>');" data-toggle="tab"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a></li>
						<?php } ?>
					</ul>
					
					<div class="tab-content"> <!-- language tab content -->
						<?php foreach ($languages as $language) { ?>
							<div class="tab-pane" id="b-language<?php echo $language['language_id']; ?>">
								<div id="brand-title-block<?php echo $language['language_id']; ?>"></div>
								<div id="brand-desc-block<?php echo $language['language_id']; ?>"></div>
								<div id="brand-keyword-block<?php echo $language['language_id']; ?>"></div>
								<div id="brand-h1-block<?php echo $language['language_id']; ?>"></div>
								<div id="brand-h2-block<?php echo $language['language_id']; ?>"></div>
								<div id="brand-imgalt-block<?php echo $language['language_id']; ?>"></div>
								<div id="brand-imgtitle-block<?php echo $language['language_id']; ?>"></div>	
							</div>
						<?php } ?>
					</div> <!-- language tab content end-->	
				</div>
				
				<!--INFORMATION-->
				<div class="tab-pane" id="tab-information">
					<ul class="nav nav-tabs" id="i-languages">
						<?php foreach ($languages as $language) { ?>
						<li><a href="#i-language<?php echo $language['language_id']; ?>" onclick="loadInfLangBlock('<?php echo $language['language_id']; ?>');" data-toggle="tab"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a></li>
						<?php } ?>
					</ul>
					
					<div class="tab-content"> <!-- language tab content -->
						<?php foreach ($languages as $language) { ?>
							<div class="tab-pane" id="i-language<?php echo $language['language_id']; ?>">
								<div id="information-title-block<?php echo $language['language_id']; ?>"></div>
								<div id="information-desc-block<?php echo $language['language_id']; ?>"></div>
								<div id="information-keyword-block<?php echo $language['language_id']; ?>"></div>
							</div>
						<?php } ?>
					</div> <!-- language tab content end-->	
				</div>
				
				<!--SETUP-->
				<div class="tab-pane" id="tab-setting">				   
					<div class="form-group">
						<label class="col-sm-3 control-label">Enable Logs?</label>
						<div class="col-sm-9">
							<input type="checkbox" data-toggle="toggle" data-onstyle="success" name="hb_onpage_logs" class="form-control" value="1" <?php echo ($hb_onpage_logs == '1')? 'checked':'' ; ?> />
						</div>
				   </div> 
				   
				   <div class="form-group">
						<label class="col-sm-3 control-label">Authentication Password Key</label>
						<div class="col-sm-9">
						  <input type="text" name="hb_onpage_authkey"  value="<?php echo $hb_onpage_authkey; ?>" class="form-control" />
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-sm-3 control-label">Run Script on Admin catalog Pages?</label>
						<div class="col-sm-9">
							<input type="checkbox" data-toggle="toggle" data-onstyle="success" name="hb_onpage_auto" class="form-control" value="1" <?php echo ($hb_onpage_auto == '1')? 'checked':'' ; ?> />
						</div>
				   </div> 
					
					<div class="form-group">
						<label class="col-sm-3 control-label">No of records to process in an attempt in Auto Mode</label>
						<div class="col-sm-9">
						  <input type="text" name="hb_onpage_autolimit"  value="<?php echo $hb_onpage_autolimit; ?>" class="form-control" />
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-sm-3 control-label">Cron Command</label>
						<div class="col-sm-9">
							<div class="well"><?php echo $hb_onpage_cron; ?></div>
							<p><a href="https://www.huntbee.com/documentation/docs/cron-job-setup/" target="_blank"><i class="fa fa-question-circle" aria-hidden="true"></i> How to setup cron job?</a></p>
						</div>
				   </div>

				</div>
				
				<!--TOOLS-->
				<div class="tab-pane" id="tab-tools">
					<div id="tools-block"></div>
				</div>
				
				<!--LOGS-->
				<div class="tab-pane" id="tab-logs">
					<div class="form-group">
						<div class="col-sm-4">
						<select class="form-control" id="logfiles">
							<?php foreach ($all_files as $logfile) { ?>
							<option value="<?php echo $logfile; ?>" <?php echo ($filename == $logfile)? 'selected':'' ?>><?php echo $logfile; ?></option>
							<?php } ?>
						</select>
						</div>
					</div>
					<div id="logs-block"></div>
				</div>
				
			  </div><!--tab-content block end-->
        </form>
		
		<!--ADD NEW TEMPLATE FORM START-->
		<div class="modal fade" id="add-template" tabindex="-1" role="dialog" aria-labelledby="add-templateLabel">
		  <div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><i class="fa fa-newspaper-o"></i> Add Template or Pattern</h4>
			  </div>
			  <div class="modal-body">
				  <div class="form-group col-sm-4">
					<label class="control-label">Page Type</label>
					<select id="page_type" class="form-control">
                        <option value="product" >Product Page</option>
                        <option value="category" >Category Page</option>
                        <option value="brand" >Brand Page</option>
						<option value="information" >Information Page</option>
                 	</select>
				  </div>
				  <div class="form-group col-sm-4">
					<label class="control-label">Element Type</label>
					<select id="element_type" class="form-control">
                        <option value="meta_title" >Meta Title</option>
                        <option value="meta_description" >Meta Description</option>
                        <option value="meta_keyword" >Meta Keyword</option>
						<option value="h1" >H1</option>
						<option value="h2" >H2</option>
						<option value="image_alt" >IMAGE ALT</option>
						<option value="image_title" >IMAGE Title</option>
                 	</select>
				  </div>
				  <div class="form-group col-sm-4">
					<label class="control-label">Language</label>
					<select id="language_id" class="form-control">
					<?php foreach ($languages as $language) { ?>
					<option value="<?php echo $language['language_id']; ?>" ><?php echo $language['name']; ?></option>
					<?php } ?>
                 	</select>
				  </div>
				  <div class="form-group">
					<label class="control-label">Template</label>
					<textarea class="form-control" id="template"></textarea> 
				  </div>
				  <div class="pr_info" id="short-codes-block"></div>
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" id="add-template-entry">ADD</button>
			  </div>
			  <div id="addentry-result" style="text-align:center;"></div>
			</div>
		  </div>
		</div>
		<!--ADD NEW TEMPLATE FORM END-->
		<!--PREVIEW TEMPLATE MODAL START-->
		<div class="modal fade" id="preview-modal" tabindex="-1" role="dialog">
		  <div class="modal-dialog modal-md" role="document">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><i class="fa fa-eye"></i> PREVIEW</h4>
			  </div>
			  <div class="modal-body">
			  		<div id="preview-block"></div>
			  </div>
			</div>
		  </div>
		</div>
		<!--PREVIEW TEMPLATE MODAL END-->
		
      </div>
    </div>
    <!--Panel Content End-->
    <!--Huntbee copyrights-->
    <div class="container-fluid">
      <center>
        <span class="help"><?php echo $heading_title; ?> - <?php echo $extension_version;?> &copy; <a href="https://www.huntbee.com/">WWW.HUNTBEE.COM</a> | <a href="https://www.huntbee.com/get-support">SUPPORT</a> | <a href="https://www.huntbee.com/documentation/docs/seo-on-page-tags-generator/" target="_blank">DOCUMENTATION</a></span>
      </center>
    </div>
    <!--Huntbee copyrights end-->
  </div>
  <!--Container 1 start -->
</div>
<!--Main Content block end-->
<style type="text/css"> <!--addtional css-->
.pr_infos, .pr_info, .pr_success, .pr_warning, .pr_error {
margin: 10px 0px;
padding:12px;
}
.pr_info {
    color: #00529B;
    background-color: #BDE5F8;
}
.pr_success {
    color: #4F8A10;
    background-color: #DFF2BF;
}
.pr_warning {
    color: #9F6000;
    background-color: #FEEFB3;
}
.pr_error {
    color: #D8000C;
    background-color: #FFBABA;
}
.pr_info i, .pr_success i, .pr_warning i, .pr_error i {
    margin:10px 22px;
    font-size:2em;
    vertical-align:middle;
}
</style>

<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>	   

<script type="text/javascript"><!--
$('#p-languages a:first').tab('show');
$('#c-languages a:first').tab('show');
$('#b-languages a:first').tab('show');
$('#i-languages a:first').tab('show');
//--></script>

<script type="text/javascript">
$(document).ready(function() {
	$('#dashboard-block').html('<div class="text-center"><i class="fa fa-refresh fa-spin fa-5x fa-fw"></i></div>');
	$('#dashboard-block').load('index.php?route=<?php echo $base_route; ?>/hb_onpage/dashboard&token=<?php echo $token; ?>&store_id=<?php echo $store_id; ?>');
	availableshortcodes();
});

function loadBlock(name){
	$('#'+name+'-block').html('<center><i class="fa fa-refresh fa-spin fa-3x fa-fw"></i></center>');
	$('#'+name+'-block').load('index.php?route=<?php echo $base_route; ?>/hb_onpage/'+name+'&token=<?php echo $token; ?>&store_id=<?php echo $store_id; ?>');
}

function loadProLangBlock(language_id){
	$("#product-title-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#product-desc-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#product-keyword-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#product-h1-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#product-h2-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#product-imgalt-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#product-imgtitle-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');

	$("#product-title-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=product&element_type=meta_title&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#product-desc-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=product&element_type=meta_description&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#product-keyword-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=product&element_type=meta_keyword&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#product-h1-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=product&element_type=h1&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#product-h2-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=product&element_type=h2&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#product-imgalt-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=product&element_type=image_alt&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#product-imgtitle-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=product&element_type=image_title&store_id=<?php echo $store_id; ?>&language_id="+language_id);
}

function loadCatLangBlock(language_id){
	$("#category-title-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#category-desc-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#category-keyword-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#category-h1-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#category-h2-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#category-imgalt-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#category-imgtitle-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');

	$("#category-title-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=category&element_type=meta_title&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#category-desc-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=category&element_type=meta_description&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#category-keyword-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=category&element_type=meta_keyword&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#category-h1-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=category&element_type=h1&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#category-h2-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=category&element_type=h2&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#category-imgalt-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=category&element_type=image_alt&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#category-imgtitle-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=category&element_type=image_title&store_id=<?php echo $store_id; ?>&language_id="+language_id);
}

function loadBraLangBlock(language_id){
	$("#brand-title-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#brand-desc-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#brand-keyword-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#brand-h1-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#brand-h2-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#brand-imgalt-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#brand-imgtitle-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');

	$("#brand-title-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=brand&element_type=meta_title&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#brand-desc-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=brand&element_type=meta_description&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#brand-keyword-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=brand&element_type=meta_keyword&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#brand-h1-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=brand&element_type=h1&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#brand-h2-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=brand&element_type=h2&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#brand-imgalt-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=brand&element_type=image_alt&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#brand-imgtitle-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=brand&element_type=image_title&store_id=<?php echo $store_id; ?>&language_id="+language_id);
}

function loadInfLangBlock(language_id){
	$("#information-title-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#information-desc-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
	$("#information-keyword-block"+language_id).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');

	$("#information-title-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=information&element_type=meta_title&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#information-desc-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=information&element_type=meta_description&store_id=<?php echo $store_id; ?>&language_id="+language_id);
	$("#information-keyword-block"+language_id).load("index.php?route=<?php echo $base_route; ?>/hb_onpage/loadblock&token=<?php echo $token; ?>&page_type=information&element_type=meta_keyword&store_id=<?php echo $store_id; ?>&language_id="+language_id);
}

</script>
<script type="text/javascript">
function loadpages(){
	$('#dashboard-block').load('index.php?route=<?php echo $base_route; ?>/hb_onpage/dashboard&token=<?php echo $token; ?>&store_id=<?php echo $store_id; ?>');
	$('#logs-block').load('index.php?route=<?php echo $base_route; ?>/hb_onpage/logs&token=<?php echo $token; ?>&store_id=<?php echo $store_id; ?>');
}
</script>

<script type="text/javascript">
$('#set-samples').on('click', function() {
	$('#msgoutput').html('');
	$.ajax({
		  url: 'index.php?route=<?php echo $base_route; ?>/hb_onpage/setsamples&token=<?php echo $token; ?>&store_id=<?php echo $store_id; ?>',
		  dataType: 'json',
		  beforeSend: function() {
				$('#set-samples').button('loading');
		  },
		  complete: function() {
				$('#set-samples').button('reset');
		  },
		  success: function(json) {
				if (json['success']) {
					  $('#msgoutput').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> '+json['success']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
					  loadProLangBlock(<?php echo $first_language; ?>);
					  loadCatLangBlock(<?php echo $first_language; ?>);
					  loadBraLangBlock(<?php echo $first_language; ?>);
					  loadInfLangBlock(<?php echo $first_language; ?>);
				}
		  }
	 });	
});

$('#add-template-entry').on('click', function() {
	$('#addentry-result').html('');
	$.ajax({
		  type: 'post',
		  url: 'index.php?route=<?php echo $base_route; ?>/hb_onpage/addtemplate&token=<?php echo $token; ?>&store_id=<?php echo $store_id; ?>',
		  data: {page_type : $('#page_type').val(), element_type : $('#element_type').val(), language_id : $('#language_id').val(), template : $('#template').val()},
		  dataType: 'json',
		  success: function(json) {
				if (json['success']) {
					  $('#addentry-result').html('<div class="pr_success"><i class="fa fa-check-circle"></i> '+json['success']+'</div>');
					  loadpages();
				}
				if (json['warning']) {
					  $('#addentry-result').html('<div class="pr_error">'+json['warning']+'</div>');
				}
		  },			
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}

	 });	
});

$('#page_type').on('change', function() {
	availableshortcodes();
	checkselection();
});
$('#element_type').on('change', function() {
	checkselection();
});

function availableshortcodes(){
	var page_type = $('#page_type').val();
	if (page_type == 'product') {
		var shortcodes = '{p} - Product Name, {c} - Category Assigned, {b} - Brand Assigned, {m} - Model, {u} - UPC, {t} - Tags, {xp} - Product Name (Cleaned), {xc} - Category Assigned (Cleaned), {xb} - Brand Assigned (Cleaned), {xm} - Model (Cleaned), {f} First line of description';
	}
	if (page_type == 'category') {
		var shortcodes = '{cn} - Category Name, {xcn} Category Name (Cleaned), {f} First line of description';
	}
	if (page_type == 'brand') {
		var shortcodes = '{bn} - Brand Name, {xbn} - Brand Name (Cleaned), {f} First line of description';
	}
	if (page_type == 'information') {
		var shortcodes = '{in}  - Information Title, {xin} - Information Title (Cleaned), {f} First line of description';
	}
	$('#short-codes-block').html('<b>Short-codes:</b> '+shortcodes);
}

function setfields(page_type, element_type, language_id){
	$('#page_type').val(page_type);
	$('#element_type').val(element_type);
	$('#language_id').val(language_id);
	checkselection();
	availableshortcodes();
}

function checkselection(){
	var page_type = $('#page_type').val();
	var element_type = $('#element_type').val();
	
	if (page_type == 'information' && (element_type == 'h1' || element_type == 'h2' || element_type == 'image_alt' || element_type == 'image_title')){
		$('#add-template-entry').attr("disabled", true);
	}else{
		$('#add-template-entry').removeAttr("disabled");
	}
}

function deletetemplate(id, block, refreshlink){
	$('#msgoutput').html('<div class="text-center"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></div>');
	$.ajax({
		  type: 'post',
		  url: 'index.php?route=<?php echo $base_route; ?>/hb_onpage/deletetemplate&token=<?php echo $token; ?>&store_id=<?php echo $store_id; ?>',
		  data: {id : id},
		  dataType: 'json',
		  success: function(json) {
				if (json['success']) {
					$('#msgoutput').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> '+json['success']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
					$("#"+block).html('<center><i class="fa fa-spinner fa-pulse fa-fw"></i></center>');
					$("#"+block).load(refreshlink);
				}
		  }
	 });
}

function previewtemplate(id){
	$('#preview-block').html('<div class="text-center"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></div>');
	$.ajax({
		  type: 'post',
		  url: '../index.php?route=extension/hbseo/onpage_tags_generator/preview',
		  data: {template_id: id, authkey: '<?php echo $hb_onpage_authkey; ?>', store_id: '<?php echo $store_id; ?>'},
		  dataType: 'json',
		  success: function(json) {
				if (json['success']) {
					  $('#preview-block').html('<div class="pr_success">' +json['success'] + '</div>');
				}
				if (json['count']) {
					  $('#preview-block').append('<div class="pr_info">' +json['count'] + '</div>');
				}
		  }
	 });
	$('#preview-modal').modal('show');
}

function generateElement(page_type, element) {
	$('#msgoutput').html('<div class="alert alert-info text-center"><i class="fa fa-cog fa-spin fa-fw"></i> Generating... </div>');
	$.ajax({
		type: 'post',
		url: '../index.php?route=extension/hbseo/onpage_tags_generator/generate_element&store_id=<?php echo $store_id; ?>',
		data: {page_type: page_type, element: element, authkey: '<?php echo $hb_onpage_authkey; ?>'},
		dataType: 'json',
		success: function(json) {
			$('.alert, .text-danger').remove();

			if (json['error']) {
				if (json['error']) {
					$('#msgoutput').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}
			}

			if (json['success']) {
				$('#msgoutput').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i>  ' + json['success'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
			}
			
			if (json['next']) {
				generateElement(page_type, element);
			}

		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
	$('#dashboard-block').load('index.php?route=<?php echo $base_route; ?>/hb_onpage/dashboard&token=<?php echo $token; ?>&store_id=<?php echo $store_id; ?>');
}

function cleartags(page_type, element){
	$('#msgoutput').html('<div class="text-center"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></div>');
	$.ajax({
		  type: 'post',
		  url: 'index.php?route=<?php echo $base_route; ?>/hb_onpage/cleartags&token=<?php echo $token; ?>&store_id=<?php echo $store_id; ?>',
		  data: {page_type: page_type, element: element},
		  dataType: 'json',	
		  success: function(json) {
				if (json['success']) {
					  $('#msgoutput').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i>  '+json['success']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
					  loadBlock('dashboard');
				}
		  },			
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}

	 });
}

function fixLanguageEntries(){
	$('#msgoutput').html('<div class="text-center"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></div>');
	$.ajax({
		  url: 'index.php?route=<?php echo $base_route; ?>/hb_onpage/fixlanguageentries&token=<?php echo $token; ?>&store_id=<?php echo $store_id; ?>',
		  dataType: 'json',
		  success: function(json) {
				if (json['success']) {
					$('#msgoutput').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> '+json['success']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
					loadBlock('tools');
				}
		  }
	 });
}

function clearMetaTitleIssues(page_type){
	$('#msgoutput').html('<div class="text-center"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></div>');
	$.ajax({
		  url: 'index.php?route=<?php echo $base_route; ?>/hb_onpage/clearmetatitleissues&token=<?php echo $token; ?>&store_id=<?php echo $store_id; ?>&page_type='+page_type,
		  dataType: 'json',
		  success: function(json) {
				if (json['success']) {
					$('#msgoutput').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> '+json['success']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
					loadBlock('tools');
				}
		  }
	 });
}

function clearMetaDescIssues(page_type){
	$('#msgoutput').html('<div class="text-center"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></div>');
	$.ajax({
		  url: 'index.php?route=<?php echo $base_route; ?>/hb_onpage/clearmetadescissues&token=<?php echo $token; ?>&store_id=<?php echo $store_id; ?>&page_type='+page_type,
		  dataType: 'json',
		  success: function(json) {
				if (json['success']) {
					$('#msgoutput').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> '+json['success']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
					loadBlock('tools');
				}
		  }
	 });
}
</script>

<script type="text/javascript">
$('#store').on('change', function() {
	window.location.href = 'index.php?route=<?php echo $base_route; ?>/hb_onpage&token=<?php echo $token; ?>&store_id='+$('#store').val();
});
</script>
<?php echo $footer; ?>