<?php
/**
 * @version     4.0
 * @package		com_ose_msc
 * @subpackage	
 * @author		Open Source Excellence {@link http://www.opensource-excellence.co.uk}
 * @author		EasyJoomla {@link http://www.easy-joomla.org Easy-Joomla.org}
 * @author		SSRRN {@link http://www.ssrrn.com}
 * @author		Created on 15-Sep-2008
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

class phpbbdb
{
    var $phpbb_config = array();
    var $query = '';
    var $connection = '';
    var $db = '';
    
    function __construct()
    {
    }
    function getTableFields( $tables, $typeonly = true )
    {
    	$db=JFactory::getDBO();
        settype($tables, 'array'); //force to array
        $result = array();
 
        foreach ($tables as $tblval)
        {
        	//print_r($tblval);exit;
            $this->setQuery( 'SHOW FIELDS FROM ' . $tblval );
            //$db->setQuery($this->query);
            $fields = $this->loadObjectList();
 			
            if($typeonly)
            {
            	
                foreach ($fields as $field) {
                    $result[$tblval][$field->Field] =  $field->Type;
                }
                
            }
            else
            {
                foreach ($fields as $field) {
                    $result[$tblval][$field->Field] = $field;
                }
            }
        }
 
        return $result;
    }      
    function connect_phpbb()
    {
        $path=$this->getConfigpath();
        $file_config = $path.DS. 'config.php';
        $file_lines = fread($fp = fopen($file_config, "r"), filesize($file_config));
        fclose($fp);
        $config = explode(';', $file_lines);
        $this->phpbb_config['dbhost'] = $this->get_phpbb_config($config, '$dbhost');
        $this->phpbb_config['dbname'] = $this->get_phpbb_config($config, '$dbname');
        $this->phpbb_config['dbuser'] = $this->get_phpbb_config($config, '$dbuser');
        $this->phpbb_config['dbpasswd'] = $this->get_phpbb_config($config, '$dbpasswd');
        $this->phpbb_config['table_prefix'] = $this->get_phpbb_config($config,
            '$table_prefix');
        $this->connection = mysql_connect($this->phpbb_config['dbhost'], $this->
            phpbb_config['dbuser'], $this->phpbb_config['dbpasswd']);
        $this->db = mysql_select_db($this->phpbb_config['dbname']);
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
            }
        }
        return $return;
    }
   

	function setQuery( $query )
	{
		$query = str_replace("#__", $this->phpbb_config['table_prefix'], $query) ;
        $this->query = $query ;

	}    
	
    function loadObject()
    {
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($object = mysql_fetch_object( $cur )) {
			$ret = $object;
		}
		mysql_free_result( $cur );
		return $ret;
    }
     function loadObjectList($key='')
    {
        $array = array() ;
        //$key = array() ;
        $que = mysql_query($this->query) ;
        while ($row = mysql_fetch_object($que))
        {
            if ($key) {
                $array[$row->$key] = $row;
            } else {
                $array[] = $row;
            }
        }
        return $array ;
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
    
    function query()
    {
        mysql_query($this->query) ;
    }
    
    function getConfigpath()
    {
    	$db=JFactory::getDBO();
		$query = "SELECT params FROM #__jfusion WHERE name = 'phpbb3' ";
        $db->setQuery($query);
		$existing_serialized = $db->loadResult();
        if (!empty($existing_serialized))
        {
        	$existing_params = unserialize(base64_decode($existing_serialized));
        }
        $path=$existing_params['source_path'];
        
        return $path;
    
    }
	
}

?>
