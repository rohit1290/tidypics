<?php
use Elgg\Database\Clauses\OrderByClause;

$offset = (int) get_input('offset', 0);
$limit = (int) get_input('limit', 16);

$images = elgg_get_entities([
	'type' => 'object',
	'subtype' => TidypicsImage::SUBTYPE,
	'limit' => $limit,
	'offset' => $offset,
	'annotation_name' => 'fivestar',
	'calculation' => 'count',
	'order_by' => [new OrderByClause('"annotation_calculation"', 'DESC'),],
]);

echo tidypics_slideshow_json_data($images);
