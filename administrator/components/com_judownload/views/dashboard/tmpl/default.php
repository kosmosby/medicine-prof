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

JHtml::_('behavior.multiselect');
JHtml::_('behavior.tooltip');

$model = $this->getModel();
$statistics = $this->get('Statistics');
$lastDownloadedDocuments = $this->get('lastDownloadedDocuments');
$lastCreatedComments = $this->get('lastCreatedComments');
$lastCreatedDocuments = $model->getDocuments("lastCreatedDocuments");
$lastUpdatedDocuments = $model->getDocuments("lastUpdatedDocuments");
$topDownloadDocuments = $model->getDocuments("topDownloadDocuments");
$popularDocuments = $model->getDocuments("popularDocuments");
$totalUnreadReports = $model->getTotalUnreadReports();
$totalMailqs = $model->getTotalMailqs();
$totalPendingDocument = JUDownloadHelper::getTotalPendingDocuments();
$totalPendingComment = JUDownloadHelper::getTotalPendingComments();
?>

<div id="iframe-help"></div>

<div class="adminform" id="adminForm">
<div class="cpanel-left">
<div id="position-icon" class="pane-sliders">
<?php if (JUDownloadHelper::checkGroupPermission(null, "listcats"))
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=listcats'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_MANAGER'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/manager.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_MANAGER'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission("category.add"))
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=category.add'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_ADD_CATEGORY'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/category-add.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_ADD_CATEGORY'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission("document.add"))
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=document.add'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_ADD_DOCUMENT'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/document-add.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_ADD_DOCUMENT'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "pendingdocuments") && JUDLPROVERSION)
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=pendingdocuments'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_PENDING_DOCUMENTS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/pending-document.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_PENDING_DOCUMENTS'); ?></span>
					<?php if ($totalPendingDocument)
					{
						?>
						<span class="update-badge"><?php echo $totalPendingDocument; ?></span>
					<?php
					}
					?>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "fields"))
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=fields'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_FIELDS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/field.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_FIELDS'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "fieldgroups") && JUDLPROVERSION)
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=fieldgroups'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_FIELD_GROUPS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/field-group.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_FIELD_GROUPS'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "criterias") && JUDownloadHelper::hasMultiRating())
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=criterias'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_CRITERIAS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/criteria.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_CRITERIAS'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "criteriagroups") && JUDownloadHelper::hasMultiRating())
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=criteriagroups'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_CRITERIA_GROUPS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/criteria-group.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_CRITERIA_GROUPS'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "comments"))
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=comments'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_COMMENTS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/comment.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_COMMENTS'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "pendingcomments"))
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=pendingcomments'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_PENDING_COMMENTS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/pending-comment.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_PENDING_COMMENTS'); ?></span>
					<?php if ($totalPendingComment)
					{
						?>
						<span class="update-badge"><?php echo $totalPendingComment; ?></span>
					<?php
					}
					?>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "emails") && JUDLPROVERSION)
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=emails'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_EMAILS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/email.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_EMAILS'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "mailqs") && JUDLPROVERSION)
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=mailqs'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_MAIL_QUEUE'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/mailqueue.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_MAIL_QUEUE'); ?></span>
					<?php if ($totalMailqs)
					{
						?>
						<span class="update-badge"><?php echo $totalMailqs; ?></span>
					<?php
					}
					?>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "licenses"))
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=licenses'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_LICENSES'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/license.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_LICENSES'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "reports") && JUDLPROVERSION)
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=reports'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_REPORTS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/report.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_REPORTS'); ?></span>
					<?php  if ($totalUnreadReports)
					{
						?>
						<span class="update-badge"><?php echo $totalUnreadReports; ?></span>
					<?php
					}
					?>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "logs") && JUDLPROVERSION)
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=logs'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_LOGS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/log.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_LOGS'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "plugins"))
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=plugins'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_PLUGINS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/plugin.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_PLUGINS'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "styles"))
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=styles'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_TEMPLATE_STYLES'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/style.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_TEMPLATE_STYLES'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "languages"))
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=languages'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_LANGUAGES'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/language.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_LANGUAGES'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "collections") && JUDLPROVERSION)
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=collections'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_COLLECTIONS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/collection.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_COLLECTIONS'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php
}?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "tags"))
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=tags'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_TAGS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/tag.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_TAGS'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "subscriptions") && JUDLPROVERSION)
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=subscriptions'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_SUBSCRIPTIONS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/subscription.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_SUBSCRIPTIONS'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "tmpfiles") && JUDLPROVERSION)
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=tmpfiles'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_TMP_FILES'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/tmpfile.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_TMP_FILES'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "users") && JUDLPROVERSION)
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=users'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_USERS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/user.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_USERS'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "moderators") && JUDLPROVERSION)
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=moderators'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_MODERATORS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/moderator.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_MODERATORS'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "csvprocess") && JUDownloadHelper::hasCSVPlugin())
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=csvprocess'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_CSV'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/csv.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_CSV'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "backendpermission") && JUDLPROVERSION)
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=backendpermission'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_BACKEND_PERMISSION'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/permission.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_BACKEND_PERMISSION'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<div class="cpanel">
	<div class="icon-wrapper">
		<div class="icon">
			<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=treestructure'); ?>">
				<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_TREE_STRUCTURE'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/manager.png" />
				<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_TREE_STRUCTURE'); ?></span>
			</a>
		</div>
	</div>
