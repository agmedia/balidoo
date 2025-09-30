<div class="form-group">
	<label class="col-sm-4 control-label">Invalid Language Entries in Product / Category / Manufacturer / Information tables</label>
	<div class="col-sm-8">
		<?php if ($product_language_check) { ?>
				<div class="alert alert-danger"><i class="fa fa-exclamation"></i> <?php echo $product_language_check; ?> invalid entries/rows found in products table</div>
			<?php } ?>
			
			<?php if ($category_language_check) { ?>
				<div class="alert alert-danger"><i class="fa fa-exclamation"></i> <?php echo $category_language_check; ?> invalid entries/rows found in category table</div>
			<?php } ?>
			
			<?php if ($brand_language_check) { ?>
				<div class="alert alert-danger"><i class="fa fa-exclamation"></i> <?php echo $brand_language_check; ?> invalid entries/rows found in manufacturer table</div>
			<?php } ?>
			
			<?php if ($information_language_check) { ?>
				<div class="alert alert-danger"><i class="fa fa-exclamation"></i> <?php echo $information_language_check; ?> invalid entries/rows found in information table</div>
			<?php } ?>
			
			<?php if ($language_check_fine) { ?>
				<div class="alert alert-success"><i class="fa fa-check"></i> Looks Great</div>
			<?php } ?>
			
			<?php if (!$language_check_fine) { ?>
				<a class="btn btn-primary col-sm-6" onclick="fixLanguageEntries()"><i class="fa fa-magic"></i> Fix all</a>
			<?php } ?>
	</div>
</div> 

<div class="form-group">
	<label class="col-sm-4 control-label">Product Meta Title Length Checkup</label>
	<div class="col-sm-8">
			<?php if ($product_title_check) { ?>
				<div class="alert alert-warning"><i class="fa fa-exclamation"></i> <?php echo $product_title_check; ?> product meta-title length is either below 50 or above 60. Google typically displays the first 50 - 60 characters of a title tag</div>
				<a class="btn btn-primary col-sm-6" onclick="clearMetaTitleIssues('product')"><i class="fa fa-magic"></i> Clear these values</a>
			<?php } else { ?>
				<div class="alert alert-success"><i class="fa fa-check"></i> Looks Great</div>
			<?php } ?>
	</div>
</div>

<div class="form-group">
	<label class="col-sm-4 control-label">Category Meta Title Length Checkup</label>
	<div class="col-sm-8">
			<?php if ($category_title_check) { ?>
				<div class="alert alert-warning"><i class="fa fa-exclamation"></i> <?php echo $category_title_check; ?> category meta-title length is either below 50 or above 60. Google typically displays the first 50 - 60 characters of a title tag</div>
				<a class="btn btn-primary col-sm-6" onclick="clearMetaTitleIssues('category')"><i class="fa fa-magic"></i> Clear these values</a>
			<?php } else { ?>
				<div class="alert alert-success"><i class="fa fa-check"></i> Looks Good</div>
			<?php } ?>
	</div>
</div>

<div class="form-group">
	<label class="col-sm-4 control-label">Manufacturer Meta Title Length Checkup</label>
	<div class="col-sm-8">
			<?php if ($brand_title_check) { ?>
				<div class="alert alert-warning"><i class="fa fa-exclamation"></i> <?php echo $brand_title_check; ?> manufacturer meta-title length is either below 50 or above 60. Google typically displays the first 50 - 60 characters of a title tag</div>
				<a class="btn btn-primary col-sm-6" onclick="clearMetaTitleIssues('brand')"><i class="fa fa-magic"></i> Clear these values</a>
			<?php } else { ?>
				<div class="alert alert-success"><i class="fa fa-check"></i> Looks Good</div>
			<?php } ?>
	</div>
</div>

<div class="form-group">
	<label class="col-sm-4 control-label">Information Meta Title Length Checkup</label>
	<div class="col-sm-8">
			<?php if ($information_title_check) { ?>
				<div class="alert alert-warning"><i class="fa fa-exclamation"></i> <?php echo $information_title_check; ?> information meta-title length is either below 50 or above 60. Google typically displays the first 50 - 60 characters of a title tag</div>
				<a class="btn btn-primary col-sm-6" onclick="clearMetaTitleIssues('information')"><i class="fa fa-magic"></i> Clear these values</a>
			<?php } else { ?>
				<div class="alert alert-success"><i class="fa fa-check"></i> Looks Good</div>
			<?php } ?>
	</div>
</div>

<div class="form-group">
	<label class="col-sm-4 control-label">Product Meta Description Length Checkup</label>
	<div class="col-sm-8">
			<?php if ($product_md_check) { ?>
				<div class="alert alert-warning"><i class="fa fa-exclamation"></i> <?php echo $product_title_check; ?> product meta-description length is below 100. Google generally truncates to ~155 - 160 characters.  We recommend descriptions between 100 - 160 characters.</div>
				<a class="btn btn-primary col-sm-6" onclick="clearMetaDescIssues('product')"><i class="fa fa-magic"></i> Clear these values</a>
			<?php } else { ?>
				<div class="alert alert-success"><i class="fa fa-check"></i> Looks Great</div>
			<?php } ?>
	</div>
</div>

<div class="form-group">
	<label class="col-sm-4 control-label">Category Meta Description Length Checkup</label>
	<div class="col-sm-8">
			<?php if ($category_md_check) { ?>
				<div class="alert alert-warning"><i class="fa fa-exclamation"></i> <?php echo $category_title_check; ?> category meta-description length is below 100. Google generally truncates to ~155 - 160 characters.  We recommend descriptions between 100 - 160 characters.</div>
				<a class="btn btn-primary col-sm-6" onclick="clearMetaDescIssues('category')"><i class="fa fa-magic"></i> Clear these values</a>
			<?php } else { ?>
				<div class="alert alert-success"><i class="fa fa-check"></i> Looks Good</div>
			<?php } ?>
	</div>
</div>

<div class="form-group">
	<label class="col-sm-4 control-label">Manufacturer Meta Description Length Checkup</label>
	<div class="col-sm-8">
			<?php if ($brand_md_check) { ?>
				<div class="alert alert-warning"><i class="fa fa-exclamation"></i> <?php echo $brand_title_check; ?> manufacturer meta-description length is below 100. Google generally truncates to ~155 - 160 characters.  We recommend descriptions between 100 - 160 characters.</div>
				<a class="btn btn-primary col-sm-6" onclick="clearMetaDescIssues('brand')"><i class="fa fa-magic"></i> Clear these values</a>
			<?php } else { ?>
				<div class="alert alert-success"><i class="fa fa-check"></i> Looks Good</div>
			<?php } ?>
	</div>
</div>

<div class="form-group">
	<label class="col-sm-4 control-label">Information Meta Description Length Checkup</label>
	<div class="col-sm-8">
			<?php if ($information_md_check) { ?>
				<div class="alert alert-warning"><i class="fa fa-exclamation"></i> <?php echo $information_title_check; ?> information meta-description length is below 100. Google generally truncates to ~155 - 160 characters.  We recommend descriptions between 100 - 160 characters.</div>
				<a class="btn btn-primary col-sm-6" onclick="clearMetaDescIssues('information')"><i class="fa fa-magic"></i> Clear these values</a>
			<?php } else { ?>
				<div class="alert alert-success"><i class="fa fa-check"></i> Looks Good</div>
			<?php } ?>
	</div>
</div>
