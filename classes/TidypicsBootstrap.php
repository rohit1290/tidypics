<?php

use Elgg\DefaultPluginBootstrap;

class TidypicsBootstrap extends DefaultPluginBootstrap {

	public function init() {
		// Register an ajax view that allows selection of album to upload images to
		elgg_register_ajax_view('photos/selectalbum');

		// Register an ajax view for the broken images cleanup routine
		elgg_register_ajax_view('photos/broken_images_delete_log');

		// Register an ajax view for the Galleria slideshow
		elgg_register_ajax_view('photos/galleria');

		// Register an ajax view for the River image popups
		elgg_register_ajax_view('photos/riverpopup');

		// Register the JavaScript libs
		elgg_register_esm('tidypics/plupload', elgg_get_simplecache_url('tidypics/js/plupload/plupload.full.min.js'));
		elgg_register_esm('tidypics/plupload_ui', elgg_get_simplecache_url('tidypics/js/plupload/jquery.ui.plupload/jquery.ui.plupload.min.js'));
		elgg_register_esm('tidypics/imgareaselect', elgg_get_simplecache_url('tidypics/js/jquery-imgareaselect.js'));
		elgg_register_esm('tidypics/internaljs/broken_images', elgg_get_simplecache_url('tidypics/internaljs/broken_images.mjs'));
		elgg_register_esm('tidypics/internaljs/create_thumbnail', elgg_get_simplecache_url('tidypics/internaljs/create_thumbnail.mjs'));
		elgg_register_esm('tidypics/internaljs/galleria', elgg_get_simplecache_url('tidypics/internaljs/galleria.mjs'));
		elgg_register_esm('tidypics/internaljs/imtest', elgg_get_simplecache_url('tidypics/internaljs/imtest.mjs'));
		elgg_register_esm('tidypics/internaljs/resize_thumbnails', elgg_get_simplecache_url('tidypics/internaljs/resize_thumbnails.mjs'));
		elgg_register_esm('tidypics/internaljs/slideshow', elgg_get_simplecache_url('tidypics/internaljs/slideshow.mjs'));
		elgg_register_esm('tidypics/internaljs/tagging', elgg_get_simplecache_url('tidypics/internaljs/tagging.mjs'));
		elgg_register_esm('tidypics/internaljs/tidypics', elgg_get_simplecache_url('tidypics/internaljs/tidypics.mjs'));
		elgg_register_esm('tidypics/internaljs/tidypics_windows', elgg_get_simplecache_url('tidypics/internaljs/tidypics_windows.mjs'));
		elgg_register_esm('tidypics/internaljs/uploading', elgg_get_simplecache_url('tidypics/internaljs/uploading.mjs'));
	}

	public function activate() {
		// sets $version based on code
		require_once elgg_get_plugins_path() . "tidypics/version.php";

		$local_version = elgg_get_plugin_setting('version', 'tidypics');
		if ($local_version === null) {
			// set initial version for new install
			$plugin = elgg_get_plugin_from_id('tidypics');
			$plugin->setSetting('version', $version);
		}
	}
}
