<?php
/*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7541 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (file_exists(dirname(__FILE__).'/../config/settings.inc.php'))
	include_once(dirname(__FILE__).'/../config/settings.inc.php');
//include_once(dirname(__FILE__).'/../classes/MongoCon.php');

/*TODO: You may find some of the mysql commands , that can be changed one by one in future*/
abstract class MongoDbApiCore
{
	/** @var string Server (eg. localhost) */
	protected $_server;

	/** @var string Database user (eg. root) */
	protected $_user;

	/** @var string Database password (eg. can be empty !) */
	protected $_password;

	/** @var string Database type (MongoDb) */
	protected $_type;

	/** @var string Database name */
	protected $_database;

	/** @var mixed Ressource link */
	protected $_link;

	/** @var mixed ? */
	protected $_collection;
	
	/** @var mixed Mongo cached result */
	protected $_result;

	/** @var mixed ? */
	protected static $_db;
	
	/** @var mixed Object instance for singleton */
	protected static $_instance = array();

	protected static $_servers = array(	
	array('server' => _MONGO_DB_SERVER_, 'user' => _MONGO_DB_USER_, 'password' => _MONGO_DB_PASSWD_, 'database' => _MONGO_DB_NAME_), /* MongoDB Master server */
	);
	
	protected $_lastQuery;
	protected $_lastCached;
	
	protected static $_idServer;

	/**
	 * Get Db object instance (Singleton)
	 *
	 * @param boolean $master Decides wether the connection to be returned by the master server or the slave server
	 * @return object Db instance
	 */
	public static function getInstance($master = 1)
	{
		if ($master OR ($nServers = sizeof(self::$_servers)) == 1)
			$idServer = 0;
		else
			$idServer = ($nServers > 2 AND ($id = ++self::$_idServer % (int)$nServers) !== 0) ? $id : 1;

		if (!isset(self::$_instance[$idServer]))
			self::$_instance[(int)($idServer)] = new MongoCon(self::$_servers[(int)($idServer)]['server'], self::$_servers[(int)($idServer)]['user'], self::$_servers[(int)($idServer)]['password'], self::$_servers[(int)($idServer)]['database']);
		//echo "<pre>"; print_r(self::$_instance[(int)($idServer)]); echo "</pre>";
		return self::$_instance[(int)($idServer)];
	}
	
	public function getRessource() { return $this->_link;}
	
	public function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * Build a Db object
	 */
	public function __construct($server, $user, $password, $database)
	{
		$this->_server = $server;
		$this->_user = $user;
		$this->_password = $password;
		$this->_type = _DB_TYPE_2;
		$this->_database = $database;

		$this->connect();
	}

	/**
	 * Filter SQL query within a blacklist
	 *
	 * @param string $table Table where insert/update data
	 * @param string $values Data to insert/update
	 * @param string $type INSERT or UPDATE
	 * @param string $where WHERE clause, only for UPDATE (optional)
	 * @param string $limit LIMIT clause (optional)
	 * @return mixed|boolean SQL query result
	 */
	public function	autoExecute($collection, $values, $type, $where = false, $limit = false, $use_cache = 1)
	{		 
		if (!sizeof($values))
			return true;
		
		if (strtoupper($type) == 'INSERT')
		{
			$this->_collection = $this->_db->selectCollection($collection);
			$this->_collection->insert($values);
		}
		elseif (strtoupper($type) == 'UPDATE')
		{
			$this->_collection = $this->_db->selectCollection($collection);
			return $this->_collection->update($where,$values);
		}
		
		return false;
	}


	/**
	 * Filter SQL query within a blacklist
	 *
	 * @param string $table Table where insert/update data
	 * @param string $values Data to insert/update
	 * @param string $type INSERT or UPDATE
	 * @param string $where WHERE clause, only for UPDATE (optional)
	 * @param string $limit LIMIT clause (optional)
	 * @return mixed|boolean SQL query result
	 */
	public function	autoExecuteWithNullValues($collection, $values, $type, $where = false, $limit = false)
	{
		if (!sizeof($values))
			return true;
		
		if (strtoupper($type) == 'INSERT'){
			$this->_collection = $this->_db->selectCollection($collection);
			$this->_collection->insert($values);
		}elseif (strtoupper($type) == 'UPDATE'){	
			$this->_collection = $this->_db->selectCollection($collection);
			$this->_collection->update($where,$values);
		}
		
		return false;
	}

	/*********************************************************
	 * ABSTRACT METHODS
	 *********************************************************/
	
	/**
	 * Open a connection
	 */
	abstract public function connect();

	/**
	 * Get the ID generated from the previous INSERT operation
	 */
	abstract public function Insert_ID();

	/**
	 * Get number of affected rows in previous databse operation
	 */
	abstract public function Affected_Rows();

	/**
	 * Gets the number of rows in a result
	 */
	abstract public function NumRows();

	/**
	 * Delete
	 */
	abstract public function delete ($table, $where = false, $limit = false, $use_cache = 1);
	/**
	 * Fetches a row from a result set
	 */
	abstract public function Execute ($collection, $query, $use_cache = 0);

	/**
	 * Fetches an array containing all of the rows from a result set
	 */
	abstract public function ExecuteS($collection, $query, $array = true, $use_cache = 0);
	
	/*
	 * Get next row for a query which doesn't return an array 
	 */
	abstract public function nextRow($result = false);
	
	/*
	 * return sql server version.
	 * used in Order.php to allow or not subquery in update
	 */
	abstract public function getServerVersion();

	/**
		 * Alias of MongoDbApi::getInstance()->ExecuteS
		 *
		 * @acces string query The query to execute
		 * @return array Array of line returned by MongoDB
		 */
	public static function s($query, $use_cache = 0)
	{
		return MongoDbApi::getInstance()->ExecuteS($query, true, $use_cache);
	}
	
	public static function ps($query, $use_cache = 0)
	{
		$ret = MongoDbApi::s($query, $use_cache);
		p($ret);
		return $ret;
	}
	
	public static function ds($query, $use_cache = 0)
	{
		MongoDbApi::s($query, $use_cache);
		die();
	}

	/**
	 * getRow return an associative array containing the first row of the query
	 * This function automatically add "limit 1" to the query
	 * 
	 * @param mixed $query the select query (without "LIMIT 1")
	 * @param int $use_cache find it in cache first
	 * @return array associative array of (field=>value)
	 */
	abstract public function getRow($collection, $query);

	/**
	 * getValue return the first item of a select query.
	 * 
	 * @param mixed $query 
	 * @param int $use_cache 
	 * @return void
	 */
	abstract public function getValue($collection, $query, $use_cache = 0);

	/**
	 * Returns the text of the error message from previous database operation
	 */
	abstract public function getMsgError();
}



