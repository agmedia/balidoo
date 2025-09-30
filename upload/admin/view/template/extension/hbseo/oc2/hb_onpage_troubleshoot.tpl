<table class="table table-hover table-bordered">
	<thead>
		<tr style="background-color:#007fda; color:#FFFFFF;">
			<td class="col-sm-6">Filename</td>
			<td class="text-center">Search Data</td>
			<td class="text-center">Comment</td>
		</tr>
	</thead>
	<tbody>
		<?php if($finds) { ?>
		<?php foreach ($templates as $template) { ?>
			<tr class="success"><th colspan="3">Template: <?php echo $template; ?></th></tr>
			<?php foreach ($finds[$template] as $find) { ?>
			<tr>
				<td style="word-break:break-all;"><strong><?php echo $find['filename']; ?></strong></td>
				<td><?php echo $find['search_string']; ?></td>
				<td class="text-center"><?php echo $find['comment']; ?></td>
			</tr>
			<?php } ?>
		<?php } ?>
		<?php } ?>
	</tbody>
</table>