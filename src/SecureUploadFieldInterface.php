<?php

namespace UWDOEM\SecureUploadField;

use Athens\Core\Field\FieldInterface;

/**
 * Interface SecureUploadFieldInterface
 *
 * @package UWDOEM\SecureUploads
 */
interface SecureUploadFieldInterface extends FieldInterface, SecureUploadFieldConstantsInterface
{

    /**
     * @param string $fileNamePrefix
     * @return FieldInterface
     */
    public function setFileNamePrefix($fileNamePrefix);

    /**
     * @param string $fileNamePrefix
     * @return FieldInterface
     */
    public function clearDestinationPaths($fileNamePrefix);
}
