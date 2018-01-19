<?php
require_once('./Services/Mail/classes/class.ilMail.php');
require_once('./Services/UIComponent/Button/classes/class.ilLinkButton.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerVideo.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerFolder.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerTree.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Subscription/class.vidmSubscription.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Administration/class.ilVideoManagerTreeExplorerGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Administration/class.ilVideoManagerAdminTableGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Administration/class.ilVideoManagerVideoFormGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Administration/class.ilVideoManagerVideoDetailsGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Administration/class.ilVideoManagerFolderFormGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Subscription/class.vidmSubscription.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Count/class.vidmCountTableGUI.php');

/**
 * Class ilVideoManagerGUI
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilVideoManagerAdminGUI: ilRouterGUI, ilUIPluginRouterGUI
 * @ilCtrl_Calls      ilVideoManagerAdminGUI: ilVideoManagerAdminTableGUI, vidmSubscriptionGUI
 */
class ilVideoManagerAdminGUI {

	const CMD_VIEW = 'view';
	const CMD_ADD_FOLDER = 'addFolder';
	const CMD_ADD_VIDEO = 'addVideo';
	const CMD_SHOW_FOLDER_CONTENT = 'showFolderContent';
	const PARAM_NODE_ID = 'node_id';
	const CMD_CREATE = 'create';
	const CMD_EDIT = 'edit';
	const CMD_SHOW_STATISTICS = 'showStatistics';
	/**
	 * @var ilCtrl
	 */
	public $ctrl;
	/**
	 * @var ilTemplate
	 */
	public $tpl;
	/**
	 * @var ilTabsGUI
	 */
	public $tabs;
	/**
	 * @var ilAccessHandler
	 */
	protected $ilAccess;
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;
	/**
	 * @var ilLocatorGUI
	 */
	protected $ilLocator;
	/**
	 * @var ilVideoManagerTree
	 */
	public $tree;
	/**
	 * @var ilVideoManagerVideo|ilVideoManagerFolder
	 */
	public $object;
	/**
	 * @var ilVideoManagerPlugin
	 */
	protected $pl;
	/**
	 * @var ilRbacSystem
	 */
	protected $rbacsystem;

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
		$this->rbacsystem = $DIC->rbac()->system();

		$this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/administration_gui.css');

