<?php
/**
 * Tidypics Album class
 *
 * @package TidypicsAlbum
 * @author Cash Costello
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */


class TidypicsAlbum extends ElggObject {

	/**
	 * A single-word arbitrary string that defines what
	 * kind of object this is
	 *
	 * @var string
	 */
	const SUBTYPE = 'album';

	/**
	 * Sets the internal attributes
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();

		$this->attributes['subtype'] = self::SUBTYPE;
	}

	/**
	 * Constructor
	 * @param mixed $guid
	 */
	public function __construct($guid = null) {
		parent::__construct($guid);
	}

	/**
	 * Save an album
	 *
	 * @return bool
	 */
	public function save() : bool {
		if (!isset($this->new_album)) {
			$this->new_album = 1;
		}

		if (!isset($this->last_notified)) {
			$this->last_notified = 0;
		}

		if (!parent::save()) {
			return false;
		}

		mkdir(TidypicsTidypics::tp_get_img_dir($this->guid), 0755, true);

		return true;
	}

	/**
	 * Delete album
	 *
	 * @return bool
	 */
	public function delete(bool $recursive = true, bool $persistent = null): bool {
		$this->deleteImages();
		$this->deleteAlbumDir();

		return parent::delete($recursive, $persistent);
	}

	/**
	 * Get the title of the photo album
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Get the URL for this album
	 *
	 * @return string
	 */
	public function getURL(): string {
		$title = elgg_get_friendly_title($this->getTitle());
		$url = "photos/album/$this->guid/$title";
		return elgg_normalize_url($url);
	}

	/**
	 * Get an array of image objects
	 *
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function getImages($limit, $offset = 0) {
		$imageList = $this->getImageList();
		if ($offset > count($imageList)) {
			return [];
		}

		$imageList = array_slice($imageList, $offset, $limit);

		$images = [];
		foreach ($imageList as $guid) {
			$images[] = get_entity($guid);
		}
		return $images;
	}

	/**
	 * View a list of images
	 *
	 * @param array $options Options to pass to elgg_view_entity_list()
	 * @return string
	 */
	public function viewImages(array $options = []) {
		$count = $this->getSize();

		if ($count == 0) {
			return '';
		}

		$defaults = [
			'count' => $count,
			'limit' => (int) get_input('limit', 25),
			'offset' => (int) get_input('offset', 0),
			'full_view' => false,
			'list_type' => 'gallery',
			'list_type_toggle' => false,
			'pagination' => true,
			'gallery_class' => 'tidypics-gallery',
		];

		$options = array_merge($defaults, (array) $options);
		$images = $this->getImages($options['limit'], $options['offset']);

		if (count($images) == 0) {
			return '';
		}

		return elgg_view_entity_list($images, $options);
	}

	/**
	 * Returns the cover image entity
	 * @return TidypicsImage
	 */
	public function getCoverImage() {
		return get_entity($this->getCoverImageGuid());
	}

	/**
	 * Get the GUID of the album cover
	 *
	 * @return int
	 */
	public function getCoverImageGuid() {
		if ($this->getSize() == 0) {
			return 0;
		}

		$guid = $this->cover;
		$imageList = $this->getImageList();
		if (!in_array($guid, $imageList)) {
			// select random photo to be cover
			$index = array_rand($imageList, 1);
			$guid = $imageList[$index];
			$this->cover = $guid;
		}
		return $guid;
	}

	/**
	 * Set the GUID for the album cover
	 *
	 * @param int $guid
	 * @return bool
	 */
	public function setCoverImageGuid($guid) {
		$imageList = $this->getImageList();
		if (!in_array($guid, $imageList)) {
			return false;
		}
		$this->cover = $guid;
		return true;
	}

	/**
	 * Get the number of photos in the album
	 *
	 * @return int
	 */
	public function getSize() {
		return count($this->getImageList());
	}

	/**
	 * Returns an order list of image guids
	 *
	 * @return array
	 */
	public function getImageList() {
		$listString = $this->orderedImages;
		if (!$listString) {
			return [];
		}
		$list = unserialize($listString);

		// if empty don't need to check the permissions.
		if (!$list) {
			return [];
		}

		// check access levels
		$guidsString = implode(',', $list);

		$list = elgg_get_entities([
			'wheres' => function(\Elgg\Database\QueryBuilder $qb, $alias) use ($guidsString) {
 				return $qb->compare('e.guid', 'IN', $guidsString); // comparison of int with string element of imploded array!
			},
			'order_by' => [
				new \Elgg\Database\Clauses\OrderByClause("FIELD(e.guid, $guidsString)"),
			],
			'callback' => 'TidypicsTidypics::tp_guid_callback',
			'limit' => false,
		]);
		return $list;
	}

