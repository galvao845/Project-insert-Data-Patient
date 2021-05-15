<?php
namespace Zeedhi\Framework\File;

use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser as SymfonyMimeTypeExtensionGuesser;

class MimeTypeExtensionGuesser extends SymfonyMimeTypeExtensionGuesser implements ExtensionGuesserInterface {

    public function addMimeType($mimeType, $extension) {
        $this->defaultExtensions[$mimeType] = $extension;
    }
}