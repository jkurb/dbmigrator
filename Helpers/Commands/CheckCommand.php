<?php
/**
 * Команда проверяет корректность составленной дельты
 * для двух миграций
 *
 * usage: dbmg mig1_uid mig2_uid config.ini
 *
 * PHP version 5
 *
 * @package
 * @author  Andrey Filippov <afi@i-loto.ru>
 */

require_once 'BaseCommand.php';
require_once realpath(dirname(__FILE__)) .'/../MigrationManager.php';

class CheckCommand extends BaseCommand
{

	/**
	 * Id младшей миграции
	 *
	 * @var string
	 */
	private $mig0 = null;

	/**
	 * Id старшей миграции
	 *
	 * @var string
	 */
	private $mig1 = null;

	/**
	 *
	 *
	 * @var MigrationManagerHelper
	 */
	private $mmh0 = null;


	/**
	 *
	 *
	 * @var MigrationManagerHelper
	 */
	private $mmh1 = null;

	/**
	 * КОнструктор
	 *
	 * @param string Id младшей миграции
	 * @param string $configPath Путь к файлу конфигурации
	 * @param string Id старшей миграции
	 *
	 * @return void
	 */
	public function __construct($mig0, $mig1, $configPath)
	{
		parent::__construct($configPath);
		$this->mig0 = $mig0;
		$this->mig1 = $mig1;


	}

	/**
	 * Выполнение команды
	 *
	 * @see BaseCommand::execute()
	 *
	 * @return void
	 */
	public function execute()
	{
		echo "\n-----------------------------------------------\n";
		echo "Start compare migration {$this->mig0} vs {$this->mig1}";
		echo "\n-----------------------------------------------\n";

		$storage = $this->config['migrationStorage'];

		$this->receiver->dbHelper->executeQuery("DROP DATABASE IF EXISTS `{$this->mig0}`");
		$this->receiver->dbHelper->executeQuery("DROP DATABASE IF EXISTS `{$this->mig1}`");

		$this->receiver->dbHelper->executeQuery("CREATE DATABASE `{$this->mig0}`");
		$this->receiver->dbHelper->executeQuery("CREATE DATABASE `{$this->mig1}`");

		$this->mmh0 = new MigrationManagerHelper(
			$this->config['host'],
			$this->config['user'],
			$this->config['pass'],
			$this->mig0
		);


		$this->mmh1 = new MigrationManagerHelper(
			$this->config['host'],
			$this->config['user'],
			$this->config['pass'],
			$this->mig1
		);

		$sqlFileSet = array('scheme.sql', 'data.sql', 'procedures.sql', 'triggers.sql');

		$host = $this->config ['host'];
		$user = $this->config ['user'];
		$pass = $this->config ['pass'];

		// установка первой базы
		foreach ($sqlFileSet as $file)
			$this->executeSQLFromFile($host, $user, $pass, $this->mig0, "{$storage}/{$this->mig0}/{$file}");

		// установка второй базы
		foreach ($sqlFileSet as $file)
			$this->executeSQLFromFile($host, $user, $pass, $this->mig1, "{$storage}/{$this->mig1}/{$file}");

		// на первую базу накатим delta.sql из старшей миграции
		$this->executeSQLFromFile($host, $user, $pass, $this->mig0, "{$storage}/{$this->mig1}/delta.sql");

		$tmp = sys_get_temp_dir();

		$tmp0 = $tmp . "/{$this->mig0}";
		$tmp1 = $tmp . "/{$this->mig1}";

		$this->removeDirectory($tmp0);
		$this->removeDirectory($tmp1);

		// дампы во временные каталоги для сравнения
		$this->mmh0->createDump($tmp0);
		$this->mmh1->createDump($tmp1);

		foreach ($sqlFileSet as $file)
		{
			$hash0 = md5_file($tmp0 . "/{$file}");
			$hash1 = md5_file($tmp1 . "/{$file}");

			// если дампы не равны - выводим diff
			if ($hash0 !== $hash1)
			{
				$this->getDiff("{$tmp0}/{$file}", "$tmp1/{$file}");
			}
		}

		echo "Check migration completed\n";
	}

	function removeDirectory($dir)
	{
		if ($objs = glob($dir ."/*"))
		{
			foreach ( $objs as $obj )
			{
				is_dir($obj) ? $this->removeDirectory($obj) : unlink($obj);
			}
		}
		rmdir($dir);
	}


	/**
	 * Выводит результат команды diff для дампов миграций
	 *
	 * @param string $file1
	 * @param string $file2
	 * @throws RuntimeException
	 *
	 * @return void
	 */
	public function getDiff($file1, $file2)
	{
		$retVal = null;
		$output = null;
		echo "\n-----------------------------------------------\n";
		echo " Dumps are not identical. Result of compare files {$file1} --> {$file2}";
		echo "\n-----------------------------------------------\n";
		$rr = exec("diff {$file1} {$file2} 2>&1 &", $output, $retVal);
		if ($retVal !==0)
			throw new RuntimeException($this->parseConsoleError($output));
		else
			echo $this->parseConsoleError($output) . "\n\n";
	}

	/**
	 * Загружает файл с SQL кодом напрямую в БД
	 *
	 * @param string $file путь к файлу с SQL кодом
	 *
	 * @return boolean|RuntimeException
	 */
	public function executeSQLFromFile($host, $user, $pass, $dbname, $file)
	{
		$retVal = null;
		$output = null;
		echo "Import {$file} to database '{$dbname}'\n";
		exec("mysql --host={$host} --password={$pass} -u {$user} {$dbname} < {$file} 2>&1", $output, $retVal);
		if ($retVal !==0)
			throw new RuntimeException($this->parseConsoleError($output));

		return true;
	}


	/**
	 * Дамп базы в файл
	 *
	 * @param string $host ДБ хост
	 * @param string $user ДБ юзер
	 * @param string $pass ДБ пароль
	 * @param string $dbname имя ДБ
	 * @param string $toFile имя файла с дампом
	 * @throws RuntimeException
	 *
	 * @return boolean
	 */
	public function dumpToFile($host, $user, $pass, $dbname, $toFile)
	{
		$retVal = null;
		$output = null;
		echo "Executing dump database '{$dbname}' to {$toFile}\n";

		exec("mysqldump --host={$host} --password={$pass} -u {$user} {$dbname} --skip-comments > {$toFile}", $output, $retVal);
		if ($retVal !==0)
			throw new RuntimeException($this->parseConsoleError($output));

		return true;
	}

	/**
	 * Преобразует список сообщений в строку
	 *
	 * @param array $array список сообщений
	 *
	 * @return string
	 */
	public function parseConsoleError($array)
	{
		return implode(PHP_EOL, $array);
	}

	public function getDescription()
	{
		return "check <mig1Uid> <mig2Uid> <configPath>  - Check whether delta between two adjacent migrations is correct\n"
            . "\t<mig1Uid> - first migration id\n"
            . "\t<mig2Uid> - second migration id";
	}
}
?>