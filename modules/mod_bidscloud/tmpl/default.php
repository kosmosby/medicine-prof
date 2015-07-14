<?php // no direct access
defined('_JEXEC') or die('Restricted access');
?>

<div class="bidsCloudModule">
<?php

if (count($ordered_tag_list)>0)
	foreach($ordered_tag_list as $tagname => $v)
	{
        if($v) {
            $vrate='';
            $size = $min_font+($rank_font * ( ($v['recurrence'] - $minimum_count) / $rank_freq) );
            $href=JRoute::_('index.php?option=com_bids&amp;task=tags&tagid='.$v['id'].':'.JFilterOutput::stringURLUnicodeSlug($tagname));
            echo JHtml::link($href,$tagname,'style="font-size: '.$size.'px;" class="fontbidsCloudModule" title="'.$tagname.'"').PHP_EOL;
        }
	}  
?>
</div>