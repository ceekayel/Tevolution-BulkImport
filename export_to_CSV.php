<?php
set_time_limit(0);
require("../../../wp-load.php");
$fname = @$_REQUEST['post_type_export']."_report_".strtotime(date('Y-m-d')).".csv";
header('Content-Description: File Transfer');
header("Content-type: application/force-download");
header('Content-Disposition: inline; filename="'.$fname.'"');
ob_start();
$f = fopen('php://output', 'w') or show_error("Can't open php://output");
$n = 0;
		function templatic_get_post_images($pid)
		{
			$image_array = array();
			$pmeta = get_post_meta($pid, 'key', $single = true);
			if($pmeta['productimage'])
			{
				$image_array[] = $pmeta['productimage'];
			}
			if($pmeta['productimage1'])
			{
				$image_array[] = $pmeta['productimage1'];
			}
			if($pmeta['productimage2'])
			{
				$image_array[] = $pmeta['productimage2'];
			}
			if($pmeta['productimage3'])
			{
				$image_array[] = $pmeta['productimage3'];
			}
			if($pmeta['productimage4'])
			{
				$image_array[] = $pmeta['productimage4'];
			}
			if($pmeta['productimage5'])
			{
				$image_array[] = $pmeta['productimage5'];
			}
			if($pmeta['productimage6'])
			{
				$image_array[] = $pmeta['productimage6'];
			}
			return $image_array;
		}
		
		function templatic_get_post_image($post,$img_size='thumb',$detail='',$numberofimgs=6)
		{
			$return_arr = array();
			if($post->ID)
			{
				$images = templatic_get_post_images($post->ID);
				if(is_array($images))
				{
					$return_arr = $images;
				}
			}
			$arrImages =&get_children('order=ASC&orderby=menu_order ID&post_type=attachment&post_mime_type=image&post_parent=' . $post->ID );
			if($arrImages) 
			{
				$counter=0;
			   foreach($arrImages as $key=>$val)
			   {
					$counter++;
					$id = $val->ID;
					if($img_size == 'large')
					{
						$img_arr = wp_get_attachment_image_src($id,'full');	// THE FULL SIZE IMAGE INSTEAD
						if(!strstr($post->post_content,$img_arr[0]))
						{
							if($detail)
							{
								$img_arr['id']=$id;
								$return_arr[] = $img_arr;
							}else
							{
								$return_arr[] = $img_arr[0];
							}
						}
					}
					elseif($img_size == 'medium')
					{
						$img_arr = wp_get_attachment_image_src($id, 'medium'); //THE medium SIZE IMAGE INSTEAD
						if(!strstr($post->post_content,$img_arr[0]))
						{
							if($detail)
							{
								$img_arr['id']=$id;
								$return_arr[] = $img_arr;
							}else
							{
								$return_arr[] = $img_arr[0];
							}
						}
					}
					elseif($img_size == 'thumb')
					{
						$img_arr = wp_get_attachment_image_src($id, 'thumbnail'); // Get the thumbnail url for the attachment
						if(!strstr($post->post_content,$img_arr[0]))
						{
							if($detail)
							{
								$img_arr['id']=$id;
								$return_arr[] = $img_arr;
							}else
							{
								$return_arr[] = $img_arr[0];
							}
						}						
					}
			   }
			  return $return_arr;
			}			
		}