	/**
	 * Sets the album image order
	 *
	 * @param array $list An indexed array of image guids
	 * @return bool
	 */
	public function setImageList($list) {
		// validate data
		foreach ($list as $guid) {
			if (!filter_var($guid, FILTER_VALIDATE_INT)) {
				return false;
			}
		}

		$listString = serialize($list);
		$this->orderedImages = $listString;
		return true;
	}

	/**
	 * Add new images to the front of the image list
	 *
	 * @param array $list An indexed array of image guids
	 * @return bool
	 */
	public function prependImageList($list) {
		$currentList = $this->getImageList();
		$list = array_merge($list, $currentList);
		return $this->setImageList($list);
	}

	/**
	 * Get the previous image in the album. Wraps around to the last image if given the first.
	 *
	 * @param int $guid GUID of the current image
	 * @return TidypicsImage
	 */
	public function getPreviousImage($guid) {
		$imageList = $this->getImageList();
		$key = array_search($guid, $imageList);
		if ($key === false) {
			return null;
		}
		$key--;
		if ($key < 0) {
			return get_entity(end($imageList));
		}
		return get_entity($imageList[$key]);
	}

	/**
	 * Get the next image in the album. Wraps around to the first image if given the last.
	 *
	 * @param int $guid GUID of the current image
	 * @return TidypicsImage
	 */
	public function getNextImage($guid) {
		$imageList = $this->getImageList();
		$key = array_search($guid, $imageList);
		if ($key === false) {
			return null;
		}
		$key++;
		if ($key >= count($imageList)) {
			return get_entity($imageList[0]);
		}
		return get_entity($imageList[$key]);
	}

	/**
	 * Get the index into the album for a particular image
	 *
	 * @param int $guid GUID of the image
	 * @return int
	 */
	public function getIndex($guid) {
		return array_search($guid, $this->getImageList()) + 1;
	}

	/**
	 * Remove an image from the album list
	 *
	 * @param int $imageGuid
	 * @return bool
	 */
	public function removeImage($imageGuid) {
		$imageList = $this->getImageList();
		$key = array_search($imageGuid, $imageList);
		if ($key === false) {
			return false;
		}

		unset($imageList[$key]);
		$this->setImageList($imageList);

		return true;
	}

	/**
	 * Has enough time elapsed between the last_notified and notify_interval setting?
	 *
	 * @return bool
	 */
	public function shouldNotify() {
		return time() - $this->last_notified > elgg_get_plugin_setting('notify_interval', 'tidypics');
	}

	/**
	 * Delete all the images in this album
	 */
	protected function deleteImages() {
		$images_count = elgg_get_entities([
			'type' => 'object',
			'subtype' => TidypicsImage::SUBTYPE,
			'container_guid' => $this->guid,
			'count' => true,
		]);
		if ($images_count > 0) {
			$images = elgg_get_entities([
				'type' => 'object',
				'subtype' => TidypicsImage::SUBTYPE,
				'container_guid' => $this->guid,
				'limit' => false,
				'batch' => true,
				'batch_inc_offset' => false,
			]);
			foreach ($images as $image) {
				if ($image) {
					$image->delete();
				}
			}
		}
	}

	/**
	 * Delete the album directory on disk
	 */
	protected function deleteAlbumDir() {
		$tmpfile = new ElggFile();
		$tmpfile->setFilename('image/' . $this->guid . '/._tmp_del_tidypics_album_');
		$tmpfile->setSubtype(TidypicsImage::SUBTYPE);
		$tmpfile->owner_guid = $this->owner_guid;
		$tmpfile->container_guid = $this->guid;
		$tmpfile->open("write");
		$tmpfile->write('');
		$tmpfile->close();
		$tmpfile->save();
		$albumdir = preg_replace('#/._tmp_del_tidypics_album_#i', '', $tmpfile->getFilenameOnFilestore());
		$tmpfile->delete();

		// sanity check: must be a directory
		if (!$handle = opendir($albumdir)) {
			return false;
		}

		// loop through all files that might still remain undeleted in this directory and delete them
		// note: this does not delete the corresponding image entities from the database
		while (($file = readdir($handle)) !== false) {
			if (in_array($file, ['.', '..'])) {
				continue;
			}
			$path = "$albumdir/$file";
			unlink($path);
		}

		// remove empty directory
		closedir($handle);
		return rmdir($albumdir);
	}
}
