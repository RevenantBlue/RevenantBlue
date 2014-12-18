<?php
namespace RevenantBlue\Admin;
use RevenantBlue;
use \PDO;

class Plugins extends RevenantBlue\Db {
	
	public $whiteList = array('id', 'plugin_name', 'description', 'version');
	public $pluginsTable;

	public function __construct() {
		$this->pluginsTable = PREFIX . 'plugins';
	}
	
	public function loadPlugins() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->pluginsTable
			                             ORDER BY plugin_name ASC");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC); 
		} catch(PDOException $e) {
			$this->errorLog($e);	
		}
	}
}
