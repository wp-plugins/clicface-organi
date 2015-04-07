<?php
/*
Title: Clicface Organi
Post Type: orgchart-label
Order: 5
Collapse: false
*/


piklist('field', array(
	'type' => 'text'
	,'field' => 'label_title'
	,'label' => __('Title', 'clicface-trombi')
	,'attributes' => array(
		'columns' => 6
	)
));

piklist('field', array(
	'type' => 'radio'
	,'field' => 'display_page_link'
	,'label' => __('Display a link to a specific page', 'clicface-trombi')
	,'value' => 'non'
	,'choices' => array(
		'oui' => __('Yes', 'clicface-trombi')
		,'non' => __('No', 'clicface-trombi')
	)
));

piklist('field', array(
	'type' => 'select'
	,'field' => 'link_page_id'
	,'label' => __('Link to this page', 'clicface-trombi')
	,'choices' => piklist(
	get_posts(
		array(
			'numberposts' => -1
			,'order' => 'ASC'
			,'orderby' => 'title'
			,'post_type' => 'page'
			,'post_status' => 'publish'
			)
			,'objects'
		)
		,array(
			'ID'
			,'post_title'
		)
	)
	,'conditions' => array(
		array(
			'field' => 'display_page_link'
			,'value' => 'oui'
		)
	)
));