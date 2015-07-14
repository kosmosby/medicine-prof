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


jimport('joomla.application.component.model');



jimport('joomla.application.component.modellist');

class JUDownloadModelLanguages extends JModelList
{
	protected $_total;

	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('site');

		$listLimit = $app->getUserStateFromRequest($this->context . 'list.limit', 'limit', 100);
		$this->setState('list.limit', $listLimit);

		$limitStart = $app->getUserStateFromRequest($this->context . 'list.start', 'limitstart', 0);
		$this->setState('list.start', $limitStart);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$filter = $this->getUserStateFromRequest($this->context . '.filter.filter', 'filter', 'none');
		$this->setState('language.filter', $filter);

		$site = $this->getUserStateFromRequest($this->context . 'list.site', 'site', 'frontend');
		$this->setState('language.site', $site);

		$lang = $this->getUserStateFromRequest($this->context . 'list.language', 'lang', 'en-GB');
		$this->setState('language.lang', $lang);

		$item = $this->getUserStateFromRequest($this->context . 'list.item', 'item', '.com_judownload.ini');
		$this->setState('language.item', $item);
	}

	
	public function getTranslate($lang, $item, $site = 'frontend')
	{
		$languages = array();
		$search    = $this->getState('filter.search', '');
		$limit     = $this->getState('list.limit');
		if ($site == 'frontend')
		{
			$path = JPATH_ROOT . "/language/en-GB/en-GB" . $item;
		}
		else
		{
			$path = JPATH_ADMINISTRATOR . "/language/en-GB/en-GB" . $item;
		}

		
		$content     = file_get_contents($path);
		$enGBStrings = @parse_ini_string($content);

		
		if ($search != '')
		{
			$enGBStringsOriginal = $enGBStrings;

			
			if (is_numeric($search))
			{
				$newENGBStrings = array();
				$i              = 1;
				foreach ($enGBStrings AS $key => $value)
				{
					if ($i == $search)
					{
						$newENGBStrings[$key] = $value;
						break;
					}

					$i++;
				}

				$enGBStrings = $newENGBStrings;

				$this->setState('list.start', $search - 1);
			}
			
			else
			{
				$newENGBStrings = array();
				foreach ($enGBStrings AS $key => $value)
				{
					$pattern = "#$search#is";
					if (preg_match($pattern, $key))
					{
						$newENGBStrings[$key] = $value;
					}
				}

				$enGBStrings = $newENGBStrings;
			}
		}

		if ($lang != 'en-GB')
		{
			if ($site == 'frontend')
			{
				$path = JPATH_ROOT . "/" . 'language' . "/" . $lang . "/" . $lang . $item;
			}
			if ($site == 'backend')
			{
				$path = JPATH_ADMINISTRATOR . "/" . 'language' . "/" . $lang . "/" . $lang . $item;
			}

			if (JFile::exists($path))
			{
				$content = file_get_contents($path);
				$strings = @parse_ini_string($content);
				
				if ($search != '')
				{
					$newStrings = array();
					foreach ($strings AS $key => $value)
					{
						
						if (array_key_exists($key, $enGBStrings))
						{
							$newStrings[$key] = $value;
						}
						
						else
						{
							$pattern = '#' . $search . '#';
							if (preg_match($pattern, $value))
							{
								$newStrings[$key]  = $value;
								$enGBStrings[$key] = $enGBStringsOriginal[$key];
							}
						}
						$strings = $newStrings;
					}
				}
				$strings['COMMENT'] = trim($this->getComment($content));
			}
			else
			{
				$strings = array();
			}

			$filter = $this->getState('language.filter', 'none');
			if ($filter == 'empty')
			{
				foreach ($enGBStrings AS $key => $string)
				{
					if (isset($strings[$key]))
					{
						unset($enGBStrings[$key]);
					}
				}
			}

			if ($filter == 'warning')
			{
				foreach ($enGBStrings AS $key => $string)
				{
					if (!isset($strings[$key]) || trim($strings[$key]) != trim($string))
					{
						unset($enGBStrings[$key]);
						unset($strings[$key]);
					}
				}
			}

			
			$this->_total = count($enGBStrings);
			$start        = $this->getStart();
			$enGBStrings  = array_slice($enGBStrings, $start, $limit);
			$strings      = array_slice($strings, $start, $limit);

			$languages['en-GB'][$item] = $enGBStrings;
			$languages[$lang][$item]   = $strings;
		}
		else
		{
			
			$this->_total              = count($enGBStrings);
			$start                     = $this->getStart();
			$enGBStrings               = array_slice($enGBStrings, $start, $limit);
			$enGBStrings['COMMENT']    = trim($this->getComment($content));
			$languages['en-GB'][$item] = $enGBStrings;
		}

		return $languages;
	}

	
	public function getComment($content = '')
	{
		$comment = '';
		if ($content != '')
		{
			$pattern = '#([A-Z][A-Z0-9_\-]*\s*)=#ism';
			preg_match_all($pattern, $content, $matches);
			if ($matches)
			{
				$firstElement    = $matches[0][0];
				$posFirstElement = strpos($content, $firstElement);
				$comment         = substr($content, 0, $posFirstElement);
			}
		}

		return trim($comment);
	}

	
	public function getSiteLanguages()
	{
		$path    = JPATH_ROOT . "/" . 'language';
		$folders = JFolder::folders($path);
		$rets    = array();

		foreach ($folders AS $folder)
		{
			if ($folder != 'pdf_fonts')
			{
				$rets[] = $folder;
			}
		}

		return $rets;
	}

	
	public function getFileLanguages($site = 'frontend', $language = 'en-GB', $filter = 'com_judownload')
	{
		if ($site == 'frontend')
		{
			$path = JPATH_ROOT . "/language/" . $language;
		}
		elseif ($site == 'backend')
		{
			$path = JPATH_ADMINISTRATOR . "/language/" . $language;
		}

		$folders = array();
		if (JFolder::exists($path))
		{
			$folders = JFolder::files($path, $filter);
		}

		return $folders;
	}

	
	public function save($data)
	{
		$lang    = $data['lang'];
		$item    = $data['item'];
		$keysArr = $data['keys'];

		$originalFile = ($data['site'] == 'frontend')
			? JPATH_ROOT . "/language/en-GB/en-GB" . $item
			: JPATH_ADMINISTRATOR . "/language/en-GB/en-GB" . $item;

		$filePath = ($data['site'] == 'frontend')
			? JPATH_ROOT . "/language/" . $lang . "/" . $lang . $item
			: JPATH_ADMINISTRATOR . "/language/" . $lang . "/" . $lang . $item;

		$language = '';

		
		$comment = $data['comment'];
		unset($data['comment']);
		$commentArr = explode("\n", $comment);
		foreach ($commentArr AS $comment)
		{
			$language .= ";" . $comment . "\n";
		}
		$language .= "\n";

		$content = file_get_contents($originalFile);
		$strings = @parse_ini_string($content);
		foreach ($strings AS $key => $string)
		{
			if (!empty($data[$key]))
			{
				$index = array_search($key, $keysArr);
				unset($keysArr[$index]);
				$string = stripslashes($data[$key]);
				$string = addslashes($string);
			}
			else
			{
				$string = stripslashes($data[$key]);
				$string = addslashes($string);
			}
			$key = strtoupper($key);
			$language .= "$key=\"$string\"\n";
		}


		
		if (!empty($keysArr) && $lang == 'en-GB')
		{
			foreach ($keysArr AS $key)
			{
				if (!empty($key) && !empty($data[$key]))
				{
					$string = stripslashes($string);
					$string = addslashes($string);
					$language .= "$key=\"$string\"\n";
				}
			}
		}

		return JFile::write($filePath, $language, true);
	}

	
	public function getFileLanguageList($site = 'frontend')
	{
		$languageFolder = $this->getSiteLanguages();
		$fileArr        = array();
		if (count($languageFolder))
		{
			foreach ($languageFolder AS $language)
			{
				if ($language != 'overrides')
				{
					$fileArr[$language] = $this->getFileLanguages($site, $language);
				}
			}
		}

		return $fileArr;
	}

	
	public function saveLanguageKey($key, $val, $filePath)
	{
		$content  = file_get_contents($filePath);
		$language = '';
		$language .= $this->getComment($content) . "\n";

		$strings = @parse_ini_string($content);
		$strings = array_merge($strings, array($key => $val));
		foreach ($strings AS $key => $string)
		{
			$string = stripslashes($string);
			$string = addslashes($string);
			$language .= "$key=\"$string\"\n";
		}

		if (JFile::write($filePath, $language, true))
		{
			echo JText::_('COM_JUDOWNLOAD_LANGUAGE_KEY_SAVE_SUCCESSFULLY');
		}
		else
		{
			echo JText::_('COM_JUDOWNLOAD_LANGUAGE_KEY_SAVE_FAILED');
		}
	}

	
	public function removeLanguageKey($data, $filePath)
	{
		$key        = $data['key'];
		$lang       = $data['lang'];
		$file       = $data['file'];
		$site       = $data['site'];
		$content    = file_get_contents($filePath);
		$newContent = '';
		$newContent .= $this->getComment($content) . "\n";
		$stringArr = @parse_ini_string($content);
		unset($stringArr[$key]);
		foreach ($stringArr AS $key => $string)
		{
			$string = stripslashes($string);
			$string = addslashes($string);
			$newContent .= "$key=\"$string\"\n";
		}

		if (@JFile::write($filePath, $newContent, true))
		{
			echo JText::_('COM_JUDOWNLOAD_LANGUAGE_KEY_REMOVE_SUCCESSFULLY');
		}
		else
		{
			echo JText::_('COM_JUDOWNLOAD_LANGUAGE_KEY_REMOVE_FAILED');
		}

		
		if ($lang == 'en-GB')
		{
			$siteLanguages = $this->getSiteLanguages();
			if (sizeof($siteLanguages) > 0)
			{
				foreach ($siteLanguages AS $value)
				{
					if ($value != 'en-GB' && $value != 'overrides')
					{
						$filePath = ($site == 'frontend') ? JPATH_ROOT . "/language/" . $value . "/" . $value . $file
							: JPATH_ADMINISTRATOR . "/language/" . $value . "/" . $value . $file;
						if (JFile::exists($filePath))
						{
							$content    = file_get_contents($filePath);
							$newContent = '';
							$newContent .= $this->getComment($content) . "\n";
							unset($stringArr[$key]);
							foreach ($stringArr AS $key => $string)
							{
								$string = stripslashes($string);
								$string = addslashes($string);
								$newContent .= "$key=\"$string\"\n";
							}
							@JFile::write($filePath, $newContent, true);
						}
					}
				}
			}
		}
	}

	
	public function share($files, $site = 'frontend')
	{
		$mail = JFactory::getMailer();
		$mail->setSender(array('admin@joomultra.com', 'JU Download'));

		foreach ($files AS $file)
		{
			if (strtolower($site) == 'frontend')
			{
				$mail->addAttachment(JPATH_SITE . '/language/' . $file);
			}
			elseif (strtolower($site) == 'backend')
			{
				$mail->addAttachment(JPATH_ADMINISTRATOR . '/language/' . $file);
			}
		}

		$app = JFactory::getApplication();

		$subject = $app->input->get('subject', 'Language file for JU Download', 'string');
		$message = $app->input->get('messege', '', 'string');

		$mail->setSubject($subject);
		$mail->addRecipient('qvsoft@gmail.com');

		$mail->addReplyTo($app->get('mailfrom'), $app->get('fromname'));
		$mail->IsHTML(true);
		$body = $message;
		$body .= "<br/>------------------------------<br/>";
		$body .= "Language file for JU Download<br/>";
		$body .= "Side: " . ucfirst($site) . "<br/>";
		$body .= "Share from: " . JUri::root();
		$mail->setBody($body);

		return $mail->send();
	}

	public function setTotal($total)
	{
		$this->_total = $total;
	}

	public function getTotal()
	{
		return $this->_total;
	}

	public function getPagination()
	{
		jimport('joomla.html.pagination');
		$pagination = new JPagination($this->getTotal(), $this->getStart(), $this->getState('list.limit'));

		return $pagination;
	}
}