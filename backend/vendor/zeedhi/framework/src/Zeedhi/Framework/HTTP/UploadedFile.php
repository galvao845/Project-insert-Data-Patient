<?php
namespace Zeedhi\Framework\HTTP;

class UploadedFile extends \Symfony\Component\HttpFoundation\File\UploadedFile {


    public static function factoryFromFileData($fileData, $destinationFolder) {
        list($contentType, $base64String) = explode(',', $fileData['b64File']);
        $filePath = $destinationFolder . DIRECTORY_SEPARATOR . uniqid() . $fileData['name'];
        file_put_contents($filePath, base64_decode($base64String));
        return new static($filePath, $fileData['name'], $contentType, $fileData['size'], null, true);
    }
}