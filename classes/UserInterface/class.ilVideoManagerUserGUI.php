<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Subscription/class.vidmSubscription.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/UserInterface/class.ilVideoManagerPlayVideoGUI.php');
require_once('./Services/Form/classes/class.ilTextInputGUI.php');
require_once("./Services/Rating/classes/class.ilRatingGUI.php");
require_once('class.ilVideoManagerQueryBuilder.php');
require_once('class.xvidChannelListGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/UserInterface/class.xvidListGUI.php');

/**
 * Class ilVideoManagerUserGUI
 *
 * @ilCtrl_IsCalledBy ilVideoManagerUserGUI: ilRouterGUI, ilUIPluginRouterGUI
 * @ilCtrl_Calls      ilVideoManagerUserGUI: ilVideoManagerVideoTableGUI, ilVideoManagerPlayVideoGUI, ilRatingGUI
 */
class ilVideoManagerUserGUI {

	const CMD_PERFORM_SEARCH = 'performSearch';
	const CMD_PLAY_VIDEO = 'playVideo';
	const CMD_SEARCH = 'search';
	const CMD_SUBSCRIBE = 'subscribe';
	const CMD_UNSUBSCRIBE = 'unsubscribe';
	const CMD_VIEW = 'view';
	const SUB_CAT_ID = 'sub_cat_id';
	const LIMIT_RECENTLY_UPLOADED = 12;
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
	public $tabs_gui;
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;
	/**
	 * @var ilVideoManagerPlugin
	 */
	protected $pl;
	/**
	 * @var ilObjUser
	 */
	protected $usr;


	public function __construct() {
		global $DIC;

		$this->usr = $DIC->user();
		$this->pl = ilVideoManagerPlugin::getInstance();
		$this->tpl = $DIC->ui()->mainTemplate();
		$this->ctrl = $DIC->ctrl();
		$this->toolbar = $DIC->toolbar();
	}


	public function executeCommand() {
		$next_class = $this->ctrl->getNextClass();
		$cmd = $this->ctrl->getCmd(self::CMD_VIEW);

		switch ($next_class) {
			case 'ilratinggui':
				$rating = new ilRatingGUI();
				$rating->setObject($_GET[ilVideoManagerAdminGUI::PARAM_NODE_ID], ilVideoManagerObject::TYPE_VID);
				$rating->saveRating();
				$this->ctrl->setParameter($this, ilVideoManagerAdminGUI::PARAM_NODE_ID, $_GET[ilVideoManagerAdminGUI::PARAM_NODE_ID]);
				$this->ctrl->redirect($this, self::CMD_PLAY_VIDEO);
				break;
			default:
				if ($cmd == self::CMD_VIEW) {
					unset($_SESSION['search_value']);
				}
				$this->prepareOutput();

				switch ($cmd) {
					case self::CMD_VIEW:
						$this->view();
						break;
					case self::CMD_PERFORM_SEARCH:
						$this->performSearch();
						break;
					case self::CMD_SEARCH:
						$this->search();
						break;
					case self::CMD_PLAY_VIDEO:
						$this->playVideo();
						break;
					case self::CMD_SUBSCRIBE:
						$this->subscribe();
						break;
					case self::CMD_UNSUBSCRIBE:
						$this->unsubscribe();
						break;
				}
		}

		$this->tpl->getStandardTemplate();
		$this->tpl->show();
	}


	protected function view() {
		$this->tpl->addCss($this->pl->getDirectory() . '/templates/css/search_table.css');
		$this->tpl->setTitle($this->pl->txt('common_title_home'));
		$ilVideoManagerQueryBuilder = new ilVideoManagerQueryBuilder(array(
			'cmd'              => self::CMD_VIEW,
			'sort_create_date' => 'DESC',
			'limit'            => self::LIMIT_RECENTLY_UPLOADED,
		));
		$xvidListGUI = new xvidListGUI($ilVideoManagerQueryBuilder->getVideos());
		$this->tpl->setContent('<h2>' . $this->pl->txt('common_recently_uploaded') . '</h2><br>' . $xvidListGUI->render());
		$this->initLeftContent();
	}


	protected function playVideo() {
		$video_gui = new ilVideoManagerPlayVideoGUI($this);
		$this->ctrl->setParameter($video_gui, ilVideoManagerAdminGUI::PARAM_NODE_ID, $_GET[ilVideoManagerAdminGUI::PARAM_NODE_ID]);
		$video_gui->init();
	}


