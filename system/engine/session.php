<?php

namespace RevenantBlue;

class _SessionHandler extends Db implements \SessionHandlerInterface {
	
	private $sessionsTable;
	private $sessLifeTime = 84600;
	private $sessionStartTime;
	private $lastActivity;

	public function __construct() {
		$this->sessionsTable = PREFIX . 'sessions';
		try {
			if(!self::$dbh) $this->connect();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function open($sessionPath, $sessionName) {
		return true;
	}
	public function close() {
		return true;
	}
	public function read($sessionId) {
		// Run the garbage collector before reading session information
		try {
			$stmt = self::$dbh->prepare("SELECT * FROM $this->sessionsTable WHERE session_id = :sessionId");
			$stmt->bindParam(':sessionId', $sessionId, PDO::PARAM_STR);
			$stmt->execute();
			$sessionRows = $stmt->rowCount();
			if($sessionRows == 0) return '';
			if($sessionRows > 0) {
				$sessionResult = $stmt->fetch(PDO::FETCH_ASSOC);
				$sessionData = unserialize($sessionResult['session_data']);
				$this->sessionStartTime = $sessionResult['session_strt'];
				return $sessionData;
			} else {
				return '';
			}
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function write($sessionId, $sessionData) {
		if(!isset($this->sessionStartTime)) $this->sessionStartTime = time();
		$lastActivity = time();
		$this->lastActivity = $lastActivity;
		$sessionData = serialize($sessionData);
		try {
			$stmt = self::$dbh->prepare("SELECT * FROM $this->sessionsTable WHERE session_id = :sessionId");
			$stmt->bindParam(':sessionId', $sessionId, PDO::PARAM_STR);
			$stmt->execute();
			$sessionExists = $stmt->rowCount();
			$session = $stmt->fetch(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
		try {
			if($sessionExists == 0) {
				$stmt2 = self::$dbh->prepare(
					"INSERT INTO $this->sessionsTable
						(session_id, last_activity, session_strt, session_data)
					 VALUES (:sessionId, :lastActivity, :sessionStrt, :sessionData)"
				);
				$stmt2->bindParam(':sessionId', $sessionId, PDO::PARAM_STR);
				$stmt2->bindParam(':sessionStrt', $this->sessionStartTime, PDO::PARAM_INT);
				$stmt2->bindParam(':lastActivity', $lastActivity, PDO::PARAM_INT);
				$stmt2->bindParam(':sessionData', $sessionData, PDO::PARAM_STR);
				$stmt2->execute();
				return true;
			} else {
				if(empty($session['userId']) && !empty($_SESSION['userId'])) {
					$stmt2 = self::$dbh->prepare(
						"UPDATE $this->sessionsTable SET
							last_activity = :lastActivity
						  , session_data  = :sessionData
						  , user_id       = :userId
						 WHERE session_id = :sessionId"
					);
					$stmt2->bindParam(':lastActivity', $lastActivity, PDO::PARAM_INT);
					$stmt2->bindParam(':sessionData', $sessionData, PDO::PARAM_STR);
					$stmt2->bindParam(':sessionId', $sessionId, PDO::PARAM_STR);
					$stmt2->bindParam(':userId', $_SESSION['userId'], PDO::PARAM_INT);
				} else {
					$stmt2 = self::$dbh->prepare(
						"UPDATE $this->sessionsTable SET last_activity = :lastActivity, session_data = :sessionData 
						 WHERE session_id = :sessionId"
					);
					$stmt2->bindParam(':lastActivity', $lastActivity, PDO::PARAM_INT);
					$stmt2->bindParam(':sessionData', $sessionData, PDO::PARAM_STR);
					$stmt2->bindParam(':sessionId', $sessionId, PDO::PARAM_STR);
				}
				$stmt2->execute();
				return '';
			}
		} catch(PDOException $e) {
			$this->errorLog($e);
			return false;
		}
	}
	public function gc($maxlifetime) {
		$sessionExpTest = time() - $this->sessLifeTime;
		try {
			$stmt = self::$dbh->prepare("DELETE FROM $this->sessionsTable WHERE last_activity < :sessionExpTest");
			$stmt->bindParam(':sessionExpTest', $sessionExpTest, PDO::PARAM_INT);
			$stmt->execute();
			$sessionsCleaned = $stmt->rowCount();
			if($sessionsCleaned == 0) {
				return false;
			} else {
				return true;
			}
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function destroy($sessionId) {
		try {
			$stmt = self::$dbh->prepare("DELETE FROM $this->sessionsTable WHERE session_id = :sessionId");
			$stmt->bindParam(':sessionId', $sessionId, PDO::PARAM_STR);
			$stmt->execute();
			$rowsDestroyed = $stmt->rowCount();
			if($rowsDestroyed == 0) {
				return false;
			} else {
				return true;
			}
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
}

class RedisSessionHandler extends RedisConnection implements \SessionHandlerInterface {
	
	public $ttl = 259200;

	protected $prefix;
	protected $globalSettings;

	public function __construct() {

		global $globalSettings;
		
		if(empty($this->ttl) && $this->ttl !== 'none') {
			$this->ttl = (int)$globalSettings['session_ttl']['value'];
		}
		
		$this->prefix = PREFIX . 'session:';
	}

	public function open($savePath, $sessionName) {
		// No action necessary because connection is injected
		// in constructor and arguments are not applicable.
	}

	public function close() {
		//self::$rdh = null;
		//unset(self::$rdh);
	}

	public function read($id) {
		if(REDIS_PHP_NODE_SESSION_SHARING) {
			// We need to backup the session because we have to use session_encode,
			// session decode does not take a varibale it only encodes the current session.
			$backupSession = $_SESSION;
			// Set the current session id.
			$id = $this->prefix . $id;
			// Set the session to equal the json_decoded value from the redis database.
			$_SESSION = json_decode(self::$rdh->get($id), true);
			self::$rdh->expire($id, $this->ttl);
			if(!empty($_SESSION) & isset($_SESSION)) {
				$sessData = session_encode();
				// Restore the original session.
				$_SESSION = $backupSession;
				return $sessData;
			} else {
				return FALSE;
			}
		} else {
			$id = $this->prefix . $id;
			$sessData = self::$rdh->get($id);
			self::$rdh->expire($id, $this->ttl);
			return $sessData;
		}

	}

	public function write($id, $data) {
		if(REDIS_PHP_NODE_SESSION_SHARING) {
			// Backup the original session since session_decode
			$backupSession = $_SESSION;
			// Decode the session data, session decode returns its decoded results to $_SESSION
			session_decode($data);
			// Set the session data to the decoded session stored in $_SESSION
			$data = $_SESSION;
			// Restore the original session.
			$_SESSION = $backupSession;
			$id = $this->prefix . $id;
			self::$rdh->set($id, json_encode($data));
			self::$rdh->expire($id, $this->ttl);
		} else {
			$id = $this->prefix . $id;
			self::$rdh->set($id, $data);
			self::$rdh->expire($id, $this->ttl);
		}

	}

	public function destroy($id) {
		self::$rdh->del($this->prefix . $id);
	}

	public function gc($maxLifetime) {
		// no action necessary because using EXPIRE
	}
}

class RedisSessionHandlerAdmin extends RedisConnection implements \SessionHandlerInterface {
	
	public $ttl = 259200;

	protected $prefix;
	protected $globalSettings;

	public function __construct() {

		global $globalSettings;
		
		if(empty($this->ttl) && $this->ttl !== 'none') {
			$this->ttl = (int)$globalSettings['session_ttl']['value'];
		}
		
		$this->prefix = PREFIX . 'session:';
	}

	public function open($savePath, $sessionName) {
		// No action necessary because connection is injected
		// in constructor and arguments are not applicable.
	}

	public function close() {
		//self::$rdh = null;
		//unset(self::$rdh);
	}

	public function read($id) {
		$id = $this->prefix . $id;
		$sessData = self::$rdh->get($id);
		self::$rdh->expire($id, $this->ttl);
		return $sessData;

	}

	public function write($id, $data) {
		$id = $this->prefix . $id;
		self::$rdh->set($id, $data);
		self::$rdh->expire($id, $this->ttl);
	}

	public function destroy($id) {
		self::$rdh->del($this->prefix . $id);
	}

	public function gc($maxLifetime) {
		// no action necessary because using EXPIRE
	}
}


