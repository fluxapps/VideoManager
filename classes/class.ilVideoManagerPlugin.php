<?php
require_once('./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php');
require_once('./Services/MainMenu/classes/class.ilMainMenuGUI.php');
// require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Subscription/classes/class.ilDynamicLanguage.php');
/**
 * Class ilVideoManagerPlugin
 *
 * @author Theodor Truffer <tt@studer-ramimann.ch>
 */
class ilVideoManagerPlugin extends ilUserInterfaceHookPlugin { // implements ilDynamicLanguageInterface



	/**
	 * @var ilSubscriptionPlugin
	 */
	protected static $instance;


	/**
	 * @return ilVideoManagerPlugin
	 */
	public static function getInstance() {
		if (! isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * @return string
	 */
	public function getPluginName() {
		return 'VideoManager';
	}


	/**
	 * @return bool
	 */
	public static function checkPreconditions() {
		require_once('class.videoman.php');
		videoman::loadActiveRecord();
		global $ilCtrl;
		if (! class_exists('ActiveRecord') OR $ilCtrl->lookupClassPath('ilUIPluginRouterGUI') === NULL) {
			return false;
		}

		return true;
	}

	public static function loadActiveRecord() {
		require_once('class.videoman.php');
		if (videoman::is50()) {
			require_once('./Services/ActiveRecord/class.ActiveRecord.php');
		} else {
			require_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.ActiveRecord.php');
		}
	}

	public function isCtrlMainMenuActive() {
		global $ilPluginAdmin;
		/**
		 * @var ilPluginAdmin $ilPluginAdmin
		 */
		return in_array('CtrlMainMenu', $ilPluginAdmin->getActivePluginsForSlot('Services', 'UIComponent', 'uihk'));
	}

	/**
	 * @return bool
	 */
	public function beforeActivation() {
		//if CtrlMainMenu Plugin is active and no Video-Manager entry exists, create one
		if (self::isCtrlMainMenuActive()) {
			require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/EntryTypes/Dropdown/class.ctrlmmEntryDropdown.php');
			require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/EntryTypes/Ctrl/class.ctrlmmEntryCtrl.php');

			$dropdown_entries = ctrlmmEntryDropdown::get();

			$create_dropdown = true;
			foreach ($dropdown_entries as $entry) {
				$translations = ctrlmmTranslation::_getAllTranslationsAsArray($entry->getId());
				foreach ($translations as $l => $t) {
					if ($t == 'Video-Manager'){
						$create_dropdown = false;
					}
				}
			}

			if ($create_dropdown) {
				$dropdown = new ctrlmmEntryDropdown();
				$dropdown->create();

				$admin = new ctrlmmEntryCtrl();
				$admin->setParent($dropdown->getId());
				$admin->setGuiClass('ilUIPluginRouterGUI,ilVideoManagerAdminGUI');
				$admin->create();

				$channels = new ctrlmmEntryCtrl();
				$channels->setParent($dropdown->getId());
				$channels->setGuiClass('ilUIPluginRouterGUI,ilVideoManagerUserGUI');
				$channels->create();

				foreach (array('en', 'de') as $lang) {
					$trans = ctrlmmTranslation::_getInstanceForLanguageKey($dropdown->getId(), $lang);
					$trans->setTitle('Video-Manager');
					$trans->store();

					$trans = ctrlmmTranslation::_getInstanceForLanguageKey($admin->getId(), $lang);
					$trans->setTitle('Administration');
					$trans->store();

					$trans = ctrlmmTranslation::_getInstanceForLanguageKey($channels->getId(), $lang);
					$trans->setTitle('Channels');
					$trans->store();
				}
			}
		}


		return self::checkPreconditions();
	}


	protected function afterActivation() {
		if (!self::isCtrlMainMenuActive()) {
			ilUtil::sendFailure($this->txt('msg_no_ctrlmm'), true);
		}
	}


	/**
	 * @param $usr_id
	 *
	 * @return ilLanguage
	 */
	public function loadLanguageForUser($usr_id) {
		$lng = ilObjUser::_lookupLanguage($usr_id);
		$ilLanguage = new ilLanguage($lng);
		$ilLanguage->loadLanguageModule("ui_uihk_video_man");

		return $ilLanguage;
	}

	//	/**
	//	 * @return string
	//	 */
	//		public function getCsvPath() {
	//		$path = substr(__FILE__, 0, strpos(__FILE__, 'classes')) . 'lang/';
	//		if (file_exists($path . 'lang_custom.csv')) {
	//			$file = $path . 'lang_custom.csv';
	//		} else {
	//			$file = $path . 'lang.csv';
	//		}
	//
	//		return $file;
	//	}
	//
	//
	//	/**
	//	 * @return string
	//	 */
	//	public function getAjaxLink() {
	//		return false;
	//	}
	//
	//
	//	/**
	//	 * @param $key
	//	 *
	//	 * @return mixed
	//	 */
	//	public function txt($key) {
	//		return ilDynamicLanguage::getInstance($this, ilDynamicLanguage::MODE_DEV)->txt($key);
	//	}
}