<?php 
ini_set('set_time_limit', 0);
ini_set('max_execution_time', 0);
ini_set("memory_limit",-1);
$upload_size_unit = $max_upload_size = wp_max_upload_size();
$sizes = array( 'KB', 'MB', 'GB' );		
for ( $u = -1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u++ ) {
	$upload_size_unit /= 1024;
}
if ( $u < 0 ) {
	$upload_size_unit = 0;
	$u = 0;
} else {
	$upload_size_unit = (int) $upload_size_unit;
}

$msg= __("CSV file size large. Maximum allowed upload file size is",TBI_DOMAIN)." ".esc_html($upload_size_unit)." ".esc_html($sizes[$u]);
?>
<script type="text/javascript">	
var wp_max_upload_size='<?php echo wp_max_upload_size()?>';
var upload_size_unit='<?php echo $upload_size_unit;?>';	
</script>
<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-posts-post"></div>
	<h2><?php echo __('Bulk Import/Export',TBI_DOMAIN); ?></h2>
	<p class="tevolution_desc"><?php echo __('Save your time by directly performing Import/export process here (it only accepts .csv format). You can import/export thousands of records of your desired post type in no time.',TBI_DOMAIN);?><strong> <?php echo __('Note',TBI_DOMAIN); ?>: </strong><?php echo __('For a successful import/export you will have to follow the sample CSV format, there is a link below to download the sample file.',TBI_DOMAIN); ?></p>	
	
	<div class="updated fade" id="csv_import_message" style="display:none;"></div>
	<?php
	if(isset($_REQUEST['ptype']) && $_REQUEST['ptype'] == "csvdl"){
		if(file_exists(BULKIMPORT_DIR."csvdl.php")){
			include_once(BULKIMPORT_DIR."csvdl.php");
		}
	}
	if(isset($_POST['start']) && $_POST['start']!="" && $_POST['start']!=0 && isset($_SESSION['csv_data']) && $_SESSION['csv_data']!=""){
		$inserted = $_POST['start'];
		$total_record = $_SESSION['total_record'];
		$completed="";
		$imported = ($_SESSION['imported'] > 0) ? $_SESSION['imported'] : 0;
		$updated  = ($_SESSION['updated'] > 0 ) ? $_SESSION['updated'] : 0;
		$skipped  = ($_SESSION['skipped'] > 0 ) ? $_SESSION['skipped'] : 0;
		if($inserted==$total_record ){
			$completed = "<span style='color:green'>&nbsp;&nbsp; Import process completed.</span>";
			unset($_SESSION['imported']);
			unset($_SESSION['updated']);
			unset($_SESSION['skipped']);
			unset($_SESSION['total_record']);
			$_SESSION['imported']="";
			$_SESSION['updated']="";
			$_SESSION['skipped']="";
		}		
		echo '<div class="updated fade" style="padding:10px;margin:0 0 10px; font-weight:bold;">';
		echo sprintf(__("%s imported, %s updated, %s skipped of %s posts. %s",TBI_DOMAIN),$imported,$updated,$skipped,$total_record,$completed );
		echo '</div>';
	}
	if(isset($_REQUEST['structure_error']) && $_REQUEST['structure_error']!="" && $_REQUEST['structure_error']==1){
		echo '<div  id="message" class="error" style="padding:10px;margin:0 0 10px;">';
		$download = "<a href='".get_bloginfo("url")."/wp-admin/admin.php?page=bulk_upload&ptype=csvdl' style='color:#21759B'>download</a>";
          echo sprintf(__("csv file structure doesn't match. Please download sample csv file to see required structure.",TBI_DOMAIN),$download);
		echo '</div>';
	}
	do_action('tevolution_before_bulk_upload');?>
	<!-- It's section to export csv form BOF-->
	<div class="tevo_sub_title"><?php echo __('CSV Import',TBI_DOMAIN); ?> </div>
	<form action="<?php echo site_url('/wp-admin/admin.php')?>?page=bulk_upload" method="post" name="bukl_upload_frm" enctype="multipart/form-data" id="bukl_upload_frm" onsubmit="return chek_file();">
	     <input type="hidden" name="ptype" id="ptype" value="post"/>
          <p class="tevolution_desc"><?php echo __('Directly upload all the content of your desired custom post type (e.g. events, places, posts etc) here and check under its section located at your WordPress menu panel.(e.g. If you upload data for events then check under events section in your wp-admin panel)',TBI_DOMAIN)?></p>
          <table class="form-table">
               <tbody>
                    <tr>
                    	<th><?php echo __('Select post type',TBI_DOMAIN);?></th>
                    <td>
                    <?php
                    $posttaxonomy = get_option("templatic_custom_post");
                    $e=0;
					if(!empty($posttaxonomy)){
                    echo "<ul class='hr_input_radio'>";
                    foreach(@$posttaxonomy as $key=>$post_types){
							if($key=='admanager'){
								continue;	
							}
						?>
                    	<li><label><input type="radio" id="my_post_type" name="my_post_type" value="<?php echo $key;?>" <?php if($e == "0" ){echo "checked='checked'";}?> /> <?php echo $post_types['label'];?></label> <?php do_action('tevolution_'.$key.'_sample_csvfile');?></li>
                    <?php 
                    $e++;
                    } 
					}
                    echo '</ul>';
                    ?>
                    <div id="csv_import_id">
                         <input name="csv_import" id="csv_import" class="csv_import" type="file"  value="" />
                         <br /><br/>
                         <input type="submit" class="button button-primary" name="submit" id="submit" value="<?php echo __('Import csv',TBI_DOMAIN); ?>"/>
                         <div id="status" style="padding:0 0 0 5px;color:red"></div>
                         <div id="csv_status" style="text-align:center;margin-left:10px;color:red"></div>
                         <span id="read" style="font-weight:bold;color:black"></span>
                    </div>
                    <p class="description"><i><?php echo __('Download the sample file to see the correct structure of the .csv file. To use bulk upload with custom fields simply add them as new columns inside the .csv file. Add them last (at the end).', TBI_DOMAIN);?> </i></p>
                    </td>
                    </tr>
               </tbody>
          </table>
	</form>
	<div class="tevo_sub_title"><?php echo __('CSV Export',TBI_DOMAIN); ?></div>
	<table class="form-table">
		<tbody><p class="tevolution_desc"><?php echo __('Directly download all the content of your desired custom post type (e.g. events, places, posts etc) from here and save it on your hard drive.',TBI_DOMAIN)?>
			<tr>
				<th><?php echo __('Select post type',TBI_DOMAIN);?> </th>
			<td>
                    <form name="templatic_bulk_upload" method="post" action="<?php echo plugins_url(BULKIMPORT_FOLDER_NAME.'/export_to_CSV.php');?>" style="padding-top:10px;" >
					<?php $posttaxonomy = get_option("templatic_custom_post");
					$e=0;
					if(!empty($posttaxonomy)){
					echo "<ul class='hr_input_radio'>";
                         foreach(@$posttaxonomy as $key=>$post_types){
							 if($key=='admanager'){
								continue;	
							}
							 ?>
                         	<li><label><input type="radio" id="post_type_export" name="post_type_export" value="<?php echo $key;?>" <?php if($e == "0" ){echo "checked='checked'";}?> /> <?php echo $post_types['label'];?></label></li>
                         <?php $e++;}
					echo '</ul>';
					} ?>
                         <br/>     
                         <input type="submit" name="export_to_csv" value="<?php echo __('Export TO csv',TBI_DOMAIN);?>" class="button button-primary" id="submit">
                         <p class="description"><i><?php echo __('If your current theme doesn&lsquo;t support .csv format then follow <a href="http://codex.wordpress.org/Backing_Up_Your_Database">these</a> steps  to export your post from database.<p class="description"><b>Note</b>: Instead of exporting in sql format,just export them into .csv format</p>',TBI_DOMAIN);?></i></p>
                    </form>	
			</td>
			</tr>
		</tbody>
	</table>
