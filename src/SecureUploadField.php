<?php

namespace UWDOEM\SecureUploadField;

use Athens\Core\Field\Field;
use Athens\Core\Field\FieldInterface;

use UWDOEM\SecureUploads\Cipher;

/**
 * Class SecureUploadField provides a field which can securely handle the upload of
 * documents.
 *
 * @package UWDOEM\SecureUploads
 */
class SecureUploadField extends Field implements SecureUploadFieldInterface
{
    /** @var string */
    protected $destinationPath = "";

    /** @var string */
    protected $fileNamePrefix = "";

    /**
     * Sets the file name prefix for when the file is decrypted.
     *
     * Useful for avoiding name collisions. Eg: add a nonce prefix and never have a file name collision.
     *
     * @param string $fileNamePrefix
     * @return FieldInterface
     */
    public function setFileNamePrefix($fileNamePrefix)
    {
        $this->fileNamePrefix = $fileNamePrefix;
        return $this;
    }

    /**
     * Return a list of filenames uploaded to this field.
     *
     * As from $_FILES['field_name']['name'], but always an array.
     *
     * @return string[]
     */
    protected function getFileNames()
    {
        return $this->getType() === static::TYPE_SECURE_UPLOAD_MULTIPLE ? $_FILES[$this->getSlug()]['name'] :
            [$_FILES[$this->getSlug()]['name']];
    }

    /**
     * Return a list of temp file locations uploaded to this field.
     *
     * As from $_FILES['field_name']['tmp_name'], but always an array.
     *
     * @return string[]
     */
    protected function getFileLocations()
    {
        return $this->getType() === static::TYPE_SECURE_UPLOAD_MULTIPLE ? $_FILES[$this->getSlug()]['tmp_name'] :
            [$_FILES[$this->getSlug()]['tmp_name']];
    }

    /**
     * A predicate which reports whether or not the field was submitted content.
     *
     * @return boolean
     */
    public function wasSubmitted()
    {
        if (array_key_exists($this->getSlug(), $_FILES) === false) {
            return false;
        }

        $nonBlankFilenames = array_filter(
            $this->getFileNames(),
            function ($fileName) {
                return $fileName !== '';
            }
        );

        return sizeof($nonBlankFilenames) > 0;
    }

    /**
     * Returns a space separated list of the final decrypted file paths for this field.
     *
     * @return string
     */
    public function getValidatedData()
    {
        if ($this->destinationPath === "" && $this->wasSubmitted() === true) {
            $fileNames = $this->getFileNames();
            $fileLocations = $this->getFileLocations();

            $destinationPaths = [];
            foreach (array_combine($fileLocations, $fileNames) as $fileLocation => $fileName) {
                $fileName = Cipher::cleanFilename($this->fileNamePrefix . $fileName);

                Cipher::encrypt(
                    $fileName,
                    $fileLocation,
                    SECURE_UPLOAD_DESTINATION_PATH_PREFIX,
                    SECURE_UPLOAD_PUBLIC_KEY_PATH
                );

                $destinationPaths[] = SECURE_UPLOAD_CIPHER_FILE_DESTINATION_PATH . $fileName;
            }

            $this->destinationPath = implode(" ", $destinationPaths);
        }

        return $this->destinationPath;
    }
}
