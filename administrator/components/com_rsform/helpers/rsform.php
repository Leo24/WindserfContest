<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__).'/config.php';
require_once dirname(__FILE__).'/version.php';

// Product info
if(!defined('_RSFORM_REVISION')) {
	$version = new RSFormProVersion();
	
	define('_RSFORM_PRODUCT', 'RSform!Pro');
	define('_RSFORM_VERSION', $version->long);
	define('_RSFORM_KEY', $version->key);
	define('_RSFORM_REVISION', $version->revision);
}

JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_rsform/tables');

$cache = JFactory::getCache('com_rsform');
$cache->clean();

$lang = JFactory::getLanguage();
$lang->load('com_rsform', JPATH_ADMINISTRATOR, 'en-GB', true);
$lang->load('com_rsform', JPATH_ADMINISTRATOR, $lang->getDefault(), true);
$lang->load('com_rsform', JPATH_ADMINISTRATOR, null, true);

// Create the Legacy adapter
$GLOBALS['RSadapter'] = RSFormProHelper::getLegacyAdapter();

// Legacy function -- RSgetValidationRules()
function RSgetValidationRules()
{
	return RSFormProHelper::getValidationRules();
}

function _modifyResponsiveTemplate()
{
	$buffer = JResponse::getBody();
	$buffer = trim($buffer);
	
	$lines = RSFormProHelper::explode($buffer);
	$line = $lines[0];
	if (strtolower($line) != '<!doctype html>')
	{
		// single line
		if (strpos($line, '>') !== false) {
			$buffer = str_replace($line, '<!doctype html>', $buffer);
		} else {
			// should be on multiple lines
			$i = 0;
			while (strpos($line, '>') === false) {
				$i++;
				$line = $lines[$i];
			}
			
			// bail out, we might be modifying something else
			if (strpos($line, '<') !== false) {
				return;
			}
			
			// remove the first lines...
			for ($j=0; $j<=$i; $j++) {
				unset($lines[$j]);
			}
			
			// add this on the first line
			array_unshift($lines, '<!doctype html>');
			
			// join the new buffer
			$buffer = implode("\r\n", $lines);
		}
		
		JResponse::setBody($buffer);
	}
}

class RSFormProHelper
{	
	public static function isJ16()
	{
		// just for legacy reasons
		return true;
	}
	
	public static function isJ($version) {
		static $cache = array();
		if (!isset($cache[$version])) {
			$jversion = new JVersion();
			$cache[$version] = $jversion->isCompatible($version);
		}
		
		return $cache[$version];
	}
	
	public static function getDate($date)
	{
		return JHTML::_('date', $date, 'Y-m-d H:i:s');
	}
	
	public static function getLegacyAdapter()
	{
		static $adapter;
		if (empty($adapter))
		{
			require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/legacy.php';
			$adapter = new RSAdapter();
		}
		
		return $adapter;
	}
	
	public static function getComponentId($name, $formId=0)
	{
		static $cache;
		if (!is_array($cache))
			$cache = array();
			
		if (empty($formId))
		{
			$formId = JRequest::getInt('formId');
			if (empty($formId))
			{
				$post   = JRequest::getVar('form');
				$formId = (int) @$post['formId'];
			}
		}
		
		if (!isset($cache[$formId][$name]))
			$cache[$formId][$name] = RSFormProHelper::componentNameExists($name, $formId);
		
		return $cache[$formId][$name];
	}
	
	public static function checkValue($setvalue, $array)
	{
		if (!is_array($array))
			$array = RSFormProHelper::explode($array);
		
		if (strlen($setvalue))
			foreach ($array as $k => $v)
			{
				@list($value, $text) = explode("|", $v, 2);
				if ($value == $setvalue)
					$array[$k] = $v.'[c]';
			}
		
		return implode("\n", $array);
	}
	
	public static function createList($results, $value='value', $text='text')
	{
		$list = array();
		if (is_array($results))
			foreach ($results as $result)
				if (is_object($result))
					$list[] = $result->{$value}.'|'.$result->{$text};
				elseif (is_array($result))
					$list[] = $result[$value].'|'.$result[$text];
		
		return implode("\n", $list);
	}
	
	public static function displayForm($formId, $is_module=false)
	{
		$mainframe = JFactory::getApplication();
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT Published, FormTitle, MetaTitle, MetaDesc, MetaKeywords, ShowThankyou FROM #__rsform_forms WHERE FormId='".(int) $formId."'");
		$form = $db->loadObject();
		
		if (empty($form) || !$form->Published)
		{
			JError::raiseWarning(500, JText::_('_NOT_EXIST'));
			return;
		}
		
		$lang 		  = RSFormProHelper::getCurrentLanguage($formId);
		$translations = RSFormProHelper::getTranslations('forms', $formId, $lang);
		if ($translations)
			foreach ($translations as $field => $value)
			{
				if (isset($form->$field))
					$form->$field = $value;
			}
		
		$doc = JFactory::getDocument();
		if (!$is_module)
		{
			if ($form->MetaDesc)
				$doc->setMetaData('description', $form->MetaDesc);
			if ($form->MetaKeywords)
				$doc->setMetaData('keywords', $form->MetaKeywords);
			if ($form->MetaTitle)
				$doc->setTitle($form->FormTitle);
		}
		
		$session = JFactory::getSession();
		$formparams = $session->get('com_rsform.formparams.'.$formId);
		
		// Form has been processed ?
		if ($formparams && $formparams->formProcessed)
		{
			// Must show Thank You Message
			if ($form->ShowThankyou)
			{
				return RSFormProHelper::showThankYouMessage($formId);
			}
			
			// Clear
			$session->clear('com_rsform.formparams.'.$formId);
			
			// Must show small message
			$mainframe->enqueueMessage(JText::_('RSFP_THANKYOU_SMALL'));
		}
		
		// Must process form
		$post = JRequest::getVar('form', array(), 'post', 'none', JREQUEST_ALLOWRAW);
		if (isset($post['formId']) && $post['formId'] == $formId)
		{
			$invalid = RSFormProHelper::processForm($formId);
			// Did not pass validation - show the form
			if ($invalid)
			{
				$mainframe->triggerEvent('rsfp_f_onBeforeShowForm');
				return RSFormProHelper::showForm($formId, $post, $invalid);
			}
		}
		
		// Default - show the form
		$mainframe->triggerEvent('rsfp_f_onBeforeShowForm');
		return RSFormProHelper::showForm($formId);
	}
	
	public static function WYSIWYG($name, $content, $hiddenField, $width, $height, $col, $row)
    {
    	$editor = JFactory::getEditor();		
		$params = array('relative_urls' => '0', 'cleanup_save' => '0', 'cleanup_startup' => '0', 'cleanup_entities' => '0');
		
		$id = trim(substr($name, 4), '][');
		$content = $editor->display($name, $content , $width, $height, $col, $row, true, $id, null, null, $params);
		
		return $content;
    }
	
	public static function getValidationRules()
	{
		require_once JPATH_SITE.'/components/com_rsform/helpers/validation.php';
		$results = get_class_methods('RSFormProValidations');
		return implode("\n",$results);
	}
	
	public static function readConfig($force=false)
	{
		$config = RSFormProConfig::getInstance();
		
		if ($force) {
			$config->reload();
		}
		
		return $config->getData();
	}
	
	public static function getConfig($name = null)
	{
		$config = RSFormProConfig::getInstance();
		if (is_null($name)) {
			return $config->getData();
		} else {
			return $config->get($name);
		}
	}
	
	public static function genKeyCode()
	{
		$code = RSFormProHelper::getConfig('global.register.code');
		return md5($code._RSFORM_KEY);
	}
	
	public static function componentNameExists($componentName, $formId, $currentComponentId=0)
	{
		$db = JFactory::getDBO();
		
		if ($componentName == 'formId')
			return true;
		
		$componentName = $db->escape($componentName);
		$formId = (int) $formId;
		$currentComponentId = (int) $currentComponentId;
		
		$query  = "SELECT c.ComponentId FROM #__rsform_properties p LEFT JOIN #__rsform_components c ON (p.ComponentId = c.ComponentId)";
		$query .= "WHERE c.FormId='".$formId."' AND p.PropertyName='NAME' AND p.PropertyValue='".$componentName."'";
		if ($currentComponentId)
			$query .= " AND c.ComponentId != '".$currentComponentId."'";
		
		$db->setQuery($query);
		$exists = $db->loadResult();
		
		return $exists;
	}
	
	public static function copyComponent($sourceComponentId, $toFormId)
	{
		$sourceComponentId 	= (int) $sourceComponentId;
		$toFormId 			= (int) $toFormId;
		$db 				= JFactory::getDBO();
		
		$db->setQuery("SELECT * FROM #__rsform_components WHERE ComponentId='".$sourceComponentId."'");
		$component = $db->loadObject();
		if (!$component)
			return false;
	
		//get max ordering
		$db->setQuery("SELECT MAX(`Order`)+1 FROM #__rsform_components WHERE FormId = '".$toFormId."'");
		$component->Order = $db->loadResult();
		
		$db->setQuery("INSERT INTO #__rsform_components SET `FormId`='".$toFormId."', `ComponentTypeId`='".$component->ComponentTypeId."', `Order`='".$component->Order."',`Published`='".$component->Published."'");
		$db->execute();
		$newComponentId = $db->insertid();
		
		$db->setQuery("SELECT * FROM #__rsform_properties WHERE ComponentId='".$sourceComponentId."'");
		$properties = $db->loadObjectList();
		
		foreach ($properties as $property)
		{
			if ($property->PropertyName == 'NAME' && $toFormId == $component->FormId)
			{
				$property->PropertyValue .= ' copy';
			
				while (RSFormProHelper::componentNameExists($property->PropertyValue, $toFormId))
					$property->PropertyValue .= mt_rand(0,9);
			}
			
			$db->setQuery("INSERT INTO #__rsform_properties SET ComponentId='".$newComponentId."', PropertyName='".$db->escape($property->PropertyName)."', PropertyValue='".$db->escape($property->PropertyValue)."'");
			$db->execute();
		}
		
		// copy language
		$db->setQuery("SELECT * FROM #__rsform_translations WHERE `reference`='properties' AND `reference_id` LIKE '".$sourceComponentId.".%'");
		$translations = $db->loadObjectList();
		foreach ($translations as $translation)
		{
			$reference_id = $newComponentId.'.'.end(explode('.', $translation->reference_id, 2));
			
			$db->setQuery("INSERT INTO #__rsform_translations SET `form_id`='".$toFormId."', `lang_code`='".$db->escape($translation->lang_code)."', `reference`='properties', `reference_id`='".$db->escape($reference_id)."', `value`='".$db->escape($translation->value)."'");
			$db->execute();
		}
		
		return $newComponentId;
	}
	
	public static function getCurrentLanguage($formId=null)
	{
		$mainframe = JFactory::getApplication();
		$lang 	   = JFactory::getLanguage();
		
		$session   = JFactory::getSession();
		$formId    = !$formId ? JRequest::getInt('formId') || JRequest::getInt('FormId') : $formId;
		
		// editing in backend ?
		if ($mainframe->isAdmin())
		{
			if (JRequest::getVar('task') == 'submissions.edit')
			{
				$cid = JRequest::getVar('cid', array());
				if (is_array($cid))
					$cid = (int) @$cid[0];
					
				$db = JFactory::getDBO();
				$db->setQuery("SELECT `Lang` FROM #__rsform_submissions WHERE SubmissionId='".$cid."'");
				$language = $db->loadResult();
				
				return $language;
			}
			
			return $session->get('com_rsform.form.'.$formId.'.lang', $lang->getTag());
		}
		// frontend
		else
		{
			return $lang->getTag();
		}
	}
	
	public static function getComponentProperties($components)
	{
		$db = JFactory::getDBO();
		
		if (is_numeric($components))
		{
			$componentId = (int) $components;
		
			//load component properties
			$db->setQuery("SELECT `PropertyName`, `PropertyValue` FROM #__rsform_properties WHERE `ComponentId`='".$componentId."'");
			$properties = $db->loadObjectList();
		
			//set up data array with component properties
			$data = array();
			foreach($properties as $property)
				$data[$property->PropertyName] = $property->PropertyValue;
			$data['componentId'] = $componentId;
		
			unset($properties);
			
			$db->setQuery("SELECT FormId FROM #__rsform_components WHERE ComponentId='".$componentId."'");
			$formId = $db->loadResult();
			
			// language
			$lang 		  = RSFormProHelper::getCurrentLanguage($formId);
			$translations = RSFormProHelper::getTranslations('properties', $formId, $lang);
			foreach ($data as $property => $value)
			{
				$reference_id = $componentId.'.'.$property;
				if (isset($translations[$reference_id]))
					$data[$property] = $translations[$reference_id];
			}
			
			return $data;
		}
		elseif (is_array($components))
		{
			$componentIds = array();
			foreach ($components as $componentId)
			{
				if (is_object($componentId) && !empty($componentId->ComponentId))
					$componentIds[] = (int) $componentId->ComponentId;
				elseif (is_array($componentId) && !empty($componentId['ComponentId']))
					$componentIds[] = (int) $componentId['ComponentId'];
				else
					$componentIds[] = (int) $componentId;
			}
			if (!empty($componentIds))
			{
				$db->setQuery("SELECT `PropertyName`, `PropertyValue`, `ComponentId` FROM #__rsform_properties WHERE `ComponentId` IN (".implode(',', $componentIds).")");
				$results = $db->loadObjectList();
				
				$all_data = array();
				foreach ($results as $result)
					$all_data[$result->ComponentId][$result->PropertyName] = $result->PropertyValue;
				
				foreach ($all_data as $componentId => $properties)
					$all_data[$componentId]['componentId'] = $componentId;
				
				$db->setQuery("SELECT FormId FROM #__rsform_components WHERE ComponentId='".$componentIds[0]."'");
				$formId = $db->loadResult();
				
				// language
				$lang 		  = RSFormProHelper::getCurrentLanguage($formId);
				$translations = RSFormProHelper::getTranslations('properties', $formId, $lang);
				foreach ($all_data as $componentId => $properties)
				{
					foreach ($properties as $property => $value)
					{
						$reference_id = $componentId.'.'.$property;
						if (isset($translations[$reference_id]))
							$properties[$property] = $translations[$reference_id];
					}
					$all_data[$componentId] = $properties;
				}
				
				return $all_data;
			}
		}
		
		return false;
	}
	
	public static function isCode($value)
	{
		$RSadapter = RSFormProHelper::getLegacyAdapter();
		
		if (strpos($value, '<code>') !== false)
			return eval($value);
		
		return $value;
	}
	
