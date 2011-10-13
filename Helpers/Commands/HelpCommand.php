<?php
/**
 * Команда вывода справки
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  HelpCommand.php 27.05.11 17:32 evkur
 * @link     nolink
 */


require_once 'BaseCommand.php';
require_once realpath(dirname(__FILE__)) . '/../DirectoryHandler.php';

class HelpCommand extends BaseCommand
{
    private $command = null;

	function __construct($command = null)
    {
        $this->command = $command;
    }

	public function execute()
	{        
        if (!is_null($this->command))
        {
            $className = ucfirst($this->command).'Command';
            $filePath = realpath(dirname(__FILE__)) . "/{$className}.php";

            if (!is_file($filePath))
                throw new Exception('Unsupported command. Use <help>');

		    require_once $filePath;
            echo PHP_EOL . call_user_func_array(array($className, 'getDescription'), array()). PHP_EOL . PHP_EOL;
        }
        else
        {
            echo "DataBase Migration tool. Only MySQL supports.\n";
            echo "See example config file 'dbmigration.ini.example'.\n\n";
            echo "Commands:\n\n";

            $fileList = DirectoryHandler::fileList(realpath(dirname(__FILE__)), "/.*Command\.php/is");

            foreach ($fileList as $file)
            {
                require_once realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . $file;
                list($fileName, ) = explode(".", $file);

                $className = lcfirst($fileName);
                $rc = new ReflectionClass($className);

                if (!$rc->isAbstract() && !$rc->isInterface() && $rc->hasMethod("getDescription"))
                    echo call_user_func_array(array(lcfirst($fileName), 'getDescription'), array())
                        . PHP_EOL . PHP_EOL;
            }
        }
	}

	public function getDescription()
	{
		return "help - list of commands";
	}
}
