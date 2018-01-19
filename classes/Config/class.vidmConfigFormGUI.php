<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/Form/classes/class.ilMultiSelectInputGUI.php');
require_once('class.vidmConfig.php');

/**
 * Class vidmConfigFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class vidmConfigFormGUI extends ilPropertyFormGUI {

	/**
	 * @var ilVideoManagerConfigGUI
	 */
	protected $parent_gui;
	/**
	 * @var ilVideoManagerPlugin
	 */
	protected $pl;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @param ilVideoManagerConfigGUI $parent_gui
	 */
	public function __construct(ilVideoManagerConfigGUI $parent_gui) {
		parent::__construct();
		global $DIC;
		$this->ctrl = $DIC->ctrl();
		$this->parent_gui = $parent_gui;
		$this->pl = ilVideoManagerPlugin::getInstance();
		//		$this->pl->updateLanguageFiles();
		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
		$this->initForm();
	}


	/**
	 * @param $field
	 *
	 * @return string
	 */
	public function txt($field) {
		return $this->pl->txt('admin_' . $field);
	}


	protected function initForm() {
		$this->setTitle($this->pl->txt('admin_form_title'));

		$cb = new ilCheckboxInputGUI($this->txt(vidmConfig::F_ACTIVATE_VIEW_LOG), vidmConfig::F_ACTIVATE_VIEW_LOG);
		$this->addItem($cb);

		$cb = new ilCheckboxInputGUI($this->txt(vidmConfig::F_ACTIVATE_SUBSCRIPTION), vidmConfig::F_ACTIVATE_SUBSCRIPTION);
		$this->addItem($cb);

		$se = new ilMultiSelectInputGUI($this->txt(vidmConfig::F_ROLES), vidmConfig::F_ROLES);
		$se->setOptions(self::getRoles(ilRbacReview::FILTER_ALL_GLOBAL));
		$this->addItem($se);

		$this->addCommandButtons();
	}


	public function fillForm() {
		$array = array();
		foreach ($this->getItems() as $item) {
			$this->getValuesForItem($item, $array);
		}
		$this->setValuesByArray($array);
	}


	/**
	 * @param ilFormPropertyGUI $item
	 * @param                   $array
	 *
	 * @internal param $key
	 */
	private function getValuesForItem($item, &$array) {
		if (self::checkItem($item)) {
			$key = $item->getPostVar();
			$array[$key] = vidmConfig::getV($key);
			if (self::checkForSubItem($item)) {
				foreach ($item->getSubItems() as $subitem) {
					$this->getValuesForItem($subitem, $array);
				}
			}
		}
	}


	/**
	 * @return bool
	 */
	public function saveObject() {
		if (! $this->checkInput()) {
			return false;
		}
		foreach ($this->getItems() as $item) {
			$this->saveValueForItem($item);
		}

		return true;
	}


	/**
	 * @param  ilFormPropertyGUI $item
	 */
	private function saveValueForItem($item) {
		if (self::checkItem($item)) {
			$key = $item->getPostVar();
			vidmConfig::setV($key, $this->getInput($key));

			if (self::checkForSubItem($item)) {
				foreach ($item->getSubItems() as $subitem) {
					$this->saveValueForItem($subitem);
				}
			}
		}
	}


	/**
	 * @param $item
	 *
	 * @return bool
	 */
	public static function checkForSubItem($item) {
		return ! $item instanceof ilFormSectionHeaderGUI AND ! $item instanceof ilMultiSelectInputGUI;
	}


	/**
	 * @param $item
	 *
	 * @return bool
	 */
	public static function checkItem($item) {
		return ! $item instanceof ilFormSectionHeaderGUI;
	}


	protected function addCommandButtons() {
		$this->addCommandButton('save', $this->pl->txt('admin_form_button_save'));
		$this->addCommandButton('cancel', $this->pl->txt('admin_form_button_cancel'));
	}


	/**
	 * @param int  $filter
	 * @param bool $with_text
	 *
	 * @return array
	 */
	public static function getRoles($filter, $with_text = true) {
		global $DIC;
		$opt = array();
		$role_ids = array();
		foreach ($DIC->rbac()->review()->getRolesByFilter($filter) as $role) {
			$opt[$role['obj_id']] = $role['title'] . ' (' . $role['obj_id'] . ')';
			$role_ids[] = $role['obj_id'];
		}
		if ($with_text) {
			return $opt;
		} else {
			return $role_ids;
		}
	}
}