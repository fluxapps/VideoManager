<?php
require_once('./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');

/**
 * Class ilVideoManagerTreeExplorerGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilVideoManagerTreeExplorerGUI extends ilTreeExplorerGUI {

	/**
	 * @var array
	 */
	protected $ignoreSubTree;


	/**
	 * @param mixed $node
	 *
	 * @return string
	 */
	function getNodeContent($node) {
		$object = new ilVideoManagerObject($node['id']);

		return ilUtil::img($object->getIcon()) . " " . $node["title"];
	}


	function getNodeHref($node) {
		if ($this->ctrl->getCmd() == "cut" || $this->ctrl->getCmd() == "moveMultiple") {
			$this->ctrl->saveParameterByClass("ilVideoManagerAdminGUI", "target_id");
			$this->ctrl->setParameterByClass("ilVideoManagerAdminGUI", "node_id", $node["child"]);

			return $this->ctrl->getLinkTargetByClass("ilVideoManagerAdminGUI", 'performPaste');
		} elseif ($this->ctrl->getCmd() == 'insert') {
			$this->ctrl->setParameterByClass("ilVideoManagerTMEPluginGUI", 'video_id', $node['id']);

			return $this->ctrl->getLinkTargetByClass("ilVideoManagerTMEPluginGUI", 'create');
		} else {
			$this->ctrl->setParameterByClass("ilVideoManagerAdminGUI", "node_id", $node["child"]);

			return $this->ctrl->getLinkTargetByClass("ilVideoManagerAdminGUI", 'view');
		}
	}


	/**
	 * Preload childs, overwrite to ignore subtree
	 */
	protected function preloadChilds() {
		$subtree = $this->tree->getSubTree($this->getRootNode());
		foreach ($subtree as $s) {
			$wl = $this->getTypeWhiteList();
			if (is_array($wl) && count($wl) > 0 && ! in_array($s["type"], $wl)) {
				continue;
			}
			$bl = $this->getTypeBlackList();
			if (is_array($bl) && count($bl) > 0 && in_array($s["type"], $bl)) {
				continue;
			}
			if (is_array($this->ignoreSubTree) && in_array($s, $this->ignoreSubTree)) {
				continue;
			}
			$this->childs[$s["parent"]][] = $s;
			$this->all_childs[$s["child"]] = $s;
		}

		if ($this->order_field != "") {
			foreach ($this->childs as $k => $childs) {
				$this->childs[$k] = ilUtil::sortArray($childs, $this->order_field, "asc", $this->order_field_numeric);
			}
		}

		// sort childs and store prev/next reference
		if ($this->order_field == "") {
			$this->all_childs = ilUtil::sortArray($this->all_childs, "lft", "asc", true, true);
			$prev = false;
			foreach ($this->all_childs as $k => $c) {
				if ($prev) {
					$this->all_childs[$prev]["next_node_id"] = $k;
				}
				$this->all_childs[$k]["prev_node_id"] = $prev;
				$this->all_childs[$k]["next_node_id"] = false;
				$prev = $k;
			}
		}

		$this->preloaded = true;
	}


	/**
	 * @param array $subtree
	 */
	public function setIgnoreSubTree(array $subtree) {
		$this->ignoreSubTree = $subtree;
		$this->preloadChilds();
	}


	/**
	 * @param mixed $node
	 *
	 * @return bool
	 */
	public function isNodeClickable($node) {
		if ($this->ctrl->getCmd() != 'insert' || ilVideoManagerObject::__getTypeForId($node['id']) == ilVideoManagerObject::TYPE_VID) {
			return true;
		} else {
			return false;
		}
	}
}