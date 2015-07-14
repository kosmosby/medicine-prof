<?php
/**
 * ------------------------------------------------------------------------
 * JUDownload for Joomla 2.5, 3.x
 * ------------------------------------------------------------------------
 *
 * @copyright      Copyright (C) 2010-2015 JoomUltra Co., Ltd. All Rights Reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @author         JoomUltra Co., Ltd
 * @website        http://www.joomultra.com
 * @----------------------------------------------------------------------@
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
$item_class = "";
if ($this->item->label_new)
{
	$item_class .= " new";
}

if ($this->item->label_updated)
{
	$item_class .= " updated";
}

if ($this->item->label_hot)
{
	$item_class .= " hot";
}

if ($this->item->label_featured)
{
	$item_class .= " featured";
}
?>
<div id="judl-container"
	class="jubootstrap component judl-container view-document layout-default <?php echo $item_class; ?> category-<?php echo $this->item->cat_id; ?> <?php echo isset($this->tl_catid) ? 'tlcat-id-' . $this->tl_catid : ""; ?> <?php echo $this->item->class_sfx; ?> <?php echo $this->pageclass_sfx; ?>">

	<?php
		echo $this->loadTemplate('document_details');
	?>

	<?php
	if (count($this->item->files) || $this->item->external_link != '')
	{
		echo $this->loadTemplate('files');
	}
	?>

	<?php
	if (is_object($this->item->fieldGallery) && $this->item->fieldGallery->canView())
	{
		echo $this->loadTemplate('gallery');
	} ?>

	<?php
	if (count($this->item->changelogs))
	{
		echo $this->loadTemplate('changelogs');
	}
	?>

	<?php
	if (count($this->item->related_documents))
	{
		echo $this->loadTemplate('related_documents');
	}
	?>

	<?php if ($this->item->next_item || $this->item->prev_item)
	{
		echo $this->loadTemplate('prev_next');
	}
	?>

	<?php
	if ($this->item->params->get('show_comment'))
	{
		echo $this->item->event->beforeDisplayJUDLComment;

		$commentSystem = $this->params->get('comment_system', 'default');
		switch ($commentSystem)
		{
			case 'default':
				echo $this->loadTemplate('comment_default');
				break;
			case 'disqus':
				echo $this->loadTemplate('comment_disqus');
				break;
			case 'facebook':
				echo $this->loadTemplate('comment_facebook');
				break;
		}

		echo $this->item->event->afterDisplayJUDLComment;
	}
	?>
</div>