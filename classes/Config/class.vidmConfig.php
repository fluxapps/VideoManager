<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class vidmConfig
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class vidmConfig extends ActiveRecord {

	const TABLE_NAME = 'vidm_config';
	const F_ACTIVATE_VIEW_LOG = 'activate_view_log';
	const F_ACTIVATE_SUBSCRIPTION = 'activate_subscription';
	const F_ROLES = 'roles';


	/**
	 * @return string
	 * @description Return the Name of your Database Table
	 * @deprecated
	 */
	static function returnDbTableName() {
		return self::TABLE_NAME;
	}


	public function getConnectorContainerName() {
		return self::TABLE_NAME;
	}


	/**
	 * @param $id
	 *
	 * @return string
	 */
	public static function getV($id) {
		/**
		 * @var $obj vidmConfig
		 */
		$obj = self::findOrGetInstance($id);

		$value = json_decode($obj->getValue());
		$return = ($value ? $value : $obj->getValue());

		return $return;
	}


	/**
	 * @param $id
	 * @param $value
	 */
	public static function setV($id, $value) {
		/**
		 * @var $obj vidmConfig
		 */
		$obj = self::find($id);
		if (is_array($value)) {
			$encoded_value = json_encode($value);
		} else {
			$encoded_value = $value;
		}
		if ($obj instanceof vidmConfig) {
			$obj->setValue($encoded_value);
			$obj->update();
		} else {
			$obj = new self();
			$obj->setId($id);
			$obj->setValue($encoded_value);
			$obj->create();
		}
	}


	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     128
	 */
	protected $id = '';
	/**
	 * @var string
	 *
	 * @con_has_field true
	 * @con_fieldtype text
	 * @con_length    4000
	 */
	protected $value = '';


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
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}


	/**
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}
}

?>
