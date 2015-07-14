<?php /**
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
       <title><?php echo $this->escape($params->get('page_heading', $this->translate('Documents')))?> - <?php echo $this->escape($sitename)?></title>
       <description><![CDATA[<?php echo $description?>]]></description>
       <link><?php echo $channel_link?></link>
       <?php if (isset($image)): ?>
       <image>
           <url><?php echo $image?></url>
           <title><?php echo $this->escape($params->get('page_heading', $this->translate('Documents')))?> - <?php echo $this->escape($sitename)?></title>
           <link><?php echo $channel_link?></link>
       </image>
       <?php endif; ?>
       <lastBuildDate><?php echo count($documents) ? $this->helper('date.format', array(
                            'date' => $documents->top()->created_on,
                            'gmt_offset' => 0,
                            'format' => 'r'
                         )) : ''
       ?></lastBuildDate>
       <atom:link href="<?php echo $feed_link?>" rel="self" type="application/rss+xml"/>
       <language><?php echo $language?></language>
       <sy:updatePeriod><?php echo $update_period ?></sy:updatePeriod>
       <sy:updateFrequency><?php echo $update_frequency ?></sy:updateFrequency>

       <?php foreach($documents as $document):?>
       <item>
           <title><?php echo $this->escape($document->title); ?></title>
           <link><?php echo $document->document_link; ?></link>
           <guid isPermaLink="true"><?php echo $document->document_link; ?></guid>
           <description><![CDATA[<?php echo $document->description_summary?>]]></description>
           <author><?php echo $this->escape($document->getAuthor()->getEmail().' ('.$document->getAuthor()->getName().')') ?></author>
           <category><?php echo $document->category_title; ?></category>
           <pubDate><?php echo $this->helper('date.format', array(
                   'date' => $document->created_on,
                   'gmt_offset' => 0,
                   'format' => 'r'
               ))?></pubDate>
       </item>
       <?php endforeach?>
   </channel>
</rss>