</div>

<?php
if (JUDownloadHelper::checkGroupPermission(null, "globalconfig"))
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&view=globalconfig&layout=edit'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_GLOBALCONFIG'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/global-config.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_GLOBALCONFIG'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>

<?php if (JUDownloadHelper::checkGroupPermission(null, "tools"))
{
	?>
	<div class="cpanel">
		<div class="icon-wrapper">
			<div class="icon">
				<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;view=tools'); ?>">
					<img alt="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_TOOLS'); ?>" src="<?php echo JUri::root(true); ?>/administrator/components/com_judownload/assets/img/icon/tool.png" />
					<span><?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_TOOLS'); ?></span>
				</a>
			</div>
		</div>
	</div>
<?php } ?>
</div>
</div>

<div class="cpanel-right">
<?php
echo JHtml::_('bootstrap.startAccordion', 'accordion', array('active' => 'top-5-sliders'));
echo JHtml::_('bootstrap.addSlide', 'accordion', JText::_('COM_JUDOWNLOAD_TOP_5'), 'top-5-sliders', 'top-5-sliders');
echo JHtml::_('bootstrap.startTabSet', 'top-5', array('active' => 'last-add-document'));
echo JHtml::_('bootstrap.addTab', 'top-5', 'last-add-document', JText::_('COM_JUDOWNLOAD_LAST_ADDED_DOCUMENT'));
?>
<table class="adminlist table table-striped">
	<thead>
	<tr>
		<th style="width: 30%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_TITLE'); ?></th>
		<th style="width: 20%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_CATEGORIES'); ?></th>
		<th style="width: 15%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_CREATED_BY'); ?></th>
		<th style="width: 15%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_CREATED'); ?></th>
		<th style="width: 10%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_PUBLISHED'); ?></th>
		<th style="width: 10%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_APPROVED'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($lastCreatedDocuments AS $document)
	{
		$link        = 'index.php?option=com_judownload&amp;task=document.edit&amp;id=' . $document->id;
		$checked_out = $document->checked_out ? JHtml::_('jgrid.checkedout', $document->id, $document->checked_out_name, $document->checked_out_time, 'documents.', false) : '';
		?>
		<tr>
			<td><?php echo $checked_out ?>
				<a href="<?php echo $link; ?>" title="<?php echo $document->title; ?>"><?php echo $document->title; ?></a>
			</td>
			<td><?php echo $model->getCategories($document->id); ?></td>
			<td><?php echo $document->created_by_name; ?></td>
			<td><?php echo JHtml::date($document->created, 'Y-m-d H:i:s'); ?></td>
			<td class="center"><?php echo JHtml::_('grid.boolean', $document->id, $document->published); ?></td>
			<td class="center"><?php echo JHtml::_('grid.boolean', $document->id, $document->approved); ?></td>
		</tr>
	<?php
	} ?>
	</tbody>
