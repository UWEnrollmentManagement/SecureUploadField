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
    /** @var string[] */
    protected $destinationPaths = [];

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
    protected function getUploadedFileNames()
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
    protected function getUploadedFileLocations()
    {
        return $this->getType() === static::TYPE_SECURE_UPLOAD_MULTIPLE ? $_FILES[$this->getSlug()]['tmp_name'] :
            [$_FILES[$this->getSlug()]['tmp_name']];
    }

    /**
     * Return a list of file names that were submitted as strings.
     *
     * @return string[]
     */
    protected function getSubmittedFileNames()
    {
        $submittedFileNames = parent::getSubmitted();

        if (is_string($submittedFileNames) === true) {
            $submittedFileNames = [$submittedFileNames];
        }

        return $submittedFileNames;
    }

    /**
     * A predicate which reports whether or not the field was submitted as a filename.
     *
     * For example, this would occur if the user attempted to submit a form and the form was
     * invalid but the file upload field was valid; the file will be uploaded and then the
     * field will contain a hidden field which includes the destination file name.
     *
     * @return boolean
     */
    protected function wasSubmittedAsFileNames()
    {
        return array_key_exists($this->getSlug(), $_POST) === true && $_POST[$this->getSlug()] !== '';
    }

    /**
     * A predicate which reports whether or not the field was submitted as an
     * uploaded file.
     * @return boolean
     */
    protected function wasSubmittedAsFiles()
    {
        if (array_key_exists($this->getSlug(), $_FILES) === false) {
            return false;
        }

        $nonBlankFilenames = array_filter(
            $this->getUploadedFileNames(),
            function ($fileName) {
                return $fileName !== '';
            }
        );

        return sizeof($nonBlankFilenames) > 0;
    }

    /**
     * A predicate which reports whether or not the field was submitted content.
     *
     * @return boolean
     */
    public function wasSubmitted()
    {
        return $this->wasSubmittedAsFiles() || $this->wasSubmittedAsFileNames();
    }

    /**
     * @return void
     */
    public function validateFilenameSubmissions()
    {
        foreach ($this->getSubmittedFileNames() as $filename) {
            $destinationPath = SECURE_UPLOAD_CIPHER_FILE_DESTINATION_PATH . Cipher::cleanFilename($filename);

            if (strpos($this->getInitial(), $destinationPath) === false) {
                $this->addError("Unrecognized file $filename.");
            } else {
                $this->destinationPaths[] = $destinationPath;
            }
        }
    }

    /**
     * @return void
     */
    public function validateFileUploadSubmissions()
    {
        $fileNames = $this->getUploadedFileNames();
        $fileLocations = $this->getUploadedFileLocations();

        foreach (array_combine($fileLocations, $fileNames) as $fileLocation => $fileName) {
            $fileName = Cipher::cleanFilename($this->fileNamePrefix . $fileName);

            Cipher::encrypt(
                $fileName,
                $fileLocation,
                SECURE_UPLOAD_DESTINATION_PATH_PREFIX,
                SECURE_UPLOAD_PUBLIC_KEY_PATH
            );

            $this->destinationPaths[] = SECURE_UPLOAD_CIPHER_FILE_DESTINATION_PATH . $fileName;
        }
    }

    /**
     * Validate the submission and force file encryption.
     *
     * Invokes parent::validate to perform basic field validation. Then, if a file was
     * uploaded for this field, performs encryption and stores the target filename as
     * validated data.
     *
     * @return void
     */
    public function validate()
    {
        parent::validate();

        if ($this->isValid() === true && $this->destinationPaths === []) {
            if ($this->wasSubmittedAsFiles() === true) {
                $this->validateFileUploadSubmissions();
            }

            if ($this->wasSubmittedAsFileNames() === true) {
                $this->validateFilenameSubmissions();
            }
        }
    }

    /**
     * Returns a space separated list of the final decrypted file paths for this field.
     *
     * @return string
     */
    public function getValidatedData()
    {
        return implode(' ', $this->destinationPaths);
    }

    /**
     * Returns the validated destination path, if available.
     *
     * @return string
     */
    public function getSubmitted()
    {
        if ($this->destinationPaths !== []) {
            return implode(' ', $this->destinationPaths);
        } else {
            return parent::getSubmitted();
        }
    }
}
