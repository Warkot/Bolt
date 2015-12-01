<?php

class DataBase {

	public $pdo;
	private $dsn = DB_DSN;
	private $username = DB_USERNAME;
	private $password = DB_PASSWORD;

	public function __construct() {
		try {
			$this->pdo = new PDO( $this->dsn, $this->username, $this->password );
			$this->pdo->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ );
		} catch( PDOException $e ) {
			throw new Exception( 'Connection error' );
		}
	}

	public function startRelease() {
		$statement = 'INSERT INTO Releases (status) VALUES (:status)';
		$pdoStatement = $this->pdo->prepare($statement);
		$pdoStatement->bindValue(':status', 'init');
		if(!$pdoStatement->execute()) {
			throw new Exception( 'Statement error' );
		}
		return $this->pdo->lastInsertId();
	}

	public function logStep($releaseId, $name, $output, $exitCode = null) {
		if($exitCode === null) {
			$exitCode = $output['exitCode'];
		}

		if($exitCode === '0' || $exitCode === 0) {
			$status = 'OK';
			$errorMessage = '';
		} else {
			$status = 'ERROR';
			empty($output['error']) ? $errorMessage = $output['success'] : $errorMessage = $output['error'];
		}

		$status .= " Code($exitCode)";

		$statement = 'INSERT INTO Steps (rId, name, errorMessage, status) VALUES (:rId, :name, :errorMessage, :status)';
		$pdoStatement = $this->pdo->prepare($statement);
		$pdoStatement->bindValue(':rId', $releaseId);
		$pdoStatement->bindValue(':name', $name);
		$pdoStatement->bindValue(':errorMessage', $errorMessage);
		$pdoStatement->bindValue(':status', $status);

		if(!$pdoStatement->execute()) {
			throw new Exception( 'Statement error' );
		}
	}
}
