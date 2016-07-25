<?php

require_once dirname(__FILE__) ."/../vendor/autoload.php";

define('SECURE_UPLOAD_PUBLIC_KEY_PATH', __DIR__ . '/certs/publickey.cer');
define('SECURE_UPLOAD_CIPHER_FILE_DESTINATION_PATH', 'https://example.com/files/');
define('SECURE_UPLOAD_DESTINATION_PATH_PREFIX', __DIR__ . '/tmp/secure/');

$_SERVER["REQUEST_URI"] = "http://example.com/";

Athens\Core\Settings\Settings::getInstance()->addTemplateDirectories(__DIR__ . '/../templates');
