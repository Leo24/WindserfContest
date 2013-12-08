<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>
<fieldset>
	<legend><?php echo JText::_('RSFP_CLASSIC_LAYOUTS'); ?></legend>
	<?php $classicLayouts = array('inline', '2lines', '2colsinline', '2cols2lines'); ?>
	<?php foreach ($classicLayouts as $layout) { ?>
	<div class="rsform_layout_box">
		<label for="formLayout<?php echo ucfirst($layout); ?>">
			<input type="radio" id="formLayout<?php echo ucfirst($layout); ?>" name="FormLayoutName" value="<?php echo $layout; ?>" onclick="saveLayoutName('<?php echo $this->form->FormId; ?>', this.value);" <?php if ($this->form->FormLayoutName == $layout) { ?>checked="checked"<?php } ?> /><?php echo JText::_('RSFP_LAYOUT_'.$layout);?><br/>
			<img src="components/com_rsform/assets/images/layouts/<?php echo $layout; ?>.gif" width="175"/>
		</label>
	</div>
	<?php } ?>
	<span class="rsform_clear_both"></span>
</fieldset>

<fieldset>
	<legend><?php echo JText::_('RSFP_XHTML_LAYOUTS'); ?></legend>
	<?php $xhtmlLayouts = array('inline-xhtml', '2lines-xhtml', 'responsive'); ?>
	<?php foreach ($xhtmlLayouts as $layout) { ?>
	<div class="rsform_layout_box">
		<label for="formLayout<?php echo ucfirst($layout); ?>">
			<input type="radio" id="formLayout<?php echo ucfirst($layout); ?>" name="FormLayoutName" value="<?php echo $layout; ?>" onclick="saveLayoutName('<?php echo $this->form->FormId; ?>', this.value);" <?php if ($this->form->FormLayoutName == $layout) { ?>checked="checked"<?php } ?> /><?php echo JText::_('RSFP_LAYOUT_'.str_replace('-', '_', $layout));?><br/>
			<img src="components/com_rsform/assets/images/layouts/<?php echo $layout; ?>.gif" width="175"/>
		</label>
	</div>
	<?php } ?>
	<span class="rsform_clear_both"></span>
</fieldset>

<fieldset>
	<legend><?php echo JText::_('RSFP_FORM_HTML_LAYOUT'); ?></legend>
	<table border="0">
		<tr>
			<td><button class="rs_button rs_left" type="button" onclick="generateLayout('<?php echo $this->form->FormId; ?>');"><?php echo JText::_('RSFP_GENERATE_LAYOUT'); ?></button></td>
			<td><?php echo JText::_('RSFP_AUTOGENERATE_LAYOUT');?></td>
			<td><?php echo $this->lists['FormLayoutAutogenerate']; ?></td>
		</tr>
	</table>
	<table width="100%">
		<tr>
			<td valign="top">
			   <table width="98%" style="clear:both;">
					<tr>
						<td>
							<textarea rows="20" cols="75" style="width:100%;" class="codemirror-html" name="FormLayout" id="formLayout" <?php echo $this->form->FormLayoutAutogenerate ? 'readonly="readonly"' : '';?>><?php echo $this->escape($this->form->FormLayout); ?></textarea>
						</td>
					</tr>
				</table>
			</td>
			<td valign="top" width="1%" nowrap="nowrap">
				<button class="rs_button" type="button" onclick="toggleQuickAdd();"><?php echo JText::_('RSFP_TOGGLE_QUICKADD'); ?></button>
				<span class="rsform_clear_both"></span>
				<div id="QuickAdd1">
					<h3><?php echo JText::_('RSFP_QUICK_ADD');?></h3>
					<?php echo JText::_('RSFP_QUICK_ADD_DESC');?><br/><br/>
					<?php if(!empty($this->quickfields))
						foreach($this->quickfields as $quickfield) { ?>
							<strong><?php echo $quickfield;?></strong><br/>
							<pre>{<?php echo $quickfield; ?>:caption}</pre>
							<pre>{<?php echo $quickfield; ?>:body}</pre>
							<pre>{<?php echo $quickfield; ?>:validation}</pre>
							<pre>{<?php echo $quickfield; ?>:description}</pre>
							<br/>
					<?php } ?>
				</div>
			</td>
		</tr>
	</table>
</fieldset>