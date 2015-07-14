<?php
/*
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


JLoader::register('JUDownloadFrontHelper', JPATH_SITE . '/components/com_judownload/helpers/helper.php');
JLoader::register('JUDownloadFrontHelperBreadcrumb', JPATH_SITE . '/components/com_judownload/helpers/breadcrumb.php');
JLoader::register('JUDownloadFrontHelperCaptcha', JPATH_SITE . '/components/com_judownload/helpers/captcha.php');
JLoader::register('JUDownloadFrontHelperCategory', JPATH_SITE . '/components/com_judownload/helpers/category.php');
JLoader::register('JUDownloadFrontHelperComment', JPATH_SITE . '/components/com_judownload/helpers/comment.php');
JLoader::register('JUDownloadFrontHelperCriteria', JPATH_SITE . '/components/com_judownload/helpers/criteria.php');
JLoader::register('JUDownloadFrontHelperDocument', JPATH_SITE . '/components/com_judownload/helpers/document.php');
JLoader::register('JUDownloadFrontHelperEditor', JPATH_SITE . '/components/com_judownload/helpers/editor.php');
JLoader::register('JUDownloadFrontHelperField', JPATH_SITE . '/components/com_judownload/helpers/field.php');
JLoader::register('JUDownloadFrontHelperLanguage', JPATH_SITE . '/components/com_judownload/helpers/language.php');
JLoader::register('JUDownloadFrontHelperLog', JPATH_SITE . '/components/com_judownload/helpers/log.php');
JLoader::register('JUDownloadFrontHelperMail', JPATH_SITE . '/components/com_judownload/helpers/mail.php');
JLoader::register('JUDownloadFrontHelperModerator', JPATH_SITE . '/components/com_judownload/helpers/moderator.php');
JLoader::register('JUDownloadFrontHelperPassword', JPATH_SITE . '/components/com_judownload/helpers/password.php');
JLoader::register('JUDownloadFrontHelperPermission', JPATH_SITE . '/components/com_judownload/helpers/permission.php');
JLoader::register('JUDownloadFrontHelperPluginParams', JPATH_SITE . '/components/com_judownload/helpers/pluginparams.php');
JLoader::register('JUDownloadFrontHelperRating', JPATH_SITE . '/components/com_judownload/helpers/rating.php');
JLoader::register('JUDownloadFrontHelperSeo', JPATH_SITE . '/components/com_judownload/helpers/seo.php');
JLoader::register('JUDownloadFrontHelperString', JPATH_SITE . '/components/com_judownload/helpers/string.php');
JLoader::register('JUDownloadFrontHelperTemplate', JPATH_SITE . '/components/com_judownload/helpers/template.php');
JLoader::register('JUDownloadHelper', JPATH_ADMINISTRATOR . '/components/com_judownload/helpers/judownload.php');
JLoader::register('JUDownloadHelperRoute', JPATH_SITE . '/components/com_judownload/helpers/route.php');


if (!class_exists('JComponentRouterBase'))
{
	
	abstract class JComponentRouterBase
	{
		
		public function preprocess($query)
		{
			return $query;
		}
	}
}


class JUDownloadRouter extends JComponentRouterBase
{
	
	public function build(&$query)
	{
		$segments = array();

		
		$app   = JFactory::getApplication('site');
		$menus = $app->getMenu('site');
		
		$activeMenu = $menus->getActive();
		$params     = JUDownloadHelper::getParams();
		$homeItemId = JUDownloadHelperRoute::getHomeMenuItemId();

		
		if (empty($query['Itemid']) && isset($query['view']) && $query['view'] != 'category' && $query['view'] != 'document')
		{
			$query['Itemid'] = JUDownloadHelperRoute::findJUDownloadTreeItemId();
		}

		if (isset($query['view']))
		{
			$menuItem = $menus->getItem($query['Itemid']);

			
			if (isset($menuItem) && ($menuItem->component != 'com_judownload' && $menuItem->id != $homeItemId))
			{
				unset($query['Itemid']);
			}
		}

		if (!$query || (!isset($query['view']) && !isset($query['task'])))
		{
			
			if (isset($query['start']))
			{
				$sefPageConfig = JApplication::stringURLSafe('page');
				$pageX         = JUDownloadHelperRoute::getPage($query['start'], $activeMenu->query['view']);
				$segments[]    = $sefPageConfig . ':' . $pageX;

				unset($query['start']);
			}

			JUDownloadHelperRoute::seoFormat($query, $params, $segments);

			$total = count($segments);

			for ($i = 0; $i < $total; $i++)
			{
				$segments[$i] = str_replace(':', '-', $segments[$i]);
			}

			if (isset($query['limit']))
			{
				unset($query['limit']);
			}

			
			return $segments;
		}

		$hasActiveMenu = false;
		if (is_object($activeMenu) && isset($activeMenu->query))
		{
			if (isset($query['Itemid']) && ($query['Itemid'] == $activeMenu->id))
			{
				$hasActiveMenu = JUDownloadHelperRoute::compareQuery($activeMenu, $query);
			}
		}

		

		
		if (isset($query['view']) && $query['view'] == 'categories')
		{
			if (!$hasActiveMenu)
			{
				if (isset($query['id']))
				{
					if (isset($query['Itemid']))
					{
						if ($query['Itemid'] == $homeItemId)
						{
							
							$sefRootCategory = JApplication::stringURLSafe('root');
							$segments[]      = JApplication::stringURLSafe($sefRootCategory);
						}
					}

					
					$sefCategoriesConfig = JApplication::stringURLSafe('categories');
					$segments[]          = JApplication::stringURLSafe($sefCategoriesConfig);

					$categoryObject = JUDownloadHelper::getCategoryById($query['id']);

					$segments[] = $query['id'] . ":" . (isset($categoryObject->alias) ? $categoryObject->alias : '');

					unset($query['id']);
				}

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['id']))
				{
					unset($query['id']);
				}

				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'category')
		{
			if (!$hasActiveMenu)
			{
				$fullPathCategory = $params->get('sef_category_full_path', 0);
				if (isset($query['id']))
				{
					$segments = JUDownloadHelperRoute::getCategorySegment($query['id'], $query, $fullPathCategory);
					if ($segments !== false)
					{
						unset($query['id']);
					}
				}

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['id']))
				{
					unset($query['id']);
				}

				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			if (isset($query['format']))
			{
				$segments[] = JApplication::stringURLSafe('rss');
				unset($query['format']);
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'advsearch')
		{
			if (!$hasActiveMenu)
			{
				if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
				{
					
					$sefRootCategory = JApplication::stringURLSafe('root');
					$segments[]      = JApplication::stringURLSafe($sefRootCategory);
				}

				$segments[] = JApplication::stringURLSafe('advanced-search');

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'collection')
		{
			if (!$hasActiveMenu)
			{
				if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
				{
					
					$sefRootCategory = 'root';
					$segments[]      = JApplication::stringURLSafe($sefRootCategory);
				}

				if (isset($query['id']) && $query['id'])
				{
					if (isset($query['user_id']))
					{
						$user       = JFactory::getUser($query['user_id']);
						$userAlias  = JApplication::stringURLSafe($user->username);
						$segments[] = $query['user_id'] . ':' . $userAlias;
						unset($query['user_id']);
					}

					$segments[] = JApplication::stringURLSafe('collection');

					$collectionObject = JUDownloadFrontHelper::getCollectionById($query['id']);

					$segments[] = $query['id'] . ':' . (isset($collectionObject->alias) ? $collectionObject->alias : '');
					unset($query['id']);

					JUDownloadHelperRoute::seoLayout($query, $segments, $params);
				}
				else
				{
					$segments[] = JApplication::stringURLSafe('collection');

					$segments[] = JApplication::stringURLSafe('new-collection');

					JUDownloadHelperRoute::seoLayout($query, $segments, $params);

					if (isset($query['id']))
					{
						unset($query['id']);
					}
				}
			}
			else
			{
				if (isset($query['user_id']))
				{
					unset($query['user_id']);
				}

				if (isset($query['id']))
				{
					unset($query['id']);
				}
			}

			if (isset($query['format']))
			{
				$segments[] = JApplication::stringURLSafe('rss');
				unset($query['format']);
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'collections')
		{
			if (!$hasActiveMenu)
			{
				if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
				{
					
					$sefRootCategory = JApplication::stringURLSafe('root');
					$segments[]      = JApplication::stringURLSafe($sefRootCategory);
				}

				if (isset($query['id']))
				{
					$user       = JFactory::getUser($query['id']);
					$userAlias  = JApplication::stringURLSafe($user->username);
					$segments[] = $query['id'] . ':' . $userAlias;
					unset($query['id']);
				}

				$segments[] = JApplication::stringURLSafe('collections');

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['id']))
				{
					unset($query['id']);
				}

				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'commenttree' && isset($query['id']))
		{
			if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
			{
				
				$sefRootCategory = JApplication::stringURLSafe('root');
				$segments[]      = JApplication::stringURLSafe($sefRootCategory);
			}

			$segments[] = JApplication::stringURLSafe('comment-tree');

			$commentObject = JUDownloadFrontHelperComment::getCommentObject($query['id']);

			$commentAlias = JApplication::stringURLSafe($commentObject->title);
			$segments[]   = $query['id'] . ':' . $commentAlias;

			if (isset($query['tmpl']))
			{
				$segments[] = JApplication::stringURLSafe('component');
				unset($query['tmpl']);
			}

			unset($query['id']);
			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'contact' && isset($query['doc_id']))
		{
			if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
			{
				
				$sefRootCategory = JApplication::stringURLSafe('root');
				$segments[]      = JApplication::stringURLSafe($sefRootCategory);
			}

			$segments   = JUDownloadHelperRoute::getDocumentSegment($query['doc_id'], $query, $params);
			$segments[] = JApplication::stringURLSafe('contact');

			JUDownloadHelperRoute::seoLayout($query, $segments, $params);

			unset($query['doc_id']);
			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'dashboard')
		{
			if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
			{
				
				$sefRootCategory = JApplication::stringURLSafe('root');
				$segments[]      = JApplication::stringURLSafe($sefRootCategory);
			}

			if (isset($query['id']))
			{
				$user       = JFactory::getUser($query['id']);
				$userAlias  = JApplication::stringURLSafe($user->username);
				$segments[] = $query['id'] . ':' . $userAlias;
				unset($query['id']);
			}

			$segments[] = JApplication::stringURLSafe('dashboard');

			JUDownloadHelperRoute::seoLayout($query, $segments, $params);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'document')
		{
			if (!$hasActiveMenu)
			{
				$seoLayout = true;
				if (isset($query['id']))
				{
					$segments = JUDownloadHelperRoute::getDocumentSegment($query['id'], $query, $params);
					unset($query['id']);
				}

				if (isset($query['print']))
				{
					$seoLayout  = false;
					$segments[] = JApplication::stringURLSafe('print');
					unset($query['print']);
					unset($query['layout']);
					unset($query['tmpl']);
				}

				if (isset($query['layout']) && $query['layout'] == 'changelogs')
				{
					$seoLayout  = false;
					$segments[] = JApplication::stringURLSafe('changelogs');
					unset($query['layout']);
				}

				if (isset($query['layout']) && $query['layout'] == 'versions')
				{
					$seoLayout  = false;
					$segments[] = JApplication::stringURLSafe('versions');
					unset($query['layout']);
				}

				if ($seoLayout)
				{
					JUDownloadHelperRoute::seoLayout($query, $segments, $params);
				}
			}
			else
			{
				if (isset($query['id']))
				{
					unset($query['id']);
				}

				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'documents')
		{
			if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
			{
				
				$sefRootCategory = JApplication::stringURLSafe('root');
				$segments[]      = JApplication::stringURLSafe($sefRootCategory);
			}

			$segments[] = JApplication::stringURLSafe('modal-documents');

			if (isset($query['tmpl']))
			{
				$segments[] = $query['tmpl'];
				unset($query['tmpl']);
			}

			if (isset($query['function']))
			{
				$segments[] = $query['function'];
				unset($query['function']);
			}

			JUDownloadHelperRoute::seoLayout($query, $segments, $params);

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		

		
		if (isset($query['view']) && $query['view'] == 'downloaderror')
		{
			if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
			{
				
				$sefRootCategory = JApplication::stringURLSafe('root');
				$segments[]      = JApplication::stringURLSafe($sefRootCategory);
			}

			$segments[] = JApplication::stringURLSafe('error-download');

			JUDownloadHelperRoute::seoLayout($query, $segments, $params);

			if (isset($query['return']))
			{
				$segments[] = $query['return'];
				unset($query['return']);
			}

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'featured')
		{
			if (!$hasActiveMenu)
			{
				$addCategoryToSegment = true;
				if (isset($query['Itemid']))
				{
					if ($query['Itemid'] == $homeItemId)
					{
						
						$sefRootCategory = JApplication::stringURLSafe('root');
						$segments[]      = JApplication::stringURLSafe($sefRootCategory);

						if (isset($query['id']))
						{
							$categoryObject = JUDownloadHelper::getCategoryById($query['id']);
							if ($categoryObject->level > 0)
							{
								$segments[] = $query['id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
							}
							unset($query['id']);
						}

						$addCategoryToSegment = false;
					}
					else
					{
						$assignMenuFeatured = $menus->getItem($query['Itemid']);
						if ($assignMenuFeatured && isset($assignMenuFeatured->query) && $assignMenuFeatured->query['view'] == 'tree'
							&& isset($assignMenuFeatured->query['id'])
						)
						{
							if (isset($query['id']))
							{
								$categoryObject = JUDownloadHelper::getCategoryById($query['id']);
								if ($assignMenuFeatured->query['id'] != $categoryObject->id)
								{
									$segments[] = $query['id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
								}
								unset($query['id']);
							}

							$addCategoryToSegment = false;
						}
					}
				}

				if ($addCategoryToSegment)
				{
					if (isset($query['id']))
					{
						$categoryObject = JUDownloadHelper::getCategoryById($query['id']);
						$segments[]     = $query['id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
						unset($query['id']);
					}
				}

				$segments[] = JApplication::stringURLSafe('featured');

				if (isset($query['all']))
				{
					if ($query['all'] == 1)
					{
						$segments[] = JApplication::stringURLSafe('all');
					}
					unset($query['all']);
				}

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['id']))
				{
					unset($query['id']);
				}

				if (isset($query['all']))
				{
					unset($query['all']);
				}

				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			if (isset($query['format']))
			{
				$segments[] = JApplication::stringURLSafe('rss');
				unset($query['format']);
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'form' && isset($query['layout']) && $query['layout'] == 'edit' && (!isset($query['id']) || (isset($query['id']) && !$query['id'])))
		{
			if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
			{
				
				$sefRootCategory = JApplication::stringURLSafe('root');
				$segments[]      = JApplication::stringURLSafe($sefRootCategory);
			}

			if (isset($query['cat_id']))
			{
				$categoryObject = JUDownloadHelper::getCategoryById($query['cat_id']);
				$segments[]     = $query['cat_id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
				unset($query['cat_id']);
			}
			else
			{
				$categoryRoot = JUDownloadFrontHelperCategory::getRootCategory();
				if (is_object($categoryRoot))
				{
					$segments[] = $categoryRoot->id . ':' . (isset($categoryRoot->alias) ? $categoryRoot->alias : '');
				}
			}

			$segments[] = JApplication::stringURLSafe('new-document');

			JUDownloadHelperRoute::seoLayout($query, $segments, $params);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'form' && isset($query['layout']) && $query['layout'] == 'edit' && isset($query['id']) && $query['id'])
		{
			if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
			{
				
				$sefRootCategory = JApplication::stringURLSafe('root');
				$segments[]      = JApplication::stringURLSafe($sefRootCategory);
			}

			$segments = JUDownloadHelperRoute::getDocumentSegment($query['id'], $query, $params);
			if (isset($query['approve']) && $query['approve'] == 1)
			{
				$segments[] = JApplication::stringURLSafe('approve');
				unset($query['approve']);
			}

			JUDownloadHelperRoute::seoLayout($query, $segments, $params);

			unset($query['id']);
			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'license' && isset($query['id']))
		{
			if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
			{
				
				$sefRootCategory = JApplication::stringURLSafe('root');
				$segments[]      = JApplication::stringURLSafe($sefRootCategory);
			}

			$segments[] = JApplication::stringURLSafe('license');

			$licenseObject = JUDownloadFrontHelper::getLicense($query['id']);

			$segments[] = $query['id'] . ':' . (isset($licenseObject->alias) ? $licenseObject->alias : '');

			JUDownloadHelperRoute::seoLayout($query, $segments, $params);

			unset($query['id']);
			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'listall')
		{
			if (!$hasActiveMenu)
			{
				$addCategoryToSegment = true;
				if (isset($query['Itemid']))
				{
					if ($query['Itemid'] == $homeItemId)
					{
						
						$sefRootCategory = JApplication::stringURLSafe('root');
						$segments[]      = JApplication::stringURLSafe($sefRootCategory);

						if (isset($query['id']))
						{
							$categoryObject = JUDownloadHelper::getCategoryById($query['id']);
							if ($categoryObject->level > 0)
							{
								$segments[] = $query['id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
								unset($query['id']);
							}
						}

						$addCategoryToSegment = false;
					}
					else
					{
						$assignMenuListAll = $menus->getItem($query['Itemid']);
						if ($assignMenuListAll && isset($assignMenuListAll->query) && $assignMenuListAll->query['view'] == 'tree'
							&& isset($assignMenuListAll->query['id'])
						)
						{
							if (isset($query['id']))
							{
								$categoryObject = JUDownloadHelper::getCategoryById($query['id']);
								if ($assignMenuListAll->query['id'] != $categoryObject->id)
								{
									$segments[] = $query['id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
								}
								unset($query['id']);
							}

							$addCategoryToSegment = false;
						}
					}
				}

				if ($addCategoryToSegment)
				{
					if (isset($query['id']))
					{
						$categoryObject = JUDownloadHelper::getCategoryById($query['id']);
						$segments[]     = $query['id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
						unset($query['id']);
					}
				}

				$segments[] = JApplication::stringURLSafe('list-all');

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['id']))
				{
					unset($query['id']);
				}
			}

			if (isset($query['format']))
			{
				$segments[] = JApplication::stringURLSafe('rss');
				unset($query['format']);
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'listalpha')
		{
			if (!$hasActiveMenu)
			{
				$addCategoryToSegment = true;
				if (isset($query['Itemid']))
				{
					if ($query['Itemid'] == $homeItemId)
					{
						
						$sefRootCategory = JApplication::stringURLSafe('root');
						$segments[]      = JApplication::stringURLSafe($sefRootCategory);

						if (isset($query['id']))
						{
							$categoryObject = JUDownloadHelper::getCategoryById($query['id']);
							if ($categoryObject->level > 0)
							{
								$segments[] = $query['id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
							}
							unset($query['id']);
						}

						$addCategoryToSegment = false;
					}
					else
					{
						$assignMenuListAlpha = $menus->getItem($query['Itemid']);
						if ($assignMenuListAlpha && isset($assignMenuListAlpha->query) && $assignMenuListAlpha->query['view'] == 'tree'
							&& isset($assignMenuListAlpha->query['id'])
						)
						{
							if (isset($query['id']))
							{
								$categoryObject = JUDownloadHelper::getCategoryById($query['id']);
								if ($assignMenuListAlpha->query['id'] != $categoryObject->id)
								{
									$segments[] = $query['id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
								}
								unset($query['id']);
							}

							$addCategoryToSegment = false;
						}
					}
				}

				if ($addCategoryToSegment)
				{
					if (isset($query['id']))
					{
						$categoryObject = JUDownloadHelper::getCategoryById($query['id']);
						$segments[]     = $query['id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
						unset($query['id']);
					}
				}

				$segments[] = JApplication::stringURLSafe('list-alpha');

				if (isset($query['alpha']))
				{
					$segments[] = $query['alpha'];
					unset($query['alpha']);
				}

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['id']))
				{
					unset($query['id']);
				}

				if (isset($query['alpha']))
				{
					unset($query['alpha']);
				}

				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			if (isset($query['format']))
			{
				$segments[] = JApplication::stringURLSafe('rss');
				unset($query['format']);
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'maintenance')
		{
			if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
			{
				
				$sefRootCategory = JApplication::stringURLSafe('root');
				$segments[]      = JApplication::stringURLSafe($sefRootCategory);
			}

			$segments[] = JApplication::stringURLSafe('maintenance');

			JUDownloadHelperRoute::seoLayout($query, $segments, $params);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'modcomment' && isset($query['layout']) && $query['layout'] == 'edit' && isset($query['id']) && $query['id'])
		{
			if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
			{
				
				$sefRootCategory = JApplication::stringURLSafe('root');
				$segments[]      = JApplication::stringURLSafe($sefRootCategory);
			}

			$segments[] = JApplication::stringURLSafe('mod-comment');

			$commentObject = JUDownloadFrontHelperComment::getCommentObject($query['id']);
			if (is_object($commentObject))
			{
				$commentAlias = JApplication::stringURLSafe($commentObject->title);
				$segments[]   = $query['id'] . ':' . $commentAlias;
				unset($query['id']);
			}

			if (isset($query['approve']) && $query['approve'])
			{
				$segments[] = JApplication::stringURLSafe('approve');
				unset($query['approve']);
			}

			JUDownloadHelperRoute::seoLayout($query, $segments, $params);

			unset($query['view']);
			unset($query['layout']);
		}

		
		if (isset($query['view']) && $query['view'] == 'modcomments')
		{
			if (!$hasActiveMenu)
			{
				if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
				{
					
					$sefRootCategory = JApplication::stringURLSafe('root');
					$segments[]      = JApplication::stringURLSafe($sefRootCategory);
				}

				$segments[] = JApplication::stringURLSafe('mod-comments');

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'moddocuments')
		{
			if (!$hasActiveMenu)
			{
				if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
				{
					
					$sefRootCategory = JApplication::stringURLSafe('root');
					$segments[]      = JApplication::stringURLSafe($sefRootCategory);
				}

				$segments[] = JApplication::stringURLSafe('mod-documents');

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'modpermission' && isset($query['id']))
		{
			if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
			{
				
				$sefRootCategory = JApplication::stringURLSafe('root');
				$segments[]      = JApplication::stringURLSafe($sefRootCategory);
			}

			$segments[] = JApplication::stringURLSafe('mod-permission');
			$segments[] = $query['id'];

			JUDownloadHelperRoute::seoLayout($query, $segments, $params);

			unset($query['id']);
			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'modpermissions')
		{
			if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
			{
				
				$sefRootCategory = 'root';
				$segments[]      = JApplication::stringURLSafe($sefRootCategory);
			}

			$segments[] = JApplication::stringURLSafe('mod-permissions');

			JUDownloadHelperRoute::seoLayout($query, $segments, $params);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'modpendingcomments')
		{
			if (!$hasActiveMenu)
			{
				if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
				{
					
					$sefRootCategory = 'root';
					$segments[]      = JApplication::stringURLSafe($sefRootCategory);
				}

				$segments[] = JApplication::stringURLSafe('mod-pending-comments');

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'modpendingdocuments')
		{
			if (!$hasActiveMenu)
			{
				if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
				{
					
					$sefRootCategory = 'root';
					$segments[]      = JApplication::stringURLSafe($sefRootCategory);
				}

				$segments[] = JApplication::stringURLSafe('mod-pending-documents');

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'profile')
		{
			if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
			{
				
				$sefRootCategory = 'root';
				$segments[]      = JApplication::stringURLSafe($sefRootCategory);
			}

			$segments[] = JApplication::stringURLSafe('profile');

			JUDownloadHelperRoute::seoLayout($query, $segments, $params);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'report' && isset($query['doc_id']))
		{
			if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
			{
				
				$sefRootCategory = 'root';
				$segments[]      = JApplication::stringURLSafe($sefRootCategory);
			}

			$segments   = JUDownloadHelperRoute::getDocumentSegment($query['doc_id'], $query, $params);
			$segments[] = JApplication::stringURLSafe('report');

			JUDownloadHelperRoute::seoLayout($query, $segments, $params);

			unset($query['doc_id']);
			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'report' && isset($query['comment_id']))
		{
			if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
			{
				
				$sefRootCategory = 'root';
				$segments[]      = JApplication::stringURLSafe($sefRootCategory);
			}

			$segments[]    = JApplication::stringURLSafe('comment');
			$commentObject = JUDownloadFrontHelperComment::getCommentObject($query['comment_id']);

			$commentAlias = JApplication::stringURLSafe($commentObject->title);
			$segments[]   = $query['comment_id'] . ':' . $commentAlias;
			unset($query['comment_id']);

			$segments[] = JApplication::stringURLSafe('report');

			JUDownloadHelperRoute::seoLayout($query, $segments, $params);

			unset($query['comment_id']);
			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'search')
		{
			if (!$hasActiveMenu)
			{
				if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
				{
					
					$sefRootCategory = 'root';
					$segments[]      = JApplication::stringURLSafe($sefRootCategory);
				}

				if (isset($query['cat_id']))
				{
					$categoryObject = JUDownloadHelper::getCategoryById($query['cat_id']);
					$segments[]     = $query['cat_id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
					unset($query['cat_id']);
				}

				if (isset($query['sub_cat']))
				{
					$segments[] = JApplication::stringURLSafe('all');
					unset($query['sub_cat']);
				}

				$segments[] = JApplication::stringURLSafe('search');

				if (isset($query['searchword']))
				{
					$segments[] = $query['searchword'];
					unset($query['searchword']);
				}

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['cat_id']))
				{
					unset($query['cat_id']);
				}

				if (isset($query['searchword']))
				{
					unset($query['searchword']);
				}

				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'searchby')
		{
			if (!$hasActiveMenu)
			{
				if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
				{
					
					$sefRootCategory = 'root';
					$segments[]      = JApplication::stringURLSafe($sefRootCategory);
				}

				$segments[] = JApplication::stringURLSafe('search-by');

				if (isset($query['field_id']))
				{
					$fieldObject = JUDownloadFrontHelperField::getFieldById($query['field_id']);
					$segments[]  = $query['field_id'] . ':' . (isset($fieldObject->alias) ? $fieldObject->alias : '');
					unset($query['field_id']);
				}

				if (isset($query['value']))
				{
					$segments[] = $query['value'];
					unset($query['value']);
				}

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['field_id']))
				{
					unset($query['field_id']);
				}

				if (isset($query['value']))
				{
					unset($query['value']);
				}

				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'subscribe' && isset($query['doc_id']))
		{
			if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
			{
				
				$sefRootCategory = 'root';
				$segments[]      = JApplication::stringURLSafe($sefRootCategory);
			}

			$segments   = JUDownloadHelperRoute::getDocumentSegment($query['doc_id'], $query, $params);
			$segments[] = JApplication::stringURLSafe('guest-subscribe');

			JUDownloadHelperRoute::seoLayout($query, $segments, $params);

			unset($query['doc_id']);
			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'tag' && isset($query['id']))
		{
			if (!$hasActiveMenu)
			{
				if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
				{
					
					$sefRootCategory = 'root';
					$segments[]      = JApplication::stringURLSafe($sefRootCategory);
				}

				$segments[] = JApplication::stringURLSafe('tag');

				$tagObject = JUDownloadFrontHelper::getTagById($query['id']);

				$segments[] = $query['id'] . ':' . (isset($tagObject->alias) ? $tagObject->alias : '');

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['id']))
				{
					unset($query['id']);
				}

				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoFormat($query, $params, $segments);

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['id']);
			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'tags')
		{
			if (!$hasActiveMenu)
			{
				if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
				{
					
					$sefRootCategory = 'root';
					$segments[]      = JApplication::stringURLSafe($sefRootCategory);
				}

				$segments[] = JApplication::stringURLSafe('tags');

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'topcomments')
		{
			if (!$hasActiveMenu)
			{
				if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
				{
					
					$sefRootCategory = 'root';
					$segments[]      = JApplication::stringURLSafe($sefRootCategory);
				}

				$segments[] = JApplication::stringURLSafe('top-comments');

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'topdocuments')
		{
			$addCategoryToSegment = true;
			if (!$hasActiveMenu)
			{
				if (isset($query['Itemid']))
				{
					if ($query['Itemid'] == $homeItemId)
					{
						
						$sefRootCategory = 'root';
						$segments[]      = JApplication::stringURLSafe($sefRootCategory);

						if (isset($query['id']))
						{
							$categoryObject = JUDownloadHelper::getCategoryById($query['id']);
							if ($categoryObject->level > 0)
							{
								$segments[] = $query['id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
							}
							unset($query['id']);
						}

						$addCategoryToSegment = false;
					}
					else
					{
						$assignMenuTopDocuments = $menus->getItem($query['Itemid']);
						if ($assignMenuTopDocuments && isset($assignMenuTopDocuments->query) && $assignMenuTopDocuments->query['view'] == 'tree'
							&& isset($assignMenuTopDocuments->query['id'])
						)
						{
							if (isset($query['id']))
							{
								$categoryObject = JUDownloadHelper::getCategoryById($query['id']);
								if ($assignMenuTopDocuments->query['id'] != $categoryObject->id)
								{
									$segments[] = $query['id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
								}
								unset($query['id']);
							}

							$addCategoryToSegment = false;
						}
					}
				}

				if ($addCategoryToSegment)
				{
					if (isset($query['id']))
					{
						$categoryObject = JUDownloadHelper::getCategoryById($query['id']);
						$segments[]     = $query['id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
						unset($query['id']);
					}
				}

				if (isset($query['ordertype']))
				{
					switch ($query['ordertype'])
					{
						case 'new' :
							$segments[] = JApplication::stringURLSafe('latest-documents');
							break;
						case 'featured' :
							$segments[] = JApplication::stringURLSafe('top-featured-documents');
							break;
						case 'recent_modified' :
							$segments[] = JApplication::stringURLSafe('recent-modified-documents');
							break;
						case 'recent_updated' :
							$segments[] = JApplication::stringURLSafe('recent-updated-documents');
							break;
						case 'popular' :
							$segments[] = JApplication::stringURLSafe('popular-documents');
							break;
						case 'most_downloaded' :
							$segments[] = JApplication::stringURLSafe('most-downloaded-documents');
							break;
						case 'most_rated' :
							$segments[] = JApplication::stringURLSafe('most-rated-documents');
							break;
						case 'top_rated' :
							$segments[] = JApplication::stringURLSafe('top-rated-documents');
							break;
						case 'latest_rated' :
							$segments[] = JApplication::stringURLSafe('latest-rated-documents');
							break;
						case 'most_commented' :
							$segments[] = JApplication::stringURLSafe('most-commented-documents');
							break;
						case 'latest_commented' :
							$segments[] = JApplication::stringURLSafe('latest-commented-documents');
							break;
						case 'recently_viewed' :
							$segments[] = JApplication::stringURLSafe('recent-viewed-documents');
							break;
						case 'alpha_ordered' :
							$segments[] = JApplication::stringURLSafe('alpha-ordered-documents');
							break;
						case 'random' :
							$segments[] = JApplication::stringURLSafe('random-documents');
							break;
						case 'random_fast' :
							$segments[] = JApplication::stringURLSafe('random-fast-documents');
							break;
						case 'random_featured' :
							$segments[] = JApplication::stringURLSafe('random-featured-documents');
							break;
						default:
							$segments[] = JApplication::stringURLSafe('latest-documents');
					}
					unset($query['ordertype']);
				}

				if (isset($query['all']))
				{
					if ($query['all'] == 1)
					{
						$segments[] = JApplication::stringURLSafe('all');
					}

					unset($query['all']);
				}

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['id']))
				{
					unset($query['id']);
				}

				if (isset($query['ordertype']))
				{
					unset($query['ordertype']);
				}

				if (isset($query['all']))
				{
					unset($query['all']);
				}

				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoFormat($query, $params, $segments);

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'tree' && isset($query['id']))
		{
			if (!$hasActiveMenu)
			{
				$addCategoryToSegment = true;
				if (isset($query['Itemid']))
				{
					if ($query['Itemid'] == $homeItemId)
					{
						
						$sefRootCategory = 'root';
						$segments[]      = JApplication::stringURLSafe($sefRootCategory);

						if (isset($query['id']))
						{
							$categoryObject = JUDownloadHelper::getCategoryById($query['id']);
							if ($categoryObject->level > 0)
							{
								$segments[] = $query['id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
							}
							unset($query['id']);
						}

						$addCategoryToSegment = false;
					}
					else
					{
						$assignMenuTree = $menus->getItem($query['Itemid']);
						if ($assignMenuTree && isset($assignMenuTree->query) && $assignMenuTree->query['view'] == 'tree'
							&& isset($assignMenuTree->query['id'])
						)
						{
							if (isset($query['id']))
							{
								$categoryObject = JUDownloadHelper::getCategoryById($query['id']);
								if ($assignMenuTree->query['id'] != $categoryObject->id)
								{
									$segments[] = $query['id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
								}
								unset($query['id']);
							}

							$addCategoryToSegment = false;
						}
					}
				}

				$segments[] = 'tree';

				if ($addCategoryToSegment)
				{
					if (isset($query['id']))
					{
						$categoryObject = JUDownloadHelper::getCategoryById($query['id']);
						$segments[]     = $query['id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
					}
				}

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['id']))
				{
					unset($query['id']);
				}

				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoFormat($query, $params, $segments);

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['id']);
			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'usercomments')
		{
			if (!$hasActiveMenu)
			{
				if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
				{
					
					$sefRootCategory = 'root';
					$segments[]      = JApplication::stringURLSafe($sefRootCategory);
				}

				if (isset($query['id']))
				{
					$user       = JFactory::getUser($query['id']);
					$userAlias  = JApplication::stringURLSafe($user->username);
					$segments[] = $query['id'] . ':' . $userAlias;
					unset($query['id']);
				}
				$segments[] = JApplication::stringURLSafe('comments');

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['id']))
				{
					unset($query['id']);
				}
				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'userdocuments')
		{
			if (!$hasActiveMenu)
			{
				if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
				{
					
					$sefRootCategory = 'root';
					$segments[]      = JApplication::stringURLSafe($sefRootCategory);
				}

				if (isset($query['id']))
				{
					$user       = JFactory::getUser($query['id']);
					$userAlias  = JApplication::stringURLSafe($user->username);
					$segments[] = $query['id'] . ':' . $userAlias;
					unset($query['id']);
				}

				$segments[] = JApplication::stringURLSafe('documents');

				if (isset($query['filter']))
				{
					if ($query['filter'] == 'pending')
					{
						$segments[] = JApplication::stringURLSafe('pending');
					}
					elseif ($query['filter'] == 'unpublished')
					{
						$segments[] = JApplication::stringURLSafe('unpublished');
					}
					else
					{
						$segments[] = JApplication::stringURLSafe('published');
					}
					unset($query['filter']);
				}

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if (isset($query['id']))
				{
					unset($query['id']);
				}

				if (isset($query['filter']))
				{
					unset($query['filter']);
				}

				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoFormat($query, $params, $segments);

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['view']);
		}

		
		if (isset($query['view']) && $query['view'] == 'usersubscriptions' && isset($query['id']))
		{
			if (!$hasActiveMenu)
			{
				if (isset($query['Itemid']) && $query['Itemid'] == $homeItemId)
				{
					
					$sefRootCategory = 'root';
					$segments[]      = JApplication::stringURLSafe($sefRootCategory);
				}

				$user       = JFactory::getUser($query['id']);
				$userAlias  = JApplication::stringURLSafe($user->username);
				$segments[] = $query['id'] . ':' . $userAlias;
				$segments[] = JApplication::stringURLSafe('user-subscriptions');

				JUDownloadHelperRoute::seoLayout($query, $segments, $params);
			}
			else
			{
				if ($query['layout'])
				{
					unset($query['layout']);
				}
			}

			JUDownloadHelperRoute::seoPagination($query, $params, $segments);

			unset($query['id']);
			unset($query['view']);
		}

		

		
		if (isset($query['task']) && $query['task'] == 'form.add')
		{
			if (isset($query['cat_id']))
			{
				$categoryObject = JUDownloadHelper::getCategoryById($query['cat_id']);
				if (is_object($categoryObject))
				{
					$segments[] = $query['cat_id'] . ':' . (isset($categoryObject->alias) ? $categoryObject->alias : '');
					unset($query['cat_id']);
				}
			}

			$segments[] = JApplication::stringURLSafe('add');
			unset($query['task']);
		}

		
		if (isset($query['task']) && $query['task'] == 'form.edit' && isset($query['id']))
		{
			$segments   = JUDownloadHelperRoute::getDocumentSegment($query['id'], $query, $params);
			$segments[] = JApplication::stringURLSafe('edit');

			if (isset($query['layout']))
			{
				unset($query['layout']);
			}

			unset($query['id']);
			unset($query['task']);
		}

		
		if (isset($query['task']) && $query['task'] == 'forms.delete' && isset($query['id']))
		{
			$segments   = JUDownloadHelperRoute::getDocumentSegment($query['id'], $query, $params);
			$segments[] = JApplication::stringURLSafe('delete');

			unset($query['id']);
			unset($query['task']);
		}

		if (isset($query['task']) && $query['task'] == 'forms.checkin' && isset($query['id']))
		{
			$segments   = JUDownloadHelperRoute::getDocumentSegment($query['id'], $query, $params);
			$segments[] = JApplication::stringURLSafe('checkin');

			unset($query['id']);
			unset($query['task']);
		}

		
		if (isset($query['task']) && $query['task'] == 'modpendingdocument.edit' && isset($query['id']))
		{
			$segments = JUDownloadHelperRoute::getDocumentSegment($query['id'], $query, $params);

			if (isset($query['approve']))
			{
				$segments[] = JApplication::stringURLSafe('approve');
				unset($query['approve']);
			}

			if (isset($query['layout']))
			{
				unset($query['layout']);
			}

			unset($query['id']);
			unset($query['task']);
		}

		
		if (isset($query['task']) && $query['task'] == 'forms.unpublish' && isset($query['id']))
		{
			$segments   = JUDownloadHelperRoute::getDocumentSegment($query['id'], $query, $params);
			$segments[] = JApplication::stringURLSafe('unpublish');

			unset($query['id']);
			unset($query['task']);
		}

		
		if (isset($query['task']) && $query['task'] == 'forms.publish' && isset($query['id']))
		{
			$segments   = JUDownloadHelperRoute::getDocumentSegment($query['id'], $query, $params);
			$segments[] = JApplication::stringURLSafe('publish');

			unset($query['id']);
			unset($query['task']);
		}

		
		if (isset($query['task']) && $query['task'] == 'subscribe.save' && isset($query['doc_id']) && !isset($query['comment_id']))
		{
			$segments   = JUDownloadHelperRoute::getDocumentSegment($query['doc_id'], $query, $params);
			$segments[] = JApplication::stringURLSafe('subscribe');

			unset($query['doc_id']);
			unset($query['task']);
		}

		
		if (isset($query['task']) && $query['task'] == 'download.download' && isset($query['doc_id']))
		{
			$segments = JUDownloadHelperRoute::getDocumentSegment($query['doc_id'], $query, $params);

			if (isset($query['version']))
			{
				$segments[] = $query['version'];
				unset($query['version']);
			}
			else
			{
				$segments[] = JApplication::stringURLSafe('latest');
			}

			$segments[] = JApplication::stringURLSafe('download');

			unset($query['doc_id']);
			unset($query['task']);
		}

		if (isset($query['task']) && $query['task'] == 'download.download' && isset($query['file_id']))
		{
			$segments[] = JApplication::stringURLSafe('file');
			$fileObject = JUDownloadFrontHelper::getFileObject($query['file_id']);
			$fileAlias  = JApplication::stringURLSafe($fileObject->rename);
			$segments[] = $query['file_id'] . ':' . $fileAlias;

			if (isset($query['version']))
			{
				$segments[] = $query['version'];
				unset($query['version']);
			}
			else
			{
				$segments[] = JApplication::stringURLSafe('latest');
			}

			$segments[] = JApplication::stringURLSafe('download');

			unset($query['file_id']);
			unset($query['task']);
		}

		
		if (isset($query['task']) && $query['task'] == 'document.redirecturl')
		{
			if (isset($query['doc_id']))
			{
				$segments = JUDownloadHelperRoute::getDocumentSegment($query['doc_id'], $query, $params);
				unset($query['doc_id']);
			}

			if (isset($query['field_id']))
			{
				$fieldObject = JUDownloadFrontHelperField::getFieldById($query['field_id']);
				$segments[]  = $query['field_id'] . ':' . (isset($fieldObject->alias) ? $fieldObject->alias : '');
				unset($query['field_id']);
			}

			$segments[] = JApplication::stringURLSafe('redirect-url');

			unset($query['task']);
		}

		

		
		if (isset($query['task']) && $query['task'] == 'modcomment.edit' && isset($query['id']))
		{
			$segments[] = JApplication::stringURLSafe('comment');

			$commentObject = JUDownloadFrontHelperComment::getCommentObject($query['id']);

			$commentAlias = JApplication::stringURLSafe($commentObject->title);
			$segments[]   = $query['id'] . ':' . $commentAlias;
			unset($query['id']);

			$segments[] = JApplication::stringURLSafe('edit');

			if (isset($query['layout']))
			{
				unset($query['layout']);
			}

			unset($query['task']);
		}

		
		if (isset($query['task']) && $query['task'] == 'modpendingcomment.edit' && isset($query['id']))
		{
			$segments[] = JApplication::stringURLSafe('comment');

			$commentObject = JUDownloadFrontHelperComment::getCommentObject($query['id']);

			$commentAlias = JApplication::stringURLSafe($commentObject->title);
			$segments[]   = $query['id'] . ':' . $commentAlias;
			unset($query['id']);

			$segments[] = JApplication::stringURLSafe('approve');

			if (isset($query['layout']))
			{
				unset($query['layout']);
			}

			unset($query['id']);
			unset($query['task']);
		}

		
		if (isset($query['task']) && $query['task'] == 'subscribe.save' && isset($query['comment_id']))
		{
			$segments[] = JApplication::stringURLSafe('comment');

			$commentObject = JUDownloadFrontHelperComment::getCommentObject($query['comment_id']);

			$commentAlias = JApplication::stringURLSafe($commentObject->title);
			$segments[]   = $query['comment_id'] . ':' . $commentAlias;
			unset($query['comment_id']);

			$segments[] = JApplication::stringURLSafe('subscribe');
			unset($query['task']);
		}

		
		if (isset($query['task']) && $query['task'] == 'subscribe.remove' && isset($query['sub_id']))
		{
			$subscriptionObject = JUDownloadFrontHelper::getSubscriptionObject($query['sub_id']);
			if ($subscriptionObject->type == 'document')
			{
				$segments = JUDownloadHelperRoute::getDocumentSegment($subscriptionObject->item_id, $query, $params);

				$segments[] = JApplication::stringURLSafe('unsubscribe');

				$segments[] = $query['sub_id'];

				if ($query['code'])
				{
					$segments[] = $query['code'];
					unset($query['code']);
				}

				unset($query['doc_id']);
				unset($query['task']);

				unset($query['sub_id']);
				unset($query['task']);
			}
			elseif ($subscriptionObject->type == 'comment')
			{
				$segments[] = JApplication::stringURLSafe('comment');

				$commentObject = JUDownloadFrontHelperComment::getCommentObject($subscriptionObject->item_id);
				if (is_object($commentObject))
				{
					$commentAlias = JApplication::stringURLSafe($commentObject->title);
					$segments[]   = $commentObject->id . ':' . $commentAlias;
				}

				$segments[] = JApplication::stringURLSafe('unsubscribe');

				$segments[] = $query['sub_id'];

				if ($query['code'])
				{
					$segments[] = $query['code'];
					unset($query['code']);
				}

				unset($query['sub_id']);
				unset($query['task']);
			}
		}

		
		if (isset($query['task']) && $query['task'] == 'document.deleteComment' && isset($query['comment_id']))
		{
			$segments[] = JApplication::stringURLSafe('comment');

			$commentObject = JUDownloadFrontHelperComment::getCommentObject($query['comment_id']);

			$commentAlias = JApplication::stringURLSafe($commentObject->title);
			$segments[]   = $query['comment_id'] . ':' . $commentAlias;
			unset($query['comment_id']);

			$segments[] = JApplication::stringURLSafe('delete');
			unset($query['task']);
		}

		

		
		if (isset($query['task']) && $query['task'] == 'collection.add')
		{
			$segments[] = JApplication::stringURLSafe('collection');
			$segments[] = JApplication::stringURLSafe('add');

			unset($query['task']);
		}

		
		if (isset($query['task']) && $query['task'] == 'collection.edit' && isset($query['id']))
		{
			if (isset($query['user_id']))
			{
				$user       = JFactory::getUser($query['user_id']);
				$userAlias  = JApplication::stringURLSafe($user->username);
				$segments[] = $query['user_id'] . ':' . $userAlias;
				unset($query['user_id']);
			}

			$segments[] = JApplication::stringURLSafe('collection');

			$collectionObject = JUDownloadFrontHelper::getCollectionById($query['id']);

			$segments[] = $query['id'] . ':' . (isset($collectionObject->alias) ? $collectionObject->alias : '');
			unset($query['id']);

			$segments[] = JApplication::stringURLSafe('edit');

			if (isset($query['layout']))
			{
				unset($query['layout']);
			}

			unset($query['task']);
		}

		
		if (isset($query['task']) && $query['task'] == 'collections.delete' && isset($query['cid']))
		{
			$segments[] = JApplication::stringURLSafe('collection');

			$collectionObject = JUDownloadFrontHelper::getCollectionById($query['cid']);

			$segments[] = $query['cid'] . ':' . (isset($collectionObject->alias) ? $collectionObject->alias : '');
			unset($query['cid']);

			$segments[] = JApplication::stringURLSafe('delete');

			unset($query['task']);
		}

		

		
		if (isset($query['task']) && $query['task'] == 'usersubscriptions.unsubscribe' && isset($query['sub_id']))
		{
			$segments[] = JApplication::stringURLSafe('user-subscriptions');
			$segments[] = $query['sub_id'];
			$segments[] = JApplication::stringURLSafe('unsubscribe');

			unset($query['sub_id']);
			unset($query['task']);
		}

		
		if (isset($query['task']) && $query['task'] == 'rawdata')
		{
			if (isset($query['doc_id']))
			{
				$segments = JUDownloadHelperRoute::getDocumentSegment($query['doc_id'], $query, $params);
				unset($query['doc_id']);
			}

			if (isset($query['field_id']))
			{
				$fieldObject = JUDownloadFrontHelperField::getFieldById($query['field_id']);

				$segments[] = $query['field_id'] . ':' . (isset($fieldObject->alias) ? $fieldObject->alias : '');
				unset($query['field_id']);
			}

			$segments[] = JApplication::stringURLSafe('raw-data');
			unset($query['task']);
		}

		
		if (isset($query['task']) && $query['task'] == 'subscribe.activate')
		{
			$segments[] = JApplication::stringURLSafe('activate-subscription');

			if ($query['id'])
			{
				$segments[] = $query['id'];
			}

			if (isset($query['code']))
			{
				$segments[] = $query['code'];
			}

			unset($query['task']);
		}

		
		if (isset($query['task']) && $query['task'] == 'email.downloadattachment')
		{
			$segments[] = JApplication::stringURLSafe('email');
			$segments[] = JApplication::stringURLSafe('download-attachment');

			if (isset($query['mail_id']))
			{
				$segments[] = $query['mail_id'];
				unset($query['mail_id']);
			}

			if (isset($query['file']))
			{
				$segments[] = $query['file'];
				unset($query['file']);
			}

			if (isset($query['code']))
			{
				$segments[] = $query['code'];
				unset($query['code']);
			}

			unset($query['task']);
		}

		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}

		if (isset($query['limit']))
		{
			unset($query['limit']);
		}

		return $segments;
	}

	
	public function parse(&$segments)
	{
		$total = count($segments);
		$vars  = array();

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = preg_replace('/:/', '-', $segments[$i], 1);
		}

		$params     = JUDownloadHelper::getParams();
		$app        = JFactory::getApplication('site');
		$menu       = $app->getMenu();
		$activeMenu = $menu->getActive();

		$indexLastSegment = $total - 1;
		$endSegment       = end($segments);

		
		$searchViewApproveComment = array_search(JApplication::stringURLSafe('mod-comment'), $segments);
		if ($searchViewApproveComment !== false)
		{
			$vars['view'] = 'modcomment';
			if (isset($segments[$searchViewApproveComment + 1]))
			{
				$vars['id'] = (int) $segments[$searchViewApproveComment + 1];
			}

			if (isset($segments[$searchViewApproveComment + 2]))
			{
				if ($segments[$searchViewApproveComment + 2] == JApplication::stringURLSafe('approve'))
				{
					$vars['approve'] = 1;
				}
			}

			$previousIndexSegment = $total - 1;

			if (isset($segments[$previousIndexSegment]))
			{
				$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
				if ($isLayout)
				{
					$previousIndexSegment -= 1;
				}
			}

			return $vars;
		}

		
		if (isset($segments[0]) && $segments[0] == JApplication::stringURLSafe('comment'))
		{
			if (isset($segments[2]))
			{
				switch ($segments[2])
				{
					case JApplication::stringURLSafe('edit') :
						$vars['task'] = 'modcomment.edit';
						if (isset($segments[1]))
						{
							$vars['id'] = (int) $segments[1];
						}
						break;
					case JApplication::stringURLSafe('approve') :
						$vars['task'] = 'modpendingcomment.edit';
						if (isset($segments[1]))
						{
							$vars['id'] = (int) $segments[1];
						}
						break;
					case JApplication::stringURLSafe('subscribe') :
						$vars['task'] = 'subscribe.save';
						if (isset($segments[1]))
						{
							$vars['comment_id'] = (int) $segments[1];
						}
						break;
					case JApplication::stringURLSafe('unsubscribe') :
						$vars['task'] = 'subscribe.remove';
						if (isset($segments[3]))
						{
							$vars['sub_id'] = (int) $segments[3];
						}
						if (isset($segments[4]))
						{
							$vars['code'] = $segments[4];
						}
						break;
					case JApplication::stringURLSafe('delete') :
						$vars['task'] = 'document.deleteComment';
						if (isset($segments[1]))
						{
							$vars['comment_id'] = (int) $segments[1];
						}
						break;
					default :
						break;
				}

				if (isset($vars['task']))
				{
					return $vars;
				}
			}
		}

		
		$searchViewReportComment = array_search(JApplication::stringURLSafe('comment'), $segments);
		if ($searchViewReportComment !== false)
		{
			
			$validArrayIndex = array(0, 1);
			if (in_array($searchViewReportComment, $validArrayIndex))
			{
				if (isset($segments[$searchViewReportComment + 2]))
				{
					if ($segments[$searchViewReportComment + 2] == JApplication::stringURLSafe('report'))
					{
						$vars['view'] = 'report';
						if (isset($segments[$searchViewReportComment + 1]))
						{
							$vars['comment_id'] = (int) $segments[$searchViewReportComment + 1];
						}

						$previousIndexSegment = $total - 1;

						if (isset($segments[$previousIndexSegment]))
						{
							$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
							if ($isLayout)
							{
								$previousIndexSegment -= 1;
							}
						}

						return $vars;
					}
				}
			}
		}

		
		$searchViewModeratorPermission = array_search(JApplication::stringURLSafe('mod-permission'), $segments);
		if ($searchViewModeratorPermission !== false)
		{
			
			$validArrayIndex = array(0, 1);
			if (in_array($searchViewModeratorPermission, $validArrayIndex))
			{
				$vars['view'] = 'modpermission';
				if (isset($segments[$searchViewModeratorPermission + 1]))
				{
					$vars['id'] = (int) $segments[$searchViewModeratorPermission + 1];
				}

				$previousIndexSegment = $total - 1;

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		if (isset($segments[0]) && $segments[0] == JApplication::stringURLSafe('user-subscriptions'))
		{
			if (isset($segments[2]))
			{
				if ($segments[2] == JApplication::stringURLSafe('unsubscribe'))
				{
					$vars['task'] = 'usersubscriptions.unsubscribe';
					if (isset($segments[1]))
					{
						$vars['sub_id'] = (int) $segments[1];
					}

					return $vars;
				}
			}
		}

		
		if (isset($segments[0]) && $segments[0] == JApplication::stringURLSafe('email'))
		{
			if (isset($segments[1]) && $segments[1] == JApplication::stringURLSafe('download-attachment'))
			{
				$vars['task'] = 'email.downloadattachment';

				if (isset($segments[2]))
				{
					$vars['mail_id'] = (int) $segments[2];
				}

				if (isset($segments[3]))
				{
					$vars['file'] = $segments[3];
				}

				if (isset($segments[4]))
				{
					$vars['code'] = $segments[4];
				}

			}

			return $vars;
		}

		
		$searchViewModeratorPermissions = array_search(JApplication::stringURLSafe('mod-permissions'), $segments);
		if ($searchViewModeratorPermissions !== false)
		{
			
			$validArrayIndex = array(0, 1);
			if (in_array($searchViewModeratorPermissions, $validArrayIndex))
			{
				$vars['view'] = 'modpermissions';

				$previousIndexSegment = $total - 1;

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		$searchViewProfile = array_search(JApplication::stringURLSafe('profile'), $segments);
		if ($searchViewProfile !== false)
		{
			
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchViewProfile, $validArrayIndex))
			{
				$vars['view'] = 'profile';

				$previousIndexSegment = $total - 1;

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		$searchViewModeratorPendingDocuments = array_search(JApplication::stringURLSafe('mod-pending-documents'), $segments);
		if ($searchViewModeratorPendingDocuments !== false)
		{
			
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchViewModeratorPendingDocuments, $validArrayIndex))
			{
				$vars['view'] = 'modpendingdocuments';

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		$searchViewModeratorPendingComments = array_search(JApplication::stringURLSafe('mod-pending-comments'), $segments);
		if ($searchViewModeratorPendingComments !== false)
		{
			
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchViewModeratorPendingComments, $validArrayIndex))
			{
				$vars['view'] = 'modpendingcomments';

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		$searchViewModeratorComments = array_search(JApplication::stringURLSafe('mod-comments'), $segments);
		if ($searchViewModeratorComments !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchViewModeratorComments, $validArrayIndex))
			{
				$vars['view'] = 'modcomments';

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		$searchViewModeratorDocuments = array_search(JApplication::stringURLSafe('mod-documents'), $segments);
		if ($searchViewModeratorDocuments !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchViewModeratorDocuments, $validArrayIndex))
			{
				$vars['view'] = 'moddocuments';

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		if (isset($segments[0]) && $segments[0] == JApplication::stringURLSafe('subscribe'))
		{
			if (isset($segments[1]) && $segments[1] == JApplication::stringURLSafe('activate'))
			{
				$vars['task'] = 'subscribe.activate';

				if (isset($segments[2]))
				{
					$vars['code'] = $segments[2];
				}

				if (isset($segments[3]))
				{
					$vars['id'] = (int) $segments[3];
				}

				if (isset($segments[4]))
				{
					$vars['doc_id'] = (int) $segments[4];
				}

				return $vars;
			}
		}

		
		$searchViewSearch = array_search(JApplication::stringURLSafe('search'), $segments);
		if ($searchViewSearch !== false)
		{
			$validArrayIndex = array(0, 1, 2, 3);
			if (in_array($searchViewSearch, $validArrayIndex))
			{
				$vars['view'] = 'search';

				if (isset($segments[$searchViewSearch - 1]))
				{
					if ($segments[$searchViewSearch - 1] == JApplication::stringURLSafe('all'))
					{
						$vars['sub_cat'] = 1;
						if (isset($segments[$searchViewSearch - 2]))
						{
							$vars['cat_id'] = (int) $segments[$searchViewSearch - 2];
						}
					}
					else
					{
						$vars['cat_id'] = (int) $segments[$searchViewSearch - 1];
					}
				}

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				if (isset($segments[$previousIndexSegment]) && $previousIndexSegment > $searchViewSearch)
				{
					$vars['searchword'] = $segments[$previousIndexSegment];
					$previousIndexSegment -= 1;
				}

				return $vars;
			}
		}

		
		$searchViewCategories = array_search(JApplication::stringURLSafe('categories'), $segments);
		if ($searchViewCategories !== false)
		{
			$validArrayIndex = array(0, 1);
			if (in_array($searchViewCategories, $validArrayIndex))
			{
				$vars['view'] = 'categories';
				if (isset($segments[$searchViewCategories + 1]))
				{
					$vars['id'] = (int) $segments[$searchViewCategories + 1];
				}

				if (isset($segments[$searchViewCategories + 2]))
				{
					JUDownloadHelperRoute::parseLayout($segments[$searchViewCategories + 2], $vars, $params);
				}

				return $vars;
			}
		}

		
		$searchViewAdvancedSearch = array_search(JApplication::stringURLSafe('advanced-search'), $segments);
		if ($searchViewAdvancedSearch !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchViewAdvancedSearch, $validArrayIndex))
			{
				$vars['view'] = 'advsearch';

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		$searchViewCommentTree = array_search(JApplication::stringURLSafe('comment-tree'), $segments);
		if ($searchViewCommentTree !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchViewCommentTree, $validArrayIndex))
			{
				$vars['view'] = 'commenttree';

				if (isset($segments[$searchViewCommentTree + 1]))
				{
					$vars['id'] = (int) $segments[$searchViewCommentTree + 1];
				}

				$previousIndexSegment = $total - 1;

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		$searchViewDocuments = array_search(JApplication::stringURLSafe('modal-documents'), $segments);
		if ($searchViewDocuments !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchViewDocuments, $validArrayIndex))
			{
				$vars['view'] = 'documents';

				if (isset($segments[$searchViewDocuments + 1]))
				{
					$vars['tmpl'] = $segments[$searchViewDocuments + 1];
				}

				if (isset($segments[$searchViewDocuments + 2]))
				{
					$vars['function'] = $segments[$searchViewDocuments + 2];
				}

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		$searchViewDownloadError = array_search(JApplication::stringURLSafe('error-download'), $segments);
		if ($searchViewDownloadError !== false)
		{
			$validArrayIndex = array(0, 1);
			if (in_array($searchViewDownloadError, $validArrayIndex))
			{
				$vars['view'] = 'downloaderror';

				$previousIndexSegment = $total - 1;

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				if (isset($segments[$previousIndexSegment]) && $previousIndexSegment > $searchViewDownloadError)
				{
					$vars['return'] = $segments[$previousIndexSegment];
				}

				return $vars;
			}
		}

		
		$searchViewLicense = array_search(JApplication::stringURLSafe('license'), $segments);
		if ($searchViewLicense !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchViewLicense, $validArrayIndex))
			{
				$vars['view'] = 'license';
				if (isset($segments[$searchViewLicense + 1]))
				{
					$vars['id'] = (int) $segments[$searchViewLicense + 1];
				}

				$previousIndexSegment = $total - 1;

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		$searchViewMaintenance = array_search(JApplication::stringURLSafe('maintenance'), $segments);
		if ($searchViewMaintenance !== false)
		{
			$validArrayIndex = array(0, 1);
			if (in_array($searchViewMaintenance, $validArrayIndex))
			{
				$vars['view'] = 'maintenance';

				$previousIndexSegment = $total - 1;

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		$searchViewSearchBy = array_search(JApplication::stringURLSafe('search-by'), $segments);
		if ($searchViewSearchBy !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchViewSearchBy, $validArrayIndex))
			{
				$vars['view'] = 'searchby';

				if (isset($segments[$searchViewSearchBy + 1]))
				{
					$vars['field_id'] = (int) $segments[$searchViewSearchBy + 1];
				}

				if (isset($segments[$searchViewSearchBy + 2]))
				{
					$vars['value'] = $segments[$searchViewSearchBy + 2];
				}

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		$searchViewTag = array_search(JApplication::stringURLSafe('tag'), $segments);
		if ($searchViewTag !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchViewTag, $validArrayIndex))
			{
				$vars['view'] = 'tag';

				if (isset($segments[$searchViewTag + 1]))
				{
					$vars['id'] = (int) $segments[$searchViewTag + 1];
				}

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					if ($segments[$previousIndexSegment] == JApplication::stringURLSafe('rss'))
					{
						$vars['format'] = 'feed';
						$previousIndexSegment -= 1;
					}
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		$searchViewTags = array_search(JApplication::stringURLSafe('tags'), $segments);
		if ($searchViewTags !== false)
		{
			$validArrayIndex = array(0, 1);
			if (in_array($searchViewTags, $validArrayIndex))
			{
				$vars['view'] = 'tags';

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		$searchViewTopComments = array_search(JApplication::stringURLSafe('top-comments'), $segments);
		if ($searchViewTopComments !== false)
		{
			$validArrayIndex = array(0, 1);
			if (in_array($searchViewTopComments, $validArrayIndex))
			{
				$vars['view'] = 'topcomments';

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		$searchViewTree = array_search(JApplication::stringURLSafe('tree'), $segments);
		if ($searchViewTree !== false)
		{
			$validArrayIndex = array(0, 1);
			if (in_array($searchViewTree, $validArrayIndex))
			{
				$vars['view']         = 'tree';
				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					if ($segments[$previousIndexSegment] == JApplication::stringURLSafe('rss'))
					{
						$vars['format'] = 'feed';
						$previousIndexSegment -= 1;
					}
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				if (isset($segments[$previousIndexSegment]))
				{
					if ($segments[$previousIndexSegment] == JApplication::stringURLSafe('root'))
					{
						$rootCategory = JUDownloadFrontHelperCategory::getRootCategory();
						$vars['id']   = $rootCategory->id;
					}
					else
					{
						$vars['id'] = (int) $segments[$previousIndexSegment];
					}
					$previousIndexSegment -= 1;
				}
				else
				{
					if ($activeMenu && isset($activeMenu->query) && isset($activeMenu->query['view']) && isset($activeMenu->query['id']) &&
						$activeMenu->query['view'] == 'tree'
					)
					{
						$vars['id'] = $activeMenu->query['id'];
					}
				}

				return $vars;
			}
		}

		
		$orderTypeTopDocuments = array();
		
		$orderTypeTopDocuments[] = JApplication::stringURLSafe('latest-documents');
		
		$orderTypeTopDocuments[] = JApplication::stringURLSafe('top-featured-documents');
		
		$orderTypeTopDocuments[] = JApplication::stringURLSafe('recent-modified-documents');
		
		$orderTypeTopDocuments[] = JApplication::stringURLSafe('recent-updated-documents');
		
		$orderTypeTopDocuments[] = JApplication::stringURLSafe('popular-documents');
		
		$orderTypeTopDocuments[] = JApplication::stringURLSafe('most-downloaded-documents');
		
		$orderTypeTopDocuments[] = JApplication::stringURLSafe('most-rated-documents');
		
		$orderTypeTopDocuments[] = JApplication::stringURLSafe('top-rated-documents');
		
		$orderTypeTopDocuments[] = JApplication::stringURLSafe('latest-rated-documents');
		
		$orderTypeTopDocuments[] = JApplication::stringURLSafe('most-commented-documents');
		
		$orderTypeTopDocuments[] = JApplication::stringURLSafe('latest-commented-documents');
		
		$orderTypeTopDocuments[] = JApplication::stringURLSafe('recent-viewed-documents');
		
		$orderTypeTopDocuments[] = JApplication::stringURLSafe('alpha-ordered-documents');
		
		$orderTypeTopDocuments[] = JApplication::stringURLSafe('random-documents');
		
		$orderTypeTopDocuments[] = JApplication::stringURLSafe('random-fast-documents');
		
		$orderTypeTopDocuments[] = JApplication::stringURLSafe('random-featured-documents');

		if (!empty($orderTypeTopDocuments))
		{
			foreach ($orderTypeTopDocuments as $orderTypeTopDocumentItem)
			{
				$searchViewTopDocuments = array_search($orderTypeTopDocumentItem, $segments);
				if ($searchViewTopDocuments !== false)
				{
					break;
				}
			}

			if ($searchViewTopDocuments !== false)
			{
				$validArrayIndex = array(0, 1, 2);
				if (in_array($searchViewTopDocuments, $validArrayIndex))
				{
					$vars['view'] = 'topdocuments';

					switch ($segments[$searchViewTopDocuments])
					{
						case JApplication::stringURLSafe('latest-documents'):
							$vars['ordertype'] = 'new';
							break;
						case JApplication::stringURLSafe('top-featured-documents'):
							$vars['ordertype'] = 'featured';
							break;
						case JApplication::stringURLSafe('recent-modified-documents'):
							$vars['ordertype'] = 'recent_modified';
							break;
						case JApplication::stringURLSafe('recent-updated-documents'):
							$vars['ordertype'] = 'recent_updated';
							break;
						case JApplication::stringURLSafe('popular-documents'):
							$vars['ordertype'] = 'popular';
							break;
						case JApplication::stringURLSafe('most-downloaded-documents'):
							$vars['ordertype'] = 'most_downloaded';
							break;
						case JApplication::stringURLSafe('most-rated-documents'):
							$vars['ordertype'] = 'most_rated';
							break;
						case JApplication::stringURLSafe('top-rated-documents'):
							$vars['ordertype'] = 'top_rated';
							break;
						case JApplication::stringURLSafe('latest-rated-documents'):
							$vars['ordertype'] = 'latest_rated';
							break;
						case JApplication::stringURLSafe('most-commented-documents'):
							$vars['ordertype'] = 'most_commented';
							break;
						case JApplication::stringURLSafe('latest-commented-documents'):
							$vars['ordertype'] = 'latest_commented';
							break;
						case JApplication::stringURLSafe('recent-viewed-documents'):
							$vars['ordertype'] = 'recently_viewed';
							break;
						case JApplication::stringURLSafe('alpha-ordered-documents'):
							$vars['ordertype'] = 'alpha_ordered';
							break;
						case JApplication::stringURLSafe('random-documents'):
							$vars['ordertype'] = 'random';
							break;
						case JApplication::stringURLSafe('random-fast-documents'):
							$vars['ordertype'] = 'random_fast';
							break;
						case JApplication::stringURLSafe('random-featured-documents'):
							$vars['ordertype'] = 'random_featured';
							break;
						default:
							$vars['ordertype'] = 'new';
							break;
					}

					if (isset($segments[$searchViewTopDocuments - 1]))
					{
						if ($segments[$searchViewTopDocuments - 1] == JApplication::stringURLSafe('root'))
						{
							$rootCategory = JUDownloadFrontHelperCategory::getRootCategory();
							$vars['id']   = $rootCategory->id;
						}
						else
						{
							$vars['id'] = (int) $segments[$searchViewTopDocuments - 1];
						}
					}
					else
					{
						if ($activeMenu && isset($activeMenu->query) && isset($activeMenu->query['view']) && isset($activeMenu->query['id']) &&
							$activeMenu->query['view'] == 'tree'
						)
						{
							$vars['id'] = $activeMenu->query['id'];
						}
					}

					if (isset($segments[$searchViewTopDocuments + 1]))
					{
						if ($segments[$searchViewTopDocuments + 1] == JApplication::stringURLSafe('all'))
						{
							$vars['all'] = 1;
						}
					}

					$previousIndexSegment = $total - 1;

					$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
					if ($isPaged)
					{
						$previousIndexSegment -= 1;
					}

					if (isset($segments[$previousIndexSegment]))
					{
						if ($segments[$previousIndexSegment] == JApplication::stringURLSafe('rss'))
						{
							$vars['format'] = 'feed';
							$previousIndexSegment -= 1;
						}
					}

					if (isset($segments[$previousIndexSegment]))
					{
						$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
						if ($isLayout)
						{
							$previousIndexSegment -= 1;
						}
					}

					return $vars;
				}
			}
		}

		
		$searchSefRedirectUrl = array_search(JApplication::stringURLSafe('redirect-url'), $segments);
		if ($searchSefRedirectUrl !== false)
		{
			$vars['task'] = 'document.redirecturl';
			if (isset($segments[$searchSefRedirectUrl - 1]))
			{
				$vars['field_id'] = (int) $segments[$searchSefRedirectUrl - 1];
			}

			if (isset($segments[$searchSefRedirectUrl - 2]))
			{
				$vars['doc_id'] = (int) $segments[$searchSefRedirectUrl - 2];
			}

			return $vars;
		}

		
		$searchSefTaskRawData = array_search(JApplication::stringURLSafe('raw-data'), $segments);
		if ($searchSefTaskRawData !== false)
		{
			$vars['task'] = 'rawdata';

			if (isset($segments[$searchSefTaskRawData - 1]))
			{
				$vars['field_id'] = (int) $segments[$searchSefTaskRawData - 1];
			}

			if (isset($segments[$searchSefTaskRawData - 2]))
			{
				$vars['doc_id'] = (int) $segments[$searchSefTaskRawData - 2];
			}

			return $vars;
		}

		$searchSefCollection = array_search(JApplication::stringURLSafe('collection'), $segments);
		if ($searchSefCollection !== false)
		{
			if (isset($segments[$searchSefCollection + 1]))
			{
				if ($segments[$searchSefCollection + 1] == JApplication::stringURLSafe('add'))
				{
					$vars['task'] = 'collection.add';

					return $vars;
				}
			}
		}

		$searchSefCollection = array_search(JApplication::stringURLSafe('collection'), $segments);
		if ($searchSefCollection !== false)
		{
			if (isset($segments[$searchSefCollection + 2]))
			{
				if ($segments[$searchSefCollection + 2] == JApplication::stringURLSafe('edit'))
				{
					if (isset($segments[$searchSefCollection - 1]))
					{
						$vars['user_id'] = (int) $segments[$searchSefCollection - 1];
					}
					$vars['id']   = (int) $segments[$searchSefCollection + 1];
					$vars['task'] = 'collection.edit';

					return $vars;
				}
			}
		}

		$searchSefCollection = array_search(JApplication::stringURLSafe('collection'), $segments);
		if ($searchSefCollection !== false)
		{
			if (isset($segments[$searchSefCollection + 2]))
			{
				if ($segments[$searchSefCollection + 2] == JApplication::stringURLSafe('delete'))
				{
					$vars['cid']  = (int) $segments[$searchSefCollection + 1];
					$vars['task'] = 'collections.delete';

					return $vars;
				}
			}
		}

		$searchSefCollection = array_search(JApplication::stringURLSafe('collection'), $segments);
		if ($searchSefCollection !== false)
		{
			if (isset($segments[$searchSefCollection + 1]))
			{
				if ($segments[$searchSefCollection + 1] == JApplication::stringURLSafe('new-collection'))
				{
					$vars['id']   = 0;
					$vars['view'] = 'collection';
					JUDownloadHelperRoute::parseLayout($segments[$searchSefCollection + 2], $vars, $params);

					return $vars;
				}
			}
		}

		$searchSefCollection = array_search(JApplication::stringURLSafe('collection'), $segments);
		if ($searchSefCollection !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchSefCollection, $validArrayIndex))
			{
				$vars['view'] = 'collection';
				if (isset($segments[$searchSefCollection - 1]))
				{
					if ($segments[$searchSefCollection - 1] != 'root')
					{
						$vars['user_id'] = (int) $segments[$searchSefCollection - 1];
					}
				}

				if (isset($segments[$searchSefCollection + 1]))
				{
					$vars['id'] = (int) $segments[$searchSefCollection + 1];
				}

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					if ($segments[$previousIndexSegment] == JApplication::stringURLSafe('rss'))
					{
						$vars['format'] = 'feed';
						$previousIndexSegment -= 1;
					}
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		$searchSefReportDocument = array_search(JApplication::stringURLSafe('report'), $segments);
		if ($searchSefReportDocument !== false)
		{
			$vars['view'] = 'report';
			if (isset($segments[$searchSefReportDocument - 1]))
			{
				$vars['doc_id'] = (int) $segments[$searchSefReportDocument - 1];
			}

			$previousIndexSegment = $total - 1;

			if (isset($segments[$previousIndexSegment]))
			{
				$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
				if ($isLayout)
				{
					$previousIndexSegment -= 1;
				}
			}

			return $vars;
		}

		
		$searchSefSubscribeDocumentForGuest = array_search(JApplication::stringURLSafe('guest-subscribe'), $segments);
		if ($searchSefSubscribeDocumentForGuest !== false)
		{
			$vars['view'] = 'subscribe';
			if (isset($segments[$searchSefSubscribeDocumentForGuest - 1]))
			{
				$vars['doc_id'] = (int) $segments[$searchSefSubscribeDocumentForGuest - 1];
			}

			$previousIndexSegment = $total - 1;

			if (isset($segments[$previousIndexSegment]))
			{
				$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
				if ($isLayout)
				{
					$previousIndexSegment -= 1;
				}
			}

			return $vars;
		}

		
		$searchSefListAll = array_search('list-all', $segments);
		if ($searchSefListAll !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchSefListAll, $validArrayIndex))
			{
				$vars['view'] = 'listall';
				if (isset($segments[$searchSefListAll - 1]))
				{
					if ($segments[$searchSefListAll - 1] == JApplication::stringURLSafe('root'))
					{
						$rootCategory = JUDownloadFrontHelperCategory::getRootCategory();
						$vars['id']   = $rootCategory->id;
					}
					else
					{
						$vars['id'] = (int) $segments[$searchSefListAll - 1];
					}
				}
				else
				{
					if ($activeMenu && isset($activeMenu->query) && isset($activeMenu->query['view']) && isset($activeMenu->query['id']) &&
						$activeMenu->query['view'] == 'tree'
					)
					{
						$vars['id'] = $activeMenu->query['id'];
					}
				}

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					if ($segments[$previousIndexSegment] == JApplication::stringURLSafe('rss'))
					{
						$vars['format'] = 'feed';
						$previousIndexSegment -= 1;
					}
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				return $vars;
			}
		}

		
		$searchSefListAlpha = array_search(JApplication::stringURLSafe('list-alpha'), $segments);
		if ($searchSefListAlpha !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchSefListAlpha, $validArrayIndex))
			{
				$vars['view'] = 'listalpha';
				if (isset($segments[$searchSefListAlpha - 1]))
				{
					if ($segments[$searchSefListAlpha - 1] == JApplication::stringURLSafe('root'))
					{
						$rootCategory = JUDownloadFrontHelperCategory::getRootCategory();
						$vars['id']   = $rootCategory->id;
					}
					else
					{
						$vars['id'] = (int) $segments[$searchSefListAlpha - 1];
					}
				}
				else
				{
					if ($activeMenu && isset($activeMenu->query) && isset($activeMenu->query['view']) && isset($activeMenu->query['id']) &&
						$activeMenu->query['view'] == 'tree'
					)
					{
						$vars['id'] = $activeMenu->query['id'];
					}
				}

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					if ($segments[$previousIndexSegment] == JApplication::stringURLSafe('rss'))
					{
						$vars['format'] = 'feed';
						$previousIndexSegment -= 1;
					}
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
					if ($isLayout)
					{
						$previousIndexSegment -= 1;
					}
				}

				if (isset($segments[$previousIndexSegment]))
				{
					if ($previousIndexSegment > $searchSefListAlpha)
					{
						$vars['alpha'] = $segments[$previousIndexSegment];
					}
				}

				return $vars;
			}
		}

		
		$searchSefFeatured = array_search(JApplication::stringURLSafe('featured'), $segments);
		if ($searchSefFeatured !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchSefFeatured, $validArrayIndex))
			{
				$vars['view'] = 'featured';
				if (isset($segments[$searchSefFeatured - 1]))
				{
					if ($segments[$searchSefFeatured - 1] == JApplication::stringURLSafe('root'))
					{
						$rootCategory = JUDownloadFrontHelperCategory::getRootCategory();
						$vars['id']   = $rootCategory->id;
					}
					else
					{
						$vars['id'] = (int) $segments[$searchSefFeatured - 1];
					}
				}
				else
				{
					if ($activeMenu && isset($activeMenu->query) && isset($activeMenu->query['view']) && isset($activeMenu->query['id']) &&
						$activeMenu->query['view'] == 'tree'
					)
					{
						$vars['id'] = $activeMenu->query['id'];
					}
				}

				if (isset($segments[$searchSefFeatured + 1]))
				{
					if ($segments[$searchSefFeatured + 1] == JApplication::stringURLSafe('all'))
					{
						$vars['all'] = 1;
					}
				}

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					if ($segments[$previousIndexSegment] == JApplication::stringURLSafe('rss'))
					{
						$vars['format'] = 'feed';
						$previousIndexSegment -= 1;
					}
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
				}

				return $vars;
			}
		}

		
		$searchSefCollections = array_search(JApplication::stringURLSafe('collections'), $segments);
		if ($searchSefCollections !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchSefCollections, $validArrayIndex))
			{
				$vars['view'] = 'collections';
				if (isset($segments[$searchSefCollections - 1]))
				{
					if ($segments[$searchSefCollections - 1] != JApplication::stringURLSafe('root'))
					{
						$vars['id'] = (int) $segments[$searchSefCollections - 1];
					}
				}

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					if ($segments[$previousIndexSegment] == JApplication::stringURLSafe('rss'))
					{
						$vars['format'] = 'feed';
						$previousIndexSegment -= 1;
					}
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
				}

				return $vars;
			}
		}

		
		$searchSefDashboard = array_search(JApplication::stringURLSafe('dashboard'), $segments);
		if ($searchSefDashboard !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchSefDashboard, $validArrayIndex))
			{
				$vars['view'] = 'dashboard';
				if (isset($segments[$searchSefDashboard - 1]))
				{
					if ($segments[$searchSefDashboard - 1] != JApplication::stringURLSafe('root'))
					{
						$vars['id'] = (int) $segments[$searchSefDashboard - 1];
					}
				}

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
				}

				return $vars;
			}
		}

		
		$searchSefUserComments = array_search(JApplication::stringURLSafe('comments'), $segments);
		if ($searchSefUserComments !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchSefUserComments, $validArrayIndex))
			{
				$vars['view'] = 'usercomments';
				if (isset($segments[$searchSefUserComments - 1]))
				{
					if ($segments[$searchSefUserComments - 1] != JApplication::stringURLSafe('root'))
					{
						$vars['id'] = (int) $segments[$searchSefUserComments - 1];
					}
				}

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
				}

				return $vars;
			}
		}

		
		$searchSefUserDocuments = array_search(JApplication::stringURLSafe('documents'), $segments);
		if ($searchSefUserDocuments !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchSefUserDocuments, $validArrayIndex))
			{
				$vars['view'] = 'userdocuments';
				if (isset($segments[$searchSefUserDocuments - 1]))
				{
					if ($segments[$searchSefUserDocuments - 1] != JApplication::stringURLSafe('root'))
					{
						$vars['id'] = (int) $segments[$searchSefUserDocuments - 1];
					}
				}

				if (isset($segments[$searchSefUserDocuments + 1]))
				{
					if ($segments[$searchSefUserDocuments + 1] == JApplication::stringURLSafe('published'))
					{
						$vars['filter'] = 'published';
					}
					elseif ($segments[$searchSefUserDocuments + 1] == JApplication::stringURLSafe('unpublished'))
					{
						$vars['filter'] = 'unpublished';
					}
					elseif ($segments[$searchSefUserDocuments + 1] == JApplication::stringURLSafe('pending'))
					{
						$vars['filter'] = 'pending';
					}
				}

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					if ($segments[$previousIndexSegment] == JApplication::stringURLSafe('rss'))
					{
						$vars['format'] = 'feed';
						$previousIndexSegment -= 1;
					}
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
				}

				return $vars;
			}
		}

		
		$searchSefUserSubscriptions = array_search(JApplication::stringURLSafe('user-subscriptions'), $segments);
		if ($searchSefUserSubscriptions !== false)
		{
			$validArrayIndex = array(0, 1, 2);
			if (in_array($searchSefUserSubscriptions, $validArrayIndex))
			{
				$vars['view'] = 'usersubscriptions';

				if (isset($segments[$searchSefUserSubscriptions - 1]))
				{
					if ($segments[$searchSefUserSubscriptions - 1] != JApplication::stringURLSafe('root'))
					{
						$vars['id'] = (int) $segments[$searchSefUserSubscriptions - 1];
					}
				}

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
				}

				return $vars;
			}
		}

		$searchSefSearchBy = array_search(JApplication::stringURLSafe('search-by'), $segments);
		if ($searchSefSearchBy !== false)
		{
			$validArrayIndex = array(0, 1);
			if (in_array($searchSefSearchBy, $validArrayIndex))
			{
				$vars['view'] = 'searchby';
				if (isset($segments[$searchSefSearchBy + 1]))
				{
					$vars['field_id'] = (int) $segments[$searchSefSearchBy + 1];
				}

				if (isset($segments[$searchSefSearchBy + 2]))
				{
					$vars['value'] = $segments[$searchSefSearchBy + 2];
				}

				$previousIndexSegment = $total - 1;

				$isPaged = JUDownloadHelperRoute::parsePagination($vars, $segments, $params);
				if ($isPaged)
				{
					$previousIndexSegment -= 1;
				}

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
				}

				return $vars;
			}
		}

		
		$searchSefContact = array_search(JApplication::stringURLSafe('contact'), $segments);
		if ($searchSefContact !== false)
		{
			if ($searchSefContact == $indexLastSegment || $searchSefContact == ($indexLastSegment - 1))
			{
				$vars['view'] = 'contact';

				if (isset($segments[$searchSefContact - 1]))
				{
					$vars['doc_id'] = (int) $segments[$searchSefContact - 1];
				}

				$previousIndexSegment = $total - 1;

				if (isset($segments[$previousIndexSegment]))
				{
					$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
				}

				return $vars;
			}
		}

		$searchSefCheckIn = array_search(JApplication::stringURLSafe('checkin'), $segments);
		if ($searchSefCheckIn !== false)
		{
			$vars['task'] = 'forms.checkin';
			if (isset($segments[$searchSefCheckIn - 1]))
			{
				$vars['id'] = (int) $segments[$searchSefCheckIn - 1];
			}

			return $vars;
		}

		$searchSefAdd = array_search(JApplication::stringURLSafe('add'), $segments);
		if ($searchSefAdd !== false)
		{
			$vars['task'] = 'form.add';
			if (isset($segments[$searchSefAdd - 1]))
			{
				$vars['cat_id'] = (int) $segments[$searchSefAdd - 1];
			}

			return $vars;
		}

		$searchSefEdit = array_search(JApplication::stringURLSafe('edit'), $segments);
		if ($searchSefEdit !== false)
		{
			$vars['task'] = 'form.edit';
			if (isset($segments[$searchSefEdit - 1]))
			{
				$vars['id'] = (int) $segments[$searchSefEdit - 1];
			}

			return $vars;
		}

		$searchSefDelete = array_search(JApplication::stringURLSafe('delete'), $segments);
		if ($searchSefDelete !== false)
		{
			$vars['task'] = 'forms.delete';
			if (isset($segments[$searchSefDelete - 1]))
			{
				$vars['id'] = (int) $segments[$searchSefDelete - 1];
			}

			return $vars;
		}

		$searchNewDocument = array_search(JApplication::stringURLSafe('new-document'), $segments);
		if ($searchNewDocument !== false)
		{
			$vars['view']   = 'form';
			$vars['layout'] = 'edit';
			if (isset($segments[$searchNewDocument - 1]))
			{
				$vars['cat_id'] = (int) $segments[$searchNewDocument - 1];
			}

			return $vars;
		}

		$searchSefApprove = array_search(JApplication::stringURLSafe('approve'), $segments);
		if ($searchSefApprove !== false)
		{
			if ($searchSefApprove == $indexLastSegment)
			{
				$vars['task']    = 'modpendingdocument.edit';
				$vars['approve'] = 1;
			}
			else
			{
				$vars['view']    = 'form';
				$vars['layout']  = 'edit';
				$vars['approve'] = 1;
			}

			if (isset($segments[$searchSefApprove - 1]))
			{
				$vars['id'] = (int) $segments[$searchSefApprove - 1];
			}

			return $vars;
		}

		$searchSefPublish = array_search(JApplication::stringURLSafe('publish'), $segments);
		if ($searchSefPublish !== false)
		{
			$vars['task'] = 'forms.publish';
			if (isset($segments[$searchSefPublish - 1]))
			{
				$vars['id'] = (int) $segments[$searchSefPublish - 1];
			}

			return $vars;
		}

		$searchSefUnPublish = array_search(JApplication::stringURLSafe('unpublish'), $segments);
		if ($searchSefUnPublish !== false)
		{
			$vars['task'] = 'forms.unpublish';
			if (isset($segments[$searchSefUnPublish - 1]))
			{
				$vars['id'] = (int) $segments[$searchSefUnPublish - 1];
			}

			return $vars;
		}

		$searchSefSubscribe = array_search(JApplication::stringURLSafe('subscribe'), $segments);
		if ($searchSefSubscribe !== false)
		{
			$vars['task'] = 'subscribe.save';
			if (isset($segments[$searchSefSubscribe - 1]))
			{
				$vars['doc_id'] = (int) $segments[$searchSefSubscribe - 1];
			}

			return $vars;
		}

		$searchSefUnSubscribe = array_search(JApplication::stringURLSafe('unsubscribe'), $segments);
		if ($searchSefUnSubscribe !== false)
		{
			$vars['task'] = 'subscribe.remove';

			if (isset($segments[$searchSefUnSubscribe + 1]))
			{
				$vars['sub_id'] = (int) $segments[$searchSefUnSubscribe + 1];
			}

			if (isset($segments[$searchSefUnSubscribe + 2]))
			{
				$vars['code'] = $segments[$searchSefUnSubscribe + 2];
			}

			return $vars;
		}

		$searchSefDownload = array_search(JApplication::stringURLSafe('download'), $segments);
		if ($searchSefDownload !== false)
		{
			$vars['task'] = 'download.download';

			if (isset($segments[$searchSefDownload - 3]))
			{
				if ($segments[$searchSefDownload - 3] == JApplication::stringURLSafe('file'))
				{
					$vars['file_id'] = (int) $segments[$searchSefDownload - 2];
					if ($segments[$searchSefDownload - 1] != JApplication::stringURLSafe('latest'))
					{
						$vars['version'] = $segments[$searchSefDownload - 1];
					}

					return $vars;
				}
			}

			if (isset($segments[$searchSefDownload - 1]))
			{
				if ($segments[$searchSefDownload - 1] != JApplication::stringURLSafe('latest'))
				{
					$vars['version'] = $segments[$searchSefDownload - 1];
				}

				if (isset($segments[$searchSefDownload - 2]))
				{
					$vars['doc_id'] = (int) $segments[$searchSefDownload - 2];
				}
			}

			return $vars;
		}

		
		$previousIndexSegment = $indexLastSegment;

		
		if (isset($segments[$previousIndexSegment]))
		{
			$isPaged = preg_match('/' . preg_quote(JApplication::stringURLSafe('page') . '-') . '[0-9]*+/', $segments[$previousIndexSegment]);
			if ($isPaged)
			{
				if ($indexLastSegment == 0)
				{
					if (is_object($activeMenu) && $activeMenu->component == 'com_judownload')
					{
						$vars = $activeMenu->query;
						JUDownloadHelperRoute::parsePagination($vars, $segments, $params);

						return $vars;
					}
				}
				$previousIndexSegment -= 1;
			}
		}

		
		if (isset($segments[$previousIndexSegment]))
		{
			$isFeed = $segments[$previousIndexSegment] == JApplication::stringURLSafe('rss') ? true : false;
			if ($isFeed)
			{
				$vars['format'] = 'feed';
				if ($indexLastSegment == 0)
				{
					if (is_object($activeMenu) && $activeMenu->component == 'com_judownload')
					{
						$vars           = $activeMenu->query;
						$vars['format'] = 'feed';

						return $vars;
					}
				}
				$previousIndexSegment -= 1;
			}
		}

		
		if (isset($segments[$previousIndexSegment]))
		{
			$isLayout = JUDownloadHelperRoute::parseLayout($segments[$previousIndexSegment], $vars, $params);
			if ($isLayout)
			{
				$previousIndexSegment -= 1;
			}
		}

		
		if (!empty($segments))
		{
			$reverseSegments = array_reverse($segments);
			foreach ($reverseSegments as $segmentItemKey => $segmentItem)
			{
				if (preg_match('/^\d+\-.+/', $segmentItem))
				{
					$indexAlias = $indexLastSegment - $segmentItemKey;
					break;
				}
			}

			if (isset($indexAlias) && isset($segments[$indexAlias]))
			{
				if (strpos($segments[$indexAlias], '-') === false)
				{
					$itemId    = (int) $segments[$indexAlias];
					$itemAlias = substr($segments[$indexAlias], strlen($itemId) + 1);
				}
				else
				{
					list($itemId, $itemAlias) = explode('-', $segments[$indexAlias], 2);
				}

				if (is_numeric($itemId))
				{
					$categoryObject = JUDownloadHelper::getCategoryById($itemId);
					if (is_object($categoryObject) && isset($categoryObject->alias) && $categoryObject->alias == $itemAlias)
					{
						$vars['view'] = 'category';
						$vars['id']   = $itemId;

						JUDownloadHelperRoute::parsePagination($vars, $segments, $params);

						return $vars;
					}

					$documentObject = JUDownloadHelper::getDocumentById($itemId);
					if (is_object($documentObject) && isset($documentObject->alias) && $documentObject->alias == $itemAlias)
					{
						$vars['id'] = $itemId;
						if (isset($vars['layout']))
						{
							if ($vars['layout'] == 'edit')
							{
								$vars['view'] = 'form';
							}
							else
							{
								$vars['view'] = 'document';
							}
						}

						if (!isset($vars['view']))
						{
							$vars['view'] = 'document';
						}

						if ($vars['view'] == 'document')
						{
							if (isset($segments[$indexAlias + 1]))
							{
								if ($segments[$indexAlias + 1] == JApplication::stringURLSafe('print'))
								{
									$vars['print']  = 1;
									$vars['tmpl']   = 'component';
									$vars['layout'] = 'print';
								}
								elseif ($segments[$indexAlias + 1] == JApplication::stringURLSafe('changelogs'))
								{
									$vars['layout'] = 'changelogs';
								}
								elseif ($segments[$indexAlias + 1] == JApplication::stringURLSafe('versions'))
								{
									$vars['layout'] = 'versions';
								}

							}
						}

						JUDownloadHelperRoute::parsePagination($vars, $segments, $params);

						return $vars;
					}

					if (is_object($categoryObject) && isset($categoryObject->id) && $categoryObject->id)
					{
						$vars['view'] = 'category';
						$vars['id']   = $itemId;

						JUDownloadHelperRoute::parsePagination($vars, $segments, $params);

						return $vars;
					}

					if (is_object($documentObject) && isset($documentObject->id) && $documentObject->id)
					{
						$vars['id'] = $itemId;

						if (isset($vars['layout']))
						{
							if ($vars['layout'] == 'edit')
							{
								$vars['view'] = 'form';
							}
							else
							{
								$vars['view'] = 'document';
							}
						}

						if (!isset($vars['view']))
						{
							$vars['view'] = 'document';
						}

						if ($vars['view'] == 'document')
						{
							if (isset($segments[$indexAlias + 1]))
							{
								if ($segments[$indexAlias + 1] == JApplication::stringURLSafe('print'))
								{
									$vars['print']  = 1;
									$vars['tmpl']   = 'component';
									$vars['layout'] = 'print';
								}
								elseif ($segments[$indexAlias + 1] == JApplication::stringURLSafe('changelogs'))
								{
									$vars['layout'] = 'changelogs';
								}
								elseif ($segments[$indexAlias + 1] == JApplication::stringURLSafe('versions'))
								{
									$vars['layout'] = 'versions';
								}
							}
						}

						JUDownloadHelperRoute::parsePagination($vars, $segments, $params);

						return $vars;
					}
				}
			}
		}

		if (is_object($activeMenu) && $activeMenu->component == 'com_judownload')
		{
			$vars = $activeMenu->query;
		}

		return $vars;
	}
}


function JUDownloadBuildRoute(&$query)
{
	$router = new JUDownloadRouter;

	return $router->build($query);
}

function JUDownloadParseRoute($segments)
{
	$router = new JUDownloadRouter;

	return $router->parse($segments);
}

