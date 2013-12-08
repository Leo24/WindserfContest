<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$out = '';
$out.='<h2>{global:formtitle}</h2>'."\n";
$out.='{error}'."\n";

$page_num = 0;
$out.= '<!-- Do not remove this ID, it is used to identify the page so that the pagination script can work correctly -->'."\n";
$out.='<fieldset class="formHorizontal formContainer" id="rsform_'.$formId.'_page_'.$page_num.'">'."\n";

foreach ($quickfields as $quickfield)
{	
	// skip...
	if (in_array($quickfield, $hiddenfields)) {
		continue;
	}
	
	// handle pagebreaks
	if (in_array($quickfield, $pagefields))
	{
		$page_num++;
		$last_page  = $quickfield == end($pagefields);
		$last_field = $quickfield == end($quickfields);
		
		$out.="\t".'<div class="rsform-block rsform-block-'.JFilterOutput::stringURLSafe($quickfield).'">'."\n";
		$out.= "\t\t".'<div class="formControlLabel">&nbsp;</div>'."\n";
		$out.= "\t\t".'<div class="formControls">'."\n";
		$out.= "\t\t".'<div class="formBody">{'.$quickfield.':body}</div>'."\n";
		$out.= "\t\t".'</div>'."\n";
		$out.="\t".'</div>'."\n";
		
		$out .= "\t".'</fieldset>'."\n";
		if (!$last_page || !$last_field)
		{
			$out.= '<!-- Do not remove this ID, it is used to identify the page so that the pagination script can work correctly -->'."\n";
			$out.='<fieldset class="formHorizontal formContainer" id="rsform_'.$formId.'_page_'.$page_num.'">'."\n";
		}
		
		continue;
	}
	
	// handle standard fields
	$required = in_array($quickfield, $requiredfields) ? '<strong class="formRequired">'.(isset($this->_form->Required) ? $this->_form->Required : '(*)').'</strong>' : "";
	$out.="\t".'<div class="rsform-block rsform-block-'.JFilterOutput::stringURLSafe($quickfield).'">'."\n";
	$out.= "\t\t".'<div class="formControlLabel">{'.$quickfield.':caption}'.$required.'</div>'."\n";
	$out.= "\t\t".'<div class="formControls">'."\n";
	$out.= "\t\t".'<div class="formBody">{'.$quickfield.':body}<span class="formValidation">{'.$quickfield.':validation}</span></div>'."\n";
	$out.= "\t\t".'<p class="formDescription">{'.$quickfield.':description}</p>'."\n";
	$out.= "\t\t".'</div>'."\n";
	$out.="\t".'</div>'."\n";
}

$out.='</fieldset>'."\n";

if ($out != $this->_form->FormLayout && $this->_form->FormId)
{
	// Clean it
	// Update the layout
	$db = JFactory::getDBO();
	$db->setQuery("UPDATE #__rsform_forms SET FormLayout='".$db->escape($out)."' WHERE FormId=".$this->_form->FormId);
	$db->execute();
}
	
return $out;