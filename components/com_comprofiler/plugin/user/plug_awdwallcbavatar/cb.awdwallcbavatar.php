<?php
/**
 * @version 2.4
 * @package AWDwall
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @copyright Copyright (C) 2010 AWDsolution.com. All rights reserved.
*/
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }
if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}
if($_REQUEST['option']=='com_comprofiler'){
global $_PLUGINS,$ueConfig;
error_reporting(0);
$_PLUGINS->registerFunction( 'onAfterUserProfileDisplay', 'awdProfileDisplay', 'awdcbavatarPlugin' );
class awdcbavatarPlugin extends cbPluginHandler {
	// Establish the trigger function:
	function awdProfileDisplay($user)
	{
	if($user)
	{
		
			$db		=& JFactory::getDBO();
			global $ueConfig;
			require_once(JPATH_COMPONENT . DS . 'plugin'. DS .'user' . DS . 'plug_awdwallcbavatar'. DS . 'class.upload.php');
				$sql="select count(*) as countx from  #__awd_wall_users where user_id=".$user->user_id;
				$db->setQuery( $sql );
				$countx=$db->loadResult();
				if(empty($countx))
				{
					$sql="INSERT INTO  #__awd_wall_users (`user_id`)VALUES (".$user->user_id.");";
					$db->setQuery( $sql );
					if (!$db->query())
					{
						return JError::raiseWarning( 500, $db->getError() );
					}
				}
				
				$sql="select avatar from  #__comprofiler where user_id=".$user->user_id;
				$db->setQuery( $sql );
				$avatar=$db->loadResult();
				
				
				if($avatar==NULL || $avatar=='')
				{
					//$avatar='pending_n.png';
					$avatar='nophoto_n.png';
					// update blank image to the table and folder
					$sql="select avatar from  #__awd_wall_users where user_id=".$user->user_id;
					$db->setQuery( $sql );
					$wavatar=$db->loadResult();
					//exit;
					if($avatar!=$wavatar )
					{
						$deleteimagepath1=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'original'. DS .$wavatar;
						$deleteimagepath2=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn133'.$wavatar;
						$deleteimagepath3=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn51'.$wavatar;
						$deleteimagepath4=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn40'.$wavatar;
						$deleteimagepath5=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn32'.$wavatar;
						$deleteimagepath6=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn19'.$wavatar;
						unlink($deleteimagepath1);
						unlink($deleteimagepath2);
						unlink($deleteimagepath3);
						unlink($deleteimagepath4);
						unlink($deleteimagepath5);
						unlink($deleteimagepath6);
						
						$sql="update #__awd_wall_users SET `avatar` = '' where user_id=".$user->user_id;
						$db->setQuery( $sql );
						if (!$db->query())
						{
							return JError::raiseWarning( 500, $db->getError() );
						}
						// update the default image of cb
						$cbtemplate=$ueConfig['templatedir'];
						$imagepath=JPATH_SITE. DS . 'components'. DS .'com_comprofiler' .DS.'plugin'.DS.'templates'.DS.$cbtemplate.DS.'images'.DS.'avatar'.DS.$avatar;
						$handle = new upload($imagepath);
					
						if ($handle->uploaded) 
						{
							$filename = preg_replace("/\\.[^.\\s]{3,4}$/", "", $avatar);
							
							$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'original';
							processthumb($handle,$filename,$folder);
							
							$handle->image_resize          = true;
							$handle->image_ratio_y        = true;
							$handle->image_x               = 133;
							
							$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
							$filename2='tn133'.$filename;
							processthumb($handle,$filename2,$folder);
							
							$handle->image_resize          = true;
							$handle->image_ratio_crop      = true;
							$handle->image_x               = 51;
							$handle->image_y               = 51;
							$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
							$filename2='tn51'.$filename;
							processthumb($handle,$filename2,$folder);
							
							$handle->image_resize          = true;
							$handle->image_ratio_crop      = true;
							$handle->image_x               = 40;
							$handle->image_y               = 40;
							$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
							$filename2='tn40'.$filename;
							processthumb($handle,$filename2,$folder);
							
							$handle->image_resize          = true;
							$handle->image_ratio_crop      = true;
							$handle->image_x               = 32;
							$handle->image_y               = 32;
							$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
							$filename2='tn32'.$filename;
							processthumb($handle,$filename2,$folder);
							
							$handle->image_resize          = true;
							$handle->image_ratio_crop      = true;
							$handle->image_x               = 19;
							$handle->image_y               = 19;
							$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
							$filename2='tn19'.$filename;
							processthumb($handle,$filename2,$folder);
							
							$sql="update #__awd_wall_users SET `avatar` = '".strtolower($avatar)."' where user_id=".$user->user_id;
							$db->setQuery( $sql );
							if (!$db->query())
							{
								return JError::raiseWarning( 500, $db->getError() );
							}
						
						}
						
					}
					
				}
				else
				{
					// update uploaded image to the table and folder
						$gpos = strpos($avatar, 'gallery');
						//if($gpos==TRUE)
						if($gpos !== false)
						{
	
						
	
								$sql="select avatar as wallavatar from  #__awd_wall_users where user_id=".$user->user_id;
								$db->setQuery( $sql );
								$wallavatar=$db->loadResult();
								
								$imagepath=JPATH_BASE. DS . 'images'. DS .'comprofiler' . DS .$avatar ;
								$avatar=str_replace('gallery/','',$avatar);
								
								if($wallavatar!=$avatar)
								{
									
									$deleteimagepath1=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'original'. DS .$wallavatar;
									$deleteimagepath2=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn133'.$wallavatar;
									$deleteimagepath3=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn51'.$wallavatar;
									$deleteimagepath4=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn40'.$wallavatar;
									$deleteimagepath5=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn32'.$wallavatar;
									$deleteimagepath6=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn19'.$wallavatar;
									unlink($deleteimagepath1);
									unlink($deleteimagepath2);
									unlink($deleteimagepath3);
									unlink($deleteimagepath4);
									unlink($deleteimagepath5);
									unlink($deleteimagepath6);
									
									$handle = new upload($imagepath);
									if ($handle->uploaded) 
									{
										
										$filename = preg_replace("/\\.[^.\\s]{3,4}$/", "", $avatar);
										
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'original';
										processthumb($handle,$filename,$folder);
										
										
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
										$filename2='tn133'.$filename;
										processthumb($handle,$filename2,$folder);
										
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
										$filename2='tn51'.$filename;
										processthumb($handle,$filename2,$folder);
										
										$handle->image_resize          = true;
										$handle->image_ratio_crop      = true;
										$handle->image_x               = 40;
										$handle->image_y               = 40;
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
										$filename2='tn40'.$filename;
										processthumb($handle,$filename2,$folder);
										
										$handle->image_resize          = true;
										$handle->image_ratio_crop      = true;
										$handle->image_x               = 32;
										$handle->image_y               = 32;
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
										$filename2='tn32'.$filename;
										processthumb($handle,$filename2,$folder);
										
										$handle->image_resize          = true;
										$handle->image_ratio_crop      = true;
										$handle->image_x               = 19;
										$handle->image_y               = 19;
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
										$filename2='tn19'.$filename;
										processthumb($handle,$filename2,$folder);
										
										$sql="update #__awd_wall_users SET `avatar` = '".strtolower($avatar)."' where user_id=".$user->user_id;
										$db->setQuery( $sql );
										if (!$db->query())
										{
											return JError::raiseWarning( 500, $db->getError() );
										}
								
								} // if ($handle->uploaded) 
							} // if($wallavatar!=$avatar)
						
						}
						else
						{
						
							if($user->avatarapproved==1)
							{
								
								$sql="select avatar from  #__awd_wall_users where user_id=".$user->user_id;
								$db->setQuery( $sql );
								$wavatar=$db->loadResult();
								
								if($user->avatar!=$wavatar)
								{
									
									$deleteimagepath1=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'original'. DS .$wavatar;
									$deleteimagepath2=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn133'.$wavatar;
									$deleteimagepath3=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn51'.$wavatar;
									$deleteimagepath4=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn40'.$wavatar;
									$deleteimagepath5=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn32'.$wavatar;
									$deleteimagepath6=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn19'.$wavatar;
									unlink($deleteimagepath1);
									unlink($deleteimagepath2);
									unlink($deleteimagepath3);
									unlink($deleteimagepath4);
									unlink($deleteimagepath5);
									unlink($deleteimagepath6);
						
									
									$imagepath=JPATH_BASE. DS . 'images'. DS .'comprofiler' . DS .$avatar ;
									$handle = new upload($imagepath);
									if ($handle->uploaded) 
									{
										
										$filename = preg_replace("/\\.[^.\\s]{3,4}$/", "", $avatar);
										
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'original';
										processthumb($handle,$filename,$folder);
										
										$handle->image_resize          = true;
										$handle->image_ratio_y        = true;
										$handle->image_x               = 133;
										
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
										$filename2='tn133'.$filename;
										processthumb($handle,$filename2,$folder);
										
										$handle->image_resize          = true;
										$handle->image_ratio_crop      = true;
										$handle->image_x               = 51;
										$handle->image_y               = 51;
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
										$filename2='tn51'.$filename;
										processthumb($handle,$filename2,$folder);
										
										$handle->image_resize          = true;
										$handle->image_ratio_crop      = true;
										$handle->image_x               = 40;
										$handle->image_y               = 40;
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
										$filename2='tn40'.$filename;
										processthumb($handle,$filename2,$folder);
										
										$handle->image_resize          = true;
										$handle->image_ratio_crop      = true;
										$handle->image_x               = 32;
										$handle->image_y               = 32;
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
										$filename2='tn32'.$filename;
										processthumb($handle,$filename2,$folder);
										
										$handle->image_resize          = true;
										$handle->image_ratio_crop      = true;
										$handle->image_x               = 19;
										$handle->image_y               = 19;
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
										$filename2='tn19'.$filename;
										processthumb($handle,$filename2,$folder);
										
										$sql="update #__awd_wall_users SET `avatar` = '".strtolower($avatar)."' where user_id=".$user->user_id;
										$db->setQuery( $sql );
										if (!$db->query())
										{
											return JError::raiseWarning( 500, $db->getError() );
										}
									
									}
								}
								
							}
							else
							{
								$avatar='pending_n.png';
								$preavatar=$user->avatar;
								//$avatar='nophoto_n.png';
								
								
								
								// update blank image to the table and folder
								$sql="select avatar from  #__awd_wall_users where user_id=".$user->user_id;
								$db->setQuery( $sql );
								$wavatar=$db->loadResult();
								
								if($preavatar!=$wavatar)
								{
									$sql="update #__awd_wall_users SET `avatar` = '' where user_id=".$user->user_id;
									$db->setQuery( $sql );
									if (!$db->query())
									{
										return JError::raiseWarning( 500, $db->getError() );
									}
									
									$deleteimagepath1=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'original'. DS .$wavatar;
									$deleteimagepath2=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn133'.$wavatar;
									$deleteimagepath3=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn51'.$wavatar;
									$deleteimagepath4=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn40'.$wavatar;
									$deleteimagepath5=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn32'.$wavatar;
									$deleteimagepath6=JPATH_SITE. DS .'images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb'. DS .'tn19'.$wavatar;
									unlink($deleteimagepath1);
									unlink($deleteimagepath2);
									unlink($deleteimagepath3);
									unlink($deleteimagepath4);
									unlink($deleteimagepath5);
									unlink($deleteimagepath6);
						
									
									// update the default image of cb
									$cbtemplate=$ueConfig['templatedir'];
									$imagepath=JPATH_SITE. DS . 'components'. DS .'com_comprofiler' .DS.'plugin'.DS.'templates'.DS.$cbtemplate.DS.'images'.DS.'avatar'.DS.$avatar;
									$handle = new upload($imagepath);
								
								
									if ($handle->uploaded) 
									{
										$filename = preg_replace("/\\.[^.\\s]{3,4}$/", "", $avatar);
										
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'original';
										processthumb($handle,$filename,$folder);
										
										$handle->image_resize          = true;
										$handle->image_ratio_y        = true;
										$handle->image_x               = 133;
										
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
										$filename2='tn133'.$filename;
										processthumb($handle,$filename2,$folder);
										
										$handle->image_resize          = true;
										$handle->image_ratio_crop      = true;
										$handle->image_x               = 51;
										$handle->image_y               = 51;
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
										$filename2='tn51'.$filename;
										processthumb($handle,$filename2,$folder);
										
										$handle->image_resize          = true;
										$handle->image_ratio_crop      = true;
										$handle->image_x               = 40;
										$handle->image_y               = 40;
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
										$filename2='tn40'.$filename;
										processthumb($handle,$filename2,$folder);
										
										$handle->image_resize          = true;
										$handle->image_ratio_crop      = true;
										$handle->image_x               = 32;
										$handle->image_y               = 32;
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
										$filename2='tn32'.$filename;
										processthumb($handle,$filename2,$folder);
										
										$handle->image_resize          = true;
										$handle->image_ratio_crop      = true;
										$handle->image_x               = 19;
										$handle->image_y               = 19;
										$folder='images' . DS . 'wallavatar' . DS . $user->user_id . DS . 'thumb';
										$filename2='tn19'.$filename;
										processthumb($handle,$filename2,$folder);
										
										$sql="update #__awd_wall_users SET `avatar` = '".strtolower($avatar)."' where user_id=".$user->user_id;
										$db->setQuery( $sql );
										if (!$db->query())
										{
											return JError::raiseWarning( 500, $db->getError() );
										}
									
									}
									
								}
							}
						
						}
					
				}
	
		
	}
	}
}
function processthumb(&$handle,$filename,$folder)
{
   
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
}