	public static function showPreview($formId, $componentId, $data)
	{
		$mainframe = JFactory::getApplication();
		
		$formId = (int) $formId;
		$componentId = (int) $componentId;
		
		// Legacy
		$r = array();
		$r['ComponentTypeName'] = $data['ComponentTypeName'];
		
		$out ='';
		
		//Trigger Event - rsfp_bk_onBeforeCreateComponentPreview
		$mainframe->triggerEvent('rsfp_bk_onBeforeCreateComponentPreview',array(array('out'=>&$out,'formId'=>$formId,'componentId'=>$componentId,'ComponentTypeName'=>$r['ComponentTypeName'],'data'=>$data)));
		
		static $passedPageBreak;
		
		switch($r['ComponentTypeName'])
		{
			case 'textBox':
			{
				$defaultValue = RSFormProHelper::isCode($data['DEFAULTVALUE']);
				
				$out.='<td>'.$data['CAPTION'].'</td>';
				$out.='<td><input type="text" value="'.RSFormProHelper::htmlEscape($defaultValue).'" size="'.$data['SIZE'].'" /></td>';
			}
			break;
			
			case 'textArea':
			{
				$defaultValue = RSFormProHelper::isCode($data['DEFAULTVALUE']);	
				
				$out.='<td>'.$data['CAPTION'].'</td>';
				$out.='<td><textarea cols="'.$data['COLS'].'" rows="'.$data['ROWS'].'">'.RSFormProHelper::htmlEscape($defaultValue).'</textarea></td>';
			}
			break;
			
			case 'selectList':
			{
				$out.='<td>'.$data['CAPTION'].'</td>';
				$out.='<td><select '.($data['MULTIPLE']=='YES' ? 'multiple="multiple"' : '').' size="'.$data['SIZE'].'">';
				
				$items = RSFormProHelper::isCode($data['ITEMS']);
				$items = str_replace(array("\r\n", "\r"), "\n", $items);
				$items = explode("\n",$items);
				
				$special = array('[c]', '[g]', '[d]');
				
				foreach ($items as $item)
				{
					@list($val, $txt) = @explode('|', str_replace($special, '', $item), 2);
					if (is_null($txt))
						$txt = $val;
					
					// <optgroup>
					if (strpos($item, '[g]') !== false) {
						$out .= '<optgroup label="'.RSFormProHelper::htmlEscape($val).'">';
						continue;
					}
					// </optgroup>
					if(strpos($item, '[/g]') !== false) {
						$out .= '</optgroup>';
						continue;
					}
					
					$additional = '';
					// selected
					if (strpos($item, '[c]') !== false)
						$additional .= 'selected="selected"';
					// disabled
					if (strpos($item, '[d]') !== false)
						$additional .= 'disabled="disabled"';
					
					$out .= '<option '.$additional.' value="'.RSFormProHelper::htmlEscape($val).'">'.RSFormProHelper::htmlEscape($txt).'</option>';
				}
				$out.='</select></td>';
			}
			break;
			
			case 'checkboxGroup':
			{
				$i=0;
				
				$out.='<td>'.$data['CAPTION'].'</td>';
				$out.='<td>';
				
				$items = RSFormProHelper::isCode($data['ITEMS']);
				$items = str_replace(array("\r\n", "\r"), "\n", $items);
				$items = explode("\n",$items);
				
				$special = array('[c]', '[d]');
				
				foreach ($items as $item)
				{
					@list($val, $txt) = @explode('|', str_replace($special, '', $item), 2);
					if (is_null($txt))
						$txt = $val;
					
					$additional = '';
					// checked
					if (strpos($item, '[c]') !== false)
						$additional .= 'checked="checked"';
					// disabled
					if (strpos($item, '[d]') !== false)
						$additional .= 'disabled="disabled"';
					
					$out.='<input type="checkbox" '.$additional.' value="'.RSFormProHelper::htmlEscape($val).'" id="'.$data['NAME'].$i.'"/><label for="'.$data['NAME'].$i.'">'.$txt.'</label>';
					if($data['FLOW']=='VERTICAL') $out.='<br/>';
					$i++;
				}
				$out.='</td>';

			}
			break;
			
			case 'radioGroup':
			{
				$i=0;
				
				$out.='<td>'.$data['CAPTION'].'</td>';
				$out.='<td>';
				
				$items = RSFormProHelper::isCode($data['ITEMS']);
				$items = str_replace(array("\r\n", "\r"), "\n", $items);
				$items = explode("\n",$items);
				
				$special = array('[c]', '[d]');
				
				foreach ($items as $item)
				{
					@list($val, $txt) = @explode('|', str_replace($special, '', $item), 2);
					if (is_null($txt))
						$txt = $val;
					
					$additional = '';
					// checked
					if (strpos($item, '[c]') !== false)
						$additional .= 'checked="checked"';
					// disabled
					if (strpos($item, '[d]') !== false)
						$additional .= 'disabled="disabled"';
					
					$out.='<input type="radio" '.$additional.' value="'.RSFormProHelper::htmlEscape($val).'" id="'.$data['NAME'].$i.'"/><label for="'.$data['NAME'].$i.'">'.$txt.'</label>';
					if ($data['FLOW']=='VERTICAL') $out.='<br/>';
					$i++;
				}
				$out.='</td>';

			}
			break;
			
			case 'calendar':
			{
				$out.='<td>'.$data['CAPTION'].'</td>';
				$out.='<td><img src="'.JURI::root(true).'/administrator/components/com_rsform/assets/images/icons/calendar.png" /> '.JText::_('RSFP_COMP_FVALUE_'.$data['CALENDARLAYOUT']).'</td>';
			}
			break;
			
			case 'captcha':
			{
				$out.='<td>'.$data['CAPTION'].'</td>';
				$out.='<td>';
				switch (@$data['IMAGETYPE'])
				{
					default:
					case 'FREETYPE':
					case 'NOFREETYPE':
						$out.='<img src="index.php?option=com_rsform&amp;task=captcha&amp;componentId='.$componentId.'&amp;tmpl=component&amp;sid='.mt_rand().'" id="captcha'.$componentId.'" alt="'.$data['CAPTION'].'"/>';
						$out.=($data['FLOW']=='HORIZONTAL') ? '':'<br/>';
						$out.='<input type="text" value="" id="captchaTxt'.$componentId.'" '.$data['ADDITIONALATTRIBUTES'].' />';
						$out.=($data['SHOWREFRESH']=='YES') ? '&nbsp;&nbsp;<a href="" onclick="refreshCaptcha('.$componentId.',\'index.php?option=com_rsform&amp;task=captcha&amp;componentId='.$componentId.'&amp;tmpl=component\'); return false;">'.$data['REFRESHTEXT'].'</a>':'';
					break;
					
					case 'INVISIBLE':
						$out.='{hidden captcha}';
					break;
				}
				$out.='</td>';
			}
			break;
			
			case 'fileUpload':
			{
				$out.='<td>'.$data['CAPTION'].'</td>';
				$out.='<td><input type="file" /></td>';
			}
			break;
			
			case 'freeText':
			{
				$out.='<td>&nbsp;</td>';
				$out.='<td>'.$data['TEXT'].'</td>';
			}
			break;
			
			case 'hidden':
			{
				$out.='<td>&nbsp;</td>';
				$out.='<td>{hidden field}</td>';
			}
			break;
			
			case 'imageButton':
			{			
				$out.='<td>'.$data['CAPTION'].'</td>';
				$out.='<td>';
				$out.='<input type="image" src="'.RSFormProHelper::htmlEscape($data['IMAGEBUTTON']).'"/>';
				if($data['RESET']=='YES')
					$out.='&nbsp;&nbsp;<input type="image" src="'.RSFormProHelper::htmlEscape($data['IMAGERESET']).'"/>';
				$out.='</td>';
			}
			break;
			
			case 'button':
			case 'submitButton':
			{				
				$out.='<td>'.$data['CAPTION'].'</td>';
				
				if (isset($data['BUTTONTYPE']) && $data['BUTTONTYPE'] == 'TYPEBUTTON')
					$out.='<td><button type="button">'.RSFormProHelper::htmlEscape($data['LABEL']).'</button>';
				else
					$out.='<td><input type="button" value="'.RSFormProHelper::htmlEscape($data['LABEL']).'" />';
					
				if($data['RESET']=='YES')
				{
					if (isset($data['BUTTONTYPE']) && $data['BUTTONTYPE'] == 'TYPEBUTTON')
						$out.='&nbsp;&nbsp;<button type="reset">'.RSFormProHelper::htmlEscape($data['RESETLABEL']).'</button>';
					else
						$out.='&nbsp;&nbsp;<input type="reset" value="'.RSFormProHelper::htmlEscape($data['RESETLABEL']).'"/>';
				}
				$out.='</td>';
			}
			break;
			
			case 'password':
			{				
				$out.='<td>'.$data['CAPTION'].'</td>';
				$out.='<td><input type="password" value="'.RSFormProHelper::htmlEscape($data['DEFAULTVALUE']).'" size="'.$data['SIZE'].'"/></td>';
			}
			break;
			
			case 'ticket':
			{				
				$out.='<td>&nbsp;</td>';
				$out.='<td>'.RSFormProHelper::generateString($data['LENGTH'],$data['CHARACTERS']).'</td>';
			}
			break;
			
			case 'pageBreak':
				$out.='<td>&nbsp;</td>';
				$out.='<td>'.($passedPageBreak ? '<input type="button" value="'.RSFormProHelper::htmlEscape($data['PREVBUTTON']).'" />' : '').' <input type="button" value="'.RSFormProHelper::htmlEscape($data['NEXTBUTTON']).'" /></td>';
				$passedPageBreak = true;
			break;
			
			case 'rseprotickets':
				$out.='<td>'.$data['CAPTION'].'</td>';
				$out.='<td>'.JText::_('RSFP_RSEVENTSPRO_TICKETS').'</td>';
			break;
			
			default:
				$out = '<td colspan="2" style="color:#333333"><em>'.JText::_('RSFP_COMP_PREVIEW_NOT_AVAILABLE').'</em></td>';
			break;
		}
		
		//Trigger Event - rsfp_bk_onAfterCreateComponentPreview
		$mainframe->triggerEvent('rsfp_bk_onAfterCreateComponentPreview',array(array('out'=>&$out, 'formId'=>$formId, 'componentId'=>$componentId, 'ComponentTypeName'=>$r['ComponentTypeName'],'data'=>$data)));
		
		return $out;
	}
	
	public static function htmlEscape($val)
	{
		return htmlentities($val, ENT_COMPAT, 'UTF-8');
	}
	
	public static function explode($value)
	{
		$value = str_replace(array("\r\n", "\r"), "\n", $value);
		$value = explode("\n", $value);
		
		return $value;
	}
	
	public static function readFile($file, $download_name=null)
	{
		if (empty($download_name))
			$download_name = basename($file);
			
		$fsize = filesize($file);
		
		header("Cache-Control: public, must-revalidate");
		header('Cache-Control: pre-check=0, post-check=0, max-age=0');
		if (!preg_match('#MSIE#', $_SERVER['HTTP_USER_AGENT']))
			header("Pragma: no-cache");
		header("Expires: 0"); 
		header("Content-Description: File Transfer");
		header("Expires: Sat, 01 Jan 2000 01:00:00 GMT");
		if (preg_match('#Opera#', $_SERVER['HTTP_USER_AGENT']))
			header("Content-Type: application/octetstream"); 
		else 
			header("Content-Type: application/octet-stream");
		header("Content-Length: ".(string) ($fsize));
		header('Content-Disposition: attachment; filename="'.$download_name.'"');
		header("Content-Transfer-Encoding: binary\n");
		ob_end_flush();
		RSFormProHelper::readFileChunked($file);
		exit();
	}
	
	public static function readFileChunked($filename, $retbytes=true)
	{
		$chunksize = 1*(1024*1024); // how many bytes per chunk
		$buffer = '';
		$cnt =0;
		$handle = fopen($filename, 'rb');
		if ($handle === false) {
			return false;
		}
		while (!feof($handle)) {
			$buffer = fread($handle, $chunksize);
			echo $buffer;
			if ($retbytes) {
				$cnt += strlen($buffer);
			}
		}
		$status = fclose($handle);
		if ($retbytes && $status) {
			return $cnt; // return num. bytes delivered like readfile() does.
		}
		return $status;
	}
	
	public static function getReplacements($SubmissionId, $skip_globals=false)
	{
		// Small hack
		return RSFormProHelper::sendSubmissionEmails($SubmissionId, true, $skip_globals);
	}
	
