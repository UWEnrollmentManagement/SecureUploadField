<?php

namespace UWDOEM\SecureUploads;

use Athens\Core\Field\Field;
use Athens\Core\Etc\ArrayUtils;

class SecureUploadField extends Field
{
    protected $destinationPath = "";
    protected $fileNamePrefix = "";

    public function setFileNamePrefix($fileNamePrefix)
    {
        $this->fileNamePrefix = $fileNamePrefix;
    }

    protected function getUploadedFileName()
    {
        return array_key_exists($this->getSlug(), $_FILES) === true ? $_FILES[$this->getSlug()]['name'] : '';
    }

    public function wasSubmitted()
    {

        return array_key_exists($this->getSlug(), $_FILES) === true && $this->getUploadedFileName() !== '';
    }

    public function getValidatedData()
    {
        if ($this->destinationPath === "" && $this->wasSubmitted() === true) {
            $_FILES[$this->getSlug()]['name'] = $this->fileNamePrefix . $this->getUploadedFileName();

            $fileName = Cipher::cleanFilename($_FILES[$this->getSlug()]['name']);

            Cipher::encrypt($this->getSlug(), SECURE_UPLOAD_DESTINATION_PATH_PREFIX, SECURE_UPLOAD_PUBLIC_KEY_PATH);

            $this->destinationPath = SECURE_UPLOAD_CIPHER_FILE_DESTINATION_PATH . $fileName;
        }

        return $this->destinationPath;
    }
}