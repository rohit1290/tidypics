<?php
/**
 * Adds last notified metadata and sets the notify interval
 */

elgg_set_plugin_setting('notify_interval', 60 * 60 * 24, 'tidypics');

$prefix = elgg_get_config('dbprefix');
$batch = elgg_get_entities([
	'type' => 'object',
	'subtype' => TidypicsAlbum::SUBTYPE,
	'limit' => false,
	'batch' => true,
]);

foreach ($batch as $album) {
	// grab earliest picture and use that as the notification time
	// in old version of tidypics notifications went out only when a new album was populated.
	$q = "SELECT MIN(time_created) as ts FROM {$prefix}entities WHERE container_guid = $album->guid";
	$row = get_data_row($q);

	if ($row) {
		$album->last_notified = $row->ts;
	}
}
