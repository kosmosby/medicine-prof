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
?>
<div id="judl-container" class="jubootstrap component judl-container view-moddocuments">
<h2 class="judl-view-title"><?php echo JText::_('COM_JUDOWNLOAD_MANAGE_DOCUMENTS'); ?></h2>

<?php if (!is_array($this->items) || !count($this->items))
{
	?>
	<div class="alert alert-block">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<?php echo JText::_('COM_JUDOWNLOAD_NO_DOCUMENT'); ?>
	</div>
<?php
} ?>

<form name="judl-documents-form" id="judl-documents-form" class="judl-form" method="post"
      action="<?php echo JRoute::_('index.php?option=com_judownload&view=moddocuments'); ?>">

<?php
	echo $this->loadTemplate('header');
?>

<table class="table table-striped table-bordered">
<thead>
<tr>
	<th style="width:5%" class="center">
		<input type="checkbox" name="judl-cbAll" id="judl-cbAll"
		       title="<?php echo JText::_('COM_JUDOWNLOAD_CHECK_ALL'); ?>" value=""/>
	</th>
	<th>
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_TITLE'); ?>
	</th>
	<th style="width:15%" class="center">
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_CATEGORY'); ?>
	</th>
	<th style="width:15%" class="center">
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_CREATED_BY'); ?>
	</th>
	<th style="width:15%" class="center">
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_CREATED'); ?>
	</th>
	<th style="width:5%" class="center">
		<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOAD'); ?>
	</th>
	<th style="width:5%" class="center">
		<?php echo JText::_('COM_JUDOWNLOAD_FIELD_ID'); ?>
	</th>
</tr>
</thead>

<tbody>
<?php
if (is_array($this->items) && count($this->items))
{
	foreach ($this->items AS $i => $item)
	{
		$this->item = $item;
		$fields = JUDownloadFrontHelperField::getFields($item, null);
		?>
		<tr>
		<td class="center">
			<input type="checkbox" checked="checked" class="judl-cb" name="cid[]" value="<?php echo $item->id; ?>"
			       id="judl-cb-<?php echo $i; ?>"/>
		</td>
		<td class="title">
			<div class="title-wrapper">
				<a href="<?php echo JRoute::_(JUDownloadHelperRoute::getDocumentRoute($item->id)); ?>"
				   title="<?php echo $item->title; ?>">
					<?php echo $item->title; ?>
				</a>
				<?php
				if ($item->label_unpublished)
				{
					?>
					<span class="label label-unpublished"><?php echo JText::_('COM_JUDOWNLOAD_UNPUBLISHED'); ?></span>
				<?php
				}

				if ($item->label_pending)
				{
					?>
					<span class="label label-pending"><?php echo JText::_('COM_JUDOWNLOAD_PENDING'); ?></span>
				<?php
				}

				if ($item->label_expired)
				{
					?>
					<span class="label label-expired"><?php echo JText::_('COM_JUDOWNLOAD_EXPIRED'); ?></span>
				<?php
				}

				if ($item->label_new)
				{
					?>
					<span class="label label-new"><?php echo JText::_('COM_JUDOWNLOAD_NEW'); ?></span>
				<?php
				}

				if ($item->label_updated)
				{
					?>
					<span class="label label-updated"><?php echo JText::_('COM_JUDOWNLOAD_UPDATED'); ?></span>
				<?php
				}

				if ($item->label_hot)
				{
					?>
					<span class="label label-hot"><?php echo JText::_('COM_JUDOWNLOAD_HOT'); ?></span>
				<?php
				}

				if ($item->label_featured)
				{
					?>
					<span class="label label-featured"><?php echo JText::_('COM_JUDOWNLOAD_FEATURED'); ?></span>
				<?php
				}

				echo $this->loadTemplate('private_actions');
				?>
			</div>
		</td>
		<td class="center">
			<?php
			$categoriesField = $fields['cat_id'];
			echo $categoriesField->getOutput(array("view" => "list", "template" => $this->template));
			?>
		</td>
		<td class="center">
			<?php
			$createdByField = $fields['created_by'];
			echo $createdByField->getOutput(array("view" => "list", "template" => $this->template));
			?>
		</td>
		<td class="center">
			<?php
			$createdField = $fields['created'];
			echo $createdField->getOutput(array("view" => "list", "template" => $this->template));
			?>
		</td>
		<td class="center">
			<?php
				echo $this->loadTemplate('download_button');
			?>
		</td>
		<td class="center">
			<?php echo $item->id; ?>
		</td>
		</tr>
	<?php
	}
} ?>
</tbody>
</table>

<?php
	echo $this->loadTemplate('footer');
?>

<div>
	<input type="hidden" name="task" value=""/>
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>

<?php
	// Load modal password form outside of document list form
	echo $this->loadTemplate('password_form');
?>
</div>