<?php
namespace Zeedhi\Framework\File\Image\Processor;

use Zeedhi\Framework\File\Image\Image;

class Resize implements Processor {

    /** @var integer Width to scale the images to */
    protected $width;
    /** @var integer Height to scale the image to */
    protected $height;
    /** @var integer Scale mode */
    protected $mode;

    /**
     * __construct
     *
     * @param integer $width  Width to scale the images to
     * @param integer $height Height to scale the image to
     * @param integer $mode   Scale mode
     */
    public function __construct($width, $height = -1, $mode = IMG_BILINEAR_FIXED) {
        $this->width = $width;
        $this->height = $height;
        $this->mode = $mode;
    }

    /**
     * apply
     * Resize image
     *
     * @param  Image $image
     *
     * @return Image
     */
    public function apply(Image $image) {
        $appliedImageResource = imagescale($image->getImage(), $this->width, $this->height, $this->mode);
        return new Image($appliedImageResource, $image->getMimeType());
    }

}