<?php do_action('tevolution_after_bulk_upload');?>
</div><!-- wrap close -->
<?php
// saved file name to session	
if(isset($_FILES['csv_import']['tmp_name']) && $_FILES['csv_import']['tmp_name']!=""){
	$_SESSION['file_name'] = $_FILES['csv_import']['tmp_name'];
}
// finish saved file name to session
// saved post type to session	
if(isset($_POST['my_post_type']) && $_POST['my_post_type']!=""){
	$_SESSION['my_post_type'] = $_POST['my_post_type'];
}
if(isset($_SESSION['my_post_type']) && $_SESSION['my_post_type']!=""){
	$post_type = $_SESSION['my_post_type'];
}else{
	$post_type = 'post';
}
// finish saved post type to session	
$file = isset($_FILES['csv_import']['tmp_name']) ? $_FILES['csv_import']['tmp_name'] : "";
if(isset($_POST))
{
	$error= isset($_FILES['csv_import']['error']) ? $_FILES['csv_import']['error'] : "";
	//check the upload file error 
	if($error > 0)
	{	
		$upload_size_unit = $max_upload_size = wp_max_upload_size();
		$sizes = array( 'KB', 'MB', 'GB' );		
		for ( $u = -1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u++ ) {
			$upload_size_unit /= 1024;
		}
		if ( $u < 0 ) {
			$upload_size_unit = 0;
			$u = 0;
		} else {
			$upload_size_unit = (int) $upload_size_unit;
		}
		$msg= __("CSV file size large. Maximum allowed upload file size is",TBI_DOMAIN)." ".esc_html($upload_size_unit)." ".esc_html($sizes[$u]);
		echo "<script type='text/javascript'>jQuery('#status').html('$msg')</script>";
		exit;	
	}
	//finish the upload file error condition
}
if(isset($_FILES['csv_import']['tmp_name']) && $_FILES['csv_import']['tmp_name']!=""){
	$rows    = array();
	$headers = array();
	//open upload file 
	if($file!=""){
		$res = fopen($file, 'r');
	}	
	if($file!=""){
		$c=0;
		while ($keys = fgetcsv($res,99999)) {
			if ($c == 0) {
				$headers = $keys;
				
			}else {
				array_push($rows, $keys);
			}
			$c ++;
		}		
		fclose($res);	
		$columns=$headers;
		$ret_arr = array();
		foreach ($rows as $record) {
			$item_array = array();
			foreach ($record as $column => $value) {
			  if($value!=""){
				$header = $headers[$column];			
				//echo $header."=".$value."<br>";
				if (in_array($header, $columns)) {
					$item_array[$header] = $value;
				}
			  }	
			}
			// do not append empty results
			if ($item_array !== array()) {
				array_push($ret_arr, $item_array);			
			}
		}
		$_SESSION['total_record']=count($ret_arr);
		$_SESSION['csv_data']= $ret_arr;
	}
}
if($_POST && isset($_SESSION['file_name']) && $_SESSION['file_name']!=""){
	//print_r($_SESSION['csv_data']);exit;	
	
	if(isset($_SESSION['csv_data'][0]['templatic_post_author']) && $_SESSION['csv_data'][0]['templatic_post_author']!=""){
		
	}else{
		$_SESSION['csv_data']="";
		$_SESSION['file_name']="";
		$_SESSION['my_post_type']="";
		unset($_SESSION['csv_data']);
		unset($_SESSION['file_name']);
		unset($_SESSION['my_post_type']);
		$error_url =site_url().'/wp-admin/admin.php';
	?>
		<form action="<?php echo $error_url; ?>?page=bulk_upload" method="post" id="structure_error_frm" name="structure_error_frm">
			<input type="hidden" name="structure_error" value="1"/>
		</form>
		<script type="text/javascript">
			document.structure_error_frm.submit();
		</script>
	<?php
		continue;
	}
	echo "<script type='text/javascript'>jQuery('#read').html('Reading file...')</script>";			
	global $wpdb;
	$file = isset($_FILES['csv_import']['tmp_name']) ? $_FILES['csv_import']['tmp_name'] : "";
	$error= isset($_FILES['csv_import']['error']) ? $_FILES['csv_import']['error'] : "";
	
	$comments = 0;	 
	$imported = isset($_SESSION['imported']) ? $_SESSION['imported'] : 0;
	$updated = isset($_SESSION['updated']) ? $_SESSION['updated'] : 0;
	$skipped = isset($_SESSION['skipped']) ? $_SESSION['skipped'] : 0;
	
	$count_arr = @$_SESSION['total_record'];
	if(isset($_REQUEST['start']) && $_REQUEST['start']!=""){
		echo "<script type='text/javascript'>jQuery('#read').html('');</script>";	
		if($count_arr>$_REQUEST['start']){
			$start = $_REQUEST['start'];
		}else{
			$start=0;
		}
	}else{
		$start = 0;
	}
	if(isset($_REQUEST['loop']) && $_REQUEST['loop']!=""){
		if($count_arr>$_REQUEST['loop']){
			$remain = $count_arr - $_REQUEST['loop'];
			if($remain>=10){
				$loop = $_REQUEST['loop'] + 10;
			}else{
				$loop = $_REQUEST['loop'] + $remain;
			}
		}else{
			$loop=0;
			$_SESSION['csv_data']="";
			$_SESSION['file_name']="";
			$_SESSION['my_post_type']="";
			unset($_SESSION['csv_data']);
			unset($_SESSION['file_name']);
			unset($_SESSION['my_post_type']);
		}
	}else{
		if($count_arr>=10){
			$loop=10;
		}else{
			$loop=$count_arr;
		}
	}
	global $session_count;	
	for($i=$start;$i<$loop; $i++){
		$session_count = $i;
		$postid = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_type='".$post_type."' and  post_title = '".convert_chars($_SESSION['csv_data'][$i]['templatic_post_title'])."'" );		 
		if($postid==""):

			$new_post = array(
			'post_title'   => convert_chars($_SESSION['csv_data'][$i]['templatic_post_title']),
			'post_content' => wpautop(convert_chars($_SESSION['csv_data'][$i]['templatic_post_content'])),
			'post_status'  => 'publish',	
			'post_type'    => $post_type,
			'post_date'    => ($_SESSION['csv_data'][$i]['templatic_post_date']) ? date('Y-m-d H:i:s', strtotime($_SESSION['csv_data'][$i]['templatic_post_date'])): date('Y-m-d H:i:s'),
			'post_excerpt' => convert_chars($_SESSION['csv_data'][$i]['templatic_post_excerpt']),
			'post_author'  =>($_SESSION['csv_data'][$i]['templatic_post_author']) ? $_SESSION['csv_data'][$i]['templatic_post_author'] : 0, 		
			'post_parent'  => $_SESSION['csv_data'][$i]['templatic_post_parent'],
			//'tags_input' => $_SESSION['csv_data'][$i]['templatic_post_tags'],
		 );
		
		  // pages don't have tags or categories
		  //create the or get the categories id
			if ('page' !== $post_type) {
				$new_post['tags_input'] = $_SESSION['csv_data'][$i]['templatic_post_tags'];
				//if($_SESSION['csv_data'][$i]['templatic_post_category']!=""){}
				$cats =create_or_get_categories($_SESSION['csv_data'][$i]);
				$new_post['post_category'] = $cats['post'];
			}
			$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post_type,'public'   => true, '_builtin' => true ));
			$last_postid = wp_insert_post( $new_post );
			/*insert recurring event*/
			if(function_exists('save_bulk_upload_recurring_event'))
			{
				save_bulk_upload_recurring_event($last_postid);
			}
			/* Finish the place geo_latitude and geo_longitude in postcodes table*/
			
			if($_SESSION['csv_data'][$i]['templatic_post_category']!="")
			{
				$category_name=explode(',',$_SESSION['csv_data'][$i]['templatic_post_category']);
				wp_set_object_terms($last_postid,$category_name, $taxonomies[0]);
			}
			if($_SESSION['csv_data'][$i]['templatic_post_tags']!="")
			{
				wp_set_post_terms($last_postid,$_SESSION['csv_data'][$i]['templatic_post_tags'],$taxonomies[1]);
			}
			
			if(is_plugin_active('sitepress-multilingual-cms/sitepress.php') && function_exists('wpml_insert_templ_post')){
				wpml_insert_templ_post($last_postid,$post_type); /* insert post in language */
			}
			 //below add for comment
			 
			 //check the temlatic header is available in csv file or not
			 if($_SESSION['csv_data'][$i]["templatic_comments_data"]!=""){
				 $comments=$_SESSION['csv_data'][$i]["templatic_comments_data"];
				 $comeents_explode = explode('##',$comments);
					foreach($comeents_explode as $comeents_explode_obj){
						$comment_data = explode("~",$comeents_explode_obj);
						$data = array(
								'comment_post_ID' => $last_postid,
								'comment_author' =>convert_chars($comment_data[2]),
								'comment_author_email' =>convert_chars( $comment_data[3]),
								'comment_author_url' =>convert_chars($comment_data[4]),
								'comment_content' =>convert_chars($comment_data[8]),
								'comment_type' =>  $comment_data[12],
								'comment_parent' =>  $comment_data[13],
								'user_id' =>  $comment_data[14],
								'comment_author_IP' => $comment_data[5],
								'comment_agent' =>  $comment_data[11],
								'comment_date' =>  date('Y-m-d H:i:s', strtotime($comment_data[6])),
								'comment_approved' =>  $comment_data[10],
							);
							wp_insert_comment($data);
					}
			 }//finish the insert comment if condition
			 
			 //below add the custom field
			 create_templatic_custom_field($last_postid,$_SESSION['csv_data'][$i]);
			 do_action('tevolution_custom_fields_import');
			 //upload images
			 upload_templatic_images($last_postid,$_SESSION['csv_data'][$i]);
			 $imported++;
		 elseif($postid!=""):	
			//update the existing post
			$new_post = array('ID'=>$postid,
				'post_title'   => convert_chars($_SESSION['csv_data'][$i]['templatic_post_title']),
				'post_content' => wpautop(convert_chars($_SESSION['csv_data'][$i]['templatic_post_content'])),
				'post_status'  => 'publish',
				'post_type'    => $post_type,
				'post_date'    => date('Y-m-d H:i:s', strtotime($_SESSION['csv_data'][$i]['templatic_post_date'])),
				'post_excerpt' => convert_chars($_SESSION['csv_data'][$i]['templatic_post_excerpt']),
				'post_name'    => convert_chars($_SESSION['csv_data'][$i]['templatic_post_name']),
				'post_author'  =>($_SESSION['csv_data'][$i]['templatic_post_author']) ? $_SESSION['csv_data'][$i]['templatic_post_author'] : 0,
				'post_parent'  => $_SESSION['csv_data'][$i]['templatic_post_parent'],
				'tags_input' => convert_chars($_SESSION['csv_data'][$i]['templatic_post_tag']),
			 );
			 wp_update_post( $new_post );
			//below update the custom field
			create_templatic_custom_field($postid,$_SESSION['csv_data'][$i]);
						
			do_action('tevolution_custom_fields_import');
			$updated++;
		 else:
			$skipped++;
		 endif;
		if($i!=0){
			unset($_SESSION['csv_data'][$i]);
		}
		if($i==($loop-1)){ 
			$start=$loop;			
			$url = site_url().'/wp-admin/admin.php';
	?>
		<form action="<?php echo $url; ?>?page=bulk_upload" method="post" id="upload_frm" name="upload_frm">
			<input type="hidden" name="start" value="<?php echo $start;?>"/>
			<input type="hidden" name="loop" value="<?php echo $loop;?>"/>
		</form>
		<script type="text/javascript">
			document.upload_frm.submit();
		</script>
		<?php 
		}			
	}
	$_SESSION['imported'] = $imported;
	$_SESSION['updated'] = $updated;
	$_SESSION['skipped'] = $skipped;
}
//
// Function Name: create_or_get_categories
// Argument: csv data array
// return: create new categories and return ids or get the existing categories ids
//
function create_or_get_categories($data)
{	
	$ids = array('post' => array(),'cleanup' => array());
	$items = array_map('trim', explode(',', $data['templatic_post_category']));
	$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $data['templatic_post_type'],'public'   => true, '_builtin' => true ));
	
     foreach ($items as $item){
		
     	if (is_numeric($item)){
          	if (get_category($item) !== null){
               	$ids['post'][] = $item;
			}
		}else{
			$parent_id = 0;
               // item can be a single category name or a string such as
               // Parent > Child > Grandchild
               $categories = array_map('trim', explode('>', $item));
               if (count($categories) > 1 && is_numeric($categories[0])) {
                   $parent_id = $categories[0];
                   if (get_category($parent_id) !== null) {
                       // valid id, everything's ok
                       $categories = array_slice($categories, 1);
                   } 
               }
               foreach ($categories as $category) {
                   if ($category) {
                       $term = term_exists($category, $taxonomies[0], $parent_id);				   
                       if ($term) {
                           $term_id = $term['term_id'];
                       } else {
                           $term_id = wp_insert_category(array('cat_name' => $category,'category_parent' => $parent_id,'taxonomy' => $taxonomies[0]));
                           $ids['cleanup'][] = $term_id;
                       }
                       $parent_id = $term_id;
                   }
               }
          	$ids['post'][] = $term_id;
     	}
	}
	return $ids;
}
//
//  Function Name: create_templatic_custom_field
//  add the custom field.
//
function create_templatic_custom_field($post_id, $data) {
	foreach ($data as $k => $v) {
		// anything that doesn't start with csv_ is a custom field
		if (!preg_match('/^templatic_/', $k) && trim($v) != '') {
			//add_post_meta($post_id, $k, $v);
			do_action('tevolution_custom_fields',$post_id,$data,$k,$v);
			$v=apply_filters('tevolution_import_custom_fields',trim($v),trim($k));	
			if(trim($k) == 'package_id')
			{
				update_post_meta($post_id, 'package_select' , convert_chars($v));
			}
			else
			{
				if (strpos($v,'~') !== false) {
					$v = explode('~',$v);
					update_post_meta($post_id, trim($k), $v);
				}
				else
				{
					update_post_meta($post_id, trim($k), convert_chars($v));
				}
			}
		}
	}
}
//
//  Function Name: upload_templatic_images
//  Upload images
//
function upload_templatic_images($last_postid,$data)
{
	$image_folder_name = '/bulk/';
	$dirinfo = wp_upload_dir();		
	$path = $dirinfo['path'];
	$url = $dirinfo['url'];
	$subdir = $dirinfo['subdir'];
	$basedir = $dirinfo['basedir'];
	$baseurl = $dirinfo['baseurl'];	
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	foreach ($data as $k => $v) {
		if (preg_match('/^templatic_img/', $k) && $v != '') 
		{
			$image_name=$v;// image name
			$image_name_arr = explode(';',$image_name);
			foreach($image_name_arr as $_image_name_arr)
			{
				$upload_img_path=$basedir.$image_folder_name._wp_relative_upload_path( $_image_name_arr );				
				$wp_filetype = wp_check_filetype(basename($_image_name_arr), null );
				$attachment = array('guid' => $baseurl.$image_folder_name._wp_relative_upload_path( $_image_name_arr ),
								'post_mime_type' => $wp_filetype['type'],
								'post_title' => preg_replace('/\.[^.]+$/', '', basename($_image_name_arr)),
								'post_content' => '',
								'post_status' => 'inherit'
							);
				$img_attachment=substr($image_folder_name.$_image_name_arr,1);
				$attach_id = wp_insert_attachment( $attachment, $img_attachment, $last_postid );
				$upload_img_path=$basedir.$image_folder_name._wp_relative_upload_path( $_image_name_arr );
				$attach_data = wp_generate_attachment_metadata( $attach_id, $upload_img_path );
				wp_update_attachment_metadata( $attach_id, $attach_data );
			}//finish foreach loop
		}//finish the templatic_img preg_match condition
	}//finish the foreach loop
}
?>
