<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class RSFormControllerSubmissions extends RSFormController
{
	function __construct()
	{
		parent::__construct();
		
		$this->registerTask('apply', 		'save');
		$this->registerTask('exportCSV', 	'export');
		$this->registerTask('exportODS', 	'export');
		$this->registerTask('exportExcel', 	'export');
		$this->registerTask('exportXML', 	'export');
		
		$this->_db = JFactory::getDBO();
	}

	function manage()
	{
		$app	= JFactory::getApplication();
		$model	= $this->getModel('submissions');
		$formId = $model->getFormId();
		
		$app->redirect('index.php?option=com_rsform&view=submissions'.($formId ? '&formId='.$formId : ''));
	}
	
	function back() {
		$app	= JFactory::getApplication();
		$formId = $app->input->getInt('formId');
		$app->redirect('index.php?option=com_rsform&view=submissions&formId='.$formId);
	}
	
	function edit()
	{
		$model = $this->getModel('submissions');
		$cid   = $model->getSubmissionId();
		$app = JFactory::getApplication();
		$app->redirect('index.php?option=com_rsform&view=submissions&layout=edit&cid='.$cid);
	}
	
	function columns()
	{
		$app 	= JFactory::getApplication();
		$formId = JRequest::getInt('formId');
		
		$this->_db->setQuery("DELETE FROM #__rsform_submission_columns WHERE FormId='".$formId."'");
		$this->_db->execute();
		
		$staticcolumns = JRequest::getVar('staticcolumns', array());
		foreach ($staticcolumns as $column)
		{
			$this->_db->setQuery("INSERT INTO #__rsform_submission_columns SET FormId='".$formId."', ColumnName='".$this->_db->escape($column)."', ColumnStatic='1'");
			$this->_db->execute();
		}
		
		$columns = JRequest::getVar('columns', array());
		foreach ($columns as $column)
		{
			$this->_db->setQuery("INSERT INTO #__rsform_submission_columns SET FormId='".$formId."', ColumnName='".$this->_db->escape($column)."', ColumnStatic='0'");
			$this->_db->execute();
		}
		
		$app->redirect('index.php?option=com_rsform&view=submissions&formId='.$formId);
	}
	
	function save()
	{
		$app 	= JFactory::getApplication();
		// Get the model
		$model = $this->getModel('submissions');
		
		// Save
		$model->save();
		
		$task = $this->getTask();
		switch ($task)
		{
			case 'apply':
				$cid  = $model->getSubmissionId();
				$link = 'index.php?option=com_rsform&view=submissions&layout=edit&cid='.$cid;
			break;
		
			case 'save':
				$link = 'index.php?option=com_rsform&view=submissions';
			break;
		}
		
		$app->redirect($link, JText::_('RSFP_SUBMISSION_SAVED'));
	}
	
	function resend()
	{
		$app 	= JFactory::getApplication();
		$formId = JRequest::getInt('formId');
		$cid 	= JRequest::getVar('cid', array(), 'post');
		JArrayHelper::toInteger($cid);
		
		foreach ($cid as $SubmissionId)
			RSFormProHelper::sendSubmissionEmails($SubmissionId);
		
		$app->redirect('index.php?option=com_rsform&view=submissions&formId='.$formId, JText::_('RSFP_SUBMISSION_MAILS_RESENT'));
	}
	
	function cancel()
	{
		$app = JFactory::getApplication();
		$app->redirect('index.php?option=com_rsform');
	}
	
	function cancelForm()
	{
		$app 	= JFactory::getApplication();
		$formId = $app->input->getInt('formId');
		$app->redirect('index.php?option=com_rsform&view=forms&layout=edit&formId='.$formId);
	}
	
	function clear()
	{
		$app 	= JFactory::getApplication();
		$formId = JRequest::getInt('formId');
		$model 	= $this->getModel('submissions');
		
		$model->deleteSubmissionFiles($formId);
		$total = $model->deleteSubmissions($formId);
		
		$app->redirect('index.php?option=com_rsform&view=forms', JText::sprintf('RSFP_SUBMISSIONS_CLEARED', $total));
	}
	
	function delete()
	{
		$app 	= JFactory::getApplication();
		$formId = JRequest::getInt('formId');
		$cid 	= JRequest::getVar('cid', array(), 'post');
		JArrayHelper::toInteger($cid);
		
		$model = $this->getModel('submissions');
		$model->deleteSubmissionFiles($cid);
		$model->deleteSubmissions($cid);
		
		$app->redirect('index.php?option=com_rsform&view=submissions&formId='.$formId);
	}
	
	function export()
	{
		$app 	  = JFactory::getApplication();
		$config   = JFactory::getConfig();
		
		$model 	= $this->getModel('submissions');
		$formId = $model->getFormId();
		
		$tmp_path = $config->get('tmp_path');
		if (!is_writable($tmp_path))
		{
			JError::raiseWarning(500, JText::sprintf('RSFP_EXPORT_ERROR_MSG', $tmp_path));
			$app->redirect('index.php?option=com_rsform&view=submissions');
		}
		
		$view 	= $this->getView('submissions', 'html');
		$model 	= $this->getModel('submissions');
		$view->setLayout('export');
		$view->setModel($model, true);
		
		$view->display();
	}
	
	function exportProcess()
	{
		$mainframe = JFactory::getApplication();
		$option    =  JRequest::getVar('option', 'com_rsform');
		
		$config = JFactory::getConfig();
	
		// Get post
		$session = JFactory::getSession();
		$post = $session->get($option.'.export.data', serialize(array()));
		$post = unserialize($post);
		
		// Limit
		$start = JRequest::getInt('exportStart');
		$mainframe->setUserState($option.'.submissions.limitstart', $start);
		$limit = JRequest::getInt('exportLimit', 500);
		$mainframe->setUserState($option.'.submissions.limit', $limit);
		
		// Tmp path
		$tmp_path = $config->get('tmp_path');
		$file = $tmp_path.'/'.$post['ExportFile'];
		
		$formId = $post['formId'];
		
		// Type
		$type = strtolower($post['exportType']);
		
		// Selected rows or all rows
		$rows = !empty($post['ExportRows']) ? explode(',', $post['ExportRows']) : '';
		
		// Use headers ?
		$use_headers = (int) $post['ExportHeaders'];
		
		// Headers and ordering
		$staticHeaders = $post['ExportSubmission'];
		$headers = $post['ExportComponent'];
		$order = $post['ExportOrder'];
		
		// Remove headers that we're not going to export
		foreach ($order as $name => $id)
		{
			if (!isset($staticHeaders[$name]) && !isset($headers[$name]))
				unset($order[$name]);
		}
		
		// Adjust order array
		$order = array_flip($order);
		ksort($order);
		
		$model = $this->getModel('submissions');
		$model->export = true;
		$model->rows = $rows;
		$model->_query = $model->_buildQuery();
		$submissions = $model->getSubmissions();
		
		// CSV Options
		if ($type == 'csv')
		{
			$delimiter = str_replace(array('\t', '\n', '\r'), array("\t","\n","\r"), $post['ExportDelimiter']);
			$enclosure = str_replace(array('\t', '\n', '\r'), array("\t","\n","\r"), $post['ExportFieldEnclosure']);
			
			// Create and open file for writing if this is the first call
			// If not, just append to the file
			// Using fopen() because JFile::write() lacks such options
			$handle = fopen($file, $start == 0 ? 'w' : 'a');
			
			if ($start == 0 && $use_headers)
			{
				fwrite($handle, $enclosure.implode($enclosure.$delimiter.$enclosure,$order).$enclosure);
				fwrite($handle, "\n");
			}
			
			if (empty($submissions))
			{
				fclose($handle);
				// Adjust pagination
				$mainframe->setUserState($option.'.submissions.limitstart', 0);
				$mainframe->setUserState($option.'.submissions.limit', $mainframe->getCfg('list_limit'));
				echo 'END';
			}
			else
			{
				foreach ($submissions as $submissionId => $submission)
				{
					foreach ($order as $orderId => $header)
					{
						if (isset($submission['SubmissionValues'][$header]))
						{
							$submission['SubmissionValues'][$header]['Value'] = ereg_replace("\015(\012)?", "\012", $submission['SubmissionValues'][$header]['Value']);
							// Is this right ?
							if (strpos($submission['SubmissionValues'][$header]['Value'],"\n") !== false)
								$submission['SubmissionValues'][$header]['Value'] = str_replace("\n",' ',$submission['SubmissionValues'][$header]['Value']);
						}
						fwrite($handle, $enclosure.(isset($submission['SubmissionValues'][$header]) ? str_replace(array('\\r','\\n','\\t',$enclosure), array("\015","\012","\011",$enclosure.$enclosure), $submission['SubmissionValues'][$header]['Value']) : (isset($submission[$header]) ? $submission[$header] : '')).$enclosure.($header != end($order) ? $delimiter : ""));
					}
					fwrite($handle, "\n");
				}
				fclose($handle);
			}
		}
		// Excel Options
		elseif ($type == 'excel')
		{
			require_once(JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/excel.php');
			
			$xls = new RSFormProXLS();
			$xls->use_headers = $use_headers;
			$xls->open($file, $start == 0 ? 'w' : 'a', $start);
			
			if ($start == 0 && $use_headers)
				$xls->write_headers($order);
			
			if (empty($submissions))
			{
				$xls->close();
				// Adjust pagination
				$mainframe->setUserState($option.'.submissions.limitstart', 0);
				$mainframe->setUserState($option.'.submissions.limit', $mainframe->getCfg('list_limit'));
				echo 'END';
			}
			else
			{
				$array = array();
				foreach ($submissions as $submissionId => $submission)
				{
					$item = array();
					foreach ($order as $orderId => $header)
					{
						if (isset($submission['SubmissionValues'][$header]))
							$item[$header] = $submission['SubmissionValues'][$header]['Value'];
						elseif (isset($submission[$header]))
							$item[$header] = $submission[$header];
						else
							$item[$header] = '';
					}
					
					$array[] = $item;
				}
				$xls->write($array);
				$xls->close();
			}
		}
		// XML Options
		elseif ($type == 'xml')
		{
			$handle = fopen($file, $start == 0 ? 'w' : 'a');
			
			if ($start == 0)
			{
				$buffer = '';
				$buffer .= '<?xml version="1.0" encoding="utf-8"?>'."\n";
				$buffer .= '<form>'."\n";
				$buffer .= '<title><![CDATA['.$model->getFormTitle().']]></title>'."\n";
				$buffer .= "\t".'<submissions>'."\n";
				fwrite($handle, $buffer);
			}
			
			if (empty($submissions))
			{
				$buffer = '';
				$buffer .= "\t".'</submissions>'."\n";
				$buffer .= '</form>';
				fwrite($handle, $buffer);
				fclose($handle);
				// Adjust pagination
				$mainframe->setUserState($option.'.submissions.limitstart', 0);
				$mainframe->setUserState($option.'.submissions.limit', $mainframe->getCfg('list_limit'));
				echo 'END';
			}
			else
			{
				foreach ($submissions as $submissionId => $submission)
				{
					fwrite($handle, "\t\t".'<submission>'."\n");
					$buffer = '';
					foreach ($order as $orderId => $header)
					{
						if (isset($submission['SubmissionValues'][$header]))
							$item = $submission['SubmissionValues'][$header]['Value'];
						elseif (isset($submission[$header]))
							$item = $submission[$header];
						else
							$item = '';
						
						if (!is_numeric($item))
							$item = '<![CDATA['.$item.']]>';
						
						$header = preg_replace('#\s+#', '', $header);
						
						$buffer .= "\t\t\t".'<'.$header.'>'.$item.'</'.$header.'>'."\n";
					}
					fwrite($handle, $buffer);
					fwrite($handle, "\t\t".'</submission>'."\n");
				}
				fclose($handle);
			}
		} elseif ($type == 'ods') {
			require_once JPATH_COMPONENT.'/helpers/ods.php';
			
			$ods = new RSFormProODS($file);
			if ($start == 0) {
				$ods->startDoc();
				$ods->startSheet();
				if ($use_headers) {
					foreach ($order as $orderId => $header) {
						$ods->addCell($orderId, $header, 'string');
					}
					$ods->saveRow();
				}
			}
			
			if (empty($submissions)) {
				$ods->endSheet();
				$ods->endDoc();
				$ods->saveOds();
				
				// Adjust pagination
				$mainframe->setUserState($option.'.submissions.limitstart', 0);
				$mainframe->setUserState($option.'.submissions.limit', $mainframe->getCfg('list_limit'));
				echo 'END';
			} else {
				foreach ($submissions as $submissionId => $submission) {
					foreach ($order as $orderId => $header) {
						if (isset($submission['SubmissionValues'][$header]))
							$item = $submission['SubmissionValues'][$header]['Value'];
						elseif (isset($submission[$header]))
							$item = $submission[$header];
						else
							$item = '';
						
						if (is_numeric($item)) {
							$ods->addCell($orderId, (float) $item, 'float');
						} else {
							$ods->addCell($orderId, $item, 'string');
						}
					}
					$ods->saveRow();
				}
			}
		}
		
		exit();
	}
	
	function exportTask()
	{
		$session = JFactory::getSession();
		$option  = JRequest::getVar('option', 'com_rsform');
		
		$session->set($option.'.export.data', serialize(JRequest::get('post', JREQUEST_ALLOWRAW)));
		
		$view 	= $this->getView('submissions', 'html');
		$model 	= $this->getModel('submissions');
		$view->setLayout('exportprocess');
		$view->setModel($model, true);
		$view->display();
	}
	
	function exportFile()
	{
		$config = JFactory::getConfig();
		$file = JRequest::getCmd('ExportFile');
		$file = $config->get('tmp_path').'/'.$file;
		
		$type = JRequest::getCmd('ExportType');
		$extension = 'csv';
		
		switch ($type) {
			default:
				$extension = $type;
			break;
			
			case 'ods':	
				$extension = 'ods';
				$file = $file.'.ods';
			break;
			
			case 'excel':
				$extension = 'xls';
			break;
		}
		
		RSFormProHelper::readFile($file, date('Y-m-d').'_rsform.'.$extension);
	}
	
	function viewFile()
	{
		$app	= JFactory::getApplication();
		$id 	= JRequest::getInt('id');
		
		$this->_db->setQuery("SELECT * FROM #__rsform_submission_values WHERE SubmissionValueId='".$id."'");
		$result = $this->_db->loadObject();
		
		// Not found
		if (empty($result)) {
			$app->redirect('index.php?option=com_rsform&view=submissions');
		}
		
		// Not an upload field
		$this->_db->setQuery("SELECT c.ComponentTypeId FROM #__rsform_properties p LEFT JOIN #__rsform_components c ON (p.ComponentId=c.ComponentId) WHERE p.PropertyName='NAME' AND p.PropertyValue='".$this->_db->escape($result->FieldName)."'");
		$type = $this->_db->loadResult();
		if ($type != 9) {
			$app->redirect('index.php?option=com_rsform&view=submissions', JText::_('RSFP_VIEW_FILE_NOT_UPLOAD'));
		}
		
		jimport('joomla.filesystem.file');
		
		if (JFile::exists($result->FieldValue))
			RSFormProHelper::readFile($result->FieldValue);
		
		$app->redirect('index.php?option=com_rsform&view=submissions', JText::_('RSFP_VIEW_FILE_NOT_FOUND'));
	}
}