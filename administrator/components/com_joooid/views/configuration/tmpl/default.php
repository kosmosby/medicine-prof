<?php defined('_JEXEC') or die;?>
<style type="text/css">
fieldset dl dt, fieldset div{padding-left:2em;}
</style>

<fieldset>
	<legend><?php echo "Joooid Configuration";?></legend>

<?php
//jimport('joomlas.language.multilang');
$endpoint = '/index.php?option=com_joooid';
$config =& JFactory::getConfig();
$l =& JFactory::getLanguage();
$lang = "";
$isMultiLang = & JLanguageMultilang::isEnabled();
if($isMultiLang=="1"){
	$code = $l->getLocale();
	$lang = $code[4];
	$endpoint .="&lang=".$lang;
}

if($config->get('sef')=="1"){
	$endpoint = "/index.php/component/joooid/";
	if($isMultiLang=="1"){
		$endpoint = "/index.php/".$lang."/component/joooid/";
	}
	if($config->get('sef_rewrite')=="1"){
		$endpoint =  str_replace("/index.php","",$endpoint);
	}
}


/*

//echo "--->".$lang->toString()."--";
echo "<pre>";
var_dump($endpoint);
echo "</pre><br/>";
var_dump($config->get('sef'));
var_dump($config->get('sef_rewrite'));
die;
*/
?>

	<dl>
		<dt><strong>The following url is suggested: it should be changed if you have custom url rewrite rules, or third part SEF plugins according to third part documentation</strong></dt>
		<dt>You should use the following endpoint during the creation of a Joooid account on your Mobile Device, in the Component/Plugin Url field.</dt>
		<dt style="color:green"><strong><?php echo $endpoint;?></strong></dt>
	</dl>

</fieldset>

<fieldset>
	<legend><?php echo JText::_('COM_JOOOID_INSTALLED_JOOOID_PLUGINS');?></legend>
	<?php $plugins = array();
		$plugins['xmlrpc'] = false;	
		$plugins['joooidcontent'] = false;	
	
	?>
	<?php foreach($this->joooid_plugins as $row):?>
<?php		if ($row->name =="joooid"){ 
			$plugins['xmlrpc'] = true;
		} 
?>



<?php		

		if ($row->name =="joooidcontent"){ 
			$plugins['joooidcontent'] = true;
		} 
?>

	<?php endforeach;?>

	<dl>
		<dt style="color:<?php echo ($plugins['xmlrpc'])?"green": "red"?>">Joooid XMLRPC: <strong>
			<?php echo ($plugins['xmlrpc'])?"Enabled": JText::_('COM_JOOOID_NOT_ENABLED_JOOOID_PLUGIN')?></strong>
		</dt>
		<dt style="color:<?php echo ($plugins['joooidcontent'])?"green": "red"?>">Joooid Content Plugin: <strong><?php echo ($plugins['joooidcontent'])?"Enabled": JText::_('COM_JOOOID_NOT_ENABLED_JOOOID_PLUGIN')?></strong></dt>
	</dl>

</fieldset>


	<form action = "<?php echo "$_SERVER[REQUEST_URI]" ?>" method="POST">
	<?php


		//$filename = JUri::base() . "components/com_joooid/views/configuration/tmpl/tmp.css";
		$filename = getcwd()."/components/com_joooid/views/configuration/tmpl/joooidcontent.css";
		if(isset($_POST['cssform'])){
			$i = file_put_contents($filename,$_POST['cssform']);
			if(!$i){
				?>
					<dl>
						<dt style="color:red">
							<?php print_r(error_get_last()); ?>
						</dt>
					</dl>
				<?php
							
			}
			else{
				?>
					<dl>
						<dt style="color:green">
							Css Saved
						</dt>
					</dl>
				<?php
			
			}
		}


		$handle = fopen($filename, "r");
		if(!$handle){
			?>
				<dl>
					<dt style="color:red">
						<?php echo "Error opening file:". $filename; ?>
					</dt>
				</dl>
			<?php
		}
		$cssContent = file_get_contents($filename);
		
	?>
<script type="text/javascript">
function closeWindow(ref){
if(typeof window.joooidChildrenWindow !== 'undefined')
window.joooidChildrenWindow.close();	
}


function anteprima(linkTo){

	if(linkTo=="<?php echo JURI::root();  ?>"){
		alert("<?php echo JText::_('COM_JOOOID_EDIT_CSS_WARNING_PERFORMANCE'); ?>");
	}
	var css = '';
	css = document.getElementById('joooid_css_textarea').value;

	window.closeWindow();
	var w = null;
	var w = window.open(linkTo);
	window.joooidDebugWindowName = location.href;
	w.joooidPreviewParent = window;
	window.joooidChildrenWindow = null;

	window.joooidChildrenWindow = w;

	// attaching function on new window.
	// this function will change css with preview rules
	// and it will will call this same function on all links, so whatever link will be clicked modifications will be preserved
	w.document.getElementsByTagName('body')[0].onload = function(){

		var elements = w.document.getElementsByTagName('a');
		for(var i = 0, len = elements.length; i < len; i++) {
			elements[i].onclick = function () {
				//alert('clicco sul link'+this.href);
				var tmp = this;
				setTimeout(function(){window['anteprima'](tmp.href);},300);
				
				return false;

			}
		}
		
		var joooidcss = null;
		var stylesheets = w.document.getElementsByTagName('link');

		for(var i = 0; i<stylesheets.length;i++){
			var ref = stylesheets[i].href;
			if(typeof ref !== 'undefined' && ref.contains('joooidcontent.css')){ 
				joooidcss = stylesheets[i];
				joooidcss.parentNode.removeChild(joooidcss);
			}
		}



		link=w.document.createElement('style');
		link.innerHTML =css;
		//link.href  ="";
		link.type  ="text/css";
		w.document.getElementsByTagName('head')[0].appendChild(link );

	}
}
</script>
<fieldset>
	<legend><?php echo JText::_('COM_JOOOID_EDIT_CSS_LABEL') ?></legend>
	<div style=" width:100%;height:250px;">
	<textarea id="joooid_css_textarea" style ="width:80%;height:100%;" name = "cssform"><?php echo $cssContent ?></textarea>
	</div>
	<div style=";float:left;">
	<button type="submit" value="Save" ><?php echo JText::_('COM_JOOOID_EDIT_CSS_SAVE') ?></button>
	<button type="button" value="Save" onClick="anteprima('<?php echo JURI::root(); ?>');">Anteprima</button>
	</div>
	</form>

</fieldset>
