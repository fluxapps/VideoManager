<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');
require_once('./Services/MediaObjects/classes/class.ilFFmpeg.php');
/**
 * Class ilVideoManagerVideo
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilVideoManagerVideo extends ilVideoManagerObject{

    public function __construct($id = 0)
    {
        $this->type = 'vid';
        parent::__construct($id);
    }

    public function create()
    {
        parent::create();
    }

    /**
     * @param string $tmp_path
     *
     * @return bool
     */
    public function uploadVideo($tmp_path) {
        move_uploaded_file($tmp_path, $this->getPath().'/'.$this->getTitle().'.'.$this->getSuffix());
        ilFFmpeg::extractImage($this->getAbsolutePath(), $this->getTitle().'_poster.png', $this->getPath());
        ilUtil::resizeImage($this->getPoster(), $this->getPreviewImage(), 178, 100, true); //TODO same size for all, add black frame for smaller images
        return true;
    }

    public function getPreviewImage()
    {
        return $this->getPath().'/'.$this->getTitle().'_preview.png';
    }

    public function getPoster()
    {
        return $this->getPath().'/'.$this->getTitle().'_poster.png';
    }
    public function getPreviewImageHttp()
    {
        return $this->getHttpPath().'/'.$this->getTitle().'_preview.png';
    }

    public function getPosterHttp()
    {
        return $this->getHttpPath().'/'.$this->getTitle().'_poster.png';
    }

    public function getImagePath()
    {
        return $this->getPath().'/'.rtrim($this->getTitle(), '.'.$this->getSuffix()).'_poster';
    }

} 