<?php

namespace vojtabiberle\MediaStorage;

class Utils
{
    const NAMESPACE_DELIMITER = '/';

    public static function explodeNamespace($name)
    {
        $pathParts = explode(Utils::NAMESPACE_DELIMITER, $name);
        $name = array_pop($pathParts);
        if (count($pathParts) > 0) {
            $namespace = implode(Utils::NAMESPACE_DELIMITER, $pathParts);
        } else {
            $namespace = null;
        }

        return [$name, $namespace];
    }

    public static function implodeNamespace($name, $namespace = null)
    {
        return is_null($namespace) ? $name : $namespace.Utils::NAMESPACE_DELIMITER.$name;
    }

    public static function contentType2IconName($contentType) {

        if (strpos($contentType, '/')) {
            list(, $contentType) = explode('/', $contentType);
        }

        switch ($contentType){
            case 'vnd.openxmlformats-officedocument.wordprocessingml.document':
                $contentType = 'docx';
                break;
            case 'vnd.oasis.opendocument.text':
                $contentType = 'odt';
                break;
        }

        return $contentType.'.png';
    }
}