	public function prepareOutput() {
		$this->tpl->addCss($this->pl->getDirectory() . '/templates/css/video_player.css');

		$textinput = new ilTextInputGUI('search_input', 'search_value');
		if (!$_SESSION['search_method'] == 'category') {
			$textinput->setValue($_SESSION['search_value']);
		}

		$this->toolbar->addInputItem($textinput);
		$button = ilSubmitButton::getInstance();
		$button->setCaption($this->pl->txt('common_search'),false);
		$button->setCommand(self::CMD_SEARCH);
		$this->toolbar->addButtonInstance($button);
		$this->toolbar->setFormAction($this->ctrl->getLinkTarget($this, self::CMD_SEARCH));
		if ($this->usr->getId() == 6) {
			$button = ilLinkButton::getInstance();
			$button->setCaption($this->pl->txt('common_back_to_channels'),false);
			$button->setUrl($this->ctrl->getLinkTarget($this, self::CMD_VIEW));
			$this->toolbar->addButtonInstance($button);
		}
	}


	public function search() {
		if (array_key_exists('search_value', $_POST)) {
			$_SESSION['search_value'] = $_POST['search_value'];
			$_SESSION['search_method'] = 'all';
		} elseif ($_GET['search_value'] && $_GET['search_method']) {
			$_SESSION['search_value'] = $_GET['search_value'];
			$_SESSION['search_method'] = $_GET['search_method'];
		}

		$this->ctrl->redirect($this, self::CMD_PERFORM_SEARCH);
	}


	public function performSearch() {
		if ($_SESSION['search_method'] == 'category') {
			/**
			 * @var $cat ilVideoManagerFolder
			 */
			$cat = ilVideoManagerFolder::find($_SESSION['search_value']);
			$this->tpl->setTitle('Results for Channel: ' . $cat->getTitle());
		} else {
			$this->tpl->setTitle('Results for: ' . $_SESSION['search_value']);
		}

		// Search
		if (array_key_exists('search_value', $_SESSION)) {
			$search = array(
				'value'  => $_SESSION['search_value'],
				'method' => $_SESSION['search_method'],
			);
		} else {
			ilUtil::sendFailure('Error: no search value given');

			return false;
		}

		$ilVideoManagerQueryBuilder = new ilVideoManagerQueryBuilder(array(
			'cmd'              => self::CMD_PERFORM_SEARCH,
			'search'           => $search,
			'sort_create_date' => 'ASC',
		));
		$xvidListGUI = new xvidListGUI($ilVideoManagerQueryBuilder->getVideos());
		$this->tpl->setContent($xvidListGUI->render());
		$this->initLeftContent();
	}


	protected function subscribe() {
		$subscription = new vidmSubscription();
		$subscription->setUsrId($this->usr->getId());
		$subscription->setCatId($_GET[self::SUB_CAT_ID]);
		$subscription->create();

		ilUtil::sendSuccess($this->pl->txt('msg_subscribed_successfully'), true);
		$this->ctrl->saveParameter($this, 'video_tbl_table_nav');

		if ($_GET['fallbackCmd']) {
			$this->ctrl->saveParameter($this, ilVideoManagerAdminGUI::PARAM_NODE_ID);
			$this->ctrl->redirect($this, $_GET['fallbackCmd']);
		} else {
			$this->ctrl->redirect($this, self::CMD_PERFORM_SEARCH);
		}
	}


	protected function unsubscribe() {
		$existing = vidmSubscription::where(array( 'usr_id' => $this->usr->getId(), 'cat_id' => $_GET[self::SUB_CAT_ID] ))->get();
		/**
		 * @var $subscription vidmSubscription
		 */
		foreach ($existing as $subscription) {
			$subscription->delete();
		}

		ilUtil::sendSuccess($this->pl->txt('msg_unsubscribed_successfully'), true);
		$this->ctrl->saveParameter($this, 'video_tbl_table_nav');
		if ($_GET['fallbackCmd']) {
			$this->ctrl->saveParameter($this, ilVideoManagerAdminGUI::PARAM_NODE_ID);
			$this->ctrl->redirect($this, $_GET['fallbackCmd']);
		} else {
			$this->ctrl->redirect($this, self::CMD_PERFORM_SEARCH);
		}
	}


	protected function initLeftContent() {
		// Left Content
		$xvidChannelListGUI = new xvidChannelListGUI(ilVideoManagerFolder::where(array( 'type' => ilVideoManagerObject::TYPE_FLD ))->where('( hidden IS NULL OR hidden = 0)')
		                                                                 ->get());
		$xvidChannelListGUI->setId('xvidm_channel_list');
		$this->tpl->setLeftContent($xvidChannelListGUI->render());
	}
} 