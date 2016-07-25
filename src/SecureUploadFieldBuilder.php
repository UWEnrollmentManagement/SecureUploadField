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
        if ($this->type === 'secure-upload') {
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