<?php
require_once('./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php');

/**
 * Class vidmSubscriptionButtonGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class vidmSubscriptionButtonGUI {

	const SIZE_NORMAL = NULL;
	const SIZE_SMALL = '-xs';
	const TYPE_SUBSCRIBE = 'primary';
	const TYPE_UNSUBSCRIBE = 'default';
	const ICON_SUBSCRIBE = 'plus-sign';
	const ICON_UNSUBSCRIBE = 'minus-sign';
	/**
	 * @var null
	 */
	protected $size = self::SIZE_NORMAL;
	/**
	 * @var string
	 */
	protected $type = self::TYPE_SUBSCRIBE;
	/**
	 * @var string
	 */
	protected $icon = self::ICON_SUBSCRIBE;
	/**
	 * @var string
	 */
	protected $title = '';
	/**
	 * @var string
	 */
	protected $link = '';
	/**
	 * @var string
	 */
	protected $tooltip = '';
	/**
	 * @var bool
	 */
	protected $show_tooltip = true;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilObjUser
	 */
	protected $usr;
	/**
	 * @var int
	 */
	protected static $id_count = 0;


	public function __construct() {
		global $DIC;
		$this->ctrl = $DIC->ctrl();
		$this->usr = $DIC->user();
		$this->tpl = new ilTemplate('tpl.sub_button.html', false, false, 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager');
	}


	/**
	 *
	 */
	public function getHTML() {
		self::$id_count ++;
		$id = 'vidm_sub_' . self::$id_count;
		if ($this->isShowTooltip()) {
			ilTooltipGUI::init();
			ilTooltipGUI::addTooltip($id, $this->getTooltip(), '', 'left center', 'right center');
		}

		$this->tpl->setVariable('SIZE', $this->getSize());
		$this->tpl->setVariable('ID', $id);
		$this->tpl->setVariable('ICON', $this->getIcon());
		$this->tpl->setVariable('TYPE', $this->getType());
		$this->tpl->setVariable('LINK', $this->getLink());
		$this->tpl->setVariable('TITLE', $this->getTitle());
		$this->tpl->setVariable('TOOLTIP', $this->getTooltip());

		return $this->tpl->get();
	}


	/**
	 * @return null
	 */
	public function getSize() {
		return $this->size;
	}


	/**
	 * @param null $size
	 */
	public function setSize($size) {
		$this->size = $size;
	}


	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}


	/**
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}


	/**
	 * @return string
	 */
	public function getIcon() {
		return $this->icon;
	}


	/**
	 * @param string $icon
	 */
	public function setIcon($icon) {
		$this->icon = $icon;
	}


	/**
	 * @return string
	 */
	public function getLink() {
		return $this->link;
	}


	/**
	 * @param string $link
	 */
	public function setLink($link) {
		$this->link = $link;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getTooltip() {
		return $this->tooltip;
	}


	/**
	 * @param string $tooltip
	 */
	public function setTooltip($tooltip) {
		$this->tooltip = $tooltip;
	}


	/**
	 * @return boolean
	 */
	public function isShowTooltip() {
		return $this->show_tooltip;
	}


	/**
	 * @param boolean $show_tooltip
	 */
	public function setShowTooltip($show_tooltip) {
		$this->show_tooltip = $show_tooltip;
	}


	/**
	 * @param ilVideoManagerFolder $ilVideoManagerFolder
	 * @param null                 $fallback_cmd
	 */
	public function generate(ilVideoManagerFolder $ilVideoManagerFolder, $fallback_cmd = NULL) {
		$pl = ilVideoManagerPlugin::getInstance();
		$this->ctrl->setParameterByClass('ilVideoManagerUserGUI', ilVideoManagerUserGUI::SUB_CAT_ID, $ilVideoManagerFolder->getId());
		$this->ctrl->setParameterByClass('ilVideoManagerUserGUI', 'fallbackCmd', $fallback_cmd);
		$this->setTooltip($pl->txt('player_sub_tooltip'));
		$this->ctrl->saveParameterByClass('ilVideoManagerUserGUI', 'node_id');

		if (vidmSubscription::isSubscribed($this->usr->getId(), $ilVideoManagerFolder->getId())) {
			$this->setIcon(self::ICON_UNSUBSCRIBE);
			$this->setType(self::TYPE_UNSUBSCRIBE);
			$this->setTitle($pl->txt('tbl_unsubscribe_action'));
			$this->setLink($this->ctrl->getLinkTargetByClass('ilVideoManagerUserGUI', 'unsubscribe'));
		} else {
			$this->setIcon(self::ICON_SUBSCRIBE);
			$this->setType(self::TYPE_SUBSCRIBE);
			$this->setTitle($pl->txt('tbl_subscribe_action'));
			$this->setLink($this->ctrl->getLinkTargetByClass('ilVideoManagerUserGUI', 'subscribe'));
		}
	}
}

?>
