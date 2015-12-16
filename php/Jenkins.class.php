<?php

class Jenkins {

	private $error = false;

	private $report = [
		'SUCCESS' => [],
		'ABORTED' => [],
		'UNSTABLE' => [],
		'FAILURE' => []
	];

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
		$raw = [];

		foreach ($combined as $test) {
			array_push($raw, $test[0]);
		}

		$retryGroup = implode(",", $raw);

		echo "\n# Retry group:\n$retryGroup\n";
	}

	public function getErrorFlag() {
		return $this->error;
	}

	public function setErrorFlag() {
		$this->error = true;
	}

	public function addReportEntry($status, $groups, $logUrl) {
		$this->report[$status][] = [
			$groups,
			$logUrl
		];
	}

	public function getTestsFromGroup($haystack, $needle, &$output, $found = false) {
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

	public function getTestsFromGroups($groups) {
		$list = [];

		foreach ($groups as $group) {
			$output = [];
			$this->getTestsFromGroup(Tests::$GROUPS, $group, $output);

			$list = array_merge($list, $output);
		}

		return $list;
	}

	public function getJobUrl($job, $parameters = null) {
		if ($parameters === null) {
			return JENKINS_HOST.'/job/'.$job.'/build';
		} else {
			$parameters = http_build_query($parameters);

			return JENKINS_HOST.'/job/'.$job.'/buildWithParameters?'.$parameters;
		}
	}

	public function abortJob($job) {
		$this->getCurlOutput($job.'stop');
	}

	public function getQueueUrl($jobUrl) {
		$queueUrl = $this->getCurlOutput($jobUrl, true);
		$queueUrl = explode("\n", $queueUrl);
		$queueUrl = preg_grep('/^Location:/', $queueUrl);
		$queueUrl = array_values($queueUrl);
		$queueUrl = $queueUrl[0];
		$queueUrl = substr($queueUrl, strpos($queueUrl, ' ') + 1);

		return trim($queueUrl);
	}

	public function getBuildUrl($queueUrl) {
		$queueUrl .= 'api/json?pretty=true';
		$buildUrl = $this->getCurlOutput($queueUrl);
		$buildUrl = json_decode($buildUrl, true);

		return $buildUrl['executable']['url'];
	}

	public function getBuildStatus($buildUrl) {
		$buildUrl = $buildUrl.'api/json?pretty=true';
		$result = $this->getCurlOutput($buildUrl);
		$result = json_decode($result, true);

		if ($result['building']) {
			return null;
		} else {
			return $result['result'];
		}
	}

	public function isJobAvailable($job) {
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

	public function getCurlOutput($url, $addHeadersToOutput = false) {
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
