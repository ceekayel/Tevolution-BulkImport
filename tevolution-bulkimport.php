<?php
/*
Plugin Name: Tevolution Bulk Import
Plugin URI: http://templatic.com/docs/tevolution-guide/
Description: Use this free Directory add-on to import/export .csv content from other Directory sites. 
Version: 1.0
Author: Templatic
Author URI: http://templatic.com/
*/


define('BULKIMPORT_FOLDER_NAME','Tevolution-BulkImport');
define('BULKIMPORT_VERSION','1.0');
define('BULKIMPORT_PLUGIN_NAME','TevolutionBulk Import Plugin');
define('BULKIMPORT_SLUG','Tevolution-BulkImport/tevolution-bulkimport.php');

// Plugin Folder URL
define( 'BULKIMPORT_URL', plugin_dir_url( __FILE__ ) );
// Plugin Folder Path
define( 'BULKIMPORT_DIR', plugin_dir_path( __FILE__ ) );

define( 'TBI_DOMAIN', 'templatic');

$locale = get_locale();
load_textdomain( TBI_DOMAIN, plugin_dir_path( __FILE__ ).'languages/'.$locale.'.mo' );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );


/*Check tevolution plugin activate */
if(is_plugin_active('Tevolution/templatic.php'))
{

	add_action('templ_add_admin_menu_', 'templ_add_submenu_bulk_import',20);
	
	function templ_add_submenu_bulk_import()
	{
		$menu_title = __('Bulk Import/Export',TBI_DOMAIN);	
		
		add_submenu_page('templatic_system_menu', $menu_title,$menu_title, 'administrator', 'bulk_upload', 'templ_tevolution_bulk_import');
		
	}
	add_action('tevolution_custom_fields','tevolution_address_custom_fields',10,4);
	function tevolution_address_custom_fields($post_id,$data,$k,$v){
		
		if($k=='address' && $data['geo_latitude']=='' && $data['geo_longitude']==''){		
			$http=(is_ssl())?"https://":"http://";
			$v = str_replace(' ','+',convert_chars(addslashes(iconv('', 'utf-8',$v))));
			$geocode = file_get_contents($http.'maps.google.com/maps/api/geocode/json?address='.$v.'&sensor=false');
			$output= json_decode($geocode);
			$lat = $output->results[0]->geometry->location->lat;
			$long = $output->results[0]->geometry->location->lng;
			update_post_meta($post_id, 'geo_latitude', convert_chars(addslashes(iconv('', 'utf-8',$lat))));
			update_post_meta($post_id, 'geo_longitude', convert_chars(addslashes(iconv('', 'utf-8',$long))));		
		}
		
	}
	add_action('tevolution_custom_fields','tevolution_postal_custom_fields',20,4);
	function tevolution_postal_custom_fields($post_id,$data,$k,$v){
		global $wpdb;
		$postcodes_table = $wpdb->prefix . "postcodes";	
		if($wpdb->get_var("SHOW TABLES LIKE \"$postcodes_table\"") == $postcodes_table) { 		
			if($k=='address' && trim($data['geo_latitude'])=='' && trim($data['geo_longitude'])==''){
				$http=(is_ssl())?"https://":"http://";
				$v = str_replace(' ','+',convert_chars(addslashes(iconv('', 'utf-8',$v))));
				$geocode = file_get_contents($http.'maps.google.com/maps/api/geocode/json?address='.$v.'&sensor=false');
				$output= json_decode($geocode);			
				$lat = $output->results[0]->geometry->location->lat;
				$long = $output->results[0]->geometry->location->lng;			
				update_post_meta($post_id, 'geo_latitude', convert_chars(addslashes(iconv('', 'utf-8',$lat))));
				update_post_meta($post_id, 'geo_longitude', convert_chars(addslashes(iconv('', 'utf-8',$long))));	
				echo "<br />".convert_chars(addslashes(iconv('', 'utf-8',$lat)))."==".convert_chars(addslashes(iconv('', 'utf-8',$long)))."==".$k."==".$post_id;			
				$pcid = $wpdb->get_results($wpdb->prepare("select pcid from $postcodes_table where post_id = %d",$post_id));
				if(count($pcid)!=0){
					$wpdb->update($postcodes_table , array('post_type' => $data['templatic_post_type'],'address'=>$data['address'],'latitude'=> $lat,'longitude'=> $long), array('pcid' => $pcid,'post_id'=>$post_id) );	
				}else{
					$wpdb->query( $wpdb->prepare("INSERT INTO $postcodes_table ( post_id,post_type,address,latitude,longitude) VALUES ( %d, %s, %s, %s, %s)", $post_id,$data['templatic_post_type'],$data['address'],$lat,$long ) );
				}
			}
			elseif($k=='address' && $data['geo_latitude']!='' && $data['geo_longitude']!='')
			{ echo "sdfsdfsdf";
				$pcid = $wpdb->get_results($wpdb->prepare("select pcid from $postcodes_table where post_id = %d",$post_id));
				if(count($pcid)!=0){
					$wpdb->update($postcodes_table , array('post_type' => $data['templatic_post_type'],'address'=>$data['address'],'latitude'=> $data['geo_latitude'],'longitude'=> $data['geo_longitude']), array('pcid' => $pcid,'post_id'=>$post_id) );	
				}else{ 
					$wpdb->query( $wpdb->prepare("INSERT INTO $postcodes_table ( post_id,post_type,address,latitude,longitude) VALUES ( %d, %s, %s, %s, %s)", $post_id,$data['templatic_post_type'],$data['address'],$data['geo_latitude'],$data['geo_longitude'] ) );
				}
			}
		}
	}
		
	
	/*	included file containing bulk upload functionality	*/
	function templ_tevolution_bulk_import()
	{
		if(file_exists(BULKIMPORT_DIR.'templatic_bulk_upload.php')){
			include_once(BULKIMPORT_DIR.'templatic_bulk_upload.php');
		}
	}
	
}else{
	add_action('admin_notices','tevolution_bulkimport_admin_notices');	
}

