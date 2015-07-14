<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
    $doc= JFactory::getDocument();
    $doc->addScript(JURI::root().'administrator/components/'.APP_EXTENSION.'/thefactory/mailman/js/mailman.js');
?>

<form action="index.php" method="post" name="adminForm">
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>"/>
<input type="hidden" name="task" value="mailman.mails"/>
<?php echo $this->mailtype_select;?>
<input type='checkbox' name='enabled' value='1' onclick='toggleMail();' <?php echo ($this->current_mail->enabled)?"checked":"";?> />
<?php echo ($this->current_mail->enabled)?JText::_("FACTORY_ENABLED"):("<div><h2>".JText::_("FACTORY_CURRENT_MAILTYPE_NOT_ENABLED")."</h2></div>");?>
<table width="100%">
	<tr>
		<td width="400" align="left" valign="top">
			<table>
				<tr >
					<td align="center"><b><?php echo JText::_("FACTORY_SHORTCUTS_LEGEND");?></b></td>
				</tr>
				<tr><td height="10%"></td></tr>
				<tr>
                    <td valign="top">
                        <?php echo $this->display('legend');?>
					</td>
				</tr>
			</table>
		</td>
		<td align="left" valign="top">
            <table >
            <tr>
                <th class="title">
                    <?php echo $this->title;?>&nbsp;
                    <?php echo JHTML::_('tooltip',$this->help);?>
                </th>
            </tr>
            <tr>
                <td>
                    <input name="subject" value="<?php echo $this->current_mail->subject;?>"  size="80" <?php echo $this->current_mail->enabled?"":"disabled"; ?>>
                </td>
            </tr>
            <tr id="mailbody-tr" style="display: <?php echo ($this->current_mail->enabled)?"block":"none";?>;">
                <td valign="top">
                	<?php echo $this->editor->display( 'mailbody',
                        $this->current_mail->content,
                        '100%', '300', '200', '200' ) ;?>
               </td>
            </tr>
            </table>
		</td>
	</tr>
</table>

</form>