	public static function sendSubmissionEmails($SubmissionId, $only_return_replacements=false, $skip_globals=false)
	{
		$db = JFactory::getDBO();
		$u = JUri::getInstance();
		$config = JFactory::getConfig();
		$SubmissionId = (int) $SubmissionId;
		$mainframe = JFactory::getApplication();
		$Itemid = JRequest::getInt('Itemid');
		$Itemid = $Itemid ? '&amp;Itemid='.$Itemid : '';
		
		$db->setQuery("SELECT * FROM #__rsform_submissions WHERE SubmissionId='".$SubmissionId."'");
		$submission = $db->loadObject();
		
		$submission->values = array();
		$db->setQuery("SELECT FieldName, FieldValue FROM #__rsform_submission_values WHERE SubmissionId='".$SubmissionId."'");
		$fields = $db->loadObjectList();
		foreach ($fields as $field)
			$submission->values[$field->FieldName] = $field->FieldValue;
		unset($fields);
		
		$formId = $submission->FormId;
		$db->setQuery("SELECT * FROM #__rsform_forms WHERE FormId='".$formId."'");
		$form = $db->loadObject();
		$form->MultipleSeparator = str_replace(array('\n', '\r', '\t'), array("\n", "\r", "\t"), $form->MultipleSeparator);

		if (empty($submission->Lang))
		{
			if (!empty($form->Lang))
				$submission->Lang = $form->Lang;
			else
			{
				$lang = JFactory::getLanguage();
				$language = $lang->getDefault();
				$submission->Lang = $language;
			}
			$db->setQuery("UPDATE #__rsform_submissions SET Lang='".$db->escape($submission->Lang)."' WHERE SubmissionId='".$submission->SubmissionId."'");
			$db->execute();
		}
			
		$translations = RSFormProHelper::getTranslations('forms', $form->FormId, $submission->Lang);
		if ($translations)
			foreach ($translations as $field => $value)
			{
				if (isset($form->$field))
					$form->$field = $value;
			}
		
		$placeholders = array();
		$values = array();
		
		$db->setQuery("SELECT c.ComponentTypeId, p.ComponentId, p.PropertyName, p.PropertyValue FROM #__rsform_components c LEFT JOIN #__rsform_properties p ON (c.ComponentId=p.ComponentId) WHERE c.FormId='".$formId."' AND c.Published='1' AND p.PropertyName IN ('NAME', 'CAPTION', 'EMAILATTACH', 'WYSIWYG', 'ITEMS')");
		$components = $db->loadObjectList();
		$properties 	   = array();
		$uploadFields 	   = array();
		$multipleFields    = array();
		$textareaFields    = array();
		$userEmailUploads  = array();
		$adminEmailUploads = array();
		$additionalEmailUploads = array();
		$additionalEmailUploadsIds = array();
		
		foreach ($components as $component)
		{
			// Upload fields - grab by NAME so that we can use it later on when checking $_FILES
			if ($component->ComponentTypeId == 9)
			{
				if ($component->PropertyName == 'EMAILATTACH')
				{
					$emailsvalues = $component->PropertyValue;
					$emailsvalues = trim($emailsvalues) != '' ? explode(',',$emailsvalues) : array();
					
					if (!empty($emailsvalues))
					foreach ($emailsvalues as $emailvalue)
					{
						if ($emailvalue == 'useremail' || $emailvalue == 'adminemail') continue;
						$additionalEmailUploadsIds[] = $emailvalue;
					}
					
					$additionalEmailUploadsIds = array_unique($additionalEmailUploadsIds);
					
					if (!empty($additionalEmailUploadsIds))
					foreach ($additionalEmailUploadsIds as $additionalEmailUploadsId)
					{
						if (in_array($additionalEmailUploadsId,$emailsvalues))
						$additionalEmailUploads[$additionalEmailUploadsId][] = $component->ComponentId;
					}
				}
				
				if ($component->PropertyName == 'NAME')
					$uploadFields[] = $component->PropertyValue;
				
				if ($component->PropertyName == 'EMAILATTACH' && !empty($component->PropertyValue))
				{
					$emailvalues = explode(',',$component->PropertyValue);
					
					if (in_array('useremail',$emailvalues))
					{
						$userEmailUploads[] = $component->ComponentId;
						//continue;
					}
					
					if (in_array('adminemail',$emailvalues))
					{
						$adminEmailUploads[] = $component->ComponentId;
						//continue;
					}				
				}
			}
			// Multiple fields - grab by ComponentId for performance
			elseif (in_array($component->ComponentTypeId, array(3, 4)))
			{
				if ($component->PropertyName == 'NAME')
					$multipleFields[] = $component->ComponentId;
			}
			// Textarea fields - grab by ComponentId for performance
			elseif ($component->ComponentTypeId == 2)
			{
				if ($component->PropertyName == 'WYSIWYG' && $component->PropertyValue == 'NO')
					$textareaFields[] = $component->ComponentId;
			}
			
			$properties[$component->ComponentId][$component->PropertyName] = $component->PropertyValue;
		}
		
		// language
		$translations = RSFormProHelper::getTranslations('properties', $formId, $submission->Lang);
		foreach ($properties as $componentId => $componentProperties)
		{
			foreach ($componentProperties as $property => $value)
			{
				$reference_id = $componentId.'.'.$property;
				if (isset($translations[$reference_id]))
					$componentProperties[$property] = $translations[$reference_id];
			}
			$properties[$componentId] = $componentProperties;
		}
		
		$secret = $config->get('secret');
		foreach ($properties as $ComponentId => $property)
		{
			// {component:caption}
			$placeholders[] = '{'.$property['NAME'].':caption}';
			$values[] = isset($property['CAPTION']) ? $property['CAPTION'] : '';
			
			// {component:name}
			$placeholders[] = '{'.$property['NAME'].':name}';
			$values[] = $property['NAME'];
			
			// {component:value}
			$placeholders[] = '{'.$property['NAME'].':value}';
			$value = '';
			if (isset($submission->values[$property['NAME']]))
			{
				$value = $submission->values[$property['NAME']];
				
				// Check if this is an upload field
				if (in_array($property['NAME'], $uploadFields))
					$value = '<a href="'.JURI::root().'index.php?option=com_rsform&amp;task=submissions.view.file&amp;hash='.md5($submission->SubmissionId.$secret.$property['NAME']).$Itemid.'">'.basename($submission->values[$property['NAME']]).'</a>';
				// Check if this is a multiple field
				elseif (in_array($ComponentId, $multipleFields))
					$value = str_replace("\n", $form->MultipleSeparator, $value);
				elseif ($form->TextareaNewLines && in_array($ComponentId, $textareaFields))
					$value = nl2br($value);
			}
			$values[] = $value;
			
			if (isset($property['ITEMS'])) {
				$placeholders[] = '{'.$property['NAME'].':text}';
				if (isset($submission->values[$property['NAME']])) {
					$value = $submission->values[$property['NAME']];
					$all_values = explode("\n", $value);
					$all_texts  = array();
					$items = RSFormProHelper::explode(RSFormProHelper::isCode($property['ITEMS']));
					
					$special = array('[c]', '[g]', '[d]');
					foreach ($all_values as $v => $value) {
						$all_texts[$v] = $value;
						foreach ($items as $item) {
							$item = str_replace($special, '', $item);
							@list($item_val, $item_text) = explode("|", $item, 2);
							
							if ($item_text && $item_val == $value)
							{
								$all_texts[$v] = $item_text;
								break;
							}
						}
					}
					
					if ($all_texts) {
						$values[] = implode($form->MultipleSeparator, $all_texts);
					} else {
						$values[] = $value;
					}
				} else {
					$values[] = '';
				}
            }
			
			// {component:path}
			if (in_array($property['NAME'], $uploadFields))
			{
				$placeholders[] = '{'.$property['NAME'].':path}';
				if (isset($submission->values[$property['NAME']]))
				{
					$filepath = $submission->values[$property['NAME']];
					$filepath = substr_replace($filepath, JURI::root(), 0, strlen(JPATH_SITE)+1);
					$filepath = str_replace(array('\\', '\\/', '//\\'), '/', $filepath);
					$values[] = $filepath;
				}
				else
					$values[] = '';
			}
		}
		$placeholders[] = '{_STATUS:value}';
		$values[] = isset($submission->values['_STATUS']) ? JText::_('RSFP_PAYPAL_STATUS_'.$submission->values['_STATUS']) : '';
		
		$placeholders[] = '{_ANZ_STATUS:value}';
		$values[] = isset($submission->values['_ANZ_STATUS']) ? JText::_('RSFP_ANZ_STATUS_'.$submission->values['_ANZ_STATUS']) : '';
		
		$user = JFactory::getUser($submission->UserId);
		if (empty($user->id))
			$user = JFactory::getUser(0);
		
		$root 				= $mainframe->isAdmin() ? JURI::root() : $u->toString(array('scheme','host', 'port'));
		$confirmation_hash 	= md5($submission->SubmissionId.$formId.$submission->DateSubmitted);
		$hash_link 			= 'index.php?option=com_rsform&task=confirm&hash='.$confirmation_hash;
		$confirmation 		= $root.($mainframe->isAdmin() ? $hash_link : JRoute::_($hash_link));
		
		if (!$skip_globals)
		{
			array_push($placeholders, '{global:username}', '{global:userid}', '{global:useremail}', '{global:fullname}', '{global:userip}', '{global:date_added}', '{global:sitename}', '{global:siteurl}','{global:confirmation}','{global:submissionid}', '{global:submission_id}');
			array_push($values, $user->username, $user->id, $user->email, $user->name, isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '', $submission->DateSubmitted, $config->get('sitename'), JURI::root(),$confirmation, $submission->SubmissionId, $submission->SubmissionId);
		}
		
		$mainframe->triggerEvent('rsfp_onAfterCreatePlaceholders', array(array('form' => &$form, 'placeholders' => &$placeholders, 'values' => &$values, 'submission' => $submission)));
		
		if ($only_return_replacements)
			return array($placeholders, $values);
		
		$userEmail = array(
			'to' => str_replace($placeholders, $values, $form->UserEmailTo),
			'cc' => str_replace($placeholders, $values, $form->UserEmailCC),
			'bcc' => str_replace($placeholders, $values, $form->UserEmailBCC),
			'from' => str_replace($placeholders, $values, $form->UserEmailFrom),
			'replyto' => str_replace($placeholders, $values, $form->UserEmailReplyTo),
			'fromName' => str_replace($placeholders, $values, $form->UserEmailFromName),
			'text' => str_replace($placeholders, $values, $form->UserEmailText),
			'subject' => str_replace($placeholders, $values, $form->UserEmailSubject),
			'mode' => $form->UserEmailMode,
			'files' => array()
		);

		// user cc
		if (strpos($userEmail['cc'], ',') !== false)
			$userEmail['cc'] = explode(',', $userEmail['cc']);
		// user bcc
		if (strpos($userEmail['bcc'], ',') !== false)
			$userEmail['bcc'] = explode(',', $userEmail['bcc']);
		
		jimport('joomla.filesystem.file');
		
		$file = str_replace($placeholders, $values, $form->UserEmailAttachFile);
		if ($form->UserEmailAttach && JFile::exists($file))
			$userEmail['files'][] = $file;
		
		// Need to attach files
		// User Email
		foreach ($userEmailUploads as $componentId)
		{
			$name = $properties[$componentId]['NAME'];
			if (!empty($submission->values[$name]))
				$userEmail['files'][] = $submission->values[$name];
		}
		
		$adminEmail = array(
			'to' => str_replace($placeholders, $values, $form->AdminEmailTo),
			'cc' => str_replace($placeholders, $values, $form->AdminEmailCC),
			'bcc' => str_replace($placeholders, $values, $form->AdminEmailBCC),
			'from' => str_replace($placeholders, $values, $form->AdminEmailFrom),
			'replyto' => str_replace($placeholders, $values, $form->AdminEmailReplyTo),
			'fromName' => str_replace($placeholders, $values, $form->AdminEmailFromName),
			'text' => str_replace($placeholders, $values, $form->AdminEmailText),
			'subject' => str_replace($placeholders, $values, $form->AdminEmailSubject),
			'mode' => $form->AdminEmailMode,
			'files' => array()
		);
		
		// admin cc
		if (strpos($adminEmail['cc'], ',') !== false)
			$adminEmail['cc'] = explode(',', $adminEmail['cc']);
		// admin bcc
		if (strpos($adminEmail['bcc'], ',') !== false)
			$adminEmail['bcc'] = explode(',', $adminEmail['bcc']);
		
		// Admin Email
		foreach ($adminEmailUploads as $componentId)
		{
			$name = $properties[$componentId]['NAME'];
			if (!empty($submission->values[$name]))
				$adminEmail['files'][] = $submission->values[$name];
		}
		
		$mainframe->triggerEvent('rsfp_beforeUserEmail', array(array('form' => &$form, 'placeholders' => &$placeholders, 'values' => &$values, 'submissionId' => $SubmissionId, 'userEmail'=>&$userEmail)));
		
		// Script called before the User Email is sent.
		eval($form->UserEmailScript);
		
		// mail users
		$recipients = explode(',',$userEmail['to']);
		if(!empty($recipients))
			foreach($recipients as $recipient)
				if(!empty($recipient))
					RSFormProHelper::sendMail($userEmail['from'], $userEmail['fromName'], $recipient, $userEmail['subject'], $userEmail['text'], $userEmail['mode'], !empty($userEmail['cc']) ? $userEmail['cc'] : null, !empty($userEmail['bcc']) ? $userEmail['bcc'] : null, $userEmail['files'], !empty($userEmail['replyto']) ? $userEmail['replyto'] : '');
		
		$mainframe->triggerEvent('rsfp_beforeAdminEmail', array(array('form' => &$form, 'placeholders' => &$placeholders, 'values' => &$values, 'submissionId' => $SubmissionId, 'adminEmail'=>&$adminEmail)));
		
		// Script called before the Admin Email is sent.
		eval($form->AdminEmailScript);
		
		//mail admins
		$recipients = explode(',',$adminEmail['to']);
		if(!empty($recipients))
			foreach($recipients as $recipient)
				if(!empty($recipient))
					RSFormProHelper::sendMail($adminEmail['from'], $adminEmail['fromName'], $recipient, $adminEmail['subject'], $adminEmail['text'], $adminEmail['mode'], !empty($adminEmail['cc']) ? $adminEmail['cc'] : null, !empty($adminEmail['bcc']) ? $adminEmail['bcc'] : null, $adminEmail['files'], !empty($adminEmail['replyto']) ? $adminEmail['replyto'] : '');
		
		
		//additional emails
		$db->setQuery("SELECT * FROM #__rsform_emails WHERE formId = ".$formId." ");
		$emails = $db->loadObjectList();
		$etranslations = RSFormProHelper::getTranslations('emails', $formId, $submission->Lang);
		
		if (!empty($emails))
			foreach ($emails as $email)
			{
				if (empty($email->from) || empty($email->fromname) || empty($email->subject) || empty($email->message)) continue;
				$fromname = isset($etranslations[$email->id.'.fromname']) ? $etranslations[$email->id.'.fromname'] : $email->fromname;
				$subject = isset($etranslations[$email->id.'.subject']) ? $etranslations[$email->id.'.subject'] : $email->subject;
				$message = isset($etranslations[$email->id.'.message']) ? $etranslations[$email->id.'.message'] : $email->message;
				
				$additionalEmail = array(
					'to' => str_replace($placeholders, $values, $email->to),
					'cc' => str_replace($placeholders, $values, $email->cc),
					'bcc' => str_replace($placeholders, $values, $email->bcc),
					'from' => str_replace($placeholders, $values, $email->from),
					'replyto' => str_replace($placeholders, $values, $email->replyto),
					'fromName' => str_replace($placeholders, $values, $fromname),
					'text' => str_replace($placeholders, $values, $message),
					'subject' => str_replace($placeholders, $values, $subject),
					'mode' => $email->mode,
					'files' => array()
				);
				
				if (!empty($additionalEmailUploads))
				foreach ($additionalEmailUploads as $additionalEmailId => $additionalEmailUpload)
				{
					if ($additionalEmailId == $email->id)
						foreach ($additionalEmailUpload as $componentId)
						{
							$name = $properties[$componentId]['NAME'];
							if (!empty($submission->values[$name]))
								$additionalEmail['files'][] = $submission->values[$name];
						}
				}
				
				// additional cc
				if (strpos($additionalEmail['cc'], ',') !== false)
					$additionalEmail['cc'] = explode(',', $additionalEmail['cc']);
				// additional bcc
				if (strpos($additionalEmail['bcc'], ',') !== false)
					$additionalEmail['bcc'] = explode(',', $additionalEmail['bcc']);
				
				$mainframe->triggerEvent('rsfp_beforeAdditionalEmail', array(array('form' => &$form, 'placeholders' => &$placeholders, 'values' => &$values, 'submissionId' => $SubmissionId, 'additionalEmail'=>&$additionalEmail)));
				eval($form->AdditionalEmailsScript);
				
				// mail users
				$recipients = explode(',',$additionalEmail['to']);
				if(!empty($recipients))
					foreach($recipients as $recipient)
						if(!empty($recipient))
							RSFormProHelper::sendMail($additionalEmail['from'], $additionalEmail['fromName'], $recipient, $additionalEmail['subject'], $additionalEmail['text'], $additionalEmail['mode'], !empty($additionalEmail['cc']) ? $additionalEmail['cc'] : null, !empty($additionalEmail['bcc']) ? $additionalEmail['bcc'] : null, $additionalEmail['files'], !empty($additionalEmail['replyto']) ? $additionalEmail['replyto'] : '');
				
			}
		
		return array($placeholders, $values);
	}
	
	public static function escapeArray(&$val, &$key)
	{
		$db = JFactory::getDBO();
		$val = $db->escape($val);
		$key = $db->escape($key);
	}
	
	public static function componentExists($formId, $componentTypeId)
	{
		$formId = (int) $formId;
		$db = JFactory::getDBO();
		
		if (is_array($componentTypeId))
		{
			JArrayHelper::toInteger($componentTypeId);
			$db->setQuery("SELECT ComponentId FROM #__rsform_components WHERE ComponentTypeId IN (".implode(',', $componentTypeId).") AND FormId='".$formId."' AND Published='1'");
		}
		else
		{
			$componentTypeId = (int) $componentTypeId;
			$db->setQuery("SELECT ComponentId FROM #__rsform_components WHERE ComponentTypeId='".$componentTypeId."' AND FormId='".$formId."' AND Published='1'");
		}
		
		return $db->loadColumn();
	}
	
	public static function cleanCache()
	{
		$cache 	= JCache::getInstance('page');
		$id 	= $cache->makeId();
			
		if ($handler = $cache->_getStorage()) {
			$handler->remove($id, 'page');
		}
		
		// Test this
		// $cache->clean();
	}
	
	public static function loadTheme($form)
	{
		jimport('joomla.html.parameter');
		
		$doc = JFactory::getDocument();
		
		$registry = new JRegistry();
		$registry->loadString($form->ThemeParams, 'INI');
		$form->ThemeParams =& $registry;
			
		if ($form->ThemeParams->get('num_css', 0) > 0)
			for ($i=0; $i<$form->ThemeParams->get('num_css'); $i++)
			{
				$css = $form->ThemeParams->get('css'.$i);
				$doc->addStyleSheet(JURI::root(true).'/components/com_rsform/assets/themes/'.$form->ThemeParams->get('name').'/'.$css);
			}
		if ($form->ThemeParams->get('num_js', 0) > 0)
			for ($i=0; $i<$form->ThemeParams->get('num_js'); $i++)
			{
				$js = $form->ThemeParams->get('js'.$i);
				$doc->addScript(JURI::root(true).'/components/com_rsform/assets/themes/'.$form->ThemeParams->get('name').'/'.$js);
			}
	}
	
	// conditions
	public static function getConditions($formId, $lang=null)
	{
		$db   = JFactory::getDBO();
		
		if (!$lang)
			$lang = RSFormProHelper::getCurrentLanguage();
		
		// get all conditions
		$db->setQuery("SELECT c.*,p.PropertyValue AS ComponentName FROM `#__rsform_conditions` c LEFT JOIN #__rsform_properties p ON (c.component_id = p.ComponentId) LEFT JOIN #__rsform_components comp ON (comp.ComponentId=p.ComponentId) WHERE c.`form_id` = ".$formId." AND c.lang_code='".$db->escape($lang)."' AND comp.Published = 1 AND p.PropertyName='NAME' ORDER BY c.`id` ASC");
		if ($conditions = $db->loadObjectList())
		{
			// put them all in an array so we can use only one query
			$cids = array();
			foreach ($conditions as $condition)
				$cids[] = $condition->id;
			
			// get details
			$db->setQuery("SELECT d.*,p.PropertyValue AS ComponentName FROM #__rsform_condition_details d LEFT JOIN #__rsform_properties p ON (d.component_id = p.ComponentId) LEFT JOIN #__rsform_components comp ON (comp.ComponentId=p.ComponentId) WHERE d.condition_id IN (".implode(",", $cids).") AND comp.Published = 1 AND p.PropertyName='NAME'");
			$details = $db->loadObjectList();
			
			// arrange details within conditions
			foreach ($conditions as $i => $condition)
			{
				$condition->details = array();
				foreach ($details as $detail)
				{
					if ($detail->condition_id != $condition->id) continue;
					$condition->details[] = $detail;
				}
				
				$conditions[$i] = $condition;
			}
			// all done
			return $conditions;
		}
		// nothing found
		return false;
	}
	
