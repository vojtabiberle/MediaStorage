<?php


namespace vojtabiberle\MediaStorage\Files;


use vojtabiberle\MediaStorage\Bridges\IFileUpload;

interface IFile
{
    public function __construct($resource = null, $name = null, $full_path = null, $size = null, $content_type = null, $is_image = null);
    public function fillFileUpload(IFileUpload $fileUpload);
    public function fillData($data);
    public function setStoragePath($path);
    public function getName();
    public function getFullPath();
    public function getSize();
    public function getContentType();
    public function getIconName();
    public function isImage();
    public function isPrimary();
    public function getNamespace();
    public function save($path);
    public function getContent();
    public function getUID();
    public function setUID($uid);
    public function generateUID();
    public function __clone();
}