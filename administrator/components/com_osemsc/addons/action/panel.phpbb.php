<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionPanelPhpbb extends oseMscAddon
{
	var $phpbb_config = array();
    var $query = '';
    var $connection = '';

	function save($params = array())
	{
		$result = array();
		$post = JRequest::get('post');
		if (oseMscAddon::quickSavePanel('phpbb_',$post))
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('SAVE_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
		}

		return $result;
	}

	function create()
	{
		$msc_id = JRequest::getInt('msc_id',null);
		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
			return $result;
		}

		$jdb = oseDB :: instance();
		$query = "SELECT title FROM `#__osemsc_acl` WHERE `id` = '{$msc_id}'";
		$jdb->setQuery($query);
		$msc = $jdb->loadResult();

		$check = self::connect_phpbb();
		if (!$check)
        {
        	$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('UNABLE_TO_CONNECT_TO_PHPBB');
			return $result;
        }

	 		$phpbb_query = "SELECT * FROM `#__groups` WHERE `group_name` = '{$msc}'";
            $this->setQuery($phpbb_query);
            $result_query = $this->loadObjectList();
            if (empty($result_query))
            {
                $phpbb_query = "INSERT INTO `#__groups` (
									`group_id` ,
									`group_type` ,
									`group_founder_manage` ,
									`group_name` ,
									`group_desc` ,
									`group_desc_bitfield` ,
									`group_desc_options` ,
									`group_desc_uid` ,
									`group_display` ,
									`group_avatar` ,
									`group_avatar_type` ,
									`group_avatar_width` ,
									`group_avatar_height` ,
									`group_rank` ,
									`group_colour` ,
									`group_sig_chars` ,
									`group_receive_pm` ,
									`group_message_limit` ,
									`group_max_recipients` ,
									`group_legend`
									)
									VALUES (
									NULL , '0', '0', '{$msc}', '" . JRequest::getVar('name') . "', '', '7', '', '0', '', '0', '0', '0', '0', '', '0', '0', '0', '0', '1'
);";
                $this->setQuery($phpbb_query);
                $this->query();

                $result['success'] = true;
				$result['title'] = JText::_('DONE');
				$result['content'] = JText::_('CREATE_SUCCESSFULLY');
            }else{
            	$result['success'] = true;
				$result['title'] = JText::_('DONE');
				$result['content'] = JText::_('GROUP_NAME_ALREADY_EXISTED');
            }
            $this->close_phpbb();
            return $result;
	}
	function get_phpbb_group()
	{
		$check = self::connect_phpbb();
	 	if (!$check)
        {
        	return JText::_('PHPBB is not installed.');
        }
		$query = "SELECT * FROM `#__groups`";
        $this->setQuery($query);
        $items = $this->loadObjectList();
		$this->close_phpbb();
		$result = array();

		if(count($items) < 1)
		{
			$result['total'] = 0;
			$result['results'] = '';
		}
		else
		{
			$result['total'] = count($items);
			$result['results'] = $items;
		}

		return $result;
	}

   function connect_phpbb()
    {
    	$db=JFactory::getDBO();
		$query = "SELECT params FROM `#__jfusion` WHERE `name` = 'phpbb3' ";
	    $db->setQuery($query);
		$existing_serialized = $db->loadResult();

	    if (!empty($existing_serialized))
	    {
	      	$existing_params = unserialize(base64_decode($existing_serialized));
	    }else{
	    	return false;
	    }

	    $path=$existing_params['source_path'];
	    if (file_exists($path . DS . 'config.php'))
	    {
	        $file_config = $path.DS. 'config.php';
	        $file_lines = fread($fp = fopen($file_config, "r"), filesize($file_config));
	        fclose($fp);

	        $config = explode("\n", $file_lines);
	        $this->phpbb_config['dbhost'] = $this->get_phpbb_config($config, '$dbhost');
	        $this->phpbb_config['dbname'] = $this->get_phpbb_config($config, '$dbname');
	        $this->phpbb_config['dbuser'] = $this->get_phpbb_config($config, '$dbuser');
	        $this->phpbb_config['dbpasswd'] = $this->get_phpbb_config($config, '$dbpasswd');
	        $this->phpbb_config['table_prefix'] = $this->get_phpbb_config($config,
	            '$table_prefix');
	        $this->connection = mysql_connect($this->phpbb_config['dbhost'], $this->
	            phpbb_config['dbuser'], $this->phpbb_config['dbpasswd']);
	        $this->db = mysql_select_db($this->phpbb_config['dbname']);
	    }else{
	    	return false;
	    }
        return true;
    }
    function close_phpbb()
    {
        mysql_close($this->connection);
        $config = new JConfig();
		$this->connection = mysql_connect($config->host, $config->user, $config->password);
        $this->db = mysql_select_db($config->db);

    }
    function get_phpbb_config($config, $var)
    {
        foreach ($config as $config_field)
        {
            $str = strstr($config_field, $var);
            if ($str != false)
            {
                $value = explode('=', $config_field);
                $return = $value[1];
                $return = trim($return);
                $return = str_replace("'", "", $return);
                $return = trim($return,";");
            }
        }
        return $return;
    }

	function setQuery( $query )
	{
		$query = str_replace("#__", $this->phpbb_config['table_prefix'], $query) ;
        $this->query = $query ;

	}

    function loadResult()
    {
        $que = mysql_query($this->query) ;
        $ret = null;
        if ($row = mysql_fetch_row( $que )) {
            $ret = $row[0];
        }
        mysql_free_result( $que );
        return $ret;
    }

    function loadObject()
    {
    	$que = mysql_query($this->query) ;
		if (!$que) {
			return null;
		}
		$ret = null;
		if ($object = mysql_fetch_object( $que )) {
			$ret = $object;
		}
		mysql_free_result($que);
		return $ret;
    }
    function loadObjectList()
    {
        $array = array() ;
        $key = array() ;
        $que = mysql_query($this->query) ;
        while ($row = mysql_fetch_array($que))
        {
            $array[] = $row ;
        }
        return $array ;
    }
    function query()
    {
        mysql_query($this->query) ;
    }
}
?>