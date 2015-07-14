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
//@todo properties has called in view
$user = JFactory::getUser();
$isOwnDashboard = JUDownloadFrontHelperPermission::isOwnDashboard();
JLoader::register('JUDownloadAvatarHelper', JPATH_SITE . '/components/com_judownload/helpers/avatar.php');
$avatar = JUDownloadAvatarHelper::getJUAvatar($user->id);
$userId = JUDownloadFrontHelper::getDashboardUserId();
$linkDashboard = JRoute::_(JUDownloadHelperRoute::getDashboardRoute($userId));
$linkUserProfile = JRoute::_(JUDownloadHelperRoute::getUserProfileRoute());
$linkCreateDocument = JRoute::_(JUDownloadHelperRoute::getFormRoute());
$linkUserComments = JRoute::_(JUDownloadHelperRoute::getUserCommentsRoute($userId));
$linkUserCollection = JRoute::_(JUDownloadHelperRoute::getCollectionsRoute($userId));
$linkUserDocuments = JRoute::_(JUDownloadHelperRoute::getUserDocumentsRoute($userId));
$linkUserSubscriptions = JRoute::_(JUDownloadHelperRoute::getUserSubscriptionsRoute($userId));
$linkLogOutReturn = JRoute::_('index.php',false);
?>
	<div class="navbar">
		<div class="navbar-inner">
			<ul class="nav">
				<li class="go-home">
					<a class="hasTooltip" title="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_HOME'); ?>"
					   href="<?php echo $linkDashboard ?>">
						<i class="fa fa-home"></i>
					</a>
				</li>

				<li class="documents">
					<a class="hasTooltip" title="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_DOCUMENTS'); ?>"
					   href="<?php echo $linkUserDocuments ?>">
						<i class="fa fa-files-o"></i>
					</a>
				</li>

				<li class="collections">
					<a class="hasTooltip" title="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_COLLECTIONS'); ?>"
					   href="<?php echo $linkUserCollection ?>">
						<i class="fa fa-inbox"></i>
					</a>
				</li>

				<li class="subscriptions">
					<a class="hasTooltip" title="<?php echo JText::_('COM_JUDOWNLOAD_USER_SUBSCRIPTIONS'); ?>"
					   href="<?php echo $linkUserSubscriptions ?>">
						<i class="fa fa-bookmark"></i>
					</a>
				</li>

				<li class="comments">
					<a class="hasTooltip" title="<?php echo JText::_('COM_JUDOWNLOAD_DASHBOARD_COMMENTS'); ?>"
					   href="<?php echo $linkUserComments ?>">
						<i class="fa fa-comments-o"></i>
					</a>
				</li>
				<?php if ($isOwnDashboard)
				{
					?>
					<li class="settings dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i><span class="caret"></span></a>
						<div class="dropdown-menu">
							<div style="float:right;">
								<img src="<?php echo $avatar ?>" alt="<?php echo $user->name ?>" class="dashboard-avatar" style="width:70px;height:70px"/>

								<h6><?php echo $user->name ?></h6>
							</div>

							<div>
								<a href="<?php echo $linkUserProfile ?>"><?php echo JText::_('COM_JUDOWNLOAD_EDIT_USER_PROFILE'); ?></a>
						     	<form id="logout" action="#" method="post">
									<input type="submit" value="Logout" class="btn btn-success"/>
									<input type="hidden" name="option" value="com_users"/>
									<input type="hidden" name="task" value="user.logout"/>
									<input type="hidden" name="return" value="<?php echo base64_encode($linkLogOutReturn); ?>"/>
									<?php echo JHtml::_('form.token'); ?>
								</form>
							</div>

						</div>
					</li>
				<?php
				} ?>
			</ul>
		</div>
	</div>

<?php if ($isOwnDashboard)
{ ?>
	<div class="dashboard-head clearfix">
		<div class="dashboard-avatar pull-left">
			<img alt="<?php echo $user->name ?>" src="<?php echo $avatar ?>"/>
		</div>

		<div class="pull-left">
			<div class="dashboard-username">
				<span class="user-name">
				<a href="<?php echo $linkDashboard ?>"><?php echo JText::_('COM_JUDOWNLOAD_USER_DASHBOARD'); ?></a>
				</span>
			</div>
			<div class="dashboard-actions">
				<a href="<?php echo $linkCreateDocument ?>"
				   class="btn btn-small"><?php echo JText::_('COM_JUDOWNLOAD_ADD_DOCUMENT'); ?></a>
				<a href="<?php echo $linkUserProfile ?>"
				   class="btn btn-small"><?php echo JText::_('COM_JUDOWNLOAD_EDIT_PROFILE'); ?></a>
			</div>
		</div>
	</div>
<?php
} ?>