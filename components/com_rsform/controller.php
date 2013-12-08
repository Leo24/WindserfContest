<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class RSFormController extends JControllerLegacy
{
	var $_db;
	
	function __construct()
	{
		parent::__construct();
		
		$this->_db = JFactory::getDBO();
		
		$doc = JFactory::getDocument();
		$doc->addScript(JURI::root(true).'/components/com_rsform/assets/js/script.js');
	}
	
	function captcha()
	{
		require_once JPATH_SITE.'/components/com_rsform/helpers/captcha.php';
		
		$componentId = JRequest::getInt('componentId');
		$captcha = new RSFormProCaptcha($componentId);

		$session = JFactory::getSession();
		$session->set('com_rsform.captcha.'.$componentId, $captcha->getCaptcha());
		exit();
	}
	
	function plugin()
	{
		$mainframe = JFactory::getApplication();
		$mainframe->triggerEvent('rsfp_f_onSwitchTasks');
	}
	
	/* deprecated */
	function showForm()
	{
		
	}
	
	function submissionsViewFile()
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_rsform', JPATH_ADMINISTRATOR);
		
		$hash = JRequest::getCmd('hash');
		if (strlen($hash) != 32)
			return $this->setRedirect('index.php');
		
		$config = JFactory::getConfig();
		$secret = $config->get('secret');
			
		$this->_db->setQuery("SELECT * FROM #__rsform_submission_values WHERE MD5(CONCAT(SubmissionId,'".$this->_db->escape($secret)."',FieldName)) = '".$hash."'");
		$result = $this->_db->loadObject();
		
		// Not found
		if (empty($result))
			return $this->setRedirect('index.php');
		
		// Not an upload field
		$this->_db->setQuery("SELECT c.ComponentTypeId FROM #__rsform_properties p LEFT JOIN #__rsform_components c ON (p.ComponentId=c.ComponentId) WHERE p.PropertyName='NAME' AND p.PropertyValue='".$this->_db->escape($result->FieldName)."'");
		$type = $this->_db->loadResult();
		if ($type != 9)
			return $this->setRedirect('index.php', JText::_('RSFP_VIEW_FILE_NOT_UPLOAD'));
		
		jimport('joomla.filesystem.file');
		if (JFile::exists($result->FieldValue))
			RSFormProHelper::readFile($result->FieldValue);
		
		$this->setRedirect('index.php', JText::_('RSFP_VIEW_FILE_NOT_FOUND'));
	}
	
	function ajaxValidate()
	{
		$form = JRequest::getVar('form');
		$formId = (int) @$form['formId'];
		
		$this->_db->setQuery("SELECT ComponentId, ComponentTypeId FROM #__rsform_components WHERE `FormId`='".$formId."' AND `Published`='1' ORDER BY `Order`");
		$components = $this->_db->loadObjectList();
		
		$page = JRequest::getInt('page');
		if ($page)
		{
			$current_page = 1;
			foreach ($components as $i => $component)
			{
				if ($current_page != $page)
					unset($components[$i]);
				if ($component->ComponentTypeId == 41)
					$current_page++;
			}
		}
		
		$removeUploads   = array();
		$formComponents  = array();
		foreach ($components as $component)
		{
			$formComponents[] = $component->ComponentId;
			if ($component->ComponentTypeId == 9)
				$removeUploads[] = $component->ComponentId;
		}
		
		echo implode(',', $formComponents);
		
		echo "\n";
		
		$invalid = RSFormProHelper::validateForm($formId);
		
		//Trigger Event - onBeforeFormValidation
		$mainframe = JFactory::getApplication();
		$post = JRequest::get('post', JREQUEST_ALLOWRAW);
		$mainframe->triggerEvent('rsfp_f_onBeforeFormValidation', array(array('invalid'=>&$invalid, 'formId' => $formId, 'post' => &$post)));
		
		if (count($invalid))
		{
			foreach ($invalid as $i => $componentId)
				if (in_array($componentId, $removeUploads))
					unset($invalid[$i]);
			
			$invalidComponents = array_intersect($formComponents, $invalid);
			
			echo implode(',', $invalidComponents);
		}
		
		if (isset($invalidComponents))
		{
			echo "\n";
			
			$pages = RSFormProHelper::componentExists($formId, 41);
			$pages = count($pages);
			
			if ($pages && !$page)
			{
				$first = reset($invalidComponents);
				$current_page = 1;
				foreach ($components as $i => $component)
				{
					if ($component->ComponentId == $first)
						break;
					if ($component->ComponentTypeId == 41)
						$current_page++;
				}
				echo $current_page;
				
				echo "\n";
				
				echo $pages;
			}
		}
		
		jexit();
	}
	
	function confirm()
	{
		$db = JFactory::getDBO();
		$hash = JRequest::getVar('hash');
		
		$db->setQuery("SELECT `SubmissionId` FROM `#__rsform_submissions` WHERE MD5(CONCAT(`SubmissionId`,`FormId`,`DateSubmitted`)) = '".$db->escape($hash)."' ");
		$SubmissionId = $db->loadResult();
		
		if ($SubmissionId)
		{
			$db->setQuery("UPDATE `#__rsform_submissions` SET `confirmed` = 1 WHERE `SubmissionId` = '".$SubmissionId."' ");
			$db->execute();
			JError::raiseNotice('200',JText::_('RSFP_SUBMISSION_CONFIRMED'));
		}
		else
		{
			JError::raiseWarning('500',JText::_('RSFP_SUBMISSION_CONFIRMED_ERROR'));
			return;
		}
	}
}