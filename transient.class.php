<?php
/**
 * Transients (for webEdition)
 * [EN] Short-Description: Persistent Key/Value-Storage
 * [DE] Kurz-Beschreibung: Persistenter Schlüssel/Wert-Speicher
 * Copyright 2013 - Aurelian Hermand, aurel@hermand.de
 * Version 1.0.0 - 12.10.2013 - Initial Version
 */

/**
 * USAGE:
 *
 * $transient = new Transients();
 * $transient->set("VarName", "MyAwesomeValue", 60);
 * echo $transient->get("VarName");
 * $transient->removeExpired();
 */


/**
 * webEdition DB Usage:
 *
 * $db = new DB_WE(); // Or: $db = $GLOBALS['DB_WE'];
 * $sql = "SELECT ...";
 * $db->escape(); // we/include/we_classes/database/we_database_base.class.php
 * $db->query($sql); // getHash($query, $DB_WE, MYSQL_ASSOC)
 * while ( $db->next_record() ) { // returning true or false
 *	echo $db->f('cell');
 * }
 */


class Transients {

	private $db;
	private $table;

	public function __construct( /*...*/ ) {
		$args = func_get_args();
		$this->db = $GLOBALS['DB_WE']; //$db = new DB_WE(); | $db = $GLOBALS['DB_WE']; $db->query();
		$this->table = 'tblTransients';
		if ( $args[0]==true ) {
			$sql = "DROP TABLE `".$this->table."`";
			$this->db->query($sql);
		}
		$sql  = "CREATE TABLE IF NOT EXISTS `".$this->table."` ( ";
		$sql .= "`key` varchar(255) NOT NULL default '', ";
		$sql .= "`val` varchar(2000) NOT NULL default '', ";
		$sql .= "`expire` int(20) unsigned NOT NULL default '0', ";
		$sql .= "PRIMARY KEY  (`key`) ";
		$sql .= ") ENGINE=MyISAM  DEFAULT CHARSET=utf8";
		$this->db->query($sql);
	}

	public function set($key, $val, $expire) {
		$key = (string) $key;
		$expire = (int) time() + $expire;
		$sql  = "INSERT INTO `".$this->table."` (`key`, `val`, `expire`) ";
		$sql .= "VALUES ('".$this->escape($key)."', '".$this->escape($val)."', '".$this->escape($expire)."') ";
		$sql .= "ON DUPLICATE KEY UPDATE `key`='".$this->escape($key)."', `val`='".$this->escape($val)."', `expire`='".$this->escape($expire)."' ";
		$this->db->query($sql);
	}

	public function get($key) {
		$sql = "SELECT `key`, `val`, `expire` FROM `".$this->table."` WHERE `key`='".$this->escape($key)."' AND `expire`>='".time()."'";
		$result = $this->db->query($sql);
		if ( $this->db->next_record() ) {
			return $this->db->f('val');
		}
		return null;
	}
	
	public function removeExpired() {
		$sql = "DELETE FROM `".$this->table."` WHERE `expire`<'".time()."'";
		$this->db->query($sql);
	}

	private function escape($unsafe) {
		return $this->db->escape($unsafe);
	}

}

?>
