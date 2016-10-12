<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/UserInterface/int.xvidUIComponent.php');

/**
 * Class xvidChannelListGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class xvidChannelListGUI implements xvidUIComponent {

	/**
	 * @var ilVideoManagerFolder[]
	 */
	protected $channels = array();
	/**
	 * @var xvidChannelListItemGUI[]
	 */
	protected $items = array();
	/**
	 * @var string
	 */
	protected $id = '';


	/**
	 * xvidChannelListGUI constructor.
	 *
	 * @param array $channels
	 */
	public function __construct(array $channels) {
		$this->channels = $channels;
	}


	protected function loadItems() {
		global $ilCtrl;
		foreach ($this->channels as $ilVideoManagerFolder) {
			if (!$ilVideoManagerFolder->getVideoCount()) {
				continue;
			}
			// Link
			$ilCtrl->setParameterByClass('ilVideoManagerUserGUI', 'search_value', $ilVideoManagerFolder->getId());
			$ilCtrl->setParameterByClass('ilVideoManagerUserGUI', 'search_method', 'category');

			$xvidChannelListItemGUI = new xvidChannelListItemGUI();
			$xvidChannelListItemGUI->setTitle($ilVideoManagerFolder->getTitle());
			$xvidChannelListItemGUI->setCounter($ilVideoManagerFolder->getVideoCount());
			$xvidChannelListItemGUI->setLink($ilCtrl->getLinkTargetByClass('ilVideoManagerUserGUI', 'search'));
			$this->addItem($xvidChannelListItemGUI);
		}
	}


	/**
	 * @return string
	 */
	public function render() {
		$this->loadItems();
		$item_html = '';
		foreach ($this->items as $xvidChannelListItemGUI) {
			$item_html .= $xvidChannelListItemGUI->render();
		}
		$list_html = <<<EOL
			<ul id="{$this->getId()}" class="list-group">
				{$item_html}
			</ul>
EOL;

		return $list_html;
	}


	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @param \xvidChannelListItemGUI $xvidChannelListItemGUI
	 */
	public function addItem(xvidChannelListItemGUI $xvidChannelListItemGUI) {
		$this->items[] = $xvidChannelListItemGUI;
	}


	/**
	 * @return \ilVideoManagerFolder[]
	 */
	public function getChannels() {
		return $this->channels;
	}


	/**
	 * @param \ilVideoManagerFolder[] $channels
	 */
	public function setChannels($channels) {
		$this->channels = $channels;
	}


	/**
	 * @return \xvidChannelListItemGUI[]
	 */
	public function getItems() {
		return $this->items;
	}


	/**
	 * @param \xvidChannelListItemGUI[] $items
	 */
	public function setItems($items) {
		$this->items = $items;
	}
}

/**
 * Class xvidChannelListItemGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class xvidChannelListItemGUI implements xvidUIComponent {

	/**
	 * @var string
	 */
	protected $title = '';
	/**
	 * @var string
	 */
	protected $link = '';
	/**
	 * @var int
	 */
	protected $counter = 0;


	/**
	 * @return string
	 */
	public function render() {
		$item = <<<EOL
	<li class="list-group-item">
		<a href="{$this->getLink()}">{$this->getTitle()}</a>
		<span class="badge badge-primary">{$this->getCounter()}</span>
	</li> 
EOL;

		return $item;
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
}