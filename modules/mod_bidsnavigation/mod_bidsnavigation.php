<?php
/**
 * @package AuctionsFactory
 * @version 2.0.0
 * @copyright www.thefactory.ro
 * @license: commercial
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$option = JRequest::getCmd('option');
$task = JRequest::getCmd('task');

JHTML::stylesheet(JURI::root().'modules/mod_bidsnavigation/assets/css/style.css');

$jsRoot = JURI::root().'components/com_bids/js/jquery/';
JHTML::script($jsRoot.'jquery.js');
JHTML::script($jsRoot.'jquery.noconflict.js');

if('com_bids'!=$option) {
    return;
}

$document = JFactory::getDocument();

switch($task) {

    case 'listcats':
    case 'tree':
    case 'listauctions':
        $categoryId = JRequest::getInt('cat',1);
        if(0==$categoryId) {
            $categoryId = 1;
        }
        break;

    case 'viewbids':
        JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_bids'.DS.'tables');
        $auctionId = JRequest::getInt('id');
        $auction = JTable::getInstance('auction');
        $auction->load($auctionId);
        $categoryId = $auction->cat;
        break;

    default:
        $categoryId = 1;
        break;
}


$db = JFactory::getDbo();

$q = $db->getQuery(true);
$q->select('p.*')
    ->from('#__categories c')
    ->leftJoin('#__categories p ON c.lft BETWEEN p.lft AND p.rgt')
    ->where('p.extension=\'com_bids\' AND p.published=1 AND c.id='.$db->quote($categoryId))
    ->order('p.lft ASC');
$db->setQuery($q);
$pathRows = $db->loadObjectList();

$q = $db->getQuery(true);
$q->select('*')
        ->from('#__categories')
        ->where('extension=\'com_bids\' AND parent_id='.$db->quote($categoryId))
        ->order('lft');
$db->setQuery($q);
$children = $db->loadObjectList();

$linksPath = array();
$linksPath[] = JHTML::link('index.php?option=com_bids&task=listauctions&reset=all', JText::_('COM_BIDS_ROOT_CATEGORY'), 'style="float: left;"' );

foreach($pathRows as $r) {

    if($r->id==1) {
        //category system root
        continue;
    }
    $linksPath[] = JHTML::link( JRoute::_('index.php?option=com_bids&task=listauctions&cat='.$r->id), $r->title, 'style="float: left;"' );
}

$childrenHtml = '';
if( count($children) ) {

    $childrenHtml = '<div class="bids_navigation_children_arrow" id="bids_navigation_children_arrow_'.$module->id.'">';

    $childrenHtml .= '<div class="bids_navigation_children" style="display: none;" id="bids_navigation_children_'.$module->id.'">';
    foreach($children as $child) {
        $childrenHtml .= '<div>'.JHTML::link('index.php?option=com_bids&task=listauctions&cat='.$child->id,$child->title).'</div>';
    }
    $childrenHtml .= '</div></div>';

    $js =
    "jQueryBids(document).ready(function() {
        jQueryBids('#bids_navigation_children_arrow_".$module->id."').hover(
            function () {
                jQueryBids('#bids_navigation_children_arrow_".$module->id."').css('background','url(\"".JURI::root()."modules/mod_bidsnavigation/assets/img/arrow_down.gif\") no-repeat');
    			jQueryBids('#bids_navigation_children_".$module->id."').stop(true, true).slideDown();
    		},
            function () {
                jQueryBids('#bids_navigation_children_arrow_".$module->id."').css('background','url(\"".JURI::root()."modules/mod_bidsnavigation/assets/img/arrow_right.gif\") no-repeat');
    			jQueryBids('#bids_navigation_children_".$module->id."').stop(true, true).delay(500).slideUp();
    		});
    });";
    $document->addScriptDeclaration($js);
}

echo '<div class="bids_navigation_path">'.implode('<span style="float: left;">&nbsp;&raquo;&nbsp;</span>',$linksPath).'<span style="float: left;">&nbsp;</span>'.
        $childrenHtml.
    '</div><div style="clear: both;"></div>';