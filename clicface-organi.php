<?php
/*
Plugin Name: Clicface Organi
Plugin URI: http://www.clicface.com/
Description: The Org Chart web application. Clicface Trombi required.
Version: 1.02
Author: Clicface
Author URI: http://www.clicface.com/
Plugin Type: Piklist
License: GPL2
*/

require_once( plugin_dir_path( dirname(__FILE__) ) . 'clicface-trombi/includes/class-collaborateur.php' );
require_once( plugin_dir_path( dirname(__FILE__) ) . 'clicface-organi/includes/class-label.php' );

$ExtraLink = '';
$WindowTarget = '';
$ExtraClassImg = '';
$ExtraClassTxt = '';
$clicface_trombi_settings = array();
$clicface_organi_settings = array();
$boss_id = '';

add_action('init', 'clicface_organi_init');
function clicface_organi_init(){
	wp_register_style( 'clicface-organi-style-common', plugins_url( 'clicface-organi/css/clicface-organi.style.common.css') );
	wp_register_style( 'clicface-organi-style-1', plugins_url( 'clicface-organi/css/clicface-organi.style1.css') );
	wp_register_style( 'clicface-organi-style-2', plugins_url( 'clicface-organi/css/clicface-organi.style2.css') );
	wp_register_style( 'clicface-organi-style-3', plugins_url( 'clicface-organi/css/clicface-organi.style3.css') );
	wp_register_style( 'clicface-organi-style-4', plugins_url( 'clicface-organi/css/clicface-organi.style4.css') );
	wp_register_script( 'jOrgChart', plugins_url( 'clicface-organi/lib/jquery.jOrgChart.js') );
	wp_register_script( 'clicface-organi-script', plugins_url( 'clicface-organi/lib/clicface-organi.js') );
}

add_action('admin_init', 'clicface_organi_init_function', -1);
function clicface_organi_init_function() {
	include_once('includes/class-piklist-checker.php');
	if (!piklist_checker::check(__FILE__)) {
		return;
	}
}

add_filter('piklist_post_types', 'piklist_orgchart_post_types');
function piklist_orgchart_post_types($post_types) {
	$post_types['orgchart'] = array(
			'labels' => array(
				'name' => __('Org Charts', 'clicface-trombi')
				,'singular_name' => __('Org Chart', 'clicface-trombi')
				,'add_new' => __('Add New Org Chart', 'clicface-trombi')
			)
			,'public' => true
			,'rewrite' => array(
				'slug' => 'orgchart'
			)
			,'capability_type' => 'post'
			,'menu_position' => 46
			,'hide_meta_box' => array(
				'slug'
				,'author'
				,'piklist_orgchart_type'
			)
	);
	return $post_types;
}

add_filter('piklist_post_types', 'piklist_orgchart_label_post_types');
function piklist_orgchart_label_post_types($post_types) {
	$post_types['orgchart-label'] = array(
			'labels' => array(
				'name' => __('Labels', 'clicface-trombi')
				,'singular_name' => __('Label', 'clicface-trombi')
				,'add_new' => __('Add New Label', 'clicface-trombi')
			)
			,'public' => true
			,'rewrite' => array(
				'slug' => 'orgchart-label'
			)
			,'capability_type' => 'post'
			,'menu_position' => 47
			,'hide_meta_box' => array(
				'slug'
				,'author'
				,'piklist_orgchart_label_type'
			)
	);
	return $post_types;
}

add_filter('piklist_admin_pages', 'piklist_orgchart_admin_pages');
function piklist_orgchart_admin_pages($pages) {
	$pages[] = array(
		'page_title' => __('Clicface Organi Settings', 'clicface-trombi')
		,'menu_title' => 'Clicface Organi'
		,'capability' => 'manage_options'
		,'menu_slug' => 'clicface_organi_settings_menu'
		,'setting' => 'clicface_organi_settings'
		,'icon' => 'options-general'
		,'single_line' => false
		,'default_tab' => __('General', 'clicface-trombi')
	);

	return $pages;
}

add_action('new_to_publish', 'organi_check_num_orgchart');
add_action('auto-draft_to_publish', 'organi_check_num_orgchart');
add_action('draft_to_publish', 'organi_check_num_orgchart');
add_action('pending_to_publish', 'organi_check_num_orgchart');
function organi_check_num_orgchart( $post ) {
	if ($post->post_type == 'orgchart') {
		if ( wp_count_posts('orgchart')->publish > strlen('clicface') ) {
			wp_delete_post( $post->ID, true );
			wp_die( __('You can\'t create any more record, you have already reached the limit. You have to update your Clicface Organi plugin version to add more records.', 'clicface-trombi') . '<br /><center><a href="http://www.clicface.com/" target="_blank">http://www.clicface.com/</a></center>' );
		}
	}
}

add_action('new_to_publish', 'organi_check_num_label');
add_action('auto-draft_to_publish', 'organi_check_num_label');
add_action('draft_to_publish', 'organi_check_num_label');
add_action('pending_to_publish', 'organi_check_num_label');
function organi_check_num_label( $post ) {
	if ($post->post_type == 'label') {
		if ( wp_count_posts('label')->publish > strlen('-clicface-') ) {
			wp_delete_post( $post->ID, true );
			wp_die( __('You can\'t create any more record, you have already reached the limit. You have to update your Clicface Organi plugin version to add more records.', 'clicface-trombi') . '<br /><center><a href="http://www.clicface.com/" target="_blank">http://www.clicface.com/</a></center>' );
		}
	}
}

