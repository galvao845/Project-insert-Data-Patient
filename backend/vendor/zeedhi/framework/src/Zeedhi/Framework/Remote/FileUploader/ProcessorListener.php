<?php
namespace Zeedhi\Framework\Remote\FileUploader;

use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\Events\PreDispatch\Listener as AbstractListener;
use Zeedhi\Framework\File\Image\Image;
use Zeedhi\Framework\File\Image\Processor\Processor;

class ProcessorListener extends AbstractListener {

    protected $processor;
    protected $configuration = array();

    public function __construct(Processor $processor, $configuration) {
        $this->processor = $processor;
        $this->configuration = $configuration;
    }

    protected function shouldCreateImages($route) {
        return isset($this->configuration[$route]);
    }

    protected function getRequestRows(Request $request) {
        $rows = array();
        if ($request instanceof Request\Row) {
            $rows = array($request->getRow());
        }

        if ($request instanceof Request\DataSet) {
            $rows = $request->getDataSet()->getRows();
        }

        return $rows;
    }

    protected function createImageFromBase64($b64File) {
        $imageData = explode(',', $b64File);

        $image = imagecreatefromstring(base64_decode($imageData[1]));
        $type = substr($imageData[0], 5, -7);

        return new Image($image, $type);
    }

    protected function getImageWriterMethod($image) {
        switch ($image->getMimeType()) {
            case 'image/jpeg': return 'imagejpeg';
            case 'image/png': return 'imagepng';
            default: throw new \Exception('Invalid image type');
        }
    }

    protected function getImageInfo($image) {
        ob_start();

        $method = $this->getImageWriterMethod($image);
        $method($image->getImage());

        $contents = ob_get_contents();
        ob_end_clean();

        return array(
            'base64' => 'data:' . $image->getMimeType() . ';base64,' . base64_encode($contents),
            'size' => strlen($contents)
        );
    }

    protected function generateImages($images) {
        return array_map(function($image) {
            $imageObj = $this->createImageFromBase64($image['b64File']);
            $processedImage = $this->processor->apply($imageObj);

            $processedImageInfo = $this->getImageInfo($processedImage);
            return array(
                'b64File' => $processedImageInfo['base64'],
                'name' => $image['name'],
                'size' => $processedImageInfo['size'],
                'type' => $processedImage->getMimeType()
            );
        }, $images);
    }

    protected function createProcessedImage(&$row, $configuration) {
        $imageFieldName = $configuration['originalFileField'];
        $generatedImageFieldName = $configuration['generatedFileField'];

        if (isset($row[$imageFieldName])) {
            $row[$generatedImageFieldName] = $this->generateImages($row[$imageFieldName]);
        }
    }

    protected function createProcessedImages(&$rows, $configuration) {
        foreach ($rows as $row) {
            $this->createProcessedImage($row, $configuration);
        }
    }

    public function preDispatch(Request $request) {
        $route = $request->getRoutePath();
        if ($this->shouldCreateImages($route)) {
            $rows = $this->getRequestRows($request);

            $this->createProcessedImages($rows, $this->configuration[$route]);
        }
    }

}