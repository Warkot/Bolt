<?php

require __DIR__.'/config.php';

class MainController {

	public function runTests($params) {
		$jenkinsRunner = new JenkinsRunner($params);
		$jenkinsRunner->runInitJob();
		$jenkinsRunner->runTests();
		$jenkinsRunner->printResults();
		$jenkinsRunner->printRetryGroup();
	}
}

$mainController = new MainController();

parse_str($argv[1], $params);
$mainController->runTests($params);
