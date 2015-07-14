<?
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('_JEXEC') or die; ?>
<?='<?xml version="1.0" encoding="utf-8" ?>' ?>

<rss version="2.0"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:sy="http://purl.org/rss/1.0/modules/syndication/">

   <channel>
      <title><?=@escape($sitename)?> - <?=@text('Activities')?></title>
      <description></description>
      <link><?=$base_url?></link>
      <lastBuildDate><?= count($activities) ? @date(array(
                           'date' => $activities->top()->created_on,
                           'format' => '%a, %d %b %Y %H:%M:%S GMT'
                        )) : ''
      ?></lastBuildDate>
      <generator>Joomlatools LOGman</generator>
      <language><?=$language?></language>
      
      <dc:language><?= JFactory::getLanguage()->getTag() ?></dc:language>
      <dc:rights>Copyright <?= @helper('date.format', array('format' => '%Y')) ?></dc:rights>
      <dc:date><?= count($activities) ? @date(array(
                       'date' => $activities->top()->created_on,
                       'format' => '%a, %d %b %Y %H:%M:%S GMT'
                    )) : ''
      ?></dc:date>  
      
      <sy:updatePeriod><?= $update_period ?></sy:updatePeriod>
      <sy:updateFrequency><?= $update_frequency ?></sy:updateFrequency>
      
      <atom:link href="<?=$base_url?>" rel="self" type="application/rss+xml"/>
        
      <?foreach($activities as $activity):?>
      <item>
         <title><?=@escape(@helper('activity.message', array(
             'row' => $activity, 
             'formatted' => false
         ))) ?>
         </title>
         <dc:creator><?= @escape($activity->created_by_name)?></dc:creator>
         <description><![CDATA[<?=@helper('activity.message', array(
             'row' => $activity, 
             'escape_html' => true,
             'absolute_links' => true
         )) ?>
         ]]></description>
         <pubDate><?=@date(array(
                     'date' => $activity->created_on,
                     'format' => '%a, %d %b %Y %H:%M:%S GMT'
         ))?></pubDate>
         <dc:date><?=@date(array(
                     'date' => $activity->created_on,
                     'format' => '%a, %d %b %Y %H:%M:%S GMT'
         ))?></dc:date>
      </item>
      <?endforeach?>
   </channel>
</rss>