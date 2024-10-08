<?php

/**
 * Most commented images of the current year
 *
 */

elgg_import_esm('tidypics/internaljs/tidypics');

elgg_push_collection_breadcrumbs('object', TidypicsImage::SUBTYPE);

$title = elgg_echo('collection:object:image:mostcommentedthisyear');

$offset = (int) get_input('offset', 0);
$limit = (int) get_input('limit', 25);

$start = mktime(0, 0, 0, 1, 1, date("Y"));
$end = mktime(0, 0, 0, date("m"), 0, date("Y"));

$result = elgg_list_entities([
	'type' => 'object',
	'subtype' => TidypicsImage::SUBTYPE,
	'limit' => $limit,
	'offset' => $offset,
	'wheres' => function(\Elgg\Database\QueryBuilder $qb, $alias) use($start, $end) {
		$qb->groupBy("$alias.guid");
		$qb->innerJoin($alias, 'entities', 'ce', "ce.container_guid = e.guid");
		$qb->addSelect("count( * ) as views");
		$qb->orderBy('views', 'DESC');
		return $qb->merge([
			$qb->compare('ce.subtype', '=', 'comment', ELGG_VALUE_STRING),
			$qb->compare('ce.time_created', '>', $start, ELGG_VALUE_INTEGER),
		], 'AND');
	},
	'full_view' => false,
	'list_type' => 'gallery',
	'list_type_toggle' => false,
	'gallery_class' => 'tidypics-gallery',
]);

$logged_in_user = elgg_get_logged_in_user_entity();
if (TidypicsTidypics::tidypics_can_add_new_photos(null, $logged_in_user)) {
	elgg_register_menu_item('title', [
		'name' => 'addphotos',
		'href' => "ajax/view/photos/selectalbum/?owner_guid=" . $logged_in_user->getGUID(),
		'text' => elgg_echo("photos:addphotos"),
		'link_class' => 'elgg-button elgg-button-action tidypics-selectalbum-lightbox elgg-lightbox',
		'class' => 'elgg-lightbox',
	]);
}

// only show slideshow link if slideshow is enabled in plugin settings and there are images
if (elgg_get_plugin_setting('slideshow', 'tidypics') && !empty($result)) {
	elgg_import_esm('tidypics/internaljs/slideshow');
	elgg_register_menu_item('title', [
		'name' => 'slideshow',
		'id' => 'slideshow',
		'data-slideshowurl' => elgg_get_site_url() . "photos/mostcommentedthisyear",
		'data-limit' => $limit,
		'data-offset' => $offset,
		'href' => 'ajax/view/photos/galleria',
		'text' => '<i class="far fa-images"></i>',
		'title' => elgg_echo('album:slideshow'),
		'item_class' => 'tidypics-slideshow-button',
		'link_class' => 'elgg-button elgg-button-action tidypics-slideshow-lightbox elgg-lightbox',
		'class' => 'elgg-lightbox',
	]);
}

if (!empty($result)) {
	$content = $result;
} else {
	$content = elgg_echo('tidypics:mostcommentedthisyear:nosuccess');
}

$body = elgg_view_layout('default', [
	'filter' => '',
	'content' => $content,
	'title' => $title,
	'sidebar' => elgg_view('photos/sidebar_im', ['page' => 'all']),
]);

// Draw it
echo elgg_view_page($title, $body);
