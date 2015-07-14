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
<div id="judl-container" class="jubootstrap component judl-container view-usercomments">

	<?php
	if ($this->params->get('show_page_heading') && $this->params->get('page_heading'))
	{
		?>
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	<?php
	} ?>

	<h2><?php echo JText::sprintf('COM_JUDOWNLOAD_USER_COMMENTS_HEADING', $this->userCommentsUserName); ?></h2>

	<form action="#" method="post"
	      name="judl-form-usercomments" id="judl-form-usercomments" class="judl-form-usercomments">
		<?php
		echo $this->loadTemplate('header');

		if (count($this->items))
		{
			require_once JPATH_SITE . "/components/com_judownload/helpers/avatar.php";
			require_once JPATH_SITE . "/components/com_judownload/helpers/timeago.php";

			echo $this->loadTemplate('comments');
		}

		echo $this->loadTemplate('footer');
		?>
	</form>
</div>