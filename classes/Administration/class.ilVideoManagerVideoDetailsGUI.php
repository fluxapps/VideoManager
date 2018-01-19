<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerVideo.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/Form/classes/class.ilNonEditableValueGUI.php');
require_once("./Services/Rating/classes/class.ilRating.php");
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Count/class.vidmCount.php');

/**
 * Class ilVideoManagerVideoDetailsGUI
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilVideoManagerVideoDetailsGUI: ilRouterGUI, ilUIPluginRouterGUI
 */
class ilVideoManagerVideoDetailsGUI {

	/**
	 * @var ilVideoManagerVideo
	 */
	protected $video;
	/**
	 * @var ilVideoManagerPlugin
	 */
	protected $pl;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilVideoManagerAdminGUI
	 */
	protected $parent_gui;


	/**
	 * @param $video ilVideoManagerVideo
	 */
	public function __construct($parent_gui, $video) {
		$this->parent_gui = $parent_gui;
		$this->video = $video;
		$this->pl = ilVideoManagerPlugin::getInstance();
		$this->tpl = new ilTemplate('tpl.video_player.html', false, false, 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager');;
	}


	public function init() {
		if (! ilVideoManagerObject::__checkConverting($this->video->getId())) {
			ilUtil::sendInfo($this->pl->txt('msg_vid_converting'), true);
		}

		//		$this->tpl->setCurrentBlock('video_details');

		$this->initPropertiesForm();
		$this->initMediaPlayer();
		global $tpl;
		$tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/video_details.css');
		$tpl->setContent($this->tpl->get());
	}


	protected function initPropertiesForm() {
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->video->getTitle());
		//Title
		$title = new ilNonEditableValueGUI($this->pl->txt('common_title'));
		$title->setValue($this->video->getTitle());
		$form->addItem($title);

		//Description
		$description = new ilNonEditableValueGUI($this->pl->txt('common_description'));
		$description->setValue($this->video->getDescription(200));
		if (! $description->getValue()) {
			$description->setValue('-');
		}
		$form->addItem($description);

		//Tags
		$tags = new ilNonEditableValueGUI($this->pl->txt('common_tags'));
		$tags->setValue(implode(';', $this->video->getTags()));
		if (! $tags->getValue()) {
			$tags->setValue('-');
		}
		$form->addItem($tags);

		//Duration
		$duration = new ilNonEditableValueGUI($this->pl->txt('common_duration'));
		$duration->setValue(vmFFmpeg::getDuration($this->video->getAbsolutePath(), false));
		$form->addItem($duration);

		//Filesize
		$filesize = new ilNonEditableValueGUI($this->pl->txt('common_filesize'));

		if ($this->video->getFolderSize() < 1000) {
			$filesize->setValue(number_format($this->video->getFolderSize(), 1, '.', "'") . " Bytes");
			$form->addItem($filesize);
		} elseif ($this->video->getFolderSize() < 1000000) {
			$filesize->setValue(number_format($this->video->getFolderSize() / 1000, 1, '.', "'") . " KB");
			$form->addItem($filesize);
		} else {
			$filesize->setValue(number_format($this->video->getFolderSize() / 1000000, 1, '.', "'") . " MB");
			$form->addItem($filesize);
		}

		//Upload Date
		$update = new ilNonEditableValueGUI($this->pl->txt('common_upload_date'));
		$update->setValue($this->video->getCreateDate());
		$form->addItem($update);

		// Rating
		$rating = new ilRating();
		$average = $rating->getOverallRatingForObject($this->video->getId(), 'vid', 0, '-');
		$rating_gui = new ilNonEditableValueGUI($this->pl->txt('common_rating'));
		if ($average['avg']) {
			$rating_gui->setValue($average['avg'] . ' / 5 (' . $average['cnt'] . ' Rating(s))');
		} else {
			$rating_gui->setValue('-');
		}
		$form->addItem($rating_gui);

		if (vidmCount::isActive()) {
			$views = new ilNonEditableValueGUI($this->pl->txt('stats_views'));
			$views->setValue(vidmCount::countV($this->video->getId()));
			$form->addItem($views);
		}

		$this->tpl->setVariable('DESCRIPTION', $form->getHTML());
	}


	protected function initMediaPlayer() {
//		require_once('./Services/MediaObjects/classes/class.ilPlayerUtil.php');
//		ilPlayerUtil::initMediaElementJs();
		$this->tpl->setVariable('POSTER_SRC', $this->video->getPosterHttp());
		$this->tpl->setVariable('VIDEO_SRC', $this->video->getHttpPath() . '/' . $this->video->getTitle());
	}
}