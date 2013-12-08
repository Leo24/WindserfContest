<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');
?>
<script type="text/javascript">
function submitbutton(pressbutton)
{
	var form = document.adminForm;
	
	submitform(pressbutton);
}

<?php if (RSFormProHelper::isJ16()) { ?>
	Joomla.submitbutton = submitbutton;
<?php } ?>
</script>

<form action="index.php?option=com_rsform" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<div class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div class="span10">
	<?php
	foreach ($this->fieldsets as $name => $fieldset) {
		// add the tab title
		$this->tabs->addTitle($fieldset->label, $fieldset->name);
		
		// prepare the content
		$this->fieldset =& $fieldset;
		$this->fields 	= $this->form->getFieldset($fieldset->name);
		$content = $this->loadTemplate($fieldset->name);
		
		// add the tab content
		$this->tabs->addContent($content);
	}
	
	$this->triggerEvent('rsfp_bk_onAfterShowConfigurationTabs', array($this->tabs));
	
	// render tabs
	$this->tabs->render();
	?>
	</div>
	
	<div>
		<?php echo JHtml::_('form.token'); ?>
		<input type="hidden" name="option" value="com_rsform" />
		<input type="hidden" name="task" value="" />
	</div>
</form>