add_action ('save_post', 'titlize_orgchart');
function titlize_orgchart( $post_id ) {
	$type = get_post_type( $post_id );
	if ($type == 'orgchart') {
		$update_post['ID'] = $post_id;
		
		// On sauvegarde une premiere fois
		remove_action('save_post', 'titlize_orgchart'); // unhook this function so it doesn't loop infinitely
		wp_update_post( $update_post );
		
		// On met a jour le titre, et on sauvegarde a nouveau
		$update_post['post_title'] = get_post_meta($post_id, 'orgchart_title', true);
		wp_update_post( $update_post );
		
		add_action ('save_post', 'titlize_orgchart'); // re-hook this function
	} else {
		return true;
	}
}

add_action ('save_post', 'titlize_orgchart_label');
function titlize_orgchart_label( $post_id ) {
	$type = get_post_type( $post_id );
	if ($type == 'orgchart-label') {
		$update_post['ID'] = $post_id;
		
		// On sauvegarde une premiere fois
		remove_action('save_post', 'titlize_orgchart_label'); // unhook this function so it doesn't loop infinitely
		wp_update_post( $update_post );
		
		// On met a jour le titre, et on sauvegarde a nouveau
		$update_post['post_title'] = get_post_meta($post_id, 'label_title', true);
		wp_update_post( $update_post );
		
		add_action ('save_post', 'titlize_orgchart_label'); // re-hook this function
	} else {
		return true;
	}
}

add_action ('save_post', 'bossize_orgchart');
function bossize_orgchart( $post_id ) {
	$type = get_post_type( $post_id );
	if ($type == 'orgchart') {
		$update_post['ID'] = $post_id;
		
		$boss_id = get_post_meta($post_id, 'orgchart_boss', true);
		$json = get_post_meta($post_id, 'orgchart_data', true);
		
		if ( $json != "" ) {
			$array = json_decode( $json, true );
			$array[0]['id'] = $boss_id;
			$array[0]['ty'] = 's';
			$json = json_encode( $array );
			update_post_meta($post_id, 'orgchart_data', $json);
		} else {
			$array = array();
			$array['id'] = $boss_id;
			$array['ty'] = 's';
			$json = json_encode( array($array) );
			update_post_meta($post_id, 'orgchart_data', $json);
		}
	} else {
		return true;
	}
}

add_filter( 'manage_edit-orgchart_columns', 'set_custom_edit_orgchart_columns' );
function set_custom_edit_orgchart_columns($columns) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __('Org Chart', 'clicface-trombi'),
		'id' => __('Shortcode to copy', 'clicface-trombi'),
		'author' => __('Author', 'clicface-trombi'),
		'date' => __('Date', 'clicface-trombi')
	);
	return $columns;
}

add_action('manage_orgchart_posts_custom_column', 'manage_orgchart_columns', 10, 2);
function manage_orgchart_columns($column_name, $id) {
	global $wpdb;
	switch ($column_name) {
		case 'id':
			echo '[clicface-organi id="' . $id . '"]';
		break;

		default:
		break;
	}
}

add_filter( 'manage_edit-orgchart-label_columns', 'set_custom_edit_orgchart_label_columns' );
function set_custom_edit_orgchart_label_columns($columns) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __('Labels for Org Charts', 'clicface-trombi'),
		'link_page' => __('Link to page', 'clicface-trombi'),
		'author' => __('Author', 'clicface-trombi'),
		'date' => __('Date', 'clicface-trombi')
	);
	return $columns;
}

add_action('manage_orgchart-label_posts_custom_column', 'manage_orgchart_label_columns', 10, 2);
function manage_orgchart_label_columns($column_name, $id) {
	global $wpdb;
	switch ($column_name) {
		case 'link_page':
			$label_objet = new clicface_Label( $id );
			if ( $label_objet->DisplayPagetLink == 'oui' ) {
				echo get_the_title( $label_objet->PageLinkID );
			}
		break;

		default:
		break;
	}
}

