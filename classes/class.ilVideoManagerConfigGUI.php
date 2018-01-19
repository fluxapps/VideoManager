<?php
require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Config/class.vidmConfig.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Config/class.vidmConfigFormGUI.php');

/**
 * Class ilVideoManagerConfigGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 *
 */
class ilVideoManagerConfigGUI extends ilPluginConfigGUI {

	const CMD_DEFAULT = 'index';
	const CMD_SAVE = 'save';
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;


	public function __construct() {
		global $DIC;
		$this->tpl = $DIC->ui()->mainTemplate();
		$this->ctrl = $DIC->ctrl();
	}


	public function performCommand($cmd) {
		if ($cmd == 'configure') {
			$cmd = self::CMD_DEFAULT;
		}
		switch ($cmd) {
			case self::CMD_DEFAULT:
			case self::CMD_SAVE:
				$this->{$cmd}();
				break;
		}
	}


	public function index() {
		$multaConfigFormGUI = new vidmConfigFormGUI($this);
		$multaConfigFormGUI->fillForm();
		$this->tpl->setContent($multaConfigFormGUI->getHTML());
	}


	protected function save() {
		$form = new vidmConfigFormGUI($this);
		$form->setValuesByPost();
		if ($form->saveObject()) {
			ilUtil::sendSuccess('Saved', true);
			$this->ctrl->redirect($this, self::CMD_DEFAULT);
		}
		$this->tpl->setContent($form->getHTML());
	}
}

?>
