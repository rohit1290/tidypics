<?php
/**
 * Find and delete any images that don't have an image file
 *
 * iionly@gmx.de
 */

elgg_import_esm('tidypics/internaljs/broken_images');

echo elgg_view_field([
	'#type' => 'submit',
	'text' => elgg_echo('search'),
	'id' => 'elgg-tidypics-broken-images',
]);
