<?php

namespace Athens\Encryption\Test;

use PHPUnit_Framework_TestCase;

use UWDOEM\SecureUploads\Cipher;

use UWDOEM\SecureUploads\SecureUploadFieldBuilder;
use UWDOEM\SecureUploads\SecureUploadField;


class FieldTest extends PHPUnit_Framework_TestCase
{
    public function testBuilder()
    {
        $field = SecureUploadFieldBuilder::begin()
            ->setType(SecureUploadFieldBuilder::TYPE_SECURE_UPLOAD)
            ->setLabel('label')
            ->build();

        $this->assertInstanceOf(SecureUploadField::class, $field);

        $field = SecureUploadFieldBuilder::begin()
            ->setType(SecureUploadFieldBuilder::TYPE_SECURE_UPLOAD_MULTIPLE)
            ->setLabel('label')
            ->build();

        $this->assertInstanceOf(SecureUploadField::class, $field);
    }

    public function testWasSubmitted()
    {
        $field = SecureUploadFieldBuilder::begin()
            ->setType('secure-upload')
            ->setLabel('label')
            ->build();

        $slug = $field->getSlug();

        $this->assertFalse($field->wasSubmitted());

        $_FILES[$slug]['name'] = 'test';

        $this->assertTrue($field->wasSubmitted());
    }

    public function testEncryptDecrypt()
    {
        define('SECURE_UPLOAD_PUBLIC_KEY_PATH', __DIR__ . '/certs/publickey.cer');
        define('SECURE_UPLOAD_CIPHER_FILE_DESTINATION_PATH', 'https://example.com/files');
        define('SECURE_UPLOAD_DESTINATION_PATH_PREFIX', __DIR__ . '/tmp/secure/');

        if (!is_dir(__DIR__ . '/tmp')) {
            mkdir(__DIR__ . '/tmp');
        }

        if (!is_dir(__DIR__ . '/tmp/out')) {
            mkdir(__DIR__ . '/tmp/out');
        }

        if (!is_dir(__DIR__ . '/tmp/secure')) {
            mkdir(__DIR__ . '/tmp/secure');
        }

        $data = '';
        for($i = 0; $i < 100; $i++) {
            $data .= str_shuffle('abcdefeghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        }

        $filename = md5(rand()) . '.txt';
        $fileLocation = __DIR__ . '/tmp/' . $filename;

        file_put_contents($fileLocation, $data);

        $field = SecureUploadFieldBuilder::begin()
            ->setType('secure-upload')
            ->setLabel('label')
            ->build();

        $_FILES[$field->getSlug()] = [
            'tmp_name' => $fileLocation,
            'name' => $filename,
        ];

        // Force encryption
        $field->getValidatedData();

        $location = SECURE_UPLOAD_DESTINATION_PATH_PREFIX . '/' . scandir(SECURE_UPLOAD_DESTINATION_PATH_PREFIX)[2];

        $decryptedLocation = Cipher::decrypt($location, __DIR__ . '/tmp/out/' , __DIR__ . '/certs/privatekey.pem');

        $this->assertEquals($data, trim(file_get_contents($decryptedLocation)));

        // Delete everything in the tmp directory
        array_map('unlink', glob(__DIR__ . "/tmp/out/*"));
        rmdir(__DIR__ . '/tmp/out');
        array_map('unlink', glob(__DIR__ . "/tmp/secure/*"));
        rmdir(__DIR__ . '/tmp/secure');
        array_map('unlink', glob(__DIR__ . "/tmp/*"));
        rmdir(__DIR__ . '/tmp');
    }
}
