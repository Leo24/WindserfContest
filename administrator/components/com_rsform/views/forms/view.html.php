<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class RSFormViewForms extends JViewLegacy
{
	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$document  = JFactory::getDocument();
		$document->addCustomTag('<!--[if IE 7]><link href="'.JURI::root().'administrator/components/com_rsform/assets/css/styleie.css" rel="stylesheet" type="text/css" /><![endif]-->');
		
		if (RSFormProHelper::getConfig('global.codemirror'))
		{	
			$document->addScript(JURI::root(true).'/administrator/components/com_rsform/assets/codemirror/lib/codemirror.js');
			$document->addScript(JURI::root(true).'/administrator/components/com_rsform/assets/codemirror/mode/css/css.js');
			$document->addScript(JURI::root(true).'/administrator/components/com_rsform/assets/codemirror/mode/htmlmixed/htmlmixed.js');
			$document->addScript(JURI::root(true).'/administrator/components/com_rsform/assets/codemirror/mode/javascript/javascript.js');
			$document->addScript(JURI::root(true).'/administrator/components/com_rsform/assets/codemirror/mode/php/php.js');
			$document->addScript(JURI::root(true).'/administrator/components/com_rsform/assets/codemirror/mode/clike/clike.js');
			$document->addScript(JURI::root(true).'/administrator/components/com_rsform/assets/codemirror/mode/xml/xml.js');
			
			$document->addStyleSheet(JURI::root(true).'/administrator/components/com_rsform/assets/codemirror/lib/codemirror.css');
			$document->addStyleSheet(JURI::root(true).'/administrator/components/com_rsform/assets/codemirror/theme/default.css');
		}
		
		JToolBarHelper::title('RSForm! Pro','rsform');
		
		// adding the toolbar on 2.5
		if (!RSFormProHelper::isJ('3.0')) {
			$this->addToolbar();
		}
		
		$layout = $this->getLayout();
		$this->isComponent = JRequest::getVar('tmpl') == 'component';
		if ($layout == 'edit')
		{
			$submissionsIcon = RSFormProHelper::isJ('3.0') ? 'database' : 'forward';
			$previewIcon	 = RSFormProHelper::isJ('3.0') ? 'new tab' : 'preview';
			
			JToolBarHelper::apply('forms.apply');
			JToolBarHelper::save('forms.save');
			JToolBarHelper::spacer();
			JToolBarHelper::custom('forms.preview', $previewIcon, $previewIcon, JText::_('JGLOBAL_PREVIEW'), false);
			JToolBarHelper::custom('submissions.back', $submissionsIcon, $submissionsIcon, JText::_('RSFP_SUBMISSIONS'), false);
			JToolBarHelper::custom('components.copy', 'copy', 'copy', JText::_('RSFP_COPY_TO_FORM'), false);
			JToolBarHelper::custom('components.duplicate', 'copy', 'copy', JText::_('RSFP_DUPLICATE'), false);
			JToolBarHelper::deleteList(JText::_('VALIDDELETEITEMS'), 'components.remove', RSFormProHelper::isJ16() ? JText::_('JTOOLBAR_DELETE') : JText::_('DELETE'));
			JToolBarHelper::publishList('components.publish', RSFormProHelper::isJ16() ? JText::_('JTOOLBAR_PUBLISH') : JText::_('Publish'));
			JToolBarHelper::unpublishList('components.unpublish', RSFormProHelper::isJ16() ? JText::_('JTOOLBAR_UNPUBLISH') : JText::_('Unpublish'));
			JToolBarHelper::spacer();
			JToolBarHelper::cancel('forms.cancel');
			
			$this->tabposition = JRequest::getInt('tabposition', 0);
			$this->tab 		   = JRequest::getInt('tab', 0);
			$this->form 	   = $this->get('form');
			$this->form_post   = $this->get('formPost');
			
			$this->hasSubmitButton = $this->get('hasSubmitButton');
			
			JToolBarHelper::title('RSForm! Pro <small>['.JText::sprintf('RSFP_EDITING_FORM', $this->form->FormTitle).']</small>','rsform');
			
			$this->fields = $this->get('fields');
			$this->quickfields = $this->get('quickfields');
			$this->pagination = $this->get('fieldspagination');
			
			$lists['Published'] = $this->renderHTML('select.booleanlist','Published','',$this->form->Published);
			$lists['keepdata'] = $this->renderHTML('select.booleanlist','Keepdata','',$this->form->Keepdata);
			$lists['confirmsubmission'] = $this->renderHTML('select.booleanlist','ConfirmSubmission','',$this->form->ConfirmSubmission);
			$lists['ShowThankyou'] = $this->renderHTML('select.booleanlist','ShowThankyou','onclick="enableThankyou(this.value);"',$this->form->ShowThankyou);
			$lists['ShowContinue'] = $this->renderHTML('select.booleanlist', 'ShowContinue', !$this->form->ShowThankyou ? 'disabled="true"' : '', $this->form->ShowContinue);
			$lists['UserEmailMode'] = $this->renderHTML('select.booleanlist', 'UserEmailMode', 'onclick="enableEmailMode(\'User\', this.value)"', $this->form->UserEmailMode, JText::_('HTML'), JText::_('RSFP_COMP_FIELD_TEXT'));
			$lists['UserEmailAttach'] = $this->renderHTML('select.booleanlist', 'UserEmailAttach', 'onclick="enableAttachFile(this.value)"', $this->form->UserEmailAttach);
			$lists['AdminEmailMode'] = $this->renderHTML('select.booleanlist', 'AdminEmailMode', 'onclick="enableEmailMode(\'Admin\', this.value)"', $this->form->AdminEmailMode, JText::_('HTML'), JText::_('RSFP_COMP_FIELD_TEXT'));
			$lists['MetaTitle'] = $this->renderHTML('select.booleanlist', 'MetaTitle', '', $this->form->MetaTitle);
			$lists['TextareaNewLines'] = $this->renderHTML('select.booleanlist', 'TextareaNewLines', '', $this->form->TextareaNewLines);
			$lists['AjaxValidation'] = $this->renderHTML('select.booleanlist', 'AjaxValidation', '', $this->form->AjaxValidation);
			$lists['FormLayoutAutogenerate'] = $this->renderHTML('select.booleanlist', 'FormLayoutAutogenerate', 'onclick="changeFormAutoGenerateLayout('.$this->form->FormId.', this.value);"', $this->form->FormLayoutAutogenerate);
			
			$lists['post_enabled'] 	= $this->renderHTML('select.booleanlist', 'form_post[enabled]', '', $this->form_post->enabled);
			$lists['post_method'] 	= $this->renderHTML('select.booleanlist', 'form_post[method]', '', $this->form_post->method, JText::_('RSFP_POST_METHOD_POST'), JText::_('RSFP_POST_METHOD_GET'));
			$lists['post_silent'] 	= $this->renderHTML('select.booleanlist', 'form_post[silent]', '', $this->form_post->silent);
			
			$this->themes = $this->get('themes');
			$this->lang = $this->get('lang');
			
			$lists['Languages'] = JHTML::_('select.genericlist', $this->get('languages'), 'Language', 'onchange="submitbutton(\'changeLanguage\')"', 'value', 'text', $this->lang);
			
			$this->mappings = $this->get('mappings');
			$this->mpagination = $this->get('mpagination');
			$this->conditions = $this->get('conditions');
			$this->formId = $this->form->FormId;
			$this->emails = $this->get('emails');
			
			$this->lists = $lists;
		}
		elseif ($layout == 'new')
		{
			$nextIcon = RSFormProHelper::isJ('3.0') ? 'next' : 'forward';
			
			JToolBarHelper::custom('forms.new.steptwo', $nextIcon, $nextIcon, JText::_('JNEXT'), false);
			JToolBarHelper::cancel('forms.cancel');
		}
		elseif ($layout == 'new2')
		{
			$nextIcon = RSFormProHelper::isJ('3.0') ? 'next' : 'forward';
			
			JToolBarHelper::custom('forms.new.stepthree', $nextIcon, $nextIcon, JText::_('JNEXT'), false);
			JToolBarHelper::cancel('forms.cancel');
			
			$lists['AdminEmail'] = $this->renderHTML('select.booleanlist', 'AdminEmail', 'onclick="changeAdminEmail(this.value)"', 1);
			$lists['UserEmail'] = $this->renderHTML('select.booleanlist', 'UserEmail', '', 1);
			$actions = array(
				JHTML::_('select.option', 'refresh', JText::_('RSFP_SUBMISSION_REFRESH_PAGE')),
				JHTML::_('select.option', 'thankyou', JText::_('RSFP_SUBMISSION_THANKYOU')),
				JHTML::_('select.option', 'redirect', JText::_('RSFP_SUBMISSION_REDIRECT_TO'))				
			);
			$lists['SubmissionAction'] = JHTML::_('select.genericlist', $actions, 'SubmissionAction', 'onclick="changeSubmissionAction(this.value)"');
			
			$this->adminEmail = $this->get('adminEmail');
			$this->lists = $lists;
			$this->editor = JFactory::getEditor();
		}
		elseif ($layout == 'new3')
		{
			$nextIcon = RSFormProHelper::isJ('3.0') ? 'next' : 'forward';
			
			JToolBarHelper::custom('forms.new.stepfinal', $nextIcon, $nextIcon, JText::_('Finish'), false);
			JToolBarHelper::cancel('forms.cancel');
			
			$lists['predefinedForms'] = JHTML::_('select.genericlist', $this->get('predefinedforms'), 'predefinedForm', '');
			$this->lists = $lists;
		}
		elseif ($layout == 'component_copy')
		{
			JToolBarHelper::custom('components.copy.process', 'copy', 'copy', 'Copy', false);
			JToolBarHelper::cancel('components.copy.cancel');
			
			$formlist = $this->get('formlist');
			$lists['forms'] = JHTML::_('select.genericlist', $formlist, 'toFormId', '', 'value', 'text');
			
			$this->formId = JRequest::getInt('formId');
			$this->cids = JRequest::getVar('cid', array());
			$this->lists = $lists;
		}
		elseif ($layout == 'richtext')
		{
			$this->editor = JFactory::getEditor();
			$this->noEditor = JRequest::getInt('noEditor');
			$this->formId = JRequest::getInt('formId');
			$this->editorName = JRequest::getCmd('opener');
			$this->editorText = $this->get('editorText');
		}
		elseif ($layout == 'edit_mappings')
		{
			$formId = JRequest::getInt('formId');
			$this->mappings = $this->get('mappings');
			$this->mpagination = $this->get('mpagination');
			$this->formId = $formId;
		}
		elseif ($layout == 'edit_conditions')
		{
			$formId = JRequest::getInt('formId');
			$this->conditions = $this->get('conditions');
			$this->formId = $formId;
		}
		elseif ($layout == 'edit_emails')
		{
			$this->emails = $this->get('emails');
		}
		elseif ($layout == 'show')
		{
			$db = JFactory::getDBO();
			$lang = JFactory::getLanguage();
			$lang->load('com_rsform', JPATH_SITE);
			$formId = JRequest::getInt('formId');
			
			$db->setQuery("SELECT FormTitle FROM #__rsform_forms WHERE FormId = ".$formId." ");
			JToolBarHelper::title($db->loadResult(),'rsform');
			
			$this->formId = $formId;
		}
		elseif ($layout == 'emails')
		{
			$this->row = $this->get('email');
			$lists['mode'] = $this->renderHTML('select.booleanlist', 'mode', 'onclick="showMode(this.value);"', $this->row->mode, JText::_('HTML'), JText::_('Text'));
			$this->lists = $lists;
			$this->editor = JFactory::getEditor();
			$this->quickfields = $this->get('quickfields');
			$this->lang = $this->get('emaillang');
			$lists['Languages'] = JHTML::_('select.genericlist', $this->get('languages'), 'ELanguage', 'onchange="submitbutton(\'changeEmailLanguage\')"', 'value', 'text', $this->lang);
		}
		else
		{
			$this->addToolbar();
			$this->sidebar = $this->get('Sidebar');
			
			JToolbarHelper::addNew('forms.add', RSFormProHelper::isJ16() ? JText::_('JTOOLBAR_NEW') : JText::_('New'));
			JToolBarHelper::spacer();
			JToolBarHelper::custom('forms.copy', 'copy.png', 'copy_f2.png', JText::_('RSFP_DUPLICATE'), false);
			JToolBarHelper::spacer();
			JToolBarHelper::deleteList(JText::_('VALIDDELETEITEMS'), 'forms.delete', RSFormProHelper::isJ16() ? JText::_('JTOOLBAR_DELETE') : JText::_('DELETE'));
			JToolBarHelper::spacer();
			JToolBarHelper::publishList('forms.publish', RSFormProHelper::isJ16() ? JText::_('JTOOLBAR_PUBLISH') : JText::_('Publish'));
			JToolBarHelper::unpublishList('forms.unpublish', RSFormProHelper::isJ16() ? JText::_('JTOOLBAR_UNPUBLISH') : JText::_('Unpublish'));
		
			$this->forms = $this->get('forms');
			$this->pagination = $this->get('pagination');
		
			$this->sortColumn = $this->get('sortColumn');
			$this->sortOrder = $this->get('sortOrder');
		}
		
		parent::display($tpl);
	}
	
	function triggerEvent($event)
	{
		$app = JFactory::getApplication();
		$app->triggerEvent($event);
	}
	
	protected function renderHTML() {
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
				$name	 	= $this->escape($args[1]);
				$additional = isset($args[2]) ? (string) $args[2] : '';
				$value		= $args[3];
				$yes 	 	= isset($args[4]) ? $this->escape($args[4]) : 'JYES';
				$no 	 	= isset($args[5]) ? $this->escape($args[5]) : 'JNO';
				
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
				$yes 	 	= isset($args[4]) ? $this->escape($args[4]) : 'JYES';
				$no 	 	= isset($args[5]) ? $this->escape($args[5]) : 'JNO';
				
				return JHtml::_($args[0], $name, $additional, $value, $yes, $no);
			}
		}
	}
	
	protected function addToolbar() {
		static $called;
		
		// this is a workaround so if called multiple times it will not duplicate the buttons
		if (!$called) {
			// set title
			JToolBarHelper::title('RSForm! Pro', 'rsform');
			
			require_once JPATH_COMPONENT.'/helpers/toolbar.php';
			RSFormProToolbarHelper::addToolbar('forms');
			
			$called = true;
		}
	}
}