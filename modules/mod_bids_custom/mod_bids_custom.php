<?php
/**
 * @package AuctionsFactory
 * @version 2.0.0
 * @copyright www.thefactory.ro
 * @license: commercial
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(JRequest::getVar('option')=='com_comprofiler' &&
   JRequest::getVar('view')  == 'userprofile' &&
   JRequest::getVar('task')  == 'userprofile'
){
    $user_id =JRequest::getVar('user');
    if(!$user_id){
        return;
    }
    require_once (JPATH_ROOT.DS."components".DS."com_bids".DS.'options.php');
    require_once (JPATH_ROOT.DS."components".DS."com_bids".DS.'defines.php');
    require_once (JPATH_ROOT.DS."components".DS."com_bids".DS.'helpers'.DS.'bids.php');

    BidsHelper::LoadHelperClasses();
    JHtml::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_bids'.DS.'htmlelements');

    $mid = $module->id;

    $module_type		=	$params->get("type_display",0);
    $nr_auctions		=	$params->get("nr_auctions_displayed",1);
    $image_width		=	$params->get("image_width",30);
    $image_height		=	$params->get("image_height",30);
    $display_image		=	$params->get("display_image",1);
    $display_counter	=	$params->get("display_counter",0);
    $layout             =   $params->get('template','list');

    $sort_by			=	$params->get("sort_by","start_date");
    $filter_featured	=	$params->get("featured",false);

    require_once(dirname(__FILE__).DS.'helper.php');
    $rows = modBidsHelper::getRecords( $user_id, 1 );
    if($rows && count($rows) > 0){
      require(JModuleHelper::getLayoutPath('mod_bids_custom', $layout));
    }
}
