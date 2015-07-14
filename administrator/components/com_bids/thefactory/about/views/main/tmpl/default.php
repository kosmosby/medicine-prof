<?php
defined('_JEXEC') or die('Restricted access');

$document = JFactory::getDocument();
$js = '(function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s); js.id = id;
          js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
          fjs.parentNode.insertBefore(js, fjs);
        }(document, \'script\', \'facebook - jssdk\'));';
$document->addScriptDeclaration($js);
?>

<table class="adminlist" style="border: none;">
    <tr style="background-color: #F4F4F4; border: none;">
        <td style="padding-left: 20px; background-color: #F4F4F4; border: none;">

            <h2><a href="#" style="text-decoration: none;">Latest Release Notes</a></h2>
            <div>
                <?php if ($this->isnew_version): ?>
                <div>
                    <span style="color:red"><h2><?php echo JText::_("FACTORY_NEW_VERSION_AVAILABLE"); ?></h2></span>
                </div>
                <?php endif; ?>
                <div style="float: left; width: 260px;">
                    Your installed version: <strong><?php echo COMPONENT_VERSION; ?></strong><br/>
                    Latest version available: <strong><?php echo $this->latestversion; ?></strong><br/><br/>
                    <?php echo $this->versionhistory; ?>
                </div>
                <div style="float: left">
                    <div class="fb-like" data-href="https://www.facebook.com/theFactoryJoomla" data-send="false" data-layout="box_count" data-width="450" data-show-faces="false"></div>
                </div>
                <div style="clear: both; line-height: 1px;">&nbsp;</div>

            </div>

            <h2><a href="#" style="text-decoration: none;">Support and Updates</a></h2>
            <?php if ($this->downloadlink): ?>
                <div>
                    <?php echo $this->downloadlink; ?>
                </div>
            <?php endif; ?>

            <h2><a href="#" style="text-decoration: none;">Other Products</a></h2>
            <?php if ($this->otherproducts): ?>
            <div>
                <?php echo $this->otherproducts; ?>
            </div>
            <?php endif; ?>

            <h2><a href="#" style="text-decoration: none;">About theFactory</a></h2>
            <?php if ($this->aboutfactory): ?>
                <div>
                    <?php echo $this->aboutfactory; ?>
                </div>
            <?php endif; ?>
        </td>
    </tr>
</table>

<form action="index.php" method="post" name="adminForm">
    <input type="hidden" name="option" value="<?php echo APP_EXTENSION ?>" />
    <input type="hidden" name="task" value="about.main" />
</form>

