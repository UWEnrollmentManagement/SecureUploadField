<?php

namespace UWDOEM\SecureUploads;

use Athens\Core\Field\FieldInterface;
use Athens\Core\Field\Field;
use Athens\Core\Writer\WritableTrait;

class SecureUploadField extends Field
{
    
    protected $hasBeenCiphered = false;
    
    public function getFilePrefix()
    {
        
    }

    protected function getUploadedFileName()
    {
        return array_key_exists($this->getSlug(), $_FILES) === true ? $_FILES[$this->getSlug()]['name'] : '';
    }

    public function wasSubmitted()
    {
        return array_key_exists($this->getSlug(), $_FILES) === true && $this->getUploadedFileName() !== '';
    }
    
    public function getSubmitted()
    {
        $_FILES[$this->getSlug()]['name'] = $this->getFilePrefix() . $this->getUploadedFileName();
        
        $fileName = Cipher::cleanFilename($_FILES[$this->getSlug()]['name']);
        
        if ($this->hasBeenCiphered === false) {
            Cipher::encrypt($this->getSlug(), SECURE_UPLOAD_CIPHER_FILE_DESTINATION_PATH, SECURE_UPLOAD_PUBLIC_KEY_PATH);
        }
        
        return $fileName;
    }
}