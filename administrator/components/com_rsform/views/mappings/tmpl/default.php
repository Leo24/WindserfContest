<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>
<form action="index.php?option=com_rsform" method="post" name="adminForm">
<input type="hidden" name="formId" value="<?php echo $this->formId; ?>" />
<div id="tablers">
<table class="admintable">
	<tr>
		<td width="160" style="width: 160px;" align="right" class="key"><?php echo JText::_('RSFP_FORM_MAPPINGS_CONNECTION') ?></td>
		<td>
			<span id="mpConnectionOn">
				<?php
					if ($this->mapping->id)
					{
						echo $this->mapping->connection ? JText::_('RSFP_FORM_MAPPINGS_CONNECTION_REMOTE') : JText::_('RSFP_FORM_MAPPINGS_CONNECTION_LOCAL');
						echo '<input type="hidden" name="connection" value="'.$this->mapping->connection.'" />';
					}
					else echo $this->lists['MappingConnection']; 
				?>
			</span>
			<span id="mpConnectionOff" style="display:none;"></span>
		</td>
	</tr>
	<tr>
		<td width="160" style="width: 160px;" align="right" class="key"><?php echo JText::_('RSFP_FORM_MAPPINGS_METHOD') ?></td>
		<td>
			<span id="mpMethodOn">
				<?php
					if ($this->mapping->id)
					{
						if ($this->mapping->method == 0)
							echo JText::_('RSFP_FORM_MAPPINGS_METHOD_INSERT');
						elseif ($this->mapping->method == 1)
							echo JText::_('RSFP_FORM_MAPPINGS_METHOD_UPDATE');
						else echo JText::_('RSFP_FORM_MAPPINGS_METHOD_DELETE');
						echo '<input type="hidden" name="method" id="method" value="'.$this->mapping->method.'" />';
					}
					else echo $this->lists['MappingMethod']; 
				?>
			</span>
			<span id="mpMethodOff" style="display:none;"></span>
		</td>
	</tr>
	<tbody id="mappingsid">
	<tr>
		<td width="160" style="width: 160px;" align="right" class="key"><?php echo JText::_('RSFP_FORM_MAPPINGS_HOST') ?></td>
		<td>
			<span id="mpHostOn">
				<?php if ($this->mapping->id) { ?>
				<?php echo $this->escape($this->mapping->host); ?>
				<input type="hidden" name="host" value="<?php echo $this->escape($this->mapping->host); ?>" />
				<?php } else { ?>
				<input type="text" class="rs_inp rs_50" name="host" id="MappingHost" value="<?php echo $this->escape($this->mapping->host); ?>" size="50" />
				<?php } ?>
			</span>
			<span id="mpHostOff" style="display:none;"></span>
			<span id="mpPortOn">
				<?php if ($this->mapping->id) { ?>
				<?php echo ':'.$this->escape($this->mapping->port); ?>
				<input type="hidden" name="port" value="<?php echo $this->escape($this->mapping->port); ?>" />
				<?php } else { ?>
				<?php echo JText::_('RSFP_FORM_MAPPINGS_PORT'); ?> : <input type="text" class="rs_inp rs_10" name="port" id="MappingPort" value="<?php echo $this->escape($this->mapping->port); ?>" size="5" />
				<?php } ?>
			</span>
		</td>
	</tr>
	<tr>
		<td width="160" style="width: 160px;" align="right" class="key"><?php echo JText::_('RSFP_FORM_MAPPINGS_USERNAME') ?></td>
		<td>
			<span id="mpUsernameOn">
				<?php if ($this->mapping->id) { ?>
				<?php echo $this->escape($this->mapping->username); ?>
				<input type="hidden" name="username" value="<?php echo $this->escape($this->mapping->username); ?>" />
				<?php } else { ?>
				<input type="text" class="rs_inp rs_50" name="username" id="MappingUsername" value="<?php echo $this->escape($this->mapping->username); ?>" size="50" />
				<?php } ?>
			</span>
			<span id="mpUsernameOff" style="display:none;"></span>
		</td>
	</tr>
	<tr>
		<td width="160" style="width: 160px;" align="right" class="key"><?php echo JText::_('RSFP_FORM_MAPPINGS_PASSWORD') ?></td>
		<td>
			<span id="mpPasswordOn">
				<?php if ($this->mapping->id) { ?>
				<?php echo $this->escape($this->mapping->password); ?>
				<input type="hidden" name="password" value="<?php echo $this->escape($this->mapping->password); ?>" />
				<?php } else { ?>
				<input type="password" class="rs_inp rs_50" name="password" id="MappingPassword" value="<?php echo $this->escape($this->mapping->password); ?>" size="50" />
				<?php } ?>
			</span>
			<span id="mpPasswordOff" style="display:none;"></span>
		</td>
	</tr>
	<tr>
		<td width="160" style="width: 160px;" align="right" class="key"><?php echo JText::_('RSFP_FORM_MAPPINGS_DATABASE') ?></td>
		<td>
			<span id="mpDatabaseOn">
				<?php if ($this->mapping->id) { ?>
				<?php echo $this->escape($this->mapping->database); ?>
				<input type="hidden" name="database" value="<?php echo $this->escape($this->mapping->database); ?>" />
				<?php } else { ?>
				<input type="text" class="rs_inp rs_50" name="database" id="MappingDatabase" value="<?php echo $this->escape($this->mapping->database); ?>" size="50" />
				<?php } ?>
			</span>
			<span id="mpDatabaseOff" style="display:none;"></span>
		</td>
	</tr>
	</tbody>
	<?php if (!$this->mapping->id) { ?>
	<tr>
		<td width="160" style="width: 160px;" align="right">&nbsp;</td>
		<td>
			<button class="rs_button" type="button" id="connectBtn" onclick="javascript: mpConnect();"><?php echo JText::_('RSFP_FORM_MAPPINGS_CONNECT'); ?></button> 
			<img id="mappingloader" src="<?php echo JURI::root(); ?>administrator/components/com_rsform/assets/images/loading.gif" style="vertical-align: middle; display: none;" />
		</td>
	</tr>
	<?php } ?>
