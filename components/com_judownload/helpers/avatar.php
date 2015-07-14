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

class JUDownloadAvatarHelper
{
	public static function getAvatar($user, $params = null)
	{
		if (!$params)
		{
			$params = JUDownloadHelper::getParams();
		}

		$avatar_source = $params->get('avatar_source', 'juavatar');
		switch ($avatar_source)
		{
			case 'gavatar':
				return self::getGAvatar($user->email);
				break;

			case 'comprofiler':
				return self::getCBAvatar($user->id);
				break;

			case 'kunena':
				return self::getKunenaAvatar($user->id);
				break;

			case 'k2':
				return self::getK2Avatar($user->id);
				break;

			case 'community':
				return self::getJomSocialAvatar($user->id);
				break;

			case 'juavatar':
				return self::getJUAvatar($user->id, $params);
				break;

			case 'none':
			default:
				return self::getDefaultAvatar($params);
				break;
		}
	}

	public static function getDefaultAvatar($params = null)
	{
		if (!$params)
		{
			$params = JUDownloadHelper::getParams();
		}

		$linkAvatar    = JUri::root(true) . "/" . JUDownloadFrontHelper::getDirectory("avatar_directory", "media/com_judownload/images/avatar/", true) . "default/" . $params->get('default_avatar', 'default-avatar.png');
		$avatar_source = $params->get('avatar_source', 'juavatar');
		if ($avatar_source == 'gavatar')
		{
			$linkAvatar = self::getGAvatar();
		}

		return $linkAvatar;
	}

	public static function getJUAvatar($id)
	{
		$user = JUDownloadFrontHelper::getUser($id, true);
		if ($user && isset($user->avatar))
		{
			$avatar = JUri::root(true) . '/' . JUDownloadFrontHelper::getDirectory('avatar_directory', 'media/com_judownload/images/avatar/', true) . $user->avatar;
		}
		else
		{
			$avatar = self::getDefaultAvatar();
		}

		return $avatar;
	}

	public static function getCBAvatar($id)
	{
		$files = JPATH_SITE . 'administrator/components/com_comprofiler/plugin.foundation.php';
		if (!JFile::exists($files))
		{
			$avatar = self::getDefaultAvatar();

			return $avatar;
		}

		require_once $files;
		cbimport('cb.database');
		cbimport('cb.tables');
		cbimport('cb.tabs');
		$user = CBuser::getInstance($id);

		
		if (!$id)
		{
			$avatar = self::getDefaultAvatar();

			return $avatar;
		}

		if (!$user)
		{
			$user = CBuser::getInstance(null);
		}

		
		ob_start();
		$source = $user->getField('avatar', null, 'php');
		$reset  = ob_get_contents();
		ob_end_clean();
		unset($reset);

		$source = $source['avatar'];

		
		$source = str_replace('/administrator/', '/', $source);




		return $source;
	}

	public static function getKunenaAvatar($id)
	{
		$files = JPATH_SITE . 'components/com_kunena/kunena.php';

		if (!JFile::exists($files))
		{
			$avatar = self::getDefaultAvatar();

			return $avatar;
		}

		$db    = JFactory::getDbo();
		$query = 'SELECT a.*, b.* FROM #__kunena_users AS a INNER JOIN #__users AS b ON b.id=a.userid WHERE a.userid=' . $db->quote($id);
		$db->setQuery($query);

		$user = $db->loadObject();
		
		$path   = 'media/kunena/avatars';
		$source = empty($user->avatar) ? 'nophoto.jpg' : str_replace('{', '', $user->avatar);

		$avatar = new stdClass();

		$avatar->link = JUri::root(true) . '/' . $path . '/' . $source;

		return $avatar->link;
	}

	public static function getJomSocialAvatar($id)
	{
		$files = JPATH_SITE . 'components/com_community/libraries/core.php';

		if (!JFile::exists($files))
		{
			$avatar = self::getDefaultAvatar();

			return $avatar;
		}

		require_once $files;

		$user = null;
		if (is_null($id))
		{
			$user = CFactory::getUser(0);
		}
		else
		{
			$user = CFactory::getUser($id);
		}

		$source = $user->getThumbAvatar();




		return $source;
	}

	public static function getK2Avatar($id)
	{
		$files = JPATH_SITE . 'components/com_k2/k2.php';

		if (!JFile::exists($files))
		{
			$avatar = self::getDefaultAvatar();

			return $avatar;
		}

		$file1 = JPATH_SITE . '/components/com_k2/helpers/route.php';
		$file2 = JPATH_SITE . '/components/com_k2/helpers/utilities.php';

		if (!JFile::exists($file1) || !JFile::exists($file2))
		{
			$avatar = self::getDefaultAvatar();

			return $avatar;
		}

		require_once $file1;
		require_once $file2;

		$db    = JFactory::getDbo();
		$query = 'SELECT * FROM #__k2_users  WHERE userID = ' . $db->Quote($id);

		$db->setQuery($query);
		$result = $db->loadObject();

		if (!$result || !$result->image)
		{
			$avatar = self::getDefaultAvatar();

			return $avatar;
		}

		$avatarLink = JUri::root(true) . '/' . 'media/k2/users/' . $result->image;




		return $avatarLink;
	}

	
	public static function getGAvatar($email = '', $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array())
	{
		$url = 'http://www.gravatar.com/avatar/';
		$url .= md5(strtolower(trim($email)));
		$url .= "?s=$s&d=$d&r=$r";
		if ($img)
		{
			$url = '<img src="' . $url . '"';
			foreach ($atts AS $key => $val)
				$url .= ' ' . $key . '="' . $val . '"';
			$url .= ' />';
		}

		return $url;
	}
}