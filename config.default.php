<?php

define('JENKINS_HOST', '');

define('MERCURY_DIR', '');
define('PATH_TO_PRIVATE_KEY', '');

define('SSH_SERVER', '');
define('SSH_LOGIN', '');
define('SSH_PASSWORD', '');

define('DB_DSN', '');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');

define('INIT_JOB', '');
define('TEST_JOBS', serialize([]));

define('EMULATOR_JOB', '');

set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');

include __DIR__.'/phpseclib/Net/SSH2.php';
include __DIR__.'/phpseclib/Crypt/RSA.php';

function class_loader( $class ) {
	$filename = 'php/' . $class . '.class.php';
	if( is_readable( $filename ) ) {
		require $filename;
	}
}

spl_autoload_register( 'class_loader' );
