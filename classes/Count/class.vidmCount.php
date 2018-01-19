<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Config/class.vidmConfig.php');

/**
 * Class vidmCount
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class vidmCount extends ActiveRecord {

	/**
	 * @return string
	 */
	static function returnDbTableName() {
		return 'vidm_views';
	}


	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 * @con_sequence   true
	 */
	protected $id = 0;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 * @con_index      true
	 */
	protected $video_id = 0;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 * @con_index      true
	 */
	protected $user_id = 0;


	/**
	 * @param $video_id
	 * @param $user_id
	 *
	 * @return bool
	 */
	public static function up($video_id, $user_id) {
		if (! self::isActive()) {
			return false;
		}
		$obj = new self();
		$obj->setUserId($user_id);
		$obj->setVideoId($video_id);
		$obj->create();
	}


	/**
	 * @param $video_id
	 *
	 * @return int
	 */
	public static function countV($video_id) {
		if (! self::isActive()) {
			return false;
		}

		return self::where(array( 'video_id' => $video_id ))->count();
	}


	/**
	 * @return string
	 */
	public static function isActive() {
		return vidmConfig::getV(vidmConfig::F_ACTIVATE_VIEW_LOG);
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getVideoId() {
		return $this->video_id;
	}


	/**
	 * @param int $video_id
	 */
	public function setVideoId($video_id) {
		$this->video_id = $video_id;
	}


	/**
	 * @return int
	 */
	public function getUserId() {
		return $this->user_id;
	}


	/**
	 * @param int $user_id
	 */
	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}
}

?>
