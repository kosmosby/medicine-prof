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
<div id="judl-container" class="jubootstrap component judl-container view-topcomments">
	<form action="#" method="post"
	      name="judl-form-topcomments" id="judl-form-topcomments" class="judl-form-topcomments">
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