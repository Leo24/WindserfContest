<?php
/**
* @version 1.3.0
* @package RSform!Pro 1.3.0
* @copyright (C) 2007-2010 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * RSForm! Pro system plugin
 */
class plgSystemRSFPRegistration extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatibility we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param 	array   $config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function plgSystemRSFPRegistration(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->params = $config;
	}
	
	function canRun()
	{
		if (class_exists('RSFormProHelper')) return true;
		
		$helper = JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/rsform.php';
		if (file_exists($helper))
		{
			require_once($helper);
			RSFormProHelper::readConfig();
			return true;
		}
		
		return false;
	}
	
	function rsfp_onFormSave($form)
	{
		$post = JRequest::get('post', JREQUEST_ALLOWRAW);
		$post['form_id'] = $post['formId'];
		
		$row = JTable::getInstance('RSForm_Registration', 'Table');
		$post['published'] = $post['jur_published'];
		if (!$row)
			return;
		if (!$row->bind($post))
		{
			JError::raiseWarning(500, $row->getError());
			return false;
		}
		
		$row->reg_merge_vars = serialize($post['reg_vars']);
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT form_id FROM #__rsform_registration WHERE form_id='".(int) $post['form_id']."'");
		if (!$db->loadResult())
		{
			$db->setQuery("INSERT INTO #__rsform_registration SET form_id='".(int) $post['form_id']."'");
			$db->execute();
		}
		
		if ($row->store())
		{
			return true;
		}
		else
		{
			JError::raiseWarning(500, $row->getError());
			return false;
		}
	}
	
	function rsfp_bk_onAfterShowFormEditTabs()
	{
		$formId = JRequest::getInt('formId');
		
		$lang = JFactory::getLanguage();
		$lang->load('plg_system_rsfpregistration');
		
		$row = JTable::getInstance('RSForm_Registration', 'Table');
		if (!$row) return;
		$row->load($formId);
		$row->reg_merge_vars = @unserialize($row->reg_merge_vars);
		if ($row->reg_merge_vars === false)
			$row->reg_merge_vars = array();
		
		// Fields
		$fields_array = $this->_getFields($formId);
		$fields = array();
		foreach ($fields_array as $field)
			$fields[] = JHTML::_('select.option', $field, $field);
		
		// Merge Vars
		$merge_vars = array("name" => JText::_('RSFP_REG_NAME'),"username" => JText::_('RSFP_REG_USERNAME'),"email1" => JText::_('RSFP_REG_EMAIL'),"email2" => JText::_('RSFP_REG_EMAIL2') ,"password1" => JText::_('RSFP_REG_PASSWORD1'),"password2" => JText::_('RSFP_REG_PASSWORD2'));
		
		$lists['fields'] = array();
		if (is_array($merge_vars))
			foreach ($merge_vars as $merge_var => $title)
			{
				$lists['fields'][$merge_var] = JHTML::_('select.genericlist', $fields, 'reg_vars['.$merge_var.']', null, 'value', 'text', isset($row->reg_merge_vars[$merge_var]) ? $row->reg_merge_vars[$merge_var] : null);
			}
		
		$lists['published'] = RSFormProHelper::renderHTML('select.booleanlist','jur_published','class="inputbox"',$row->published);
		$activations = array(
			0 => JText::_('RSFP_REG_NONE'),
			1 => JText::_('RSFP_REG_SELF'),
			2 => JText::_('RSFP_REG_ADMIN')
		);
		$lists['activation'] = JHTML::_('select.genericlist', $activations, 'activation','class="inputbox"', 'value', 'text', $row->activation);
		$cb = file_exists(JPATH_ADMINISTRATOR.'/components/com_comprofiler/admin.comprofiler.php');
		$lists['cb'] = RSFormProHelper::renderHTML('select.booleanlist','cbactivation','class="inputbox"',$row->cbactivation);
		
		echo '<div id="joomlaregistrationdiv">';
			include JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/registration.php';
		echo '</div>';
	}
	
	function rsfp_bk_onAfterShowFormEditTabsTab()
	{
		$lang = JFactory::getLanguage();
		$lang->load('plg_system_rsfpregistration');
		
		echo '<li><a href="javascript: void(0);" id="joomlaregistration"><span>'.JText::_('RSFP_REG_JOOMLA_INTEGRATION').'</span></a></li>';
	}
	
	function rsfp_f_onBeforeFormValidation($args)
	{
		$db 	= JFactory::getDBO();
		$post	= JRequest::getVar('form', array(), 'default', 'none', JREQUEST_ALLOWRAW);
		$formId = (int) $post['formId'];
		
		if ($row = $this->_getRow($formId))
		{
			list($vars, $fields) = $this->_prepareData($post, $row->reg_merge_vars);
			$this->_validateData($vars, $fields, $args['invalid'], $formId);
		}
	}
	
	function regValidateUsername($value)
	{
		if ($value == '' || (preg_match( "#[<>\"'%;()&]#i", $value) || strlen(utf8_decode($value)) < 2))
			return false;
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT id FROM #__users WHERE `username` LIKE '".$db->escape($value)."'");
		return $db->loadResult() ? false : true;
	}
	
	function regValidateEmail($value)
	{
		if ($value == '' || !RSFormProValidations::email($value))
			return false;
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT id FROM #__users WHERE `email` LIKE '".$db->escape($value)."'");
		return $db->loadResult() ? false : true;
	}
	
	function rsfp_f_onBeforeStoreSubmissions($args)
	{		
		$formId = (int) $args['formId'];
		$post 	=& $args['post'];
		
		if ($row = $this->_getRow($formId))
		{			
			list($vars, $fields) = $this->_prepareData($post, $row->reg_merge_vars);
			
			$this->_register($vars, $row->activation, $row->cbactivation);
		}
	}
	
	protected function _getRow($formId) {
		$db = JFactory::getDbo();
		$db->setQuery("SELECT * FROM #__rsform_registration WHERE `form_id`='".(int) $formId."' AND `published`='1'");
		return $db->loadObject();
	}
	
	// done, remove the data from the submission
	public function rsfp_f_onAfterFormProcess($args) {
		$SubmissionId 	= $args['SubmissionId'];
		$formId 		= $args['formId'];
		
		if ($row = $this->_getRow($formId)) {
			$db = JFactory::getDbo();
			
			list($vars, $fields) = $this->_prepareData(array(), $row->reg_merge_vars);
			
			$passwords = array();
			if (isset($fields['password'])) {
				$passwords[] = $db->quote($fields['password']);
			}
			if (isset($fields['password1'])) {
				$passwords[] = $db->quote($fields['password1']);
			}
			if (isset($fields['password2'])) {
				$passwords[] = $db->quote($fields['password2']);
			}
			
			if ($passwords) {
				$db->setQuery("UPDATE #__rsform_submission_values SET `FieldValue`='' WHERE `FieldName` IN (".implode(",", $passwords).") AND SubmissionId='".$SubmissionId."' AND FormId='".$formId."'");
				$db->execute();
			}
		}
	}
	
	protected function _prepareData($post, $merge_vars) {
		$merge_vars = @unserialize($merge_vars);
		if ($merge_vars === false)
			$merge_vars = array();
		
		if (!isset($merge_vars['password'])) {
			$merge_vars['password'] = $merge_vars['password1'];
		}
		if (!isset($merge_vars['email'])) {
			$merge_vars['email'] = $merge_vars['email1'];
		}
		
		$vars = array(
			'name' => isset($post[$merge_vars['name']]) ? $post[$merge_vars['name']] : '',
			'username' => isset($post[$merge_vars['username']]) ? $post[$merge_vars['username']] : '',
			'email' => isset($post[$merge_vars['email']]) ? $post[$merge_vars['email']] : '',
			'email1' => isset($post[$merge_vars['email1']]) ? $post[$merge_vars['email1']] : '',
			'email2' => isset($post[$merge_vars['email2']]) ? $post[$merge_vars['email2']] : '',
			'password' => isset($post[$merge_vars['password']]) ? $post[$merge_vars['password']] : '',
			'password1' => isset($post[$merge_vars['password1']]) ? $post[$merge_vars['password1']] : '',
			'password2' => isset($post[$merge_vars['password2']]) ? $post[$merge_vars['password2']] : ''
		);
		
		foreach ($vars as $k => $v) {
			if (is_array($v)) {
				array_walk($v, array('plgSystemRSFPRegistration', '_escapeCommas'));
				$vars[$k] = implode(',', $v);
			}
		}
		
		return array($vars, $merge_vars);
	}
	
	protected function _validateData($vars, $fields, &$invalid, $formId) {
		$lang 		= JFactory::getLanguage();
		$params		= JComponentHelper::getParams('com_users');
		$app		= JFactory::getApplication();
		
		$lang->load('com_users', JPATH_SITE);
		
		// do our own validation first
		if ($vars['name'] == '') {
			$invalid[] = RSFormProHelper::componentNameExists($fields['name'], $formId);
		}
		if ($vars['username'] == '' || !plgSystemRSFPRegistration::regValidateUsername($vars['username'])) {
			$invalid[] = RSFormProHelper::componentNameExists($fields['username'], $formId);
		}
		if ($vars['email'] == '' || !plgSystemRSFPRegistration::regValidateEmail($vars['email'])) {
			$invalid[] = RSFormProHelper::componentNameExists($fields['email'], $formId);
		}
		if ($vars['password'] == '') {
			$invalid[] = RSFormProHelper::componentNameExists($fields['password'], $formId);
		}
		if ($vars['email1'] != $vars['email2']) {
			$invalid[] = RSFormProHelper::componentNameExists($fields['email'], $formId);
			$invalid[] = RSFormProHelper::componentNameExists($fields['email2'], $formId);
		}
		if ($vars['password1'] != $vars['password2']) {
			$invalid[] = RSFormProHelper::componentNameExists($fields['password'], $formId);
			$invalid[] = RSFormProHelper::componentNameExists($fields['password2'], $formId);
		}
		
		$data = new stdClass();
		foreach ($vars as $k => $v) {
			$data->$k = $v;
		}
		$data->groups = array($params->get('new_usertype', 2));
		
		// Get the dispatcher and load the users plugins.
		JPluginHelper::importPlugin('user');
		
		// Trigger the data preparation event.
		$results = $app->triggerEvent('onContentPrepareData', array('com_users.registration', $data));
		
		// Check for errors encountered while preparing the data.
		if (count($results) && in_array(false, $results, true)) {
			//$app->enqueueMessage($dispatcher->getError(), 'warning');
			return false;
		}
		
		$data = (array) $data;
		
		$user = new JUser;
		
		// Bind the data.
		if (!$user->bind($data)) {
			$app->enqueueMessage(JText::sprintf('COM_USERS_REGISTRATION_BIND_FAILED', $user->getError()), 'warning');
			return false;
		}
		
		$table = $user->getTable();
		$table->bind($user->getProperties());
		if (!$table->check()) {
			$app->enqueueMessage($table->getError(), 'warning');
			return false;
		}
		
		return true;
	}
	
	protected function _register($vars, $useractivation, $cbactivation) {
		$params	= JComponentHelper::getParams('com_users');
		$config = JFactory::getConfig();
		$app	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		
		$data = new stdClass();
		foreach ($vars as $k => $v) {
			$data->$k = $v;
		}
		$data->groups = array($params->get('new_usertype', 2));
		
		// Get the dispatcher and load the users plugins.
		JPluginHelper::importPlugin('user');
		
		// Trigger the data preparation event.
		$results = $app->triggerEvent('onContentPrepareData', array('com_users.registration', $data));
		
		// Check for errors encountered while preparing the data.
		if (count($results) && in_array(false, $results, true)) {
			//$app->enqueueMessage($dispatcher->getError(), 'warning');
			return false;
		}
		
		$data = (array) $data;
		
		$user = new JUser;
		
		// Prepare the data for the user object.
		$sendpassword 	= $params->get('sendpassword', 1);

		// Check if the user needs to activate their account.
		if (($useractivation == 1) || ($useractivation == 2)) {
			$data['activation'] = JApplication::getHash(JUserHelper::genRandomPassword());
			$data['block'] = 1;
		}

		// Bind the data.
		if (!$user->bind($data)) {
			$app->enqueueMessage(JText::sprintf('COM_USERS_REGISTRATION_BIND_FAILED', $user->getError()), 'warning');
			return false;
		}
		
		// Store the data.
		if (!$user->save()) {
			$app->enqueueMessage(JText::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $user->getError()), 'warning');
			return false;
		}
		
		// Compile the notification mail values.
		$data = $user->getProperties();
		$data['fromname']	= $config->get('fromname');
		$data['mailfrom']	= $config->get('mailfrom');
		$data['sitename']	= $config->get('sitename');
		$data['siteurl']	= JUri::root();
		
		// Handle account activation/confirmation emails.
		if ($useractivation == 2)
		{
			// Set the link to confirm the user email.
			$uri = JURI::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			if ($sendpassword)
			{
				$emailBody = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY',
					$data['name'],
					$data['sitename'],
					$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
					$data['siteurl'],
					$data['username'],
					$data['password_clear']
				);
			}
			else
			{
				$emailBody = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY_NOPW',
					$data['name'],
					$data['sitename'],
					$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
					$data['siteurl'],
					$data['username']
				);
			}
		}
		elseif ($useractivation == 1)
		{
			// Set the link to activate the user account.
			$uri = JURI::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			if ($sendpassword)
			{
				$emailBody = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
					$data['name'],
					$data['sitename'],
					$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
					$data['siteurl'],
					$data['username'],
					$data['password_clear']
				);
			}
			else
			{
				$emailBody = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY_NOPW',
					$data['name'],
					$data['sitename'],
					$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
					$data['siteurl'],
					$data['username']
				);
			}
		}
		else
		{

			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			$emailBody = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_BODY',
				$data['name'],
				$data['sitename'],
				$data['siteurl']
			);
		}

		// Send the registration email.
		$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);

		//Send Notification mail to administrators
		if (($useractivation < 2) && ($params->get('mail_to_admin') == 1)) {
			$emailSubject = JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			$emailBodyAdmin = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY',
				$data['name'],
				$data['username'],
				$data['siteurl']
			);

			// get all admin users
			$query = 'SELECT name, email, sendEmail' .
					' FROM #__users' .
					' WHERE sendEmail=1';

			$db->setQuery($query);
			$rows = $db->loadObjectList();

			// Send mail to all superadministrators id
			foreach( $rows as $row )
			{
				$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $row->email, $emailSubject, $emailBodyAdmin);
			}
		}
		// Check for an error.
		if ($return !== true) {
			$app->enqueueMessage(JText::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'), 'warning');

			// Send a system message to administrators receiving system mails
			$db = JFactory::getDBO();
			$q = "SELECT id
				FROM #__users
				WHERE block = 0
				AND sendEmail = 1";
			$db->setQuery($q);
			$sendEmail = $db->loadColumn();
			if (count($sendEmail) > 0) {
				$jdate = new JDate;
				// Build the query to add the messages
				$q = "INSERT INTO ".$db->quoteName('#__messages')." (".$db->quoteName('user_id_from').
				", ".$db->quoteName('user_id_to').", ".$db->quoteName('date_time').
				", ".$db->quoteName('subject').", ".$db->quoteName('message').") VALUES ";
				$messages = array();

				foreach ($sendEmail as $userid) {
					$messages[] = "(".$userid.", ".$userid.", '".$jdate->toSql()."', '".JText::_('COM_USERS_MAIL_SEND_FAILURE_SUBJECT')."', '".JText::sprintf('COM_USERS_MAIL_SEND_FAILURE_BODY', $return, $data['username'])."')";
				}
				$q .= implode(',', $messages);
				$db->setQuery($q);
				$db->execute();
			}
			return true;
		}
		
		$this->cbactivate($cbactivation,$user->id);

		if ($useractivation == 1)
		{
			return "useractivate";
		}
		elseif ($useractivation == 2)
		{
			return "adminactivate";
		}
		else
		{
			return $user->id;
		}
	}
	
	function _getFields($formId)
	{
		$db = JFactory::getDBO();
		
		$db->setQuery("SELECT p.PropertyValue FROM #__rsform_components c LEFT JOIN #__rsform_properties p ON (c.ComponentId=p.ComponentId) WHERE c.FormId='".(int) $formId."' AND p.PropertyName='NAME' ORDER BY c.Order");
		return $db->loadColumn();
	}
	
	function _escapeCommas(&$item)
	{
		$item = str_replace(',', '\,', $item);
	}
	
	function cbactivate($cbactivation,$uid)
	{
		$db = JFactory::getDBO();
		
		$cb = file_exists(JPATH_ADMINISTRATOR.'/components/com_comprofiler/admin.comprofiler.php');
		if ($cb && $cbactivation)
		{
			$db->setQuery("INSERT IGNORE INTO #__comprofiler SET `id` = ".$uid." , `user_id` = ".$uid.", `approved` = 1 , `confirmed` = 1");
			$db->execute();
		}
		
		return true;
	}
	
	function rsfp_bk_onAfterShowConfigurationTabs($tabs)
	{
		$lang = JFactory::getLanguage();
		$lang->load('plg_system_rsfpregistration');
		
		$tabs->addTitle(JText::_('RSFP_REG_FORM_NAME_LABEL'), 'form-register');
		$tabs->addContent($this->registerformConfigurationScreen());
	}
	
	function registerformConfigurationScreen()
	{
		ob_start();
		
		$db = JFactory::getDBO();
		$query = "SELECT f.`FormId` as value, f.`FormName` as text FROM #__rsform_forms f LEFT JOIN #__rsform_registration r ON f.FormId = r.form_id WHERE r.published = 1 ORDER BY f.`FormName` ASC";
		$db->setQuery($query);
		$myforms = $db->loadObjectList();

		$tmp = new stdClass();
		$tmp->value = '0';
		$tmp->text = JText::_('Default Joomla User Registration Form');
		array_unshift($myforms, $tmp);
?>
		<div id="page-register">
			<table class="admintable">
				<tr>
					<td width="200" style="width: 200px;" align="right" class="key"><label for=""><span class="hasTip" title="<?php echo JText::_('RSFP_REG_FORM_NAME_DESC'); ?>"><?php echo JText::_( 'RSFP_REG_FORM_NAME_LABEL' ); ?></span></label></td>
					<td>
						<?php echo JHTML::_('select.genericlist', $myforms, 'rsformConfig[registration_form]', null, 'value', 'text', RSFormProHelper::htmlEscape(RSFormProHelper::getConfig('registration_form'))); ?>
					</td>
				</tr>
				<tr>
					<td align="right"><strong><?php echo JText::_('RSFP_REG_OR'); ?></strong></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td width="200" style="width: 200px;" align="right" class="key"><label for="redirect_url"><span class="hasTip" title="<?php echo JText::_('RSFP_REDIRECT_URL_DESC'); ?>"><?php echo JText::_( 'RSFP_REDIRECT_URL_LABEL' ); ?></span></label></td>
					<td>
						<input type="text" name="rsformConfig[redirect_url]" id="redirect_url" value="<?php echo RSFormProHelper::htmlEscape(RSFormProHelper::getConfig('redirect_url')); ?>" size="150" maxlength="150">
					</td>
				</tr>
			</table>
		</div>
		<?php
		
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
	
	function onAfterDispatch()
	{
		if (!$this->canRun()) return;
		
		$option = JRequest::getVar('option');
		$view = JRequest::getVar('view');
		
		if ($option == 'com_users' && $view == 'registration') {
			$custom_url = RSFormProHelper::getConfig('redirect_url');
			$formid     = RSFormProHelper::getConfig('registration_form');
			$url  		= false;
			
			if (!empty($custom_url) && (strpos($custom_url, 'http://') !== false || strpos($custom_url, 'https://') !== false) && JURI::isInternal($custom_url)) {
				$url = $custom_url;				
			} elseif ($formid != 0) {
				$url = JRoute::_('index.php?option=com_rsform&formId='.$formid, false);
			}
				
			if ($url) {
				$mainframe = JFactory::getApplication();
				$mainframe->redirect($url);
			}
		}
	}
}