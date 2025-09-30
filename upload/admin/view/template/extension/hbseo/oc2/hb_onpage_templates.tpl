<table class="table table-hover table-bordered">
	<thead>
		<tr class="info">
			<td><?php echo $table_heading; ?> <a class="btn btn-sm btn-primary" data-toggle="modal" data-target="#add-template" title="Add Template" onclick="setfields('<?php echo $page_type; ?>','<?php echo $element_type; ?>','<?php echo $language_id; ?>');"><i class="fa fa-plus-circle"></i> Add Template</a></td>
			<td class="text-center col-sm-2">ACTION</td>
		</tr>
	</thead>
	<tbody>
		<?php if ($templates) { ?>
		  <?php foreach ($templates as $template) { ?>
			<tr>
				<td><?php echo $template['template']; ?></td>
				<td class="text-center">
					<a class="btn btn-sm btn-primary" title="Preview Template" onclick="previewtemplate('<?php echo $template['id']; ?>');"><i class="fa fa-eye"></i></a>
					<a class="btn btn-sm btn-danger" title="Delete Template" onclick="deletetemplate('<?php echo $template['id']; ?>','<?php echo $block; ?>','<?php echo $refreshlink; ?>');"><i class="fa fa-trash-o"></i></a>
				</td>
			</tr>
		  <?php } ?>
		<?php } else { ?>
			<tr>
				<td colspan="2" style="color:#FF0000;">No templates found. <a class="btn" data-toggle="modal" data-target="#add-template" onclick="setfields('<?php echo $page_type; ?>','<?php echo $element_type; ?>','<?php echo $language_id; ?>');">Please Add Template</a></td>
			</tr>
		<?php } ?>
	</tbody>
</table>