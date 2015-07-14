<?
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('_JEXEC') or die; ?>

<rss version="2.0"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:sy="http://purl.org/rss/1.0/modules/syndication/">

   <channel>
       <title><?=escape($params->get('page_heading', translate('Documents')))?> - <?=escape($sitename)?></title>
       <description><![CDATA[<?=$description?>]]></description>
       <link><?=$channel_link?></link>
       <? if (isset($image)): ?>
       <image>
           <url><?=$image?></url>
           <title><?=escape($params->get('page_heading', translate('Documents')))?> - <?=escape($sitename)?></title>
           <link><?=$channel_link?></link>
       </image>
       <? endif; ?>
       <lastBuildDate><?= count($documents) ? helper('date.format', array(
                            'date' => $documents->top()->created_on,
                            'gmt_offset' => 0,
                            'format' => 'r'
                         )) : ''
       ?></lastBuildDate>
       <atom:link href="<?=$feed_link?>" rel="self" type="application/rss+xml"/>
       <language><?=$language?></language>
       <sy:updatePeriod><?= $update_period ?></sy:updatePeriod>
       <sy:updateFrequency><?= $update_frequency ?></sy:updateFrequency>

       <?foreach($documents as $document):?>
       <item>
           <title><?= escape($document->title); ?></title>
           <link><?= $document->document_link; ?></link>
           <guid isPermaLink="true"><?= $document->document_link; ?></guid>
           <description><![CDATA[<?=$document->description_summary?>]]></description>
           <author><?= escape($document->getAuthor()->getEmail().' ('.$document->getAuthor()->getName().')') ?></author>
           <category><?= $document->category_title; ?></category>
           <pubDate><?=helper('date.format', array(
                   'date' => $document->created_on,
                   'gmt_offset' => 0,
                   'format' => 'r'
               ))?></pubDate>
       </item>
       <?endforeach?>
   </channel>
</rss>