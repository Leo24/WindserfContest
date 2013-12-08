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
function submitbutton(task)
{
	if (task == 'forms.copy' && document.adminForm.boxchecked.value == 0)
		return alert('<?php echo JText::sprintf( 'RSFP_PLEASE_MAKE_SELECTION_TO', JText::_('RSFP_COPY')); ?>');
	submitform(task);
}

<?php if (RSFormProHelper::isJ16()) { ?>
	Joomla.submitbutton = submitbutton;
<?php } ?>
</script>

<form action="index.php?option=com_rsform" method="post" name="adminForm" id="adminForm">
	<div class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div class="span10">
	<table class="adminlist table table-striped" id="articleList">
		<thead>
		<tr>
			<th width="1%" nowrap="nowrap"><?php echo JText::_('#'); ?></th>
			<th width="1%" nowrap="nowrap"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" /></th>
			<th class="title"><?php echo JHTML::_('grid.sort', JText::_('RSFP_FORM_TITLE'), 'FormTitle', $this->sortOrder, $this->sortColumn, 'forms.manage'); ?></th>
			<th class="title"><?php echo JHTML::_('grid.sort', JText::_('RSFP_FORM_NAME'), 'FormName', $this->sortOrder, $this->sortColumn, 'forms.manage'); ?></th>
			<th width="1%" nowrap="nowrap" class="title"><?php echo JHTML::_('grid.sort', JText::_('PUBLISHED'), 'Published', $this->sortOrder, $this->sortColumn, 'forms.manage'); ?></th>
			<th width="1%" nowrap="nowrap" class="title"><?php echo JText::_('RSFP_SUBMISSIONS'); ?></th>
			<th class="title" width="300"><?php echo JText::_('TOOLS'); ?></th>
			<th width="1%" nowrap="nowrap" class="title"><?php echo JHTML::_('grid.sort', JText::_('RSFP_FORM_ID'), 'FormId', $this->sortOrder, $this->sortColumn, 'forms.manage'); ?></th>
		</tr>
		</thead>
	<?php
	$i = 0;
	$k = 0;
	foreach($this->forms as $row)
	{
		$row->published = $row->Published;
		$row->FormTitle = strip_tags($row->FormTitle);
		?>
		<tr class="row<?php echo $k; ?>">
			<td width="1%" nowrap="nowrap"><?php echo $this->pagination->getRowOffset($i); ?></td>
			<td width="1%" nowrap="nowrap"><?php echo JHTML::_('grid.id', $i, $row->FormId); ?></td>
			<td><a href="index.php?option=com_rsform&amp;view=forms&amp;layout=edit&amp;formId=<?php echo $row->FormId; ?>"><?php echo !empty($row->FormTitle) ? $row->FormTitle : '<em>no title</em>'; ?></a></td>
			<td><?php echo $row->FormName; ?></td>
			<!--<td width="1%" nowrap="nowrap" align="center"><?php echo JHTML::_('grid.published', $row, $i, 'tick.png', 'publish_x.png', 'forms.'); ?></td>-->
			<td width="1%" nowrap="nowrap" align="center"><?php echo JHTML::_('jgrid.published', $row->published, $i, 'forms.'); ?></td>
			<td width="1%" nowrap="nowrap"><a href="index.php?option=com_rsform&amp;view=submissions&amp;formId=<?php echo $row->FormId; ?>">
					<?php echo JText::sprintf('RSFP_TODAY_SUBMISSIONS', $row->_todaySubmissions); ?><br/>
					<?php echo JText::sprintf('RSFP_MONTH_SUBMISSIONS', $row->_monthSubmissions); ?><br/>
					<?php echo JText::sprintf('RSFP_ALL_SUBMISSIONS', $row->_allSubmissions); ?><br/>
					</a>
			</td>
			<td align="center" nowrap="nowrap">
				<a class="rsform_icon rsform_preview" href="<?php echo JURI::root(); ?>index.php?option=com_rsform&amp;formId=<?php echo $row->FormId; ?>" target="_blank"><?php echo JText::_('PREVIEW'); ?></a>
				<a class="rsform_icon rsform_add_menu" href="index.php?option=com_rsform&amp;task=forms.menuadd.screen&amp;formId=<?php echo $row->FormId; ?>"><?php echo RSFormProHelper::isJ16() ? JText::_('LINK_TO_MENU') : JText::_('LINK TO MENU'); ?></a>
				<?php if ($row->Backendmenu) { ?>
				<a class="rsform_icon rsform_add_backend_menu" href="index.php?option=com_rsform&amp;task=forms.menuremove.backend&amp;formId=<?php echo $row->FormId; ?>"><?php echo JText::_('LINK_TO_BACKEND_REMOVE_MENU'); ?></a>
				<?php } else { ?>
				<a class="rsform_icon rsform_add_backend_menu" href="index.php?option=com_rsform&amp;task=forms.menuadd.backend&amp;formId=<?php echo $row->FormId; ?>"><?php echo JText::_('LINK_TO_BACKEND_MENU'); ?></a>
				<?php } ?>
				<a class="rsform_icon rsform_clear" href="index.php?option=com_rsform&amp;task=submissions.clear&amp;formId=<?php echo $row->FormId; ?>" onclick="return (confirm('<?php echo JText::_('VALIDDELETEITEMS', true); ?>'));"><?php echo JText::_('RSFP_CLEAR_SUBMISSIONS'); ?></a>
			</td>
			<td width="1%" nowrap="nowrap"><?php echo $row->FormId; ?></td>
		</tr>
	<?php
		$i++;
		$k=1-$k;
	}
	?>
	<tfoot>
	<tr>
		<td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td>
	</tr>
	</tfoot>
	</table>
	</div>
	
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="option" value="com_rsform" />
	<input type="hidden" name="task" value="forms.manage" />
	
	<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortOrder; ?>" />
</form>