<?php

namespace UWDOEM\SecureUploads;

use Athens\Core\Field\FieldBuilder;
use Athens\Core\Field\FieldInterface;


class SecureUploadFieldBuilder extends FieldBuilder implements SecureUploadFieldConstantsInterface
{

    /**
     * @return FieldInterface
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