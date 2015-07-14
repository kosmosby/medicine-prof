<?php

/**
 * @package AuctionsFactory
 * @version 2.0.6
 * @copyright www.thefactory.ro
 * @license: commercial
 */
// no direct access
defined('_JEXEC') or die('Restricted access');


$jsRoot = JUri::root().'components/com_bids/js/jquery/';

$jdoc = JFactory::getDocument();

$jdoc->addScript($jsRoot.'jquery.js');
$jdoc->addScript($jsRoot.'jquery.noconflict.js');

$jdoc->addScript($jsRoot.'jquery.cookie.js');
$jdoc->addScript($jsRoot.'tree/jquery.treeview.js');

$theme_css = '';
$theming = $params->get('theming', 'default');
switch ($theming) {

    case 'red':
    case 'azuro':
    case 'green':
        $theme_css = $jsRoot.'tree/themes/'.$theming.'/jquery.treeview.css';
        break;

    default:
        $theme_css = $jsRoot.'tree/jquery.treeview.css';
        break;
}
$jdoc->addStyleSheet($theme_css);

require_once (dirname(__FILE__).DS.'helper.php');

$countSubcategories = $params->get('display_subcategories_nr',0);
$countAuctions = $params->get('category_counter',0);

$cats = modBidsCategoryTreeHelper::getCategories( $countAuctions);

//output HTML
echo modBidsCategoryTreeHelper::catHTML( $cats, $countSubcategories, $countAuctions, $module->id);

//init JS behavior
$jsInitTree =
		'jQueryBids(document).ready(function(){
            jQueryBids("#bids_treecontainer_'.$module->id.'").show();
			jQueryBids("#bids_treecontainer_'.$module->id.'").treeview({
			    collapsed: '.$params->get('collapsed', true).',
			    animated: '.intval($params->get('fold_speed', 250)).',
			    persist: "cookie"
			});
		});';
$jdoc->addScriptDeclaration($jsInitTree);