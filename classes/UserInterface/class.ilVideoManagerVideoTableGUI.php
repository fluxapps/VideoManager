<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerVideo.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerTree.php');
require_once 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/UserInterface/class.ilVideoManagerVideoTree.php';

/**
 * Class ilVideoManagerVideoTableGUI
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilVideoManagerVideoTableGUI: ilRouterGUI
 */
class ilVideoManagerVideoTableGUI extends ilTable2GUI {

	/**
	 * @var ilDB
	 */
	protected $db;
	/**
	 * @var ilVideoManagerPlugin
	 */
	protected $pl;
	/**
	 * @var ilVideoManagerVideo
	 */
	protected $video;
	/**
	 * @var ilVideoManagerTree
	 */
	protected $tree;
	/**
	 * @var array
	 */
	protected $options;
	/**
	 * @var int
	 */
	protected $max_desc_length;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @param                     $parent_gui
	 * @param array $options
	 * @param ilVideoManagerVideo $video
	 */
	public function __construct($parent_gui, $options, ilVideoManagerVideo $video = null) {
		global $DIC;
		parent::__construct($parent_gui, $options['cmd']);
		$this->ctrl = $DIC->ctrl();
		$this->db = $DIC->database();
		$this->tree = new ilVideoManagerTree(1);
		$this->pl = ilVideoManagerPlugin::getInstance();
		$this->video = $video;
		$this->options = $options;
		$this->setId('video_tbl');
		$this->setDefaultOrderField('sort');
		$this->setShowRowsSelector(false);
		$this->setFormAction($this->ctrl->getFormAction($parent_gui));
		$this->setEnableNumInfo(true);
		$this->setExternalSorting(true);
		$this->setEnableNumInfo(false);
		if ($options['cmd'] == 'related_videos') {
			$this->max_desc_length = 70;
			$this->setExternalSegmentation(true);
			$this->options['limit'] = 5;
		} else {
			$this->max_desc_length = 320;
		}

		$this->setRowTemplate('tpl.video_tbl_row.html', $this->pl->getDirectory());

		$this->addColumn('', '');
		$this->addColumn('', '');
		$this->buildData();
	}


	public function buildData() {
		$data = $this->createData();
		$this->setData($data);
	}


	/**
	 * @param array $row
	 */
	public function fillRow($row) {
		//first row with id 0 is the title
		if ($row['id'] == 0) {
			$this->tpl->setCurrentBlock('tbl_title');
			$this->tpl->setVariable('ID', 0);
			$this->tpl->setVariable('TBL_TITLE', $this->pl->txt('tbl_' . $this->options['cmd']));
			$this->tpl->parseCurrentBlock();
		} else {
			//all other rows
			$this->tpl->setCurrentBlock('td');
			$this->tpl->setVariable('ID', $row['id']);
			$this->tpl->setVariable('IMAGE', $row['img']);
			$this->tpl->setVariable('LINK', $row['link']);
			$this->tpl->setVariable('TITLE', $row['title']);
			$this->tpl->setVariable('DESCRIPTION', $row['description']);
			$this->tpl->setVariable('VIEWS', $row['views']);
			$this->tpl->parseCurrentBlock();
		}
	}


	public function createData() {
		$tree = new ilVideoManagerTree(1);
		if ($this->options['count']) {
			$sql = 'SELECT COUNT(' . ilVideoManagerObject::TABLE_NAME . '.id) AS count';
		} else {
			$sql = 'SELECT *, (SELECT COUNT(id) FROM ' . vidmCount::TABLE_NAME . ' WHERE ' . vidmCount::TABLE_NAME . '.video_id = ' . ilVideoManagerObject::TABLE_NAME . '.id) AS views';
		}

		$sql .= ' FROM ' . ilVideoManagerObject::TABLE_NAME . '
                    JOIN ' . ilVideoManagerVideoTree::TABLE_NAME . ' ON (' . ilVideoManagerVideoTree::TABLE_NAME . '.child = ' . ilVideoManagerObject::TABLE_NAME . '.id)';

		$sql .= ' WHERE ' . ilVideoManagerObject::TABLE_NAME . '.type = ' . $this->db->quote(ilVideoManagerObject::TYPE_VID, 'text');

		if ($hidden_nodes = $tree->getHiddenNodes()) {
			$sql .= ' AND ' . ilVideoManagerObject::TABLE_NAME . '.id NOT IN (' . implode(',', $hidden_nodes) . ')';
		}

		foreach ($this->options as $option => $value) {
			switch ($option) {
				case 'search':
					switch ($value['method']) {
						case '':
						case 'all':
							$sql .= ' AND (';
							$or = '';
							if (!is_array($value['value'])) {
								$value['value'] = array( $value['value'] );
							}
							foreach ($value['value'] as $word) {
								$sql .= $or;
								$sql .= ilVideoManagerObject::TABLE_NAME . '.title LIKE ' . $this->db->quote("%" . $word . "%", 'text');
								$sql .= ' OR ' . ilVideoManagerObject::TABLE_NAME . '.description LIKE ' . $this->db->quote("%" . $word . "%", 'text');
								$sql .= ' OR ' . ilVideoManagerObject::TABLE_NAME . '.tags LIKE ' . $this->db->quote("%" . $word . "%", 'text');
								$or = ' OR ';
							}
							$sql .= ')';
							break;
						case 'related':
							//related videos search for same tags/categories
							$sql .= ' AND (' . ilVideoManagerVideoTree::TABLE_NAME . '.parent = ' . $tree->getParentId($this->video->getId()); //categories names must be unique

							if ($this->video->getTags()) {
								foreach ($this->video->getTags() as $tag) {
									$sql .= ' OR ' . ilVideoManagerObject::TABLE_NAME . '.tags LIKE ' . $this->db->quote("%" . $tag . "%", 'text');
								}
							}
							$sql .= ')';
							$sql .= ' AND ' . ilVideoManagerObject::TABLE_NAME . '.id != ' . $this->video->getId();
							break;
						case 'category':
							$sql .= ' AND ' . ilVideoManagerVideoTree::TABLE_NAME . '.parent = ' . $value['value'];
							break;
						case 'tag':
							$sql .= ' AND ' . ilVideoManagerObject::TABLE_NAME . '.tags LIKE ' . $this->db->quote("%" . $value['value'] . "%", 'text');
							break;
					}
					break;

				case 'sort_create_date':
					$sql .= ' ORDER BY ' . ilVideoManagerObject::TABLE_NAME . '.create_date ' . $value;
					break;

				case 'limit':
					$sql .= ' LIMIT ' . $value;
					break;
			}
		}

		$query = $this->db->query($sql);
		if ($this->options['count']) {
			return (int)$this->db->fetchObject($query)->count;
		}

		$data = array();
		$x = 0;
		while ($result = $this->db->fetchAssoc($query)) {
			$row = array();
			$video = new ilVideoManagerVideo($result['id']);
			$row['sort'] = $x;
			$x ++;
			$row['img'] = $video->getPreviewImageHttp();
			$row['title'] = $video->getTitle();
			$row['id'] = $video->getId();
			$row['create_date'] = $video->getCreateDate();
			$this->ctrl->setParameterByClass(ilVideoManagerUserGUI::class, ilVideoManagerAdminGUI::PARAM_NODE_ID, $video->getId());
			$row['link'] = $this->ctrl->getLinkTargetByClass(ilVideoManagerUserGUI::class, ilVideoManagerUserGUI::CMD_PLAY_VIDEO);
			$row['description'] = $video->getDescription($this->max_desc_length);
			$row['views'] = $result['views'];

			$data[] = $row;
		}

		return $data;
	}
}
