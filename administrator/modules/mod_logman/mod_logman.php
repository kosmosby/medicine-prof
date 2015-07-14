<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

defined('_JEXEC') or die;

if (!class_exists('Koowa')) {
	return;
}

KService::get('com://admin/logman.aliases')->setAliases();

if (!$params->get('limit')) {
    $params->set('limit', 20);
}

if ($params->get('user_filter', 0)) {
    $params->set('user', JFactory::getUser()->id);
}

echo KService::get('com://admin/logman.controller.activity', array('request' => $params->toArray()))
		->layout('list')
		->display();