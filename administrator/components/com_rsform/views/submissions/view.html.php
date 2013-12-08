<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.html.pane');

class RSFormViewSubmissions extends JViewLegacy
{
	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		
		JToolBarHelper::title('RSForm! Pro','rsform');
		
		// adding the toolbar on 2.5
		if (!RSFormProHelper::isJ('3.0')) {
			$this->addToolbar();
		}
		
		$layout = strtolower($this->getLayout());
		
		if ($layout == 'export')
		{
			JToolBarHelper::custom('submissions.export.task', 'archive', 'archive', JText::_('RSFP_EXPORT'), false);
			JToolBarHelper::spacer();
			JToolBarHelper::cancel('submissions.manage');
			
			$this->formId = $this->get('formId');
			$this->headers = $this->get('headers');
			$this->staticHeaders = $this->get('staticHeaders');
			
			$previewArray = array();
			$i = 0;
			foreach ($this->staticHeaders as $header)
			{
				$i++;
				$previewArray[] = 'Value '.$i;
			}
			foreach ($this->headers as $header)
			{
				$i++;
				$previewArray[] = 'Value '.$i;
			}
			$this->previewArray = $previewArray;
			
			$this->formTitle = $this->get('formTitle');
			$this->exportSelected = $this->get('exportSelected');
			$this->exportSelectedCount = count($this->exportSelected);
			$this->exportAll = $this->exportSelectedCount == 0;
			$this->exportType = $this->get('exportType');
			$this->exportFile = $this->get('exportFile');
			
			$formTitle = $this->get('formTitle');
			JToolBarHelper::title('RSForm! Pro <small>['.JText::sprintf('RSFP_EXPORTING', $this->exportType, $formTitle).']</small>','rsform');
			
			// tabs
			$this->tabs = $this->get('RSTabs');
		}
		elseif ($layout == 'exportprocess')
		{
			$this->limit = 500;
			$this->total = $this->get('exportTotal');
			$this->file = JRequest::getCmd('ExportFile');
			$this->exportType = JRequest::getCmd('exportType');
			$this->formId	= $this->get('FormId');
			
			$formTitle = $this->get('formTitle');
			JToolBarHelper::title('RSForm! Pro <small>['.JText::sprintf('RSFP_EXPORTING', $this->exportType, $formTitle).']</small>','rsform');
		}
		elseif ($layout == 'edit')
		{
			JToolBarHelper::custom('submission.export.pdf', 'archive', 'archive', JText::_('RSFP_EXPORT_PDF'), false);
			JToolBarHelper::spacer();
			JToolBarHelper::apply('submissions.apply');
			JToolBarHelper::save('submissions.save');
			JToolBarHelper::spacer();
			JToolBarHelper::cancel('submissions.manage');
			
			$this->formId = $this->get('submissionFormId');
			$this->submissionId = $this->get('submissionId');
			$this->submission = $this->get('submission');
			$this->staticHeaders = $this->get('staticHeaders');
			$this->staticFields = $this->get('staticFields');
			$this->fields = $this->get('editFields');
		}
		else
		{
			JToolBarHelper::custom('submissions.export.csv', 'archive', 'archive', JText::_('RSFP_EXPORT_CSV'), false);
			JToolBarHelper::custom('submissions.export.ods', 'archive', 'archive', JText::_('RSFP_EXPORT_ODS'), false);
			JToolBarHelper::custom('submissions.export.excel', 'archive', 'archive', JText::_('RSFP_EXPORT_EXCEL'), false);
			JToolBarHelper::custom('submissions.export.xml', 'archive', 'archive', JText::_('RSFP_EXPORT_XML'), false);
			JToolBarHelper::spacer();
			
			$backIcon = RSFormProHelper::isJ('3.0') ? 'previous' : 'back';
			$sendIcon = RSFormProHelper::isJ('3.0') ? 'mail' : 'send';
			
			JToolBarHelper::custom('submissions.cancelform', $backIcon, $backIcon, JText::_('RSFP_BACK_TO_FORM'), false);
			JToolBarHelper::spacer();
			JToolBarHelper::custom('submissions.resend', $sendIcon, $sendIcon, JText::_('RSFP_RESEND_EMAILS'), false);
			JToolbarHelper::editList('submissions.edit', RSFormProHelper::isJ16() ? JText::_('JTOOLBAR_EDIT') : JText::_('Edit'));
			JToolBarHelper::deleteList(JText::_('VALIDDELETEITEMS'), 'submissions.delete', RSFormProHelper::isJ16() ? JText::_('JTOOLBAR_DELETE') : JText::_('DELETE'));
			JToolBarHelper::spacer();
			JToolBarHelper::cancel('submissions.cancel', RSFormProHelper::isJ16() ? JText::_('JTOOLBAR_CLOSE') : JText::_('Close'));
			
			$forms = $this->get('forms');
			$formId = $this->get('formId');
		
			$formTitle = $this->get('formTitle');
			JToolBarHelper::title('RSForm! Pro <small>['.$formTitle.']</small>','rsform');
		
			$this->headers = $this->get('headers');
			$this->staticHeaders = $this->get('staticHeaders');
			$this->submissions = $this->get('submissions');
			$this->pagination = $this->get('pagination');
			$this->sortColumn = $this->get('sortColumn');
			$this->sortOrder = $this->get('sortOrder');
		
			$this->filter = $this->get('filter');
			$this->formId = $formId;
		
			$calendars['from'] = JHTML::calendar($this->get('dateFrom'), 'dateFrom', 'dateFrom');
			$calendars['to']   = JHTML::calendar($this->get('dateTo'), 'dateTo', 'dateTo');
			$this->calendars = $calendars;
		
			$lists['Languages'] = JHTML::_('select.genericlist', $this->get('languages'), 'Language', '', 'value', 'text', $this->get('lang'));
		
			$lists['forms'] = JHTML::_('select.genericlist', $forms, 'formId', 'onchange="submissionChangeForm(this.value)"', 'value', 'text', $formId);
			$this->lists = $lists;
		}
		
		parent::display($tpl);
	}
	
	function isHeaderEnabled($header, $static=0)
	{
		if (!isset($this->headersEnabled))
			$this->headersEnabled = $this->get('headersEnabled');
		
		$array = 'headers';
		if ($static)
			$array = 'staticHeaders';
		
		if (empty($this->headersEnabled->headers) && empty($this->headersEnabled->staticHeaders))
			return true;
		
		return in_array($header, $this->headersEnabled->{$array});
	}
	
	protected function addToolbar() {
		static $called;
		
		// this is a workaround so if called multiple times it will not duplicate the buttons
		if (!$called) {
			// set title
			JToolBarHelper::title('RSForm! Pro', 'rsform');
			
			require_once JPATH_COMPONENT.'/helpers/toolbar.php';
			RSFormProToolbarHelper::addToolbar('submissions');
			
			$called = true;
		}
	}
}