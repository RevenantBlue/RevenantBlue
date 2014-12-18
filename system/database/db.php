<?php

namespace RevenantBlue;
use \PDO;

class Db {

	static protected $dbh = FALSE;
	
	protected function connect($adminUser = FALSE) {
		try {
			// If connecting with a user with admin privileges else use the standard access privileges.
			if($adminUser === TRUE) {
				if(DEVELOPMENT_ENVIRONMENT === TRUE) {
					self::$dbh = new PDO(
						DB_CONN_LOCAL,
						DB_USER_ADMIN,
						DB_PASS_ADMIN,
						array(
							PDO::MYSQL_ATTR_FOUND_ROWS => TRUE // Allows PDO's rowCount() function to return true even if no rows were affected.
						)
					);
					self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				} elseif(DEVELOPMENT_ENVIRONMENT === FALSE) {
					self::$dbh = new PDO(
						DB_CONN,
						DB_USER_ADMIN,
						DB_PASS_ADMIN, 
						array(
							PDO::MYSQL_ATTR_FOUND_ROWS => true
						)
					);
					self::$dbh->setAttribute(
						PDO::ATTR_ERRMODE,
						PDO::ERRMODE_EXCEPTION
					);
				}	
			} else {
				if(DEVELOPMENT_ENVIRONMENT === TRUE) {
					self::$dbh = new PDO(
						DB_CONN_LOCAL,
						DB_USER,
						DB_PASS, 
						array(
							PDO::MYSQL_ATTR_FOUND_ROWS => true // Allows PDO's rowCount() function to return true even if no rows were affected.
						)
					);
					self::$dbh->setAttribute(
						PDO::ATTR_ERRMODE,
						PDO::ERRMODE_EXCEPTION
					);
				} elseif(DEVELOPMENT_ENVIRONMENT === FALSE) {
					self::$dbh = new PDO(
						DB_CONN,
						DB_USER,
						DB_PASS,
						array(
							PDO::MYSQL_ATTR_FOUND_ROWS => true
						)
					);
					self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				}
			}
		} catch(PDOException $e) {
			$this->errorLog($e);
			return $e;
		}
	}
	
	protected function errorLog($msg) {
		
		$error =  "Date: " . date("m-d-Y H:i:s", time()) . "\n";
		$error .= "Error:  "  . $msg->getMessage() . "\n" . 
				  "File:  " . $msg->getFile() . "\n" . 
				  "Line:  " . $msg->getLine() . "\n" . 
				  "Code:  " . $msg->getCode() . "\n" . 
				  "Trace:  " . $msg->getTraceAsString() . "\n";
		if(isset($_SERVER['HTTP_HOST'])) {
			$error .= "Host: " . $_SERVER['HTTP_HOST'] . "\n";
			$error .= "Client: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
			$error .= "Client IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
			$error .= "Request URI: " . $_SERVER['REQUEST_URI'];
		}
		$error .= "\n\n\n"; 
		error_log($error, 3, DIR_LOGS . "error.log");
	}
	
	private function __clone() {
		//Make __clone private to prevent cloning of the instance.
	}
}
