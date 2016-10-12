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
     * @var ilCtrl
     */
    protected $ctrl;
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
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @param $parent_obj
     * @param int $node_id
     */
    public function __construct($parent_obj, $node_id = 0)
    {
        global $ilCtrl, $tpl;
        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
        $this->pl = ilVideoManagerPlugin::getInstance();
        $this->tree = new ilVideoManagerTree(1);
        $this->setId('vidm_admin_tbl_'.$node_id);
        parent::__construct($parent_obj);

        if($node_id == 0)
        {
            $_GET['node_id'] ? $node_id = $_GET['node_id'] : $node_id = ilVideoManagerObject::__getRootFolder()->getId();
        }

        $nodes = $this->tree->getChilds($node_id);

        foreach($nodes as $key => $node)
        {
            $this->objects[] = new ilVideoManagerObject($node['id']);
        }

        $this->setFormAction($ilCtrl->getFormAction($parent_obj));
        $this->addColumn("", "", "1", true);
        $this->addColumn('', '', 1);
        $this->addColumn('', '', 800);
        $this->addColumn('', '', 15);
        $this->setRowTemplate('tpl.admin_tbl_row.html', $this->pl->getDirectory());
        $this->setExternalSegmentation(true);
        $this->setSelectAllCheckbox("id");
        $this->setTopCommands(true);

        $commands = array(
            'deleteMultiple' => $this->pl->txt('common_delete'),
            'moveMultiple' => $this->pl->txt('common_move'),
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
    public function fillRow(array $row)
    {
        $this->tpl->setVariable('ID', $row['node_id']);
        $this->tpl->setVariable('ICON', $row['icon']);
        $this->ctrl->clearParameters($this->parent_obj);
        $this->ctrl->setParameter($this->parent_obj, 'node_id', $row['node_id']);
        $this->tpl->setVariable('LINK', $this->ctrl->getLinkTarget($this->parent_obj, 'view'));
        $this->tpl->setVariable('TITLE', $row['title']);

        $current_selection_list = new ilAdvancedSelectionListGUI();
        $current_selection_list->setListTitle($this->pl->txt("common_actions"));
        $current_selection_list->setId($row['node_id']);

        $this->ctrl->setParameter($this->parent_obj, 'node_id', $_GET['node_id']);
        $this->ctrl->setParameter($this->parent_obj, 'target_id', $row['node_id']);

        $current_selection_list->addItem($this->pl->txt("common_delete"), "",
            $this->ctrl->getLinkTargetByClass('ilvideomanageradmingui', 'confirmDelete'));

        $current_selection_list->addItem($this->pl->txt("common_edit"), "",
            $this->ctrl->getLinkTargetByClass('ilvideomanageradmingui', 'edit'.$row['type']));

        $current_selection_list->addItem($this->pl->txt("common_move"), "",
            $this->ctrl->getLinkTargetByClass('ilvideomanageradmingui', 'cut'));

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
            $row['node_id'] = $obj->getId();

            $data[] = $row;
        }
        $this->setData($data);
    }



} 