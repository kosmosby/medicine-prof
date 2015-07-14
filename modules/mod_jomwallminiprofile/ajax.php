<?php
// Get Joomla! framework
define( '_JEXEC', 1 );
define( '_VALID_MOS', 1 );
define( 'DS', DIRECTORY_SEPARATOR );
$str=DS.'modules'.DS.'mod_jomwallminiprofile';
define( 'JPATH_BASE', str_replace($str,'',realpath(dirname(__FILE__))));
define('REAL_NAME', 0);
define('USERNAME', 1);
error_reporting(0);
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

require_once(JPATH_SITE . DS . 'components'.DS.'com_awdwall'. DS . 'helpers' . DS . 'user.php');
require_once(JPATH_SITE . DS .'modules'.DS.'mod_jomwallminiprofile'.DS.'helper.php');


$mainframe =JFactory::getApplication('site');
$mainframe->initialise();
$user =JFactory::getUser();
$session =JFactory::getSession();
$db		=JFactory::getDBO();

$app = JFactory::getApplication('site');
$config =  $app->getParams('com_awdwall');
$template 		= $config->get('temp', 'default');
$lang = JFactory::getLanguage();
$extension = 'com_awdwall';
$base_dir = JPATH_SITE;
$language_tag = $lang->getTag();
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);
$Itemid=AwdwallHelperUser::getComItemId();


