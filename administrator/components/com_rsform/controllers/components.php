<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class RSFormControllerComponents extends RSFormController
{
	function __construct()
	{
		parent::__construct();
		
		$this->registerTask('apply', 	 'save');
		$this->registerTask('new', 	 	 'add');
		$this->registerTask('publish',   'changestatus');
		$this->registerTask('unpublish', 'changestatus');
		
		$this->registerTask('setrequired',   'changerequired');
		$this->registerTask('unsetrequired', 'changerequired');
		
		$this->_db = JFactory::getDBO();
	}
	
	function save()
	{
		$db = JFactory::getDBO();
		
		$componentType 	   = JRequest::getInt('COMPONENTTYPE');
		$componentIdToEdit = JRequest::getInt('componentIdToEdit');
		$formId 		   = JRequest::getInt('formId');
		
		$params = JRequest::getVar('param', array(), 'post', 'none', JREQUEST_ALLOWRAW);
		$params['EMAILATTACH'] = !empty($params['EMAILATTACH']) ? implode(',',$params['EMAILATTACH']) : '';
		
		$just_added = false;
		if ($componentIdToEdit < 1)
		{
			$db->setQuery("SELECT MAX(`Order`)+1 AS MO FROM #__rsform_components WHERE FormId='".$formId."'");
			$nextOrder = $db->loadResult();
			
			$db->setQuery("INSERT INTO #__rsform_components SET FormId='".$formId."', ComponentTypeId='".$componentType."', `Order`='".$nextOrder."'");
			$db->execute();
			$componentIdToEdit = $db->insertid();
			$just_added = true;
		}
		
		$model = $this->getModel('forms');
		$lang  = $model->getLang();
		
		if (!$just_added && isset($params['ITEMS'])) {
			$db->setQuery("SELECT cd.* FROM #__rsform_condition_details cd LEFT JOIN #__rsform_conditions c ON (cd.condition_id=c.id) WHERE cd.component_id='".$componentIdToEdit."' AND c.lang_code=".$db->quote($lang));
			if ($conditions = $db->loadObjectList()) {
				$data 		= RSFormProHelper::getComponentProperties($componentIdToEdit);
				$oldvalues 	= RSFormProHelper::explode(RSFormProHelper::isCode($data['ITEMS']));
				$newvalues 	= RSFormProHelper::explode(RSFormProHelper::isCode($params['ITEMS']));
				
				foreach ($oldvalues as $i => $oldvalue) {
					$oldvalue = reset(explode('|', $oldvalue, 2));
					$oldvalue = str_replace(array('[c]', '[g]'), '', $oldvalue);
					
					$oldvalues[$i] = $oldvalue;
				}
				
				foreach ($newvalues as $i => $newvalue) {
					$newvalue = reset(explode('|', $newvalue, 2));
					$newvalue = str_replace(array('[c]', '[g]'), '', $newvalue);
					
					$newvalues[$i] = $newvalue;
				}
				
				foreach ($conditions as $condition) {
					$pos = array_search($condition->value, $oldvalues);
					if ($pos !== false && isset($newvalues[$pos])) {
						$newvalue = $newvalues[$pos];
						if ($condition->value != $newvalue) {
							$db->setQuery("UPDATE #__rsform_condition_details SET `value`=".$db->quote($newvalue)." WHERE id='".$condition->id."'");
							$db->execute();
						}
					}
				}
			}
		}
		
		array_walk($params, array('RSFormProHelper', 'escapeArray'));
		if ($model->_form->Lang != $lang)
			$model->saveFormPropertyTranslation($formId, $componentIdToEdit, $params, $lang, $just_added);
		
		if ($componentIdToEdit > 0)
		{
			$db->setQuery("SELECT PropertyName FROM #__rsform_properties WHERE ComponentId='".$componentIdToEdit."' AND PropertyName IN ('".implode("','", array_keys($params))."')");
			$properties = $db->loadColumn();
			
			foreach ($params as $key => $val)
			{
				if (in_array($key, $properties))
					$db->setQuery("UPDATE #__rsform_properties SET PropertyValue='".$val."' WHERE PropertyName='".$key."' AND ComponentId='".$componentIdToEdit."'");
				else
					$db->setQuery("INSERT INTO #__rsform_properties SET PropertyValue='".$val."', PropertyName='".$key."', ComponentId='".$componentIdToEdit."'");
				
				$db->execute();
			}
		}
		
		$link = 'index.php?option=com_rsform&task=forms.edit&formId='.$formId;
		if (JRequest::getVar('tmpl') == 'component')
			$link .= '&tmpl=component';
		
		$this->setRedirect($link);
	}
	
	function saveOrdering()
	{
		$db = JFactory::getDBO();
		$post = JRequest::get('post');
		foreach ($post as $key => $val)
		{
			$key = (int) str_replace('cid_', '', $key);
			$val = (int) $val;
			if (empty($key)) continue;
			
			$db->setQuery("UPDATE #__rsform_components SET `Order`='".$val."' WHERE ComponentId='".$key."'");
			$db->execute();
		}
		
		echo 'Ok';
		
		exit();
	}
	
	function validateName()
	{
		$componentName = trim(JRequest::getVar('componentName', ''));
		if (preg_match('#([^a-zA-Z0-9_ ])#', $componentName) || empty($componentName) || $componentName == 'elements')
		{
			echo '0|'.JText::_('RSFP_UNIQUE_NAME_MSG');
			exit();
		}
		
		//on file upload component, check destination
		$componentType = JRequest::getInt('componentType');
		if ($componentType == 9)
		{
			$destination = JRequest::getVar('destination');
			if (empty($destination))
			{
				echo '2|'.JText::_('RSFP_ERROR_DESTINATION_MSG');
				exit();
			}
			if(!is_dir($destination))
			{
				echo '2|'.JText::_('RSFP_ERROR_DESTINATION_MSG');
				exit();
			}
			if(!is_writable($destination))
			{
				echo '2|'.JText::_('RSFP_ERROR_DESTINATION_WRITABLE_MSG');
				exit();
			}
		}
		
		if ($componentType == 6)
		{
			$mindate = JRequest::getVar('mindate');
			$maxdate = JRequest::getVar('maxdate');
			if ($mindate && $maxdate && @strtotime($mindate) > @strtotime($maxdate))
			{
				echo '2|'.JText::_('RSFP_CALENDAR_DATES_ERROR_MSG');
				exit();
			}
		}
		
		$currentComponentId = JRequest::getInt('currentComponentId');
		$componentId		= JRequest::getInt('componentId');
		$formId				= JRequest::getInt('formId');
		
		$exists = RSFormProHelper::componentNameExists($componentName, $formId, $currentComponentId);
		if ($exists)
			echo '0|'.JText::_('RSFP_UNIQUE_NAME_MSG');
		else
			echo 'Ok';

		exit();
	}
	
	function display($cachable = false, $urlparams = false)
	{
		JRequest::setVar('view', 	'formajax');
		JRequest::setVar('layout', 	'component');
		JRequest::setVar('format', 	'raw');
		
		parent::display($cachable, $urlparams);
	}
	
	function copyProcess()
	{
		$toFormId 	= JRequest::getInt('toFormId');
		$cids 		= JRequest::getVar('cid');
		JArrayHelper::toInteger($cids, array());
		foreach ($cids as $cid)
			RSFormProHelper::copyComponent($cid, $toFormId);
		
		$this->setRedirect('index.php?option=com_rsform&task=forms.edit&formId='.$toFormId, JText::sprintf('RSFP_COMPONENTS_COPIED', count($cids)));
	}
	
	function copy()
	{
		$formId = JRequest::getInt('formId');
		$db = JFactory::getDBO();
		$db->setQuery("SELECT FormId FROM #__rsform_forms WHERE FormId != '".$formId."'");
		if (!$db->loadResult())
			return $this->setRedirect('index.php?option=com_rsform&task=forms.edit&formId='.$formId, JText::_('RSFP_NEED_MORE_FORMS'));
		
		JRequest::setVar('view', 'forms');
		JRequest::setVar('layout', 'component_copy');
		
		parent::display();
	}
	
	function copyCancel()
	{
		$formId = JRequest::getInt('formId');
		$this->setRedirect('index.php?option=com_rsform&task=forms.edit&formId='.$formId);
	}
	
	function duplicate()
	{
		$formId = JRequest::getInt('formId');
		$cids 	= JRequest::getVar('cid');
		
		JArrayHelper::toInteger($cids, array());
		foreach ($cids as $cid)
			RSFormProHelper::copyComponent($cid, $formId);
		
		$this->setRedirect('index.php?option=com_rsform&task=forms.edit&formId='.$formId, JText::sprintf('RSFP_COMPONENTS_COPIED', count($cids)));
	}
	
	function changeStatus()
	{
		$model = $this->getModel('formajax');
		$model->componentsChangeStatus();
		$componentId = $model->getComponentId();
		
		if (is_array($componentId))
		{
			$formId = JRequest::getInt('formId');
			
			$task = $this->getTask();
			$msg = 'RSFP_ITEMS_UNPUBLISHED';
			if ($task == 'publish')
				$msg = 'RSFP_ITEMS_PUBLISHED';
			
			$this->setRedirect('index.php?option=com_rsform&task=forms.edit&formId='.$formId, JText::sprintf($msg, count($componentId)));
		}
		// Ajax request
		else
		{
			JRequest::setVar('view', 'formajax');
			JRequest::setVar('layout', 'component_published');
			JRequest::setVar('format', 'raw');
		
			parent::display();
		}
	}
	
	function changeRequired()
	{
		$model = $this->getModel('formajax');
		$model->componentsChangeRequired();
		$componentId = $model->getComponentId();
		
		JRequest::setVar('view', 'formajax');
		JRequest::setVar('layout', 'component_required');
		JRequest::setVar('format', 'raw');
		
		parent::display();
	}
	
	function remove()
	{
		$formId = JRequest::getInt('formId');
		$ajax 	= JRequest::getInt('ajax');
		$cids 	= JRequest::getVar('cid', array());
		$db 	= JFactory::getDBO();
		
		JArrayHelper::toInteger($cids);
		if (!empty($cids))
		{
			$db->setQuery("DELETE FROM #__rsform_components WHERE ComponentId IN (".implode(',', $cids).")");
			$db->execute();
			$db->setQuery("DELETE FROM #__rsform_properties WHERE ComponentId IN (".implode(',', $cids).")");
			$db->execute();
			foreach ($cids as $cid)
			{
				$db->setQuery("DELETE FROM #__rsform_translations WHERE reference_id LIKE '".$cid.".%'");
				$db->execute();
			}
		}
		
		$db->setQuery("SELECT ComponentId FROM #__rsform_components WHERE FormId='".$formId."' ORDER BY `Order`");
		$components = $db->loadAssocList();
		$i = 1;
		foreach ($components as $r)
		{
			$db->setQuery("UPDATE #__rsform_components SET `Order`='".$i."' WHERE ComponentId='".$r['ComponentId']."'");
			$db->execute();
			$i++;
		}
		
		if ($ajax)
		{
			$model = $this->getModel('forms');			
			if (!$model->getHasSubmitButton())
				echo 'NOSUBMIT';
			
			exit();
		}
		
		$this->setRedirect('index.php?option=com_rsform&task=forms.edit&formId='.$formId, JText::sprintf('ITEMS REMOVED', count($cids)));
	}
}