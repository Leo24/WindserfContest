<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class RSFormControllerRichtext extends RSFormController
{
	function __construct()
	{
		parent::__construct();
		
		$this->registerTask('apply', 'save');
		
		$this->_db = JFactory::getDBO();
	}
	
	function show()
	{
		JRequest::setVar('view', 	'forms');
		JRequest::setVar('layout', 	'richtext');
		
		parent::display();
	}
	
	function save()
	{
		$db 	= JFactory::getDBO();
		$formId = JRequest::getInt('formId');
		$opener = JRequest::getCmd('opener');
		$value  = JRequest::getVar($opener, '', 'post', 'none', JREQUEST_ALLOWRAW);
		$model  = $this->getModel('forms');
		
		$model->getForm();
		$lang = $model->getLang();
		if ($model->_form->Lang != $lang)
		{
			$model->saveFormRichtextTranslation($formId, $opener, $value, $lang);
		}
		else
		{
			$db->setQuery("UPDATE #__rsform_forms SET `".$opener."`='".$db->escape($value)."' WHERE FormId='".$formId."'");
			$db->execute();
		}
		
		if ($this->getTask() == 'apply')
			return $this->setRedirect('index.php?option=com_rsform&task=richtext.show&opener='.$opener.'&formId='.$formId.'&tmpl=component');
		
		$document = JFactory::getDocument();
		$document->addScriptDeclaration("window.close();");
	}
	
	function preview()
	{
		$formId = JRequest::getInt('formId');
		$opener = JRequest::getCmd('opener');
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT `".$opener."` FROM #__rsform_forms WHERE FormId='".$formId."'");
		$value = $db->loadResult();
		
		$model = $this->getModel('forms');
		$model->getForm();
		$lang = $model->getLang();
		$translations = RSFormProHelper::getTranslations('forms', $formId, $lang);
		if ($translations && isset($translations[$opener]))
			$value = $translations[$opener];
		
		echo $value;
	}
}