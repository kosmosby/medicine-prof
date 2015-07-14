<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
JHtml::_( 'behavior.mootools' );
JHtml::script(JURI::root().'administrator/components/'.APP_EXTENSION.'/pricing/listing/js/listing.js');
?>
<form name="adminForm" action="index.php" method="post">
<fieldset>
    <legend><?php echo JText::_("COM_BIDS_GENERAL_SETTINGS"); ?></legend>
    <table width="100%" class="paramlist admintable">
    <tbody>
    <tr>
        <td width="220" class="paramlist key"><?php echo JText::_("COM_BIDS_CURRENCY_USED"); ?>: </td>
        <td><?php echo JHtml::_('currency.selectlist','currency','',$this->currency);?></td>
    </tr>
    <tr>
        <td width="220" class="paramlist key"><?php echo JText::_("COM_BIDS_DEFAULT_LISTING_PRICE"); ?>: </td>
        <td><input value="<?php echo $this->default_price;?>" class="inputbox" name="default_price" size="5"></td>
    </tr>
    </tbody>
    </table>
</fieldset>
<fieldset>
    <legend><?php echo JText::_("COM_BIDS_SPECIAL_PRICES"); ?></legend>
    <table width="100%" class="paramlist admintable">
    <tbody>
    <tr>
        <td width="220" class="paramlist key"><?php echo JText::_("COM_BIDS_PRICE_FOR_POWERSELLERS"); ?>: </td>
        <td><input value="<?php echo $this->price_powerseller;?>" class="inputbox" name="price_powerseller" size="5"></td>
    </tr>
    <tr>
        <td width="220" class="paramlist key"><?php echo JText::_("COM_BIDS_PRICE_FOR_VERIFIED_USERS"); ?>: </td>
        <td><input value="<?php echo $this->price_verified;?>" class="inputbox" name="price_verified" size="5"></td>
    </tr>
    </tbody>
    </table>
</fieldset>
<fieldset>
    <legend><input type="checkbox" name="category_pricing_enabled" value="1" <?php echo $this->category_pricing_enabled?'checked':'';?>> <?php echo JText::_("COM_BIDS_ENABLE_CATEGORIES_PRICING"); ?></legend>
    <table width="100%" class="admintable <?php echo $this->category_pricing_enabled?'disabled_table':'';?>">
    <tbody>
    <tr>
        <td colspan="2"><span class="legend"><?php echo JText::_("COM_BIDS_LEAVE_EMPTY_FOR_DEFAULT_PRICE"); ?></span></td>
    </tr>
    <tr>
        <th width="300"><?php echo JText::_("COM_BIDS_CATEGORY_NAME"); ?></th>
        <th><?php echo JText::_("COM_BIDS_LISTING_PRICE_FOR_CATEGORY"); ?></th>
    </tr>
    <?php
    $k=0;
    foreach($this->category_tree as $catobject):
    ?>
    <tr class="row<?php echo $k=1-$k;?>">
        <td><?php
            if ($catobject->depth)
            {
                //$lpadding=$catobject['depth']*10;
                $lpadding=0;
                for ($i=0;$i<$catobject->depth;$i++)
                    echo JHtml::_('image','j_arrow.png',"Subcategory","Subcategory",true);
                echo "<span style='padding-left: {$lpadding}px;font-size: 90%;font-weight: normal;'>",$catobject->title,"</span>";
            }else
                echo "<span style='font-size: 110%;font-weight: bolder;'>",$catobject->title,"</span>";
        ?></td>
        <td><input name="category_pricing[<?php echo $catobject->id; ?>]" type="text" size="5"
            class="category_pricing"
            <?php echo $this->category_pricing_enabled?'':'disabled';?>
            value="<?php echo isset($this->category_pricing[$catobject->id])?$this->category_pricing[$catobject->id]['price']:""; ?>"></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
</fieldset>

<input type="hidden" name="task" value="">
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>">
<input type="hidden" name="item" value="<?php echo $this->itemname;?>">

</form>