	public static function showForm($formId, $val='', $validation=array())
	{
		$mainframe = JFactory::getApplication();
		
		$formId = (int) $formId;
		
		$db = JFactory::getDBO();
		$doc = JFactory::getDocument();
		
		$db->setQuery("SELECT `FormId`, `FormLayoutName`, `FormLayout`, `ScriptDisplay`, `ErrorMessage`, `FormTitle`, `CSS`, `JS`, `CSSClass`, `CSSId`, `CSSName`, `CSSAction`, `CSSAdditionalAttributes`, `AjaxValidation`, `ThemeParams` FROM #__rsform_forms WHERE FormId='".$formId."' AND `Published`='1'");
		$form = $db->loadObject();
		
		$lang 		  = RSFormProHelper::getCurrentLanguage();
		$translations = RSFormProHelper::getTranslations('forms', $form->FormId, $lang);
		if ($translations)
			foreach ($translations as $field => $value)
			{
				if (isset($form->$field))
					$form->$field = $value;
			}
		
		if ($form->JS)
			$doc->addCustomTag($form->JS);
		if ($form->CSS)
			$doc->addCustomTag($form->CSS);
		if ($form->ThemeParams)
			RSFormProHelper::loadTheme($form);
		
		$doc->addStyleSheet(JURI::root(true).'/components/com_rsform/assets/css/front.css');
		if ($doc->getDirection() == 'rtl')
			$doc->addStyleSheet(JURI::root(true).'/components/com_rsform/assets/css/front-rtl.css');
		$doc->addScript(JURI::root(true).'/components/com_rsform/assets/js/script.js');
		
		$calendars = RSFormProHelper::componentExists($formId, 6); //6 is the componentTypeId for calendar
		if(!empty($calendars))
		{
			$doc->addStyleSheet(JURI::root(true).'/components/com_rsform/assets/calendar/calendar.css');
			
			$hidden = JRequest::getVar('hidden');
			$all_data = RSFormProHelper::getComponentProperties($calendars);
			foreach($calendars as $i => $calendarComponentId)
			{
				$data = $all_data[$calendarComponentId];
				
				$calendars['CALENDARLAYOUT'][$i] = $data['CALENDARLAYOUT'];
				$calendars['DATEFORMAT'][$i] = $data['DATEFORMAT'];
				$calendars['VALUES'][$i] = '';
				$calendars['EXTRA'][$i] = array();
				if (!empty($hidden[$data['NAME']]))
					$calendars['VALUES'][$i] = preg_replace('#[^0-9\/]+#i', '', $hidden[$data['NAME']]);
				
				if (!empty($data['MINDATE']))
					$calendars['EXTRA'][$i][] = "'mindate': '".$data['MINDATE']."'";
				if (!empty($data['MAXDATE']))
					$calendars['EXTRA'][$i][] = "'maxdate': '".$data['MAXDATE']."'";
				
				$calendars['EXTRA'][$i] = '{'.implode(', ', $calendars['EXTRA'][$i]).'}';
			}
			unset($all_data);
			
			$calendarsLayout = "'".implode("','", $calendars['CALENDARLAYOUT'])."'";
			$calendarsFormat = "'".implode("','", $calendars['DATEFORMAT'])."'";
			$calendarsValues = "'".implode("','", $calendars['VALUES'])."'";
			$calendarsExtra  = implode(',', $calendars['EXTRA']);
		}
		
		$formLayout = $form->FormLayout;
		unset($form->FormLayout);
		$errorMessage = $form->ErrorMessage;
		unset($form->ErrorMessage);
		
		$db->setQuery("SELECT p.PropertyValue AS name, c.ComponentId, c.ComponentTypeId, ct.ComponentTypeName, c.Order FROM #__rsform_properties p LEFT JOIN #__rsform_components c ON (c.ComponentId=p.ComponentId) LEFT JOIN #__rsform_component_types ct ON (ct.ComponentTypeId=c.ComponentTypeId) WHERE c.FormId='".$formId."' AND p.PropertyName='NAME' AND c.Published='1' ORDER BY c.Order");
		$components = $db->loadObjectList();
		
		$pages			= array();
		$page_progress  = array();
		$submits		= array();
		foreach ($components as $component)
		{
			if ($component->ComponentTypeId == 41)
				$pages[] = $component->ComponentId;
			elseif ($component->ComponentTypeId == 13)
				$submits[] = $component->ComponentId;
		}
		
		$start_page = 0;
		if (!empty($validation))
			foreach ($components as $component)
			{
				if (in_array($component->ComponentId, $validation))
					break;
				if ($component->ComponentTypeId == 41)
					$start_page++;
			}
		
		$find 	  = array();
		$replace  = array();
		$all_data = RSFormProHelper::getComponentProperties($components);
		foreach ($components as $component)
		{
			$data = $all_data[$component->ComponentId];
			$data['componentTypeId'] = $component->ComponentTypeId;
			$data['ComponentTypeName'] = $component->ComponentTypeName;
			$data['Order'] = $component->Order;
			
			// Pagination
			if ($component->ComponentTypeId == 41)
			{
				$data['PAGES'] 	 = $pages;
				$page_progress[] = array('show' => @$data['DISPLAYPROGRESS'] == 'YES', 'text' => @$data['DISPLAYPROGRESSMSG']);
			}
			elseif ($component->ComponentTypeId == 13)
			{
				$data['SUBMITS'] = $submits;
				if ($component->ComponentId == end($submits))
					$page_progress[] = array('show' => @$data['DISPLAYPROGRESS'] == 'YES', 'text' => @$data['DISPLAYPROGRESSMSG']);
			}
			
			// Caption
			$find[] = '{'.$component->name.':caption}';
			$caption = '';
			if (isset($data['SHOW']) && $data['SHOW'] == 'NO')
				$caption = '';
			elseif (isset($data['CAPTION']))
				$caption = $data['CAPTION'];
			$replace[] = $caption;
			
			// Body	
			$find[] = '{'.$component->name.':body}';
			$replace[] = RSFormProHelper::getFrontComponentBody($formId, $component->ComponentId, $data, $val, in_array($component->ComponentId,$validation), $form->FormLayoutName);
			
			// Description
			$find[] = '{'.$component->name.':description}';
			$description = '';
			if (isset($data['SHOW']) && $data['SHOW'] == 'NO')
				$description = '';
			elseif (isset($data['DESCRIPTION']))
				$description = $data['DESCRIPTION'];
			$replace[] = $description;
			
			// Validation message
			$find[] = '{'.$component->name.':validation}';
			$validationMessage = '';
			if (isset($data['SHOW']) && $data['SHOW'] == 'NO')
				$validationMessage = '';
			elseif (isset($data['VALIDATIONMESSAGE']))
			{
				if(!empty($validation) && in_array($component->ComponentId,$validation))
					$validationMessage = '<span id="component'.$component->ComponentId.'" class="formError">'.$data['VALIDATIONMESSAGE'].'</span>';
				else
					$validationMessage = '<span id="component'.$component->ComponentId.'" class="formNoError">'.$data['VALIDATIONMESSAGE'].'</span>';
			}
			$replace[] = $validationMessage;
		}
		unset($all_data);
		
		$u = RSFormProHelper::getURL();
		
		//Trigger Event - onInitFormDisplay
		$mainframe->triggerEvent('rsfp_f_onInitFormDisplay',array(array('find'=>&$find,'replace'=>&$replace,'formLayout'=>&$formLayout)));
		
		$user = JFactory::getUser();
		$jconfig = JFactory::getConfig();
		array_push($find, '{global:formtitle}', '{global:username}', '{global:userip}', '{global:userid}', '{global:useremail}', '{global:fullname}', '{global:sitename}', '{global:siteurl}');
		array_push($replace, $form->FormTitle, $user->get('username'), isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '', $user->get('id'), $user->get('email'), $user->get('name'), $jconfig->get('sitename'), JURI::root());
		
		$formLayout = str_replace($find,$replace,$formLayout);
		
		if (strpos($formLayout, 'class="formError"') !== false)
			$formLayout = str_replace('{error}', $errorMessage, $formLayout);
		elseif ($form->AjaxValidation)
			$formLayout = str_replace('{error}', '<div id="rsform_error_'.$formId.'" style="display: none;">'.$errorMessage.'</div>', $formLayout);
		else
			$formLayout = str_replace('{error}', '', $formLayout);
		
		$formLayout.= '<input type="hidden" name="form[formId]" value="'.$formId.'"/>';
		
		if ($form->FormLayoutName == 'responsive')
		{
			$form->CSSClass .= ' formResponsive';
			if (RSFormProHelper::getConfig('auto_responsive'))
			{
				$doc->addCustomTag('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
				$mainframe->registerEvent('onAfterRender', '_modifyResponsiveTemplate');
			}
		}
		
		$CSSClass 	= $form->CSSClass ? ' class="'.RSFormProHelper::htmlEscape(trim($form->CSSClass)).'"' : '';
		$CSSId 		= $form->CSSId ? ' id="'.RSFormProHelper::htmlEscape(trim($form->CSSId)).'"' : '';
		$CSSName 	= $form->CSSName ? ' name="'.RSFormProHelper::htmlEscape(trim($form->CSSName)).'"' : '';
		$u 			= $form->CSSAction ? RSFormProHelper::htmlEscape($form->CSSAction) : $u;
		$CSSAdditionalAttributes = $form->CSSAdditionalAttributes ? ' '.trim($form->CSSAdditionalAttributes) : '';
		
		if (!empty($pages))
		{
			$total_pages 	  = count($pages)+1;
			$step			  = floor(100/$total_pages);
			$replace_progress = array('{page}', '{total}', '{percent}');
			$with_progress 	  = array(1, $total_pages, $step*1);
			
			$progress 		 = reset($page_progress);
			$progress_script = '';
			$formLayout = '<div id="rsform_progress_'.$formId.'" class="rsformProgress">'.($progress['show'] ? str_replace($replace_progress, $with_progress, $progress['text']) : '').'</div>'."\n".$formLayout;
			foreach ($page_progress as $p => $progress)
			{
				$progress['text'] = str_replace(array("\r", "\n"), array('', '\n'), addcslashes($progress['text'], "'"));
				$replace_progress = array('{page}', '{total}', '{percent}');
				$with_progress 	  = array($p+1, $total_pages, $p+1 == $total_pages ? 100 : $step*($p+1));
				$progress_script .= "if (page == ".$p.") document.getElementById('rsform_progress_".$formId."').innerHTML = '".($progress['show'] ? str_replace($replace_progress, $with_progress, $progress['text']) : '')."';";
			}
			$formLayout .= "\n".'<script type="text/javascript">'."\n".'function rsfp_showProgress_'.$formId.'(page) {'."\n".$progress_script."\n".'}'."\n".'</script>';
		}
		
		$formLayout = '<form method="post" '.$CSSId.$CSSClass.$CSSName.$CSSAdditionalAttributes.' enctype="multipart/form-data" action="'.RSFormProHelper::htmlEscape($u).'">'.$formLayout.'</form>';
		if(!empty($calendars))
		{
			$formLayout .= "\n".'<script type="text/javascript" src="'.JURI::root(true).'/components/com_rsform/assets/calendar/cal.js"></script>'."\n";
			$formLayout .= '<script type="text/javascript">'.RSFormProHelper::getCalendarJS().'</script>'."\n";
			$formLayout .= '<script type="text/javascript" defer="defer">rsf_CALENDAR.util.Event.addListener(window, "load", rsfp_init('.$formId.',{ layouts: Array('.$calendarsLayout.'), formats: Array('.$calendarsFormat.'), values: Array('.$calendarsValues.'), extra: Array('.$calendarsExtra.') }));</script>'."\n";
		}
		if (!empty($pages))
		{
			$formLayout .= '<script type="text/javascript" src="'.JURI::root(true).'/components/com_rsform/assets/js/pages.js"></script>'."\n";
			$formLayout .= '<script type="text/javascript">rsfp_changePage('.$formId.', '.$start_page.', '.count($pages).')</script>'."\n";
		}
		
		if ($form->AjaxValidation || !empty($pages))
			$formLayout .= '<script type="text/javascript">var rsfp_ajax_root = \''.addslashes(JURI::root(true)).'\'</script>';
		
		if ($form->AjaxValidation)
			$formLayout .= '<script type="text/javascript">rsfp_addEvent(window, \'load\', function(){var form = rsfp_getForm('.$formId.'); form.onsubmit = ajaxValidation;});</script>';
		
		$ajaxScript = '';
		$mainframe->triggerEvent('rsfp_f_onAJAXScriptCreate', array(array('script' => &$ajaxScript, 'formId' => $formId)));
		// has this been modified?
		if ($ajaxScript) {
			$formLayout .= '<script type="text/javascript">';
			$formLayout .= 'ajaxExtraValidationScript['.$formId.'] = function(task, formId, data) {';
			$formLayout .= $ajaxScript;
			$formLayout .= '}';
			$formLayout .= '</script>';
		}
		
		if ($conditions = RSFormProHelper::getConditions($formId))
		{
			$formLayout .= '<script type="text/javascript">';
			
			foreach ($conditions as $condition)
			{
				$formLayout .= "\n".'function rsfp_runCondition'.$condition->id.'(){';
					if ($condition->details)
					{
						$condition_vars = array();
						foreach ($condition->details as $detail)
						{
							$formLayout .= "\n"."isChecked = rsfp_verifyChecked(".$formId.", '".addslashes($detail->ComponentName)."', '".addslashes($detail->value)."');";
							$formLayout .= "\n"."condition".$detail->id." = isChecked == ".($detail->operator == 'is' ? 'true' : 'false').";";
							
							$condition_vars[] = "condition".$detail->id;
						}
						
						if ($condition->block)
						{
							$block 		= JFilterOutput::stringURLSafe($condition->ComponentName);
							$formLayout .= "\n"."items = rsfp_getBlock(".$formId.", '".addslashes($block)."');";
						}
						else
						{
							$formLayout .= "\n"."items = rsfp_getFieldsByName(".$formId.", '".addslashes($condition->ComponentName)."');";
						}
						
						$formLayout .= "\n"."if (items) {";
						$formLayout .= "\n"."if (".implode($condition->condition == 'all' ? '&&' : '||', $condition_vars).")";
						$formLayout .= "\n"."rsfp_setDisplay(items, '".($condition->action == 'show' ? '' : 'none')."');";
						$formLayout .= "\n".'else';
						$formLayout .= "\n"."rsfp_setDisplay(items, '".($condition->action == 'show' ? 'none' : '')."');";
						$formLayout .= "\n"."}";
					}
				$formLayout .= "\n".'}';
				$formLayout .= "\n".'rsfp_runCondition'.$condition->id.'();';
				if ($condition->details)
					foreach ($condition->details as $detail)
					{
						$formLayout .= "\n"."rsfp_addCondition(".$formId.", '".addslashes($detail->ComponentName)."', rsfp_runCondition".$condition->id.");";
					}
			}
			
			$formLayout .= "\n".'</script>';
		}
		
		$RSadapter = RSFormProHelper::getLegacyAdapter();
		eval($form->ScriptDisplay);
		
		//Trigger Event - onBeforeFormDisplay
		$mainframe->triggerEvent('rsfp_f_onBeforeFormDisplay', array(array('formLayout'=>&$formLayout,'formId'=>$formId)));
		return $formLayout;
	}
	
	public static function showThankYouMessage($formId)
	{
		$mainframe = JFactory::getApplication();
		
		$output = '';
		$formId = (int) $formId;		
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT ThemeParams FROM #__rsform_forms WHERE FormId='".$formId."'");
		$form = $db->loadObject();
		if ($form->ThemeParams)
			RSFormProHelper::loadTheme($form);
		
		$session = JFactory::getSession();
		$formparams = $session->get('com_rsform.formparams.'.$formId);
		$output = base64_decode($formparams->thankYouMessage);
		
		// Clear
		$session->clear('com_rsform.formparams.'.$formId);

		//Trigger Event - onAfterShowThankyouMessage
		$mainframe->triggerEvent('rsfp_f_onAfterShowThankyouMessage', array(array('output'=>&$output,'formId'=>&$formId)));
		
		// Cache enabled ?
		jimport('joomla.plugin.helper');
		$cache_enabled = JPluginHelper::isEnabled('system', 'cache');
		if ($cache_enabled)
			RSFormProHelper::cleanCache();
		
		return $output;
	}
	
	public static function processForm($formId)
	{
		$mainframe = JFactory::getApplication();
		
		$formId = (int) $formId;
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT `Keepdata`, `ConfirmSubmission`, `ScriptProcess`, `ScriptProcess2`, `UserEmailScript`, `AdminEmailScript`, `ReturnUrl`, `ShowThankyou`, `Thankyou`, `ShowContinue` FROM #__rsform_forms WHERE `FormId`='".$formId."'");
		$form = $db->loadObject();
		
		$lang 		  = RSFormProHelper::getCurrentLanguage();
		$translations = RSFormProHelper::getTranslations('forms', $formId, $lang);
		if ($translations)
			foreach ($translations as $field => $value)
			{
				if (isset($form->$field))
					$form->$field = $value;
			}
		
		$invalid = RSFormProHelper::validateForm($formId);
		
		$post = JRequest::getVar('form', array(), 'post', 'none', JREQUEST_ALLOWRAW);
		
		//Trigger Event - onBeforeFormValidation
		$mainframe->triggerEvent('rsfp_f_onBeforeFormValidation', array(array('invalid'=>&$invalid, 'formId' => $formId, 'post' => &$post)));
		
		$userEmail=array(
			'to'=>'',
			'cc'=>'',
			'bcc'=>'',
			'from'=>'',
			'replyto'=>'',
			'fromName'=>'',
			'text'=>'',
			'subject'=>'',
			'files' =>array()
			);
		$adminEmail=array(
			'to'=>'',
			'cc'=>'',
			'bcc'=>'',
			'from'=>'',
			'replyto'=>'',
			'fromName'=>'',
			'text'=>'',
			'subject'=>'',
			'files' =>array()
			);
		
		
		$_POST['form'] = $post;
		
		$RSadapter = RSFormProHelper::getLegacyAdapter();
		eval($form->ScriptProcess);
		
		if (!empty($invalid))
			return $invalid;
		
		$post = $_POST['form'];
		
		//Trigger Event - onBeforeFormProcess
		$mainframe->triggerEvent('rsfp_f_onBeforeFormProcess', array(array('post' => &$post)));
		
		if (empty($invalid))
		{
			// Cache enabled ?
			jimport('joomla.plugin.helper');
			$cache_enabled = JPluginHelper::isEnabled('system', 'cache');
			if ($cache_enabled)
				RSFormProHelper::cleanCache();
			
			$user = JFactory::getUser();
			
			$confirmsubmission = $form->ConfirmSubmission ? 0 : 1;
			
			// Add to db (submission)
			$date = JFactory::getDate();
			$db->setQuery("INSERT INTO #__rsform_submissions SET `FormId`='".$formId."', `DateSubmitted`='".$date->toSql()."', `UserIp`='".(isset($_SERVER['REMOTE_ADDR']) ? $db->escape($_SERVER['REMOTE_ADDR']) : '')."', `Username`='".$db->escape($user->get('username'))."', `UserId`='".(int) $user->get('id')."', `Lang`='".RSFormProHelper::getCurrentLanguage()."', `confirmed` = '".$confirmsubmission."' ");
			$db->execute();
			
			$SubmissionId = $db->insertid();
			
			$files = JRequest::get('files');
			if (isset($files['form']['tmp_name']) && is_array($files['form']['tmp_name']))
			{
				$names = array();
				foreach ($files['form']['tmp_name'] as $fieldName => $val)
				{
					if ($files['form']['error'][$fieldName]) continue;
						$names[] = $db->escape($fieldName);
				}
				$componentIds = array();
				if (!empty($names))
				{
					$db->setQuery("SELECT c.ComponentId, p.PropertyValue FROM #__rsform_components c LEFT JOIN #__rsform_properties p ON (c.ComponentId=p.ComponentId AND p.PropertyName='NAME') WHERE c.FormId='".$formId."' AND p.PropertyValue IN ('".implode("','", $names)."')");
					$results = $db->loadObjectList();
					
					foreach ($results as $result)
						$componentIds[$result->PropertyValue] = $result->ComponentId;
				}
				
				$all_data = RSFormProHelper::getComponentProperties($componentIds);
				
				jimport('joomla.filesystem.file');				
				foreach ($files['form']['tmp_name'] as $fieldName => $val)
				{
					if ($files['form']['error'][$fieldName]) continue;
					
					$data = @$all_data[$componentIds[$fieldName]];
					if (empty($data)) continue;
					
					// Prefix
					$prefix = uniqid('').'-';
					if (isset($data['PREFIX']) && strlen(trim($data['PREFIX'])) > 0)
						$prefix = RSFormProHelper::isCode($data['PREFIX']);
					
					// Path
					$realpath = realpath($data['DESTINATION'].DIRECTORY_SEPARATOR);
					if (substr($realpath, -1) != DIRECTORY_SEPARATOR)
						$realpath .= DIRECTORY_SEPARATOR;
					
					// Filename
					$file = $realpath.$prefix.$files['form']['name'][$fieldName];
					
					// Upload File
					JFile::upload($files['form']['tmp_name'][$fieldName], $file);
					
					// Add to db (submission value)
					$db->setQuery("INSERT INTO #__rsform_submission_values SET `SubmissionId`='".$SubmissionId."', `FormId`='".$formId."', `FieldName`='".$db->escape($fieldName)."', `FieldValue`='".$db->escape($file)."'");
					$db->execute();
					
					$emails = !empty($data['EMAILATTACH']) ? explode(',',$data['EMAILATTACH']) : array();					
					// Attach to user and admin email
					if (in_array('useremail',$emails))
						$userEmail['files'][] = $file;
					if (in_array('adminemail',$emails))
						$adminEmail['files'][] = $file;
				}
			}
			
			//Trigger Event - onBeforeStoreSubmissions
			$mainframe->triggerEvent('rsfp_f_onBeforeStoreSubmissions', array(array('formId'=>$formId,'post'=>&$post,'SubmissionId'=>$SubmissionId)));
			
			// Add to db (values)
			foreach ($post as $key => $val)
			{
				$val = is_array($val) ? implode("\n", $val) : $val;
				$val = RSFormProHelper::stripJava($val);
				
				$db->setQuery("INSERT INTO #__rsform_submission_values SET `SubmissionId`='".$SubmissionId."', `FormId`='".$formId."', `FieldName`='".$db->escape($key)."', `FieldValue`='".$db->escape($val)."'");
				$db->execute();
			}
			
			//Trigger Event - onAfterStoreSubmissions
			$mainframe->triggerEvent('rsfp_f_onAfterStoreSubmissions', array(array('SubmissionId'=>$SubmissionId, 'formId'=>$formId)));
			
			// Send emails
			list($replace, $with) = RSFormProHelper::sendSubmissionEmails($SubmissionId);
			
			// Thank You Message
			$thankYouMessage = str_replace($replace, $with, $form->Thankyou);
			$form->ReturnUrl = str_replace($replace, $with, $form->ReturnUrl);
			
			// Set redirect link
			$u = RSFormProHelper::getURL();
			
			// Create the Continue button
			$continueButton = '';
			if ($form->ShowContinue)
			{
				// Create goto link
				$goto = 'document.location.reload();';
				
				// Cache workaround #1
				if ($cache_enabled)
					$goto = "document.location='".addslashes($u)."';";
				
				if (!empty($form->ReturnUrl))
					$goto = "document.location='".addslashes($form->ReturnUrl)."';";
				
				// Continue button
				$continueButtonLabel = JText::_('RSFP_THANKYOU_BUTTON');
				if (strpos($continueButtonLabel, 'input'))
					$continueButton = JText::sprintf('RSFP_THANKYOU_BUTTON',$goto);
				else
					$continueButton = '<br/><input type="button" class="rsform-submit-button btn btn-primary" name="continue" value="'.JText::_('RSFP_THANKYOU_BUTTON').'" onclick="'.$goto.'"/>';
			}
			
			// get mappings data
			$db->setQuery("SELECT * FROM #__rsform_mappings WHERE formId = ".(int) $formId." ORDER BY ordering ASC");
			$mappings = $db->loadObjectList();
			
			// get Post to another location
			$db->setQuery("SELECT * FROM #__rsform_posts WHERE form_id='".(int) $formId."' AND enabled='1'");
			$silentPost = $db->loadObject();
			
			$RSadapter = RSFormProHelper::getLegacyAdapter();
			eval($form->ScriptProcess2);
			
			$thankYouMessage .= $continueButton;
			
			//Mappings
			if (!empty($mappings))
			{
				$lastinsertid = '';
				$replacewith = $with;
				array_walk($replacewith, array('RSFormProHelper', 'escapeSql'));
				
				foreach ($mappings as $mapping)
				{
					//get the query
					$query = RSFormProHelper::getMappingQuery($mapping);
					
					//replace the placeholders
					$query = str_replace($replace, $replacewith, $query);
					
					//replace the last insertid placeholder
					$query = str_replace('{last_insert_id}',$lastinsertid,$query);
					
					if ($mapping->connection)
					{
						$options = array(
							'driver' 	=> 'mysql',
							'host' 		=> $mapping->host,
							'user' 		=> $mapping->username,
							'password' 	=> $mapping->password,
							'database' 	=> $mapping->database
						);
						
						if (RSFormProHelper::isJ('3.0')) {
							$database = JDatabaseDriver::getInstance($options);
						} else {
							$database = JDatabase::getInstance($options);
						}
						
						//is a valid database connection
						if (is_a($database,'JException')) continue;
						
						$database->setQuery($query);
						$database->execute();
						$lastinsertid = $database->insertid();
						
					} else 
					{
						$db->setQuery($query);
						$db->execute();
						$lastinsertid = $db->insertid();
					}
				}
			}
			
			if (!$form->Keepdata)
			{
				$db->setQuery("DELETE FROM #__rsform_submission_values WHERE SubmissionId = ".(int) $SubmissionId." ");
				$db->execute();
				$db->setQuery("DELETE FROM #__rsform_submissions WHERE SubmissionId = ".(int) $SubmissionId." ");
				$db->execute();
			}
			
			if ($silentPost && !empty($silentPost->url) && $silentPost->url != 'http://')
			{
				// url
				$url = $silentPost->url;
				// set the variables to be sent
				// the format of the variables is var1=value1&var2=value2&var3=value3
				$data = array();
				foreach ($post as $key => $value)
				{
					if (is_array($value))
						foreach ($value as $post2 => $value2)
							$data[] = urlencode($key).'[]='.urlencode($value2);
					else
						$data[] = urlencode($key).'='.urlencode($value);
				}
				// do we need to post silently?
				if ($silentPost->silent)
				{
					$data = implode('&', $data);
					$params = array(
						'method' => $silentPost->method ? 'POST' : 'GET'
					);
					require_once dirname(__FILE__).'/connect.php';
					RSFormProConnect($url, $data, $params);
				}
				else
				{
					// just try to redirect
					if ($silentPost->method)
					{
						@ob_end_clean();
						
						// create form
						$output = array();
						$output[] = '<form id="formSubmit" method="POST" action="'.RSFormProHelper::htmlEscape($url).'">';
						foreach ($post as $key => $value)
						{
							if (is_array($value))
								foreach ($value as $post2 => $value2)
									$output[] = '<input type="hidden" name="'.RSFormProHelper::htmlEscape($key).'[]" value="'.RSFormProHelper::htmlEscape($value2).'" />';
							else
								$output[] = '<input type="hidden" name="'.RSFormProHelper::htmlEscape($key).'" value="'.RSFormProHelper::htmlEscape($value).'" />';
						}
						$output[] = '</form>';
						$output[] = '<script type="text/javascript">';
						$output[] = 'function formSubmit() { document.getElementById(\'formSubmit\').submit(); }';
						$output[] = 'try { window.addEventListener ? window.addEventListener("load",formSubmit,false) : window.attachEvent("onload",formSubmit); }';
						$output[] = 'catch (err) { formSubmit(); }';
						$output[] = '</script>';
						
						// echo form and submit it
						echo implode("\r\n", $output);
						die();
					}
					else
					{
						$data = implode('&', $data);
						$mainframe->redirect($url.(strpos($url, '?') === false ? '?' : '&').$data);
					}
				}
			}
			
			//Trigger - After form process
			$mainframe->triggerEvent('rsfp_f_onAfterFormProcess', array(array('SubmissionId'=>$SubmissionId,'formId'=>$formId)));
			
			if (!$form->ShowThankyou && $form->ReturnUrl)
			{
				$mainframe->redirect($form->ReturnUrl);
				return;
			}
			
			// SESSION quick hack - we base64 encode it here and decode it when we show it
			$session = JFactory::getSession();
			$formParams = new stdClass();
			$formParams->formProcessed = true;
			$formParams->submissionId = $SubmissionId;
			$formParams->thankYouMessage = base64_encode($thankYouMessage);
			$session->set('com_rsform.formparams.'.$formId, $formParams);
			
			// Cache workaround #2
			if ($cache_enabled)
			{
				$uniqid = uniqid('rsform');
				$u .= (strpos($u, '?') === false) ? '?skipcache='.$uniqid : '&skipcache='.$uniqid;
			}
			
			$mainframe->redirect($u);
		}

		return false;
	}
	
	public static function getURL()
	{
		// IIS hack
		if (RSFormProHelper::getConfig('global.iis') && !empty($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'IIS') !== false && !empty($_SERVER['QUERY_STRING']))
		{
			$u = JRoute::_('index.php?'.$_SERVER['QUERY_STRING'],false);
		}
		else
		{
			$uri = JUri::getInstance();
			$u = $uri->toString();
		}
		
		return $u;
	}
	
	public static function verifyChecked($componentName, $value, $post)
	{
		if (isset($post['form'][$componentName]))
		{
			if (is_array($post['form'][$componentName]) && in_array($value, $post['form'][$componentName]))
				return 1;
			
			if (!is_array($post['form'][$componentName]) && $post['form'][$componentName] == $value)
				return 1;
			
			return 0;
		}
		
		return 0;
	}
	
	public static function validateForm($formId)
	{
		require_once JPATH_SITE.'/components/com_rsform/helpers/validation.php';
		
		$mainframe  = JFactory::getApplication();
		$db 	 	= JFactory::getDBO();
		$invalid 	= array();
		$formId  	= (int) $formId;
		$post 	 	= JRequest::get('post', JREQUEST_ALLOWRAW);
		
		$db->setQuery("SELECT ComponentId, ComponentTypeId FROM #__rsform_components WHERE FormId='".$formId."' AND Published=1 ORDER BY `Order`");
		if ($components = $db->loadObjectList())
		{
			$componentIds = array();
			foreach ($components as $component)
				$componentIds[] = $component->ComponentId;		
			$all_data = RSFormProHelper::getComponentProperties($componentIds);
			if (empty($all_data))
				return $invalid;
			
			if ($conditions = RSFormProHelper::getConditions($formId))
			{
				foreach ($conditions as $condition)
				{
					if ($condition->details)
					{
						$condition_vars = array();
						foreach ($condition->details as $detail)
						{
							$isChecked 		  = RSFormProHelper::verifyChecked($detail->ComponentName, $detail->value, $post);
							$condition_vars[] = $detail->operator == 'is' ? $isChecked : !$isChecked;
						}
						// this check is performed like this
						// 'all' must be true (ie. no 0s in the array); 'any' can be true (ie. one value of 1 in the array will do)
						$result = $condition->condition == 'all'? !in_array(0, $condition_vars) : in_array(1, $condition_vars);
						
						// if the item is hidden, no need to validate it
						if (($condition->action == 'show' && !$result) || ($condition->action == 'hide' && $result))
							foreach ($components as $i => $component)
								if ($component->ComponentId == $condition->component_id)
								{
									// ... just remove it from the components array
									unset($components[$i]);
									break;
								}
					}
				}
			}
			
			foreach ($components as $component)
			{
				$data 			= $all_data[$component->ComponentId];
				$required 		= isset($data['REQUIRED']) ? $data['REQUIRED'] : 'NO';
				$validationRule = isset($data['VALIDATIONRULE']) ? $data['VALIDATIONRULE'] : '';
				$typeId 		= $component->ComponentTypeId;
				
				// CAPTCHA
				if ($typeId == 8)
				{
					$session = JFactory::getSession();
					$captchaCode = $session->get('com_rsform.captcha.'.$component->ComponentId);
					if ($data['IMAGETYPE'] == 'INVISIBLE')
					{
						$words = RSFormProHelper::getInvisibleCaptchaWords();
						if (!empty($post[$captchaCode]))
							$invalid[] = $data['componentId'];
						foreach ($words as $word)
							if (!empty($post[$word]))
								$invalid[] = $data['componentId'];
					}
					else
					{
						if (empty($post['form'][$data['NAME']]) || empty($captchaCode) || $post['form'][$data['NAME']] != $captchaCode)
							$invalid[] = $data['componentId'];
					}
				}
				
				// Trigger Event - rsfp_bk_validate_onSubmitValidateRecaptcha
				if ($typeId == 24)
					$mainframe->triggerEvent('rsfp_bk_validate_onSubmitValidateRecaptcha',array(array('data'=> &$data,'invalid'=> &$invalid)));
				
				if ($typeId == 9)
				{
					$files = JRequest::getVar('form', null, 'files');
					
					// File has been *sent* to the server
					if (isset($files['tmp_name'][$data['NAME']]) && $files['error'][$data['NAME']] != 4)
					{
						// File has been uploaded correctly to the server
						if ($files['error'][$data['NAME']] == 0)
						{
							// Let's check if the extension is allowed
							$buf = explode('.', $files['name'][$data['NAME']]);
							$m = '#'.preg_quote($buf[count($buf)-1]).'#';
							if (!empty($data['ACCEPTEDFILES']) && !preg_match(strtolower($m),strtolower($data['ACCEPTEDFILES'])))
								$invalid[] = $data['componentId'];
							// Let's check if it's the correct size
							if ($files['size'][$data['NAME']] > 0 && $data['FILESIZE'] > 0 && $files['size'][$data['NAME']] > $data['FILESIZE']*1024)
								$invalid[] = $data['componentId'];
						}
						// File has not been uploaded correctly - next version we'll trigger some messages based on the error code
						else
							$invalid[] = $data['componentId'];
					}
					// File has not been sent but it's required
					elseif($required == 'YES')
						$invalid[] = $data['componentId'];
					
					continue;
				}
				
				if ($required == 'YES')
				{
					if (!isset($post['form'][$data['NAME']]))
					{
						$invalid[] = $data['componentId'];
						continue;
					}
					if (!is_array($post['form'][$data['NAME']]) && strlen(trim($post['form'][$data['NAME']])) == 0)
					{
						$invalid[] = $data['componentId'];
						continue;
					}
					if (!is_array($post['form'][$data['NAME']]) && strlen(trim($post['form'][$data['NAME']])) > 0 && is_callable(array('RSFormProValidations', $validationRule)) && call_user_func(array('RSFormProValidations', $validationRule),$post['form'][$data['NAME']],isset($data['VALIDATIONEXTRA']) ? $data['VALIDATIONEXTRA'] : '',$data) == false)
					{
						$invalid[] = $data['componentId'];
						continue;
					}
					if (is_array($post['form'][$data['NAME']]))
					{
						$valid = implode('',$post['form'][$data['NAME']]);
						if(empty($valid))
						{
							$invalid[] = $data['componentId'];
							continue;
						}
					}
				}
				else
				{
					if (isset($post['form'][$data['NAME']]) && !is_array($post['form'][$data['NAME']]) && strlen(trim($post['form'][$data['NAME']])) > 0 && is_callable(array('RSFormProValidations', $validationRule)) && call_user_func(array('RSFormProValidations', $validationRule),$post['form'][$data['NAME']],isset($data['VALIDATIONEXTRA']) ? $data['VALIDATIONEXTRA'] : '', $data) == false)
					{
						$invalid[] = $data['componentId'];
						continue;
					}
				}
			}
		}
		return $invalid;
	}
	
	public static function getFrontComponentBody($formId, $componentId, $data, $value='', $invalid=false, $layoutName)
	{
		$mainframe = JFactory::getApplication();
		
		$formId = (int) $formId;
		$componentId = (int) $componentId;
		
		$db = JFactory::getDBO();
		
		// Optimized, don't need this anymore
		//$db->setQuery("SELECT `ComponentTypeId`, `Order` FROM #__rsform_components WHERE ComponentId='".$componentId."' LIMIT 1");
		//$r = $db->loadAssoc();
		
		// For legacy reasons...
		$r = array();
		$r['ComponentTypeId'] = $data['componentTypeId'];
		$r['Order'] = @$data['Order'];
		
		$out = '';
		
		//Trigger Event - rsfp_bk_onBeforeCreateFrontComponentBody
		$mainframe->triggerEvent('rsfp_bk_onBeforeCreateFrontComponentBody',array(array('out'=>&$out, 'formId'=>$formId, 'componentId'=>$componentId,'data'=>&$data,'value'=>&$value)));
		
		switch($data['ComponentTypeName'])
		{
			case 1:
			case 'textBox':
				$defaultValue = RSFormProHelper::isCode($data['DEFAULTVALUE']);
				
				$className = 'rsform-input-box';
				if ($invalid)
					$className .= ' rsform-error';
				RSFormProHelper::addClass($data['ADDITIONALATTRIBUTES'], $className);
				
				$out .= '<input type="text" value="'.(isset($value[$data['NAME']]) ? RSFormProHelper::htmlEscape($value[$data['NAME']]) : RSFormProHelper::htmlEscape($defaultValue)).'" size="'.$data['SIZE'].'" '.((int) $data['MAXSIZE'] > 0 ? 'maxlength="'.(int) $data['MAXSIZE'].'"' : '').' name="form['.$data['NAME'].']" id="'.$data['NAME'].'" '.$data['ADDITIONALATTRIBUTES'].'/>';
			break;

			case 2:
			case 'textArea':
				$defaultValue = RSFormProHelper::isCode($data['DEFAULTVALUE']);
				
				$className = 'rsform-text-box';
				if ($invalid)
					$className .= ' rsform-error';
				RSFormProHelper::addClass($data['ADDITIONALATTRIBUTES'], $className);
				
				if (isset($data['WYSIWYG']) && $data['WYSIWYG'] == 'YES')
				{
					$out .= RSFormProHelper::WYSIWYG('form['.$data['NAME'].']', (isset($value[$data['NAME']]) ? RSFormProHelper::htmlEscape($value[$data['NAME']]) : RSFormProHelper::htmlEscape($defaultValue)), 'id['.$data['NAME'].']', $data['COLS']*10, $data['ROWS']*10, $data['COLS'], $data['ROWS']);
				}
				else
					$out .= '<textarea cols="'.(int) $data['COLS'].'" rows="'.(int) $data['ROWS'].'" name="form['.$data['NAME'].']" id="'.$data['NAME'].'" '.$data['ADDITIONALATTRIBUTES'].'>'.(isset($value[$data['NAME']]) ? RSFormProHelper::htmlEscape($value[$data['NAME']]) : RSFormProHelper::htmlEscape($defaultValue)).'</textarea>';
			break;

			case 3:
			case 'selectList':
				$className = 'rsform-select-box';
				if ($invalid)
					$className .= ' rsform-error';
				RSFormProHelper::addClass($data['ADDITIONALATTRIBUTES'], $className);
				
				$out .= '<select '.($data['MULTIPLE']=='YES' ? 'multiple="multiple"' : '').' name="form['.$data['NAME'].'][]" '.((int) $data['SIZE'] > 0 ? 'size="'.(int) $data['SIZE'].'"' : '').' id="'.$data['NAME'].'" '.$data['ADDITIONALATTRIBUTES'].' >';
				
				$items = RSFormProHelper::explode(RSFormProHelper::isCode($data['ITEMS']));
				
				$special = array('[c]', '[g]', '[d]');
				
				foreach ($items as $item)
				{
					@list($val, $txt) = @explode('|', str_replace($special, '', $item), 2);
					if (is_null($txt))
						$txt = $val;
					
					// <optgroup>
					if (strpos($item, '[g]') !== false) {
						$out .= '<optgroup label="'.RSFormProHelper::htmlEscape($val).'">';
						continue;
					}
					// </optgroup>
					if(strpos($item, '[/g]') !== false) {
						$out .= '</optgroup>';
						continue;
					}
					
					$additional = '';
					// selected
					if ((strpos($item, '[c]') !== false && empty($value)) || (isset($value[$data['NAME']]) && in_array($val, $value[$data['NAME']])))
						$additional .= 'selected="selected"';
					// disabled
					if (strpos($item, '[d]') !== false)
						$additional .= 'disabled="disabled"';
					
					$out .= '<option '.$additional.' value="'.RSFormProHelper::htmlEscape($val).'">'.RSFormProHelper::htmlEscape($txt).'</option>';
				}
				$out .= '</select>';
				
				$option = JRequest::getCmd('option');
				$view 	= JRequest::getCmd('view');
				$layout = JRequest::getCmd('layout');
				if ($option == 'com_rsevents' && $view == 'events' && $layout == 'subscribe' && $data['NAME'] == 'RSEventsTickets')
				{
					$db->setQuery("SELECT ConfigValue FROM #__rsevents_config WHERE ConfigName = 'event.multiple.tickets' ");
					$multipleTickets = $db->loadResult();
					
					if ($multipleTickets)
					{
						$lang = JFactory::getLanguage();
						$lang->load('com_rsevents', JPATH_SITE);
						$out .= ' <a onclick="add_ticket(1,\''.JText::_('RSE_REMOVE_TICKET',true).'\');" href="javascript:void(0)">'.JText::_('RSE_ADD_TICKET').'</a> ';

						$out .= '<br /><span id="tickets"></span>';
						$out .= '<span id="rse_tickets_ids"></span>'."\n";
						$out .= '<span id="rse_tickets_no"></span>'."\n";
					}
				}
			break;
			
			case 4:
			case 'checkboxGroup':
				$i = 0;
				
				$items = RSFormProHelper::explode(RSFormProHelper::isCode($data['ITEMS']));
				
				$special = array('[c]', '[d]');
				
				foreach ($items as $item)
				{
					@list($val, $txt) = @explode('|', str_replace($special, '', $item), 2);
					if (is_null($txt))
						$txt = $val;
					
					$additional = '';
					// checked
					if ((strpos($item, '[c]') !== false && empty($value)) || (isset($value[$data['NAME']]) && in_array($val, $value[$data['NAME']])))
						$additional .= 'checked="checked"';
					// disabled
					if (strpos($item, '[d]') !== false)
						$additional .= 'disabled="disabled"';
					
					if ($data['FLOW']=='VERTICAL' && $layoutName == 'responsive')
						$out .= '<p class="rsformVerticalClear">';
					$out .= '<input '.$additional.' name="form['.$data['NAME'].'][]" type="checkbox" value="'.RSFormProHelper::htmlEscape($val).'" id="'.$data['NAME'].$i.'" '.$data['ADDITIONALATTRIBUTES'].' /><label for="'.$data['NAME'].$i.'">'.$txt.'</label>';
					if ($data['FLOW']=='VERTICAL')
					{
						if ($layoutName == 'responsive')
							$out .= '</p>';
						else
							$out .= '<br />';
					}	
					$i++;
				}
			break;
			
			case 5:
			case 'radioGroup':
				$i = 0;
				
				$items = RSFormProHelper::explode(RSFormProHelper::isCode($data['ITEMS']));
				
				$special = array('[c]', '[d]');
				
				foreach ($items as $item)
				{
					@list($val, $txt) = @explode('|', str_replace($special, '', $item), 2);
					if (is_null($txt))
						$txt = $val;
						
					$additional = '';
					// checked
					if ((strpos($item, '[c]') !== false && empty($value)) || (isset($value[$data['NAME']]) && $val == $value[$data['NAME']]))
						$additional .= 'checked="checked"';
					// disabled
					if (strpos($item, '[d]') !== false)
						$additional .= 'disabled="disabled"';
					
					if ($data['FLOW']=='VERTICAL' && $layoutName == 'responsive')
						$out .= '<p class="rsformVerticalClear">';
					$out .= '<input '.$additional.' name="form['.$data['NAME'].']" type="radio" value="'.RSFormProHelper::htmlEscape($val).'" id="'.$data['NAME'].$i.'" '.$data['ADDITIONALATTRIBUTES'].' /><label for="'.$data['NAME'].$i.'">'.$txt.'</label>';
					if ($data['FLOW']=='VERTICAL')
					{
						if ($layoutName == 'responsive')
							$out .= '</p>';
						else
							$out .= '<br />';
					}
					$i++;
				}
			break;
			
			case 6:
			case 'calendar':
				$calendars = RSFormProHelper::componentExists($formId, 6);
				$calendars = array_flip($calendars);
				
				$defaultValue = isset($value[$data['NAME']]) ? $value[$data['NAME']] : (isset($data['DEFAULTVALUE']) ? RSFormProHelper::isCode($data['DEFAULTVALUE']) : '');
				
				switch($data['CALENDARLAYOUT'])
				{
					case 'FLAT':						
						$className = 'rsform-calendar-box';
						if ($invalid)
							$className .= ' rsform-error';
				
						$out .= '<input id="txtcal'.$formId.'_'.$calendars[$componentId].'" name="form['.$data['NAME'].']" type="text" '.($data['READONLY'] == 'YES' ? 'readonly="readonly"' : '').' class="txtCal '.$className.'" value="'.RSFormProHelper::htmlEscape($defaultValue).'" '.$data['ADDITIONALATTRIBUTES'].'/><br />';
						$out .= '<div id="cal'.$formId.'_'.$calendars[$componentId].'Container" style="z-index:'.(9999-$data['Order']).'"></div>';
					break;

					case 'POPUP':
						$data['ADDITIONALATTRIBUTES2'] = $data['ADDITIONALATTRIBUTES'];
						
						$className = 'rsform-calendar-box';
						if ($invalid)
							$className .= ' rsform-error';
						RSFormProHelper::addClass($data['ADDITIONALATTRIBUTES'], $className);
						
						$out .= '<input id="txtcal'.$formId.'_'.$calendars[$componentId].'" name="form['.$data['NAME'].']" type="text" '.($data['READONLY'] == 'YES' ? 'readonly="readonly"' : '').'  value="'.RSFormProHelper::htmlEscape($defaultValue).'" '.$data['ADDITIONALATTRIBUTES'].'/>';
						
						$className = 'rsform-calendar-button';
						if ($invalid)
							$className .= ' rsform-error';
						
						$out .= '<input id="btn'.$formId.'_'.$calendars[$componentId].'" type="button" value="'.RSFormProHelper::htmlEscape($data['POPUPLABEL']).'" onclick="showHideCalendar(\'cal'.$formId.'_'.$calendars[$componentId].'Container\');" class="btnCal '.$className.'" '.$data['ADDITIONALATTRIBUTES2'].' />';
						$out .= '<div id="cal'.$formId.'_'.$calendars[$componentId].'Container" style="clear:both;display:none;position:absolute;z-index:'.(9999-$data['Order']).'"></div>';
					break;
				}
				
				$out .= '<input id="hiddencal'.$formId.'_'.$calendars[$componentId].'" type="hidden" name="hidden['.$data['NAME'].']" />';
			break;
			
			case 7:
			case 'button':
				$button_type = (isset($data['BUTTONTYPE']) && $data['BUTTONTYPE'] == 'TYPEBUTTON') ? 'button' : 'input';
				$data['ADDITIONALATTRIBUTES2'] = $data['ADDITIONALATTRIBUTES'];
				
				$className = 'rsform-button';
				if ($invalid)
					$className .= ' rsform-error';
				RSFormProHelper::addClass($data['ADDITIONALATTRIBUTES'], $className);
				
				if ($button_type == 'button')
					$out .= '<button type="button" name="form['.$data['NAME'].']" id="'.$data['NAME'].'" '.$data['ADDITIONALATTRIBUTES'].'>'.RSFormProHelper::htmlEscape($data['LABEL']).'</button>';
				else
					$out .= '<input type="button" value="'.RSFormProHelper::htmlEscape($data['LABEL']).'" name="form['.$data['NAME'].']" id="'.$data['NAME'].'" '.$data['ADDITIONALATTRIBUTES'].' />';
				if ($data['RESET']=='YES')
				{
					$className = 'rsform-reset-button';
					RSFormProHelper::addClass($data['ADDITIONALATTRIBUTES2'], $className);
					
					if ($button_type == 'button')
						$out .= '&nbsp;&nbsp;<button type="reset" name="form['.$data['NAME'].']" id="'.$data['NAME'].'" '.$data['ADDITIONALATTRIBUTES2'].'>'.RSFormProHelper::htmlEscape($data['RESETLABEL']).'</button>';
					else
						$out .= '&nbsp;&nbsp;<input type="reset" value="'.RSFormProHelper::htmlEscape($data['RESETLABEL']).'" name="form['.$data['NAME'].']" id="'.$data['NAME'].'" '.$data['ADDITIONALATTRIBUTES2'].' />';
				}
			break;
			
			case 8:
			case 'captcha':
				switch (@$data['IMAGETYPE'])
				{
					default:
					case 'FREETYPE':
					case 'NOFREETYPE':
						$className = 'rsform-captcha-box';
						if ($invalid)
							$className .= ' rsform-error';
						RSFormProHelper::addClass($data['ADDITIONALATTRIBUTES'], $className);
				
						$out .= '<img src="'.JRoute::_('index.php?option=com_rsform&amp;task=captcha&amp;componentId='.$componentId.'&amp;tmpl=component&amp;sid='.mt_rand()).'" id="captcha'.$componentId.'" alt="'.RSFormProHelper::htmlEscape($data['CAPTION']).' "/>';
						if ($data['FLOW'] == 'VERTICAL')
							$out .= '<br />';
						$out .= '<input type="text" name="form['.$data['NAME'].']" value="" id="captchaTxt'.$componentId.'" '.$data['ADDITIONALATTRIBUTES'].' />';
						if ($data['SHOWREFRESH']=='YES')
							$out .= '&nbsp;&nbsp;<a href="javascript:void(0)" onclick="refreshCaptcha('.$componentId.',\''.JRoute::_('index.php?option=com_rsform&task=captcha&componentId='.$componentId.'&tmpl=component').'\'); return false;">'.$data['REFRESHTEXT'].'</a>';
					break;
					
					case 'INVISIBLE':
						// a list of words that spam bots might auto-complete
						$words = RSFormProHelper::getInvisibleCaptchaWords();
						$word = $words[array_rand($words, 1)];
						
						// a list of styles so that the field is hidden
						$styles = array('display: none !important', 'position: absolute !important; left: -4000px !important; top: -4000px !important;', 'position: absolute !important; left: -4000px !important; top: -4000px !important; display: none !important', 'position: absolute !important; display: none !important', 'display: none !important; position: absolute !important; left: -4000px !important; top: -4000px !important;');
						$style = $styles[array_rand($styles, 1)];
						
						// now we're going to shuffle the properties of the html tag
						$properties = array('type="text"', 'name="'.$word.'"', 'value=""', 'style="'.$style.'"');
						shuffle($properties);
						
						$session = JFactory::getSession();
						$session->set('com_rsform.captcha.'.$componentId, $word);
						
						$out .= '<input '.implode(' ', $properties).' />';
					break;
				}
			break;
			
			case 9:
			case 'fileUpload':
				$className = 'rsform-upload-box';
				if ($invalid)
					$className .= ' rsform-error';
				RSFormProHelper::addClass($data['ADDITIONALATTRIBUTES'], $className);
				
				$out .= '<input type="hidden" name="MAX_FILE_SIZE" value="'.(int) $data['FILESIZE'].'000" />';
				$out .= '<input type="file" name="form['.$data['NAME'].']" id="'.$data['NAME'].'" '.$data['ADDITIONALATTRIBUTES'].' />';
			break;
			
			case 10:
			case 'freeText':
				$out .= $data['TEXT'];
			break;
			
			case 11:
			case 'hidden':
				$defaultValue = RSFormProHelper::isCode($data['DEFAULTVALUE']);
				$out .= '<input type="hidden" name="form['.$data['NAME'].']" id="'.$data['NAME'].'" value="'.RSFormProHelper::htmlEscape($defaultValue).'" '.$data['ADDITIONALATTRIBUTES'].' />';
			break;
			
			case 12:
			case 'imageButton':
				$data['ADDITIONALATTRIBUTES2'] = $data['ADDITIONALATTRIBUTES'];
				
				$className = 'rsform-image-button';
				RSFormProHelper::addClass($data['ADDITIONALATTRIBUTES'], $className);
				
				$data['ADDITIONALATTRIBUTES3'] = $data['ADDITIONALATTRIBUTES'];
				
				$pages = RSFormProHelper::componentExists($formId, 41);
				$pages = count($pages);
				if (!empty($pages))
				{
					if (empty($data['PREVBUTTON']))
						$data['PREVBUTTON'] = JText::_('PREV');
					
					$onclick = 'rsfp_changePage('.$formId.', '.($pages-1).', '.$pages.')';
					RSFormProHelper::addOnClick($data['ADDITIONALATTRIBUTES3'], $onclick);
					
					$out .= '<input type="button" value="'.RSFormProHelper::htmlEscape($data['PREVBUTTON']).'"  id="'.$data['NAME'].'Prev" '.$data['ADDITIONALATTRIBUTES3'].' />';
				}
				
				$out .= '<input type="image" src="'.RSFormProHelper::htmlEscape($data['IMAGEBUTTON']).'" name="form['.$data['NAME'].']" id="'.$data['NAME'].'" '.$data['ADDITIONALATTRIBUTES2'].' />';
				if ($data['RESET']=='YES')
				{
					$className = 'rsform-reset-button';
					RSFormProHelper::addClass($data['ADDITIONALATTRIBUTES2'], $className);
					
					$out .= '<input type="reset" name="" id="reset_'.$data['NAME'].'" style="display: none !important" />&nbsp;&nbsp;<input onclick="document.getElementById(\'reset_'.$data['NAME'].'\').click();return false;" type="image" src="'.RSFormProHelper::htmlEscape($data['IMAGERESET']).'" name="form['.$data['NAME'].']" '.$data['ADDITIONALATTRIBUTES2'].' />';
				}
			break;
			
			case 13:
			case 'submitButton':
				$button_type = (isset($data['BUTTONTYPE']) && $data['BUTTONTYPE'] == 'TYPEBUTTON') ? 'button' : 'input';
				
				$data['ADDITIONALATTRIBUTES2'] = $data['ADDITIONALATTRIBUTES'];
				
				$className = 'rsform-submit-button';
				RSFormProHelper::addClass($data['ADDITIONALATTRIBUTES'], $className);
				
				$data['ADDITIONALATTRIBUTES3'] = $data['ADDITIONALATTRIBUTES'];
				
				$last_submit = $componentId == end($data['SUBMITS']);
				$pages = RSFormProHelper::componentExists($formId, 41);
				$pages = count($pages);
				if (!empty($pages) && $last_submit)
				{
					if (empty($data['PREVBUTTON']))
						$data['PREVBUTTON'] = JText::_('PREV');
					
					$onclick = 'rsfp_changePage('.$formId.', '.($pages-1).', '.$pages.')';
					RSFormProHelper::addOnClick($data['ADDITIONALATTRIBUTES3'], $onclick);
					
					if ($button_type == 'button')
						$out .= '<button type="button" id="'.$data['NAME'].'Prev" '.$data['ADDITIONALATTRIBUTES3'].'>'.RSFormProHelper::htmlEscape($data['PREVBUTTON']).'</button>';
					else
						$out .= '<input type="button" value="'.RSFormProHelper::htmlEscape($data['PREVBUTTON']).'"  id="'.$data['NAME'].'Prev" '.$data['ADDITIONALATTRIBUTES3'].' />';
				}
				if ($button_type == 'button')
					$out .= '<button type="submit" name="form['.$data['NAME'].']" id="'.$data['NAME'].'" '.$data['ADDITIONALATTRIBUTES'].'>'.RSFormProHelper::htmlEscape($data['LABEL']).'</button>';
				else
					$out .= '<input type="submit" value="'.RSFormProHelper::htmlEscape($data['LABEL']).'" name="form['.$data['NAME'].']" id="'.$data['NAME'].'" '.$data['ADDITIONALATTRIBUTES'].' />';
				if ($data['RESET']=='YES')
				{
					$className = 'rsform-reset-button';
					RSFormProHelper::addClass($data['ADDITIONALATTRIBUTES2'], $className);
					
					if ($button_type == 'button')
						$out .= '&nbsp;&nbsp;<button type="reset" name="form['.$data['NAME'].']" '.$data['ADDITIONALATTRIBUTES2'].'>'.RSFormProHelper::htmlEscape($data['RESETLABEL']).'</button>';
					else
						$out .= '&nbsp;&nbsp;<input type="reset" value="'.RSFormProHelper::htmlEscape($data['RESETLABEL']).'" name="form['.$data['NAME'].']" '.$data['ADDITIONALATTRIBUTES2'].' />';
				}
			break;
			
			case 14:
			case 'password':
				$defaultValue = '';
				if (isset($data['VALIDATIONRULE']) && $data['VALIDATIONRULE'] != 'password')
					$defaultValue = $data['DEFAULTVALUE'];
				
				$className = 'rsform-password-box';
				if ($invalid)
					$className .= ' rsform-error';
				RSFormProHelper::addClass($data['ADDITIONALATTRIBUTES'], $className);
				
				$out .= '<input type="password" value="'.RSFormProHelper::htmlEscape($defaultValue).'" size="'.(int) $data['SIZE'].'" name="form['.$data['NAME'].']" id="'.$data['NAME'].'" '.((int) $data['MAXSIZE'] > 0 ? 'maxlength="'.(int) $data['MAXSIZE'].'"' : '').' '.$data['ADDITIONALATTRIBUTES'].' />';
			break;
			
			case 15:
			case 'ticket':
				$out .= '<input type="hidden" name="form['.$data['NAME'].']" value="'.RSFormProHelper::generateString($data['LENGTH'],$data['CHARACTERS']).'" '.$data['ADDITIONALATTRIBUTES'].' />';
			break;
			
			case 41:
			case 'pageBreak':
				$validate = 'false';
				if (isset($data['VALIDATENEXTPAGE']) && $data['VALIDATENEXTPAGE'] == 'YES')
					$validate = 'true';
				
				$className = 'rsform-button';
				if ($invalid)
					$className .= ' rsform-error';
				RSFormProHelper::addClass($data['ADDITIONALATTRIBUTES'], $className);
				
				$data['ADDITIONALATTRIBUTES2'] = $data['ADDITIONALATTRIBUTES'];
				
				$num = count($data['PAGES']);
				$pos = array_search($componentId, $data['PAGES']);
				if ($pos)
				{
					$onclick = 'rsfp_changePage('.$formId.', '.($pos-1).', '.$num.')';
					RSFormProHelper::addOnClick($data['ADDITIONALATTRIBUTES'], $onclick);
					
					$out .= '<input type="button" value="'.RSFormProHelper::htmlEscape($data['PREVBUTTON']).'" '.$data['ADDITIONALATTRIBUTES'].' id="'.$data['NAME'].'Prev" />';
				}
				
				if ($pos < count($data['PAGES']))
				{
					$onclick = 'rsfp_changePage('.$formId.', '.($pos+1).', '.$num.', '.$validate.')';
					RSFormProHelper::addOnClick($data['ADDITIONALATTRIBUTES2'], $onclick);
					
					$out .= '<input type="button" value="'.RSFormProHelper::htmlEscape($data['NEXTBUTTON']).'" '.$data['ADDITIONALATTRIBUTES2'].' id="'.$data['NAME'].'Next" />';
				}
			break;
			
			case 32:
			case 'rseprotickets':
				
				$html = '';
				if (JRequest::getCmd('option') == 'com_rseventspro')
				{
					$cid = JRequest::getInt('cid');
					$db->setQuery("SELECT COUNT(id) FROM #__rseventspro_tickets WHERE ide = ".$cid."");
					$eventtickets = $db->loadResult();
					
					$html .= '<input type="text" id="numberinp" name="numberinp" value="1" size="3" style="display: none;" onkeyup="this.value=this.value.replace(/[^0-9\.\,]/g, \'\');" />';
					$html .= '<select name="number" id="number"><option value="1">1</option></select> ';
					
					$className = 'rsform-select-box';
					if ($invalid)
						$className .= ' rsform-error';
					RSFormProHelper::addClass($data['ADDITIONALATTRIBUTES'], $className);
					
					$html .= '<select name="form['.$data['NAME'].']" id="'.$data['NAME'].'" '.$data['ADDITIONALATTRIBUTES'].' >';
					$items = RSFormProHelper::explode(RSFormProHelper::isCode($data['ITEMS']));
					$special = array('[c]', '[g]', '[d]');
					foreach ($items as $item)
					{
						@list($val, $txt) = @explode('|', str_replace($special, '', $item), 2);
						if (is_null($txt))
							$txt = $val;
							
						// <optgroup>
						if (strpos($item, '[g]') !== false) {
							$out .= '<optgroup label="'.RSFormProHelper::htmlEscape($val).'">';
							continue;
						}
						// </optgroup>
						if(strpos($item, '[/g]') !== false) {
							$out .= '</optgroup>';
							continue;
						}
						
						$additional = '';
						// selected
						if ((strpos($item, '[c]') !== false && empty($value)) || (isset($value[$data['NAME']]) && $val == $value[$data['NAME']]))
							$additional .= 'selected="selected"';
						// disabled
						if (strpos($item, '[d]') !== false)
							$additional .= 'disabled="disabled"';
						
						$html .= '<option '.$additional.' value="'.RSFormProHelper::htmlEscape($val).'">'.RSFormProHelper::htmlEscape($txt).'</option>';
					}
					$html .= '</select>';
					
					if (JRequest::getCmd('option') == 'com_rseventspro' && JRequest::getCmd('layout') == 'subscribe')
					{
						$db->setQuery("SELECT `value` FROM `#__rseventspro_config` WHERE `name` = 'multi_tickets'");
						$multipleTickets = $db->loadResult();
						
						if ($multipleTickets)
						{
							$lang = JFactory::getLanguage();
							$lang->load('com_rseventspro', JPATH_SITE);
							$html .= ' <a href="javascript:void(0);" onclick="rs_add_ticket();">'.JText::_('RSEPRO_SUBSCRIBER_ADD_TICKET').'</a> ';
						}
					}
					
					$html .= ' <img id="rs_loader" src="'.JURI::root().'components/com_rseventspro/assets/images/loader.gif" alt="" style="vertical-align: middle; display: none;" />';
					
					if (JRequest::getCmd('option') == 'com_rseventspro' && JRequest::getCmd('layout') == 'subscribe' && $multipleTickets)
					{
						$html .= '<br /> <br /> <span id="tickets"></span>';
						$html .= '<span id="hiddentickets"></span>';
					}
					
					$html .= ' <br /> <span id="tdescription"></span>';
					$html .= '<input type="hidden" name="from" id="from" value="" />';
					
					if (!empty($eventtickets))
						$out .= $html;
				}
			break;
		}
		
		//Trigger Event - rsfp_bk_onAfterCreateFrontComponentBody
		$mainframe->triggerEvent('rsfp_bk_onAfterCreateFrontComponentBody',array(array('out'=>&$out, 'formId'=>$formId, 'componentId'=>$componentId,'data'=>$data,'value'=>$value,'r'=>$r, 'invalid' => $invalid)));
		return $out;
	}
	
	public static function addClass(&$attributes, $className)
	{
		if (preg_match('#class="(.*?)"#is', $attributes, $matches))
			$attributes = str_replace($matches[0], str_replace($matches[1], $matches[1].' '.$className, $matches[0]), $attributes);
		else
			$attributes .= ' class="'.$className.'"';
		
		return $attributes;
	}
	
	public static function addOnClick(&$attributes, $onClick)
	{
		if (preg_match('#onclick="(.*?)"#is', $attributes, $matches))
			$attributes = str_replace($matches[0], str_replace($matches[1], $matches[1].'; '.$onClick, $matches[0]), $attributes);
		else
			$attributes .= ' onclick="'.$onClick.'"';
		
		return $attributes;
	}
	
	public static function getInvisibleCaptchaWords()
	{
		return array('Website', 'Email', 'Name', 'Address', 'User', 'Username', 'Comment', 'Message');
	}
	
	public static function generateString($length, $characters, $type='Random')
	{
		$length = (int) $length;
		if($type == 'Random')
		{
			switch($characters)
			{
				case 'ALPHANUMERIC':
				default:
					$possible = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;
				case 'ALPHA':
					$possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;
				case 'NUMERIC':
					$possible = '0123456789';
				break;
			}

			if($length<1||$length>255) $length = 8;
			  $key = '';
			  $i = 0;
			  while ($i < $length) {
				$key .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
				$i++;
			  }
		}
		if($type == 'Sequential')
		{
			$key = 0;
		}
		return $key;
	}
	
	// todo - use Joomla! string functions # done for now
	// optimize to ignore false alerts
	public static function stripJava($val)
	{
		static $filter;
		if (is_null($filter))
		{
			jimport('joomla.filter.filterinput');
			$filter = JFilterInput::getInstance(array('form', 'input', 'select', 'textarea'), array('style'), 1, 1);
		}
		
	    $val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', "", $val);
		$val = str_replace("\0", "", $val);
		
		return $filter->clean($val);
		
	   // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
	   // this prevents some character re-spacing such as <java\0script>
	   // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
	   $val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);

	   // straight replacements, the user should never need these since they're normal characters
	   // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
	   $search = 'abcdefghijklmnopqrstuvwxyz';
	   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	   $search .= '1234567890!@#$%^&*()';
	   $search .= '~`";:?+/={}[]-_|\'\\';
	   for ($i = 0; $i < strlen($search); $i++) {
		  // ;? matches the ;, which is optional
		  // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

		  // &#x0040 @ search for the hex values
		  $val = preg_replace('/(&#[x|X]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
		  // &#00064 @ 0{0,7} matches '0' zero to seven times
		  $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
	   }

	   // now the only remaining whitespace attacks are \t, \n, and \r
	   // ([ \t\r\n]+)?
	   $ra1 = Array('\/([ \t\r\n]+)?javascript', '\/([ \t\r\n]+)?vbscript', ':([ \t\r\n]+)?expression', '<([ \t\r\n]+)?applet', '<([ \t\r\n]+)?meta', '<([ \t\r\n]+)?xml', '<([ \t\r\n]+)?blink', '<([ \t\r\n]+)?link', '<([ \t\r\n]+)?style', '<([ \t\r\n]+)?script', '<([ \t\r\n]+)?embed', '<([ \t\r\n]+)?object', '<([ \t\r\n]+)?iframe', '<([ \t\r\n]+)?frame', '<([ \t\r\n]+)?frameset', '<([ \t\r\n]+)?ilayer', '<([ \t\r\n]+)?layer', '<([ \t\r\n]+)?bgsound', '<([ \t\r\n]+)?title', '<([ \t\r\n]+)?base');
	   $ra2 = Array('onabort([ \t\r\n]+)?=', 'onactivate([ \t\r\n]+)?=', 'onafterprint([ \t\r\n]+)?=', 'onafterupdate([ \t\r\n]+)?=', 'onbeforeactivate([ \t\r\n]+)?=', 'onbeforecopy([ \t\r\n]+)?=', 'onbeforecut([ \t\r\n]+)?=', 'onbeforedeactivate([ \t\r\n]+)?=', 'onbeforeeditfocus([ \t\r\n]+)?=', 'onbeforepaste([ \t\r\n]+)?=', 'onbeforeprint([ \t\r\n]+)?=', 'onbeforeunload([ \t\r\n]+)?=', 'onbeforeupdate([ \t\r\n]+)?=', 'onblur([ \t\r\n]+)?=', 'onbounce([ \t\r\n]+)?=', 'oncellchange([ \t\r\n]+)?=', 'onchange([ \t\r\n]+)?=', 'onclick([ \t\r\n]+)?=', 'oncontextmenu([ \t\r\n]+)?=', 'oncontrolselect([ \t\r\n]+)?=', 'oncopy([ \t\r\n]+)?=', 'oncut([ \t\r\n]+)?=', 'ondataavailable([ \t\r\n]+)?=', 'ondatasetchanged([ \t\r\n]+)?=', 'ondatasetcomplete([ \t\r\n]+)?=', 'ondblclick([ \t\r\n]+)?=', 'ondeactivate([ \t\r\n]+)?=', 'ondrag([ \t\r\n]+)?=', 'ondragend([ \t\r\n]+)?=', 'ondragenter([ \t\r\n]+)?=', 'ondragleave([ \t\r\n]+)?=', 'ondragover([ \t\r\n]+)?=', 'ondragstart([ \t\r\n]+)?=', 'ondrop([ \t\r\n]+)?=', 'onerror([ \t\r\n]+)?=', 'onerrorupdate([ \t\r\n]+)?=', 'onfilterchange([ \t\r\n]+)?=', 'onfinish([ \t\r\n]+)?=', 'onfocus([ \t\r\n]+)?=', 'onfocusin([ \t\r\n]+)?=', 'onfocusout([ \t\r\n]+)?=', 'onhelp([ \t\r\n]+)?=', 'onkeydown([ \t\r\n]+)?=', 'onkeypress([ \t\r\n]+)?=', 'onkeyup([ \t\r\n]+)?=', 'onlayoutcomplete([ \t\r\n]+)?=', 'onload([ \t\r\n]+)?=', 'onlosecapture([ \t\r\n]+)?=', 'onmousedown([ \t\r\n]+)?=', 'onmouseenter([ \t\r\n]+)?=', 'onmouseleave([ \t\r\n]+)?=', 'onmousemove([ \t\r\n]+)?=', 'onmouseout([ \t\r\n]+)?=', 'onmouseover([ \t\r\n]+)?=', 'onmouseup([ \t\r\n]+)?=', 'onmousewheel([ \t\r\n]+)?=', 'onmove([ \t\r\n]+)?=', 'onmoveend([ \t\r\n]+)?=', 'onmovestart([ \t\r\n]+)?=', 'onpaste([ \t\r\n]+)?=', 'onpropertychange([ \t\r\n]+)?=', 'onreadystatechange([ \t\r\n]+)?=', 'onreset([ \t\r\n]+)?=', 'onresize([ \t\r\n]+)?=', 'onresizeend([ \t\r\n]+)?=', 'onresizestart([ \t\r\n]+)?=', 'onrowenter([ \t\r\n]+)?=', 'onrowexit([ \t\r\n]+)?=', 'onrowsdelete([ \t\r\n]+)?=', 'onrowsinserted([ \t\r\n]+)?=', 'onscroll([ \t\r\n]+)?=', 'onselect([ \t\r\n]+)?=', 'onselectionchange([ \t\r\n]+)?=', 'onselectstart([ \t\r\n]+)?=', 'onstart([ \t\r\n]+)?=', 'onstop([ \t\r\n]+)?=', 'onsubmit([ \t\r\n]+)?=', 'onunload([ \t\r\n]+)?=', 'style([ \t\r\n]+)?=');
	   $ra = array_merge($ra1, $ra2);
	   
		foreach ($ra as $tag)
		{
			$pattern = '#'.$tag.'#i';
			preg_match_all($pattern, $val, $matches);
			
			foreach ($matches[0] as $match)
				$val = str_replace($match, substr($match, 0, 2).'<x>'.substr($match, 2), $val);
		}
	   
	   return $val;
	}
	
	public static function getCalendarJS()
	{
		$out = '//CALENDAR SETUP'."\n";
		
		$m_short = $m_long = array();
		for ($i=1; $i<=12; $i++)
		{
			$m_short[] = '"'.JText::_('RSFP_CALENDAR_MONTHS_SHORT_'.$i, true).'"';
			$m_long[] = '"'.JText::_('RSFP_CALENDAR_MONTHS_LONG_'.$i, true).'"';
		}
		$w_1 = $w_short = $w_med = $w_long = array();
		for ($i=0; $i<=6; $i++)
		{
			$w_1[] = '"'.JText::_('RSFP_CALENDAR_WEEKDAYS_1CHAR_'.$i, true).'"';
			$w_short[] = '"'.JText::_('RSFP_CALENDAR_WEEKDAYS_SHORT_'.$i, true).'"';
			$w_med[] = '"'.JText::_('RSFP_CALENDAR_WEEKDAYS_MEDIUM_'.$i, true).'"';
			$w_long[] = '"'.JText::_('RSFP_CALENDAR_WEEKDAYS_LONG_'.$i, true).'"';
		}
		
		$out .= 'var MONTHS_SHORT 	 = Array('.implode(',', $m_short).');'."\n";
		$out .= 'var MONTHS_LONG 	 = Array('.implode(',', $m_long).');'."\n";
		$out .= 'var WEEKDAYS_1CHAR  = Array('.implode(',', $w_1).');'."\n";
		$out .= 'var WEEKDAYS_SHORT  = Array('.implode(',', $w_short).');'."\n";
		$out .= 'var WEEKDAYS_MEDIUM = Array('.implode(',', $w_med).');'."\n";
		$out .= 'var WEEKDAYS_LONG 	 = Array('.implode(',', $w_long).');'."\n";
		$out .= 'var START_WEEKDAY 	 = '.JText::_('RSFP_CALENDAR_START_WEEKDAY').';'."\n";
		
		$lang = JFactory::getLanguage();
		if ($lang->hasKey('COM_RSFORM_CALENDAR_CHOOSE_MONTH')) {
			$out .= 'var rsfp_navConfig = { strings : { month: "'.JText::_('COM_RSFORM_CALENDAR_CHOOSE_MONTH', true).'", year: "'.JText::_('COM_RSFORM_CALENDAR_ENTER_YEAR', true).'", submit: "'.JText::_('COM_RSFORM_CALENDAR_OK').'", cancel: "'.JText::_('COM_RSFORM_CALENDAR_CANCEL').'", invalidYear: "'.JText::_('COM_RSFORM_CALENDAR_PLEASE_ENTER_A_VALID_YEAR', true).'" }, monthFormat: rsf_CALENDAR.widget.Calendar.LONG, initialFocus: "year" };'."\n";
		}
		
		return $out;
	}
	
	public static function getTranslations($reference, $formId, $lang, $select = 'value')
	{		
		$db = JFactory::getDBO();
		
		$db->setQuery("SELECT `Lang` FROM #__rsform_forms WHERE FormId='".(int) $formId."'");
		$current_lang = $db->loadResult();
		if ($current_lang == $lang)
			return false;
		
		switch ($reference)
		{
			case 'forms':
				$db->setQuery("SELECT * FROM #__rsform_translations WHERE `form_id`='".(int) $formId."' AND `lang_code`='".$db->escape($lang)."' AND `reference`='forms'");
				$results = $db->loadObjectList();
				
				$return = array();
				foreach ($results as $result)
					$return[$result->reference_id] = ($select == '*') ? $result : (isset($result->$select) ? $result->$select : false);
				
				return $return;
			break;
			
			case 'emails':
				$db->setQuery("SELECT * FROM #__rsform_translations WHERE `form_id`='".(int) $formId."' AND `lang_code`='".$db->escape($lang)."' AND `reference`='emails'");
				$results = $db->loadObjectList();
				
				$return = array();
				foreach ($results as $result)
					$return[$result->reference_id] = ($select == '*') ? $result : (isset($result->$select) ? $result->$select : false);
				
				return $return;
			break;
			
			case 'properties':
				$db->setQuery("SELECT * FROM #__rsform_translations WHERE `form_id`='".(int) $formId."' AND `lang_code`='".$db->escape($lang)."' AND `reference`='properties'");
				$results = $db->loadObjectList();
				
				$return = array();
				foreach ($results as $result)
					$return[$result->reference_id] = ($select == '*') ? $result : (isset($result->$select) ? $result->$select : false);
				
				return $return;
			break;
		}
		
		return false;
	}
	
	public static function getTranslatableProperties()
	{
		return array('LABEL', 'RESETLABEL', 'PREVBUTTON', 'NEXTBUTTON', 'CAPTION', 'DESCRIPTION', 'VALIDATIONMESSAGE', 'DEFAULTVALUE', 'ITEMS', 'TEXT', 'REFRESHTEXT', 'DISPLAYPROGRESSMSG', 'WIRE');
	}
	
	public static function translateIcon()
	{
		return JHTML::image('administrator/components/com_rsform/assets/images/translate.gif', JText::_('RSFP_THIS_ITEM_IS_TRANSLATABLE'), 'title="'.JText::_('RSFP_THIS_ITEM_IS_TRANSLATABLE').'" style="vertical-align: middle"');
	}
	
	public static function mappingsColumns($config,$method,$row = null)
	{
		jimport('joomla.application.component.model');

		if (RSFormProHelper::isJ('3.0')) {
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_rsform/models');
			$model = JModelLegacy::getInstance('mappings', 'RSFormModel');
		} else {
			JModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_rsform/models');
			$model = JModel::getInstance('mappings', 'RSFormModel');
		}
		
		$columns = $model->getColumns($config);
		
		$data = @unserialize($row->data);
		if ($data === false) $data = array();
		
		$where = @unserialize($row->wheredata);
		if ($where === false) $where = array();
		
		$extra = @unserialize($row->extra);
		if ($extra === false) $extra = array();
		
		$andor = @unserialize($row->andor);
		if ($andor === false) $andor = array();
		
		$operators = array(
							JHTML::_('select.option',  '=', JText::_( 'RSFP_OPERATOR_EQUALS' ) ),
							JHTML::_('select.option',  '!=', JText::_( 'RSFP_OPERATOR_NOTEQUAL' ) ),
							JHTML::_('select.option',  '>', JText::_( 'RSFP_OPERATOR_GREATER_THAN' ) ),
							JHTML::_('select.option',  '<', JText::_( 'RSFP_OPERATOR_LESS_THAN' ) ),
							JHTML::_('select.option',  '>=', JText::_( 'RSFP_OPERATOR_EQUALS_GREATHER_THAN' ) ),
							JHTML::_('select.option',  '<=', JText::_( 'RSFP_OPERATOR_EQUALS_LESS_THAN' ) ),
							JHTML::_('select.option',  '%..%', JText::_( 'RSFP_OPERATOR_LIKE' ) ),
							JHTML::_('select.option',  '%..', JText::_( 'RSFP_OPERATOR_STARTS_WITH' ) ),
							JHTML::_('select.option',  '..%', JText::_( 'RSFP_OPERATOR_ENDS_WITH' ) ),
					);
		
		$html = '';
		
		$html .= ($method == 'set') ? JText::_('RSFP_SET').'<hr />' : JText::_('RSFP_WHERE').'<hr />';
		$html .= '<table class="admintable">';
		
		if (!empty($columns))
		{
			$html .= '<tr>';
			$html .= '<td>&nbsp;</td>';
			if ($method == 'where')
			{
				$html .= '<td>&nbsp;</td>';
				$html .= '<td>&nbsp;</td>';
			}
			$html .= '<td align="right"><button class="rs_button" type="submit">'.JText::_('SAVE').'</button></td>';
			$html .= '</tr>';
		}
		
		if (!empty($columns))
		foreach ($columns as $column => $type)
		{
			if ($method == 'set')
			{
				$value = isset($data[$column]) ? $data[$column] : '';
				$name  = 'f_'.$column;
			} else 
			{
				$value	= isset($where[$column]) ? $where[$column] : '';
				$name	= 'w_'.$column;
				$op		= isset($extra[$column]) ? $extra[$column] : '=';
				$op2	= isset($andor[$column]) ? $andor[$column] : 0;
			}
			
			$html .= '<tr>';
			$html .= '<td width="80" nowrap="nowrap" align="right" class="key">'.$column.' ('.$type.')</td>';
			if ($method == 'where')
				$html .= '<td>'.JHTML::_('select.genericlist',  $operators, 'o_'.$column, 'class="inputbox"', 'value', 'text',$op).'</td>';
			if (strpos($type, 'text') !== false)
				$html .= '<td><textarea class="rs_textarea" onclick="toggleDropdown(this,returnMappingsExtra());" onkeydown="closeAllDropdowns();" style="width:300px; height: 200px;" id="'.RSFormProHelper::htmlEscape($name).'" name="'.RSFormProHelper::htmlEscape($name).'">'.RSFormProHelper::htmlEscape($value).'</textarea></td>';
			else
				$html .= '<td><input type="text" class="rs_inp rs_80" onclick="toggleDropdown(this,returnMappingsExtra());" onkeydown="closeAllDropdowns();" size="35" value="'.RSFormProHelper::htmlEscape($value).'" id="'.RSFormProHelper::htmlEscape($name).'" name="'.RSFormProHelper::htmlEscape($name).'"></td>';
			if ($method == 'where')
			$html .= '<td>'.JHTML::_('select.booleanlist', 'c_'.$column, 'class="inputbox"', $op2,'RSFP_OR','RSFP_AND').'</td>';
			$html .= '</tr>';
		}
		
		if (!empty($columns))
		{
			$html .= '<tr>';
			$html .= '<td>&nbsp;</td>';
			if ($method == 'where')
			{
				$html .= '<td>&nbsp;</td>';
				$html .= '<td>&nbsp;</td>';
			}
			$html .= '<td align="right"><button class="rs_button" type="submit">'.JText::_('SAVE').'</button></td>';
			$html .= '</tr>';
		}
		
		$html .= '</table>';
		
		return $html;
	}
	
	public static function getMappingQuery($row)
	{
		$db = JFactory::getDBO();
		$query = '';
		
		$database = '';
		if (!empty($row->database))
		{
			if ($row->connection)
				$database = $row->database.'.';
		}
		
		//get the fields
		$data = @unserialize($row->data);
		if ($data === false) $data = array();
		
		//get the where fields
		$wheredata = @unserialize($row->wheredata);
		if ($wheredata === false) $wheredata = array();
		
		//get the operators
		$extra = @unserialize($row->extra);
		if ($extra === false) $extra = array();
		
		//get the and / or operators
		$andor = @unserialize($row->andor);
		if ($andor === false) $andor = array();
		
		$set = array();
		$where = '';
		
		//make the WHERE cause
		$i = 0;
		if (!empty($wheredata))
		foreach ($wheredata as $column => $field)
		{			
			$andorop = isset($andor[$column]) ? $andor[$column] : 0;
			$andorop = $andorop ? "OR" : "AND";
			
			$operator = isset($extra[$column]) ? $extra[$column] : '=';
			$where .= $i ? " ".$andorop." " : '';
			
			if ($operator == '%..%')
				$where .= " ".$db->quoteName($column)." LIKE '%".$db->escape($field)."%' ";
			elseif ($operator == '%..')
				$where .= " ".$db->quoteName($column)." LIKE '%".$db->escape($field)."' ";
			elseif ($operator == '..%')
				$where .= " ".$db->quoteName($column)." LIKE '".$db->escape($field)."%' ";
			else 
				$where .= " ".$db->quoteName($column)." ".$operator." '".$db->escape($field)."' ";
			
			$i++;
		}
		
		//the WHERE cause
		$where = !empty($where) ? " WHERE ".$where : '';
		
		if (!empty($data))
		foreach ($data as $column => $field)
			$set[] = $db->quoteName($column)." = '".$db->escape($field)."'";
		
		if ($row->method == 0)
			$query = "INSERT INTO ".$database.$db->quoteName($row->table)." SET ".implode(' , ',$set);
			
		if ($row->method == 1)
			$query = "UPDATE ".$database.$db->quoteName($row->table)." SET ".implode(' , ',$set).$where;
			
		if ($row->method == 2)
			$query = "DELETE FROM ".$database.$db->quoteName($row->table).$where;
		
		return $query;
	}
	
	public static function escapeSql(&$value)
	{
		$db = JFactory::getDBO();
		$value = $db->escape($value);
	}
	
	public static function sendMail($from, $fromname, $recipient, $subject, $body, $mode=0, $cc=null, $bcc=null, $attachment=null, $replyto=null, $replytoname=null)
	{
		// Get a JMail instance
		$mail 		= JFactory::getMailer();
		$config 	= JFactory::getConfig();
		$mailfrom	= $config->get('mailfrom');
		
		$mail->ClearReplyTos();
		$mail->setSender(array($from, $fromname));
		
		$mail->setSubject($subject);
		$mail->setBody($body);

		// Are we sending the email as HTML?
		if ($mode)
			$mail->IsHTML(true);

		$mail->addRecipient($recipient);
		$mail->addCC($cc);
		$mail->addBCC($bcc);
		$mail->addAttachment($attachment);

		// Take care of reply email addresses
		if (is_array($replyto)) {
			$mail->ClearReplyTos();
			$numReplyTo = count($replyto);
			for ($i = 0; $i < $numReplyTo; $i++)
			{
				$mail->addReplyTo(array($replyto[$i], $replytoname[$i]));
			}
		}
		else if (!empty($replyto)) {
			$mail->ClearReplyTos();
			$mail->addReplyTo(array($replyto, $replytoname));
		}

		return $mail->Send();
	}
	
	public static function renderHTML() {
		$args = func_get_args();
		if (RSFormProHelper::isJ('3.0')) {
			if ($args[0] == 'select.booleanlist') {
				// 0 - type
				// 1 - name
				// 2 - additional
				// 3 - value
				// 4 - yes
				// 5 - no
				
				// get the radio element
				$radio = JFormHelper::loadFieldType('radio');
				
				// setup the properties
				$name	 	= self::htmlEscape($args[1]);
				$additional = isset($args[2]) ? (string) $args[2] : '';
				$value		= $args[3];
				$yes 	 	= isset($args[4]) ? self::htmlEscape($args[4]) : 'JYES';
				$no 	 	= isset($args[5]) ? self::htmlEscape($args[5]) : 'JNO';
				
				// prepare the xml
				$element = new SimpleXMLElement('<field name="'.$name.'" type="radio" class="btn-group"><option '.$additional.' value="0">'.$no.'</option><option '.$additional.' value="1">'.$yes.'</option></field>');
				
				// run
				$radio->setup($element, $value);
				
				return $radio->input;
			}
		} else {
			if ($args[0] == 'select.booleanlist') {
				$name	 	= $args[1];
				$additional = isset($args[2]) ? (string) $args[2] : '';
				$value		= $args[3];
				$yes 	 	= isset($args[4]) ? self::htmlEscape($args[4]) : 'JYES';
				$no 	 	= isset($args[5]) ? self::htmlEscape($args[5]) : 'JNO';
				
				return JHtml::_($args[0], $name, $additional, $value, $yes, $no);
			}
		}
	}
}