<?php
/**
 * @version 3.0
 * @package Jomgallery
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_awdjomalbum'.DS.'tables'.DS.'awdjomalbum.php' );
jimport('joomla.application.component.controller');

/**
 * awdjomalbum Component Controller
 */
class AwdjomalbumController extends JControllerLegacy {
	function display($cachable = false) {
        // Make sure we have a default view
        if( !JRequest::getVar( 'view' )) {
		    JRequest::setVar('view', 'awdalbumlist' );
        }
		$view=JRequest::getVar('view', 'awdalbumlist' );
		if($view=='awdjomalbum')
		{
			 JRequest::setVar('view', 'awdalbumlist' );
		}
		//require_once(JPATH_SITE . DS . 'components'.DS.'com_awdjomalbum'. DS.'js' . DS . 'include.php');
		
		$mainframe= JFactory::getApplication();
		$app = JFactory::getApplication('site');
		 
		$config =  & $app->getParams('com_awdwall');
		
		$display_jomsocialtoolbar 		= $config->get('display_jomsocialtoolbar', 1);
		$user 	= &JFactory::getUser();
		
		$db		=& JFactory::getDBO();
		$user=&JFactory::getUser();
		$layout = JRequest::getCmd('layout', '');	
 		$toolbarfile = JPATH_SITE . '/components/com_community/community.php';
		if($user->id) // if user is logged in (Register user only)
		{
			if (file_exists($toolbarfile)) // if jomsocial install then only
			{
				// getting the template of jomsocial config
				$query	= 'SELECT  ' . $db->nameQuote( 'params' ) . ' FROM ' . $db->nameQuote( '#__community_config' ) . ' WHERE ' . $db->nameQuote( 'name' ) . ' = ' . $db->quote('config');
				$db->setQuery( $query );
				$row = $db->loadResult();				//echo $row;exit;	
				jimport( 'joomla.html.parameter' );	
				$params	= new JParameter( $row );
				$template = $params->get('template', 'default');
				// calling the function to display toolbar	
				//if($layout!='mywalljomsocial')
				//{	 // jomsocial toolbar restriction on module display
					if($display_jomsocialtoolbar)
					{
						if($view=='awdwallimagelist' || $view=='awdimagelist')
						{
							
						}
						else
						{
							AwdjomalbumController::jomsocialtoolbar($template);
						}
					}	
				//}
			}
		}
		
		$Itemid=AwdwallHelperUser::getComItemId();;
			
		$access_level = $config->get('access_level', 0);
		$display_jomwalllogin = $config->get('display_jomwalllogin', 0);
	
		if($access_level == 1 && empty($user->id)){
			$mainlink=base64_encode(JRoute::_("index.php?option=com_awdwall&view=awdwall&layout=".$layout."&Itemid=".$Itemid,false)); 
				if($display_jomwalllogin==1)
				{ 
					$login=JRoute::_("index.php?option=com_awdwall&task=login&Itemid=".$Itemid,false);
				}
				else
				{
					$login=JRoute::_("index.php?option=com_users&view=login&Itemid=".$Itemid."&return=".$mainlink,false);
				}
			$mainframe->Redirect($login);
		}
		

		parent::display($tpl = null);
	}
function showlightboxview()
{
		//echo 'i m here12';
		$Itemid=AwdwallHelperUser::getComItemId();
		$user=&JFactory::getUser();
		$app = JFactory::getApplication('site');
		 
		$config =  & $app->getParams('com_awdwall');
		$access_level = $config->get('access_level', 0);
		if($access_level == 1 && empty($user->id)){
			$mainlink=base64_encode(JRoute::_("index.php?option=com_awdwall&view=awdwall&layout=".$layout."&Itemid=".$Itemid,false)); 
			$login=JRoute::_("index.php?option=com_awdwall&task=login&Itemid=".$Itemid,false);
			$mainframe->Redirect($login);
		}
		$albumlink=JRoute::_("index.php?option=com_awdjomalbum&view=awdalbumlist&wuid=".$user->id."&Itemid=".AwdwallHelperUser::getComItemId(),false);

		$db		=& JFactory::getDBO();
		$wuid=$_REQUEST['wuid'];
		$pid=$_REQUEST['pid'];
		$albumid=$_REQUEST['albumid'];
		require_once(JPATH_SITE . DS . 'components'.DS.'com_awdjomalbum'. DS.'js' . DS . 'include.php');
		if($albumid)
		{
$link 	=JRoute::_('index.php?option=com_awdjomalbum&view=awdimagelist&tmpl=component&pid='.$pid.'&albumid='.$albumid.'&Itemid='.$Itemid);
		}
		else
		{
		$link 	=JRoute::_("index.php?option=com_awdjomalbum&view=awdwallimagelist&tmpl=component&wuid=".$wuid."&pid=".$pid."&Itemid=".$Itemid);
		}
?>
<script type="text/javascript" language="javascript">
 jQuery(function() {
						var newurl='<?php echo $link ;?>';
						
						jQuery.colorbox({
							iframe:true, 
							width:"990px", 
							height:"550px", 
							href: newurl,
							scrolling: false,
							onLoad:function() {
								jQuery('html, body').css('overflow', 'hidden'); // page scrollbars off
							}, 
							onClose: function() {
        						window.location.href='<?php echo $albumlink;?>';
    						},
							onClosed:function() {
								jQuery('html, body').css('overflow', ''); // page scrollbars on
								window.location.href='<?php echo $albumlink;?>';
							}
						});
						//location.hash = '';
						//jQuery.colorbox({iframe: true, href: newurl, width: 990, height: 550,scrolling:false});
});
</script>
<?php
	parent::display();	
}
	// **************  function define for jomsocial toolbar start here **************

	function jomsocialtoolbar($template)

	{

		//Load Language file.
		
		$toolbarstyling = 'components/com_community/templates/'.$template.'/css/style.css';

		$lang =& JFactory::getLanguage();

		$lang->load( 'com_community' );

		require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');
		$my 	= CFactory::getUser();
		// initiate toolbar

		$customToolbar	=& CFactory::getToolbar();

		// get Jomsocial configuration

		$config	=& CFactory::getConfig();

		// Include CAppPlugins library

		require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'apps.php');

		$appsLib	=& CAppPlugins::getInstance();

		$appsLib->loadApplications();

		// Only trigger applications and set active URI when needed

		$args = array();

		$appsLib->triggerEvent( 'onSystemStart' , $args );

		// Set active URI

		CFactory::setCurrentURI();

		// Include templates

