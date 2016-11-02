<?php

/**
 * Class ilVideoManagerQueryBuilder
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilVideoManagerQueryBuilder {

	/**
	 * @var array
	 */
	protected $options = array();
	/**
	 * @var null
	 */
	protected $video = null;
	/**
	 * @var array
	 */
	protected $videos = array();
	/**
	 * @var int
	 */
	protected $limit = null;


	/**
	 * ilVideoManagerQueryBuilder constructor.
	 *
	 * @param array $options
	 * @param null $video
	 */
	public function __construct(array $options, $video = null) {
		$this->options = $options;
		$this->video = $video;
		$this->setLimit($options['limit']);
		if ($options['cmd'] == 'related_videos') {
			$this->max_desc_length = 70;
		} else {
			$this->max_desc_length = 320;
		}
		$this->loadData();
	}


	/**
	 * @return array|int
	 */
	protected function loadData() {
		global $ilDB, $ilCtrl;
		$tree = new ilVideoManagerTree(1);
		if ($this->options['count']) {
			$sql = 'SELECT COUNT(vidm_data.id) AS count';
		} else {
			$sql = 'SELECT *, (SELECT COUNT(id) FROM vidm_views WHERE vidm_views.video_id = vidm_data.id) AS views';
		}

		$sql .= ' FROM vidm_data
                    JOIN vidm_tree ON (vidm_tree.child = vidm_data.id)';

		$sql .= ' WHERE vidm_data.type = ' . $ilDB->quote('vid', 'text');

		if ($hidden_nodes = $tree->getHiddenNodes()) {
			$sql .= ' AND vidm_data.id NOT IN (' . implode(',', $hidden_nodes) . ')';
		}

		if ($this->getLimit()) {
			$this->options['limit'] = $this->getLimit();
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
								$sql .= 'vidm_data.title LIKE ' . $ilDB->quote("%" . $word . "%", 'text');
								$sql .= ' OR vidm_data.description LIKE ' . $ilDB->quote("%" . $word . "%", 'text');
								$sql .= ' OR vidm_data.tags LIKE ' . $ilDB->quote("%" . $word . "%", 'text');
								$or = ' OR ';
							}
							$sql .= ')';
							break;
						case 'related':
							//related videos search for same tags/categories
							$sql .= ' AND (vidm_tree.parent = ' . $tree->getParentId($this->video->getId()); //categories names must be unique

							if ($this->video->getTags()) {
								foreach ($this->video->getTags() as $tag) {
									$sql .= ' OR vidm_data.tags LIKE ' . $ilDB->quote("%" . $tag . "%", 'text');
								}
							}
							$sql .= ')';
							$sql .= ' AND vidm_data.id != ' . $this->video->getId();
							break;
						case 'category':
							$sql .= ' AND vidm_tree.parent = ' . $value['value'];
							break;
						case 'tag':
							$sql .= ' AND vidm_data.tags LIKE ' . $ilDB->quote("%" . $value['value'] . "%", 'text');
							break;
					}
					break;

				case 'sort_create_date':
					$sql .= ' ORDER BY vidm_data.create_date ' . $value;
					break;

				case 'limit':
					$sql .= ' LIMIT ' . $value;
					break;
			}
		}

		$query = $ilDB->query($sql);
		if ($this->options['count']) {
			return (int)$ilDB->fetchObject($query)->count;
		}
		while ($result = $ilDB->fetchAssoc($query)) {
			$video = new ilVideoManagerVideo($result['id']);
			$video->afterObjectLoad();
			// $video->buildFromArray($result);

			$this->videos[] = $video;
		}
	}


	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}


	/**
	 * @param array $options
	 */
	public function setOptions($options) {
		$this->options = $options;
	}


	/**
	 * @return null
	 */
	public function getVideo() {
		return $this->video;
	}


	/**
	 * @param null $video
	 */
	public function setVideo($video) {
		$this->video = $video;
	}


	/**
	 * @return array
	 */
	public function getVideos() {
		return $this->videos;
	}


	/**
	 * @param array $videos
	 */
	public function setVideos($videos) {
		$this->videos = $videos;
	}


	/**
	 * @return int
	 */
	public function getLimit() {
		return $this->limit;
	}


	/**
	 * @param int $limit
	 */
	public function setLimit($limit) {
		$this->limit = $limit;
	}
}
