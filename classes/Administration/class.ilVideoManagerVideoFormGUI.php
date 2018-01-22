<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerVideo.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcMedia.php');

/**
 * Class ilVideoManagerVideoFormGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilVideoManagerVideoFormGUI extends ilPropertyFormGUI {

	const GLUE = ';';
	/**
	 * @var ilVideoManagerAdminGUI
	 */
	protected $parent_gui;
	/**
	 * @var ilVideoManagerVideo
	 */
	protected $video;
	/**
	 * @var ilVideoManagerPlugin
	 */
	protected $pl;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @param                     $parent_gui
	 * @param ilVideoManagerVideo $video
	 */
	public function __construct($parent_gui, ilVideoManagerVideo $video) {
		parent::__construct();
		global $DIC;
		$this->ctrl = $DIC->ctrl();
		$this->parent_gui = $parent_gui;
		$this->video = $video;
		$this->pl = ilVideoManagerPlugin::getInstance();
		$this->initForm();
	}


	protected function initForm() {
		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));

		switch ($this->ctrl->getCmd()) {
			case ilVideoManagerAdminGUI::CMD_EDIT_VIDEO:
				$this->setTitle($this->pl->txt('form_edit_vid'));

				$title = new ilTextInputGUI($this->pl->txt('common_title'), 'title');
				$title->setRequired(true);
				$this->addItem($title);

				$desc = new ilTextAreaInputGUI($this->pl->txt('common_description'), 'description');
				$this->addItem($desc);

				$tags = new ilTextInputGUI($this->pl->txt('form_tags'), 'tags');
				$this->addItem($tags);

				$num_input = new ilNumberInputGUI($this->pl->txt('form_image_at_sec'), 'image_at_sec');
				$num_input->setInfo($this->pl->txt('form_image_at_sec_info'));
                $num_input->setRequired(true);
				$this->addItem($num_input);

				$this->addCommandButton(ilVideoManagerAdminGUI::CMD_UPDATE_VIDEO, $this->pl->txt('common_save'));
				$this->addCommandButton(ilVideoManagerAdminGUI::CMD_CANCEL, $this->pl->txt('common_cancel'));

				$this->ctrl->saveParameterByClass(ilVideoManagerAdminGUI::class, 'target_id');
				$this->setFormAction($this->ctrl->getFormActionByClass(ilVideoManagerAdminGUI::class, 'update'));

				break;

			case ilVideoManagerAdminGUI::CMD_ADD_VIDEO:
				$this->setTitle($this->pl->txt('form_upload_vid'));
				$this->setMultipart(true);

				require_once('./Services/Form/classes/class.ilDragDropFileInputGUI.php');
				$file_input = new ilFileInputGUI($this->pl->txt('form_vid'), 'video_file');
				$file_input->setRequired(true);
				$file_input->setSuffixes(array( '3gp', 'flv', 'mp4', 'webm' ));

				$this->addItem($file_input);

				$num_input = new ilNumberInputGUI($this->pl->txt('form_image_at_sec'), 'image_at_sec');
				$num_input->setInfo($this->pl->txt('form_image_at_sec_info'));
                $num_input->setRequired(true);
                $num_input->setValue(1);
				$this->addItem($num_input);

				$this->addCommandButton(ilVideoManagerAdminGUI::CMD_CREATE, $this->pl->txt('common_add'));
				$this->addCommandButton(ilVideoManagerAdminGUI::CMD_CANCEL, $this->pl->txt('common_cancel'));
				$this->setFormAction($this->ctrl->getFormActionByClass(ilVideoManagerAdminGUI::class, ilVideoManagerAdminGUI::CMD_CREATE));

				break;
		}
	}


	public function fillForm() {
		$array = array(
			'title' => $this->video->getTitle(),
			'description' => $this->video->getDescription(),
			'tags' => implode(self::GLUE, $this->video->getTags()),
			'suffix' => $this->video->getSuffix(),
			'image_at_sec' => $this->video->getImageAtSecond(),
		);
		$this->setValuesByArray($array);
	}


	/**
	 * @description returns whether checkinput was successful or not.
	 *
	 * @return bool
	 */
	public function fillObject() {
		if (! $this->checkInput()) {
			return false;
		}
		$this->video->setTitle(reset(explode('.', $this->getInput('title'))));
		$this->video->setDescription($this->getInput('description'));
		$this->video->setTags(explode(self::GLUE, $this->getInput('tags')));
		return true;
	}


	public function saveObject() {
		if (! $this->fillObject()) {
			return false;
		}

		if ($this->video->getId()) {
//			var_dump('' === 0);exit;

			//rename each file in the directory
			$dir = scandir($this->video->getPath());
			foreach ($dir as $file) {
				if($file !== "." && $file !== "..") {
					$suffix = array_pop(explode('.', $file));

					$ending = '';
					if (preg_match('/[.]*_poster[.]*/', $file)) {
						$ending = '_poster';
					} elseif (preg_match('/[.]*_preview[.]*/', $file)) {
						$ending = '_preview';
					}
					rename($this->video->getPath() . '/' . $file, $this->video->getPath() . '/' . $this->video->getTitle() . $ending . '.' . $suffix);
				}
			}

			//Extract image if changed
			if (!($this->getInput('image_at_sec') === $this->video->getImageAtSecond())) {
				$this->video->setImageAtSecond(is_numeric($this->getInput('image_at_sec')) ? $this->getInput('image_at_sec') : -1);
				$this->video->extractImage();
			}

			$this->video->update();
			$this->ctrl->redirect($this->parent_gui, ilVideoManagerUserGUI::CMD_VIEW);
		} else {
			$video_file = $_FILES['video_file'];
			$suffix = pathinfo($video_file['name'], PATHINFO_EXTENSION);
			if (! $this->checkSuffix($suffix)) {
				$response = new stdClass();
				$response->error = $this->pl->txt('form_wrong_filetype') . ' (' . $suffix . ')';

				return $response;
			}

			$this->video->setImageAtSecond(is_numeric($this->getInput('image_at_sec')) ? $this->getInput('image_at_sec') : -1);
			$this->video->setTitle(pathinfo($video_file['name'], PATHINFO_FILENAME));
			$this->video->setSuffix($suffix);
			$this->video->setCreateDate(date('Y-m-d'));
			$this->video->create();
			$this->video->uploadVideo($video_file['tmp_name']);

			$this->parent_gui->notifyUsers($this->video);

			$mediaConverter = new mcMedia();
			$mediaConverter->uploadFile($this->video->getTitle(), $this->video->getSuffix(), $this->video->getPath(), $this->video->getPath(), $this->video->getId());

			// create answer object
			$response = new stdClass();
			$response->fileName = $video_file['name'];
			$response->fileSize = intval($video_file['size']);
			$response->fileType = $video_file['type'];
			$response->fileUnzipped = '';
			$response->error = NULL;

			return $response;
		}

		return true;
	}


	function checkSuffix($suffix) {
		if (in_array($suffix, array( '3pgg', '3gp', 'flv', 'mp4', 'webm' ))) {
			return true;
		}

		return false;
	}
}