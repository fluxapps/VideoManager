<?php

/**
 * Class vidmSubscriptionGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class vidmSubscriptionGUI {

	public function __construct() {
		global $DIC;

		$this->tabs = $DIC->tabs();
		$this->pl = ilVideoManagerPlugin::getInstance();
		$this->tpl = $DIC->ui()->mainTemplate();
		$this->ctrl = $DIC->ctrl();
		$this->ilAccess = $DIC->access();
		$this->ilLocator = $DIC["ilLocator"];
		$this->toolbar = $DIC->toolbar();
		$this->tree = new ilVideoManagerTree(1);
		//$_GET[ilVideoManagerAdminGUI::PARAM_NODE_ID] ? $this->object = ilVideoManagerObject::find($_GET[ilVideoManagerAdminGUI::PARAM_NODE_ID]) : $this->object = ilVideoManagerObject::__getRootFolder();
	}


	public function executeCommand() {
		ilUtil::sendInfo('jippii');
	}
}

?>
