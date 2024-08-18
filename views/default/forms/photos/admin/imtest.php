<?php
/**
 * Test the location of ImageMagick
 */

elgg_import_esm('tidypics/internaljs/imtest');

/* @var $plugin ElggPlugin */
$plugin = elgg_extract('entity', $vars);

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('tidypics:settings:im_path'),
	'name' => 'im_location',
	'value' => $plugin->im_path,
	'required' => true,
]);

echo '<div id="tidypics-im-results"></div>';

$footer = elgg_view_field([
	'#type' => 'submit',
	'text' => elgg_echo('submit'),
]);

elgg_set_form_footer($footer);