add_shortcode( 'clicface-organi', 'organi_display_views' );
function organi_display_views( $atts ) {
	
	global $ExtraLink;
	global $WindowTarget;
	global $ExtraClassImg;
	global $ExtraClassTxt;
	global $clicface_trombi_settings;
	global $clicface_organi_settings;
	global $boss_id;
	
	extract(shortcode_atts(array(
		'id' => FALSE
	), $atts));
	
	$orgchart_id = $atts['id'];
	$clicface_trombi_settings = get_option('clicface_trombi_settings');
	$clicface_organi_settings = get_option('clicface_organi_settings');
	include_once( plugin_dir_path(__FILE__) . '../clicface-trombi/includes/settings-initialization.php' );
	wp_enqueue_style('clicface-organi-style-common');
	if ( !isset($clicface_organi_settings['organi_css_stylesheet']) ) $clicface_organi_settings['organi_css_stylesheet'] = 'style1';
	switch ( $clicface_organi_settings['organi_css_stylesheet'] ) {
		default:
		case 'style1':
			wp_enqueue_style('clicface-organi-style-1');
		break;
		
		case 'style2':
			wp_enqueue_style('clicface-organi-style-2');
		break;
		
		case 'style3':
			wp_enqueue_style('clicface-organi-style-3');
		break;
		
		case 'style4':
			wp_enqueue_style('clicface-organi-style-4');
		break;
	}
	wp_enqueue_style('clicface-trombi-style');
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('jquery-ui-draggable');
	wp_enqueue_script('jquery-ui-droppable');
	wp_enqueue_script('jOrgChart');
	wp_enqueue_script('clicface-organi-script');
	$nonce = wp_create_nonce('clicface_organi_g98er49e8hpcwzt8');
	$boutons = '';
	$output = '';
	
	if ($orgchart_id == '') {
		$output .= __('Error: The id argument has not been provided.', 'clicface-trombi');
		return $output;
	}
	
	if ( get_post_type( $orgchart_id ) != 'orgchart' ) {
		$output .= __('Error: The id argument does not correspond to an org chart.', 'clicface-trombi');
		return $output;
	}
	
	switch($clicface_trombi_settings['trombi_target_window']) {
		case 'thickbox':
			$ExtraLink = '?TB_iframe=true&width=' . $clicface_trombi_settings['trombi_thickbox_width'] . '&height=' . $clicface_trombi_settings['trombi_thickbox_height'];
			$WindowTarget = '_self';
			$ExtraClassImg = 'class="thickbox"';
			$ExtraClassTxt = 'thickbox';
			add_thickbox();
		break;
		
		case '_self':
			$ExtraLink = ($clicface_trombi_settings['trombi_move_to_anchor'] == 'oui') ? '#ClicfaceOrgani' : '';
			$WindowTarget = '_self';
			$ExtraClassImg = '';
			$ExtraClassTxt = '';
		break;
		
		default: // _blank
			$ExtraLink = ($clicface_trombi_settings['trombi_move_to_anchor'] == 'oui') ? '#ClicfaceOrgani' : '';
			$WindowTarget = '_blank';
			$ExtraClassImg = '';
			$ExtraClassTxt = '';
		break;
	}
	
	$json = get_post_meta($orgchart_id, 'orgchart_data', true);
	$array = json_decode( $json, true );
	
	if ( current_user_can('edit_pages') ) {
		$collaborateurs = piklist(
			get_posts(
			array(
				'numberposts' => -1
				,'order' => 'DESC'
				,'orderby' => 'post_title'
				,'post_type' => 'collaborateur'
				,'post_status' => 'publish'
				)
				,'objects'
			)
			,array(
				'ID'
				,'post_title'
			)
		);
		if( ! $collaborateurs ) {
			$output .= __('Error: No employees found.', 'clicface-trombi');
			return $output;
		}
		$labels = piklist(
			get_posts(
			array(
				'numberposts' => -1
				,'order' => 'DESC'
				,'orderby' => 'post_title'
				,'post_type' => 'orgchart-label'
				,'post_status' => 'publish'
				)
				,'objects'
			)
			,array(
				'ID'
				,'post_title'
			)
		);
		
		$boutons .= '<div class="clicface-organi-buttons">';
		$boutons .= '<img id="clicface-organi-add-box-button" src="' . plugins_url( 'img/plus-icon.png' , __FILE__ ) . '" title="' .  __('Add an Employee in this Org Chart', 'clicface-trombi') . '" />&nbsp;';
		$boutons .= '<img id="clicface-organi-add-label-button" src="' . plugins_url( 'img/plus-blue-icon.png' , __FILE__ ) . '" title="' .  __('Add a Label in this Org Chart', 'clicface-trombi') . '" />&nbsp;';
		$boutons .= '<a href="' . get_edit_post_link($orgchart_id) . '"><img src="' . plugins_url( 'img/switch-boss.png' , __FILE__ ) . '" title="' .  __('Switch Boss', 'clicface-trombi') . '" /></a>&nbsp;';
		$boutons .= '<a href="#" id="clicface_reload_page_button" target="_top"><img src="' . plugins_url( 'img/refresh-icon.png' , __FILE__ ) . '" title="' .  __('Reload page', 'clicface-trombi') . '" /></a>&nbsp;';
		$boutons .= '</div>';
		$boutons .= '<div id="clicface-organi-add-box"><form action="' . get_permalink() . '" method="get">';
		if ( get_option('permalink_structure') == '' ) {
			$boutons .= '<input type="hidden" name="page_id" value="' . get_query_var('page_id') . '" />';
		}
		$boutons .= '<input type="hidden" name="_wpnonce" value="' . $nonce . '" />';
		$boutons .= '<select name="co2add"><option>' . __('Select an Employee to add', 'clicface-trombi') . '</option>';
		foreach( $collaborateurs as $collaborateur_id => $collaborateur_nom ) {
			$boutons .= '<option value="' . $collaborateur_id . '">' . $collaborateur_nom . '</option>';
		}
		$boutons .= '</select><br />';
		$boutons .= '<input type="submit" name="submit" value="' . __('Add', 'clicface-trombi') . '" />';
		$boutons .= '</form></div>';
		$boutons .= '<div id="clicface-organi-add-label"><form action="' . get_permalink() . '" method="get">';
		if ( get_option('permalink_structure') == '' ) {
			$boutons .= '<input type="hidden" name="page_id" value="' . get_query_var('page_id') . '" />';
		}
		$boutons .= '<input type="hidden" name="_wpnonce" value="' . $nonce . '" />';
		$boutons .= '<select name="co2add"><option>' . __('Select a Label to add', 'clicface-trombi') . '</option>';
		foreach( $labels as $label_id => $label_nom ) {
			$boutons .= '<option value="' . $label_id . '">' . $label_nom . '</option>';
		}
		$boutons .= '</select><br />';
		$boutons .= '<input type="submit" name="submit" value="' . __('Add', 'clicface-trombi') . '" />';
		$boutons .= '</form></div>';
		
		$element_to_add = (get_query_var('co2add')) ? get_query_var('co2add') : 0;
		$element_to_add += 0;
		if ( is_int($element_to_add) && $element_to_add != NULL ) {
			$array_vide = array();
			function generateRandomString($length = 10) {
				$characters = 'abcdefghijklmnopqrstuvwxyz';
				$randomString = '';
				for ($i = 0; $i < $length; $i++) {
					$randomString .= $characters[rand(0, strlen($characters) - 1)];
				}
				return $randomString;
			}
			$chaine_alphabetique_aleatoire = generateRandomString();
			if ( $array != $array_vide ) {
				if ( count($array, COUNT_RECURSIVE) < 30 ) {
					if ( isset($array[0]['children']) ) {
						$last_collaborateur = end( $array[0]['children'] );
						$last_element_id = preg_replace("/[^0-9]/", "", $last_collaborateur['id']);
					} else {
						$last_element_id = NULL;
					}
					if ( $element_to_add != $last_element_id ) {
						switch( get_post_type($element_to_add) ) {
							case 'collaborateur':
								$array[0]['children'][] = array('id' => $element_to_add . $chaine_alphabetique_aleatoire, 'ty' => 's');
								break;
								
							case 'orgchart-label':
								$array[0]['children'][] = array('id' => $element_to_add . $chaine_alphabetique_aleatoire, 'ty' => 'd');
								break;
						}
					}
				}
			} elseif( get_post_type($element_to_add) == 'collaborateur' ) {
				$array[] = array('id' => $element_to_add, 'ty' => 's');
				update_post_meta($orgchart_id, 'orgchart_boss', $element_to_add . $chaine_alphabetique_aleatoire);
			}
			$json = json_encode( $array );
			update_post_meta($orgchart_id, 'orgchart_data', $json);
		}
		
		$dragAndDrop = "true";
	} else {
		$dragAndDrop = "false";
	}
	
	$trombi_print = (get_query_var('trombi_print')) ? get_query_var('trombi_print') : 0;
	if ( $trombi_print != 1 ) {
		$vignette_color_background_top = $clicface_trombi_settings['vignette_color_background_top'];
		$vignette_color_background_bottom = $clicface_trombi_settings['vignette_color_background_bottom'];
		$vignette_color_border = $clicface_trombi_settings['vignette_color_border'];
		$vignette_border_thickness = $clicface_trombi_settings['vignette_border_thickness'] . 'px';
		$vignette_border_radius = $clicface_trombi_settings['vignette_border_radius'] . 'px';
		$vignette_min_height = $clicface_organi_settings['vignette_min_height'] . 'px';
		$vignette_min_width = $clicface_organi_settings['vignette_min_width'] . 'px';
		$organi_line_color = $clicface_organi_settings['organi_line_color'];
		$output .= <<<EOF
<style type="text/css">
#clicface-chart .clicface-jOrgChart .a,
#clicface-chart .clicface-jOrgChart .d,
#clicface-chart .clicface-jOrgChart .e,
#clicface-chart .clicface-jOrgChart .f,
#clicface-chart .clicface-jOrgChart .h,
#clicface-chart .clicface-jOrgChart .s {
  min-width: $vignette_min_width;
}

#clicface-chart .clicface-jOrgChart .s {
  min-height: $vignette_min_height;
}