		$_GET[self::PARAM_NODE_ID] ? $this->object = ilVideoManagerObject::find($_GET[self::PARAM_NODE_ID]) : $this->object = ilVideoManagerObject::__getRootFolder();
	}


	public function executeCommand() {
		if (! $_GET[self::PARAM_NODE_ID]) {
			$_GET[self::PARAM_NODE_ID] = ilVideoManagerObject::__getRootFolder()->getId();
		}
		if (ilVideoManagerObject::__getTypeForId($_GET[self::PARAM_NODE_ID]) == ilVideoManagerObject::TYPE_VID) {
			$this->object = new ilVideoManagerVideo($_GET[self::PARAM_NODE_ID]);
		} else {
			$this->object = new ilVideoManagerFolder($_GET[self::PARAM_NODE_ID]);
		}
		$this->prepareOutput();
		$this->checkPermission();

		$cmd = $this->ctrl->getCmd(self::CMD_VIEW);

		//Otherwise move-Objects would not work
		if ($cmd != "cut" && $cmd != "moveMultiple") {
			$this->showTree();
		}

		switch ($cmd) {
			case self::CMD_ADD_FOLDER:
				$this->addFolder();
				break;
			case self::CMD_ADD_VIDEO:
				$this->addVideo();
				break;
			case self::CMD_CREATE:
				$this->create();
				break;
			case 'createFolder':
				$_POST['create_type'] = ilVideoManagerObject::TYPE_FLD;
				$this->createFolder();
				break;
			case 'showTree':
				$this->showTree();
				break;
			case 'saveFolder':
				$this->saveFolder();
				break;
			case 'delete':
				$this->delete();
				break;
			case 'deleteMultiple':
				$this->confirmDelete();
				break;
			case 'confirmDelete':
				$this->confirmDelete();
				break;
			case 'cancel':
				$this->cancel();
				break;
			case 'editfld':
				$this->editFolder();
				break;
			case 'editvid':
				$this->editVideo();
				break;
			case 'cut':
				$this->cut();
				break;
			case 'moveMultiple':
				$this->cut();
				break;
			case 'performPaste':
				$this->performPaste();
				break;
			case self::CMD_VIEW:
				$this->view();
				break;
			default:
				$this->$cmd();
		}

		$this->tpl->getStandardTemplate();
		$this->tpl->show();
	}


	public function view() {
		switch ($this->object->getType()) {
			case ilVideoManagerObject::TYPE_FLD:
				$this->showFolderContent();
				break;
			case ilVideoManagerObject::TYPE_VID:
				$this->showVideoDetails();
				break;
		}
	}


	public function showFolderContent() {
		include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$adv = new ilAdvancedSelectionListGUI();
		$adv->setListTitle($this->pl->txt("admin_add_new_item"));
		$adv->setPullRight(true);

		if (vidmSubscription::isActive()) {
			$b = ilLinkButton::getInstance();
			$b->setUrl($this->ctrl->getLinkTarget($this, self::CMD_SHOW_STATISTICS));
			$b->setCaption('ui_uihk_video_man_admin_view_statistics');
			$this->toolbar->addButtonInstance($b);
		}

		include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
		$gl = new ilGroupedListGUI();
		$icon_path = ilUtil::getImagePath('icon_cat.svg');
		$gl->addEntry(ilUtil::img($icon_path) . " "
			. $this->pl->txt("admin_add_folder"), $this->ctrl->getLinkTarget($this, self::CMD_ADD_FOLDER), "_top");
		$icon_path = ilUtil::getImagePath('icon_mobs.svg');
		$gl->addEntry(ilUtil::img($icon_path) . " "
			. $this->pl->txt("admin_add_video"), $this->ctrl->getLinkTarget($this, self::CMD_ADD_VIDEO), "_top");
		$gl->setAsDropDown(true);
		$adv->setGroupedList($gl);

		$this->toolbar->addText($adv->getHTML());
		//list items
		$table = new ilVideoManagerAdminTableGUI($this);
		$this->tpl->setContent($table->getHTML());
	}


	public function showVideoDetails() {
		$parent_id = $this->tree->getParentId($this->object->getId());
		$this->ctrl->setParameter($this, self::PARAM_NODE_ID, $parent_id);
		$this->tabs->setBackTarget($this->pl->txt('common_back'), $this->ctrl->getLinkTarget($this, self::CMD_SHOW_FOLDER_CONTENT));
		$vm_video_details = new ilVideoManagerVideoDetailsGUI($this, $this->object);
		$vm_video_details->init();
	}


	public function showTree() {
		$expl_tree = new ilVideoManagerTreeExplorerGUI('vidm_explorer', 'ilVideoManagerAdminGUI', 'showTree', $this->tree);
		$expl_tree->setTypeWhiteList(array( ilVideoManagerObject::TYPE_FLD, ilVideoManagerObject::TYPE_VID ));
		$expl_tree->setPathOpen($_GET[self::PARAM_NODE_ID] ? $_GET[self::PARAM_NODE_ID] : ilVideoManagerObject::__getRootFolder()->getId());
		if (! $expl_tree->handleCommand()) {
			$this->tpl->setLeftNavContent($expl_tree->getHTML());
		}
		$this->ctrl->setParameterByClass('ilVideoManagerAdminGUI', self::PARAM_NODE_ID, $this->object->getId());
	}


	/**
	 * invoked by executeCommand()
	 */
	protected function prepareOutput() {
		$this->addAdminLocatorItems();
		$this->tpl->setLocator();
		$this->setTitleAndDescription();
	}


	/**
	 * called by prepare output
	 */
	protected function setTitleAndDescription() {
		$this->tpl->setTitle($this->object->getTitle());
		$this->tpl->setDescription($this->object->getDescription(100));
		$this->tpl->setTitleIcon($this->object->getIcon());
	}


	/**
	 * called by prepare output
	 */
	protected function addAdminLocatorItems() {
		$_GET[self::PARAM_NODE_ID] ? $end_node = $_GET[self::PARAM_NODE_ID] : $end_node = ilVideoManagerObject::__getRootFolder()->getId();
		$path = $this->tree->getPathFull($end_node, ilVideoManagerObject::__getRootFolder()->getId());
		// add item for each node on path
		foreach ((array)$path as $key => $row) {
			$this->ctrl->setParameterByClass("ilvideomanageradmingui", self::PARAM_NODE_ID, $row["child"]);
			$this->ilLocator->addItem($row["title"], $this->ctrl->getLinkTargetByClass("ilVideoManagerAdminGUI", self::CMD_VIEW), ilFrameTargetInfo::_getFrame("MainContent"));
		}
	}


	protected function addFolder() {
		$form = new ilVideoManagerFolderFormGUI($this, self::CMD_CREATE);
		$this->tpl->setContent($form->getHTML());
	}


	protected function addVideo() {
		$form_gui = new ilVideoManagerVideoFormGUI($this, new ilVideoManagerVideo());
		$this->tpl->setContent($form_gui->getHTML());
	}


	/**
	 * @description for AJAX Drag&Drop Fileupload (Video)
	 */
	protected function create() {
		$form = new ilVideoManagerVideoFormGUI($this, new ilVideoManagerVideo());
		$form->setValuesByPost();
		if(!$form->saveObject()) {
			$this->addVideo();
		}else {
			$this->showFolderContent();
		}
	}


	protected function createFolder() {
		$form = new ilVideoManagerFolderFormGUI($this, self::CMD_CREATE);
		$form->setValuesByPost();
		if (! $form->createFolder()) {
			$this->addFolder();
		}
	}


	protected function confirmDelete() {
		$items_html = '';
		if ($_POST['selected_cmd'] == 'deleteMultiple') {
			//none selected
			if (! $_POST['id']) {
				$this->ctrl->redirect($this, self::CMD_VIEW);
			}

			//Check if one of the items is still being converted
			if (! ilVideoManagerObject::__checkConverting($_POST['id'])) {
				ilUtil::sendFailure($this->pl->txt('msg_deletion_failed'), true);
				$this->ctrl->redirect($this, self::CMD_VIEW);
			}

			foreach ($_POST['id'] as $key => $id) {
				$obj = new ilVideoManagerObject($id);
				$items_html .= ilUtil::img($obj->getIcon()) . " " . $obj->getTitle() . '</br>';
			}
		} else {
			//Check if one of the items is still being converted
			if (! ilVideoManagerObject::__checkConverting($_GET['target_id'])) {
				ilUtil::sendFailure($this->pl->txt('msg_deletion_failed'), true);
				$this->ctrl->redirect($this, self::CMD_VIEW);
			}

			$this->ctrl->setParameter($this, 'target_id', $_GET['target_id']);
			$obj = new ilVideoManagerObject($_GET['target_id']);
			$items_html = ilUtil::img($obj->getIcon()) . " " . $obj->getTitle() . '</br>';
		}

		$this->tabs->clearTargets();
		$this->tabs->setBackTarget($this->pl->txt('common_back'), $this->ctrl->getLinkTarget($this, self::CMD_VIEW));
		ilUtil::sendQuestion($this->pl->txt('admin_confirm_delete'));

		$toolbar = new ilToolbarGUI();
		$button = ilLinkButton::getInstance();
		$button->setCaption($this->pl->txt('common_confirm'),false);
		$button->setUrl($this->ctrl->getLinkTarget($this, 'delete'));
		$toolbar->addButtonInstance($button);
		$button = ilLinkButton::getInstance();
		$button->setCaption($this->pl->txt('common_cancel'),false);
		$button->setUrl($this->ctrl->getLinkTarget($this, 'cancel'));
		$toolbar->addButtonInstance($button);

		$this->tpl->setContent($items_html . '</br>' . $toolbar->getHTML());
	}


	protected function cancel() {
		$this->ctrl->redirect($this, self::CMD_VIEW);
	}


	protected function delete() {
		$ids = array();
		if ($_SESSION['post_vars']['selected_cmd'] == 'deleteMultiple') {
			$ids = $_SESSION['post_vars']['id'];
		} else {
			$ids[] = $_GET['target_id'];
		}

		foreach ($ids as $id) {
			$subtree = $this->tree->getSubTree($this->tree->getNodeData($id));
			foreach ($subtree as $node) {
				$object = new ilVideoManagerObject($node['id']);
				$object->delete();
				$this->tree->_removeEntry(1, $node['id'], 'vidm_tree');
			}
		}

		$this->ctrl->redirect($this, self::CMD_VIEW);
	}


	protected function editFolder() {
		$form = new ilVideoManagerFolderFormGUI($this, self::CMD_EDIT);
		$this->tpl->setContent($form->getHTML());
	}


	protected function saveFolder() {
		$form = new ilVideoManagerFolderFormGUI($this, self::CMD_EDIT);
		$form->setValuesByPost();
		$this->ctrl->saveParameterByClass('ilVideoManagerFolderFormGUI', 'target_id');
		if (! $form->saveFolder()) {
			$this->editFolder();
		}
		$this->ctrl->redirect($this, self::CMD_VIEW);
	}


	protected function editVideo() {
		if (! ilVideoManagerObject::__checkConverting($_GET['target_id'])) {
			ilUtil::sendInfo($this->pl->txt('msg_edit_vid_failed'), true);
			$this->ctrl->redirect($this, self::CMD_VIEW);
		}
		$form = new ilVideoManagerVideoFormGUI($this, new ilVideoManagerVideo($_GET['target_id']));
		$form->fillForm();
		$this->tpl->setContent($form->getHTML());
	}


	protected function updateVideo() {
		$form = new ilVideoManagerVideoFormGUI($this, new ilVideoManagerVideo($_GET['target_id']));
		$form->setValuesByPost();
		if (! $form->saveObject()) {
			$this->editVideo();
		}
	}


	protected function cut() {
		$this->tabs->setBackTarget($this->pl->txt('common_back'), $this->ctrl->getLinkTarget($this, self::CMD_SHOW_FOLDER_CONTENT));
		ilUtil::sendInfo($this->pl->txt('msg_choose_folder'));
		$expl_tree = new ilVideoManagerTreeExplorerGUI('vidm_explorer', 'ilVideoManagerAdminGUI', 'performPaste', $this->tree);
		$expl_tree->setTypeWhiteList(array( ilVideoManagerObject::TYPE_FLD ));
		$subtree = array();

		if ($_POST['selected_cmd'] == 'moveMultiple') {
			//none selected
			if (! $_POST['id']) {
				$this->ctrl->redirect($this, self::CMD_VIEW);
			}

			//Check if one of the items is still being converted
			if (! ilVideoManagerObject::__checkConverting($_POST['id'])) {
				ilUtil::sendFailure($this->pl->txt('msg_move_failed'), true);
				$this->ctrl->redirect($this, self::CMD_VIEW);
			}

			foreach ($_POST['id'] as $key => $id) {
				$subtree = array_merge($subtree, $this->tree->getSubTree($this->tree->getNodeData($id)));
			}
		} else {
			//Check if one of the items is still being converted
			if (! ilVideoManagerObject::__checkConverting($_GET['target_id'])) {
				ilUtil::sendFailure($this->pl->txt('msg_move_failed'), true);
				$this->ctrl->redirect($this, self::CMD_VIEW);
			}
			$subtree = $this->tree->getSubTree($this->tree->getNodeData($_GET['target_id']));
		}

		$expl_tree->setIgnoreSubTree($subtree);
		$this->tpl->setContent($expl_tree->getHTML());
	}


	protected function performPaste() {
		$ids = array();
		if ($_SESSION['post_vars']['selected_cmd'] == 'moveMultiple') {
			$ids = $_SESSION['post_vars']['id'];
		} else {
			$ids[] = $_GET['target_id'];
		}

		foreach ($ids as $id) {
			$obj = new ilVideoManagerObject($id);
			$old_path = $obj->getPath();
			$this->tree->_removeEntry(1, $id, 'vidm_tree');
			$this->tree->insertNode($id, $_GET[self::PARAM_NODE_ID]);
			rename($old_path, $obj->getPath());
		}

		$this->ctrl->redirect($this, self::CMD_VIEW);
	}


	public function notifyUsers($video) {
		$subscriptions = vidmSubscription::where(array( 'cat_id' => $this->tree->getParentId($video->getId()) ));
		$mail = new ilMail(ANONYMOUS_USER_ID);
		foreach ($subscriptions->get() as $subscription) {
			$subject = $this->getNotificationSubject($subscription);
			$message = $this->getNotificationMessage($subscription, $video);
			$mail->sendMail(ilObjUser::_lookupLogin($subscription->getUsrId()), '', '', $subject, $message, array(), array( "system" ));
		}
	}


	/**
	 * @param vidmSubscription $subscription
	 *
	 * @return string
	 */
	protected function getNotificationSubject(vidmSubscription $subscription) {
		$ilLanguage = $this->pl->loadLanguageForUser($subscription->getUsrId());

		return $ilLanguage->txt("ui_uihk_video_man_mail_subject") . " '" . ilVideoManagerFolder::find($subscription->getCatId())->getTitle() . "'";
	}


	/**
	 * @param vidmSubscription    $subscription
	 * @param ilVideoManagerVideo $video
	 *
	 * @return string
	 */
	protected function getNotificationMessage(vidmSubscription $subscription, ilVideoManagerVideo $video) {
		$ilLanguage = $this->pl->loadLanguageForUser($subscription->getUsrId());

		$message = '';
		$message .= ilMail::getSalutation($subscription->getUsrId(), $ilLanguage);

		$message .= "\n\n";
		$message .= $ilLanguage->txt("ui_uihk_video_man_mail_new_upload");
		$message .= "\n\n";
		$message .= $ilLanguage->txt("ui_uihk_video_man_common_category") . ": " . ilVideoManagerFolder::find($subscription->getCatId())->getTitle();
		$message .= "\n\n";
		$message .= $ilLanguage->txt("ui_uihk_video_man_common_video") . ': ' . $video->getTitle() . '';
		$message .= "\n\n";
		$message .= $ilLanguage->txt("ui_uihk_video_man_common_description") . ': ' . $video->getDescription() . '';

		$message .= "\n\n";
		$message .= $ilLanguage->txt('ui_uihk_video_man_mail_view_video') . ': ';
		$this->ctrl->setParameterByClass('ilVideoManagerUserGUI', self::PARAM_NODE_ID, $video->getId());
		$message .= ilUtil::_getHttpPath() . '/' . $this->ctrl->getLinkTargetByClass('ilVideoManagerUserGUI', 'playVideo');

		$message .= ilMail::_getInstallationSignature();

		return $message;
	}


	protected function showStatistics() {
		$this->tabs->setBackTarget($this->pl->txt('common_back'), $this->ctrl->getLinkTarget($this, self::CMD_SHOW_FOLDER_CONTENT));
		$stats = new vidmCountTableGUI($this, $this->tree, $this->object);
		$this->tpl->setContent($stats->getHTML());
	}


	protected function checkPermission() {
		if (!$this->rbacsystem->checkAccess("visible", SYSTEM_FOLDER_ID)) {
			ilUtil::sendFailure($this->pl->txt('msg_no_permission'), true);
			//			$this->ctrl->redirectByClass('ilRouterGUI');
		}
	}


	/**
	 * @return \ilVideoManagerTree
	 */
	public function getTree() {
		return $this->tree;
	}


	/**
	 * @return \ilVideoManagerObject
	 */
	public function getObject() {
		return $this->object;
	}
}