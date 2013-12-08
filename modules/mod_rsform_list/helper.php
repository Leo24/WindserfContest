<?php
/**
* @version 1.3.0
* @package RSform!Pro 1.3.0
* @copyright (C) 2007-2010 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class ModRSFormListHelper
{
	var $_form = null;
	var $_data = array();
	var $_total = 0;
	var $_query = '';
	var $_pagination = null;
	var $_db = null;
	
	var $formId = 1;
	var $_state;
	
	var $params;
	var $replacements;
	
	function getDate($date)
	{
		if (method_exists('RSFormProHelper', 'getDate'))
			return RSFormProHelper::getDate($date);
		
		return JHTML::_('date', $date, 'Y-m-d H:i:s');
	}
	
	function ModRSFormListHelper($params)
	{
		$this->params = $params;
		$this->formId = (int) $this->params->def('formId', 1);
		$this->_state = new JObject();
		
		$this->_db = JFactory::getDBO();
		$this->_query = $this->_buildQuery();
		
		// Get pagination request variables
		$limit = $this->params->def('limit', 30);
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		
		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
		$this->setState('mod_rsform_list.submissions.'.$this->formId.'.limit', $limit);
		$this->setState('mod_rsform_list.submissions.'.$this->formId.'.limitstart', $limitstart);
		
		$this->textareaFields = array();
	}
	
	function getForm()
	{
		if (empty($this->_form))
		{
			$this->_db->setQuery("SELECT * FROM #__rsform_forms WHERE FormId='".$this->formId."'");
			$this->_form = $this->_db->loadObject();
			
			$this->_form->MultipleSeparator = str_replace(array('\n', '\r', '\t'), array("\n", "\r", "\t"), $this->_form->MultipleSeparator);
		}
		
		return $this->_form;
	}
	
	function _buildQuery()
	{
		$query  = "SELECT SQL_CALC_FOUND_ROWS DISTINCT(sv.SubmissionId), s.* FROM #__rsform_submissions s";
		$query .= " LEFT JOIN #__rsform_submission_values sv ON (s.SubmissionId=sv.SubmissionId)";
		$query .= " WHERE s.FormId='".$this->formId."'";
		
		$confirmed = $this->params->get('show_confirmed', 0);
		if ($confirmed)
			$query .= " AND s.confirmed='1'";
		
		$lang = $this->params->get('lang', '');
		if ($lang)
			$query .= " AND s.Lang='".$this->_db->escape($lang)."'";
		
		$userId = $this->params->def('userId', 0);
		if ($userId == 'login')
		{
			$user = JFactory::getUser();
			if ($user->get('guest'))
				$query .= " AND 1>2";
			
			$query .= " AND s.UserId='".(int) $user->get('id')."'";
		}
		elseif ($userId == 0)
		{
			// Show all submissions
		}
		else
		{
			$userId = explode(',', $userId);
			JArrayHelper::toInteger($userId);
			
			$query .= " AND s.UserId IN (".implode(',', $userId).")";
		}
		
		$dir = $this->params->get('sort_submissions') ? 'ASC' : 'DESC';
		
		$query .= " ORDER BY s.DateSubmitted $dir";
		
		return $query;
	}
	
	function getPagination()
	{
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('mod_rsform_list.submissions.'.$this->formId.'.limitstart'), $this->getState('mod_rsform_list.submissions.'.$this->formId.'.limit'));
		}
		
		return $this->_pagination;
	}
	
	function getTotal()
	{
		return $this->_total;
	}
	
	function getSubmissions()
	{
		if (empty($this->_data))
		{
			$this->getComponents();

			$this->_db->setQuery("SET SQL_BIG_SELECTS=1");
			$this->_db->execute();
			
			$submissionIds = array();
			
			$this->_db->setQuery($this->_query, $this->getState('mod_rsform_list.submissions.'.$this->formId.'.limitstart'), $this->getState('mod_rsform_list.submissions.'.$this->formId.'.limit'));
			$results = $this->_db->loadObjectList();
			$this->_db->setQuery("SELECT FOUND_ROWS()");
			$this->_total = $this->_db->loadResult();
			foreach ($results as $result)
			{
				$submissionIds[] = $result->SubmissionId;
				
				$this->_data[$result->SubmissionId]['FormId'] = $result->FormId;
				$this->_data[$result->SubmissionId]['DateSubmitted'] = $this->getDate($result->DateSubmitted);
				
				$this->_data[$result->SubmissionId]['UserIp'] = $result->UserIp;
				$this->_data[$result->SubmissionId]['Username'] = $result->Username;
				$this->_data[$result->SubmissionId]['UserId'] = $result->UserId;
				$this->_data[$result->SubmissionId]['confirmed'] = $result->confirmed ? JText::_('RSFP_YES') : JText::_('RSFP_NO');
				$this->_data[$result->SubmissionId]['SubmissionValues'] = array();
			}
			
			$form = $this->getForm();
			
			if (!empty($submissionIds))
			{
				$this->_db->setQuery("SELECT * FROM `#__rsform_submission_values` WHERE `SubmissionId` IN (".implode(',',$submissionIds).")");
				$results = $this->_db->loadObjectList();
				
				$config = JFactory::getConfig();
				$secret = $config->get('secret');
				foreach ($results as $result)
				{
					// Check if this is an upload field
					if (in_array($result->FieldName, $this->uploadFields) && !empty($result->FieldValue))
					{
						$result->FilePath = $result->FieldValue;
						$result->FieldValue = '<a href="'.JURI::root().'index.php?option=com_rsform&amp;task=submissions.view.file&amp;hash='.md5($result->SubmissionId.$secret.$result->FieldName).'">'.basename($result->FieldValue).'</a>';
					}
					// Check if this is a multiple field
					elseif (in_array($result->FieldName, $this->multipleFields))
						$result->FieldValue = str_replace("\n", $form->MultipleSeparator, $result->FieldValue);
					elseif ($form->TextareaNewLines && in_array($result->FieldName, $this->textareaFields))
						$result->FieldValue = nl2br($result->FieldValue);
						
					$this->_data[$result->SubmissionId]['SubmissionValues'][$result->FieldName] = array('Value' => $result->FieldValue, 'Id' => $result->SubmissionValueId);
					if (in_array($result->FieldName, $this->uploadFields) && !empty($result->FieldValue))
					{
						$filepath = $result->FilePath;
						$filepath = str_replace(JPATH_SITE.DIRECTORY_SEPARATOR, JURI::root(), $filepath);
						$filepath = str_replace(array('\\', '\\/', '//\\'), '/', $filepath);
						
						$this->_data[$result->SubmissionId]['SubmissionValues'][$result->FieldName]['Path'] = $filepath;
					}
				}
			}
			unset($results);
		}
		
		return $this->_data;
	}
	
	function getReplacements($user_id=0)
	{
		$config  = JFactory::getConfig();
		$user    = JFactory::getUser((int) $user_id);
		$replace = array('{global:sitename}', '{global:siteurl}', '{global:userip}', '{global:userid}', '{global:username}', '{global:email}', '{/details}', '{/detailspdf}');
		$with 	 = array($config->get('sitename'), JURI::root(), isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '', $user->get('id'), $user->get('username'), $user->get('email'), '</a>', '</a>');
			
		$this->replacements = array($replace, $with);
		
		return $this->replacements;
	}
	
	function getComponents()
	{
		$this->_db->setQuery("SELECT c.ComponentTypeId, p.ComponentId, p.PropertyName, p.PropertyValue FROM #__rsform_components c LEFT JOIN #__rsform_properties p ON (c.ComponentId=p.ComponentId) WHERE c.FormId='".$this->formId."' AND c.Published='1' AND p.PropertyName IN ('NAME', 'WYSIWYG')");
		$components = $this->_db->loadObjectList();
		$this->uploadFields   = array();
		$this->multipleFields = array();
		$this->textareaFields = array();
		
		foreach ($components as $component)
		{
			// Upload fields
			if ($component->ComponentTypeId == 9)
			{
				$this->uploadFields[] = $component->PropertyValue;
			}
			// Multiple fields
			elseif (in_array($component->ComponentTypeId, array(3, 4)))
			{
				$this->multipleFields[] = $component->PropertyValue;
			}
			// Textarea fields
			elseif ($component->ComponentTypeId == 2)
			{
				if ($component->PropertyName == 'WYSIWYG' && $component->PropertyValue == 'NO')
					$this->textareaFields[] = $component->ComponentId;
			}
		}
		
		if (!empty($this->textareaFields))
		{
			$this->_db->setQuery("SELECT p.PropertyValue FROM #__rsform_components c LEFT JOIN #__rsform_properties p ON (c.ComponentId=p.ComponentId) WHERE c.ComponentId IN (".implode(',', $this->textareaFields).")");
			$this->textareaFields = $this->_db->loadColumn();
		}
	}
	
	function getHeaders()
	{
		$query  = "SELECT p.PropertyValue FROM #__rsform_components c";
		$query .= " LEFT JOIN #__rsform_properties p ON (c.ComponentId=p.ComponentId AND p.PropertyName='NAME')";
		$query .= " LEFT JOIN #__rsform_component_types ct ON (c.ComponentTypeId=ct.ComponentTypeId)";
		$query .= " WHERE c.FormId='".$this->formId."' AND c.Published='1'";
		
		$this->_db->setQuery($query);
		$headers = $this->_db->loadColumn();
		
		return $headers;
	}
	
	function getState($property)
	{
		return $this->_state->get($property);
	}
	
	function setState($property, $value)
	{
		return $this->_state->set($property, $value);
	}
}
?>