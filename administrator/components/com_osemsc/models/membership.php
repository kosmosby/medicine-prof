<?php
/**
 * @version     5.0 +
 * @package        Open Source Membership Control - com_osemsc
 * @subpackage    Open Source Access Control - com_osemsc
 * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
 * @author        Created on 15-Nov-2010
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
*/
defined('_JEXEC') or die("Direct Access Not Allowed");


class oseMscModelMembership extends oseMscModel
{
    public function __construct()
    {
        parent::__construct();
    } //function


	function getProperty($msc_id)
	{
		$db = oseDB::instance();

		oseDB::lock('#__osemsc_acl READ');

		$query = " SELECT * FROM `#__osemsc_acl`"
				." WHERE id = {$msc_id}"
				;
		$db->setQuery($query);
		$item = oseDB::loadItem();;
		oseDB::unlock();
		return $item;
	}

	function getOrder($msc_id)
	{
		$db = oseDB::instance();
		$msc = oseRegistry::call('msc');
		$siblings = $msc->getParentChildren($msc_id);
		return $siblings;
	}

	function getloginRedirect($msc_id)
	{
		$db = oseDB::instance();
		$msc = oseRegistry::call('msc');
		$siblings = $msc->getloginRedirect($msc_id);
		return $siblings;
	}

	function getTree()
	{
		$db = oseDB::instance();

		$msc = oseRegistry::call('msc');
		$list = $msc->retrieveTree();

		foreach ($list as $key => $item)
		{
			$list[$key]['displayText'] = '('.$item['id'].') '.$item['treename'];
		}
		return $list;
	}

	function getItem($msc_id)
	{
		$msc = oseRegistry::call('msc');

		$item = $msc->getInfo($msc_id);
		$item['params'] = empty($item['params'])?'{}':$item['params'];
		$item['params'] = oseJson::decode($item['params'],true);
		return $item;
	}

	function getExtItem($msc_id,$type)
	{
		$msc = oseRegistry::call('msc');

		$item = $msc->getExtInfo($msc_id,$type);

		return $item;
	}

	function update($post)
	{
		if(isset($post['msc_id']))
		{
			$post['id'] = $post['msc_id'];
			unset($post['msc_id']);
		}
		else
		{
			return true;
		}

		$msc = oseRegistry::call('msc');

		// Parameters
		$node = $msc->getInfo($post['id'],'obj');
		$params = empty($node->params)?'{}':$node->params;
		$params = oseJson::decode($params);
		$params->after_payment_menuid = JRequest::getVar('after_payment_menuid',null);

		$post['params'] = oseJson::encode($params);
		// Parameters

		$post['description'] = JRequest::getVar('description', null,'post','string', JREQUEST_ALLOWRAW);


		// for check box
		$post['published'] = empty($post['published'])?0:$post['published'];
		$post['showtitle'] = empty($post['showtitle'])?0:$post['showtitle'];

		if(isset($post['ordering']))
		{
			$ordering = $post['ordering'];
			unset($post['ordering']);



			if($node->ordering != $ordering)
			{
				if(!$msc->orderChange($node,$ordering))
				{
					return false;
				}
			}
		}

		$uploadImg = $post['image'];

		if(empty($uploadImg))
		{
			unset($post['image']);
		}
		else
		{
			$uploadImg = basename($uploadImg);

			$tmplPath = JPATH_COMPONENT_SITE.DS.'assets'.DS.'tmpl_image'.DS."{$uploadImg}";

	    	if(JFile::exists($tmplPath))
	    	{
	    		$fileType = JFile::getExt($uploadImg);

	    		$des = JPATH_COMPONENT_SITE.DS.'assets'.DS.'msc_logo'.DS."msc-{$post['id']}.{$fileType}";

	    		$logoPath = JPATH_COMPONENT_SITE.DS.'assets'.DS.'msc_logo';
	    		$files = JFolder::files($logoPath,"msc-{$post['id']}.");

	    		foreach($files as $file)
	    		{
	    			JFile::delete($logoPath.DS.$file);
	    		}

	    		if(JFile::move($tmplPath,$des))
	    		{
	    			$post['image'] = "/components/com_osemsc/assets/msc_logo/msc-{$post['id']}.{$fileType}";
	    		}
	    		else
	    		{
	    			$post['image'] = null;
	    		}
	    		//$post['image'] = $files;
	    	}
	    	else
	    	{
	    		unset($post['image']);
	    	}
		}

		return $msc->update($post);
	}

	function getAction($action_name)
	{
		$msc = oseRegistry::call('msc');

		return $msc->runAddonAction($action_name);
	}

	function preview()
    {
    	$result = array();
    	$result['success'] = true;
    	$result['uploaded'] = false;
    	$result['img_path'] = null;

    	$uploadImg = JRequest::getVar('image', null, 'files', 'array' );
    	$des = JPATH_COMPONENT_SITE.DS.'assets'.DS.'tmpl_image'.DS.$uploadImg['name'];

    	if(JFile::exists($uploadImg['tmp_name']))
    	{
			if(!self::checkImage($uploadImg))
    		{
    			$result['success'] = true;
		    	$result['uploaded'] = false;
		    	$result['img_path'] = null;
		    	$result['title'] = JText::_('Error');
		    	$result['content'] = JText::_('Only .gif,.png,.jpeg and .jpg files are allowed');
		    	return $result;
    		}

    		$result['img_path'] = OSEMSC_F_URL."/assets/tmpl_image/{$uploadImg['name']}";//$uploadImg['tmp_name'].'.png';


    		if(JFile::upload($uploadImg['tmp_name'],$des))
	    	{
	    		$result['uploaded'] = true;
	    	}
	    	else
	    	{
	    		$result['title'] = JText::_('Error');
		    	$result['content'] = JText::_('The directory:')." ". dirname($des). " ". JText::_('is not writable, please change the file permission of the folder to a writable status and re-upload the image again');
	    	}

			//$result['results'] = 'ddd';
			return $result;
    	}
    	else
    	{
    		return $result;
    	}


    }

	function checkImage($image)
	{
		$allowExt = array();
		$allowExt[] = 'png';
		$allowExt[] = 'gif';
		$allowExt[] = 'jpeg';
		$allowExt[] = 'jpg';

		$ext = explode('/',$image['type']);

		if(in_array($ext[1],$allowExt))
		{
			/*
			$mimeType = self::getMimeType($image['tmp_name']);
		   	if (!empty($mimeType))
		   	{
			    $mimeType = explode("/", $mimeType);
			    if ($mimeType[0]!='image' || !in_array($mimeType[1], array('gif', 'png', 'jpg', 'jpeg')))
			    {
					$result['success'] = true;
			    	$result['uploaded'] = false;
			    	$result['img_path'] = null;
			    	$result['title'] = JText::_('Error');
			    	$result['content'] = JText::_('Alert! Possible hacking attempt - please make sure that the file uploaded is a real image file.');
			    	echo oseJSON::encode( $result); exit;
			    }
			    else
			    {
			    	return true;
			    }
		   	}
		   	else
		   	{
		   		return true;
		   	}
		   	*/
		   	return true;
		}
		else
		{
			return false;
		}
	}
	public static function getMimeType($filename)
        {
            if (function_exists('finfo_open'))
            {
                $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
                $content_type = finfo_file($finfo, $filename);
                finfo_close($finfo);
            }
            elseif (function_exists("mime_content_type"))
            {
                $content_type = mime_content_type($filename);
            }
            else
            {
            	$content_type = false;
            }
            return $content_type;
        }
}


