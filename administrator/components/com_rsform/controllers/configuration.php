<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class RSFormControllerConfiguration extends RSFormController
{
	function __construct()
	{
		parent::__construct();
		
		$this->registerTask('apply', 'save');
		
		$this->_db = JFactory::getDBO();
	}

	function edit()
	{
		JRequest::setVar('view', 	'configuration');
		JRequest::setVar('layout', 	'default');
		
		parent::display();
	}
	
	function cancel()
	{
		$this->setRedirect('index.php?option=com_rsform');
	}
	
	function save()
	{
		$config = JRequest::getVar('rsformConfig', array(0), 'post');

		$db = JFactory::getDBO();
		foreach ($config as $name => $value)
		{
			$db->setQuery("UPDATE #__rsform_config SET SettingValue = '".$db->escape($value)."' WHERE SettingName = '".$db->escape($name)."'");
			$db->execute();
		}
		
		RSFormProHelper::readConfig(true);
		
		$task = $this->getTask();
		switch ($task)
		{
			case 'apply':
				$tabposition = JRequest::getInt('tabposition', 0);
				$link = 'index.php?option=com_rsform&task=configuration.edit&tabposition='.$tabposition;
			break;
			
			case 'save':
				$link = 'index.php?option=com_rsform';
			break;
		}
		
		$this->setRedirect($link, JText::_('RSFP_CONFIGURATION_SAVED'));
	}
}