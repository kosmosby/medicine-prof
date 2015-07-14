<?php
    defined('_JEXEC') or die('Restricted access');

    $page = $this->pagination;
    $rows = $this->messages;
?>

<form action="index.php" method="post" name="adminForm">

    <input type="hidden" name="option" value="<?php echo APP_EXTENSION; ?>" />
    <input type="hidden" name="controller" value="messages" />
    <input type="hidden" name="task" value="listing" />
    <input type="hidden" name="boxchecked" value="0" />

    <table class="adminlist" cellspacing="1" style="text-align: left;">
        <thead>
            <tr>
                <th><input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></th>
                <th width="10%" style="text-align: left;"><?php echo "Date"; ?></th>
                <th style="text-align: left;"><?php echo "Auction"; ?></th>
                <th width="10%" style="text-align: left;"><?php echo "From"; ?></th>
                <th width="10%" style="text-align: left;"><?php echo "To"; ?></th>
                <th style="text-align: left;"><?php echo "Message"; ?></th>
                <th width="3%" style="text-align: left;"><?php echo "Toogle Status"; ?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="10">
                    <?php echo $page->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
        <tbody>
            <?php
            $k = 0;

            for ($i = 0, $n = count($rows); $i < $n; $i++) {
                $row = $rows[$i];

                ?>
                <tr class="<?php echo "row$k"; ?>">
                    <td align="center"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
                    <td align="left"><?php echo JHtml::date($row->modified,'Y-m-d H:i:s'); ?></td>
                    <td align="left"><?php echo $row->title; ?></td>
                    <td align="left"><?php echo $row->username1; ?></td>
                    <td align="left"><?php echo $row->username2; ?></td>
                    <td align="left"><?php echo $row->message; ?></td>
                    <td align="left">
                        <?php echo JHtml::_('grid.published',$row->published,$i,'tick.png','publish_x.png','messages.'); ?>
                    </td>
                </tr>
                <?php
                $k = 1 - $k;
            }
            ?>
        </tbody>
    </table>
    <?php echo JHTML::_( 'form.token' );  ?>
</form>
