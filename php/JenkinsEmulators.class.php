<?php

class JenkinsEmulators extends Jenkins {

	private $emulatorJob = EMULATOR_JOB;
	private $seleniumBranch = [
		'branch' => 'master'
	];

	private $parameters = [
		'env' => 'prod',
		'groups' => '',
		'wikiName' => 'mercuryautomationtesting'
	];

	private $pendingTests = [];
	private $processedTests = [];
	private $finishedTests = [];

	public function __construct($params) {
		$this->pendingTests = array_filter(explode(',', str_replace(' ', '', $params['groups'])));

		if (!empty($params['env'])) {
			$this->parameters['env'] = $params['env'];
		}

		if (!empty($params['wikiName'])) {
			$this->parameters['wikiName'] = $params['wikiName'];
		}

		if (!empty($params['branch'])) {
			$this->seleniumBranch['branch'] = $params['branch'];
		}
	}

	public function runTests() {
		echo "# Started tests runner\n";

		foreach ($this->pendingTests as $pendingTest) {
			$parameters = $this->parameters;
			$parameters['groups'] = $pendingTest;

			$testUrl = $this->getJobUrl($this->emulatorJob, $parameters);
			$testQueueUrl = $this->getQueueUrl($testUrl);

			$this->processedTests[$pendingTest] = [
				'groups' => $pendingTest,
				'testStatus' => 'queue',
				'testQueueUrl' => $testQueueUrl
			];

			sleep(1);
		}

		while (!empty($this->processedTests)) {
			foreach ($this->processedTests as &$processedTest) {
				if ($processedTest['testStatus'] === 'queue') {
					$testBuildUrl = $this->getBuildUrl($processedTest['testQueueUrl']);

					if (!empty($testBuildUrl)) {
						$processedTest['testStatus'] = 'build';
						$processedTest['testBuildUrl'] = $testBuildUrl;

						echo "- ".$processedTest['groups']." --> started\n";
					}
				}

				if ($processedTest['testStatus'] === 'build') {
					$testBuildStatus = $this->getBuildStatus($processedTest['testBuildUrl']);

					if (!empty($testBuildStatus)) {
						$processedTest['status'] = $testBuildStatus;
						array_push($this->finishedTests, $processedTest);
						$logUrl = $processedTest['testBuildUrl'].'artifact/logs/log.html';
						$this->addReportEntry($processedTest['status'], $processedTest['groups'], $logUrl);

						if (!empty($processedTest['status']) && $processedTest['status'] !== 'SUCCESS') {
							$this->setErrorFlag();
						}

						echo "- ".$processedTest['groups']." <-- finished: ".$processedTest['status']."\n";
						unset($this->processedTests[$processedTest['groups']]);
					}
				}
			}

			sleep(1);
		}
	}
}
