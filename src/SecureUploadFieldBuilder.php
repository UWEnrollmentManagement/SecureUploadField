<?php

namespace UWDOEM\SecureUploadField;

use Athens\Core\Field\FieldBuilder;
use Athens\Core\Field\FieldInterface;

/**
 * Class SecureUploadFieldBuilder builds SecureUploadFields
 *
 * Will defer to Athens\Core\Field\FieldBuilder if field type is not secure upload.
 *
 * @package UWDOEM\SecureUploads
 */
class SecureUploadFieldBuilder extends FieldBuilder implements SecureUploadFieldConstantsInterface
{

    /**
     * @return FieldInterface|SecureUploadFieldInterface
     */
    public function build()
    {
        if ($this->type === static::TYPE_SECURE_UPLOAD || $this->type === static::TYPE_SECURE_UPLOAD_MULTIPLE) {
            return new SecureUploadField(
                $this->classes,
                $this->data,
                $this->type,
                $this->label,
                $this->initial,
                $this->required,
                $this->choices,
                $this->fieldSize,
                $this->helptext,
                $this->placeholder
            );
        } else {
            return parent::build();
        }
    }
}
