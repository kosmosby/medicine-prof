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

class JUDownloadFieldCaptcha extends JUDownloadFieldBase
{

	public function __construct($field = null, $doc = null)
	{
		parent::__construct($field, $doc);
		$this->required = true;
	}

	
	public function getCaption($forceShow = false)
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			return "";
		}
		else
		{
			return parent::getCaption($forceShow);
		}
	}

	public function getInput($fieldValue = null)
	{
		if (!$this->isPublished())
		{
			return "";
		}

		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			return "";
		}

		return JUDownloadFrontHelperCaptcha::getCaptcha(false, null, false, $this->getName(), $this->getId(), $this->getId() . "_captcha_namespace");
	}

	public function getBackendOutput()
	{
		return "";
	}

	public function getSearchInput($defaultValue = "")
	{
		return "";
	}

	protected function getValue()
	{
		return null;
	}

	
	public function canSubmit($userID = null)
	{
		$user = JFactory::getUser();

		if ($user->authorise('core.admin', 'com_judownload'))
		{
			return false;
		}

		return parent::canSubmit($userID);
	}

	
	public function canEdit($userID = null)
	{
		$user = JFactory::getUser();

		if ($user->authorise('core.admin', 'com_judownload'))
		{
			return false;
		}

		return parent::canEdit($userID);
	}

	public function PHPValidate($values)
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			return true;
		}

		
		if (($values === "" || $values === null) && !$this->isRequired())
		{
			return true;
		}

		$showCaptcha = false;

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/models');
		$modelDocument = JModelLegacy::getInstance('Document', 'JUDownloadModel');

		if (is_object($this->doc))
		{
			$documentId = $this->doc->id;
		}
		else
		{
			$documentId = 0;
		}

		if (($modelDocument->getDocumentSubmitType($documentId) == 'submit' && $this->canSubmit())
			|| ($modelDocument->getDocumentSubmitType($documentId) == 'edit' && $this->canEdit())
		)
		{
			$showCaptcha = true;
		}

		if ($showCaptcha)
		{
			$captchaId = $app->input->getString($this->getId() . "_captcha_namespace", "");

			if (!JUDownloadFrontHelperCaptcha::checkCaptcha($captchaId, $values))
			{
				
				$message = (string) $this->params->get('invalid_message');

				if ($message)
				{
					return JText::sprintf($message, $this->getCaption(true));
				}
				else
				{
					return JText::sprintf('COM_JUDOWNLOAD_FIELD_VALUE_IS_INVALID', $this->getCaption(true));
				}
			}
		}

		return true;
	}

	
	public function storeValue($value, $type = 'default', $inputData = null)
	{
		return true;
	}

	public function isPublished()
	{
		$storeId = md5(__METHOD__ . "::" . $this->id);
		if (!isset(self::$cache[$storeId]))
		{
			
			$app = JFactory::getApplication();
			if ($app->isAdmin())
			{
				self::$cache[$storeId] = false;

				return self::$cache[$storeId];
			}

			self::$cache[$storeId] = parent::isPublished();

			return self::$cache[$storeId];
		}

		return self::$cache[$storeId];
	}

	public function canView($options = array())
	{
		return false;
	}
}

?>