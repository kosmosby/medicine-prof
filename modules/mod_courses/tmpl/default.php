<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_custom
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

?>
<script>
    jQuery(document).ready(function() {
        jQuery(function() {
            jQuery("#tabs").tabs({ fx: [{opacity:'toggle', duration:'fast'},   // hide option
                {opacity:'toggle', duration:'fast'}] }).tabs("rotate", 7000, true);
        });
    });
</script>

<div id="tabs">
    <ul>
        <?php $i= 1;
            foreach($list as $k=>$v) {?>
            <li><a href="#tabs-<?php echo $i;?>"><?php echo $k;?></a></li>
        <?php $i++;}?>
    </ul>

    <?php $i=1; foreach($list as $k=>$v) {

    ?>

            <div id="tabs-<?php echo $i;?>">

                <article class="wk-content clearfix">

                    <?php $j=1; for($x=0;$x<count($v);$x++) {?>

                        <div style="<?php echo $j==1?'margin-right: 75px;':'';?>" class="tabs_egitimler">
                            <div class="preload">
                               <a class="preloader">
                                <img border="0" style="display: block; visibility: visible; opacity: 1;" src="<?php echo JURI::base();?>images/egitimler/<?php echo $v[$x]->image;?>" >
                               </a>
                            </div>
                            <h2>
                                <a href="<?php echo JRoute::_($v[$x]->link."&Itemid=".$v[$x]->Itemid);?>"><?php echo $v[$x]->title;?></a>
                            </h2>
                            <p><?php echo $v[$x]->description;?></p>
                        </div>

                    <?php if($j==1) {$j=0;} else {$j=1;} }?>

                </article>

            </div>

    <?php $i++; } ?>

</div>