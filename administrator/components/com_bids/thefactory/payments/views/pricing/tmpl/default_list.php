<?php defined('_JEXEC') or die('Restricted access'); ?>
<form name="adminForm" action="index.php" method="post">
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="themes.setdefault" />
<div id="cpanel">
    <div style="width: 600px;float:left;">
        <div class='width-100 fltlft'>
            <fieldset class="adminform">
            <legend><?php echo JText::_( 'FACTORY_PRICING_ITEMS__PAYMENT_ITEMS' ); ?></legend>
            <?php foreach($this->pricing as $item): ?>
            <div style="float: left;">
                <table width="100%">
                <tr>
                    <td>
                        <div class="icon">
                        <a href="index.php?option=<?php echo APP_EXTENSION;?>&task=pricing.config&item=<?php echo $item->itemname;?>">
                        <?php echo JHtml::_("image.administrator","icon.png","components/".APP_EXTENSION."/pricing/{$item->itemname}/");?>
                        </a>
                        <a href="index.php?option=<?php echo APP_EXTENSION;?>&task=pricing.toggle&pricingitem=<?php echo $item->itemname;?>" class="pricing-enable">
                        <?php
                            $img=($item->enabled)?"admin/tick.png":"admin/icon-16-deny.png";
                            $toggle=($item->enabled)?"Click to disable":"Click to enable";
                            echo JHtml::_("image.administrator",$img,'/images/',null,'/images/',null,'title="'.JText::_($toggle).'"');
                        ?>
                        </a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><?php echo JText::_($item->name);?></td>
                </tr>
                <tr>
                    <td>
                    </td>
                </tr>
                </table>
            </div>
            <?php endforeach;?>
            </fieldset>
        </div>
    </div>
</div>
</form>
