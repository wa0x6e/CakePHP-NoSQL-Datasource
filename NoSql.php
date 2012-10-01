<?php
/**
 * NoSql Datasource class
 *
 * NoSql Interface for others NoSql layers.
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
 * @version 	0.1
 * @license 	MIT License (http://www.opensource.org/licenses/mit-license.php)
 */



/**
 * NoSql Interface for others NoSql layers
 *
 * @package app.Vendor.NoSql
 */
class NoSql
{

/**
 * Array of references to all instanciated layers
 *
 * @var array
 */
	protected static $_sources = array();

/**
 * Return a reference to a nosql layer
 *
 * @param string $name Name of the nosql layer
 * @param mixed $args
 * @throws DataSourceNotFoundException when the datasource doesn't exists
 */
	public static function __callStatic($name, $args) {
		if (isset(self::$_sources[$name])) {
			return self::$_sources[$name]->getInstance();
		}

		try {
			$className = $name . 'Source';
			self::load($className);
			$source = $className::getInstance();
			self::$_sources[$name] = $source;
			return $source;
		} catch(DataSourceNotFoundException $e) {
			throw $e;
		}
	}


/**
 * Return logs from all NoSql datasource
 *
 * @return array
 */
	public static function getLogs() {
		$logs = array();
		foreach (self::$_sources as $name => $source) {
			$logs[$name] = $source->getLogs();
		}
		return $logs;
	}

/**
 * Instanciate a nosql layer
 *
 * All nosql layers files must be in Vendor/NoSql/
 *
 * @param string $class Name of the nosql layer
 * @throws DataSourceNotFoundException when the nosql layer class is not found
 */
	public static function load($class) {
		$path = APP . 'Vendor' . DS . 'NoSql' . DS . $class . '.php';
		if (file_exists($path)) {
			include_once $path;
		}
		else throw new DataSourceNotFoundException('Unable to load ' . str_replace('Source', '', $class) . ' datasource');
	}
}