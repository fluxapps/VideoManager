<?php

/**
 * Class xvidListGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class xvidListGUI implements xvidUIComponent {

	const SIZE_LARGE = 'large';
	const SIZE_TINY = 'tiny';
	/**
	 * @var xvidListItemGUI[]
	 */
	protected $items = array();
	/**
	 * @var ilVideoManagerVideo[]
	 */
	protected $videos = array();
	/**
	 * @var string
	 */
	protected $size = self::SIZE_LARGE;
	/**
	 * @var array
	 */
	protected static $class_map = array(
		self::SIZE_LARGE => 'col-lg-3 col-md-3 col-sm-4 col-xs-6',
		self::SIZE_TINY => 'col-lg-12 col-md-12 col-sm-12 col-xs-12',
	);
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilVideoManagerPlugin
	 */
	protected $pl;


	/**
	 * xvidListGUI constructor.
	 *
	 * @param $videos
	 */
	public function __construct($videos) {
		global $DIC;
		$this->videos = $videos;
		$this->ctrl = $DIC->ctrl();
		$tpl = $DIC->ui()->mainTemplate();
		$this->pl = ilVideoManagerPlugin::getInstance();
		$tpl->addCss($this->pl->getDirectory() . '/templates/css/cards.css');
		$tpl->addJavaScript($this->pl->getDirectory() . '/templates/js/cards.js');
	}


	protected function loadItems() {
		foreach ($this->videos as $ilVideoManagerVideo) {
			if (!$ilVideoManagerVideo instanceof ilVideoManagerVideo) {
				continue;
			}
			$this->ctrl->setParameterByClass(ilVideoManagerUserGUI::class, ilVideoManagerAdminGUI::PARAM_NODE_ID, $ilVideoManagerVideo->getId());
			$xvidListItemGUI = new xvidListItemGUI();
			$xvidListItemGUI->setTitle($ilVideoManagerVideo->getTitle());
			$xvidListItemGUI->setDescription($ilVideoManagerVideo->getDescription());
			$xvidListItemGUI->setLink($this->ctrl->getLinkTargetByClass(ilVideoManagerUserGUI::class, ilVideoManagerUserGUI::CMD_PLAY_VIDEO));
			$xvidListItemGUI->setImgSrc($ilVideoManagerVideo->getPosterHttp());
			$xvidListItemGUI->setCounter($ilVideoManagerVideo->getViews());

			$this->addItem($xvidListItemGUI);
		}
	}


	/**
	 * @return string
	 */
	public function render() {
		$tpl = new ilTemplate($this->pl->getDirectory() . '/templates/default/tpl.card_list.html', true, false);
		$this->loadItems();
		if (count($this->items) === 0) {
			$no_items = '<div class="panel panel-default">
				  <div class="panel-body">
				    No Items
				  </div>
				</div>';

			return $no_items;
		}
		$html = "";
		foreach ($this->items as $xvidListItemGUI) {
			$xvidListItemGUI->setClasses(self::$class_map[$this->getSize()]);
			$html .= $xvidListItemGUI->render();
		}
		$html .= "";
		$tpl->setVariable('CARDS', $html);

		return $tpl->get();
	}


	/**
	 * @param \xvidListItemGUI $xvidListItemGUI
	 */
	public function addItem(xvidListItemGUI $xvidListItemGUI) {
		$this->items[] = $xvidListItemGUI;
	}


	public function clearItems() {
		$this->items = array();
	}


	/**
	 * @return string
	 */
	public function getSize() {
		return $this->size;
	}


	/**
	 * @param string $size
	 */
	public function setSize($size) {
		$this->size = $size;
	}


	/**
	 * @return \xvidListItemGUI[]
	 */
	public function getItems() {
		return $this->items;
	}


	/**
	 * @param \xvidListItemGUI[] $items
	 */
	public function setItems($items) {
		$this->items = $items;
	}


	/**
	 * @return \ilVideoManagerVideo[]
	 */
	public function getVideos() {
		return $this->videos;
	}


	/**
	 * @param \ilVideoManagerVideo[] $videos
	 */
	public function setVideos($videos) {
		$this->videos = $videos;
	}
}

/**
 * Class xvidListItemGUI
 */
class xvidListItemGUI implements xvidUIComponent {

	/**
	 * @var string
	 */
	protected $img_src = '';
	/**
	 * @var string
	 */
	protected $title = '';
	/**
	 * @var string
	 */
	protected $description = '';
	/**
	 * @var string
	 */
	protected $link = '';
	/**
	 * @var string
	 */
	protected $link_title = '';
	/**
	 * @var int
	 */
	protected $counter = 0;
	/**
	 * @var string
	 */
	protected $classes = 'col-lg-3 col-md-3 col-sm-4 col-xs-6';


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
	public function getImgSrc() {
		return $this->img_src;
	}


	/**
	 * @param string $img_src
	 */
	public function setImgSrc($img_src) {
		$this->img_src = $img_src;
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
	public function getDescription() {
		return $this->description;
	}


	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}


	/**
	 * @return string
	 */
	public function getLinkTitle() {
		return $this->link_title;
	}


	/**
	 * @param string $link_title
	 */
	public function setLinkTitle($link_title) {
		$this->link_title = $link_title;
	}


	/**
	 * @return int
	 */
	public function getCounter() {
		return $this->counter;
	}


	/**
	 * @param int $counter
	 */
	public function setCounter($counter) {
		$this->counter = $counter;
	}


	/**
	 * @return string
	 */
	public function getClasses() {
		return $this->classes;
	}


	/**
	 * @param string $classes
	 */
	public function setClasses($classes) {
		$this->classes = $classes;
	}


	/**
	 * @return string
	 */
	public function render() {
		$tpl = new ilTemplate($this->pl->getDirectory() . '/templates/default/tpl.card.html', true, false);
		$tpl->setVariable('CLASSES', $this->getClasses());
		$tpl->setVariable('TITLE', $this->getTitle());
		$tpl->setVariable('DESCRIPTION', $this->getDescription());
		$tpl->setVariable('SRC', $this->getImgSrc());
		$tpl->setVariable('HREF', $this->getLink());
		$tpl->setVariable('COUNTER', $this->getCounter());

		return $tpl->get();
	}
}