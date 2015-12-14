<?php

require __DIR__.'/config.php';

class MainController {

	private $error = false;

	public function runTests($params) {
		$jenkinsRunner = new JenkinsRunner($params);
		$jenkinsRunner->runInitJob();
		$jenkinsRunner->runTests();
		$jenkinsRunner->printResults();
		$jenkinsRunner->printRetryGroup();

		if ($jenkinsRunner->getErrorFlag()) {
			$this->setErrorFlag();
		}
	}

	public function runEmulators($params) {
		$emulators = new Emulators($params);
		$emulators->runTests();
		$emulators->printResults();
		$emulators->printRetryGroup();
	}

	public function __destruct() {
		if ($this->getErrorFlag()) {
			exit(1);
		}
	}

	private function setErrorFlag() {
		$this->error = true;
	}

	private function getErrorFlag() {
		return $this->error;
	}
}

parse_str($argv[1], $params);

$mainController = new MainController();
//$mainController->runTests($params);
$mainController->runEmulators($params);
