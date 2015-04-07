<?php
/*
Title: Clicface Organi
Setting: clicface_organi_settings
Tab: Style
Tab Order: 70
*/

piklist('field', array(
	'type' => 'radio'
	,'field' => 'organi_css_stylesheet'
	,'label' => __('CSS Style Sheet', 'clicface-trombi')
	,'value' => 'style1'
	,'choices' => array(
		'style1' => __('Style 1 (bold)', 'clicface-trombi')
		,'style2' => __('Style 2 (bold)', 'clicface-trombi')
		,'style3' => __('Style 3 (thin)', 'clicface-trombi')
		,'style4' => __('Style 4 (thin)', 'clicface-trombi')
	)
));

piklist('field', array(
	'type' => 'colorpicker'
	,'field' => 'organi_line_color'
	,'label' => __('Line Color', 'clicface-trombi')
	,'value' => '#3388DD'
	,'description' => __('Click to pick a color.', 'clicface-trombi') . ' ' . __('Default color:', 'clicface-trombi') . ' #3388DD'
	,'attributes' => array(
		'class' => 'text'
	)
	,'position' => 'wrap'
));