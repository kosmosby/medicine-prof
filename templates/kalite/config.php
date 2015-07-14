<?php
require_once(dirname(__FILE__)."/warp/warp.php");

$warp = Warp::getInstance();

// add paths
$warp['path']->register(dirname(__FILE__).'/warp/systems/joomla/helpers','helpers');
$warp['path']->register(dirname(__FILE__).'/warp/systems/joomla/layouts','layouts');
$warp['path']->register(dirname(__FILE__).'/layouts','layouts');
$warp['path']->register(dirname(__FILE__).'/menus','menu');
$warp['path']->register(dirname(__FILE__).'/js', 'js');
$warp['path']->register(dirname(__FILE__).'/css', 'css');

$lang = JFactory::getLanguage();
$extension = 'tpl_kalite';
$base_dir = JPATH_SITE;
$language_tag = $lang->getTag();


$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);


// init system
$warp['system']->init();