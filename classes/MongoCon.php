<?php
/**
 * Description of Mongo
 *
 * @author gangadhar
 */

/*TODO: You may find some of the mysql commands , that can be changed one by one in future*/

class MongoConCore extends MongoDbApi
{
	public function connect()
	{
		if (!defined('_PS_DEBUG_MONGO_'))
			define('_PS_DEBUG_MONGO_', false);

        try {
            $this->_link = new MongoClient("mongodb://".$this->_server, array("username" => $this->_user, "password" => $this->_password));
        } catch (Exception $e) {
            die(Tools::displayError('Connection to MongoDB cannot be established!'));
        }
        
        if (is_object($this->_link))
		{
			if (!$this->set_db($this->_database))
				die(Tools::displayError('The database selection cannot be made.'));
		}
		else
			die(Tools::displayError('Link to database cannot be established.'));
		
		return $this->_link;
	}
	
	public function getServerVersion() {
		return MongoClient::VERSION;
	}
	
	/* do not remove, useful for some modules */
	public function set_db($db_name) {
		$this->_db = $this->_link->selectDB($db_name);
		return $this->_db;
	}
	
	public function disconnect()
	{
		if ($this->_link)
			$this->_link->close();
		$this->_link = false;
	}
	
	public function getRow($collection, $query)
	{
		$this->_collection = $this->_db->selectCollection($collection);
		$result = $this->_collection->findOne($query);
		if($result)
			return $result;
		return false;
	}

	public function getValue($collection, $query, $use_cache = 0)
	{
		$this->_collection = $this->_db->selectCollection($collection);
		$result = $this->_collection->findOne($query);
		if($result)
			return $result;
		return false;
	}
	
	public function Execute($collection, $query, $use_cache = 0)
	{
		$this->_collection = $this->_db->selectCollection($collection);
		$result = $this->_collection->find($query)->limit(1);
		if($result)
			return $result;
		return false;
	}
	
	/**
	 * ExecuteS return the result of $query as array, 
	 * or as mysqli_result if $array set to false
	 * 
	 * @param string $query query to execute
	 * @param boolean $array return an array instead of a mysql_result object
	 * @param int $use_cache if query has been already executed, use its result
	 * @return array or result object 
	 */
	public function ExecuteS($collection, $query, $array = true, $use_cache = 0)
	{
		$this->_collection = $this->_db->selectCollection($collection);
		$result = $this->_collection->find($query);
		if($result)
			return $result;
		return false;
	}

	public function nextRow($result = false)
	{
		return mysql_fetch_assoc($result ? $result : $this->_result);
	}
	
	public function delete($collection, $where = false, $limit = false, $use_cache = 0)
	{
		$this->_result = false;
		if ($this->_link)
		{
			$this->_collection = $this->_db->selectCollection($collection);
			$res = $this->_collection->remove($where? $where: '');
			return $res;
		}
			
		return false;
	}
	
	public function NumRows()
	{
		if (!$this->_lastCached AND $this->_link AND $this->_result)
		{
			$nrows = mysql_num_rows($this->_result);
			if (_PS_CACHE_ENABLED_)
				Cache::getInstance()->setNumRows(md5($this->_lastQuery), $nrows);
			return $nrows;
		}
		elseif (_PS_CACHE_ENABLED_ AND $this->_lastCached)
		{
			return Cache::getInstance()->getNumRows(md5($this->_lastQuery));
		}
	}
	
	public function Insert_ID()
	{
		if ($this->_link)
			return ObjectId($this->_link);
		return false;
	}
	
	public function Affected_Rows()
	{
		if ($this->_link)
			return $this->_link->count();
		return false;
	}

	protected function q($query, $use_cache = 1)
	{
		global $webservice_call;
		$this->_result = false;
		if ($this->_link)
		{
			$result =  mysql_query($query, $this->_link);
			$this->_lastQuery = $query;
			if ($webservice_call)
				$this->displayMySQLError($query);
			if ($use_cache AND _PS_CACHE_ENABLED_)
				Cache::getInstance()->deleteQuery($query);
			return $result;
		}
		return false;
	}
	
	/**
	 * Returns the text of the error message from previous Mongodb operation
	 *
	 * @acces public
	 * @return string error
	 */
	public function getMsgError($query = false)
	{
		return $this->_db->prevError();
	}

	public function getNumberError()
	{
		return mysql_errno($this->_link);
	}

	public function displayMySQLError($query = false)
	{
		global $webservice_call;
		if ($webservice_call && $this->_db->lastError())
		{
			WebserviceRequest::getInstance()->setError(500, '[SQL Error] '.$this->_db->lastError().'. Query was : '.$query, 97);

		}
		elseif (_PS_DEBUG_SQL_ AND $this->_db->lastError() AND !defined('PS_INSTALLATION_IN_PROGRESS'))
		{
			if ($query)
				die(Tools::displayError($this->_db->lastError().'<br /><br /><pre>'.$query.'</pre>'));
			die(Tools::displayError(($this->_db->lastError())));
		}
	}

}

?>