</table>
<?php
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'top-5', 'last-added-comment', JText::_('COM_JUDOWNLOAD_LAST_ADDED_COMMENTS'));
?>
<table class="adminlist table table-striped">
	<thead>
	<tr>
		<th style="width: 30%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_TITLE'); ?></th>
		<th style="width: 20%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOCUMENT_TITLE'); ?></th>
		<th style="width: 15%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_CREATED_BY'); ?></th>
		<th style="width: 15%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_CREATED'); ?></th>
		<th style="width: 10%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_PUBLISHED'); ?></th>
		<th style="width: 10%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_APPROVED'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($lastCreatedComments AS $comment)
	{
		$link        = 'index.php?option=com_judownload&amp;task=comment.edit&amp;id=' . $comment->id;
		$checked_out = $comment->checked_out ? JHtml::_('jgrid.checkedout', $comment->id, $comment->checked_out_name, $comment->checked_out_time, 'comments.', false) : '';
		$doc_link    = 'index.php?option=com_judownload&amp;task=document.edit&amp;id=' . $comment->doc_id;
		?>
		<tr>
			<td><?php echo $checked_out ?>
				<a href="<?php echo $link; ?>" title="<?php echo $comment->title; ?>">
					<?php echo $comment->title; ?>
				</a>
			</td>
			<td>
				<a href="<?php echo $doc_link; ?>" title="<?php echo $comment->document_title; ?>">
					<?php echo $comment->document_title; ?>
				</a>
			</td>
			<td><?php echo $comment->created_by_name; ?></td>
			<td><?php echo JHtml::date($comment->created, 'Y-m-d H:i:s'); ?></td>
			<td class="center"><?php echo JHtml::_('grid.boolean', $comment->id, $comment->published); ?></td>
			<td class="center"><?php echo JHtml::_('grid.boolean', $comment->id, $comment->approved); ?></td>
		</tr>
	<?php
	} ?>
	</tbody>
</table>
<?php
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'top-5', 'last-download-document', JText::_('COM_JUDOWNLOAD_LAST_DOWNLOADED_DOCUMENTS'));
?>
<table class="adminlist table table-striped">
	<thead>
	<tr>
		<th style="width: 40%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_FILE_NAME'); ?></th>
		<th style="width: 30%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOCUMENT_TITLE'); ?></th>
		<th style="width: 15%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOWNLOADED_BY'); ?></th>
		<th style="width: 15%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOWNLOADED'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($lastDownloadedDocuments AS $item)
	{
		$link        = 'index.php?option=com_judownload&amp;task=document.edit&amp;id=' . $item->document_id;
		$checked_out = $item->checked_out ? JHtml::_('jgrid.checkedout', $item->document_id, $item->checked_out_name, $item->checked_out_time, 'documents.', false) : '';
		?>
		<tr>
			<td><?php
				if($item->log_reference == 'external')
				{
					$documentObject = JUDownloadHelper::getDocumentById($item->document_id);
					echo $documentObject->external_link;
				}
				else
				{
					echo JUDownloadHelper::getFileNames($item->log_reference, false);
				}
				?>
			</td>
			<td><?php echo $checked_out ?>
				<a href="<?php echo $link; ?>" title="<?php echo $item->document_title; ?>"><?php echo $item->document_title; ?></a>
			</td>
			<td><?php echo $item->download_by_name ? $item->download_by_name : JText::_('COM_JUDOWNLOAD_GUEST'); ?></td>
			<td><?php echo JHtml::date($item->download_date, 'Y-m-d H:i:s'); ?></td>
		</tr>
	<?php
	} ?>
	</tbody>
</table>
<!--  Last updated -->
<?php
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'top-5', 'last-update-document', JText::_('COM_JUDOWNLOAD_LAST_UPDATED_DOCUMENTS'));
?>
<table class="adminlist table table-striped">
	<thead>
	<tr>
		<th style="width: 45%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_TITLE'); ?></th>
		<th style="width: 20%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_UPDATED'); ?></th>
		<th style="width: 15%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_VERSION'); ?></th>
		<th style="width: 15%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_CREATED'); ?></th>
		<th style="width: 10%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_PUBLISHED'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($lastUpdatedDocuments AS $document)
	{
		$link        = 'index.php?option=com_judownload&amp;task=document.edit&amp;id=' . $document->id;
		$checked_out = $document->checked_out ? JHtml::_('jgrid.checkedout', $document->id, $document->checked_out_name, $document->checked_out_time, 'documents.', false) : '';
		?>
		<tr>
			<td><?php echo $checked_out ?>
				<a href="<?php echo $link; ?>" title="<?php echo $document->title; ?>"><?php echo $document->title; ?></a>
			</td>
			<td><?php echo $document->updated; ?></td>
			<td><?php echo $document->version; ?></td>
			<td><?php echo JHtml::date($document->created, 'Y-m-d H:i:s'); ?></td>
			<td class="center"><?php echo JHtml::_('grid.boolean', $document->id, $document->published); ?></td>
		</tr>
	<?php
	} ?>
	</tbody>
