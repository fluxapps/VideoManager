<?php
require_once('class.videoman.php');
videoman::loadActiveRecord();

/**
 * Class ilVideoManagerObject
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilVideoManagerObject extends ActiveRecord {

	const TYPE_FOLDER = 'fld';
	const TYPE_VIDEO = 'vid';
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_is_unique        true
	 * @db_is_primary       true
	 * @db_is_notnull       true
	 * @db_fieldtype        integer
	 * @db_length           4
	 * @con_sequence        true
	 */
	protected $id = 0;
	/**
	 * @var String
	 *
	 * @db_has_field        true
	 * @db_is_notnull       true
	 * @db_fieldtype        text
	 * @db_length           100
	 */
	protected $title;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4000
	 */
	protected $description;
	/**
	 * @var String
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4
	 */
	protected $type = false;
	/**
	 * @var array
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           256
	 */
	protected $tags = array();
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           8
	 */
	protected $suffix;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        date
	 * @db_length           4
	 */
	protected $create_date;


	/**
	 * @param int $id
	 */
	function __construct($id = 0) {
		parent::__construct($id);
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}


	/**
	 * @return string
	 */
	public function getDescription($length = 0) {
		if ($length == 0 || strlen($this->description) <= $length) {
			return $this->description;
		}

		return substr($this->description, 0, $length) . '...';
	}


	/**
	 * @param String $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return String
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @return String
	 */
	public function getType() {
		return $this->type;
	}


	/**
	 * @param String $suffix
	 */
	public function setSuffix($suffix) {
		$this->suffix = $suffix;
	}


	/**
	 * @return String
	 */
	public function getSuffix() {
		return $this->suffix;
	}


	/**
	 * @param int $create_date
	 */
	public function setCreateDate($create_date) {
		$this->create_date = $create_date;
	}


	/**
	 * @return int
	 */
	public function getCreateDate() {
		return $this->create_date;
	}


	/**
	 * @param array $tags
	 */
	public function setTags($tags) {
		$this->tags = $tags;
	}


	/**
	 * @return array
	 */
	public function getTags() {
		return $this->tags;
	}


	public function getIcon() {
		if ($this->getType() == 'fld') {
			$icon = 'icon_cat';
		} else {
			$icon = 'icon_mobs';
		}

		return ilUtil::getImagePath($icon . '.svg');
	}


	public static function __getTypeForId($id) {
		return ilVideoManagerObject::find($id)->getType();
	}


	/**
	 * @return ActiveRecord
	 */
	public static function __getRootFolder() {
		return parent::find(1);
	}


	/**
	 * @param $ids
	 *
	 * @return bool return false if one of the items is still being converted
	 */
	public static function __checkConverting($ids) {
		$tree = new ilVideoManagerTree(1);
		if (! is_array($ids)) {
			$ids = array( $ids );
		}
		foreach ($ids as $id) {
			if (ilVideoManagerObject::__getTypeForId($id) == 'vid') {
				$vid = new ilVideoManagerVideo($id);
				if ($vid->getStatusConvert() == 1 || $vid->getStatusConvert() == 2) {
					return false;
				}
			} elseif (ilVideoManagerObject::__getTypeForId($id) == 'fld') {
				$childs = $tree->getSubTreeIds($id);
				if (! ilVideoManagerObject::__checkConverting($childs)) {
					return false;
				}
			}
		}

		return true;
	}


	/**
	 * @return string
	 * @description Return the Name of your Database Table
	 * @deprecated
	 */
	static function returnDbTableName() {
		return 'vidm_data';
	}


	/**
	 * the id of this objects parent has to be stored in $_GET['node_id'] to create it properly
	 */
	public function create() {
		parent::create();

		if ($_GET['node_id']) {
			$tree = new ilVideoManagerTree(1);
			$tree->insertNode($this->getId(), $_GET['node_id']);
			$this->recursiveMkdir($this->getPath());
		}
	}


	/**
	 * @return string f.e. for localhost: 'http://localhost/ilias_44/data/client_id/vidm/1/2/5/video_6
	 */
	public function getHttpPath() {
		$path = $this->getTreePath();
		$this->type == 'vid' ? $video_prefix = 'video_' : $video_prefix = '';

		return ilUtil::_getHttpPath() . '/data/' . CLIENT_ID . '/vidm' . $path . '/' . $video_prefix . $this->getId();
	}


	/**
	 * @return string absolute http path
	 */
	public function getAbsoluteHttpPath() {
		return $this->getHttpPath() . '/' . $this->getFileName();
	}


	/**
	 * @return string f.e.: '/var/www/ilias_44/data/client_id/vidm/1/2/5/video_6'
	 */
	public function getPath() {
		$path = $this->getTreePath();
		$this->type == 'vid' ? $video_prefix = 'video_' : $video_prefix = '';

		return ILIAS_ABSOLUTE_PATH . '/' . ILIAS_WEB_DIR . '/' . CLIENT_ID . '/vidm' . $path . '/' . $video_prefix . $this->getId();
	}


	/**
	 * @return string absolute path
	 */
	public function getAbsolutePath() {
		return $this->getPath() . '/' . $this->getFileName();
	}


	/**
	 * @param $path
	 *
	 * @return bool
	 */
	protected function recursiveMkdir($path) {
		$dirs = explode(DIRECTORY_SEPARATOR, $path);
		$count = count($dirs);
		$path = '';
		for ($i = 0; $i < $count; ++ $i) {
			if ($path != '/') {
				$path .= DIRECTORY_SEPARATOR . $dirs[$i];
			} else {
				$path .= $dirs[$i];
			}
			if (! is_dir($path)) {
				ilUtil::makeDir(($path));
			}
		}

		return true;
	}


	/**
	 * @return string
	 */
	public function getFileName() {
		if ($this->getType() == 'fld') {
			return $this->getTitle();
		}

		return $this->getTitle() . '.' . $this->getSuffix();
	}


	public function delete() {
		parent::delete();
		ilUtil::delDir($this->getPath());
	}


	/**
	 * @return string
	 */
	protected function getTreePath() {
		$path = '';
		if (! $this->getId()) {
			return $path;
		}
		$tree = new ilVideoManagerTree(1);
		$parent_id = $tree->getParentId($this->getId());

		if (! $parent_id) {
			return $path;
		}
		foreach ($tree->getPathFull($parent_id, ilVideoManagerObject::__getRootFolder()->getId()) as $node) {
			$path .= '/' . $node['id'];
		}

		return $path;
	}


	/**
	 * @param $field_name
	 *
	 * @return null
	 */
	public function sleep($field_name) {
		switch($field_name) {
			case 'tags':
				return json_encode($this->{$field_name});
				break;
		}
		return NULL;
	}


	/**
	 * @param $field_name
	 * @param $field_value
	 *
	 * @return mixed
	 */
	public function wakeUp($field_name, $field_value) {
		switch($field_name) {
			case 'tags':
				return json_decode($field_value);
				break;
		}
	}
}