global $wpdb,$current_user;
$post_table = $wpdb->prefix."posts";
$post_meta_table = $wpdb->prefix."postmeta";
$authorsql_select = "select DISTINCT p.ID,p.*";
$authorsql_from = " from $post_table p,$post_meta_table pm";
$authorsql_conditions = " where";
$authorsql_conditions .= apply_filters('tevolution_posts_where'," p.post_type = '".@$_REQUEST['post_type_export']."' and p.post_status='publish' and p.ID = pm.post_id");
$authorinfo = $wpdb->get_results($authorsql_select.$authorsql_from.$authorsql_conditions);
// fetch all custom field from custom post type
if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
{
	remove_action('parse_query','tmpl_location_parse_query');
}
$args=array( 
			'post_type' => 'custom_fields',
			'posts_per_page' => -1	,
			'post_status' => array('publish'),
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'post_type_'.$_REQUEST['post_type_export'].'',
					'value' => array($_REQUEST['post_type_export']),
					'compare' => 'IN',
					'type'=> 'text'
				),
				array(
					'key' => 'post_type',
					'value' => $_REQUEST['post_type_export'],
					'compare' => 'LIKE',
					'type'=> 'text'
				),
				array(
					'key' => 'ctype',
					'value' => 'heading_type',
					'compare' => '!='
				)
				),
			'meta_key' => 'sort_order',
			'orderby' => 'meta_value_num',
			'meta_value_num'=>'sort_order',
			'order' => 'ASC'
		);
remove_all_actions('posts_where');
$post_query_custom_field = new WP_Query($args);
if($post_query_custom_field):
	while ($post_query_custom_field->have_posts()) : $post_query_custom_field->the_post();
		$custom_fields = array("name"=> get_post_meta($post->ID,"htmlvar_name",true),'ctype' =>get_post_meta($post->ID,"ctype",true));
		$return_arr[get_post_meta($post->ID,"htmlvar_name",true)] = $custom_fields;
	endwhile;
