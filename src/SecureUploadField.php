<?php

namespace UWDOEM\SecureUploads;

use Athens\Core\Field\Field;

class SecureUploadField extends Field implements SecureUploadFieldConstantsInterface
{
    protected $destinationPath = "";
    protected $fileNamePrefix = "";

    public function setFileNamePrefix($fileNamePrefix)
    {
        $this->fileNamePrefix = $fileNamePrefix;
    }

    protected function getFileNames()
    {
        return $this->getType() === static::TYPE_SECURE_UPLOAD_MULTIPLE ? $_FILES[$this->getSlug()]['name'] :
                                                                          [$_FILES[$this->getSlug()]['name']];
    }

    protected function getFileLocations()
    {
        return $this->getType() === static::TYPE_SECURE_UPLOAD_MULTIPLE ? $_FILES[$this->getSlug()]['tmp_name'] :
                                                                          [$_FILES[$this->getSlug()]['tmp_name']];
    }

    public function wasSubmitted()
    {
        return array_key_exists($this->getSlug(), $_FILES) === true && $this->getFileNames() !== [];
    }

    public function getValidatedData()
    {
        if ($this->destinationPath === "" && $this->wasSubmitted() === true) {

            $fileNames = $this->getFileNames();
            $fileLocations = $this->getFileLocations();

            $destinationPaths = [];
            foreach (array_combine($fileLocations, $fileNames) as $fileLocation => $fileName) {
                $fileName = Cipher::cleanFilename($this->fileNamePrefix . $fileName);
                
                Cipher::encrypt($fileName, $fileLocation, SECURE_UPLOAD_DESTINATION_PATH_PREFIX, SECURE_UPLOAD_PUBLIC_KEY_PATH);
                
                $destinationPaths[] = SECURE_UPLOAD_CIPHER_FILE_DESTINATION_PATH . $fileName;
            }
            
            $this->destinationPath = implode(" ", $destinationPaths);
        }

        return $this->destinationPath;
    }
}