<?php
/**
 * Команда показывает дельту по бинарным логам
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  ShowdeltaCommand.php 06.06.11 13:59 evkur
 * @link     nolink
 */

require_once 'BaseCommand.php';
require_once realpath(dirname(__FILE__)) . '/../MigrationManager.php';

class ShowdeltaCommand extends BaseCommand
{
	private $showUnique = false;

	function __construct()
	{
		$numArgs = func_num_args();
        if ($numArgs < 1)
	        throw new Exception('Incorrect arguments. Use <help>');

        $args = func_get_args();

        $configPath = $args[count($args) - 1];
        unset($args[count($args) - 1]);
        parent::__construct($configPath);

		if (isset($args[0]) && $args[0] == "-u")
			$this->showUnique = true;
	}

	public function execute()
	{
		echo $this->receiver->getDeltaByBinLog(
			$this->config['binaryLogPath'],
			$this->config['migrationStorage'],
			$this->showUnique
		);
	}

	public function getDescription()
	{
		return "showdelta [-u] <configPath> - show delta by mysql binlog\n"
            . "\t[-u] - show only unique queries";
	}
}
