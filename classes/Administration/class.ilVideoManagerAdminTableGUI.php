<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerTree.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');


/**
 * Class ilVideoManagerAdminTableGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilVideoManagerAdminTableGUI: ilRouterGUI, ilUIPluginRouterGUI
 */
class ilVideoManagerAdminTableGUI extends ilTable2GUI{

    /**
     * @var ilVideoManagerTree
     */
    protected $tree;
    /**
     * @var ilVideoManagerObject[]
     */
    protected $objects = array();
    /**
     * @var ilVideoManagerPlugin
     */
    protected $pl;
	/**
	 * @var ilCtrl
	 */
    protected $ctrl;

    /**
     * @param $parent_obj
     * @param int $node_id
     */
    public function __construct($parent_obj, $node_id = 0)
    {
    	global $DIC;
    	$this->ctrl = $DIC->ctrl();
        $this->pl = ilVideoManagerPlugin::getInstance();
        $this->tree = new ilVideoManagerTree(1);
        $this->setId('vidm_admin_tbl_'.$node_id);
        parent::__construct($parent_obj);

        if($node_id == 0)
        {
            $_GET[ilVideoManagerAdminGUI::PARAM_NODE_ID] ? $node_id = $_GET[ilVideoManagerAdminGUI::PARAM_NODE_ID] : $node_id = ilVideoManagerObject::__getRootFolder()->getId();
        }

        $nodes = $this->tree->getChilds($node_id);

        foreach($nodes as $key => $node)
        {
            $this->objects[] = new ilVideoManagerObject($node['id']);
        }

        $this->setFormAction($this->ctrl->getFormAction($parent_obj));
        $this->addColumn("", "", "1", true);
        $this->addColumn('', '', 1);
        $this->addColumn('', '', 800);
        $this->addColumn('', '', 15);
        $this->setRowTemplate('tpl.admin_tbl_row.html', $this->pl->getDirectory());
        $this->setExternalSegmentation(true);
        $this->setSelectAllCheckbox("id");
        $this->setTopCommands(true);

        $commands = array(
            ilVideoManagerAdminGUI::CMD_DELETE_MULTIPLE => $this->pl->txt('common_delete'),
            ilVideoManagerAdminGUI::CMD_MOVE_MULTIPLE => $this->pl->txt('common_move'),
        );

        foreach($commands as $cmd => $caption){
            $this->addMultiCommand($cmd, $caption);
        }
        $this->setTopCommands($commands);

        $this->buildData();
    }

    /**
     * @param array $row
     */
    public function fillRow($row)
    {
        $this->tpl->setVariable('ID', $row[ilVideoManagerAdminGUI::PARAM_NODE_ID]);
        $this->tpl->setVariable('ICON', $row['icon']);
        $this->ctrl->clearParameters($this->parent_obj);
        $this->ctrl->setParameter($this->parent_obj, ilVideoManagerAdminGUI::PARAM_NODE_ID, $row[ilVideoManagerAdminGUI::PARAM_NODE_ID]);
        $this->tpl->setVariable('LINK', $this->ctrl->getLinkTarget($this->parent_obj, ilVideoManagerAdminGUI::CMD_VIEW));
        $this->tpl->setVariable('TITLE', $row['title']);

        $current_selection_list = new ilAdvancedSelectionListGUI();
        $current_selection_list->setListTitle($this->pl->txt("common_actions"));
        $current_selection_list->setId($row[ilVideoManagerAdminGUI::PARAM_NODE_ID]);

        $this->ctrl->setParameter($this->parent_obj, ilVideoManagerAdminGUI::PARAM_NODE_ID, $_GET[ilVideoManagerAdminGUI::PARAM_NODE_ID]);
        $this->ctrl->setParameter($this->parent_obj, 'target_id', $row[ilVideoManagerAdminGUI::PARAM_NODE_ID]);

        $current_selection_list->addItem($this->pl->txt("common_delete"), "",
            $this->ctrl->getLinkTargetByClass(ilVideoManagerAdminGUI::class, ilVideoManagerAdminGUI::CMD_CONFIRM_DELETE));

        $current_selection_list->addItem($this->pl->txt("common_edit"), "",
            $this->ctrl->getLinkTargetByClass(ilVideoManagerAdminGUI::class, ilVideoManagerAdminGUI::CMD_EDIT . $row['type']));

        $current_selection_list->addItem($this->pl->txt("common_move"), "",
            $this->ctrl->getLinkTargetByClass(ilVideoManagerAdminGUI::class, ilVideoManagerAdminGUI::CMD_CUT));

        $this->tpl->setVariable('ACTIONS', $current_selection_list->getHTML());
    }

    public function buildData()
    {
        $data = array();
        foreach($this->objects as $obj)
        {
            $row = array();

            $row['icon'] = $obj->getIcon();
            $row['title'] = $obj->getTitle();
            $row['type'] = $obj->getType();
            $row[ilVideoManagerAdminGUI::PARAM_NODE_ID] = $obj->getId();

            $data[] = $row;
        }
        $this->setData($data);
    }



} 