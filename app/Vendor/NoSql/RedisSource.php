<?php
/**
 * Redis Datasource class
 *
 * Redis layer for the NoSql Datasource
 *
 * PHP 5
 * CakePHP 2
 *
 * Copyright (c) 2012, Wan Chen aka Kamisama
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 	Copyright (c) 2012, Wan Chen aka Kamisama
 * @link 		https://github.com/kamisama
 * @package 	app.Vendor.NoSql
 * @version 	0.1
 * @license 	MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Redis layer for the Nosql datasource
 *
 * @package app.Vendor.NoSql
 */
class RedisSource
{
	/**
	 * Log of queries executed by this DataSource
	 *
	 * @var array
	 */
	protected static $_logs = array();
	
	/**
	 * Maximum number of items in query log
	 *
	 * This is to prevent query log taking over too much memory.
	 *
	 * @var integer Maximum number of queries in the queries log.
	 */
	protected static $_maxLogs = 200;
	
	/**
	 * A reference to the physical connection of this DataSource
	 *
	 * @var array
	 */
	private static $__redisInstance = null;
	
	/**
	 * A singleton reference to this DataSource
	 *
	 * This is to prevent creating more than one connection for a same instance
	 *
	 * @var array
	 */
	private static $__singleton = null;
	
	
	/**
	 * Instanciate, and create a connection to the datasource server
	 */
	public function __construct()
	{
		$Redis = new Redis();
		$Redis->connect(Configure::read('Redis.host'), Configure::read('Redis.port'));
		self::$__redisInstance = $Redis;
	}
	
	
	/**
	 * Return a singleton instance of the datasource
	 *
	 * @return RedisSource A reference to this datasource
	 */
	public static function getInstance()
	{
		if (!self::$__singleton)
		{
			self::$__singleton = new RedisSource();
		}
		return self::$__singleton;
	}

	
	public function __call($name, $args)
	{
		self::_logQuery(array(strtoupper($name) . ' ' . implode(' ', $args)));
		return call_user_func_array(array(self::$__redisInstance, $name), $args);
	}
	
	
	/**
	 * Log the query
	 *
	 * @param string $log
	 */
	protected static function _logQuery($log)
	{
		self::$_logs[] = $log;
		if (count(self::$_logs) > self::$_maxLogs) {
			array_shift(self::$_logs);
		}
	}
	
	
	/**
	 * Return the logs
	 *
	 * @return array An array of queries
	 */
	public static function logs()
	{
		return self::$_logs;
	}
}