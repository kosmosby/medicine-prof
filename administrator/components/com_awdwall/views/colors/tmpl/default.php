<?php
/**
 * @version 3.0
 * @package JomWALL -CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
Awdwalladminhelper::awdadmintoolbar('colors');
?>
<?php $path= substr(JURI::base(),0,-14).'joomla.php';?>
<style>

td.paramlist_key  {

    background-color: #F6F6F6;

    border-bottom: 1px solid #E9E9E9;

    border-right: 1px solid #E9E9E9;

    color: #666666;

    font-weight: bold;

    text-align: right;

    width: 140px;

}

table.admintable td {

    padding: 3px;

}


</style>
<script type="text/javascript" src="components/com_awdwall/js/jscolor.js"></script>
<script type="text/javascript">
Joomla.submitbutton = function submitbutton(pressbutton) {
	if(pressbutton=='Default')
	{
		document.getElementById("default").value= 'default';
		url='<?php echo $path;?>'+'?q='+document.getElementById("default").value+'&sid='+Math.random();
		var request = new Request({
		url: url,
		method:'post',
		async: true,
		onSuccess: function(responseText){
		   window.location.href = 'index.php?option=com_awdwall&controller=colors';
		}
		}).send();		
	}
	if(pressbutton=='save')
	{
	submitform(pressbutton);	
	}
}
</script>
<div class="awdm">

<div style="display:none" id="txtHint"></div>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<fieldset class="adminform">
<legend><?php echo JText :: _('Colors')?></legend>

	<div class="color-params" style="float:left; width:250px; overflow:hidden;">

	<table width="100%" class="paramlist admintable" cellspacing="1">

<?php

	//echo $this->params->render('params', 'colors');		

	foreach($this->params as $param){

		$temp = explode(':', $param);

	?>

		<tr>

			<td width="40%" class="paramlist_key">

				<span class="editlinktip"><label id="paramscolor1-lbl" for="paramscolor1"><?php echo $temp[0];?></label></span>

			</td>

			<td class="paramlist_value">

				<input type="text" name="params[<?php echo $temp[0];?>]" id="params<?php echo $temp[0];?>" value="<?php echo $temp[1];?>" class="color" />

			</td>

		</tr>		

	<?php

		/* $label[] = $temp[0];

		$value[] = $temp[1]; */

	}

?>

	</table>



	</div>
	<?php 
			$config 		= &JComponentHelper::getParams('com_awdwall');
			$template 		= $config->get('temp', 'blue');			
		?>
	<div class="color-images" style="float: right; padding: 10px; overflow: hidden; height: 470px;">
		<img src="components/com_awdwall/images/<?php echo $template; ?>.png" style="width: 443px;">		
	</div>
    </fieldset>
	<input type="hidden" name="option" value="com_awdwall" />
	<input type="hidden" name="task" value="colors" />
	<input type="hidden" name="default" id="default" value="0" />
	<input type="hidden" name="boxchecked" value="1" />
</form>

</div>