#clicface-chart .clicface-jOrgChart .node {
  background-color: $vignette_color_background_top;
  background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from($vignette_color_background_top), to($vignette_color_background_bottom));
  border: $vignette_border_thickness solid $vignette_color_border;
  border-radius: $vignette_border_radius;
}

#clicface-chart .clicface-jOrgChart .clicface-down {
  background-color: $organi_line_color;
}

#clicface-chart .clicface-jOrgChart .clicface-top {
  border-top-color: $organi_line_color;
}

#clicface-chart .clicface-jOrgChart .clicface-bottom {
  border-bottom-color: $organi_line_color;
}

#clicface-chart .clicface-jOrgChart .clicface-left {
  border-right-color: $organi_line_color;
}

#clicface-chart .clicface-jOrgChart .clicface-right {
  border-left-color: $organi_line_color;
}

#clicface-chart .clicface-jOrgChart .clicface-side-fin {
  background-color: $organi_line_color;
}

#clicface-chart .clicface-jOrgChart .clicface-side-epais {
  background-color: $organi_line_color;
}
</style>
EOF;
		if ( $clicface_trombi_settings['vignette_ext_drop_shadow'] == 'oui' ) {
			$output .= '<style type="text/css">#clicface-chart .clicface-jOrgChart .node { box-shadow: 3px 3px 3px rgba(0, 0, 0, 0.5); }</style>';
		}
		if ( $clicface_trombi_settings['vignette_int_drop_shadow'] == 'oui' ) {
			$output .= '<style type="text/css">#clicface-chart .clicface-jOrgChart .node .clicface-trombi-vignette .piklist-label-container a img { box-shadow: 2px 2px 12px #555; }</style>';
		}
	}
	
	$boss_id = get_post_meta($orgchart_id, 'orgchart_boss', true);
	
	function array_depth_count(&$array, $count=array(), $depth=1) {
		$chemin_url = explode( '/', $_SERVER['REQUEST_URI'] );
		global $ExtraLink;
		global $WindowTarget;
		global $ExtraClassImg;
		global $ExtraClassTxt;
		global $clicface_organi_settings;
		global $boss_id;
		
		foreach ( $array as &$value ) {
			if ( is_array($value) ) {
				if ( isset($value['id']) ) {
					if ( $value['ty'] != 'o' && $value['ty'] != 'd' && $value['ty'] != 'e' && $value['ty'] != 'f' ) {
						$collaborateur = get_post ( $value['id'] );
						if ( $collaborateur != NULL ) {
							$collaborateur_objet = new clicface_Collaborateur( $collaborateur->ID );
							$value['cellule'] = '<div class="clicface-trombi-vignette">';
							$value['cellule'] .= '<div class="piklist-label-container"><a href="' . $collaborateur_objet->Link . $ExtraLink . '" target="'. $WindowTarget .'" ' . $ExtraClassImg . '>' . $collaborateur_objet->PhotoThumbnail . '</a></div>';
							$value['cellule'] .= '<a class="clicface-trombi-collaborateur ' . $ExtraClassTxt . '" href="' . $collaborateur_objet->Link . $ExtraLink . '" target="'. $WindowTarget .'" ' . $ExtraClassImg . '><div>';
							$value['cellule'] .= '<div class="clicface-trombi-employee-name">' . $collaborateur_objet->Nom . '</div>';
							$value['cellule'] .= '<div class="clicface-trombi-employee-function">' . $collaborateur_objet->Fonction . '</div>';
							if ( $clicface_organi_settings['organi_display_service'] == 'oui' && $collaborateur_objet->Service != NULL ) {
								$value['cellule'] .= '<div class="clicface-trombi-employee-service">' . $collaborateur_objet->Service . '</div>';
							}
							if ( $clicface_organi_settings['organi_display_phone'] == 'oui' && $collaborateur_objet->TelephoneFixe != NULL ) {
								$value['cellule'] .= '<br />' . __('Phone:', 'clicface-trombi') . ' ' . $collaborateur_objet->TelephoneFixe;
							}
							if ( $clicface_organi_settings['organi_display_cellular'] == 'oui' && $collaborateur_objet->TelephonePortable != NULL ) {
								$value['cellule'] .= '<br />' . __('Cell:', 'clicface-trombi') . ' ' . $collaborateur_objet->TelephonePortable;
							}
							if ( $clicface_organi_settings['organi_display_email'] == 'oui' && $collaborateur_objet->Mail != NULL ) {
								$value['cellule'] .= '<br />' . $collaborateur_objet->Mailto;
							}
							if ( $collaborateur_objet->DisplayPagetLink == 'oui' && $boss_id != $collaborateur->ID ) {
								$value['cellule'] .= '<br /><br /><a href="' . get_permalink( $collaborateur_objet->PageLinkID ) .'"><img src="' . plugins_url( 'img/arrow-up.png' , __FILE__ ) . '"/></a>';
							}
							$value['cellule'] .= '</a></div>';
						} else {
							$value['cellule'] = "<br /><br /><i>" . __('employee removed', 'clicface-trombi') . "</i><br />";
						}
						if ( $chemin_url[1] == 'printpdf' ) {
							$value['gestion'] = '';
						} else {
							if ( current_user_can('edit_pages') && $value['id'] != $boss_id ) {
								if ( $collaborateur_objet->DisplayPagetLink == 'oui' ) {
									$value['gestion'] = '<div class="gestion"><div class="suppression" id="supprimer_' . $value['id'] . '"><img src="' . plugins_url('clicface-organi/img/remove-icon.png') . '" title="' . __('Remove employee', 'clicface-trombi') . '" /></div><div class="flechetransparente"><a href="' . get_permalink( $collaborateur_objet->PageLinkID ) .'"><img src="' . plugins_url( 'img/arrow-up-vide.png' , __FILE__ ) . '"/></a><div class="permutation" id="permuter_' . $value['id'] . '"><img src="' . plugins_url('clicface-organi/img/switch-icon.png') . '" title="' . __('Single click to toggle subordinate/assistant on the right.', 'clicface-trombi') . '&#013;' . __('Single click + Shift to toggle subordinate/assistant on the left.', 'clicface-trombi') . '&#013;' . __('Double-click to display a second boss.', 'clicface-trombi') . '" /></div></div></div>';
								} else {
									$value['gestion'] = '<div class="gestion"><div class="suppression" id="supprimer_' . $value['id'] . '"><img src="' . plugins_url('clicface-organi/img/remove-icon.png') . '" title="' . __('Remove employee', 'clicface-trombi') . '" /></div><div class="flechetransparente"></div><div class="permutation" id="permuter_' . $value['id'] . '"><img src="' . plugins_url('clicface-organi/img/switch-icon.png') . '" title="' . __('Single click to toggle subordinate/assistant on the right.', 'clicface-trombi') . '&#013;' . __('Single click + Shift to toggle subordinate/assistant on the left.', 'clicface-trombi') . '&#013;' . __('Double-click to display a second boss.', 'clicface-trombi') . '" /></div></div>';
								}
							} else {
								$value['gestion'] = '';
							}
						}
					}
					
					if ( $value['ty'] == 'd' || $value['ty'] == 'e' || $value['ty'] == 'f' ) {
						$label = get_post ( $value['id'] );
						if ( $label != NULL ) {
							$label_objet = new clicface_Label( $label->ID );
							$value['cellule'] = '<div class="clicface-trombi-vignette"><div class="piklist-label-container">';
							$value['cellule'] .= '<br />&nbsp;<br /><div class="clicface-trombi-employee-name">' . $label_objet->Nom . '</div>';
							if ( $label_objet->DisplayPagetLink == 'oui' ) {
								$value['cellule'] .= '<br /><br /><a href="' . get_permalink( $label_objet->PageLinkID ) .'"><img src="' . plugins_url( 'img/arrow-up.png' , __FILE__ ) . '" style="box-shadow: none;" /></a>';
							}
							$value['cellule'] .= '</div></div>';
						} else {
							$value['cellule'] = "<br /><br /><i>" . __('label removed', 'clicface-trombi') . "</i><br />";
						}
						if ( $chemin_url[1] == 'printpdf' ) {
							$value['gestion'] = '';
						} else {
							if ( current_user_can('edit_pages') && $value['id'] != $boss_id ) {
								if ( $label_objet->DisplayPagetLink == 'oui' ) {
									$value['gestion'] = '<div class="gestion"><div class="suppression" id="supprimer_' . $value['id'] . '"><img src="' . plugins_url('clicface-organi/img/remove-icon.png') . '" title="' . __('Remove label', 'clicface-trombi') . '" /></div><div class="flechetransparente"><a href="' . get_permalink( $label_objet->PageLinkID ) .'"><img src="' . plugins_url( 'img/arrow-up-vide.png' , __FILE__ ) . '"/></a><div class="permutation" id="permuter_' . $value['id'] . '"><img src="' . plugins_url('clicface-organi/img/switch-icon.png') . '" title="' . __('Single click to toggle subordinate/assistant on the right.', 'clicface-trombi') . '&#013;' . __('Single click + Shift to toggle subordinate/assistant on the left.', 'clicface-trombi') . '&#013;' . __('Double-click to display a second boss.', 'clicface-trombi') . '" /></div></div></div>';
								} else {
									$value['gestion'] = '<div class="gestion"><div class="suppression" id="supprimer_' . $value['id'] . '"><img src="' . plugins_url('clicface-organi/img/remove-icon.png') . '" title="' . __('Remove label', 'clicface-trombi') . '" /></div><div class="flechetransparente"></div><div class="permutation" id="permuter_' . $value['id'] . '"><img src="' . plugins_url('clicface-organi/img/switch-icon.png') . '" title="' . __('Single click to toggle subordinate/assistant on the right.', 'clicface-trombi') . '&#013;' . __('Single click + Shift to toggle subordinate/assistant on the left.', 'clicface-trombi') . '&#013;' . __('Double-click to display a second boss.', 'clicface-trombi') . '" /></div></div>';
								}
							} else {
								$value['gestion'] = '';
							}
						}
					}
				}
				array_depth_count($value, $count, $depth + 1);
			}
		}
	}
	
	array_depth_count($array);
	$json = json_encode($array);
	
	$ajaxurl = admin_url('admin-ajax.php');
	$confirmation_message = __('You are about to remove this item from the org chart. All subordinate elements will also be removed from the org chart.', 'clicface-trombi');
	$impossible_message = __('Impossible to modify', 'clicface-trombi');
	
	$output .= <<<EOF
