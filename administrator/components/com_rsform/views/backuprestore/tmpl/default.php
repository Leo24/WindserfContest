<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
JText::script('RSFP_BACKUP_SELECT');
?>

<script type="text/javascript">
function submitbutton(task)
{
	if (task == 'backup.download' && document.adminForm.boxchecked.value == 0)
		return alert(Joomla.JText._('RSFP_BACKUP_SELECT'));
	Joomla.submitform(task);
}

Joomla.submitbutton = submitbutton;
</script>

<form enctype="multipart/form-data" action="index.php?option=com_rsform" method="post" name="adminForm" id="adminForm">
	<div class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div class="span10">
	<?php
	// add the title
	$this->tabs->addTitle(JText::_('RSFP_BACKUP'), 'backup');
		
	// prepare the content
	$content = $this->loadTemplate('backup');
		
	// add the tab content
	$this->tabs->addContent($content);
	
	// add the title
	$this->tabs->addTitle(JText::_('RSFP_RESTORE'), 'restore');
		
	// prepare the content
	$content = $this->loadTemplate('restore');
		
	// add the tab content
	$this->tabs->addContent($content);
	
	// render tabs
	$this->tabs->render();
	?>
	</div>
	
	<div>
		<input type="hidden" name="task" value="restore.process"/>
		<input type="hidden" name="option" value="com_rsform"/>
		<input type="hidden" name="boxchecked" value="0"/>
	</div>
</form>