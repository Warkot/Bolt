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
			case 'update-sandbox-mercury-dev':
				$this->updateSandboxMercuryDev();
				break;
			default:
				echo "Exec param is incorrect\n";
				$this->setErrorFlag();
		}
	}

	public function runOnDevices() {
		$jenkinsDevices = new JenkinsDevices($this->params);
		$jenkinsDevices->runInitJob();
		$jenkinsDevices->runTests();
		$jenkinsDevices->printResults();
		$jenkinsDevices->printRetryGroup();

		if ($jenkinsDevices->getErrorFlag()) {
			$this->setErrorFlag();
		}
	}

	public function runOnEmulators() {
		$jenkinsEmulators = new JenkinsEmulators($this->params);
		$jenkinsEmulators->runTests();
		$jenkinsEmulators->printResults();
		$jenkinsEmulators->printRetryGroup();

		if ($jenkinsEmulators->getErrorFlag()) {
			$this->setErrorFlag();
		}
	}

	public function updateSandboxMercuryDev() {
		echo "# Updating sandbox-mercurydev started\n";
		$jenkins = new Jenkins();
		$jobUrl = $jenkins->getJobUrl(DEV_SYNC_JOB);
		echo "# Waiting for the job to be available\n";

		while (!$jenkins->isJobAvailable(DEV_SYNC_JOB)) {
			sleep(1);
		}

		$jobQueueUrl = $jenkins->getQueueUrl($jobUrl);
		echo "# Waiting for the job to be built\n";

		do {
			sleep(1);
			$jobBuildUrl = $jenkins->getBuildUrl($jobQueueUrl);
		} while (empty($jobBuildUrl));

		echo "# Waiting for the job to finish\n";

		do {
			sleep(1);
			$jobStatus = $jenkins->getBuildStatus($jobBuildUrl);
		} while (empty($jobStatus));

		echo "# Job done. Setting flag and exiting. Bye!\n";

		if (!empty($jobStatus) && $jobStatus !== 'SUCCESS') {
			$this->setErrorFlag();
		}
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