</table>
<!--  !Last updated -->

<!--  top download -->
<?php
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'top-5', 'top-document', JText::_('COM_JUDOWNLOAD_TOP_DOWNLOADED_DOCUMENTS'));
?>
<table class="adminlist table table-striped">
	<thead>
	<tr>
		<th style="width: 45%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_TITLE'); ?></th>
		<th style="width: 11%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_DOWNLOADS'); ?></th>
		<th style="width: 12%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_CREATED'); ?></th>
		<th style="width: 12%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_MODIFIED'); ?></th>
		<th style="width: 10%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_PUBLISHED'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($topDownloadDocuments AS $document)
	{
		$link        = 'index.php?option=com_judownload&amp;task=document.edit&amp;id=' . $document->id;
		$checked_out = $document->checked_out ? JHtml::_('jgrid.checkedout', $document->id, $document->checked_out_name, $document->checked_out_time, 'documents.', false) : '';
		?>
		<tr>
			<td><?php echo $checked_out ?>
				<a href="<?php echo $link; ?>" title="<?php echo $document->title; ?>"><?php echo $document->title; ?></a>
			</td>
			<td><?php echo $document->downloads; ?></td>
			<td><?php echo $document->created; ?></td>
			<td><?php echo $document->modified; ?></td>
			<td class="center"><?php echo JHtml::_('grid.boolean', $document->id, $document->published); ?></td>
		</tr>
	<?php
	} ?>
	</tbody>
</table>
<div id='top_download_table'></div>
<!--  !top download -->

<!--  popular document -->
<?php
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'top-5', 'popular-documents', JText::_('COM_JUDOWNLOAD_POPULAR_DOCUMENTS'));
?>
<table class="adminlist table table-striped">
	<thead>
	<tr>
		<th style="width: 45%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_TITLE'); ?></th>
		<th style="width: 15%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_HITS'); ?></th>
		<th style="width: 15%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_CREATED'); ?></th>
		<th style="width: 15%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_MODIFIED'); ?></th>
		<th style="width: 10%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_PUBLISHED'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($popularDocuments AS $document)
	{
		$link        = 'index.php?option=com_judownload&amp;task=document.edit&amp;id=' . $document->id;
		$checked_out = $document->checked_out ? JHtml::_('jgrid.checkedout', $document->id, $document->checked_out_name, $document->checked_out_time, 'documents.', false) : '';
		?>
		<tr>
			<td><?php echo $checked_out ?>
				<a href="<?php echo $link; ?>" title="<?php echo $document->title; ?>"><?php echo $document->title; ?></a>
			</td>
			<td><?php echo $document->hits; ?></td>
			<td><?php echo $document->created; ?></td>
			<td><?php echo $document->modified; ?></td>
			<td class="center"><?php echo JHtml::_('grid.boolean', $document->id, $document->published); ?></td>
		</tr>
	<?php
	} ?>
	</tbody>
</table>
<!--  !popular document -->

<!--  Static -->
<?php
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'top-5', 'static', JText::_('COM_JUDOWNLOAD_STATISTICS'));
?>
<table class="adminlist table table-striped">
	<thead>
	<tr>
		<th style="width: 75%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_TYPE'); ?></th>
		<th style="width: 25%"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_TOTAL'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($statistics AS $key => $value)
	{
		?>
		<tr>
			<td><?php echo $key; ?></td>
			<td><?php echo $value; ?></td>
		</tr>
	<?php
	} ?>
	</tbody>
</table>
<!--  !Static -->

<?php
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.endTabSet');
echo JHtml::_('bootstrap.endSlide');
echo JHtml::_('bootstrap.endAccordion');
?>

