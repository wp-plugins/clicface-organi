<?php
/*
Title: Clicface Organi
Setting: clicface_organi_settings
Tab Order: 10
*/

piklist('field', array(
	'type' => 'radio'
	,'field' => 'organi_display_service'
	,'label' => __('Display Divison', 'clicface-trombi')
	,'value' => 'non'
	,'choices' => array(
		'oui' => __('Yes', 'clicface-trombi')
		,'non' => __('No', 'clicface-trombi')
	)
));

piklist('field', array(
	'type' => 'radio'
	,'field' => 'organi_display_phone'
	,'label' => __('Display Landline Number', 'clicface-trombi')
	,'value' => 'non'
	,'choices' => array(
		'oui' => __('Yes', 'clicface-trombi')
		,'non' => __('No', 'clicface-trombi')
	)
));

piklist('field', array(
	'type' => 'radio'
	,'field' => 'organi_display_cellular'
	,'label' => __('Display Mobile Number', 'clicface-trombi')
	,'value' => 'non'
	,'choices' => array(
		'oui' => __('Yes', 'clicface-trombi')
		,'non' => __('No', 'clicface-trombi')
	)
));

piklist('field', array(
	'type' => 'radio'
	,'field' => 'organi_display_email'
	,'label' => __('Display E-mail', 'clicface-trombi')
	,'value' => 'non'
	,'choices' => array(
		'oui' => __('Yes', 'clicface-trombi')
		,'non' => __('No', 'clicface-trombi')
	)
));

piklist('field', array(
	'type' => 'number'
	,'field' => 'vignette_min_height'
	,'label' => __('Min. height of boxes', 'clicface-trombi') . '<br />' . __('(in pixels)', 'clicface-trombi')
	,'value' => 200
	,'attributes' => array(
		'class' => 'small-text'
	)
));

piklist('field', array(
	'type' => 'number'
	,'field' => 'vignette_min_width'
	,'label' => __('Min. width of boxes', 'clicface-trombi') . '<br />' . __('(in pixels)', 'clicface-trombi')
	,'value' => 160
	,'attributes' => array(
		'class' => 'small-text'
	)
));