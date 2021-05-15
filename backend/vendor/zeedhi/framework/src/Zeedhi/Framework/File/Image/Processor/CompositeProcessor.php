<?php
namespace Zeedhi\Framework\File\Image\Processor;

use Zeedhi\Framework\File\Image\Image;

class CompositeProcessor implements Processor {

    /** @var Processor */
    protected $firstProcessor;
    /** @var Processor */
    protected $secondProcessor;

    /**
     * __construct
     *
     * @param Processor $firstProcessor
     * @param Processor $secondProcessor
     */
    public function __construct(Processor $firstProcessor, Processor $secondProcessor) {
        $this->firstProcessor = $firstProcessor;
        $this->secondProcessor = $secondProcessor;
    }

    /**
     * apply
     * Apply both processor to image
     *
     * @param  Image $image
     *
     * @return Image
     */
    public function apply(Image $image) {
        return $this->secondProcessor->apply($this->firstProcessor->apply($image));
    }

}