endif;
$return_arr=apply_filters('tevolution_export_csv',$return_arr,$_REQUEST['post_type_export']);
//Finish custom field
//echo count($authorinfo);
/*	Get all	taxonomy by post type  START  */
$post_cat_type = "";
$post_tag_type = "";
$templatic_post_types = @$_REQUEST['post_type_export'];
$templatic_taxonomies = get_object_taxonomies($templatic_post_types);
$templatic_count = count($templatic_taxonomies);
if($templatic_count > 0){
	if($templatic_taxonomies[0] != ""){
		$post_cat_type = $templatic_taxonomies[0];
	}else{
		$post_cat_type = "";
	}
	if($templatic_taxonomies[1] != ""){
		$post_tag_type = $templatic_taxonomies[1];
	}else{
		$post_tag_type = "";
	}
}
/*	Get all	taxonomies by post type  END  */
$old_pattern = array("/[^a-zA-Z0-9-:;<>`'žàÐedŽ\/=.& ]/", "/_+/", "/_$/");
$new_pattern = array("_", "_", "");
$file_name = strtolower(preg_replace($old_pattern, $new_pattern , $text_title));
  	if($authorinfo)
	{
		$templatic_counter = 1;
		foreach($authorinfo as $postObj)
		{
			global $post,$wpdb;
			$templatic_keys = array();
			$templatic_values = array();
			
			$product_image_arr = templatic_get_post_image($postObj,'large','',5);
			$image = basename($product_image_arr[0]);
			$imageArr = '';
			//$image = basename($product_image_arr[0]);
			if(count($product_image_arr)>1)
			{
				for($im=0;$im<=count($product_image_arr);$im++)
				{
					$ext_arr = explode('.',$product_image_arr[$im]);
					$fileext = strtolower($ext_arr[count($ext_arr)-1]);
					if(in_array($fileext,array('jpg','jpeg','gif','png')))
					{
						//$product_image_arr[$im] .= $product_image_arr[$im];
						$imageArr .= basename($product_image_arr[$im]).";";
					}
				}
				$image = substr($imageArr,0,-1);
				//$product_image_arr = implode(";",$product_image_arr);
			}
			//$post_title =  preg_replace($old_pattern, $new_pattern , $postObj->post_title); 
			$post_title =  iconv("UTF-8", "ISO-8859-1//IGNORE", $postObj->post_title);
			$post_date =  $postObj->post_date;
			$post_date_gmt = $postObj->post_date_gmt;
			$post_content = $postObj->post_content;
			$post_excerpt =   $postObj->post_excerpt;
			
			$udata = get_userdata($postObj->post_author);
			
		/*	get all custom fields of post START	*/
			foreach($return_arr as $key=>$value)
			{				
				if($key!='post_images' && $key!="category" && $key!="post_title" & $key!="post_content" && $key!="post_excerpt")
				{
					$post_value=apply_filters('tevolution_field_value',get_post_meta($postObj->ID,$key,true),$key);
					
					if(is_array($post_value))
					{
						if($value['ctype'] == 'multicheckbox')
							$post_value=implode('~',$post_value);	
						else
						    $post_value=implode(',',$post_value);		
					}
					$templatic_keys[] = $key;
					$templatic_values[] = $post_value;
					if('geo_map'==$value['ctype'])
					{
						$templatic_keys[]='geo_latitude';
						$templatic_keys[]='geo_longitude';
						$templatic_values[]=get_post_meta($postObj->ID,'geo_latitude',true);
						$templatic_values[]=get_post_meta($postObj->ID,'geo_longitude',true);
					}
				}
			}	
			//additional custom field
			if($_REQUEST['post_type_export']!='post')
			{
				$templatic_keys[]='alive_days';
				$templatic_keys[]='featured_type';
				$templatic_keys[]='package_id';
				$templatic_values[]=get_post_meta($postObj->ID,'alive_days',true);
				$templatic_values[]=get_post_meta($postObj->ID,'featured_type',true);
				$templatic_values[]=get_post_meta($postObj->ID,'package_select',true);
			}
			
			
		/*	get all custom fields of post END	*/		
			
			$category_array = wp_get_post_terms($postObj->ID,$post_cat_type,array('fields' => 'names'));
			$category = '';
			if($category_array){				
				$category =implode(',',$category_array);
			}
			$tag_array = wp_get_post_terms($postObj->ID,$post_tag_type, array('fields' => 'names'));
			$tags = '';
			if($tag_array){				
				$tags =implode(',',$tag_array);
			}
			$args = array('post_id'=>$postObj->ID);
			$comments_data = get_comments( $args );
			
			//--fetch comments ----//;
			$newarray = "";
			if($comments_data){
				foreach($comments_data as $comments_data_obj){					
					foreach($comments_data_obj as $_comments_data_obj)
					  {
						if($_comments_data_obj == ""){
							$_comments_data_obj = "null";
						}
						$newarray .= $_comments_data_obj."~";
					  }
					  $newarray .="##";
				}
				$newarray = str_replace(','," ",$newarray);
			}else{
				$newarray = "";
			}
			if($templatic_counter == 1){				
				$header_top =  "templatic_post_author,templatic_post_date,templatic_post_title,templatic_post_category,templatic_img,templatic_post_tags,templatic_post_content,templatic_post_excerpt,templatic_post_status,templatic_comment_status,templatic_ping_status,templatic_post_name,templatic_post_type,templatic_post_comment_count,templatic_comments_data";
				
				
				if(count($templatic_keys)>0){
					$custom = implode($templatic_keys,",");
					$header_top .= ','.$custom." \r\n";
				}else{
					$header_top .= "\r\n";
				}	
				echo $header_top;
			}
			
			$content_1 =  array("$postObj->post_author","$post_date","$post_title","$category","$image","$tags","$postObj->post_content","$post_excerpt","$postObj->post_status","$postObj->comment_status","$postObj->ping_status","$postObj->post_name","$postObj->post_type","$postObj->comment_count");
			$content_1_array = array("$newarray");
			$csv_array=array_merge($content_1,$content_1_array);
			$content_1_array = $templatic_values;
			$new_csv_array = array_merge($csv_array,$content_1_array);
			if ( !fputcsv($f, $new_csv_array)){
				echo __("Can't write line",TBI_DOMAIN)." $n: $line";
			}
			$templatic_counter ++;
		}
		fclose($f) or show_error(__("Can't close",TBI_DOMAIN)." php://output");
		$csvStr = ob_get_contents();
		ob_end_clean();	
		 print_r($csvStr);
		
	}else{
	echo __("No record available",TBI_DOMAIN);
	}
/*}*/?>
