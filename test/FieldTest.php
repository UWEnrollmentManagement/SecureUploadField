<?php

namespace UWDOEM\SecureUploadField\Test;

use PHPUnit_Framework_TestCase;

use UWDOEM\SecureUploads\Cipher;

use UWDOEM\SecureUploadField\SecureUploadFieldBuilder;
use UWDOEM\SecureUploadField\SecureUploadField;

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

        $_FILES[$slug]['name'] = '';

        $this->assertFalse($field->wasSubmitted());

        $_FILES[$slug]['name'] = 'test';

        $this->assertTrue($field->wasSubmitted());
    }

    public function setUp()
    {
        if (!is_dir(__DIR__ . '/tmp')) {
            mkdir(__DIR__ . '/tmp');
        }

        if (!is_dir(__DIR__ . '/tmp/out')) {
            mkdir(__DIR__ . '/tmp/out');
        }

        if (!is_dir(__DIR__ . '/tmp/secure')) {
            mkdir(__DIR__ . '/tmp/secure');
        }
    }

    public function tearDown()
    {
        // Delete everything in the tmp directory
        array_map('unlink', glob(__DIR__ . "/tmp/out/*"));
        rmdir(__DIR__ . '/tmp/out');
        array_map('unlink', glob(__DIR__ . "/tmp/secure/*"));
        rmdir(__DIR__ . '/tmp/secure');
        array_map('unlink', glob(__DIR__ . "/tmp/*"));
        rmdir(__DIR__ . '/tmp');
    }

    protected function createFile()
    {
        $data = '';
        for ($i = 0; $i < 100; $i++) {
            $data .= str_shuffle('abcdefeghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        }

        $filename = md5(rand()) . '.txt';
        $fileLocation = __DIR__ . '/tmp/' . $filename;

        file_put_contents($fileLocation, $data);

        return $fileLocation;
    }

    public function testEncryptDecryptSingleUpload()
    {
        $field = SecureUploadFieldBuilder::begin()
            ->setType(SecureUploadFieldBuilder::TYPE_SECURE_UPLOAD)
            ->setLabel('label-for-single')
            ->build();

        $fileLocation = $this->createFile();
        $fileName = array_slice(explode('/', $fileLocation), -1)[0];

        $_FILES[$field->getSlug()] = [
            'tmp_name' => $fileLocation,
            'name' => $fileName,
        ];

        // Force encryption
        $field->getValidatedData();

        $location = SECURE_UPLOAD_DESTINATION_PATH_PREFIX . '/' . scandir(SECURE_UPLOAD_DESTINATION_PATH_PREFIX)[2];

        $decryptedLocation = Cipher::decrypt($location, __DIR__ . '/tmp/out/', __DIR__ . '/certs/privatekey.pem');

        $originalData = trim(file_get_contents($fileLocation));
        $decryptedData = trim(file_get_contents($decryptedLocation));

        $this->assertEquals($originalData, $decryptedData);
    }

    public function testValidatedDataSingleUpload()
    {
        $field = SecureUploadFieldBuilder::begin()
            ->setType(SecureUploadFieldBuilder::TYPE_SECURE_UPLOAD)
            ->setLabel('label-for-single')
            ->build();

        $prefix = (string)rand() . "-";

        $field->setFileNamePrefix($prefix);

        $fileLocation = $this->createFile();
        $fileName = array_slice(explode('/', $fileLocation), -1)[0];

        $_FILES[$field->getSlug()] = [
            'tmp_name' => $fileLocation,
            'name' => $fileName,
        ];

        $validatedData = $field->getValidatedData();
        $expectedData = SECURE_UPLOAD_CIPHER_FILE_DESTINATION_PATH . $prefix . Cipher::cleanFilename($fileName);

        $this->assertEquals($expectedData, $validatedData);
    }

    public function testEncryptDecryptMultipleUpload()
    {
        $field = SecureUploadFieldBuilder::begin()
            ->setType(SecureUploadFieldBuilder::TYPE_SECURE_UPLOAD_MULTIPLE)
            ->setLabel('label-for-multiple')
            ->build();

        $fileLocations = [$this->createFile(), $this->createFile(), $this->createFile()];
        $fileNames = array_map(
            function ($fileLocation) {
                return array_slice(explode('/', $fileLocation), -1)[0];
            },
            $fileLocations
        );

        $_FILES[$field->getSlug()] = [
            'tmp_name' => $fileLocations,
            'name' => $fileNames,
        ];

        $originalData = [];
        foreach ($fileLocations as $fileLocation) {
            $originalData[] = trim(file_get_contents($fileLocation));
        }
        sort($originalData);

        // Force encryption
        $field->getValidatedData();

        $locations = scandir(SECURE_UPLOAD_DESTINATION_PATH_PREFIX);
        $locations = array_filter(
            $locations,
            function ($location) {
                return substr($location, -5) === '.data';
            }
        );

        $decryptedData = [];
        foreach ($locations as $location) {
            $decryptedLocation = Cipher::decrypt(
                SECURE_UPLOAD_DESTINATION_PATH_PREFIX . '/' . $location,
                __DIR__ . '/tmp/out/',
                __DIR__ . '/certs/privatekey.pem'
            );
            $decryptedData[] = trim(file_get_contents($decryptedLocation));
        }
        sort($decryptedData);

        $this->assertEquals($originalData, $decryptedData);
    }

    public function testValidatedDataMultipleUpload()
    {
        $field = SecureUploadFieldBuilder::begin()
            ->setType(SecureUploadFieldBuilder::TYPE_SECURE_UPLOAD_MULTIPLE)
            ->setLabel('label-for-multiple')
            ->build();

        $prefix = (string)rand() . "-";

        $field->setFileNamePrefix($prefix);

        $fileLocations = [$this->createFile(), $this->createFile(), $this->createFile()];
        $fileNames = array_map(
            function ($fileLocation) {
                return array_slice(explode('/', $fileLocation), -1)[0];
            },
            $fileLocations
        );

        $_FILES[$field->getSlug()] = [
            'tmp_name' => $fileLocations,
            'name' => $fileNames,
        ];

        $validatedData = explode(" ", $field->getValidatedData());

        $expectedData = array_map(
            function ($fileName) use ($prefix) {
                return SECURE_UPLOAD_CIPHER_FILE_DESTINATION_PATH . $prefix . Cipher::cleanFilename($fileName);
            },
            $fileNames
        );

        $this->assertEquals($expectedData, $validatedData);
    }
}
