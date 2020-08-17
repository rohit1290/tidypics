<?php

echo elgg_format_element('div', ['id' => 'galleria-slideshow', 'style' => 'width:600px;height:500px;'], '');

$img_dummy = elgg_format_element('img', [
	'src' => elgg_get_simplecache_url("tidypics/loader.gif"),
	'title' => '',
	'alt' => '',
	'longdesc' => '',
], '');
echo elgg_format_element('div', ['id' => 'galleria-slideshow-dummy', 'style' => 'display:none;'], $img_dummy);