		require_once (JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries'.DS.'template.php');

		// Include templates

		require_once (JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries'.DS.'miniheader.php');

		require_once(JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'tooltip.php');

		require_once(JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'toolbar.php');

		// Script needs to be here if they are 

		CFactory::load( 'libraries' , 'facebook' );

		CFactory::load( 'models' , 'connect' );

		// Once they reach here, we assume that they are already logged into facebook.

		// Since CFacebook library handles the security we don't need to worry about any intercepts here.

		$facebook		= new CFacebook();
		$connectTable	=& JTable::getInstance( 'Connect' , 'CTable' );
		$fbUser			= $facebook->getUser();
		$connectTable->load( $fbUser );
		$isFacebookUser	= ( $connectTable->userid == $my->id );

		$logoutLink	= CRoute::_( 'index.php?option=com_community&view=frontpage' , false );

		$logoutLink	= base64_encode( $logoutLink );



		$document	= & JFactory::getDocument();

		if($toolbarstyling !== ""){$document->addStyleSheet( JURI::base() . $toolbarstyling );}			
		$document->addStyleSheet( JURI::base() . 'components/com_community/assets/autocomplete.css' );
		$document->addStyleSheet( JURI::base() . 'components/com_community/assets/window.css' );
		$document->addStyleSheet( JURI::base() . 'components/com_community/templates/default/css/style.green.css' );
		//$document->addScript( JURI::base() . 'components/com_community/assets/jquery-1.3.2.pack.js' );
		$document->addScript( JURI::base() . 'components/com_community/assets/joms.jquery.js' );
		$document->addScript( JURI::base() . 'components/com_community/assets/joms.ajax.js' );

		$document->addScript( JURI::base() . 'components/com_community/assets/window-1.0.pack.js' );	

		$document->addScript( JURI::base() . 'components/com_community/assets/script-1.2.pack.js' );

		//$document->addScript( JURI::base() . 'components/com_community/assets/jquery.qtip-1.0.0-rc3.min.js' );			


?>

		<div id="community-wrap" style="display:inline"> 

		<?php 
		$db =& JFactory::getDBO();
		$query	= 'SELECT  ' . $db->nameQuote( 'params' ) . ' FROM ' . $db->nameQuote( '#__community_config' ) . ' WHERE ' . $db->nameQuote( 'name' ) . ' = ' . $db->quote('config');
		$db->setQuery( $query );
		$row = $db->loadResult();		
		$params	= new JParameter( $row );
		$showToolbar = $params->get('showToolbar', '1');

		$xml=simplexml_load_file(JPATH_SITE . '/administrator/components/com_community/community.xml');
		$version=$xml->version;
		$version=str_replace('.','',$version);
 
 		if($version >= 224)
		{
			CFactory::load( 'libraries' , 'toolbar' );
			$toolbar_lib = CToolbarLibrary::getInstance();
			echo $toolbar_lib->getHTML(  );
		}
		else
		{
			CFactory::load( 'libraries' , 'toolbar' );
			echo CToolbarLibrary::getHTML(  );
		}
		
		$menus	= CToolbarLibrary::getItems();
		CToolbarLibrary::addLegacyToolbars( $menus );
		//$showToolbar=1;
		//print_r($menus);
		if(file_exists(JPATH_SITE . '/components/com_community/templates/'.$template.'/toolbar.index.php'))

		{

			require_once (JPATH_ROOT .'/components/com_community/templates/'.$template.'/toolbar.index.php');

		}

		else

		{

			require_once (JPATH_ROOT .'/components/com_community/templates/default/toolbar.index.php');

		}

		?>

		 </div>

	<?php }

	// ************** zippy code function jomsocialtoolbar  end here **************

	// ************** zippy code  end here **************

	// ************** zippy code  start here **************

	// **************  function define for jomsocial activity stream feed start here **************

	function deleteImage()
	{
		$mainframe= JFactory::getApplication();


		$db		=& JFactory :: getDBO();

		$user =& JFactory::getUser();

		$imageID=$_REQUEST['imageID'];

	 	$albumID=$_REQUEST['albumID'];

	 	if(!empty($imageID) && !empty($albumID))

		{
		

			$sql="Delete from #__awd_jomalbum_photos where id=".$imageID;
//echo "<script type='text/javascript'>alert('ok');<script>";
			$db->setQuery($sql); 

			$db->Query();

			

			$sql='Select * from #__awd_jomalbum_photos where albumid='.$albumID;

			$db->setQuery($sql);

			$photoRows=$db->loadObjectList();

			if(count($photoRows)>0){

		?>
	<table>
		<tr><td id="listofPhotos">

			<?php 

			$i=0;

			foreach($photoRows as $photoRow) {

			 $i++;

			$imgpath=JURI::base()."images/awd_photo/awd_thumb_photo/".$photoRow->image_name;

 			?>

			<div id="photoRowid<?php echo $i;?>"  style="float:left;padding:3px; margin:5px; border:1px dotted #999999;"><img src="<?php echo $imgpath; ?>" width="100" border="0" align="absmiddle" /><br />

			<a href="JavaScript:void(0);" onclick="deleteAlbumImages('<?php echo $photoRow->id;?>','photoRowid<?php echo $i;?>','<?php echo $albumID;?>');"><img src="<?php echo JURI::base()?>components/com_awdjomalbum/images/cancel_f2.png" align="right" /></a></div>

			<?php } ?>

		</td></tr>

		</table>

		<?php }  
		
			

		}
	 exit;
	}
	
	function getoldercomments()
	{
		$mainframe= JFactory::getApplication();

		$db		=& JFactory::getDBO();
		$user =& JFactory::getUser();
		$avatarTable='';
		
		$values=getCurrentUserDetails($user->id);
		
		 $imgPathCUser=$values[0];
		 $userprofileLinkCUser=$values[1];
		 $avatarTable=$values[2];
		
		
		$pid=$_REQUEST['id'];
		$query='SELECT userid FROM  #__awd_jomalbum_photos where id='.$pid;
	 	$db->setQuery($query);
		$userid=$db->loadResult();
		
		$limitstart=$_REQUEST['limitstart']+1;
		$commentqry='SELECT ajc.*, u.id as uid, u.name as cname,u.username  FROM  #__awd_jomalbum_comment as ajc left join #__users as u on ajc.userid=u.id where ajc.photoid='.$pid.' order by ajc.id desc limit '.$limitstart.' ,5';
		
	 	$db->setQuery($commentqry);
		$commentrows=$db->loadObjectList();
		$counter=$limitstart;
		foreach($commentrows as $commentrow) {
		 
			list($y, $m,$d) = explode('-', $commentrow->cdate);
			if($d!='')
			{
				$comment_date=strtotime($commentrow->cdate);
			}
			else
			{
				$comment_date=$commentrow->cdate;
			}
			$cDate=AwdwallHelperUser::getDisplayTime($comment_date);
			$uid=$commentrow->uid;
			
			$userprofileLinkAWD=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$uid.'&Itemid='.AwdwallHelperUser::getComItemId());
				
			$values1=getUserDetails($commentrow->userid,$avatarTable,$uid);
 		
			 $imgPath1=$values1[0];
			 $userprofileLink=$values1[1];
			 
			
			//$userprofileLink=$this->userprofileLink;
			//$imgPath1=$this->imgPath1;
			
			//if($counter>4) {$style='style="display:none"';}else{$style='';}   
		 ?> 
			<div class="commentBox2" id="new_comment_table<?php echo $counter; ?>" >
				<div id="avtar"><a href="<?php echo $userprofileLink; ?>"><img src="<?php echo $imgPath1;?>" border="0" height="32" width="32"  align="absmiddle" /></a></div>
				<div class="commentDetail " >
					<div style="margin-bottom:10px"><span class="cUser"><a href="<?php echo $userprofileLinkAWD; ?>" class="authorlink"><?php echo $commentrow->username;?></a></span> <span class="comments"><?php echo nl2br($commentrow->comments); ?></span></div>
					<div class="subcommentmenu"><span class="commentDate wall_date"><?php echo $cDate; ?></span> <?php /*?>- <span>
					<a href="JavaScript:void(0);" onclick="commentLike('<?php echo $commentrow->id; ?>','<?php echo $user->id;?>');"><?php echo JText::_('Like');?></a></span> <?php */?>
					- <span><a href="JavaScript:void(0);" onclick="reportComment('<?php echo $commentrow->id; ?>','<?php echo $user->id;?>');"><?php echo JText::_('Report');?></a></span> 
					<?php if($user->id==$userid) {?>
									
					- <span class="comment_del_but" id="<?php echo $counter; ?>" ><a href="JavaScript:void(0);"><?php echo JText::_('Delete');?></a></span>
					<?php } else if($user->id==$uid) { ?>
					- <span class="comment_del_but" id="<?php echo $counter; ?>" ><a href="JavaScript:void(0);"><?php echo JText::_('Delete');?></a></span>
				
				    <?php }?>
					</div>
					
					
				
				<input type="hidden" value="<?php echo $commentrow->id; ?>" id="hid<?php echo $counter; ?>" />
				
				</div> 
			</div>
			<div class="cDivider" id="new_comment_table1<?php echo $counter; ?>"></div>
			<div id="oldercommentDiv"></div>
		 <?php  $counter++;  }
		 exit;
	
	}
	