/*display base plugin tevolution not activate */
function tevolution_bulkimport_admin_notices(){
	echo '<div class="error"><p>' . sprintf(__('You have not activated the base plugin %s. Please activate it to use Tevolution-BulkImport plugin.','templatic'),'<b>Tevolution</b>'). '</p></div>';	
}



add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ),'tevolution_bulk_import_action_links'  );
function tevolution_bulk_import_action_links($links){
	if(!is_plugin_active('Tevolution/templatic.php')){
		return $links;
	}
	
	$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=bulk_upload' ) . '">' . __( 'Settings', 'templatic-admin' ) . '</a>',
		);
	
	return array_merge( $plugin_links, $links );
}


add_action('admin_init','tmpl_bulk_import_auto_update');
function tmpl_bulk_import_auto_update(){
	global $pagenow;
	remove_action( 'after_plugin_row_Tevolution-BulkImport/tevolution-bulkimport.php', 'wp_plugin_update_row' ,10, 2 );
	/* for auto updates */
	if($pagenow=='plugins.php'){		
		require_once('wp-updates-plugin.php');
		new WPUpdatesBulkImportUpdater( 'http://templatic.com/updates/api/index.php', plugin_basename(__FILE__) );
	}
}

/*
 * Function Name: tevolution_update_login
 * Return: update tevolution plugin version after templatic member login
 */
add_action('wp_ajax_tevolution_bulk_import','tevolution_bulk_import_update_login');
function tevolution_bulk_import_update_login()
{
	check_ajax_referer( 'tevolution_bulk_import', '_ajax_nonce' );
	$plugin_dir = rtrim( plugin_dir_path(__FILE__), '/' );	
	require_once( $plugin_dir .  '/templatic_login.php' );	
	exit;
}
?>