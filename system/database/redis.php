<?php

namespace RevenantBlue;

class RedisConnection {
	static protected $rdh = FALSE;

	protected function connect() {
		
		try {
			self::$rdh = new \Redis;
			self::$rdh->connect(REDIS_HOST, REDIS_PORT);
		} catch(Exception $e) {
			$this->errorLog($e);
		}
	}

	protected function errorLog($msg) {
		$error =  "Date: " . date("m-d-Y H:i:s", time()) . "\n";
		$error .= "Error:  "  . $msg->getMessage() . "\n" .
				  "File:  " . $msg->getFile() . "\n" .
				  "Line:  " . $msg->getLine() . "\n" .
				  "Code:  " . $msg->getCode() . "\n" .
				  "Trace:  " . $msg->getTraceAsString() . "\n";
		$error .= "Host: " . $_SERVER['HTTP_HOST'] . "\n";
		$error .= "Client: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
		$error .= "Client IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
		$error .= "Request URI: " . $_SERVER['REQUEST_URI'];
		$error .= "\n\n\n";
		error_log($error, 3, DIR_LOGS . "errors.txt");
	}

	private function __clone() {
		//Make __clone private to prevent cloning of the instance.
	}
}

class RedisCommand extends RedisConnection {
	
	public function loadRedisHandler() {
		return self::$rdh;
	}
	
	public function exists($key) {
		if(!self::$rdh) $this->connect();
		$exists = self::$rdh->exists($key);
		return $exists;
	}
	
	public function get($key, $decode = FALSE, $assoc = TRUE) {
		if(!self::$rdh) $this->connect();
		$value = self::$rdh->get($key);
		
		if($decode === TRUE) {
			if($assoc === TRUE) {
				return json_decode($value, TRUE);
			} else {
				return json_decode($value);
			}
		}
		
		return $value;
	}
	
	public function set($key, $value) {
		if(!self::$rdh) $this->connect();
		$success = self::$rdh->set($key, $value);
		return $success;
	}
	
	public function del($key) {
		if(!self::$rdh) $this->connect();
		$success = self::$rdh->del($key);
		return $success;
	}
	
	public function expire($key, $ttl) {
		if(!self::$rdh) $this->connect();
		$success = self::$rdh->set($key, $ttl);
		return $success;
	}
	
	public function hexists($key, $field) {
		if(!self::$rdh) $this->connect();
		return self::$rdh->hexists($key, $field);
	}
	
	public function hget($key, $field) {
		if(!self::$rdh) $this->connect();
		return self::$rdh->hget($key, $field);
	}
	
	public function hset($key, $field, $value) {
		if(!self::$rdh) $this->connect();
		return self::$rdh->hset($key, $field, $value);
	}
}