<?php
if(JUDLPROVERSION)
{
	echo JHtml::_('bootstrap.startAccordion', 'accordion-chart', array('active' => 'chart'));
	echo JHtml::_('bootstrap.addSlide', 'accordion-chart', JText::_('COM_JUDOWNLOAD_CHART'), 'chart', 'chart');
	$document = JFactory::getDocument();
	$document->addScript('https://www.google.com/jsapi');
	$app          = JFactory::getApplication();
	$type         = $app->getUserState('com_judownload.dashboard.chart.type', 'day');
	$downloadData = $this->getModel()->getUploadDownloadData($type);
	?>
	<script type="text/javascript">
		downloadData = '<?php echo json_encode($downloadData); ?>';
		var parsed = JSON.parse(downloadData);
		downloadData = [];
		for (key in parsed) {
			if (parsed.hasOwnProperty(key)) {
				downloadData[key] = parsed[key];
			}
		}

		google.load("visualization", "1", {packages: ["corechart"]});
		google.setOnLoadCallback(drawChart);

		function drawChart() {
			var vAxisTitle = getvAxisTitle('<?php echo $type; ?>');
			_drawChart(downloadData, vAxisTitle);
		}

		function _drawChart(downloadData, vAxisTitle) {
			var data = new google.visualization.DataTable();
			data.addColumn('string', '<?php echo JText::_('COM_JUDOWNLOAD_DAY')?>');
			data.addColumn('number', '<?php echo JText::_('COM_JUDOWNLOAD_DOWNLOADS')?>');
			data.addColumn('number', '<?php echo JText::_('COM_JUDOWNLOAD_UPLOADS')?>');
			data.addRows(downloadData.length);
			for (var $i = 0; $i < downloadData.length; $i++) {
				for (var $j = 0; $j < downloadData[$i].length; $j++) {
					if ($j == 0) {
						data.setCell($i, $j, String(downloadData[$i][$j]));
					} else {
						data.setCell($i, $j, parseInt(downloadData[$i][$j]));
					}
				}
			}

			var options = {
				axisTitlesPosition: 'in',
				chartArea: {left: 50, top: 80, width: '100%'},
				legend: {position: 'top'},
				title: '<?php echo JText::sprintf('COM_JUDOWNLOAD_DOWNLOADS_AND_UPLOADS_CHART', date('M/Y')); ?>',
				pointSize: 2,
				lineWidth: 1,
				hAxis: {title: '<?php echo JText::_('COM_JUDOWNLOAD_TIMES'); ?>'},
				vAxis: {title: vAxisTitle}
			};

			var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
			chart.draw(data, options);
		}

		function getvAxisTitle(type) {
			switch (type) {
				case 'day':
					var vAxisTitle = '<?php echo JText::_('COM_JUDOWNLOAD_HOUR'); ?>';
					break;
				case 'week':
				case 'month':
					var vAxisTitle = '<?php echo JText::_('COM_JUDOWNLOAD_DAY'); ?>';
					break;
				case 'year':
					var vAxisTitle = '<?php echo JText::_('COM_JUDOWNLOAD_MONTH'); ?>';
					break;
				default :
					var vAxisTitle = '';
			}

			return vAxisTitle;
		}

		jQuery(document).ready(function ($) {
			$('#upload_download_chart').change(function () {
				type = $(this).val();
				$.ajax({
					url: "index.php?option=com_judownload&task=dashboard.getChartData",
					data: {type: type},
					dataType: 'json',
					beforeSend: function () {
						$('#chart_div').css({opacity: 0.5}).append('<img style="position: absolute; top: 50%; left: 50%; opacity: 1" src="<?php echo JURi::base(true);?>/components/com_judownload/assets/img/orig-loading.gif"/>');
					}
				})
					.done(function (downloadData) {
						if (downloadData) {
							$('#chart_div').css({opacity: 1});
							var vAxisTitle = getvAxisTitle(type);
							_drawChart(downloadData, vAxisTitle);
						}
					});
			});
		});
	</script>

	<?php
	$typeOptions = array('day' => JText::_('COM_JUDOWNLOAD_DAY'), 'week' => JText::_('COM_JUDOWNLOAD_WEEK'), 'month' => JText::_('COM_JUDOWNLOAD_MONTH'), 'year' => JText::_('COM_JUDOWNLOAD_YEAR'));
	echo JHtml::_('select.genericlist', $typeOptions, 'upload_download_chart', 'class="input-medium"', 'text', 'value', $type);
	?>

	<div id="chart_div" style="width: 100%; height: 350px;"></div>

	<?php
	echo JHtml::_('bootstrap.endSlide');
	echo JHtml::_('bootstrap.endAccordion');
}
?>
</div>
</div>

<div class="clearfix"></div>

<div class="center small">
	<div><?php echo JUDownloadHelper::getComVersion(); ?></div>
	<div>A product of <a href="http://www.joomultra.com" title="Visit JoomUltra website" target="_blank">JoomUltra</a></div>
</div>