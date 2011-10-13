<?php
/**
 * Класс представляет сущность миграции
 *
 * PHP version 5
 *
 * @category PHP
 * @author   Eugene Kurbatov <eugene.kurbatov@gmail.com>
 * @version  Migration.php 27.05.11 17:38 evkur
 * @link     nolink
 */

class Migration
{
	/**
	 * @var int Id миграции
	 */
	public $id = null;

	/**
	 * @var string Врем создания в unixtime
	 */
	public $createTime = null;

	/**
	 * @var string Комментарий
	 */
	public $comment = null;
}
