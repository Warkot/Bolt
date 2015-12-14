<?php
require __DIR__.'/config.php';
parse_str($argv[1], $params);

class MainController {

	private $error = false;
	private $params;

	public function __construct($params) {
		$this->params = $params;

		switch ($this->params['exec']) {
			case 'devices':
				$this->runOnDevices();
				break;
			case 'emulators':
				$this->runOnEmulators();
				break;
			default:
				echo "Exec param is incorrect\n";
				$this->setErrorFlag();
		}
	}

	public function runOnDevices() {
		$jenkinsRunner = new JenkinsDevices($this->params);
		$jenkinsRunner->runInitJob();
		$jenkinsRunner->runTests();
		$jenkinsRunner->printResults();
		$jenkinsRunner->printRetryGroup();

		if ($jenkinsRunner->getErrorFlag()) {
			$this->setErrorFlag();
		}
	}

	public function runOnEmulators() {
		$emulators = new JenkinsEmulators($this->params);
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

$mainController = new MainController($params);