	function getolderwcomments()
	{
		$mainframe= JFactory::getApplication();

		$db		=& JFactory::getDBO();
		$user =& JFactory::getUser();
		$avatarTable='';
		
		$values=getCurrentUserDetails($user->id);
		
		 $imgPathCUser=$values[0];
		 $userprofileLinkCUser=$values[1];
		 $avatarTable=$values[2];
		
		
		$pid=$_REQUEST['id'];
		$limitstart=$_REQUEST['limitstart']+1;
		
		$query='SELECT wall_id FROM  #__awd_wall_images where id='.$pid;
	 	$db->setQuery($query);
		$wall_id=$db->loadResult();
		$query='SELECT commenter_id FROM  #__awd_wall where id='.$wall_id;
	 	$db->setQuery($query);
		$userid=$db->loadResult();
		
		$commentqry="select  ajwc.*, u.id as uid, u.name as cname,u.username  from   #__awd_wall as ajwc left join #__users as u on ajwc.commenter_id=u.id where reply='".$wall_id."' order by wall_date desc limit ".$limitstart." ,5";
		//$commentqry="select ajwc.*, u.id as uid, u.name as cname from #__awd_jomalbum_wall_comment as ajwc left join #__users as u on ajwc.userid=u.id where ajwc.photoid=".$pid." order by ajwc.id desc limit ".$limitstart." ,5";

	 	$db->setQuery($commentqry);
		$commentrows=$db->loadObjectList();
		$counter=$limitstart;
		foreach($commentrows as $commentrow) {
		 
		 	//$cDate=date("M d, Y  h:i a",strtotime($commentrow->wall_date));
			$cDate=AwdwallHelperUser::getDisplayTime($commentrow->wall_date);
			$uid=$commentrow->uid;
			
			$userprofileLinkAWD=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$uid.'&Itemid='.AwdwallHelperUser::getComItemId());
				
			$values1=getUserDetails($commentrow->commenter_id,$avatarTable,$uid);
 		
			 $imgPath1=$values1[0];
			 $userprofileLink=$values1[1];
			 
			
			//$userprofileLink=$this->userprofileLink;
			//$imgPath1=$this->imgPath1;
			
			   
		 ?> 
			<div class="commentBox2" id="new_comment_table<?php echo $counter; ?>">
				<div id="avtar"><a href="<?php echo $userprofileLink; ?>"><img src="<?php echo $imgPath1;?>" border="0" height="32" width="32"  align="absmiddle" /></a></div>
				<div class="commentDetail" >
					<div style="margin-bottom:10px"><span class="cUser"><a href="<?php echo $userprofileLinkAWD; ?>"><?php echo $commentrow->username;?></a></span> <span class="comments"><?php echo nl2br($commentrow->message); ?></span></div>
					<div class="subcommentmenu"><span class="commentDate wall_date"><?php echo $cDate; ?></span><?php /*?> - <span>
					<a href="JavaScript:void(0);" onclick="commentLike('<?php echo $commentrow->id; ?>','<?php echo $user->id;?>');"><?php echo JText::_('Like');?></a></span> <?php */?>- <span><a href="JavaScript:void(0);" onclick="reportComment('<?php echo $commentrow->id; ?>','<?php echo $user->id;?>');"><?php echo JText::_('Report');?></a></span> 				
					<?php if($user->id==$userid) {?>
					- <span class="comment_del_but" id="<?php echo $counter; ?>" ><a href="JavaScript:void(0);"><?php echo JText::_('Delete');?></a></span>
					<?php } else if($user->id==$uid) { ?>
					- <span class="comment_del_but" id="<?php echo $counter; ?>" ><a href="JavaScript:void(0);"><?php echo JText::_('Delete');?></a></span>
				
				    <?php }?>
					</div>
					
					
				
				</div> 
			</div>
			<div class="cDivider" id="new_comment_table1<?php echo $counter; ?>"></div>
		 <?php  $counter++;  }
		 exit;
	
	}
	
	function report()
	{
		$mainframe= JFactory::getApplication();

		$db		=& JFactory::getDBO();
		$user =& JFactory::getUser();
		
		$commentId=$_REQUEST['commentID'];
		$uID=$_REQUEST['uID'];
		
		$sql="Select ajc.*,u.username,u.email from #__awd_jomalbum_comment as ajc left join #__users as u on ajc.userid=u.id where ajc.id=$commentId";
		//echo $sql;
		$db->setQuery($sql);
		$rows=$db->loadObjectList();
		$row=$rows[0];
		// print_r($row);
		 $comments=$row->comments;
		$toUseremail=$row->email;
		$toUser=$row->username;
		$fromUser=$user->username; 
		
		$MailFrom 	= $fromUser;
		$sender	= $mainframe->getCfg('mailfrom');
		$recipient=$toUseremail;
		
		$receiverId=$row->userid;
		$sql="Select albumid from #__awd_jomalbum_photos where id=".$row->photoid;
		//echo $sql;
		$db->setQuery($sql);
		$albumid=$db->loadResult();

	  // mail to user
		// SENDING EMAILS mail to user
		$itemId = AwdwallHelperUser::getComItemId();	
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$displayName 	= $config->get('display_name', 1);
		$email_auto=$config->get('email_auto', 0);
		if($email_auto)
		{
			if($user->id!=$receiverId)
			{

$receiver = &JFactory::getUser($receiverId);
$rName =  AwdwallHelperUser::getDisplayName($receiverId);
$sName =  AwdwallHelperUser::getDisplayName($user->id);	

$photolink=JURI::base().'index.php?option=com_awdjomalbum&task=showlightboxview&pid='.$row->photoid.'&albumid='.$albumid.'&Itemid='.$itemId;			
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_ALBUM_EMAIL_GREETING', $rName);
$subject=$mainframe->getCfg('fromname').' - '.JText::sprintf('COM_ALBUM_EMAIL_SUBJECT_NEW_REPORT', $sName);

$emailbody=JText::sprintf('COM_ALBUM_EMAIL_NEW_REPORT_BODY',$sName,$photolink);	

$emailfooter=JText::sprintf('COM_ALBUM_EMAIL_FOOTER',$siteaddress,$sitename);	

$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.$itemId;
$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$sName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
				$body = $emailcontent;				 
				$mailer = & JFactory::getMailer();
				$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
				$mailer->setSubject($subject);
				$mailer->setBody($body);
				$mailer->IsHTML(1);
				$mailer->addRecipient($receiver->email);
				$rs = $mailer->Send();		
			}
		}
	  
	  
	  
	  //$subject="You have got an abuse report from user ".$MailFrom;
//		$subject=JText::_('You have got an abuse report from user').'&nbsp;'.$MailFrom;
//		$body=JText::_('Hello').'&nbsp;'.$toUser.'&nbsp;'.JText::_('you have got an abuse report from').'&nbsp;'.$MailFrom.'&nbsp;'.JText::_('on comment').'&nbsp;'.$comments;	
//		//echo $body;
//		$mailer = & JFactory::getMailer();
//		$mailer->setSender($sender);
//		$mailer->setSubject($subject);
//		$mailer->setBody($body);
//		$mailer->IsHTML(1);
//		$mailer->addRecipient($recipient);
//		$send =& $mailer->Send();
		
		//mail to administrator
		
		
		if($email_auto)
		{
$sender	= $mainframe->getCfg('mailfrom');
$rName= $mainframe->getCfg('fromname');
$sName =  AwdwallHelperUser::getDisplayName($user->id);	

$photolink=JURI::base().'index.php?option=com_awdjomalbum&task=showlightboxview&pid='.$row->photoid.'&albumid='.$albumid.'&Itemid='.$itemId;			
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_ALBUM_EMAIL_GREETING', $rName);
$subject=$mainframe->getCfg('fromname').' Notification - '.JText::sprintf('COM_ALBUM_EMAIL_SUBJECT_NEW_REPORT', $sName);

$emailbody=JText::sprintf('COM_ALBUM_EMAIL_NEW_REPORT_BODY',$sName,$photolink);	

$emailfooter=JText::sprintf('COM_ALBUM_EMAIL_FOOTER',$siteaddress,$sitename);	

$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.$itemId;
$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$sName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
				$body = $emailcontent;				 
				$mailer = & JFactory::getMailer();
				$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
				$mailer->setSubject($subject);
				$mailer->setBody($body);
				$mailer->IsHTML(1);
				$mailer->addRecipient($sender);
				$rs = $mailer->Send();		
		}
		
//		$toName 	= $mainframe->getCfg('fromname');
//		$subject=JText::_('You have got an abuse report from user').'&nbsp;'.$MailFrom;
//		
//		$body=JText::_('Hello').'&nbsp;'.$toName.'&nbsp;'.JText::_('you have got an abuse report from').'&nbsp;'.$MailFrom.'&nbsp;'.JText::_('on comment').'&nbsp;'.$comments;	
//		//echo $body;
//		$mailer = & JFactory::getMailer();
//		$mailer->setSender($sender);
//		$mailer->setSubject($subject);
//		$mailer->setBody($body);
//		$mailer->IsHTML(1);
//		$mailer->addRecipient($sender);
//		$send =& $mailer->Send();
		echo JText::_('Mail sent successfully to the user'); 
		exit();
	}
	
	function reportwallcomment()
	{
	$mainframe= JFactory::getApplication();

		$db		=& JFactory::getDBO();
		$user =& JFactory::getUser();
		
		$commentId=$_REQUEST['commentID'];
		$uID=$_REQUEST['uID'];
		
		$sql="Select ajc.*,u.username,u.email from #__awd_wall as ajc left join #__users as u on ajc.user_id=u.id where ajc.id=$commentId";
		//echo $sql;exit;
		$db->setQuery($sql);
		$rows=$db->loadObjectList();
		$row=$rows[0];
		$comments=$row->message;
		$toUser=$row->username;
		$fromUser=$user->username; 
		$toUseremail=$row->email;

		$MailFrom 	= $fromUser;
		$sender	= $mainframe->getCfg('mailfrom');
		$recipient=$toUseremail;
		
		$receiverId=$row->commenter_id;
		$wallId=$row->id;
		
		$sql="Select id from #__awd_wall_images where wall_id=".$wallId;
		//echo $sql;
		$db->setQuery($sql);
		$photoid=$db->loadResult();
	  // mail to user
		// SENDING EMAILS mail to user
		$itemId = AwdwallHelperUser::getComItemId();	
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$displayName 	= $config->get('display_name', 1);
		$email_auto=$config->get('email_auto', 0);
		if($email_auto)
		{
			if($user->id!=$receiverId)
			{

$receiver = &JFactory::getUser($receiverId);
$rName =  AwdwallHelperUser::getDisplayName($receiverId);
$sName =  AwdwallHelperUser::getDisplayName($user->id);	

$photolink=JURI::base().'index.php?option=com_awdjomalbum&task=showlightboxview&pid='.$photoid.'&albumid='.$receiverId.'&Itemid='.$itemId;			
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_ALBUM_EMAIL_GREETING', $rName);
$subject=$mainframe->getCfg('fromname').' Notification - '.JText::sprintf('COM_ALBUM_EMAIL_SUBJECT_NEW_REPORT', $sName);

$emailbody=JText::sprintf('COM_ALBUM_EMAIL_NEW_REPORT_BODY',$sName,$photolink);	

$emailfooter=JText::sprintf('COM_ALBUM_EMAIL_FOOTER',$siteaddress,$sitename);	

$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.$itemId;
$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$sName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
				$body = $emailcontent;				 
				$mailer = & JFactory::getMailer();
				$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
				$mailer->setSubject($subject);
				$mailer->setBody($body);
				$mailer->IsHTML(1);
				$mailer->addRecipient($receiver->email);
				$rs = $mailer->Send();		
			}
		}
	  
	  
//		$subject=JText::_('You have got an abuse report from user').'&nbsp;'.$MailFrom;
//		
//		$body=JText::_('Hello').'&nbsp;'.$toUser.'&nbsp;'.JText::_('you have got an abuse report from').'&nbsp;'.$MailFrom.'&nbsp;'.JText::_('on comment').'&nbsp;'.$comments;	
//		//echo $body;
//		$mailer = & JFactory::getMailer();
//		$mailer->setSender($sender);
//		$mailer->setSubject($subject);
//		$mailer->setBody($body);
//		$mailer->IsHTML(1);
//		$mailer->addRecipient($recipient);
//		$send =& $mailer->Send();
		
		//mail to administrator
		if($email_auto)
		{
$sender	= $mainframe->getCfg('mailfrom');
$rName= $mainframe->getCfg('fromname');
$sName =  AwdwallHelperUser::getDisplayName($user->id);	

$photolink=JURI::base().'index.php?option=com_awdjomalbum&task=showlightboxview&pid='.$photoid.'&albumid='.$receiverId.'&Itemid='.$itemId;			
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_ALBUM_EMAIL_GREETING', $rName);
$subject=$mainframe->getCfg('fromname').' Notification - '.JText::sprintf('COM_ALBUM_EMAIL_SUBJECT_NEW_REPORT', $sName);

$emailbody=JText::sprintf('COM_ALBUM_EMAIL_NEW_REPORT_BODY',$sName,$photolink);	

$emailfooter=JText::sprintf('COM_ALBUM_EMAIL_FOOTER',$siteaddress,$sitename);	

$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.$itemId;
$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$sName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
				$body = $emailcontent;				 
				$mailer = & JFactory::getMailer();
				$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
				$mailer->setSubject($subject);
				$mailer->setBody($body);
				$mailer->IsHTML(1);
				$mailer->addRecipient($sender);
				$rs = $mailer->Send();		
		}
		
//		$toName 	= $mainframe->getCfg('fromname');
//		$subject=JText::_('You have got an abuse report from user').'&nbsp;'.$MailFrom;
//		
//		$body=JText::_('Hello').'&nbsp;'.$toName.'&nbsp;'.JText::_('you have got an abuse report from').'&nbsp;'.$MailFrom.'&nbsp;'.JText::_('on comment').'&nbsp;'.$comments;	
//		//echo $body;
//		$mailer = & JFactory::getMailer();
//		$mailer->setSender($sender);
//		$mailer->setSubject($subject);
//		$mailer->setBody($body);
//		$mailer->IsHTML(1);
//		$mailer->addRecipient($sender);
//		$send =& $mailer->Send();
//		echo JText::_('Mail sent successfully to the user'); 
		exit();
	}

	function addtag()
	{
		require_once (JPATH_SITE. DS .'components'.DS.'com_awdwall'.DS. 'models' . DS . 'wall.php');
		$mainframe= JFactory::getApplication();
        $option = JRequest::getCmd('option');

		$db		=& JFactory::getDBO();
		$user =& JFactory::getUser();
		$userid		=$user->id;	
		$photoid=$_REQUEST['photoid'];
		$tagValue=$_REQUEST['tagValue'];
		$targetX=$_REQUEST['targetX'];
		$targetY=$_REQUEST['targetY'];
		$taguserid=$_REQUEST['taguserid'];
		$today=date("Y-m-d h:m:s",time()); 
		if($taguserid)
		{
		$qry = "INSERT INTO  #__awd_jomalbum_tags(photoid, userid, tagValue,targetX,targetY,taguserid) VALUES ('$photoid',  '$user->id','$tagValue','$targetX','$targetY',$taguserid )";
		
		$query = 'SELECT * FROM #__awd_jomalbum_photos where id='.$photoid;
		$db->setQuery($query);
		$photodetails=$db->loadObjectList();
		$otheruser=JFactory::getUser($taguserid);
		$albumuser=JFactory::getUser($photodetails->userid);
			//$photolink=JRoute::_('index.php?option=com_awdjomalbum&view=awdimagelist&pid='.$photoid.'&albumid='.$photodetails[0]->albumid.'&Itemid='.AwdwallHelperUser::getComItemId(),false);
			$photolink='index.php?option=com_awdjomalbum&view=awdimagelist&pid='.$photoid.'&albumid='.$photodetails[0]->albumid.'&Itemid='.AwdwallHelperUser::getComItemId();
			$imagelist='<div style="height:75px; width:75px; padding:3px;"><a href="'.$photolink.'"><img src="images/awd_photo/awd_thumb_photo/'.$photodetails[0]->image_name.'" height="72" width="72" border="0" /></a></div>';
			//$albumuserwallurl=JRoute::_('index.php?option=com_awdwall&view=mywall&wuid='.$albumuser->id.'&Itemid='.AwdwallHelperUser::getComItemId(),false);
			$albumuserwallurl='index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$albumuser->id.'&Itemid='.AwdwallHelperUser::getComItemId();
		if($photodetails[0]->userid==$taguserid)
		{
			$wmsg=JText::_('was tagged on his').'&nbsp;<a href="'.$photolink.'" class="awdiframe">'.JText::_('photo').'</a>'.'<div style="clear:both"></div>';
		}
		else
		{
			$wmsg=JText::_('was tagged in') .'&nbsp; <a href="'.$albumuserwallurl.'" >'.$albumuser->username.'</a>'.JText::_("' s").'&nbsp;<a href="'.$photolink.'"  class="awdiframe" >'.JText::_('photo').'</a>'.'<div style="clear:both"></div>';
		}
		//	$wmsg=JText::_('was tagged in ') .' <a href="'.$albumuserwallurl.'">'.$albumuser->username.'</a>'.JText::_("' s ").'<a href="'.$photolink.'">'.JText::_('photo').'</a>'.'<div style="clear:both"></div>';
			
			$wall 				=& JTable::getInstance('Wall', 'Table');									
			$wall->user_id		= '';			
			$wall->group_id		= $groupId;			
			$wall->type			= $type;			
			$wall->commenter_id	= $otheruser->id;			
			$wall->user_name	= '';			
			$wall->avatar		= '';			
			$wall->message		= nl2br($wmsg);			
			$wall->reply		= 0;			
			$wall->is_read		= 0;			
			$wall->is_pm		= 0;			
			$wall->is_reply		= 0;			
			$wall->posted_id	= NULL;			
			$wall->wall_date	= time();			
			$wall->type = JRequest::getString('type', 'tag');	
			$wall->store();	
			
			//insert into awd_wall_notification table.
			$ndate=date("Y-m-d H:i:s");
			$query = 'INSERT INTO #__awd_wall_notification(ndate, nuser, ncreator, ntype, nwallid, ngroupid, nphotoid, nalbumid, nread) VALUES( "'.$ndate.'" , "' . $taguserid . '", "' . $user->id . '", "tag", "' . $wall->id . '", "' . $groupId . '"," '.$photoid.'", "'.$photodetails[0]->albumid.'","0")';
			$db->setQuery($query);

			$db->query();
			
		// SENDING EMAILS
		$itemId = AwdwallHelperUser::getComItemId();	
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$displayName 	= $config->get('display_name', 1);
		$email_auto=$config->get('email_auto', 0);
		if($email_auto)
		{
			if($user->id!=$taguserid)
			{

$receiver = &JFactory::getUser($taguserid);
$rName =  AwdwallHelperUser::getDisplayName($taguserid);
$sName =  AwdwallHelperUser::getDisplayName($user->id);
$photolink=JURI::base().'index.php?option=com_awdjomalbum&task=showlightboxview&pid='.$photoid.'&albumid='.$photodetails[0]->albumid.'&Itemid='.$itemId;			
	
//$photolink=JURI::base().'index.php?option=com_awdjomalbum&task=showlightboxview&pid='.$photoid.'&wuid='.$userid.'&Itemid='.$itemId;
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_ALBUM_EMAIL_GREETING', $rName);
$subject=$mainframe->getCfg('fromname').' Notification - '.JText::sprintf('COM_ALBUM_EMAIL_SUBJECT_NEW_TAG', $sName);

$emailbody=JText::sprintf('COM_ALBUM_EMAIL_NEW_POST_TAGED_BODY',$sName,$photolink);	

$emailfooter=JText::sprintf('COM_ALBUM_EMAIL_FOOTER',$siteaddress,$sitename);	

$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.$itemId;
$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$sName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
				$body = $emailcontent;				 
				$mailer = & JFactory::getMailer();
				$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
				$mailer->setSubject($subject);
				$mailer->setBody($body);
				$mailer->IsHTML(1);
				$mailer->addRecipient($receiver->email);
				$rs = $mailer->Send();		
			}
		}
		// AUP POINTS
		if($taguserid!=$user->id)
		{
			$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
			if ( file_exists($api_AUP)){				
				require_once ($api_AUP);
				$keyreference  = AlphaUserPointsHelper::buildKeyreference('plgaup_points4jomwalltag', $photoid );
				 AlphaUserPointsHelper::newpoints('plgaup_points4jomwalltag','', $keyreference);
			}
		}
			
		}
		else
		{
		$qry = "INSERT INTO  #__awd_jomalbum_tags(photoid, userid, tagValue,targetX,targetY) VALUES ('$photoid',  '$user->id','$tagValue','$targetX','$targetY' )";
		}
		//echo $qry;
		$db->setQuery( $qry);
		if (!$db->query()) {
		return JError::raiseWarning( 500, $db->getError() );
		}
		//mysql_insert_id();
		$tagid=$db->insertid();
		echo $tagid;
		
		exit;
	}	
	function deletetag()
	{
		$mainframe= JFactory::getApplication();
 		$option = JRequest::getCmd('option');

		$db		=& JFactory::getDBO();
		$user =& JFactory::getUser();
		$userid		=$user->id;	
		$id=$_REQUEST['id'];
		
		$qry = "delete from  #__awd_jomalbum_tags where id=".$id;
		//echo $qry;
		$db->setQuery( $qry);
		if (!$db->query()) {
		return JError::raiseWarning( 500, $db->getError() );
		}
		
		exit;
	}	
	function addwalltag()
	{
		require_once (JPATH_SITE. DS .'components'.DS.'com_awdwall'.DS. 'models' . DS . 'wall.php');
		$mainframe= JFactory::getApplication();
 		$option = JRequest::getCmd('option');

		$db		=& JFactory::getDBO();
		$user =& JFactory::getUser();
		$userid		=$user->id;	
		$photoid=$_REQUEST['photoid'];
		$tagValue=$_REQUEST['tagValue'];
		$targetX=$_REQUEST['targetX'];
		$targetY=$_REQUEST['targetY'];
		$taguserid=$_REQUEST['taguserid'];
		$today=date("Y-m-d h:m:s",time()); 
		
		if($taguserid)
		{
		$qry = "INSERT INTO  #__awd_jomalbum_wall_tags(photoid, userid, tagValue,targetX,targetY,taguserid) VALUES ('$photoid',  '$user->id','$tagValue','$targetX','$targetY',$taguserid )";
		
		$query = 'SELECT * FROM #__awd_wall_images where id='.$photoid;
		$db->setQuery($query);
		$photodetails=$db->loadObjectList();
		$query = 'SELECT commenter_id FROM #__awd_wall where id='.$photodetails[0]->wall_id;
		$db->setQuery($query);
		$commenter_id=$db->loadResult();
		$imgpath=JURI::base()."images/".$commenter_id."/thumb/".$photodetails[0]->path;
		$otheruser=JFactory::getUser($taguserid);
		$photouser=JFactory::getUser($commenter_id);
			//$photolink=JRoute::_('index.php?option=com_awdjomalbum&view=awdwallimagelist&wuid='.$commenter_id.'&pid='.$photoid.'&Itemid='.AwdwallHelperUser::getComItemId(),false);
			$photolink='index.php?option=com_awdjomalbum&view=awdwallimagelist&wuid='.$commenter_id.'&pid='.$photoid.'&Itemid='.AwdwallHelperUser::getComItemId();
			$imagelist='<div style="height:75px; width:75px; padding:3px;"><a href="'.$photolink.'"><img src="'.$imgpath.'" height="72" width="72" border="0" /></a></div>';
			//$albumuserwallurl=JRoute::_('index.php?option=com_awdwall&view=mywall&wuid='.$photouser->id.'&Itemid='.AwdwallHelperUser::getComItemId(),false);
			$albumuserwallurl='index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$photouser->id.'&Itemid='.AwdwallHelperUser::getComItemId();
			if($commenter_id==$taguserid)
			{
				$wmsg=JText::_('was tagged on his').'&nbsp;<a href="'.$photolink.'"  class="awdiframe">'.JText::_('photo').'</a>'.'<div style="clear:both"></div>';
			}
			else
			{
			$wmsg=JText::_('was tagged in') .'&nbsp; <a href="'.$albumuserwallurl.'">'.$photouser->username.'</a>'.JText::_("' s").'&nbsp;<a href="'.$photolink.'" class="awdiframe" >'.JText::_('photo').'</a>'.'<div style="clear:both"></div>';
			}
//			$wmsg=JText::_('was tagged in ') .' <a href="'.$albumuserwallurl.'">'.$photouser->username.'</a>'.JText::_("' s ").'<a href="'.$photolink.'">'.JText::_('photo').'</a>'.'<div style="clear:both"></div>';
			
			$wall 				=& JTable::getInstance('Wall', 'Table');									
			$wall->user_id		= '';			
			$wall->group_id		= $groupId;			
			$wall->type			= $type;			
			$wall->commenter_id	= $otheruser->id;			
			$wall->user_name	= '';			
			$wall->avatar		= '';			
			$wall->message		= nl2br($wmsg);			
			$wall->reply		= 0;			
			$wall->is_read		= 0;			
			$wall->is_pm		= 0;			
			$wall->is_reply		= 0;			
			$wall->posted_id	= NULL;			
			$wall->wall_date	= time();			
			$wall->type = JRequest::getString('type', 'tag');	
			$wall->store();	
			
			//insert into awd_wall_notification table.
			$ndate=date("Y-m-d H:i:s");
			$query = 'INSERT INTO #__awd_wall_notification(ndate, nuser, ncreator, ntype, nwallid, ngroupid, nphotoid, nalbumid, nread) VALUES( "'.$ndate.'" , "' . $taguserid . '", "' . $user->id . '", "tag", "' . $wall->id . '", "' . $groupId . '"," '.$photoid.'", "","0")';
			$db->setQuery($query);

			$db->query();
			//echo $query;exit;	
		// SENDING EMAILS
		$itemId = AwdwallHelperUser::getComItemId();	
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$displayName 	= $config->get('display_name', 1);
		$email_auto=$config->get('email_auto', 0);
		if($email_auto)
		{
			if($user->id!=$taguserid)
			{

$receiver = &JFactory::getUser($taguserid);
$rName =  AwdwallHelperUser::getDisplayName($taguserid);
$sName =  AwdwallHelperUser::getDisplayName($user->id);
//$photolink=JURI::base().'index.php?option=com_awdjomalbum&task=showlightboxview&pid='.$photoid.'&albumid='.$photodetails[0]->albumid.'&Itemid='.$itemId;			
	
$photolink=JURI::base().'index.php?option=com_awdjomalbum&task=showlightboxview&pid='.$photoid.'&wuid='.$commenter_id.'&Itemid='.$itemId;
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_ALBUM_EMAIL_GREETING', $rName);
$subject=$mainframe->getCfg('fromname').' Notification - '.JText::sprintf('COM_ALBUM_EMAIL_SUBJECT_NEW_TAG', $sName);

$emailbody=JText::sprintf('COM_ALBUM_EMAIL_NEW_POST_TAGED_BODY',$sName,$photolink);	

$emailfooter=JText::sprintf('COM_ALBUM_EMAIL_FOOTER',$siteaddress,$sitename);	

$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.$itemId;
$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$sName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
				$body = $emailcontent;				 
				$mailer = & JFactory::getMailer();
				$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
				$mailer->setSubject($subject);
				$mailer->setBody($body);
				$mailer->IsHTML(1);
				$mailer->addRecipient($receiver->email);
				$rs = $mailer->Send();		
			}
		}
			// AUP POINTS
			if($taguserid!=$user->id)
			{
				$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
				if ( file_exists($api_AUP)){				
					require_once ($api_AUP);
					$keyreference  = AlphaUserPointsHelper::buildKeyreference('plgaup_points4jomwalltag', $photoid );
					AlphaUserPointsHelper::newpoints('plgaup_points4jomwalltag','', $keyreference);
				}
			}
			
		}
		else
		{
		$qry = "INSERT INTO  #__awd_jomalbum_wall_tags(photoid, userid, tagValue,targetX,targetY) VALUES ('$photoid',  '$user->id','$tagValue','$targetX','$targetY' )";
		}
		
		//echo $qry;
		$db->setQuery( $qry);
		if (!$db->query()) {
		return JError::raiseWarning( 500, $db->getError() );
		}
		//mysql_insert_id();
		$tagid=$db->insertid();
		echo $tagid;
		
		exit;
	}	
	function deletewalltag()
	{
		$mainframe= JFactory::getApplication();
 		$option = JRequest::getCmd('option');
		$db		=& JFactory::getDBO();
		$user =& JFactory::getUser();
		$userid		=$user->id;	
		$id=$_REQUEST['id'];
		
		$qry = "delete from  #__awd_jomalbum_wall_tags where id=".$id;
		//echo $qry;
		$db->setQuery( $qry);
		if (!$db->query()) {
		return JError::raiseWarning( 500, $db->getError() );
		}
		
		exit;
	}	
	
	
	function deletecomment()
	{
		$mainframe= JFactory::getApplication();
 		$option = JRequest::getCmd('option');
		$db		=& JFactory::getDBO();
/*		$id=$_REQUEST['id'];
*/		$id=$_REQUEST['commentid'];
		
	$delqry = 'DELETE FROM  #__awd_jomalbum_comment where id='.$id;
			//echo $phoqry;
			$db->setQuery($delqry);
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}	
		exit;
