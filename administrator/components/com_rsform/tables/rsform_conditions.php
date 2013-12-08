<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class TableRSForm_Conditions extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;
	
	var $form_id 		= null;
	var $action	 		= null;
	var $block 	 		= null;
	var $component_id	= null;
	var $condition 		= null;
	var $lang_code 		= null;
		
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function TableRSForm_Conditions(& $db)
	{
		parent::__construct('#__rsform_conditions', 'id', $db);
	}
}