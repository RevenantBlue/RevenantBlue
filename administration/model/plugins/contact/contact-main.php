<?php

class Contact extends Db {

	public  $whiteList = array('name', 'email', 'message', 'create_date');
	private $contactTable;

	public function __construct() {
		$this->contactTable = PREFIX . 'contact';
	}
	
	public function loadMessages($order, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->contactTable
			                             ORDER BY $order $sort");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteContact($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->contactTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
}
