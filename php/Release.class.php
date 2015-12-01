<?php

class Release {

	private $dataBase;
	private $bash;

	public function __construct() {
		//$this->dataBase = new DataBase();
		//$this->bash = new Bash();
	}

	public function deploy() {
		$releaseId = $this->dataBase->startRelease();

		$ssh = new Net_SSH2(SSH_SERVER);
		$key = new Crypt_RSA();
		$key->setPassword(SSH_PASSWORD);
		$key->loadKey(file_get_contents(PATH_TO_PRIVATE_KEY));

		if (!$ssh->login(SSH_LOGIN, $key)) {
			$this->dataBase->logStep($releaseId, 'ssh ' . SSH_SERVER, ['error' => 'Login failed'], 1);
			exit('Login Failed');
		}

		$ssh->enableQuietMode();

		$command = $this->bash->dtLock('sandbox-mercury', 'mercury');
		$output['success'] = $ssh->exec($command);
		$output['error'] = $ssh->getStdError();
		$this->dataBase->logStep($releaseId, $command, $output, $ssh->getExitStatus());

		$command = $this->bash->dtPrep('sandbox-mercury', 'mercury', [
			"mercury" => "dev"
		]);
		$output['success'] = $ssh->exec($command);
		$output['error'] = $ssh->getStdError();
		$this->dataBase->logStep($releaseId, $command, $output, $ssh->getExitStatus());

		$command = $this->bash->dtPush('sandbox-mercury', 'mercury');
		$output['success'] = $ssh->exec($command);
		$output['error'] = $ssh->getStdError();
		$this->dataBase->logStep($releaseId, $command, $output, $ssh->getExitStatus());
	}


	public function begin() {
		$fromBranch = 'origin/dev';
		$releaseId = $this->dataBase->startRelease();

		$command = $this->bash->gitCheckoutTo('dev');
		$output = $this->bash->executeBash(MERCURY_DIR, $command);
		$this->dataBase->logStep($releaseId, $command, $output);

		$command = $this->bash->removeLocalReleaseBranches;
		$output = $this->bash->executeBash(MERCURY_DIR, $command);
		$this->dataBase->logStep($releaseId, $command, $output);

		$command = $this->bash->removeLocalNpmVersions;
		$output = $this->bash->executeBash(MERCURY_DIR, $command);
		$this->dataBase->logStep($releaseId, $command, $output);

		$command = $this->bash->pullRebase;
		$output = $this->bash->executeBash(MERCURY_DIR, $command);
		$this->dataBase->logStep($releaseId, $command, $output);

		$command = $this->bash->fetchPrune;
		$output = $this->bash->executeBash(MERCURY_DIR, $command);
		$this->dataBase->logStep($releaseId, $command, $output);

		$command = $this->bash->removeAllReleaseBranches;
		$output = $this->bash->executeBash(MERCURY_DIR, $command);
		$this->dataBase->logStep($releaseId, $command, $output);

		$command = $this->bash->getLastReleaseNumber;
		$output = $this->bash->executeBash(MERCURY_DIR, $command);
		$this->dataBase->logStep($releaseId, $command, $output);
		$toBranch = 'release-' . $output['success'];
		$lastReleaseTag = Utilities::splitReleaseTag($output['success']);

		$command = $this->bash->gitGetChangesBetween($fromBranch, $toBranch);
		$output = $this->bash->executeBash(MERCURY_DIR, $command);
		$this->dataBase->logStep($releaseId, $command, $output);
		$changes = $output['success'];

		if(empty($changes)) {
			return;
		}

		$newReleaseNumber = $lastReleaseTag['release'] + 1;
		$newReleaseBranch = 'release-' . $newReleaseNumber;

		$command = $this->bash->gitCheckoutToNewBranch($newReleaseBranch);
		$output = $this->bash->executeBash(MERCURY_DIR, $command);
		$this->dataBase->logStep($releaseId, $command, $output);

		$command = $this->bash->getNumberOfReleasesInChangelog($newReleaseBranch);
		$output = $this->bash->executeBash(MERCURY_DIR, $command);
		$this->dataBase->logStep($releaseId, $command, $output);
		$newReleaseVersion = $output['success'] + 1;

		$command = $this->bash->updateChangelog($newReleaseBranch);
		$output = $this->bash->executeBash(MERCURY_DIR, $command);
		$this->dataBase->logStep($releaseId, $command, $output);

		$command = $this->bash->gitAddFile('CHANGELOG.md');
		$output = $this->bash->executeBash(MERCURY_DIR, $command);
		$this->dataBase->logStep($releaseId, $command, $output);

		$command = $this->bash->gitCommit("update changelog");
		$output = $this->bash->executeBash(MERCURY_DIR, $command);
		$this->dataBase->logStep($releaseId, $command, $output);

		$command = $this->bash->setNpmVersion($newReleaseNumber, $newReleaseVersion);
		$output = $this->bash->executeBash(MERCURY_DIR, $command);
		$this->dataBase->logStep($releaseId, $command, $output);

		$command = $this->bash->gitPush($newReleaseBranch);
		$output = $this->bash->executeBash(MERCURY_DIR, $command);
		$this->dataBase->logStep($releaseId, $command, $output);

		$prUrl = $this->bash->executeOpenPullRequest('mercury-macmini', 'Wikia123', $newReleaseBranch);
		$output = ['success' => '', 'error' => ''];
		if( strpos($prUrl, 'http') !== false) {
			$output['success'] = 'Pull request url successfully obtained';
		} else {
			$output['error'] = 'Pull request url was not obtained';
		}
		$this->dataBase->logStep($releaseId, 'Open pull request for ' . $newReleaseBranch, $output);
	}
}
