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
 * @author      Wan Qi Chen <kami@kamisama.me>
 * @copyright   Copyright 2012, Wan Qi Chen <kami@kamisama.me>
 * @link 		https://github.com/kamisama
 * @package 	app.Vendor.NoSql
 * @version 	0.4
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
	protected static $_redisInstance = null;

/**
 * A singleton reference to this DataSource
 *
 * This is to prevent creating more than one connection for a same instance
 *
 * @var array
 */
	protected static $_singleton = null;

/**
 * Total time to execute all queries, in ms
 *
 * @since 0.3
 * @var int
 */
	protected static $_totalTime = 0;

/**
 * Total number of queries
 *
 * @since 0.3
 * @var int
 */
	protected static $_totalQueries = 0;

/**
 * Instanciate, and create a connection to the datasource server
 *
 * @throws RedisClassNotFoundException when the Redis API is not found
 */
	public function __construct() {
		if (!class_exists('Redis')) {
			$redisentClass = __DIR__ . DS . 'Vendor' . DS . 'redisent' . DS . 'Redis.php';

			if (file_exists($redisentClass)) {
				include_once $redisentClass;
				$Redis = new redisent\Redis('redis:://' . Configure::read('Redis.host') . ':' . Configure::read('Redis.port'));
			}
			else throw new RedisClassNotFoundException('API to Redis no found');
		} else {
			$Redis = new Redis();
			$Redis->connect(Configure::read('Redis.host'),  (int)Configure::read('Redis.port'));
		}

		self::$_redisInstance = $Redis;
	}

/**
 * Return a singleton instance of the datasource
 *
 * @return RedisSource A reference to this datasource
 */
	public static function getInstance() {
		if (!self::$_singleton) {
			self::$_singleton = new RedisSource();
		}
		return self::$_singleton;
	}

	public function __call($name, $args) {
		$t = microtime(true);
		$result = call_user_func_array(array(self::$_redisInstance, $name), $args);
		$queryTime = round((microtime(true) - $t) * 1000, 2);
		self::_logQuery(array(
			'command' => strtoupper($name) . ' ' . multi_implode(' ', $args),
			'time' => $queryTime
			)
		);
		self::$_totalTime += $queryTime;
		self::$_totalQueries++;
		return $result;
	}

/**
 * Log the query
 *
 * @param string $log
 */
	protected static function _logQuery($log) {
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
	public static function getLogs() {
		return array(
			'count' => self::$_totalQueries,
			'time' => self::$_totalTime,
			'logs' => self::$_logs
		);
	}

}

/**
 *
 * @package app.Vendor.NoSql
 */
class RedisClassNotFoundException extends Exception
{
}

/**
 * @source http://php.net/manual/en/function.implode.php
 *
 * @param string $glue
 * @param array $pieces
 * @return string
 */
function multi_implode($glue, $pieces) {
	$string = '';

	if (is_array($pieces)) {
		reset($pieces);
		while (list($key, $value) = each($pieces)) {
			$string .= $glue . multi_implode($glue, $value);
		}
	} else {
		return $pieces;
	}
	return trim($string, $glue);
}
