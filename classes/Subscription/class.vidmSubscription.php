<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.videoman.php');
videoman::loadActiveRecord();
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Config/class.vidmConfig.php');
/**
 * Class vidmSubscription
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class vidmSubscription extends ActiveRecord {

	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_is_primary       true
	 * @db_is_notnull       true
	 * @db_fieldtype        integer
	 * @db_length           4
	 * @con_sequence        true
	 */
	protected $id;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_is_notnull       true
	 * @db_fieldtype        integer
	 * @db_length           4
	 */
	protected $cat_id;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_is_notnull       true
	 * @db_fieldtype        integer
	 * @db_length           4
	 */
	protected $usr_id;


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param $cat_id
	 */
	public function setCatId($cat_id) {
		$this->cat_id = $cat_id;
	}


	/**
	 * @return int
	 */
	public function getCatId() {
		return $this->cat_id;
	}


	/**
	 * @param int $usr_id
	 */
	public function setUsrId($usr_id) {
		$this->usr_id = $usr_id;
	}


	/**
	 * @return int
	 */
	public function getUsrId() {
		return $this->usr_id;
	}


	/**
	 * @param $usr_id
	 * @param $cat_id
	 *
	 * @return bool
	 */
	public static function isSubscribed($usr_id, $cat_id) {
		return (bool)self::where(array( 'usr_id' => $usr_id, 'cat_id' => $cat_id ))->hasSets();
	}


	/**
	 * @param $usr_id
	 * @param $cat_id
	 *
	 * @return bool
	 */
	public function subscribe($usr_id, $cat_id) {
		if (self::isSubscribed($usr_id, $cat_id)) {
			return false;
		}
		$obj = new self();
		$obj->setCatId($cat_id);
		$obj->setUsrId($usr_id);
		$obj->create();

		return true;
	}


	/**
	 * @return string
	 */
	public static function isActive() {
		return vidmConfig::getV(vidmConfig::F_ACTIVATE_SUBSCRIPTION);
	}


	/**
	 * @param $cat_id
	 */
	public static function deleteAllForCatId($cat_id) {
		global $DIC;
		$ilDB = $DIC->database();
		$q = 'DELETE FROM ' . self::returnDbTableName() . ' WHERE cat_id = ' . $ilDB->quote($cat_id, 'integer');
		$ilDB->manipulate($q);
	}


	/**
	 * @return string
	 * @description Return the Name of your Database Table
	 * @deprecated
	 */
	static function returnDbTableName() {
		return "vidm_subscription";
	}
}