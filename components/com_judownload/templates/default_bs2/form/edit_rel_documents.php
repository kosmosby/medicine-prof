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
<script type="text/javascript">
	var default_icon = "<?php echo JUDownloadHelper::getDefaultDocumentIcon(); ?>";
	var document_icon_url = "<?php echo JUri::root(true) . "/". JUDownloadFrontHelper::getDirectory("document_icon_directory", "media/com_judownload/images/document/", true); ?>";
	function jSelectDocument_related() {
		var $ul = jQuery(".related-document-list").find(".related-documents");
		var $document = $ul.find("li#document-" + arguments[0]);
		if (!$document.length && arguments[0] != <?php echo (int)$this->item->id; ?>) {
			var $li = '<li id="document-' + arguments[0] + '">';
			$li += '<div class="document-inner">';
			var icon_src = arguments[2] ? document_icon_url + arguments[2] : default_icon;
			if (icon_src) {
				$li += '<img class="image" src="' + icon_src + '" title="' + arguments[1] + '" width="<?php echo $this->params->get('document_icon_width', 150)?>px" height="<?php echo $this->params->get('document_icon_height', 150); ?>px" />';
			}
			var href = 'index.php?option=com_judownload&task=document.edit&id=' + arguments[0];
			$li += '<a class="rel-document-title" target="_blank" href="' + href + '">' + arguments[1] + '</a>';
			$li += '<a class="remove-rel-document" href="#" title="<?php echo JText::_('COM_JUDOWNLOAD_REMOVE_DOCUMENT'); ?>" ><?php echo JText::_('COM_JUDOWNLOAD_REMOVE_DOCUMENT'); ?></a>';
			$li += '<input type="hidden" name="related_documents[]" value="' + arguments[0] + '" />';
			$li += '</div>';
			$li += '</li>';
			$ul.append($li);
		}
		//SqueezeBox.close();
	}

	jQuery(document).ready(function ($) {
		var $ul = jQuery(".related-document-list").find(".related-documents");
		$ul.on("click", ".remove-rel-document", function () {
			$(this).parent().parent().remove();
		});

		$(".related-document-list > ul").dragsort({ dragSelector: "li", dragEnd: saveOrder, placeHolderTemplate: "<li class='placeHolder'><div></div></li>", dragSelectorExclude: "input,a" });
		function saveOrder() {
			return;
		}
	});
</script>

<fieldset class="adminform">
	<div id="related-document-list" class="related-document-list">
		<ul class="related-documents">
			<?php
			// The user select button.
			if ($this->relatedDocuments)
			{
				foreach ($this->relatedDocuments AS $document)
				{
					?>
					<li id="document-<?php echo $document->id; ?>">
						<div class="document-inner">
							<?php if($document->icon_src){ ?>
								<img class="image" src="<?php echo $document->icon_src; ?>" title="<?php echo $document->title; ?>" style="max-width: 100px; max-height: 100px" />
							<?php } ?>
							<a class="rel-document-title" target="_blank"
							   href="index.php?option=com_judownload&task=document.edit&id=<?php echo $document->id; ?>"><?php echo $document->title; ?></a>
							<a class="remove-rel-document" href="#"
							   title="<?php echo JText::_('COM_JUDOWNLOAD_REMOVE_DOCUMENT'); ?>"><?php echo JText::_('COM_JUDOWNLOAD_REMOVE_DOCUMENT'); ?></a>
							<input type="hidden" name="related_documents[]" value="<?php echo $document->id; ?>"/>
						</div>
					</li>
				<?php
				}
			}
			$link = 'index.php?option=com_judownload&amp;view=documents&amp;layout=modal&amp;tmpl=component&amp;function=jSelectDocument_related';
			?>
		</ul>

		<div class="button2-left">
			<div class="blank">
				<a class="modal btn btn-mini" title="<?php echo JText::_('COM_JUDOWNLOAD_ADD_DOCUMENT'); ?>"
				   href="<?php echo $link . '&amp;' . JSession::getFormToken(); ?>=1"
				   rel="{handler: 'iframe', size: {x: 800, y: 450}}"><i class="fa fa-plus"></i> <?php echo JText::_('COM_JUDOWNLOAD_ADD_DOCUMENT'); ?>
				</a>
			</div>
		</div>
	</div>
</fieldset>