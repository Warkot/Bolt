<?php

class JenkinsDevices extends Jenkins {

	private $initJob = INIT_JOB;
	private $testJobs = [];
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
		$this->testJobs = unserialize(TEST_JOBS);
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
		echo "\n# Started tests runner\n";

		while (!empty($this->pendingTests) || !empty($this->processedTests)) {

			foreach ($this->testJobs as $testJob => $appiumJob) {
				if ($this->isJobAvailable($testJob) && $this->isJobAvailable($appiumJob)) {
					$testGroup = array_shift($this->pendingTests);

					if (!empty($testGroup)) {
						$appiumUrl = $this->getJobUrl($appiumJob);
						$appiumQueueUrl = $this->getQueueUrl($appiumUrl);

						$this->processedTests[$testGroup] = [
							'testJob' => $testJob,
							'groups' => $testGroup,
							'appiumStatus' => 'queue',
							'appiumQueueUrl' => $appiumQueueUrl
						];
					}
				}
			}

			foreach ($this->processedTests as &$processedTest) {
				if ($processedTest['appiumStatus'] === 'queue') {
					$appiumBuildUrl = $this->getBuildUrl($processedTest['appiumQueueUrl']);

					if (!empty($appiumBuildUrl)) {
						$parameters = $this->parameters;
						$parameters['groups'] = $processedTest['groups'];

						$testUrl = $this->getJobUrl($processedTest['testJob'], $parameters);
						$testQueueUrl = $this->getQueueUrl($testUrl);

						$processedTest['appiumStatus'] = 'build';
						$processedTest['appiumBuildUrl'] = $appiumBuildUrl;
						$processedTest['testStatus'] = 'queue';
						$processedTest['testQueueUrl'] = $testQueueUrl;

						echo "- ".$processedTest['groups']." --> started\n";
					}
				}

				if ($processedTest['testStatus'] === 'queue') {
					$testBuildUrl = $this->getBuildUrl($processedTest['testQueueUrl']);

					if (!empty($testBuildUrl)) {
						$processedTest['testStatus'] = 'build';
						$processedTest['testBuildUrl'] = $testBuildUrl;
					}
				}

				if ($processedTest['testStatus'] === 'build') {
					$testBuildStatus = $this->getBuildStatus($processedTest['testBuildUrl']);

					if (!empty($testBuildStatus)) {
						$processedTest['status'] = $testBuildStatus;
						array_push($this->finishedTests, $processedTest);
						$this->abortJob($processedTest['appiumBuildUrl']);
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

	public function runInitJob() {
		echo "# Started init Job\n";
		$initJobUrl = $this->getJobUrl($this->initJob, $this->seleniumBranch);
		$initJobQueue = $this->getQueueUrl($initJobUrl);
		$initJobBuild = null;

		while (empty($initJobBuild)) {
			$initJobBuild = $this->getBuildUrl($initJobQueue);
			sleep(1);
		}

		while (empty($this->getBuildStatus($initJobBuild))) {
			sleep(1);
		}

		echo "# Finished init Job\n";
	}
}
