<?php foreach ($pages as $page) { ?>
<table class="table table-hover table-bordered">
	<thead>
		<tr class="info">
			<td class="col-sm-2"><?php echo $page['title']; ?></td>
			<td class="text-center">Total Items</td>
			<?php foreach ($languages as $language) { ?>
				<td class="text-center"><img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></td>
			<?php } ?>
			<td class="text-center">Action</td>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($page['items'] as $key => $value) { ?>
		<tr>
			<td><strong><?php echo $value; ?></strong></td>
			<td class="text-center"><?php echo $page['total_items']; ?></td>
			<?php foreach ($languages as $language) { ?>
				<td class="text-center <?php echo ($page['total_items'] == $page['counts'][$key][$language['language_id']])?'success':'danger'; ?>"><?php echo $page['counts'][$key][$language['language_id']]; ?></td>
			<?php } ?>
			<td class="text-center">
				<a class="btn btn-sm btn-primary" onclick="generateElement('<?php echo $page['code']; ?>','<?php echo $key; ?>');"><i class="fa fa-play-circle-o"></i> Generate</a>
				<a class="btn btn-sm btn-danger" onclick="cleartags('<?php echo $page['code']; ?>','<?php echo $key; ?>')" ><i class="fa fa-trash"></i> Clear</a>
			</td>
		</tr>
		<?php } ?>
	</tbody>
</table>
<?php } ?>