/*	$this->setRedirect( 'index.php?option=com_awdjomalbum&view=awdalbumlist&layout=displayimage&albumid='.$_REQUEST['albumid'].'&photoid='.$_REQUEST['photoid']);	*/
						
	}
	
	
	function savecomment()
	{
		$mainframe= JFactory::getApplication();
 		$option = JRequest::getCmd('option');
		//$userid		= JRequest::getVar( 'id' );	
		 $photoid=$_REQUEST['photoid'];
		//$comment=$_REQUEST['comment'];
		 $comment= JRequest::getString('comment');
		$today=date("Y-m-d h:m:s",time()); 
		$today=time(); 
	 $user =& JFactory::getUser();
	$db		=& JFactory::getDBO();
	$comment = $this->formatUrlInMsg($comment);	
	
	$qry = "INSERT INTO  #__awd_jomalbum_comment(id, photoid, comments, userid,cdate) VALUES ('', '$photoid', '".mysql_real_escape_string($comment)."', '$user->id','$today' )";
	//echo $qry;
		
		$db->setQuery( $qry);
		if (!$db->query()) {
		return JError::raiseWarning( 500, $db->getError() );
		}
		//mysql_insert_id();
		$commentid=$db->insertid();
		
		$query="select userid, albumid from #__awd_jomalbum_photos where id=".$photoid;
		$db->setQuery($query);
		$photodetails = $db->loadObjectList();
		$photodetail =$photodetails[0];
		
		//insert into awd_wall_notification table.
		$ndate=date("Y-m-d H:i:s");
		
		$query = 'INSERT INTO #__awd_wall_notification(ndate, nuser, ncreator, ntype, nwallid, ngroupid, nphotoid, nalbumid, nread) VALUES( "'.$ndate.'" , "'.$photodetail->userid.'", "' . $user->id . '", "text", "", ""," '.$photoid.'", "'.$photodetail->albumid.'","0")';
		$db->setQuery($query);
		$db->query();
		
		// SENDING EMAILS
		$itemId = AwdwallHelperUser::getComItemId();	
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$displayName 	= $config->get('display_name', 1);
		$email_auto=$config->get('email_auto', 0);
		if($email_auto)
		{
			if($user->id!=$photodetail->userid)
			{

$receiver = &JFactory::getUser($photodetail->userid);
$rName =  AwdwallHelperUser::getDisplayName($photodetail->userid);
$sName =  AwdwallHelperUser::getDisplayName($user->id);	

$photolink=JURI::base().'index.php?option=com_awdjomalbum&task=showlightboxview&pid='.$photoid.'&albumid='.$photodetail->albumid.'&Itemid='.$itemId;			
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_ALBUM_EMAIL_GREETING', $rName);
$subject=$mainframe->getCfg('fromname').' Notification - '.JText::sprintf('COM_ALBUM_EMAIL_SUBJECT_NEW_COMMENT', $sName);

$emailbody=JText::sprintf('COM_ALBUM_EMAIL_NEW_POST_COMMENT_BODY',$sName,$photolink);	

$emailfooter=JText::sprintf('COM_ALBUM_EMAIL_FOOTER',$siteaddress,$sitename);	

$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.$itemId;
$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$sName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
				$body = $emailcontent;				 
				$mailer = & JFactory::getMailer();
				$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
				$mailer->setSubject($subject);
				$mailer->setBody($body);
				$mailer->IsHTML(1);
				$mailer->addRecipient($receiver->email);
				$rs = $mailer->Send();		
			}
		}
				// AUP POINTS
				if($photodetail->userid!=$user->id)
				{
					$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
					if ( file_exists($api_AUP)){				
						require_once ($api_AUP);
						$keyreference  = AlphaUserPointsHelper::buildKeyreference('plgaup_points4jomwallphotocomment', $photoid );
						 AlphaUserPointsHelper::newpoints('plgaup_points4jomwallphotocomment','', $keyreference);
					}
				}
		
		echo nl2br(AwdwallHelperUser::showSmileyicons($comment)).'^'.$commentid; 
		
		exit; 
 
	}
	
	function deletewallcomment()
	{
		$mainframe= JFactory::getApplication();
 		$option = JRequest::getCmd('option');
		$db		=& JFactory::getDBO();
/*		$id=$_REQUEST['id'];
*/		$id=$_REQUEST['commentid'];
		
	$delqry = 'DELETE FROM  #__awd_wall where id='.$id;
			//echo $phoqry;
			$db->setQuery($delqry);
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}	
		exit;
 
						
	}
	
	function savewallcomment()
	{
		$mainframe= JFactory::getApplication();
 		$option = JRequest::getCmd('option');
		$user =& JFactory::getUser();
		$db		=& JFactory::getDBO();
		
		$photoid=$_REQUEST['photoid'];
		//$comment=$_REQUEST['comment'];
		 $comment= JRequest::getString('comment');

		$comment = $this->formatUrlInMsg($comment);	

		$query='SELECT wall_id FROM  #__awd_wall_images where id='.$photoid;
		$db->setQuery($query);
		$wall_id=$db->loadResult();
		$query='SELECT commenter_id FROM  #__awd_wall where id='.$wall_id;
	 	$db->setQuery($query);
		$userid=$db->loadResult();
		 
		$today=date("Y-m-d h:m:s",time()); 


$qry = "INSERT INTO #__awd_wall (`user_id`, `group_id`, `type`, `commenter_id`, `user_name`, `avatar`, `message`, `reply`, `is_read`, `is_pm`, `is_reply`, `posted_id`, `wall_date`) VALUES
(".$userid.", NULL, 'text', ".$user->id.", '', '', '".mysql_real_escape_string($comment)."', ".$wall_id.", 0, 0, 0, NULL, '".time()."')";

//echo $qry ;
	//$qry = "INSERT INTO  #__awd_jomalbum_wall_comment(id, photoid, comments, userid) VALUES ('', '$photoid', '$comment', '$user->id' )";
	//echo $qry;
		
		$db->setQuery( $qry);
		if (!$db->query()) {
		return JError::raiseWarning( 500, $db->getError() );
		}
		//mysql_insert_id();
		$commentid=$db->insertid();
		
		//insert into awd_wall_notification table.
		$ndate=date("Y-m-d H:i:s");
		$query = 'INSERT INTO #__awd_wall_notification(ndate, nuser, ncreator, ntype, nwallid, ngroupid, nphotoid, nalbumid, nread) VALUES( "'.$ndate.'" , "'.$userid.'", "' . $user->id . '", "text", "'.$commentid.'", ""," '.$photoid.'", "","0")';
		$db->setQuery($query);
		$db->query();
		// SENDING EMAILS
		$itemId = AwdwallHelperUser::getComItemId();	
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$displayName 	= $config->get('display_name', 1);
		$email_auto=$config->get('email_auto', 0);
		if($email_auto)
		{
			if($user->id!=$userid)
			{

$receiver = &JFactory::getUser($userid);
$rName =  AwdwallHelperUser::getDisplayName($userid);
$sName =  AwdwallHelperUser::getDisplayName($user->id);	
$photolink=JURI::base().'index.php?option=com_awdjomalbum&task=showlightboxview&pid='.$photoid.'&wuid='.$userid.'&Itemid='.$itemId;
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_ALBUM_EMAIL_GREETING', $rName);
$subject=$mainframe->getCfg('fromname').' Notification - '.JText::sprintf('COM_ALBUM_EMAIL_SUBJECT_NEW_COMMENT', $sName);

$emailbody=JText::sprintf('COM_ALBUM_EMAIL_NEW_POST_COMMENT_BODY',$sName,$photolink);	

$emailfooter=JText::sprintf('COM_ALBUM_EMAIL_FOOTER',$siteaddress,$sitename);	

$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.$itemId;
$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$sName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
				$body = $emailcontent;				 
				$mailer = & JFactory::getMailer();
				$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
				$mailer->setSubject($subject);
				$mailer->setBody($body);
				$mailer->IsHTML(1);
				$mailer->addRecipient($receiver->email);
				$rs = $mailer->Send();		
			}
		}
		// AUP POINTS
		if($userid!=$user->id)
		{
			$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
			if ( file_exists($api_AUP)){				
				require_once ($api_AUP);
				 
				$keyreference  = AlphaUserPointsHelper::buildKeyreference('plgaup_points4jomwallphotocomment', $photoid );
				 AlphaUserPointsHelper::newpoints('plgaup_points4jomwallphotocomment','', $keyreference);
				 
			}
		}
		
		echo nl2br(AwdwallHelperUser::showSmileyicons($comment)).'^'.$commentid; 
		
		exit; 
 
	}
	
	
	function deletealbum()
	{
		$mainframe= JFactory::getApplication();
 		$option = JRequest::getCmd('option');
		$db		=& JFactory::getDBO();
		$albumid=$_REQUEST['id'];
		$user =& JFactory::getUser();
		
		$phoqry = 'DELETE FROM  #__awd_jomalbum_photos where albumid='.$albumid;
		//echo $phoqry;
		$db->setQuery($phoqry);
		if (!$db->query()) {
		return JError::raiseWarning( 500, $db->getError() );
		}	
						
		$qry = 'DELETE FROM  #__awd_jomalbum where id='.$albumid;
	 				
		$db->setQuery( $qry);
		if (!$db->query()) {
		return JError::raiseWarning( 500, $db->getError() );
		}
		
		 
	$this->setMessage(JText::_('Album Deleted Successfully' ));
	$this->setRedirect( JRoute::_('index.php?option=com_awdjomalbum&view=awdalbumlist&wuid='.$user->id.'&Itemid='.AwdwallHelperUser::getComItemId(),false));
	}
	
	
	function next()
	{
	$albumid=$_REQUEST['albumid'];
	$photoindex=$_REQUEST['index'];	
	$photoindex=$photoindex+1;
	
	
	$mainframe= JFactory::getApplication();

	$db		=& JFactory :: getDBO();
	$imagequery='SELECT * FROM  #__awd_jomalbum_photos where albumid='.$albumid;	
	$db->setQuery($imagequery);
	$imagerows=$db->loadObjectList();
	
	
	foreach($imagerows as $key =>$imgrow)
	{
	$imgid[ $key+1 ]=$imgrow->id;
		
	}
	
	/*print_r($imgid);
	exit;*/
	$len=count($imgid);
	$photoid=$imgid[$photoindex];
	
	if($photoindex>$len)
	{
	$photoid=$imgid[1];
	}
	
	$this->setRedirect( JRoute::_('index.php?option=com_awdjomalbum&view=awdimagelist&layout=displayimage&albumid='.$albumid.'&photoid='.$photoid,false));

	}
	
	
	function previous()
	{
	$albumid=$_REQUEST['albumid'];
	$photoindex=$_REQUEST['index'];	
	//echo $photoindex;
	$photoindex=$photoindex-1;
	
	$mainframe= JFactory::getApplication();

	$db		=& JFactory :: getDBO();
	/*$imagequery= 'SELECT alb.*,albpt.image_name,albpt.id as photoid FROM #__awd_jomalbum as alb inner join #__awd_jomalbum_photos as albpt on alb.id=albpt.albumid  where alb.id='.$albumid.''.' order by albpt.id';*/
	$imagequery= 'SELECT * FROM  #__awd_jomalbum_photos where albumid='.$albumid;
	
	$db->setQuery($imagequery);
	$imagerows=$db->loadObjectList();
	
	foreach($imagerows as $key =>$imgrow)
	{
	$imgid[ $key+1 ]=$imgrow->id;
	
	}
	
	$photoid=$imgid[$photoindex];
	
//print_r($photoid);
	//$len=count($imgid);
	if($photoindex<=0)
	{
	$photoid=$imgid[1];
	}

	$this->setRedirect(JRoute::_('index.php?option=com_awdjomalbum&view=awdimagelist&layout=displayimage&albumid='.$albumid.'&photoid='.$photoid,false));

	}
	function saveinfo()
	{
		require_once (JPATH_SITE. DS .'components'.DS.'com_awdwall'.DS. 'models' . DS . 'wall.php');
		$mainframe= JFactory::getApplication();
 		$option = JRequest::getCmd('option');
		$db		=& JFactory::getDBO();
		$user =& JFactory::getUser();
		$app = JFactory::getApplication('site');
		
		$config =  & $app->getParams('com_awdwall');

		$fields			= $config->get('fieldids', '');
		$basicInfo 		= JsLib::getUserBasicInfo($user->id, $fields);
            if(is_array($basicInfo)){ 
            foreach($basicInfo as $arr){
				$requetfield='display_'.str_replace(' ','',$arr[1]);
				if($_REQUEST[$requetfield])
				{
					if($fieldname=='')
					{
						$fieldname=$_REQUEST[$requetfield];
					}
					else
					{
						$fieldname=$fieldname.','.$_REQUEST[$requetfield];
					}
				}
            } 
         } 
		//print_r($user);
		$id=$_REQUEST['id'];
		$highlightfields=$_REQUEST['highlightfields'];
		$userhighlightfields=implode(",",$highlightfields);
		$row = new TableAwd_jomalbum_userinfo($db);
		$post	= JRequest::get( 'post' );
		$row->id=$id;
		if (!$row->bind( $post )) {
			return JError::raiseError( 500, $db->stderr() );
		}
		$row->userhighlightfields='';
		$row->userhighlightfields=	$userhighlightfields;
		$row->cbfields='';
		$row->cbfields=	$fieldname;
		if (!$row->store()) {
			return JError::raiseError( 500, $db->stderr() );
		}
		
		$wall 				=& JTable::getInstance('Wall', 'Table');						
		$wall->user_id		= $user->id;
		$wall->type			= 'text';
		$wall->commenter_id	= $user->id;
		$wall->user_name	= '';
		$wall->avatar		= '';
		$wall->message		= JText::_('UPDATED PROFILE');
		$wall->reply		= 0;
		$wall->is_read		= 0;
		$wall->is_pm		= 0;
		$wall->is_reply		= 0;
		$wall->posted_id	= NULL;
		$wall->wall_date	= time();
		$wall->store();	
		
		// AUP POINTS
		$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
		if ( file_exists($api_AUP))
		{				
			require_once ($api_AUP);
				$keyreference  = AlphaUserPointsHelper::buildKeyreference('plgaup_points4jomwallinfosave', $user->id );
				 AlphaUserPointsHelper::newpoints('plgaup_points4jomwallinfosave','', $keyreference);
		}
		
		$this->setMessage(JText::_('Information saved') );
		$this->setRedirect(JRoute::_('index.php?option=com_awdjomalbum&view=userinfo&wuid='.$user->id.'&Itemid='.AwdwallHelperUser::getComItemId(),false));
	}
	
	function savealbum()
	{
	  	require_once (JPATH_SITE. DS .'components'.DS.'com_awdwall'.DS. 'models' . DS . 'wall.php');
		$mainframe= JFactory::getApplication();
 		$option = JRequest::getCmd('option');
		$db		=& JFactory::getDBO();
		$user =& JFactory::getUser();
		//print_r($user);
		$id=$_REQUEST['id'];
		$firsttime=$_REQUEST['firsttime'];
		$row = new TableAwd_jomalbum($db);
		$post	= JRequest::get( 'post' );
		$row->id=$id;
		//$row->userid=$user-id;
		//echo $row->userid;
		//exit;
		if (!$row->bind( $post )) {
			return JError::raiseError( 500, $db->stderr() );
		}
				 
		if (!$row->store()) {
			return JError::raiseError( 500, $db->stderr() );
		}
		if(empty($id))
		{
			$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
			if ( file_exists($api_AUP)){				
				require_once ($api_AUP);
				$keyreference  = AlphaUserPointsHelper::buildKeyreference('plgaup_points4Albumcreation', $id );
				 AlphaUserPointsHelper::newpoints('plgaup_points4Albumcreation','', $keyreference);
				
			}
		}

		$id=$row->id;
		$this->setMessage(JText::_('Album created successfully' ));
		$this->setRedirect( JRoute::_('index.php?option=com_awdjomalbum&view=awd_addphoto&id='.$id.'&firsttime=1&Itemid='.AwdwallHelperUser::getComItemId(),false));
		 
	}
	
	function savephoto()
	{
		require_once (JPATH_SITE. DS .'components'.DS.'com_awdwall'.DS. 'models' . DS . 'wall.php');
		require_once (JPATH_SITE. DS .'components'.DS.'com_awdwall'.DS. 'libraries' . DS . 'class.upload.php');	
		$mainframe= JFactory::getApplication();
 		$option = JRequest::getCmd('option');
		$db		=& JFactory::getDBO();
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$tempwidth 		= $config->get('width', 725);
		$scalimgwidth=$tempwidth-15;
		$albumid=$_REQUEST['id'];
		$firsttime=$_REQUEST['firsttime'];
		$user =& JFactory::getUser();
	 	$row = new TableAwd_add_photos($db);
		$post	= JRequest::get( 'post' );
		 
		 
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		 
		$path=JPATH_SITE. '/images/awd_photo/'; 
		
		$thumbpath=JPATH_SITE. '/images/awd_photo/awd_thumb_photo/'; 
		$largepath=JPATH_SITE. '/images/awd_photo/awd_large_photo/'; 
		$oripath=JPATH_SITE. '/images/awd_photo/awd_ori_photo/'; 


		if (ini_get('safe_mode')=='On')
		{
			$msg=JText::_('Directory creation not allowed while running in SAFE MODE as this can cause problems.');
			$mainframe->redirect(JRoute::_('index.php?option=com_awdjomalbum',false), $msg ); 
			exit();
		}  
		
		if(!is_dir( $path ) && !is_file( $path ))
		{
			//jimport('joomla.filesystem.*');
			JFolder::create($path);		 
		}
		
		
		if(!is_dir($thumbpath)&& !is_file($thumbpath))
		{
			// jimport('joomla.filesystem.*');
			JFolder::create($thumbpath);				
		}
		if(!is_dir($oripath)&& !is_file($oripath))
		{
			// jimport('joomla.filesystem.*');
			JFolder::create($thumbpath);				
		}
		
		if(!is_dir($largepath)&& !is_file($largepath))
		{
			// jimport('joomla.filesystem.*');
			JFolder::create($largepath);				
		}
    $files = array();
    foreach ($_FILES['photo'] as $k => $l) {
        foreach ($l as $i => $v) {
            if (!array_key_exists($i, $files))
                $files[$i] = array();
            $files[$i][$k] = $v;
        }
    }
	
  foreach ($files as $file) {
  	if($file['name']!='')
	{
		$filename='';
		$userfile_name='';
		$filename=$file['name'];
		$todaydate=time();
		$filename=str_replace(" ","_",$filename);
		preg_match('/\.[^\.]+$/i',$filename,$ext);
		$rand=rand(120,10000);
		$userfile_name=strtolower($rand.'_'.$todaydate.'.'.$ext[0]);
		$userfile_type=strtolower($file['type']);
		if($file['size']<=5000000)
		{
			if($userfile_type=='image/jpeg' || $userfile_type=='image/jpg' ||  
			$userfile_type=='image/pjpeg' || $userfile_type=='image/x-png' || 
			$userfile_type=='image/png' || $userfile_type=='image/gif' )
			{ 
					   $handle = new upload($file);
					   if ($handle->uploaded)
					   {
							$filename1 = preg_replace("/\\.[^.\\s]{3,4}$/", "", $userfile_name); 
							$folder=$path;
							processthumb($handle,$filename1,$folder);
							
							$handle->image_resize          = true;
							$handle->image_ratio        = true;
							//$handle->image_x               = $scalimgwidth;
							$handle->image_x               = 600;
							$handle->image_y               = 500;
							$folder=$largepath;
							processthumb($handle,$filename1,$folder);
							
							
							$handle->image_resize          = true;
							$handle->image_ratio_crop      = true;
							$handle->image_x               = 145;
							$handle->image_y               = 110;
							$folder=$thumbpath;
							//$filename2='tn145'.$filename1;
							processthumb($handle,$filename1,$folder);
							$today=date("Y-m-d h:i:s");
								$qry = "INSERT INTO  #__awd_jomalbum_photos(id, userid, albumid, image_name, title, published, upload_date) VALUES ('',$user->id, '$albumid', '$userfile_name', '', '1','$today' )"; 
										
								$db->setQuery( $qry);
								if (!$db->query()) {
									return JError::raiseWarning( 500, $db->getError() );
								}
								$photoid=$db->insertid();
								$photolink=JRoute::_('index.php?option=com_awdjomalbum&view=awdimagelist&tmpl=component&pid='.$photoid.'&albumid='.$albumid.'&Itemid='.AwdwallHelperUser::getComItemId(),false);
								$photosrc=JURI::base().'images/awd_photo/awd_thumb_photo/'.$userfile_name;
								$imagelist=$imagelist.'<div style="float:left; max-height:75px; width:75px; padding:3px;"><a href="'.$photolink.'" class="awdiframe" ><img src="'.$photosrc.'"  width="72" border="0" /></a></div>';
							
					   }
			
			} // if($userfile_type=='image/jpeg'
			else
			{
				$errmsgs[]=$filename.'&nbsp;'.JText::_('did not uploaded due to incorrect format');
				$this->setMessage(JText::_('Invaild image, Please try to upload only jpg or png or gif images.') );
				$this->setRedirect(JRoute::_( 'index.php?option=com_awdjomalbum',false));
			}
		
		} // if($file['size']<=5000000)
		else
		{
			$errmsgs[]='Image '.$filename.'&nbsp;'.JText::_('did not uploaded due to bigger size');
		}	
	
	 } // if($file['name']!='')
	
  } //  foreach ($files as $file)
		
	if($errmsgs)
	{
		foreach($errmsgs as $errmsg)
		{
			$msg.=$errmsg."<br>";
		}
	}else{
	$msg=JText::_('Photo uploaded successfully');
	}
	
	
	//addming to jomsocial activity streams
		// sending the wall post 
		if($firsttime){
		$album=getAlbumDetail($albumid);
		if($album->privacy!=3){
			$albumlink=JRoute::_('index.php?option=com_awdjomalbum&view=awdalbumimages&albumid='.$albumid.'&Itemid='.AwdwallHelperUser::getComItemId(),false);
			$wmsg=JText::_('has uploded the album').'&nbsp;<a href="'.$albumlink.'">'.$album->name.'</a><br>'.$imagelist.'<div style="clear:both"></div>';
			$wall 				=& JTable::getInstance('Wall', 'Table');									
			$wall->user_id		= $user->id;			
			$wall->group_id		= $groupId;			
			$wall->type			= $type;			
			$wall->commenter_id	= $user->id;			
			$wall->user_name	= '';			
			$wall->avatar		= '';			
			$wall->message		= nl2br($wmsg);			
			$wall->reply		= 0;			
			$wall->is_read		= 0;			
			$wall->is_pm		= 0;			
			$wall->is_reply		= 0;			
			$wall->posted_id	= NULL;			
			$wall->wall_date	= time();			
			$wall->type = JRequest::getString('type', 'text');	
			$wall->store();
			//insert into awd_wall_ privacy table.
			$query = 'INSERT INTO #__awd_wall_privacy(wall_id, privacy) VALUES(' . $wall->id . ', ' . $album->privacy . ')';
			$db->setQuery($query);
			$db->query();
			}
		}					
			//echo $id;

	$this->setMessage($msg); 
	$this->setRedirect( JRoute::_('index.php?option=com_awdjomalbum&view=awdalbumlist&wuid='.$user->id.'&Itemid='.AwdwallHelperUser::getComItemId(),false));
	
	}
	
 function resize($file, $type, $height, $width)
 {
    $img = false;
    switch ($type)
	{
      case 'image/jpeg':
      case 'image/jpg':
      case 'image/pjpeg':
        	$img = imagecreatefromjpeg($file);
       		break;
      case 'image/x-png':
      case 'image/png':
        	$img = imagecreatefrompng($file);
        	break;
      case 'image/gif':
        	$img = imagecreatefromgif($file);
        	break;
    }
    
    if(!$img)
	{
      return false;
    }
    
    $curr = @getimagesize($file);
    
    $nwimg = imagecreatetruecolor($width, $height);
	//echo $nwimg;
	//exit();
    imagecopyresampled($nwimg, $img, 0, 0, 0, 0, $width, $height, $curr[0], $curr[1]);
    $type=strtolower($type);
    switch ($type)
	{
      case 'image/jpeg':
      case 'image/jpg':
      case 'image/pjpeg':
        	imagejpeg($nwimg, $file);
        	break;
      case 'image/x-png':
      case 'image/png':
        	imagepng($nwimg, $file);
        	break;
      case 'image/gif':
        	imagegif($nwimg, $file);
        	break;
    }
    imagedestroy($nwimg);
    imagedestroy($img);
  }

	
	/*Image Upload*/ 
	function fileupload($filename,$path,$rootfolder)
	{
		$mainframe= JFactory::getApplication();
 		$option = JRequest::getCmd('option');
		if(!copy($filename, $path))
		{
			$errmsgs[]=JText::_('Failed to upload image. Please check chmod to').'&nbsp;'.$rootfolder; 
		}
	}
	
	function formatUrlInMsg($msg)
	{
		$stringToArray = explode(" ", $msg);
		$msg = '';
		foreach($stringToArray as $key => $val){
			if(preg_match('/^(http(s?):\/\/|ftp:\/\/{1})((\w+\.){1,})\w{2,}$/i', $val)){
				$val = '<a href="' . $val . '" target="_blank">' . $val . '</a>';
			}else if(preg_match('/^((\w+\.){1,})\w{2,}$/i', $val)){
				$val = '<a href="http://' . $val . '" target="_blank">' . $val . '</a>';
			}else if(preg_match('/^(http(s?):\/\/|ftp:\/\/{1})/i', $val)){
				$val = '<a href="' . $val . '" target="_blank">' . $val . '</a>';
			}
			$msg .= $val . ' ';
		}
		return $msg;
	}
	
	function addalbumlike()
	{
		$db		=& JFactory :: getDBO();
		$user =& JFactory::getUser();
		$photoid=$_REQUEST['photoID'];
		$uid=$_REQUEST['uID'];

		$sql="select count(*) from #__awd_jomalbum_photo_like where photoid=".$photoid." and userid=".$uid;
		$db->setQuery($sql);
		$totRec=$db->loadResult();
		
		if($totRec==0)
		{		
			$sql="insert into #__awd_jomalbum_photo_like(photoid,userid) values($photoid,$uid)";
			$db->setQuery($sql);
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}	
			// AUP POINTS
			$query='select userid from #__awd_jomalbum_photos where id='.$photoid;
			$db->setQuery($query);
			$userid = $db->loadResult();
			if($userid!=$user->id)
			{
				$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
				if ( file_exists($api_AUP)){				
					require_once ($api_AUP);
					
				$keyreference  = AlphaUserPointsHelper::buildKeyreference('plgaup_points4jomwallphotolike', $photoid );
				 AlphaUserPointsHelper::newpoints('plgaup_points4jomwallphotolike','', $keyreference);
					 
					 }
			}
			
			
		}
		
		
		$sql="select * from #__awd_jomalbum_photo_like where photoid=".$photoid." order by id desc Limit 5";
		$db->setQuery($sql);
		$rows=$db->loadObjectList();
	
		$sql="select count(*) from #__awd_jomalbum_photo_like where photoid=".$photoid;
		$db->setQuery($sql);
		$totLike=$db->loadResult();
		
		$link='index.php?option=com_awdwall&controller=colors';
		$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
		$params = json_decode( $db->loadResult(), true );
		for($i=1; $i<=14; $i++)
		{
			$str_color = 'color'.$i;			
			$color[$i]= $params[$str_color];
		}
		
		?>
		<div style="background-color:#<?php echo $color[12]; ?>; margin-bottom:5px">
		<?php 
		$user =& JFactory::getUser();
		?>
		<div style="width:100%; text-align:left;padding-bottom:3px;"><span  class="likespan"><?php echo $totLike.'&nbsp;'. JText::_('People like this photo');?></span></div>
		<?php
		foreach($rows as $row)
		{
		$userprofileLinkAWDCUser=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$row->userid.'&Itemid='.AwdwallHelperUser::getComItemId());
				 $values=getCurrentUserDetails($row->userid);  
				 $avatarTable=$values[2];
				 $userprofileLinkCUser=$values[1];
			
			$values1=getUserDetails($row->userid,$avatarTable,$user->id); 
			$imgPath1=$values1[0];
			 
			?>
			<a href="<?php echo $userprofileLinkCUser; ?>" style="padding-right:5px;"><img  src="<?php echo $imgPath1; ?>" height="32" width="32"  border="0"/></a>
			<?php
		}
		
		?></div><?php 		
		exit;
	}
	
	function addphotowalllike()
	{
		$db		=& JFactory :: getDBO();
		$user =& JFactory::getUser();
		$photoid=$_REQUEST['photoID'];
		$uid=$_REQUEST['uID'];
		$sql="select count(*) from #__awd_jomalbum_photo_wall_like  where photoid=".$photoid." and userid=".$uid;
		//echo $sql;exit;
		$db->setQuery($sql);
		$totRec=$db->loadResult();
		if($totRec==0)
		{		
			$sql="insert into #__awd_jomalbum_photo_wall_like(photoid,userid) values($photoid,$uid)";
			$db->setQuery($sql);
			if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
			}	
			
				// AUP POINTS
				$query='select wall_id from #__awd_wall_images where id='.$photoid;
				$db->setQuery($query);
				$wall_id = $db->loadResult();
				$query='select commenter_id from #__awd_wall where id='.$wall_id.' and wall_date IS NOT NULL';
				$db->setQuery($query);
				$commenter_id = $db->loadResult();
				if($commenter_id!=$user->id)
				{
					$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
					if ( file_exists($api_AUP)){				
						require_once ($api_AUP);
						
				$keyreference  = AlphaUserPointsHelper::buildKeyreference('plgaup_points4jomwallphotolike', $photoid );
				 AlphaUserPointsHelper::newpoints('plgaup_points4jomwallphotolike','', $keyreference);
						 
						 }
				}
			
		}
		
		
		$sql="select * from #__awd_jomalbum_photo_wall_like where photoid=".$photoid." order by id desc Limit 5";
		$db->setQuery($sql);
		$rows=$db->loadObjectList();
	
		$sql="select count(*) from #__awd_jomalbum_photo_wall_like where photoid=".$photoid;
		$db->setQuery($sql);
		$totLike=$db->loadResult();
		
		$link='index.php?option=com_awdwall&controller=colors';
		$db->setQuery("SELECT params FROM #__menu WHERE `link`='".$link."'");
		$params = json_decode( $db->loadResult(), true );
		for($i=1; $i<=14; $i++)
		{
			$str_color = 'color'.$i;			
			$color[$i]= $params[$str_color];
		}
		?>
		<div style="background-color:#<?php echo $color[12];?>;margin-bottom:5px;">
		<?php 
		$user =& JFactory::getUser();
		?>
		<div style="width:100%; text-align:left;padding-bottom:3px;"><span  class="likespan"><?php echo $totLike.'&nbsp;'.JText::_('People like this photo');?></span></div>
		<?php
		foreach($rows as $row)
		{
		$userprofileLinkAWDCUser=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$row->userid.'&Itemid='.AwdwallHelperUser::getComItemId());
				 $values=getCurrentUserDetails($row->userid);  
				 $avatarTable=$values[2];
				 $userprofileLinkCUser=$values[1];
			
			$values1=getUserDetails($row->userid,$avatarTable,$user->id); 
			$imgPath1=$values1[0];
			 
			?>
			<a href="<?php echo $userprofileLinkCUser; ?>" style="padding-right:5px;"><img  src="<?php echo $imgPath1; ?>" height="32" width="32" border="0"/></a>
			<?php
		}
		
		?></div><?php
		exit;
		}
}

function processthumb(&$handle,$filename,$folder)
{
   
   $mainframe= JFactory::getApplication(); 
	$handle->file_new_name_body   = $filename;
	$handle->process($folder);
   if ($handle->processed) {
	  // echo 'image resized';
	   //$handle->clean();
   } else {
   
   		//$mainframe->Redirect(JRoute::_('index.php?option=com_awdwall&task=uploadavatar&Itemid=' . $itemId, false));
	   //echo 'error : ' . $handle->error;
   }
}

?>