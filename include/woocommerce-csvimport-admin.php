<?php
class woocsv_import_admin {

	public function __construct() {
		add_action('admin_menu', array($this,'admin_menu'));
	}

	public function admin_menu(){
		//add main menu page
		add_menu_page('CSV Import', 'CSV Import', 'manage_options', 'woocsv_import', array($this,'main_page'),'',58);
		//add settings page
		add_submenu_page( 'woocsv_import', 'Settings', 'Settings', 'manage_options', 'woocsv_settings', array($this,'settings'));
	}

	public function main_page(){
	//some bassic checks
	$upload_dir = wp_upload_dir();
	if (!is_writable($upload_dir['basedir'].'/csvimport/'))
		woocsv_admin_notice ('Import directory niet gevonden of hij is niet schrijfbaar. check of /uploads/csvimport bestaat');
		
	//handle zip uploads
	if ( isset( $_REQUEST['handle_csv_import_zip']) && check_admin_referer('handle_csv_import_zip')) 
		woocsv_handle_zip_import();
	//handle manual uploads
	if ( isset( $_REQUEST['handle_csv_import_random']) && check_admin_referer('handle_csv_import_random')) 
		wppcsv_handle_csv_import_random();
	//handle fixed uploads
	if ( isset( $_REQUEST['handle_csv_import_fixed']) && check_admin_referer('handle_csv_import_fixed')) 
		woocsv_handle_fixed_import();

	
	//main page
		echo '<div class="wrap"><div id="icon-options-general" class="icon32"><br></div>
                <h2>Import</h2></div>';
     ?>
	<script>
	jQuery(document).ready(function() {
		    jQuery( "#tabs" ).tabs();
		  });

	</script>
	<div id="tabs">
		<ul>
			<li><a href="#tabs-1"><?php echo __('Select a zip file'); ?></a></li>
			<li><a href="#tabs-2"><?php echo __('Select youre own files'); ?></a></li>
			<li><a href="#tabs-3"><?php echo __('You already uploaded the files');?></a></li>
		</ul>
		<div id="tabs-1">
			<?php
			echo '<h3>'.__('Upload a zip file').'</h3>';
			echo '<form id="handle_csv_import_zip" name="handle_csv_import_zip" action="" method="POST" enctype="multipart/form-data">';
			echo '<input id="zip_file" name="zip_file" type="file" accept="application/zip"> <br />';
			echo '<input name="handle_csv_import_zip" type="submit" value="start">';
			echo wp_nonce_field('handle_csv_import_zip');
			echo '</form>';
			?>
		</div>
		<div id="tabs-2">
			<?php
			echo '<h3>'.__('Upload selected files from').'</h3>';
			echo '<p>'.__('We will only proccess csv and jpg files').'</p>';
			echo '<form id="handle_csv_import_random" name="handle_csv_import_random" method="POST" enctype="multipart/form-data">';
			echo __('jpg en csv:').'<input id="all_files" name="all_files[]" type="file" multiple> <br />';
			echo '<input name="handle_csv_import_random" type="submit" value="start">';
			echo wp_nonce_field('handle_csv_import_random');
			echo '</form>';
			?>
		</div>
		<div id="tabs-3">
			<?php
			echo '<h3>'.__('You already uploaded the files').'</h3>';
			echo '<p>'.__('We expect it to be the in uploads/csvimport/fixed').'</p>';
			echo '<form id="handle_csv_import_fixed" name="handle_csv_import_fixed" method="POST">';
			echo __('Override fixed directory with uploads').'<input type="text" name="fixed_dir" value="/csvimport/fixed"><br />';
			echo '<input name="handle_csv_import_fixed" type="submit" value="start">';
			echo wp_nonce_field('handle_csv_import_fixed');
			echo '</form>';
			?>
		</div>
	</div>
	<?php
	}

	public function settings(){
	global $woocsv_options;
	$upload_dir = wp_upload_dir();

	//handle form for creation of import dir
	if ( isset( $_REQUEST['create_import_directory']) && check_admin_referer('create_import_directory')) {
		mkdir($upload_dir['basedir'] .'/csvimport/');
		mkdir($upload_dir['basedir'] .'/csvimport/fixed/');
	}
	//handle form for images
	if ( isset( $_REQUEST['delete_images_import']) && check_admin_referer('delete_images_import')) {
		$woocsv_options['deleteimages'] = $_POST['images_import'];
		update_option( 'csvimport-options', $woocsv_options );
	}
	
	//hanlde form for fields seperator
	if ( isset( $_REQUEST['fieldseperator']) && check_admin_referer('fieldseperator')) {
		$woocsv_options['fieldseperator'] = $_POST['fieldseperatorvalue'];
		update_option( 'csvimport-options', $woocsv_options );
	}	

	if (!is_writable($upload_dir['basedir'].'/csvimport/'))
		woocsv_admin_notice ('Import directory niet gevonden of hij is niet schrijfbaar. check of /uploads/csvimport bestaat');
		
		
		echo '<div class="wrap"><div id="icon-options-general" class="icon32"><br></div>
                <h2>Settings</h2></div>';
        
        //import directory
        echo '<h3>Create import directory</h3>';                        
        if (!is_dir($upload_dir['basedir'] .'/csvimport/')) {
			echo '<form id="create_import_directory" name="create_import_directory" method="POST">';
			echo '<input name="create_import_directory" type="submit" value="create">';
			echo wp_nonce_field('create_import_directory');
			echo '</form>';
	        
        }
	    echo '<p>Directory is there!</p>';
	  
	    //what to do with images
	    ?>
	    <h3>What to do with images?</h3>
	    <p>You can choose if you want to keep the existing images or delete them before adding the new ones.</p>
	    <form id="delete_images_import" name="delete_images_import" method="POST">
	    <select id="images_import" name ="images_import">
	    <option value=0 <?php if ($woocsv_options['deleteimages'] == 0) echo 'selected'; ?> >append images to product</option>
	    <option value=1 <?php if ($woocsv_options['deleteimages'] == 1) echo 'selected'; ?> >remove all images before uploading new ones</option>
	    </select>
		<input name="delete_images_import" type="submit" value="Save">
		<?php echo wp_nonce_field('delete_images_import'); ?>
		</form>
		
		<h3>What to do with the field seperator?</h3>
	    <p>You can choose what field seperator you want to use</p>
	    <form id="fieldseperator" name="fieldseperator" method="POST">
		<input type="text" name="fieldseperatorvalue" id="fieldseperatorvalue"" size="1" value="<?php echo $woocsv_options['fieldseperator']; ?>">
		<input name="fieldseperator" type="submit" value="Save">
		<?php echo wp_nonce_field('fieldseperator'); ?>
		</form>
		
		
		
				<h3>How does the CSV look like?</h3>
		<p>It looks like this:</p>
		<pre>
		<code>
title,description,short_description,category,stock,price,regular_price,sales_prices,weight,length,width,height,sku,picture,tags
product1,very nice product,nice product,cat1->subcat1->subsubcat1|cat2,10,100,100,90,2,1,2,3,123456789,product1.jpg|product2.jpg|product3.jpg,tag1|tag2|tag3
product2,very nice product two,nice product two,cat2,5,200,200,190,2,4,5,6,46345,product1.jpg,tag1|tag4
product3,very nice product three,nice product three,cat2,5,200,200,190,2,4,5,6,98765,http://www.allaerd.org/plugins/product4.jpg,tag2|tag5
</code>
		</pre>
		<?php
    }
    


}

$csv_import_admin = new woocsv_import_admin();