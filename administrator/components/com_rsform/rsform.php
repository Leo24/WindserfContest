<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

// ACL Check
$user = JFactory::getUser();
if (!$user->authorise('core.manage', 'com_rsform'))
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));

// Require the base controller
require_once JPATH_COMPONENT.'/controller.php';
require_once JPATH_COMPONENT.'/helpers/rsform.php';
require_once JPATH_COMPONENT.'/helpers/adapter.php';

RSFormProHelper::readConfig();

// See if this is a request for a specific controller
$controller 		= JRequest::getWord('controller');
$controller_exists  = false;
$task				= JRequest::getCmd('task');

if (!$controller && strpos($task, '.'))
	list($controller, $controller_task) = explode('.', $task, 2);
	
if (!empty($controller) && file_exists(JPATH_COMPONENT.'/controllers/'.$controller.'.php'))
{
	require_once JPATH_COMPONENT.'/controllers/'.$controller.'.php';
	
	$controller 	   = 'RSFormController'.$controller;
	$RSFormController  = new $controller();
	$controller_exists = true;
}
else
	$RSFormController = new RSFormController();

// trigger oninit
$mainframe = JFactory::getApplication();
$mainframe->triggerEvent('rsfp_bk_onInit');

// execute task
if ($controller_exists && !empty($controller_task))
{	
	$controller_task = preg_replace('/[^A-Z_]/i', '', $controller_task);
	$RSFormController->execute($controller_task);
}
else
	$RSFormController->execute(JRequest::getWord('task'));

// Redirect if set
$RSFormController->redirect();