$currenttime=time();
$currenttime=$currenttime-8;


    $module = JModuleHelper::getModule('mod_awdautouserpost');
    $moduleParams = new JRegistry();
    $moduleParams->loadString($module->params);
			
	$userid=$moduleParams->get('userids');
	$postcount=$moduleParams->get('postcount');
	$showusername=$moduleParams->get('showusername');
	$showavatar=$moduleParams->get('showavatar');
	$displayoption=$moduleParams->get('displayoption');
	$nword=$moduleParams->get('nword',10);
		
	if($_REQUEST['task']=='gettotalnotification')
	{   error_reporting(0);
		$config 		= &JComponentHelper::getParams('com_awdwall');
		$template 		= $config->get('temp', 'default');
	
	
	
	
		header("Pragma: no-cache"); 
		$mainframe= JFactory::getApplication(); 
		$db =& JFactory::getDBO();
		$user = &JFactory::getUser();
		$Itemid = AwdwallHelperUser::getComItemId();
		$config 		= &JComponentHelper::getParams('com_awdwall');
		$jomalbumexist=0;
		$wallalbumfile = JPATH_SITE .DS. 'components'.DS.'com_awdjomalbum'.DS.'awdjomalbum.php';
		$jomalbumexist='0';
		if (file_exists($wallalbumfile)) 
		{
			$jomalbumexist=1;
		}
		
		$db =& JFactory::getDBO();
		$query = "SELECT * FROM #__awd_wall_notification WHERE 	nuser =".$user->id." and ncreator !=".$user->id." order by nid desc";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$data=1;
		$num=date("Y-m-d H:i:s");
		?>
         <?php
		$n=count($rows);
		if($n>0)
		{
		for($i=0;$i<$n;$i++)
		{
			if($i==$n-1)
			{
				$class='notiItemsWrap';
			}
			else
			{
				$class='notiItemsWrap';
			}

			$cusername=AwdwallHelperUser::getDisplayName($rows[$i]->ncreator);
			$cuserlink='index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$rows[$i]->ncreator.'&Itemid='.$Itemid;
			$cuserlink =str_replace('modules/mod_jomwallminiprofile/','',$cuserlink);
			
			$cuserimage=AwdwallHelperUser::getBigAvatar32($rows[$i]->ncreator);
			$cuserimage =str_replace('modules/mod_jomwallminiprofile/','',$cuserimage);
			
			$notifytext='';
			$notifyurl='';
			$query='';
			if($rows[$i]->ntype=='text' || $rows[$i]->ntype=='image' || $rows[$i]->ntype=='video' || $rows[$i]->ntype=='link' || $rows[$i]->ntype=='mp3' || $rows[$i]->ntype=='file' || $rows[$i]->ntype=='trail' || $rows[$i]->ntype=='jing' || $rows[$i]->ntype=='event' || $rows[$i]->ntype=='article')
			{
				if($rows[$i]->nalbumid)
				{
					$notifytext=JText::_('commented on your photo');
					$notifyurl='index.php?option=com_awdjomalbum&view=awdimagelist&albumid='.$rows[$i]->nalbumid.'&pid='.$rows[$i]->nphotoid.'&Itemid='.$Itemid;
					$type='tag';
				}
				if($rows[$i]->nwallid)
				{
					$query="select reply from #__awd_wall where id=".$rows[$i]->nwallid;
					$db->setQuery($query);
					$reply = $db->loadResult();
					if($reply==0)
					{
						$notifytext=JText::_('posted on your wall');
						$notifyurl='index.php?option=com_awdwall&view=awdwall&layout=mywall&wid='.$rows[$i]->nwallid.'&Itemid='.$Itemid;
					}
					else
					{
						if($rows[$i]->nphotoid)
						{
							$notifytext=JText::_('commented on your photo');
							if($jomalbumexist==1)
							{
								$notifyurl='index.php?option=com_awdjomalbum&view=awdwallimagelist&wuid='.$rows[$i]->nuser.'&pid='.$rows[$i]->nphotoid.'&Itemid='.$Itemid;
								$type='tag';
								
							}
							else
							{
								$notifyurl='index.php?option=com_awdwall&view=awdwall&layout=mywall&wid='.$rows[$i]->nwallid.'&Itemid='.$Itemid;
								$type='text';
							}
						}
						else
						{
							$notifytext=JText::_('POSTED ON YOUR POST');
							$notifyurl='index.php?option=com_awdwall&view=awdwall&layout=mywall&wid='.$reply.'&Itemid='.$Itemid;
						}
					}
				}

			}
			if($rows[$i]->ntype=='group')
			{
				$query = "SELECT title, creator FROM #__awd_groups WHERE id =".$rows[$i]->ngroupid;
				$db->setQuery($query);
				$grouptitle = $db->loadObjectList();
				$groupdetail =$grouptitle[0];
				
				$grouptitle=$groupdetail->title;
				$groupcreator=$groupdetail->creator;
				if($rows[$i]->nwallid)
				{
					$notifytext=JText::_('HAS JOINED THE GROUP').' <b>'.$grouptitle.'</b>';
					$notifyurl ='index.php?option=com_awdwall&task=viewgroup&groupid=' . $rows[$i]->ngroupid.'&Itemid=' . $Itemid;
					//$notifyurl='index.php?option=com_awdwall&layout=mywall&wuid='.$rows[$i]->ncreator.'&Itemid='.$Itemid;
				}
				else
				{
					$notifytext=JText::_('INVITED YOU TO JOIN').' <b>'.$grouptitle.'</b>';
					$tab3='#tabs-3';
					$notifyurl='index.php?option=com_awdwall&task=groups&Itemid='.$Itemid;
				}
			}
			if($rows[$i]->ntype=='pm')
			{
				$notifytext=JText::_('ADDED PRIVATE MESSAGE ON YOUR WALL');
				$notifyurl='index.php?option=com_awdwall&view=awdwall&layout=mywall&wid='.$rows[$i]->nwallid.'&Itemid='.$Itemid;
			}
			if($rows[$i]->ntype=='friend')
			{
				$query = "SELECT status FROM #__awd_connection WHERE connect_from =".$user->id." and connect_to=".$rows[$i]->ncreator;
				$db->setQuery($query);
				$status = $db->loadResult();
				
				if($status==0)
				{
					$notifytext=JText::_('WANTS TO BE YOUR FRIEND');
					$notifyurl='index.php?option=com_awdwall&task=friends&Itemid='.$Itemid;
					
				}
				if($status!=0)
				{
					$query = "SELECT status FROM #__awd_connection WHERE connect_from =".$rows[$i]->ncreator." and connect_to=".$user->id;
					$db->setQuery($query);
					$astatus = $db->loadResult();
					if($astatus==1) {
						$notifytext=JText::_('ACCEPTED YOUR FRIEND REQUEST');
						//$notifyurl='index.php?option=com_awdwall&layout=mywall&wuid='.$rows[$i]->ncreator.'&Itemid='.$Itemid;
						$notifyurl='index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$rows[$i]->ncreator.'&Itemid='.$Itemid;
					}
				}
			}
			if($rows[$i]->ntype=='tag')
			{
				if($rows[$i]->nalbumid==0)
				{
					$query = "SELECT a.user_id as user_id, b.wall_id as wall_id FROM #__awd_wall as a left join #__awd_wall_images as b on a.id=b.wall_id WHERE b.id =".$rows[$i]->nphotoid;
					$db->setQuery($query);
					$wallphoto = $db->loadObjectList();
					if($wallphoto[0]->user_id==$rows[$i]->ncreator)
						$notifytext=JText::_("TAGGED YOU IN HIS PHOTO");
					else if($wallphoto[0]->user_id==$rows[$i]->nuser)
						$notifytext=JText::_("TAGGED YOU IN YOUR PHOTO");
					else 					
						//$notifytext=JText::_("tagged you in ").AwdwallHelperUser::getDisplayName($wallphoto[0]->user_id).JText::_(" 's photo.");
						$notifytext=JText::sprintf('tagged you in users photo', AwdwallHelperUser::getDisplayName($wallphoto[0]->user_id));
					$notifyurl='index.php?option=com_awdjomalbum&view=awdwallimagelist&wuid='.$wallphoto[0]->user_id.'&pid='.$rows[$i]->nphotoid.'&Itemid='.$Itemid;
				}
				else
				{
					$query = "SELECT userid FROM #__awd_jomalbum_photos  WHERE id =".$rows[$i]->nphotoid." and albumid=".$rows[$i]->nalbumid;
					$db->setQuery($query);
					$albumuserid = $db->loadResult();
					//$notifytext=JText::_("tagged you in ").AwdwallHelperUser::getDisplayName($albumuserid).JText::_(" 's photo.");
						$notifytext=JText::sprintf('tagged you in users photo', AwdwallHelperUser::getDisplayName($albumuserid));
					$notifyurl='index.php?option=com_awdjomalbum&view=awdimagelist&albumid='.$rows[$i]->nalbumid.'&pid='.$rows[$i]->nphotoid.'&Itemid='.$Itemid;
				}
			}
			
		$config 	= &JComponentHelper::getParams('com_awdwall');
		$timestamp_format 	= $config->get('timestamp_format', '0');
			if($type=='')
			$type=$rows[$i]->ntype;
			
			$notifyurl=JRoute::_($notifyurl,false);
			$notifyurl =str_replace('modules/mod_jomwallminiprofile/','',$notifyurl);
		?>
         <div class="awdmodcomment_ui" onclick="navigateurl('<?php echo $rows[$i]->nid;?>','<?php echo $notifyurl;?>','<?php echo $type;?>')" >
                    <div class="awdmodcomment_text">
             <div  class="awdmodcomment_actual_text"><span style="display:block; height:32px; width:32px; float:left; margin: 5px 5px 5px 0px;box-shadow: 0px 0px 3px #111;"><img src="<?php echo $cuserimage;?>" alt="<?php echo $cusername;?>" title="<?php echo $cusername;?>" height="32" width="32" /></span><span style="margin:0px; padding:0px; clear:both"><strong><?php echo $cusername;?></strong>&nbsp;<?php echo $notifytext;?><br /><?php 
		if($timestamp_format==1)
		{
			$timestamp=strtotime($rows[$i]->ndate);
			echo AwdwallHelperUser::getDisplayTime($timestamp);
		}
		else
		{
		  	echo awdwallController::getTextDate($rows[$i]->ndate);
		}
		?></span></div>
           </div>
                  </div>
         
      
	  <?php 
	  } 
	  ?>
		<?php
		}
		else
		{
		?>
          <div class="awdmodcomment_ui">
            <div class="awdmodcomment_text">
              <div  class="awdmodcomment_actual_text"><?php echo JText::_("No new Notice");?></center></div>
            </div>
          </div>
		<?php
		}
		?>
          <script type="text/javascript">
		  <?php if($n>0){?>
          jQuery("#mes").html("<?php echo $n;?>");
		  jQuery("#mes").show();
		  <?php }else{?>
		   jQuery("#mes").hide();
		  <?php } ?>
          </script>
		<?php
		exit;
	
	
	
	
	
	}
exit;
?>