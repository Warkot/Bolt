<?php

class JenkinsRunner {

	private $initJob = INIT_JOB;
	private $testJobs = [];

	private $parameters = [
		'env' => 'prod',
		'groups' => '',
		'wikiName' => 'mercuryautomationtesting'
	];

	private $pendingTests = [];
	private $processedTests = [];
	private $finishedTests = [];
	private $report = [
		'SUCCESS' => [],
		'ABORTED' => [],
		'UNSTABLE' => [],
		'FAILURE' => []
	];

	public function __construct($params) {
		$this->testJobs = unserialize(TEST_JOBS);
		$this->pendingTests = array_filter(explode(',', str_replace(' ', '', $params['groups'])));

		if (!empty($params['env'])) {
			$this->parameters['env'] = $params['env'];
		}

		if (!empty($params['wikiName'])) {
			$this->parameters['wikiName'] = $params['wikiName'];
		}
	}

	public function runTests() {
		echo "# Started tests runner\n";

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

						switch ($processedTest['status']) {
							case 'SUCCESS':
								array_push($this->report['SUCCESS'], [
									$processedTest['groups'],
									$logUrl
								]);
								break;
							case 'FAILURE':
								array_push($this->report['FAILURE'], [
									$processedTest['groups'],
									$logUrl
								]);
								break;
							case 'ABORTED':
								array_push($this->report['ABORTED'], [
									$processedTest['groups'],
									$logUrl
								]);
								break;
							case 'UNSTABLE':
								array_push($this->report['UNSTABLE'], [
									$processedTest['groups'],
									$logUrl
								]);
								break;
						}

						echo "- ".$processedTest['groups']." <-- finished: ".$processedTest['status']."\n";
						unset($this->processedTests[$processedTest['groups']]);
					}
				}
			}

			sleep(1);
		}
	}

	public function printResults() {
		echo "\n# Test results:";

		foreach ($this->report as $status => $tests) {
			echo "\n".$status." (".count($tests)."):\n";

			foreach ($tests as $test) {
				echo "- $test[0] - $test[1]\n";
			}
		}
	}

	public function printRetryGroup() {
		$combined = array_merge($this->report['FAILURE'], $this->report['ABORTED'], $this->report['UNSTABLE']);
		$retryGroup = implode(",", $combined);

		echo "\n# Retry group:\n$retryGroup\n";
	}

	private function getTestsFromGroup($haystack, $needle, &$output, $found = false) {
		if (is_array($haystack)) {
			while ($hay = current($haystack)) {
				if (key($haystack) === $needle) {
					$this->getTestsFromGroup($hay, $needle, $output, true);
				} else {
					$this->getTestsFromGroup($hay, $needle, $output, $found);
				}

				next($haystack);
			}
		} elseif (!empty($haystack) && ($found || $haystack === $needle)) {
			array_push($output, $haystack);
		}
	}

	private function getTestsFromGroups($groups) {
		$list = [];

		foreach ($groups as $group) {
			$output = [];
			$this->getTestsFromGroup(Tests::$GROUPS, $group, $output);

			$list = array_merge($list, $output);
		}

		return $list;
	}

	private function getJobUrl($job, $parameters = null) {
		if ($parameters === null) {
			return JENKINS_HOST.'/job/'.$job.'/build';
		} else {
			$parameters = http_build_query($parameters);

			return JENKINS_HOST.'/job/'.$job.'/buildWithParameters?'.$parameters;
		}
	}

	private function abortJob($job) {
		$this->getCurlOutput($job.'stop');
	}

	private function getQueueUrl($jobUrl) {
		$queueUrl = $this->getCurlOutput($jobUrl, true);
		$queueUrl = explode("\n", $queueUrl);
		$queueUrl = preg_grep('/^Location:/', $queueUrl);
		$queueUrl = array_values($queueUrl);
		$queueUrl = $queueUrl[0];
		$queueUrl = substr($queueUrl, strpos($queueUrl, ' ') + 1);

		return trim($queueUrl);
	}

	private function getBuildUrl($queueUrl) {
		$queueUrl .= 'api/json?pretty=true';
		$buildUrl = $this->getCurlOutput($queueUrl);
		$buildUrl = json_decode($buildUrl, true);

		return $buildUrl['executable']['url'];
	}

	private function getBuildStatus($buildUrl) {
		$buildUrl = $buildUrl.'api/json?pretty=true';
		$result = $this->getCurlOutput($buildUrl);
		$result = json_decode($result, true);

		return $result['result'];
	}

	private function isJobAvailable($job) {
		$url = JENKINS_HOST.'/job/'.$job.'/api/json?pretty=true';
		$queue = $this->getCurlOutput($url);
		$queue = json_decode($queue, true);

		if (!empty($queue['queueItem'])) {
			return false;
		}

		if (empty($queue['lastBuild'])) {
			return true;
		}

		$url = JENKINS_HOST.'/job/'.$job.'/lastBuild/api/json?pretty=true';
		$current = $this->getCurlOutput($url);
		$current = json_decode($current, true);

		if (empty($current['result'])) {
			return false;
		}

		return true;
	}

	private function getCurlOutput($url, $addHeadersToOutput = false) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HEADER, $addHeadersToOutput);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERPWD, SSH_LOGIN.":".SSH_PASSWORD);
		$output = curl_exec($curl);
		curl_close($curl);

		return $output;
	}
}
