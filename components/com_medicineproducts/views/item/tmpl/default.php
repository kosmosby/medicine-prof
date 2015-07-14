<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

$urlItemid = JRequest::getInt('Itemid');
JHtml::_('behavior.caption');
?>
<div>
<?php

?>
    <div class="medizdopis">
        <label>
            <?php echo JText::_("MEDIZD_CODE");?> :
        </label>
        <?php echo $this->item->code;?>
    </div>
    <div class="medizdopis">
        <label>
            <?php echo JText::_("MEDIZD_NAME");?> :
        </label>
        <?php echo $this->item->name;?>
    </div>
    
    <div class="medizdopis">
        <label>
            <?php echo JText::_("MEDIZD_PROIZVODITEL");?> :
        </label>
        <?php echo $this->item->proizvoditel;?>
    </div>
    <div class="medizdopis">
        <label>
            <?php echo JText::_("MEDIZD_CATEGORY");?> :
        </label>
        <?php echo $this->item->catname;?>
    </div>
    <div class="medizdopis">
        <label>
            <?php echo JText::_("MEDIZD_COUNTRY");?> :
        </label>
        <?php echo $this->item->country;?>
    </div>
    <div class="medizdopis">
        <label>
            <?php echo JText::_("JAUTHOR");?> :
        </label>
        <a href="<?php echo JRoute::_('index.php?option=com_comprofiler&task=userProfile&user='. $this->item->user_id)?>"><?php echo $this->item->username;?></a>
    </div>
    <div class="medizdopis">
        <label>
            <?php echo JText::_("MEDIZD_DESCRIPTION");?> :
        </label>
        <?php echo $this->item->description;?>
    </div>
</div>

