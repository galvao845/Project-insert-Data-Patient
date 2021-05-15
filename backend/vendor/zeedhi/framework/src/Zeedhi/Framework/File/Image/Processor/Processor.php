<?php
namespace Zeedhi\Framework\File\Image\Processor;

use Zeedhi\Framework\File\Image\Image;

interface Processor {

    /**
     * apply
     * Manipulate image to create a new one
     *
     * @param  Image $image
     *
     * @return Image
     */
    public function apply(Image $image);

}