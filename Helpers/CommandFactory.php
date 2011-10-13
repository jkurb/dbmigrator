<?php
/**
 * Фабрика команд
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  CommandFactory.php 27.05.11 17:37 evkur
 * @link     nolink
 */

class CommandFactory
{
	/**
	 * Возвращает команду
	 *
	 * @static
	 * @throws Exception
	 * @param string $name Название кодманды
	 * @param array $params Параметры команды
	 *
	 * @return BaseCommand
	 */
	public static function create($name = null, $params = null)
	{
		if (!isset($params))
			$params = array();

		$className = ucfirst($name).'Command';
		$filePath = realpath(dirname(__FILE__)) . "/Commands/{$className}.php";

		if (!file_exists($filePath))
			throw new Exception('Unsupported command. Use <help>');

		require_once $filePath;
		$ref = new ReflectionClass($className);

		/**
		 * @var $refConstr ReflectionMethod
		 */
		$refConstr = $ref->getConstructor();

		if (count($params) < $refConstr->getNumberOfRequiredParameters())
			throw new Exception('Incorrect arguments. Use <help>');

		return $ref->newInstanceArgs($params);
	}
        

}
