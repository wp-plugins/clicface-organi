<?php
/*
Title: Clicface Organi
Post Type: orgchart
Order: 5
Collapse: false
*/


piklist('field', array(
	'type' => 'text'
	,'field' => 'orgchart_title'
	,'label' => __('Title', 'clicface-trombi')
	,'attributes' => array(
		'columns' => 6
	)
));

piklist('field', array(
	'type' => 'select'
	,'field' => 'orgchart_boss'
	,'label' => __('Boss', 'clicface-trombi')
	,'choices' => piklist(
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
	)
));

piklist('field', array(
	'type' => 'text'
	,'field' => 'orgchart_data'
	,'label' => 'data'
	,'capability' => 'clicface_readonly'
));