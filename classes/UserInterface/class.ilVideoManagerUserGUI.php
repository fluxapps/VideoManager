<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Subscription/class.vidmSubscription.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/UserInterface/class.ilVideoManagerVideoTableGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/UserInterface/class.ilVideoManagerPlayVideoGUI.php');
require_once('./Services/Form/classes/class.ilTextInputGUI.php');
require_once("./Services/Rating/classes/class.ilRatingGUI.php");
require_once('class.ilVideoManagerQueryBuilder.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/UserInterface/class.xvidListGUI.php');

/**
 * Class ilVideoManagerUserGUI
 *
 * @ilCtrl_IsCalledBy ilVideoManagerUserGUI: ilRouterGUI, ilUIPluginRouterGUI
 * @ilCtrl_Calls      ilVideoManagerUserGUI: ilVideoManagerVideoTableGUI, ilVideoManagerPlayVideoGUI, ilRatingGUI
 */
class ilVideoManagerUserGUI {

	const SUB_CAT_ID = 'sub_cat_id';
	const CMD_PERFORM_SEARCH = 'performSearch';
	const CMD_PLAY_VIDEO = 'playVideo';
	const CMD_SUBSCRIBE = 'subscribe';
	const CMD_UNSUBSCRIBE = 'unsubscribe';
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
		global $tpl, $ilUser, $ilCtrl, $ilToolbar;

		$this->usr = $ilUser;
		$this->pl = ilVideoManagerPlugin::getInstance();
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->toolbar = $ilToolbar;
	}


	public function executeCommand() {
		$next_class = $this->ctrl->getNextClass();
		$cmd = $this->ctrl->getCmd('view');

		switch ($next_class) {
			case 'ilratinggui':
				$rating = new ilRatingGUI();
				$rating->setObject($_GET['node_id'], 'vid');
				$rating->saveRating();
				$this->ctrl->setParameter($this, 'node_id', $_GET['node_id']);
				$this->ctrl->redirect($this, self::CMD_PLAY_VIDEO);
				break;
			default:
				if ($cmd == 'view') {
					unset($_SESSION['search_value']);
				}
				$this->prepareOutput();

				switch ($cmd) {
					case 'view':
						$this->view();
						break;
					case self::CMD_PERFORM_SEARCH:
						$this->performSearch();
						break;
					case 'search':
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
		if (videoman::is50()) {
			/**
			 * @var $tpl ilTemplate
			 */
			global $tpl;
			$tpl->getStandardTemplate();
			$tpl->show();
		}
	}


	protected function view() {
		$this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/search_table.css');
		$this->tpl->setTitle('Recently Uploaded');
		$ilVideoManagerQueryBuilder = new ilVideoManagerQueryBuilder(array(
			'cmd'              => 'view',
			'sort_create_date' => 'DESC',
			'limit'            => 10,
		));
		$xvidListGUI = new xvidListGUI($ilVideoManagerQueryBuilder->getVideos());
		$this->tpl->setContent($xvidListGUI->render());
	}


	protected function playVideo() {
		$video_gui = new ilVideoManagerPlayVideoGUI($this);
		$this->ctrl->setParameter($video_gui, 'node_id', $_GET['node_id']);
		$video_gui->init();
	}


	public function prepareOutput() {
		$this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/video_player.css');

		$textinput = new ilTextInputGUI('search_input', 'search_value');
		if (!$_SESSION['search_method'] == 'category') {
			$textinput->setValue($_SESSION['search_value']);
		}

		$this->toolbar->addInputItem($textinput);
		$this->toolbar->addFormButton($this->pl->txt('common_search'), 'search');
		$this->toolbar->setFormAction($this->ctrl->getLinkTarget($this, 'search'));
		global $ilUser;
		if ($ilUser->getId() == 6) {
			$this->toolbar->addButton('Recently Uploaded', $this->ctrl->getLinkTarget($this, 'view'));
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

		$options = array(
			'cmd'              => self::CMD_PERFORM_SEARCH,
			'search'           => $search,
			'sort_create_date' => 'ASC',
		);

		$ilVideoManagerQueryBuilder = new ilVideoManagerQueryBuilder($options);
		$xvidListGUI = new xvidListGUI($ilVideoManagerQueryBuilder->getVideos());
		$this->tpl->setContent($xvidListGUI->render());

		return;
		//		$this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/search_table.css');
		$tpl = new ilTemplate('tpl.search_gui.html', true, true, 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager');
		//		$tpl->setCurrentBlock('search_gui');


		unset($_SESSION['table']);
		$search_results = new ilVideoManagerVideoTableGUI($this, $options);
		$tpl->setVariable('TABLE', $search_results->getHTML());

		if ($_SESSION['search_method'] == 'category') {
			/**
			 * @var $channel ilVideoManagerFolder
			 */
			$channel = ilVideoManagerFolder::find($_SESSION['search_value']);
			$tpl->setVariable('CHANNEL', $this->pl->txt('common_category') . ": '" . $channel->getTitle() . "'");

			if (vidmConfig::get(vidmConfig::F_ACTIVATE_SUBSCRIPTION)) {
				$button = new vidmSubscriptionButtonGUI();
				$button->generate($channel);
				$tpl->setVariable('SUBSCRIPTION', $button->getHTML());
			}
		}

		$this->tpl->setContent($tpl->get());
	}


	protected function subscribe() {
		$subscription = new vidmSubscription();
		$subscription->setUsrId($this->usr->getId());
		$subscription->setCatId($_GET[self::SUB_CAT_ID]);
		$subscription->create();

		ilUtil::sendSuccess($this->pl->txt('msg_subscribed_successfully'), true);
		$this->ctrl->saveParameter($this, 'video_tbl_table_nav');

		if ($_GET['fallbackCmd']) {
			$this->ctrl->saveParameter($this, 'node_id');
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
			$this->ctrl->saveParameter($this, 'node_id');
			$this->ctrl->redirect($this, $_GET['fallbackCmd']);
		} else {
			$this->ctrl->redirect($this, self::CMD_PERFORM_SEARCH);
		}
	}
} 