</table>
</div>
<span id="rsfpmappingContent">
<?php if (!empty($this->mapping->id)) { ?>
	<table class="admintable">
		<tr>
			<td width="160" style="width: 160px;" align="right" class="key"><?php echo JText::_('RSFP_FORM_MAPPINGS_TABLE'); ?></td>
			<td>
				<?php echo $this->lists['tables']; ?>
				<img id="mappingloader2" src="<?php echo JURI::root(); ?>administrator/components/com_rsform/assets/images/loading.gif" style="vertical-align: middle; display: none;" /></td>
		</tr>
	</table>
<?php } ?>
</span>
<br /><br />
<span id="rsfpmappingColumns">
<?php if (!empty($this->mapping->id) && ($this->mapping->method == 0 || $this->mapping->method == 1)) { ?>
	<?php echo RSFormProHelper::mappingsColumns($this->config,'set',$this->mapping); ?>
<?php } ?>
</span>
<br /><br />
<span id="rsfpmappingWhere">
<?php if (!empty($this->mapping->id) && ($this->mapping->method == 1 || $this->mapping->method == 2)) { ?>
	<?php echo RSFormProHelper::mappingsColumns($this->config,'where',$this->mapping); ?>
<?php } ?>
</span>

<script type="text/javascript">
	function enableDbDetails(value)
	{
		if (value == 1)
			document.getElementById('mappingsid').style.display = '';
		else
			document.getElementById('mappingsid').style.display = 'none';
	}	
	enableDbDetails(<?php echo $this->mapping->connection; ?>);
	
	function returnMappingsExtra()
	{
		var extra = new Array();
		extra.push('last_insert_id');
		return extra;
	}
	
	function returnQuickFields() 
	{
		var quickfields = new Array();
		<?php
		if (!empty($this->fields))
		foreach ($this->fields as $field)
			echo 'quickfields.push(\''.$field.'\');'."\n";
		?>
		return quickfields;
	}
</script>
	<input type="hidden" name="option" value="com_rsform" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="controller" value="mappings" />
	<input type="hidden" name="id" id="mappingid" value="<?php echo $this->mapping->id; ?>" />
</form>