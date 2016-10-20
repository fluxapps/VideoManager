<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');

/**
 * Class ilVideoManagerFolder
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilVideoManagerFolder extends ilVideoManagerObject {
	/**
	 * @var String
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4
	 */
	protected $type = 'fld';
	/**
	 * @var int
	 */
	protected $video_count = 0;


	/**
	 * @param int $id
	 */
	public function __construct($id = 0) {
		$this->type = 'fld';
		parent::__construct($id);
	}


	public function afterObjectLoad() {
		parent::afterObjectLoad();
		require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerTree.php');
		$ilVideoManagerTree = new ilVideoManagerTree(1);
		$childs = $ilVideoManagerTree->getChildsByType($this->getId(), 'vid');
		$this->setVideoCount(count($childs));
	}


	/**
	 * @return int
	 */
	public function getVideoCount() {
		return $this->video_count;
	}


	/**
	 * @param int $video_count
	 */
	public function setVideoCount($video_count) {
		$this->video_count = $video_count;
	}
}