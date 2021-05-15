<?php
namespace Zeedhi\Framework\File;

use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;

class TypeValidator {

    /** @var ExtensionGuesserInterface $mimeTypeGuesser */
    protected $mimeTypeGuesser;

    public function __construct(ExtensionGuesserInterface $mimeTypeGuesser) {
        $this->mimeTypeGuesser = $mimeTypeGuesser;
    }

    /**
     * @param array $file
     *
     * @return string
     */
    private function getFileExtension(array $file) {
        return substr($file["name"], strrpos($file["name"], ".") + 1);
    }

    /**
     * @param array $file
     * @param array $acceptedExtensions
     *
     * @return bool
     */
    private function isExtensionValid(array $file, array $acceptedExtensions) {
        $fileExtension = $this->getFileExtension($file);
        return empty($acceptedExtensions)
            || in_array($fileExtension, $acceptedExtensions);
    }

    /**
     * @param array $file
     * @return mixed
     */
    private function getMimeTypeFromBase64(array $file) {
        list($b64DataType, $b64Content) = explode(";base64,", $file['b64File']);
        return str_replace("data:", "", $b64DataType);
    }

    /**
     * @param array $file
     * @return mixed
     */
    private function getFileMimeType(array $file) {
        return $file["type"];
    }

    /**
     * @param array $file
     * @param array $acceptedMimeTypes
     *
     * @return bool
     */
    private function isMimeTypeValid(array $file, array $acceptedMimeTypes) {
        $fileDataMimeType = $this->getFileMimeType($file);
        $mimeTypeFromBase64 = $this->getMimeTypeFromBase64($file);
        return (empty($acceptedMimeTypes) || in_array($fileDataMimeType, $acceptedMimeTypes))
            && $fileDataMimeType === $mimeTypeFromBase64;
    }

    private function doesExtensionMatchesMimeType(array $file) {
        $fileExtension = $this->getFileExtension($file);
        $mimeType = $this->getFileMimeType($file);
        $guessedExtension = $this->mimeTypeGuesser->guess($mimeType);
        return $guessedExtension === $fileExtension;
    }

    /**
     * Validate that file is accepted by mimeTypes and extensions.
     *
     * Check if data provided in parameter $file belongs to the chosen
     * list of mime types and the chosen list of extensions.
     *
     * @param array    $file               The file data, must has the keys "type", "b64File" and "name"
     * @param string[] $acceptedMimeTypes  This is a list of mimeTypes to be accepted.
     * @param string[] $acceptedExtensions This is a list of extensions to be accepted.
     *
     * @return boolean True if valid, false otherwise.
     */
    public function isValid(array $file, array $acceptedMimeTypes, array $acceptedExtensions) {
        return $this->isExtensionValid($file, $acceptedExtensions)
            && $this->isMimeTypeValid($file, $acceptedMimeTypes)
            && $this->doesExtensionMatchesMimeType($file);
    }
}