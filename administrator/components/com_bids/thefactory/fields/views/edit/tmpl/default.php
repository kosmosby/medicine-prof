<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
jimport('joomla.html.pane');
JHTML::_( 'behavior.mootools' );
JTheFactoryFieldsHelper::addJSLanguageStrings();
$pane	= JPane::getInstance('sliders', array('allowAllClose' => true));
$img_required="<img src='".JURI::root()."administrator/templates/bluestork/images/admin/publish_y.png' width='15p' title='".JText::_('FACTORY_REQUIRED')."' border='0' />";    
$this->assign('img_required',$img_required);
$doc= JFactory::getDocument();
$script="window.addEvent('domready', function () {
    CustomFields.pages=Array();
    ";
foreach($this->lists->get('field_pages_categories') as $page=>$value)
{
    $script.="CustomFields.pages['$page']={category:".(int)$value."};\n";    
}
$script.="});";
if ($script) $doc->addScriptDeclaration($script);


$this->category_selection_style="style='display:none;'";

if (isset($this->field->page))
    if (!$this->lists->field_pages_categories[$this->field->page])
        $this->category_selection_style="";
?>
<form action="index.php" method="post" name="adminForm">
<table class="adminform" border="0">
<tr>
<td colspan="2" >
    <?php $this->display('mainsettings'); ?>
</td>
</tr>
<tr>
<td width="50%" valign="top" style="border-right: 1px solid black;">
	<?php
        echo $pane->startPane("detail-page");

		echo $pane->startPanel( JText::_( 'FACTORY_FIELD_BEHAVIOUR' ), "detail-page" );
        $this->display('behaviour');
        echo $pane->endPanel();

        echo $pane->startPanel( JText::_( 'FACTORY_HTML_CSS_PARAMETERS' ), "detail-page" );
        $this->display('html_css');
        echo $pane->endPanel();

        echo $pane->startPanel( JText::_( 'FACTORY_CATEGORY_ASSIGNMENT' ), "detail-page" );
        $this->display('categories');
        echo $pane->endPanel();

        echo $pane->endPane();
	?>
	
	<table cellspacing="0" cellpadding="4" border="0" align="center">
	<tr align="center">
		<td>
            <span id="field_info_compulsory">
			<?php 
                if ($this->field->compulsory) 
                    echo JText::_( 'FACTORY_COMPULSORY_FIELD' );
                else 
                    echo JText::_( 'FACTORY_OPTIONAL_FIELD' ); 
            ?> 
            </span>
		</td>
	</tr>
	</table>	
</td>
<td id="parameter_tab" valign="top" >
    <?php $this->display('parameters'); ?>
</td>
</table>

<input type="hidden" name="id" value="<?php echo $this->field->id; ?>" />
<input type="hidden" id="id_opt" name="id_opt" value="" />
<input type="hidden" id="fields_aply" name="fields_aply" value="0" />
<input type="hidden" id="fields_act" name="act" value="save" />
<input type="hidden" name="option" value="<?php echo APP_EXTENSION; ?>" />
<input type="hidden" name="task" value="field_administrator" />
</form>
