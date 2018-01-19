<?php
require_once('class.vidmCount.php');

/**
 * Class vidmCountTableGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class vidmCountTableGUI extends ilTable2GUI {

	/**
	 * @var ilVideoManagerPlugin
	 */
	protected $pl;

	/**
	 * @param                      $a_parent_obj
	 * @param ilVideoManagerTree   $tree
	 * @param ilVideoManagerFolder $node
	 */
	public function __construct($a_parent_obj, ilVideoManagerTree $tree, ilVideoManagerFolder $node) {
		parent::__construct($a_parent_obj); // TODO: Change the autogenerated stub
		$this->pl = ilVideoManagerPlugin::getInstance();
		$this->setData(array());
		$this->addColumn($this->pl->txt('common_video'), '', '150px');
		$this->addColumn($this->pl->txt('common_title'));
		$this->addColumn($this->pl->txt('stats_views'));
		$this->setData($tree->getChilds($node->getId()));
		$this->setRowTemplate('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/default/tpl.stats_row.html');
	}


	/**
	 * @param array $a_set
	 */
	protected function fillRow($a_set) {
		/**
		 * @var $ilVideoManagerVideo ilVideoManagerVideo
		 */
		$ilVideoManagerVideo = ilVideoManagerVideo::find($a_set['child']);

		$this->tpl->setVariable('VIDEO', $ilVideoManagerVideo->getPreviewImageHttp());
		$this->tpl->setVariable('TITLE', $a_set['title']);
		$this->tpl->setVariable('VIEWS', vidmCount::countV($a_set['child']));
	}
}

?>
