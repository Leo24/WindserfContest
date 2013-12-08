<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class RSFormController extends JControllerLegacy
{
	var $_db;
	
	function __construct()
	{
		parent::__construct();
		
		if (RSFormProHelper::isJ16())
			JHTML::_('behavior.framework');
		
		$this->_db = JFactory::getDBO();
		
		$version = new RSFormProVersion();
		$v = $version->revision;
		
		$doc = JFactory::getDocument();
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsform/assets/js/jquery.js?v='.$v);
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsform/assets/js/script.js?v='.$v);
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsform/assets/js/tablednd.js?v='.$v);
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsform/assets/js/jquery.scrollto.js?v='.$v);
		
		$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_rsform/assets/css/style.css?v='.$v);
		if (RSFormProHelper::isJ('2.5') && !RSFormProHelper::isJ('3.0')) {
			$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_rsform/assets/css/style25.css?v='.$v);
		} elseif (RSFormProHelper::isJ('3.0')) {
			$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_rsform/assets/css/style30.css?v='.$v);
		}
		$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_rsform/assets/css/rsdesign.css?v='.$v);
	}
	
	function display($cachable = false, $urlparams = false)
	{
		parent::display($cachable, $urlparams);
	}
	
	function mappings()
	{
		JRequest::setVar('view', 'forms');
		JRequest::setVar('layout', 'edit_mappings');
		JRequest::setVar('tmpl', 'component');
		
		parent::display();
	}
	
	function changeLanguage()
	{
		$formId  	 = JRequest::getInt('formId');
		$tabposition = JRequest::getInt('tabposition');
		$tab		 = JRequest::getInt('tab',0);
		$tab 		 = $tabposition ? '&tab='.$tab : '';
		$session 	 = JFactory::getSession();
		$session->set('com_rsform.form.'.$formId.'.lang', JRequest::getVar('Language'));
		
		$this->setRedirect('index.php?option=com_rsform&task=forms.edit&formId='.$formId.'&tabposition='.$tabposition.$tab);
	}
	
	function changeEmailLanguage()
	{
		$formId  = JRequest::getInt('formId');
		$cid	 = JRequest::getInt('id');
		$session = JFactory::getSession();
		$session->set('com_rsform.emails.'.$cid.'.lang', JRequest::getVar('ELanguage'));
		
		$this->setRedirect('index.php?option=com_rsform&task=forms.emails&tmpl=component&formId='.$formId.'&cid='.$cid);
	}

	function layoutsGenerate()
	{
		$model = $this->getModel('forms');
		$model->getForm();
		$model->_form->FormLayoutName = JRequest::getCmd('layoutName');
		$model->autoGenerateLayout();
		
		echo $model->_form->FormLayout;
		exit();
	}

	function layoutsSaveName()
	{
		$formId = JRequest::getInt('formId');
		$name = JRequest::getVar('formLayoutName');
		
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__rsform_forms SET FormLayoutName='".$db->escape($name)."' WHERE FormId='".$formId."'");
		$db->execute();
		
		exit();
	}
	
	function submissionExportPDF()
	{		
		$cid = JRequest::getInt('cid');
		$this->setRedirect('index.php?option=com_rsform&view=submissions&layout=edit&cid='.$cid.'&format=pdf');
	}

	/**
	 * Saves registration form
	 */
	function saveRegistration()
	{
		$code = JRequest::getVar('code');
		$code = $this->_db->escape($code);
		if (!empty($code))
		{
			$this->_db->setQuery("UPDATE #__rsform_config SET `SettingValue`='".$code."' WHERE `SettingName`='global.register.code'");
			$this->_db->execute();
			$this->setRedirect('index.php?option=com_rsform&task=updates.manage', JText::_('RSFP_REGISTRATION_SAVED'));
		}
		else
			$this->setRedirect('index.php?option=com_rsform&task=configuration.edit');
	}

	/**
	 * Backup / Restore Screen
	 */
	function backupRestore()
	{
		JRequest::setVar('view', 'backuprestore');
		JRequest::setVar('layout', 'default');
		
		parent::display();
	}

	/**
	 * Backup Generate Process
	 *
	 * @param str $option
	 */
	function backupDownload()
	{
		require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/backup.php';
		
		jimport('joomla.filesystem.archive');
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		
		// Get the selected items
		$cid = JRequest::getVar('cid', array(0), 'post', 'array');
		
		// Force array elements to be integers
		JArrayHelper::toInteger($cid, array(0));
		
		$tmpdir = uniqid('rsformbkp');
		$path = JPATH_SITE.'/media/'.$tmpdir;
		if (!JFolder::create($path, 0777))
		{
			JError::raiseWarning(500, JText::_('Could not create directory ').$path);
			return $this->setRedirect('index.php?option=com_rsform&task=backup.restore');
		}
		
		$export_submissions = JRequest::getInt('submissions');
		if (!RSFormProBackup::create($cid, $export_submissions, $path.'/install.xml'))
		{
			JError::raiseWarning(500, JText::_('Could not write to ').$path);
			return $this->setRedirect('index.php?option=com_rsform&task=backup.restore');
		}
		
		$name = 'rsform_backup_'.date('Y-m-d_His').'.zip';
		$files = array(array('data' => JFile::read($path.'/install.xml'), 'name' => 'install.xml'));
		
		$adapter = JArchive::getAdapter('zip');
		if (!$adapter->create($path.'/'.$name, $files))
		{
			JError::raiseWarning(500, JText::_('Could not create archive ').$path.'/'.$name);
			return $this->setRedirect('index.php?option=com_rsform&task=backup.restore');
		}

		$this->setRedirect(JURI::root().'media/'.$tmpdir.'/'.$name);
	}

	function restoreProcess()
	{
		require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/restore.php';
		
		jimport('joomla.filesystem.archive');
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		
		$lang = JFactory::getLanguage();
		$lang->load('com_installer');
		
		$link = 'index.php?option=com_rsform&task=backup.restore';
		
		if(!extension_loaded('zlib'))
		{
			JError::raiseWarning(500, JText::_('WARNINSTALLZLIB'));
			return $this->setRedirect($link);
		}
		
		$userfile = JRequest::getVar('userfile', null, 'files');
		if ($userfile['error'])
		{
			JError::raiseWarning(500, JText::_($userfile['error'] == 4 ? 'ERRORNOFILE' : 'WARNINSTALLUPLOADERROR'));				
			return $this->setRedirect($link);
		}

		$baseDir = JPATH_SITE.'/media';
		$moved = JFile::upload($userfile['tmp_name'], $baseDir.'/'.$userfile['name']);
		if (!$moved)
		{
			JError::raiseWarning(500, JText::_('FAILED TO MOVE UPLOADED FILE TO'));
			return $this->setRedirect($link);
		}
		
		$options = array();
		$options['filename'] = $baseDir.'/'.$userfile['name'];
		$options['overwrite'] = JRequest::getInt('overwrite');
		
		$restore = new RSFormProRestore($options);
		if (!$restore->process())
		{
			JError::raiseWarning(500, JText::_('Unable to extract archive'));
			return $this->setRedirect($link);
		}
		
		if (!$restore->restore())
			return $this->setRedirect($link);
		
		$this->setRedirect($link, JText::_('RSFP_RESTORE_OK'));
	}

	function updatesManage()
	{
		JRequest::setVar('view', 'updates');
		JRequest::setVar('layout', 'default');
		
		parent::display();
	}
	
	function goToPlugins()
	{
		$mainframe = JFactory::getApplication();
		$mainframe->redirect('http://www.rsjoomla.com/support/documentation/view-knowledgebase/26-plugins-and-modules.html');
	}
	
	function goToSupport()
	{
		$mainframe = JFactory::getApplication();
		$mainframe->redirect('http://www.rsjoomla.com/support/documentation/view-knowledgebase/21-rsform-pro-user-guide.html');
	}
	
	function plugin()
	{
		$mainframe = JFactory::getApplication();
		$mainframe->triggerEvent('rsfp_bk_onSwitchTasks');
	}
	
	function setMenu()
	{
		$app   = JFactory::getApplication();
		
		$type  = json_decode('{"id":0,"title":"COM_RSFORM_MENU_FORM","request":{"option":"com_rsform","view":"rsform"}}');
		$title = 'component';
		
		$app->setUserState('com_menus.edit.item.type',	$title);
		
		$component = JComponentHelper::getComponent($type->request->option);
		$data['component_id'] = $component->id;
		
		$params['option'] = 'com_rsform';
		$params['view']   = 'rsform';
		$params['formId'] = JRequest::getInt('formId');
		
		$app->setUserState('com_menus.edit.item.link', 'index.php?'.JURI::buildQuery($params));
		
		$data['type'] = $title;
		$data['formId'] = JRequest::getInt('formId');
		$app->setUserState('com_menus.edit.item.data', $data);
		
		$this->setRedirect(JRoute::_('index.php?option=com_menus&view=item&layout=edit', false));
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
}