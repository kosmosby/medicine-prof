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

$small_image_dir = JUDownloadFrontHelper::getDirectory("document_small_image_directory", "media/com_judownload/images/gallery/small/", true) . $this->doc_id . "/";
$params          = JUDownloadHelper::getParams();
$requiredImage   = $this->isRequired();
?>

<!-- New gallery image template -->
<script id="gallery-template" type="text/x-handlebars-template">
	<li>
		<input type="file" name="field_<?php echo $this->id; ?>[]" class="validate-images" {{multiple}}/>
		<a href="#" class="btn btn-mini btn-xs btn-danger remove_image" onclick="return false;"><i
				class="icon-minus"></i> <?php echo JText::_('COM_JUDOWNLOAD_REMOVE'); ?></a>
	</li>
</script>

<!-- Gallery image form template -->
<script id="imageform-template" type="text/x-handlebars-template">
	<div id="img-element-data-form" class="img-element-data-form">
		<div class="form-horizontal" style="margin: 10px">
			<div class="control-group">
				<div class="control-label">
					<label for="imgtitle"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_TITLE'); ?></label>
				</div>
				<div class="controls">
					<input type="text" name="imgtitle" id="imgtitle" value="{{image.title}}" size="50"/>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label for="imgdescription"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_DESCRIPTION'); ?></label>
				</div>
				<div class="controls">
					<textarea rows="8" cols="80" name="imgdescription"
					          id="imgdescription">{{image.description}}</textarea>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label for="imgdescription"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_PUBLISHED'); ?></label>
				</div>
				<div class="controls">
					<input name="imgpublished" id="imgpublished" type="checkbox" {{checked}} value="1"/>
				</div>
			</div>
			<div class="control-group" style="margin-bottom: 0">
				<div class="controls">
					<input class="btn" onclick="updateImageData(); return false;" type="button"
					       value="<?php echo JText::_('COM_JUDOWNLOAD_UPDATE'); ?>"/>
					<input class="btn" onclick="imageFormClose(); return false;" type="button"
					       value="<?php echo JText::_('COM_JUDOWNLOAD_CANCEL'); ?>"/>
				</div>
			</div>
		</div>
	</div>
</script>

<div id="image-gallery" class="jugallery-holder">
	<ul id="jugallery" class="jugallery">
		<?php
		if ($images)
		{
			foreach ($images AS $key => $image)
			{
				$published = $image['published'] ? true : false;
				$remove    = isset($image['remove']) && $image['remove'] ? 1 : 0;
				$class     = $published ? "" : " unpublished";
				$class     = $remove ? $class . " unremove" : $class;
				?>
				<li data-itemid="<?php echo $image['id'] ?>">
					<div class="img-element<?php echo $class; ?>">
						<img class="img-item" alt="Image"
						     src="<?php echo JUri::root() . $small_image_dir . $image['file_name'] ?>"/>
						<span class="view-image"
						      title="<?php echo JText::_('COM_JUDOWNLOAD_VIEW_IMAGE'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_VIEW_IMAGE'); ?></span>
						<?php if ($published)
						{
							?>
							<span class="published-image"
							      title="<?php echo JText::_('COM_JUDOWNLOAD_TOGGLE_TO_UNPUBLISH'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_PUBLISHED'); ?></span>
						<?php
						}
						else
						{
							?>
							<span class="published-image"
							      title="<?php echo JText::_('COM_JUDOWNLOAD_TOGGLE_TO_PUBLISH'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_UNPUBLISHED'); ?></span>
						<?php
						} ?>
						<span class="remove-image"
						      title="<?php echo JText::_('COM_JUDOWNLOAD_REMOVE_IMAGE'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_REMOVED'); ?></span>
						<span class="edit-image"
						      title="<?php echo JText::_('COM_JUDOWNLOAD_EDIT_IMAGE'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_EDIT_IMAGE'); ?></span>
						<input type="hidden" class="image-id-value" value="<?php echo $image['id'] ?>"
						       name="fields[<?php echo $this->id;?>][<?php echo $key; ?>][id]"/>
						<input type="hidden" class="remove-image-value" value="<?php echo $remove;?>"
						       name="fields[<?php echo $this->id;?>][<?php echo $key; ?>][remove]"/>
						<input type="hidden" class="published-image-value"
						       value="<?php echo (int) $image['published'] ?>"
						       name="fields[<?php echo $this->id;?>][<?php echo $key; ?>][published]"/>
						<input type="hidden" class="title-image-value"
						       value="<?php echo htmlspecialchars($image['title'], ENT_QUOTES) ?>"
						       name="fields[<?php echo $this->id;?>][<?php echo $key; ?>][title]"/>
						<textarea style="display: none" class="description-image-value"
						          name="fields[<?php echo $this->id;?>][<?php echo $key; ?>][description]"><?php echo htmlspecialchars($image['description'], ENT_QUOTES) ?></textarea>

						<div class="remove-image-mask"
						     style="<?php echo !$remove ? 'display: none' : 'display:block';?>"></div>
					</div>
				</li>
			<?php
			}
		}
		?>
	</ul>

	<ul id="gallery-browser" class="gallery-browser">
		<?php
		if ($requiredImage && !$images)
		{
			?>
			<li>
				<label style="display:none;"
				       for="browser-image-required"><?php echo JText::_('COM_JUDOWNLOAD_SELECT_IMAGE'); ?></label>
				<input type="file" id="browser-image-required" multiple="" class="required validate-images"
				       name="field_<?php echo $this->id; ?>[]"/>
			</li>
		<?php
		}
		?>
	</ul>

	<a href="#" class="btn btn-mini btn-primary add_images" id="add_images" onclick="return false;"
	   title="<?php echo JText::_('COM_JUDOWNLOAD_PLEASE_UPLOAD_AN_IMAGE'); ?>"><i
			class="icon-new"></i> <?php echo JText::_('COM_JUDOWNLOAD_ADD_IMAGE'); ?></a>

	<div class="squeezebox-placeholder" style="display: none;"></div>
</div>
