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

/**
 * --- TimThumb CONFIGURATION ---
 * This file use for overriding TimThumb Configuration
 */

define ('DEBUG_ON', false);
define ('DEBUG_LEVEL', 1);
define ('MEMORY_LIMIT', '30M');
define ('DISPLAY_JERROR_MESSAGES', false);
define ('DISPLAY_ERROR_MESSAGES', false);

//define ('LOCAL_FILE_BASE_DIRECTORY', JPATH_ROOT);

//Image fetching and caching
define ('ALLOW_EXTERNAL', true);
define ('ALLOW_ALL_EXTERNAL_SITES', false);
define ('FILE_CACHE_ENABLED', true);
define ('FILE_CACHE_TIME_BETWEEN_CLEANS', 86400);
define ('FILE_CACHE_MAX_FILE_AGE', 86400);
define ('FILE_CACHE_SUFFIX', '.resized');
define ('FILE_CACHE_PREFIX', '');
define ('FILE_CACHE_DIRECTORY', JPATH_SITE . '/images/ju_cached_images');
define ('MAX_FILE_SIZE', 10485760);

//Browser caching
define ('BROWSER_CACHE_MAX_AGE', 864000);
define ('BROWSER_CACHE_DISABLE', false);

//Image size and defaults
define ('MAX_WIDTH', 3000);
define ('MAX_HEIGHT', 3000);
define ('NOT_FOUND_IMAGE', '');
define ('ERROR_IMAGE', '');
define ('PNG_IS_TRANSPARENT', false);
define ('DEFAULT_Q', 90);
define ('DEFAULT_CC', 'ffffff');
?>