<script type="text/javascript">
	var jsonData = $json;
	var ajaxurl = '$ajaxurl';
	var clicface_user = "gestionnaire";
	var clicface_organi_zoom = 100;
	jQuery(document).ready(function () {
		if ( !jQuery.curCSS ) { jQuery.curCSS = jQuery.css; }
		document.getElementById('clicface-org').innerHTML = parseNodes(jsonData).innerHTML;
		jQuery("#clicface-org").jOrgChart({ chartElement: "#clicface-chart", dragAndDrop: $dragAndDrop });
		jQuery('#clicface-org').contentChange(function() {
			var out = [];
			function processOneLi(node) {
				var retVal = {
					"id": node.attr("id"),
					"ty": node.attr("class")
				};
				node.find("> ul > li").each(function() {
					if (!retVal.hasOwnProperty("children")) {
						retVal.children = [];
					}
					retVal.children.push(processOneLi(jQuery(this)));
				});
				return retVal;
			}
			jQuery('#clicface-org').children("li").each(function() {
				out.push(processOneLi(jQuery(this)));
			});
			
			var structure = JSON.stringify(out);
			
			jQuery.post(ajaxurl, {"action" : 'my_organi_modification_submit', "_wpnonce": "$nonce", "orgchart": "$orgchart_id", "structure": structure}, function(response) {});
		});
		
		jQuery(".suppression").live('click', function() {
			function html_entity_decode(str) {
				var ta=document.createElement("textarea");
				ta.innerHTML=str.replace(/</g,"<").replace(/>/g,">");
				return ta.value;
			}
			var confirmation_message = "$confirmation_message";
			var confirmation_dialogue = confirm( html_entity_decode(confirmation_message) );
			if (confirmation_dialogue) {
				var id = jQuery(this).attr('id');
				id = id.replace("supprimer_","");
				
				var source = id;
				var target = 'cellule_' + id;
				
				jQuery('#' + source).remove();
				
				jQuery('#clicface-chart').empty();
				jQuery("#clicface-org").jOrgChart({ chartElement: "#clicface-chart", dragAndDrop: true });
			}
		});
		
		var timeout;
		var delay = 200;  // Delay in milliseconds
		
		jQuery(".permutation").live('click', function(event) {
			var id = jQuery(this).attr('id');
			id = id.replace("permuter_","");
			
			timeout = setTimeout(function() {
				if ( timeout != null && event.shiftKey ) {
					var source = id;
					var target = 'cellule_' + id;
					
					if ( typeof jQuery('#' + source).parent().parent("li").attr("id") != 'undefined' ) {
						if (
								(
									jQuery('#' + source).hasClass("s")
									&& ( jQuery('#' + source).parent().parent("li").children().children("li.s").length >= 2 || jQuery('#' + source).parent().parent("li").children().children("li.d").length >= 2 || ( jQuery('#' + source).parent().parent("li").children().children("li.s").length >= 1 && jQuery('#' + source).parent().parent("li").children().children("li.d").length >= 1 ) )
									&& jQuery('#' + source).children().children("li.a").length == 0
									&& jQuery('#' + source).children().children("li.b").length == 0
									&& jQuery('#' + source).children().children("li").children().children("li").length == 0
									&& jQuery('#' + source).parent().parent("li").hasClass("s")
								)
								|| jQuery('#' + source).hasClass("b")
							) {
							if ( jQuery('#' + source).hasClass("s") && jQuery('#' + source).parent().parent("li").children().children("li.b").length >= 1 ) {
								jQuery('#' + source).parent().parent("li").children().children("li.b").toggleClass("s");
								jQuery('#' + source).parent().parent("li").children().children("li.b").toggleClass("b");
							}
							
							jQuery('#' + source).toggleClass("s");
							jQuery('#' + source).toggleClass("b");
							
							jQuery('#clicface-chart').empty();
							jQuery("#clicface-org").jOrgChart({ chartElement: "#clicface-chart", dragAndDrop: true });
							//coloriser();
						} else if (
								(
									jQuery('#' + source).hasClass("d")
									&& ( jQuery('#' + source).parent().parent("li").children().children("li.s").length >= 2 || jQuery('#' + source).parent().parent("li").children().children("li.d").length >= 2 || ( jQuery('#' + source).parent().parent("li").children().children("li.s").length >= 1 && jQuery('#' + source).parent().parent("li").children().children("li.d").length >= 1 ) )
									&& jQuery('#' + source).children().children("li.a").length == 0
									&& jQuery('#' + source).children().children("li.b").length == 0
									&& jQuery('#' + source).children().children("li").children().children("li").length == 0
									&& jQuery('#' + source).parent().parent("li").hasClass("s")
								)
								|| jQuery('#' + source).hasClass("f")
							) {
							if ( jQuery('#' + source).hasClass("d") && jQuery('#' + source).parent().parent("li").children().children("li.f").length >= 1 ) {
								jQuery('#' + source).parent().parent("li").children().children("li.f").toggleClass("d");
								jQuery('#' + source).parent().parent("li").children().children("li.f").toggleClass("f");
							}
							
							jQuery('#' + source).toggleClass("d");
							jQuery('#' + source).toggleClass("f");
							
							jQuery('#clicface-chart').empty();
							jQuery("#clicface-org").jOrgChart({ chartElement: "#clicface-chart", dragAndDrop: true });
							//coloriser();
						} else {
							alert('$impossible_message');
						}
					} else {
						alert('$impossible_message');
					}
				}
			
				if ( timeout != null && !event.shiftKey ) {
					var source = id;
					var target = 'cellule_' + id;
					
					if ( typeof jQuery('#' + source).parent().parent("li").attr("id") != 'undefined' ) {
						if (
								(
									jQuery('#' + source).hasClass("s")
									&& ( jQuery('#' + source).parent().parent("li").children().children("li.s").length >= 2 || jQuery('#' + source).parent().parent("li").children().children("li.d").length >= 2 || ( jQuery('#' + source).parent().parent("li").children().children("li.s").length >= 1 && jQuery('#' + source).parent().parent("li").children().children("li.d").length >= 1 ) )
									&& jQuery('#' + source).children().children("li.a").length == 0
									&& jQuery('#' + source).children().children("li.b").length == 0
									&& jQuery('#' + source).children().children("li").children().children("li").length == 0
									&& jQuery('#' + source).parent().parent("li").hasClass("s")
								)
								|| jQuery('#' + source).hasClass("a")
							) {
							if ( jQuery('#' + source).hasClass("s") && jQuery('#' + source).parent().parent("li").children().children("li.a").length >= 1 ) {
								jQuery('#' + source).parent().parent("li").children().children("li.a").toggleClass("s");
								jQuery('#' + source).parent().parent("li").children().children("li.a").toggleClass("a");
							}
							
							jQuery('#' + source).toggleClass("s");
							jQuery('#' + source).toggleClass("a");
							
							jQuery('#clicface-chart').empty();
							jQuery("#clicface-org").jOrgChart({ chartElement: "#clicface-chart", dragAndDrop: true });
							//coloriser();
						} else if (
								(
									jQuery('#' + source).hasClass("d")
									&& ( jQuery('#' + source).parent().parent("li").children().children("li.s").length >= 2 || jQuery('#' + source).parent().parent("li").children().children("li.d").length >= 2 || ( jQuery('#' + source).parent().parent("li").children().children("li.s").length >= 1 && jQuery('#' + source).parent().parent("li").children().children("li.d").length >= 1 ) )
									&& jQuery('#' + source).children().children("li.a").length == 0
									&& jQuery('#' + source).children().children("li.b").length == 0
									&& jQuery('#' + source).children().children("li").children().children("li").length == 0
									&& jQuery('#' + source).parent().parent("li").hasClass("s")
								)
								|| jQuery('#' + source).hasClass("e")
							) {
							if ( jQuery('#' + source).hasClass("d") && jQuery('#' + source).parent().parent("li").children().children("li.e").length >= 1 ) {
								jQuery('#' + source).parent().parent("li").children().children("li.e").toggleClass("d");
								jQuery('#' + source).parent().parent("li").children().children("li.e").toggleClass("e");
							}
							
							jQuery('#' + source).toggleClass("d");
							jQuery('#' + source).toggleClass("e");
							
							jQuery('#clicface-chart').empty();
							jQuery("#clicface-org").jOrgChart({ chartElement: "#clicface-chart", dragAndDrop: true });
							//coloriser();
						} else {
							alert('$impossible_message');
						}
					} else {
						alert('$impossible_message');
					}
				}
				timeout = null;
			}, delay)
		});
		
		jQuery(".permutation").live('dblclick', function() {
			if (timeout) {
				// Clear the timeout since this is a double-click and we don't want
				// the 'click-only' code to run.
				clearTimeout(timeout);
				timeout = null;
			}
			
			var id = jQuery(this).attr('id');
			id = id.replace("permuter_","");
			
			var source = id;
			var target = 'cellule_' + id;
			
			if ( typeof jQuery('#' + source).parent().parent("li").attr("id") != 'undefined' ) {
				if ( (jQuery('#' + source).hasClass("s")
					&& jQuery('#' + source).parent().parent("li").children().children("li.s").length >= 2
					&& jQuery('#' + source).children().children("li.a").length == 0
					&& jQuery('#' + source).children().children("li.s").length == 0
					&& jQuery('#' + source).children().children("li").children().children("li").length == 0
					&& jQuery('#' + source).parent().parent("li").hasClass("s"))
					|| (jQuery('#' + source).hasClass("a")
					&& jQuery('#' + source).children().children("li.a").length == 0
					&& jQuery('#' + source).children().children("li.s").length == 0
					&& jQuery('#' + source).children().children("li").children().children("li").length == 0
					&& jQuery('#' + source).parent().parent("li").hasClass("s"))
					|| jQuery('#' + source).hasClass("h") ) {
					if ( jQuery('#' + source).hasClass("s") && jQuery('#' + source).parent().parent("li").children().children("li.h").length >= 1 ) {
						jQuery('#' + source).parent().parent("li").children().children("li.h").toggleClass("s");
						jQuery('#' + source).parent().parent("li").children().children("li.h").toggleClass("h");
					}
					
					if ( jQuery('#' + source).hasClass("a") && jQuery('#' + source).parent().parent("li").children().children("li.h").length >= 1 ) {
						jQuery('#' + source).parent().parent("li").children().children("li.h").toggleClass("a");
						jQuery('#' + source).parent().parent("li").children().children("li.h").toggleClass("h");
					}
					
					if ( jQuery('#' + source).hasClass("a") ) {
						jQuery('#' + source).toggleClass("a");
						jQuery('#' + source).toggleClass("h");
					} else {
						jQuery('#' + source).toggleClass("s");
						jQuery('#' + source).toggleClass("h");
					}
					
					jQuery('#clicface-chart').empty();
					jQuery("#clicface-org").jOrgChart({ chartElement: "#clicface-chart", dragAndDrop: true });
					coloriser();
				} else {
					alert('$impossible_message');
				}
			} else {
				alert('$impossible_message');
			}
		});
		
		jQuery("#clicface_reload_page_button").live('click', function() {
			location.reload();
		});
		
		jQuery('#clicface-organi-add-box-button').click(function(){
			jQuery('#clicface-organi-add-box').toggle('slow');
			jQuery('#clicface-organi-add-label').hide();
		});
		
		jQuery('#clicface-organi-add-label-button').click(function(){
			jQuery('#clicface-organi-add-box').hide();
			jQuery('#clicface-organi-add-label').toggle('slow');
		});
	});
</script>
<div align="right" class="clicface-trombi-print-mask">$boutons</div>
<ul id="clicface-org" style="display:none"></ul>
<div id="clicface-chart" class="clicface-orgChart"></div>
EOF;
	
	return $output;
}

add_action( 'wp_ajax_my_organi_modification_submit', 'my_organi_modification_submit' );
function my_organi_modification_submit() {
	$nonce = $_POST['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'clicface_organi_g98er49e8hpcwzt8' ) ) {
		die( 'Security check' ); 
	}
	
	$orgchart_id = $_POST['orgchart'];
	if ( get_post_type( $orgchart_id ) != 'orgchart' ) {
		$response = json_encode( array( 'success' => false ) );
	} else {
		update_post_meta($orgchart_id, 'orgchart_data', $_POST['structure']);
		$response = json_encode( array( 'success' => true ) );
	}
	header( "Content-Type: application/json" );
	echo $response;
	exit;
}

add_action('init','add_my_organi_service');
function add_my_organi_service() {
	global $wp;
	$wp->add_query_var('co2add');
}