<?php 
defined('_JEXEC') or die('Restricted access');

class linkvideo
{	
	
	function typelink($videoLink){
	
		//$videoLink = 'http://'.JString::str_ireplace( 'http://' , '' , $videoLink );		
		$parsedVideoLink	= parse_url($videoLink);
		preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $parsedVideoLink['host'], $matches);
		$domain		= $matches['domain'];
		$provider		= explode('.', $domain);
		$providerName	= JString::strtolower($provider[0]);		
		
		return $providerName;
	}
	
	function getidvideo($providerName, $videoLink){
		$libraryPath	= JPATH_COMPONENT . DS . 'libraries' . DS . 'videos' . DS . $providerName . '.php';	

		jimport('joomla.filesystem.file');
		if (!JFile::exists($libraryPath)){	
			$redirect = JRoute::_('index.php?option=com_awdwall&&view=awdwall', false);
			$message	= JText::_('Video Provider is not supported');
			$mainframe->redirect($redirect , $message, 'error');
		}
		
		$db =& JFactory::getDBO();
		require_once($libraryPath);
		$className		= 'TableVideo' . JString::ucfirst($providerName);
		$videoObj		= new $className($db);
		$videoObj->init($videoLink);
		
		return $videoObj;
	}
	
	function addlinkvideo($vLink){
		$user = &JFactory::getUser();		
		$wuid = JRequest::getInt('wuid', 0);
		$groupId = JRequest::getInt('groupid', NULL);
		if($groupId==0)
		$groupId =NULL;
		$itemId = AwdwallHelperUser::getComItemId();
		$db =& JFactory::getDBO();
		if($wuid == 0) $wuid = $user->id;
		if((int)$user->id){
			//$vLink = JRequest::getVar( 'vLink' , '');
			require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'avideo.php');
			if(!empty($vLink)){ 
				$AVideo 	= new AVideo($wuid);
				$videoObj 	= $AVideo->getProvider($vLink);				
				if ($videoObj->isValid()){
					require_once (JPATH_COMPONENT . DS . 'models' . DS . 'video.php');
					require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
					$video =& JTable::getInstance('Video', 'Table');						
					$video->title			= $videoObj->getTitle();
					$video->type			= $videoObj->getType();
					$videotype				= $videoObj->getType();
					$video->video_id		= $videoObj->getId();
					$video->description		= $videoObj->getDescription();
					$video->duration		= $videoObj->getDuration();
					$video->creator			= $user->id;						
					$video->created			= gmdate('Y-m-d H:i:s');										
					$video->published		= 1;						
					$video->thumb			= $videoObj->getThumbnail();
					$video->path			= $vLink;						
					
					// save into wall table first
					$wall = &JTable::getInstance('Wall', 'Table');
					$wall->user_id  = $wuid;
					$wall->group_id = $groupId;
					$wall->type			= 'video';
					$wall->commenter_id	= $user->id;
					$wall->user_name	= '';
					$wall->avatar		= '';
					$wall->message		= '';
					$wall->reply		= 0;
					$wall->is_read		= 0;
					$wall->is_pm		= 0;
					$wall->is_reply		= 0;
					$wall->posted_id	= NULL;
					$wall->wall_date	= NULL;
				
					// store wall to database
					if (!$wall->store()){			
						$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main' , false ), JText::_('Post Failed'));
					}
					$video->wall_id	= $db->insertid();
					$wall_id	= $video->wall_id;
					if (!$video->store()){					
						$url			= JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main' , false);
						$message		= JText::_('Add video link failed');
						$this->setRedirect($url , $message);
					}
					require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'remote.php');
					$thumbData		= getContentFromUrl($video->thumb);
					if ($thumbData)
					{
						jimport('joomla.filesystem.file');
						require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'file.php');
						require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'image.php');			
						
						$thumbPath		= $AVideo->videoRootHomeUserThumb;							
						$thumbFileName	= genRandomFilename($thumbPath);
						$tmpThumbPath	= $thumbPath . DS . $thumbFileName;
					
						if (JFile::write($tmpThumbPath, $thumbData)){								
							$info		= getimagesize( $tmpThumbPath );
							$mime		= image_type_to_mime_type( $info[2]);
							$thumbExtension	= imageTypeToExt( $mime );
							
							$thumbPath	= $thumbPath . DS . $thumbFileName .$thumbExtension;
							JFile::move($tmpThumbPath, $thumbPath);
							
							// Resize the thumbnails
							imageResizep( $thumbPath , $thumbPath , $mime , $AVideo->videoThumbWidth , $AVideo->videoThumbHeight );
							$video->thumb = 'videos/' . $wuid . '/thumbs/' . $thumbFileName . $thumbExtension;
							$hvideothumb = 'videos/' . $wuid . '/thumbs/' . $thumbFileName . $thumbExtension;
							$video->store();
						}
						
					}

				// adding to com_hwdvideoshare
				
		if(file_exists(JPATH_SITE . '/components/com_hwdvideoshare/hwdvideoshare.php'))
		{
			
			// check wether wall video is there in hwdvideoshare or not if not then add.
		if(file_exists(JPATH_SITE . '/plugins/hwdvs-thirdparty/'.$videotype.'.php'))
		{

			$wallcatname='Wall Video';
			$query = "SELECT count(*) as wallvideocount FROM #__hwdvidscategories WHERE category_name='".$wallcatname."'";
			$db->setQuery($query);
			$wallvideocount = $db->loadResult();
			$query = "SELECT MAX(ordering) as catmaxordering FROM #__hwdvidscategories ";
			$db->setQuery($query);
			$catmaxordering = $db->loadResult();
			$catmaxordering=$catmaxordering+1;
			if($wallvideocount==0)
			{
			$sql = 'INSERT INTO #__hwdvidscategories(category_name, category_description,ordering,published) VALUES("'.$wallcatname .'","' . $wallcatname . '",' . $catmaxordering . ',1)';
			$db->setQuery($sql);
			$db->query();
				
			}
				$query = "SELECT id FROM #__hwdvidscategories WHERE category_name='".$wallcatname."'";
				$db->setQuery($query);
				$wallcatid = $db->loadResult(); // the hwdvideoshare cat id


				
				$query = "SELECT MAX(id) as videomaxid FROM #__hwdvidsvideos ";
				$db->setQuery($query);
				$videomaxid = $db->loadResult();
				$videomaxid=$videomaxid+1;

				$parsedurl= parse_url($vLink);
				$hvideo_type=str_replace('www.','',$parsedurl['host']);
				$hvideo_id=$videoObj->getId();
				$htitle=$videoObj->getTitle();
				$hdescription=$videoObj->getDescription();
				$hcategory_id=$wallcatid;
				$hdate_uploaded=gmdate('Y-m-d H:i:s');
				$huser_id=$user->id;
				$allow_comments=1;
				$allow_embedding=1;
				$allow_ratings=1;
				$approved 	='yes';
				$published=1;
				$hthumbnail='tp-'.$videomaxid.'.jpg';
			//	copy('images/'.$hvideothumb,'hwdvideos/thumbs/'.$hthumbnail);
				
			$sql = "INSERT INTO #__hwdvidsvideos(video_type, video_id,title,description,category_id,date_uploaded,allow_comments 	,allow_embedding,allow_ratings,public_private,thumbnail,approved,user_id,published) VALUES('".$hvideo_type ."','" . $hvideo_id . "','" . $htitle . "','" . $hdescription . "','" . $hcategory_id . "','" . $hdate_uploaded . "','" . $allow_comments . "','" . $allow_embedding . "','" . $allow_ratings . "','public','" . $hthumbnail . "','" . $approved . "','" . $user->id . "',1)";
			
			
			$db->setQuery($sql);
			$db->query();
			
			$sql = 'INSERT INTO #__awd_wall_videos_hwd(	wall_id, hwdviodeo_id) VALUES('.$wall_id .','.$videomaxid.')';
			$db->setQuery($sql);
			$db->query();
			
			} // if exist
		} // if exist


				}  
			}

			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=mywall&wuid=' . $wuid . '&Itemid=' . $itemId, false));

		}else{
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=mywall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
		}
		
		$tags = get_meta_tags($vLink);
		$description = $tags['description'];
		$description 	= ltrim($description);
		$description 	= rtrim($description);
		
		$d = 1;
		$img = '<img id='.$d.' class=no_hidden src='.JURI::base() . 'images/' . $video->thumb.' >';
		echo '{"type": "video","foo": "'.$file.'","img": "'.$img.'","count_img": "'.$d.'","error": "' . $error . '","msg": "' . $msg .  '","file": "<a href=' . $vLink . ' target=_blank>' . $vLink .  '</a>","wid_tmp": "' . $video->wall_id .  '","title": "' . $video->title .  '"}';
		return '{"type": "video","foo": "'.$file.'","img": "'.$img.'","count_img": "'.$d.'","error": "' . $error . '","msg": "' . $msg .  '","file": "<a href=' . $vLink . ' target=_blank>' . $vLink .  '</a>","wid_tmp": "' . $video->wall_id .  '","title": "' . $video->title .  '"}';
	}
}
?>