<?php
/*
	** import preview **
*/
?>
<div class="wrap">
	<h2><?php echo __('Import','woocsv'); ?></h2>
	<ul class="subsubsub">
		<li><a href="<?php echo admin_url('admin.php?page=woocsv_import&amp;tab=import');?>" class="current"><?php echo __('Import','woocsv'); ?></a> |</li>
		<li><a href="<?php echo admin_url('admin.php?page=woocsv_import&amp;tab=headers');?>"><?php echo __('Headers','woocsv'); ?></a></li>
	</ul>
	<br class="clear">
	<hr>

<?php 
	global $woocsv_import;
	
		//upload the file
		$filename = $woocsv_import->handle_file_upload($_FILES['csvfile']['tmp_name'], $_FILES['csvfile']['name']);
		if (!$filename) wp_die(__('Could not upload file','woocsv'));

		$handle = fopen($filename, 'r');
	
		$row = 0;
		$csvcontent = '';
		while ( ( $line = fgetcsv( $handle, 0 ,$woocsv_import->get_separator() ) ) !== FALSE ) {
			
			//utf-8 support
			// @since 3.0.5 added check if the $line is an array
			if ( is_array($line) && get_option('woocsv_convert_to_utf8') ) {
				$line = array_map("utf8_encode", $line);
			}
				
			$csvcontent[] = $line;
			$row ++;
		}
	
		$length = count($csvcontent[0]);

		if (count($csvcontent[0]) == 1 ) {
			echo '<h2>'.__('I think you have the wrong separator','woocsv').'</h2>';
			echo '<p>'.__('Please goto the settings page and change your separator!','woocsv').'</p>';
			return;
		}
?>
		<div id="importPreview">
		<h2><?php echo __('Import preview','woocsv'); ?></h2>
		<table class="widefat">
		<thead>
			<tr>
				<th><?php echo __('Header','woocsv'); ?></th>
				<th><?php echo __('Row 1','woocsv'); ?></th>
				<th><?php echo __('Row 2','woocsv'); ?></th>
				<th><?php echo __('Row 3','woocsv'); ?></th>
				<th><?php echo __('Row 4','woocsv'); ?></th>
				<th><?php echo __('Row 5','woocsv'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php for ($i = 0; $i <= $length-1; $i++) : ?>
		<tr class="<?php echo ($i % 2 == 0)?'':'alt';?>">
			<td class="row-title"><?php echo (isset($header[$i]))?$header[$i]:''; ?></td>
			<td><?php if (isset($csvcontent[0 + $woocsv_import->get_skip_first_line() ][$i])) 
				echo $csvcontent[ 0 + $woocsv_import->get_skip_first_line() ][$i];?>
			</td>
			<td><?php if (isset($csvcontent[1 + $woocsv_import->get_skip_first_line() ][$i])) 
				echo $csvcontent[ 1 + $woocsv_import->get_skip_first_line() ][$i];?>
			</td>
			<td><?php if (isset($csvcontent[2 + $woocsv_import->get_skip_first_line() ][$i])) 
				echo $csvcontent[ 2 + $woocsv_import->get_skip_first_line() ][$i];?>
			</td>
			<td><?php if (isset($csvcontent[3 + $woocsv_import->get_skip_first_line() ][$i])) 
				echo $csvcontent[ 3 + $woocsv_import->get_skip_first_line() ][$i];?>
			</td>
			<td><?php if (isset($csvcontent[4 + $woocsv_import->get_skip_first_line() ][$i])) 
				echo $csvcontent[ 4 + $woocsv_import->get_skip_first_line() ][$i];?>
			</td>
		</tr>
		<?php endfor;?>
		</tbody>
		</table>
		<form id="runImportForm"  method="POST">
			<input type="hidden" name="currentrow" value="0" />
			<input type="hidden" name="blocksize" value="<?php echo $woocsv_import->get_blocksize(); ?>" />
			<input type="hidden" name="rows" value="<?php echo $row;?>" />
			<input type="hidden" name="filename" value="<?php echo $filename; ?>" />
			<input type="hidden" name="action" value="run_import">
			<br class="clear">
			<button type="submit" class="button-primary"><?php echo __('start','woocsv'); ?></button>
		</form>
</div>

<div class="postbox" style="margin:1em 0 0 0;">
	<div class="inside">
		<progress id="woocsv_import_progress" style="width:100%;" max="<?php echo $row; ?>" value="0"></progress>
		<div id="import_log">
		</div>
	</div>
</div>

