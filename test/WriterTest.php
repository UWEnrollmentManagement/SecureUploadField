<?php

namespace UWDOEM\SecureUploadField\Test;

use PHPUnit_Framework_TestCase;

use Athens\Core\Writer\HTMLWriter;

use UWDOEM\SecureUploadField\SecureUploadFieldBuilder;

class WriterTest extends PHPUnit_Framework_TestCase
{
    protected function stripQuotes($string)
    {
        return str_replace(['"', "'"], "", $string);
    }

    public function testBuilder()
    {
        $field = SecureUploadFieldBuilder::begin()
            ->setType(SecureUploadFieldBuilder::TYPE_SECURE_UPLOAD_DISPLAY)
            ->setLabel('label')
            ->setInitial('https://example.com/file.pdf http://www.example.edu/dir/file.html')
            ->build();

        $writer = new HTMLWriter();
        
        $result = $this->stripQuotes($writer->visitField($field));

        $this->assertContains("<span class=secure-upload-display", $result);
        $this->assertContains("<a href=https://example.com/file.pdf>file.pdf</a>", $result);
        $this->assertContains("<a href=http://www.example.edu/dir/file.html>file.html</a>", $result);
    }
}
