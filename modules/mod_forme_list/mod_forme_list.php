<?php
/**
* @version 1.0.6
* @package RSform! 1.0.6
* @copyright (C) 2007 www.rsjoomla.com
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$fid 	= intval( $params->def( 'form_id', 0 ) );
$template_module = $params->def( 'template_module', '' );
$template_formdatarow = $params->def( 'template_formdatarow', '' );
$limit = intval( $params->def( 'limit', 15 ) );
$limitstart = intval( JRequest::getVar('limitstart', 0 ) );

$forme_ok = false;
if (file_exists(JPATH_SITE.DS.'components'.DS.'com_forme'.DS.'forme.php'))
	$forme_ok = true;
else
	JError::raiseWarning(500, 'The mod_forme_listr Module works only with RSForm! (different product than RSForm! Pro), which you do not have installed. Aborting...');

if ($forme_ok)
{
	global $database;
	jimport('joomla.html.pagination');

	$CONFIG = new JConfig();
	$my = & JFactory::getUser();
	$database =& JFactory::getDBO();


	$elpath = JPATH_SITE.'/components/com_forme';

	//check language
	//first check global joomfish
	$check = false;
	if(isset($_COOKIE['mbfcookie']['lang'])) $check = $_COOKIE['mbfcookie']['lang'];
	if(isset($_COOKIE['jfcookie']['lang'])) $check = $_COOKIE['jfcookie']['lang'];
	if(isset($_REQUEST['lang'])) $check = JRequest::getWord('lang',false);
	if($check){
		require_once($elpath.'/languages/'.$check.'.php');
	}else{
		require_once($elpath.'/languages/en.php');
	}

	$database->SetQuery( "SELECT count(*)"
						. "\nFROM #__forme_data AS a"
						. "\nWHERE a.form_id = '$fid'"
						);
	$total = $database->loadResult();
	$pageNav = new JPagination( $total, $limitstart, $limit );

	$database->setQuery("SELECT * FROM #__forme_data WHERE form_id = '$fid' ORDER BY date_added DESC", $pageNav->limitstart, $pageNav->limit );
	$data = $database->loadObjectList();

	//load fields
	$database->setQuery("SELECT * FROM #__forme_fields WHERE form_id = '$fid' AND published=1");
	$fields = $database->loadObjectList();

	$html = '';

	foreach($data as $i=>$data_row){
		//parse parameters
		$database->setQuery("SELECT * FROM #__users WHERE id = '$data_row->uid'");
		$user = $database->loadObject();

		$prm = array();
		$prm['jos_sitename'] = $CONFIG->sitename;
		$prm['jos_siteurl'] = JURI::base();
		$prm['jos_userip'] = $data_row->uip;
		$prm['jos_user_id'] = $data_row->uid;
		$prm['jos_date_added'] = date(_FORME_DATETIME,strtotime($data_row->date_added));
		$prm['jos_username'] = (isset($user->username)) ? $user->username : 0;
		$prm['jos_email'] = (isset($user->email)) ? $user->email : 0;
		$prm['jos_counter'] = $i+$pageNav->limitstart+1;

		$prm_explode = explode("||\n",$data_row->params);
		foreach($prm_explode as $param_row){
			$param_row = explode('=',$param_row,2);
			if(isset($param_row[1])){
				$prm[$param_row[0]] = $param_row[1];
			}else{
				$prm[$param_row[0]] = '';
			}
		}
		$temp_html = $template_formdatarow;
		foreach($fields as $field){
			if(!isset($prm[$field->name])) $prm[$field->name] = '';
			$temp_html = str_replace('{'.$field->name.'}',$prm[$field->name],$temp_html);
		}

		$html .= $temp_html;
	}


	//load fields
	$database->setQuery("SELECT * FROM #__forme_fields WHERE published = 1 AND form_id = '$fid'");
	$fields = $database->loadObjectList();
	foreach($fields as $field){
		$template_module = str_replace('{'.$field->name.'}',$field->title,$template_module);
	}
	$template_module = str_replace('{formdata}',$html,$template_module);

	echo $template_module;
	if($total>$limit) echo '<div class="pageNav">'.$pageNav->getPagesLinks($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']).'</div>';
	echo '<div class="pageNav">'.$pageNav->getPagesCounter().'</div>';
}
?>