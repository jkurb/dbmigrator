<?php
/**
 * Команда миграции к версии БД
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  GotoCommand.php 27.05.11 17:31 evkur
 * @link     nolink
 */


require_once 'BaseCommand.php';
require_once realpath(dirname(__FILE__)) . '/../MigrationManager.php';

class GotoCommand extends BaseCommand
{
	private $migrNum = null;
	private $forceMigrate = false;

	function __construct()
	{
		$numArgs = func_num_args();
        if ($numArgs < 2)
	        throw new Exception('Incorrect arguments. Use <help>');

        $args = func_get_args();

        $configPath = $args[count($args) - 1];
        unset($args[count($args) - 1]);
        parent::__construct($configPath);

        $this->migrNum = $args[0];

        // flag detected
        if ($numArgs == 3)
	        if ($args[1] == "-f")
	            $this->forceMigrate = true;
            else
	            throw new Exception('Incorrect arguments. Use <help>');
	}

	public function execute()
	{
		echo "Please, waiting...\n";
		if ($this->migrNum == 'head' && !$this->forceMigrate)
		{
			$this->receiver->gotoLastMigration($this->config['migrationStorage']);
			echo "Migration to HEAD was succeed\n";
		}
		else
		{
			if ($this->migrNum == "head")
				$this->migrNum = $this->receiver->getLastMigrationUidFromDiretories(
					$this->config['migrationStorage']
				);

			$this->receiver->gotoMigration(
				$this->config['migrationStorage'],
				$this->migrNum,
				$this->forceMigrate
			);

			echo "Migration to #{$this->migrNum} was succeed.";
			echo $this->forceMigrate ? " (Forced)\n" : "\n";
		}
	}

	public function getDescription()
	{
		return "goto <id|head> [-f] <configPath>  - migrate to version\n"
            . "\t<id> - migratoin id\n"
            . "\t<head> - migrate to last\n"
            . "\t[-f] - force migration";
	}
}
