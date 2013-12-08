<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class TableRSForm_Mappings extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;
	
	var $formId = null;
	var $connection = 0;
	var $host = null;
	var $port = 3306;
	var $username = null;
	var $password = null;
	var $database = null;
	var $method = 0;
	var $table = null;
	var $data = null;
	var $wheredata = null;
	var $extra = null;
	var $andor = null;
	var $ordering = null;
		
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function TableRSForm_Mappings(& $db)
	{
		parent::__construct('#__rsform_mappings', 'id', $db);
	}
}