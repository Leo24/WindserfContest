<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class RSFormProRestore
{
	var $archive = '';
	var $filetype = 'rsformbackup';
	var $overwrite = 0;
	var $cleanup = 1;
	var $installDir = '';
	var $installFile = '';
	
	var $version = '';
	var $revision = '';
	
	var $dbprefix = 'jos_';
	
	var $removeColumns = array();
	
	function RSFormProRestore($options = array())
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		
		$config = JFactory::getConfig();
		$this->dbprefix = $config->get('dbprefix');
		
		if (isset($options['filename']))
			$this->archive = $options['filename'];
		if (isset($options['filetype']))
			$this->filetype = $options['filetype'];
		if (isset($options['overwrite']))
			$this->overwrite = (int) $options['overwrite'];
		if (isset($options['cleanup']))
			$this->cleanup = (int) $options['cleanup'];
	}
	
	function process()
	{
		$this->installDir = JPATH_SITE.'/media/'.uniqid('rsinstall_');
		
		$adapter = JArchive::getAdapter('zip');
		if (!$adapter->extract($this->archive, $this->installDir))
			return false;
		
		return true;
	}
	
	function restore()
	{
		$this->installFile = $this->installDir.'/install.xml';
		if (!JFile::exists($this->installFile))
		{	
			$this->installFile = '';
			$this->cleanUp();
			JError::raiseWarning(500, JText::_('RSFP_RESTORE_NOINSTALL'));
			return false;
		}
		
		
		if (!$xml = simplexml_load_file($this->installFile))
		{
			$this->cleanUp();
			JError::raiseWarning(500, JText::_('RSFP_RESTORE_BADFILE'));
			return false;
		}
		
		$attr = $xml->attributes();
		$name = strtolower($xml->getName());
		$type = (string) $attr['type'];
		
		if ($name != 'rsinstall' || $type != 'rsformbackup')
		{
			$this->cleanUp();
			JError::raiseWarning(500, JText::_('RSFP_RESTORE_BADFILE'));
			return false;
		}
		
		$this->version  = (string) $xml->version;
		$this->revision = (string) $xml->revision;
		
		$tasks = $xml->tasks->task;
		if (!empty($tasks))
		{
			if ($this->overwrite)
			{
				$db = JFactory::getDBO();
				
				$db->setQuery("TRUNCATE TABLE #__rsform_forms");
				$db->execute();
				
				$db->setQuery("TRUNCATE TABLE #__rsform_components");
				$db->execute();
				
				$db->setQuery("TRUNCATE TABLE #__rsform_properties");
				$db->execute();
				
				$db->setQuery("TRUNCATE TABLE #__rsform_submissions");
				$db->execute();
				
				$db->setQuery("TRUNCATE TABLE #__rsform_submission_values");
				$db->execute();
				
				$db->setQuery("TRUNCATE TABLE #__rsform_translations");
				$db->execute();
				
				$db->setQuery("TRUNCATE TABLE #__rsform_conditions");
				$db->execute();
				
				$db->setQuery("TRUNCATE TABLE #__rsform_condition_details");
				$db->execute();
			}
			foreach ($tasks as $task) {
				$this->processTask($task);
			}
			
			$this->updateUploads();
		}
		
		$this->cleanUp();
		return true;
	}
	
	function _addColumnToRemove($name)
	{
		if (in_array($name, $this->removeColumns))
			return true;
		
		$this->removeColumns[] = $name;
		return;
	}
	
	function cleanUp()
	{
		$db = JFactory::getDBO();
		if (count($this->removeColumns))
			foreach ($this->removeColumns as $removeColumn)
			{
				$db->setQuery("ALTER TABLE `#__rsform_forms` DROP `".$db->escape($removeColumn)."`");
				$db->execute();
			}
		
		if ($this->cleanup)
			JFolder::delete($this->installDir);
	}
	
	function processTask($task)
	{
		$db = JFactory::getDBO();
		
		$attr = $task->attributes();
		$type = 'query';
		if (isset($attr['type']))
			$type = $attr['type'];
		
		$value = (string) $task;		
		switch ($type)
		{
			case 'query':
				$replace = array();
				$with = array();
				
				$replace[] = '{PREFIX}';
				$with[] = $this->dbprefix;
				
				if (isset($GLOBALS['q_FormId']))
				{
					$replace[] = '{FormId}';
					$with[] = $GLOBALS['q_FormId'];
				}
				
				if (isset($GLOBALS['q_ComponentId']))
				{
					$replace[] = '{ComponentId}';
					$with[] = $GLOBALS['q_ComponentId'];
				}
				
				if (isset($GLOBALS['q_SubmissionId']))
				{
					$replace[] = '{SubmissionId}';
					$with[] = $GLOBALS['q_SubmissionId'];
				}
				
				if (isset($GLOBALS['q_ConditionId']))
				{
					$replace[] = '{ConditionId}';
					$with[] = $GLOBALS['q_ConditionId'];
				}
				
				if (!empty($GLOBALS['ComponentIds']))
				{
					foreach ($GLOBALS['ComponentIds'] as $ComponentName => $ComponentId)
					{
						$replace[] 	= '{ComponentIds['.$ComponentName.']}';
						$with[] 	= $ComponentId;
					}
				}
				
				// Little hack to rename all uppercase tables to new lowercase format
				if (preg_match('/INSERT INTO `'.$this->dbprefix.'(\w+)`/', $value, $matches))
					$value = str_replace($matches[1], strtolower($matches[1]), $value);
				// End of hack
				
				if ($this->version != '1.2.0')
					$value = html_entity_decode($value, ENT_COMPAT, 'UTF-8');
				
				// Old version hacks
				if ($this->version == '1.2.0')
				{
					if (strpos($value, '=\\\\\\"') !== false)
						$value = str_replace('\\\\', '', $value);
				}
				
				/*
				$value = $task->get('_data');
				$replaced = false;
				while (strpos($value, '&#39;') !== false)
				{
					$replaced = true;
					$value = str_replace('&#39;', '&amp;#39;', $value);
				}
				if (!$replaced)
					$value = $task->data();
				*/
				
				$value = str_replace($replace, $with, $value);				
				$db->setQuery($value);
				if (!$db->execute())
				{
					// Compatibility with an older version
					$pattern = "#Unknown column '(.*?)'#is";
					if (preg_match($pattern, $db->getErrorMsg(), $match) && $match[1] == 'UserEmailConfirmation')
					{
						$db->setQuery("ALTER TABLE `#__rsform_forms` ADD `UserEmailConfirmation` TINYINT(1) NOT NULL");
						$db->execute();
						
						$this->_addColumnToRemove('UserEmailConfirmation');
						
						$db->setQuery($value);
						if (!$db->execute())
						{
							JError::raiseWarning(500, $db->getErrorMsg());
							return false;
						}
						return true;
					}
					
					JError::raiseWarning(500, $db->getErrorMsg());
					return false;
				}
			break;
			
			case 'eval':
				if (strpos($value, '$GLOBALS[\'q_ComponentId\'] = mysql_insert_id();') !== false
				|| strpos($value, '$GLOBALS[\'q_SubmissionId\'] = mysql_insert_id();') !== false
				|| strpos($value, '$GLOBALS[\'q_FormId\'] = mysql_insert_id();') !== false)
					$value = str_replace('mysql_insert_id','$db->insertid',$value);
				
				eval($value);
			break;
		}
		
		return true;
	}
	
	function updateUploads()
	{
		$db = JFactory::getDBO();
		
		$db->setQuery("SELECT `ComponentId` FROM `#__rsform_components` WHERE `ComponentTypeId` = 9 ");
		$uploadcomponents = $db->loadObjectList();

		if (!empty($uploadcomponents))
		{
			foreach ($uploadcomponents as $uploadcomponent)
			{
				$db->setQuery("SELECT `PropertyId`, `PropertyValue` FROM `#__rsform_properties` WHERE `ComponentId` = '".$uploadcomponent->ComponentId."' AND `PropertyName` = 'ATTACHADMINEMAIL' ");
				$adminemail = $db->loadObject();
				$db->setQuery("SELECT `PropertyId`, `PropertyValue` FROM `#__rsform_properties` WHERE `ComponentId` = '".$uploadcomponent->ComponentId."' AND `PropertyName` = 'ATTACHUSEREMAIL' ");
				$useremail = $db->loadObject();
				
				$updateemailattach = array();
				
				if (!empty($useremail))
				{
					if (!empty($useremail->PropertyValue) && $useremail->PropertyValue == 'YES') $updateemailattach[] = 'useremail';
				}
				
				if (!empty($adminemail))
				{
					if (!empty($adminemail->PropertyValue) && $adminemail->PropertyValue == 'YES') $updateemailattach[] = 'adminemail';
				}
					
				$updateemailattach = !empty($updateemailattach) ? implode(',',$updateemailattach) : '';
				
				if (!empty($updateemailattach))
				{
					$db->setQuery("INSERT INTO #__rsform_properties SET ComponentId = '".$uploadcomponent->ComponentId."' , PropertyName = 'EMAILATTACH', PropertyValue = '".$db->escape($updateemailattach)."' ");
					$db->execute();
					
					$db->setQuery("DELETE FROM #__rsform_properties WHERE PropertyId = '".$adminemail->PropertyId."'");
					$db->execute();
					$db->setQuery("DELETE FROM #__rsform_properties WHERE PropertyId = '".$useremail->PropertyId."'");
					$db->execute();
				}
			}
		}
	}
	
}
?>