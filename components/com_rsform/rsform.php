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

$cache = JFactory::getCache('com_rsform');
$cache->clean();

// Require the base controller
require_once JPATH_COMPONENT.'/controller.php';
require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/rsform.php';

// See if this is a request for a specific controller
$controller = strtolower(JRequest::getWord('controller'));
// These are not controllers but legacy functions
if ($controller == 'functions' || $controller == 'adapter')
	$controller = '';
	
if (!empty($controller) && file_exists(JPATH_COMPONENT.'/controllers/'.$controller.'.php'))
{
	require_once(JPATH_COMPONENT.'/controllers/'.$controller.'.php');
	$controller = 'RSFormController'.$controller;
	$RSFormController = new $controller();
}
else
	$RSFormController = new RSFormController();

$RSFormController->execute(JRequest::getWord('task'));

// Redirect if set
$RSFormController->redirect();