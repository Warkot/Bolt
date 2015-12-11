<?php

require __DIR__.'/config.php';

class MainController {

	private $noErrors = true;

	public function runTests($params) {
		$jenkinsRunner = new JenkinsRunner($params);
		$jenkinsRunner->runInitJob();
		$jenkinsRunner->runTests();
		$jenkinsRunner->printResults();
		$jenkinsRunner->printRetryGroup();

		if (!$jenkinsRunner->getNoErrors()) {
			$this->noErrors = false;
		}
	}

	public function __destruct() {
		if (!$this->noErrors) {
			exit(1);
		}
	}
}

parse_str($argv[1], $params);

$mainController = new MainController();
$mainController->runTests($params);
