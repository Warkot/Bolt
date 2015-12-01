<?php

class Bash {

	const CMD_SEPARATOR = ';';

	public $status = 'git status';
	public $pullRebase = 'git pull --rebase';
	public $fetchPrune = 'git fetch --prune';
	public $removeAllReleaseBranches = 'git branch -r | awk -F/ \'/\/release/{print $2}\' | xargs -I {} git push origin :{}';
	public $getLastReleaseNumber = 'git tag -l | sed \'s/^.\{8\}//\' | sort -nr | head -1';
	public $removeLocalReleaseBranches = 'rm -rf .git/refs/heads/release*';
	public $removeLocalNpmVersions = 'rm -rf .git/refs/tags/v*';

	public function dtLock($env, $app, $lock = false) {
		$flag = '';

		if(!$lock) {
			$flag = '--release';
		}

		return "dt -y lock -t $app:$env $flag";
	}

	public function dtPrep($env, $app, $params) {
		$param = '';

		foreach($params as $key => $val) {
			$param .= "-r $key@$val ";
		}

		return "dt -y prep -e $env -a $app $param";
	}

	public function dtPush($env, $app) {
		return "dt -y push -e $env -a $app";
	}

	public function executeBash($where, $command, $decode=true) {
		$varError = 'bashError';
		$varSuccess = 'bashOK';
		$output = shell_exec(
			$this->navigateTo($where).
			self::CMD_SEPARATOR.
			$this->createTmpVar($varSuccess).
			self::CMD_SEPARATOR.
			$this->createTmpVar($varError).
			self::CMD_SEPARATOR.
			$this->execute(
				$command,
				$varSuccess,
				$varError
			).
			self::CMD_SEPARATOR.
			$this->cleanTmpVar($varSuccess).
			self::CMD_SEPARATOR.
			$this->cleanTmpVar($varError).
			self::CMD_SEPARATOR.
			$this->getResultAsJson($varSuccess, $varError).
			self::CMD_SEPARATOR.
			$this->removeTmpVar($varSuccess).
			self::CMD_SEPARATOR.
			$this->removeTmpVar($varError).
			self::CMD_SEPARATOR
		);

		if($decode) {
			return json_decode($output, true);
		}

		return $output;
	}

	public function executeOpenPullRequest($username, $password, $branch) {
		$varCurl = 'bashCurl';

		$output = shell_exec(
			$this->createTmpVar($varCurl).
			self::CMD_SEPARATOR.
			$this->openPullRequest($username, $password, $branch, $varCurl).
			self::CMD_SEPARATOR.
			$this->getPullRequestUrl($varCurl).
			self::CMD_SEPARATOR.
			$this->removeTmpVar($varCurl).
			self::CMD_SEPARATOR
		);

		return $output;
	}

	public function gitGetChangesBetween($from, $to) {
		return 'git diff --shortstat ' . $from . ' ' . $to;
	}

	public function gitCheckoutTo($branch) {
		return 'git checkout ' . $branch;
	}

	public function gitCheckoutToNewBranch($branch) {
		return 'git checkout -b ' . $branch;
	}

	public function gitAddFile($fileName) {
		return 'git add ' . $fileName;
	}

	public function gitCommit($message) {
		return 'git commit -m "' . $message . '"';
	}

	public function gitPush($branch) {
		return 'git push origin ' . $branch;
	}

	public function openPullRequest($username, $password, $branch, $rawOutput) {
		return 'curl -u ' . $username . ':' . $password . ' --data \'{"title": "' . $branch . '", "head": "Wikia:' .
		$branch . '", "base": "dev"}\' https://api.github.com/repos/wikia/mercury/pulls >$' . $rawOutput;
	}

	public function updateChangelog($branch) {
		return './tasks/changelog-update.sh -r ' . $branch;
	}

	public function getNumberOfReleasesInChangelog($release) {
		return 'cat CHANGELOG.md | grep "## ' . $release . ' " | wc -l';
	}

	public function getPullRequestUrl($rawOutput) {
		return 'cat $' . $rawOutput .
		' | grep "https://api.github.com/repos/" | grep "pulls" | grep "href" | head -1 | tr -d "[:space:]" |' .
		' sed -e \'s/^.\{8\}//\' -e \'s/.\{1\}$//\'';
	}

	public function setNpmVersion($release, $version) {
		return 'npm version ' . $release . '.' . $version . '.0';
	}

	public function navigateTo($dir) {
		return 'cd ' . $dir;
	}

	public function createTmpVar($varName) {
		return $varName . '=$(mktemp)';
	}

	public function removeTmpVar($varName) {
		return 'rm -rf $' . $varName;
	}

	public function execute($command, $success, $error) {
		return $command . ' 1>$' . $success . ' 2>$' . $error;
	}

	public function cleanTmpVar($varName) {
		return 'if [ "$(cat $'.$varName.' | head -1 | cut -c 1)" != "{" ];'.
		' then echo "\"""$(cat $'.$varName.')""\"\n" > $'.$varName.'; fi';
	}

	public function getResultAsJson($success, $error) {
		return 'printf \'{"success":%s,"error":%s,"exitCode":%s}\' '.
		'"$(cat $'. $success . ' | sed \'s#"#\"#g\')" '.
		'"$(cat $' . $error . ' | sed \'s#"#\"#g\')" '.
		"$(echo $?)";
	}
}
