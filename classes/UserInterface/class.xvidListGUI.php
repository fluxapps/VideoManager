<?php

/**
 * Class xvidListGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class xvidListGUI {

	/**
	 * @var xvidListItemGUI[]
	 */
	protected $items = array();
	/**
	 * @var ilVideoManagerVideo[]
	 */
	protected $videos = array();


	/**
	 * xvidListGUI constructor.
	 *
	 * @param $videos
	 */
	public function __construct($videos) {
		$this->videos = $videos;
		global $tpl;
		$tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/cards.css');
		$tpl->addJavaScript('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/js/cards.js');
	}


	protected function loadItems() {
		global $ilCtrl;
		foreach ($this->videos as $ilVideoManagerVideo) {
			if (!$ilVideoManagerVideo instanceof ilVideoManagerVideo) {
				continue;
			}
			$ilCtrl->setParameterByClass('ilvideomanagerusergui', 'node_id', $ilVideoManagerVideo->getId());
			$xvidListItemGUI = new xvidListItemGUI();
			$xvidListItemGUI->setTitle($ilVideoManagerVideo->getTitle());
			$xvidListItemGUI->setDescription($ilVideoManagerVideo->getDescription($this->max_desc_length));
			$xvidListItemGUI->setLink($ilCtrl->getLinkTargetByClass('ilvideomanagerusergui', 'playVideo'));
			$xvidListItemGUI->setImgSrc($ilVideoManagerVideo->getPosterHttp());
			$this->addItem($xvidListItemGUI);
		}
	}


	/**
	 * @return string
	 */
	public function render() {
		$tpl = new ilTemplate('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/default/tpl.card_list.html', true, false);
		$this->loadItems();
		$html = "";
		foreach ($this->items as $xvidListItemGUI) {
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
class xvidListItemGUI {

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
	 * @return string
	 */
	public function render() {
		$tpl = new ilTemplate('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/default/tpl.card.html', true, false);
		$tpl->setVariable('TITLE', $this->getTitle());
		$tpl->setVariable('DESCRIPTION', $this->getDescription());
		$tpl->setVariable('SRC', $this->getImgSrc());
		$tpl->setVariable('HREF', $this->getLink());

		return $tpl->get();
	}
}