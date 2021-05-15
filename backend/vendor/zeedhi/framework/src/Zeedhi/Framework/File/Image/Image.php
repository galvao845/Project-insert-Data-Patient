<?php
namespace Zeedhi\Framework\File\Image;

class Image {

    /** @var resource Image resource */
    protected $image;
    /** @var string Image mimeType */
    protected $mimeType;

    /**
     * __construct
     *
     * @param resource $image
     * @param string   $mimeType
     */
    public function __construct($image, $mimeType) {
        $this->image = $image;
        $this->mimeType = $mimeType;
    }

    /**
     * getImage
     * Retrieve image resource
     *
     * @return resource Image resource
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * setImage
     * Set image resource
     *
     * @param resource $image Image resource
     */
    public function setImage($image) {
        $this->image = $image;
    }

    /**
     * getMimeType
     * Retrieve image mime-type
     *
     * @return string Image mime-type
     */
    public function getMimeType() {
        return $this->mimeType;
    }

    /**
     * setMimeType
     * Set image mime-type
     *
     * @param string $mimeType Image mime-type
     */
    public function setMimeType($mimeType) {
        $this->mimeType = $mimeType;
    }

}