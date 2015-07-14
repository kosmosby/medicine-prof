<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionToolTranslate extends oseMscAddon
{
	function translate()
	{
		$result = array();
		$result['success'] = false;
		$result['selected'] = '';
		return $result;
		$content = JRequest::getVar('htmlcontent',null,'POST','string',4);
		$newLang = JRequest::getCmd('newLang','en-GB');
		$oldLang = oseMscPublic::getLang();;
		
		$langArray = oseMscPublic::getLangArray();
		if($newLang == $oldLang)
		{
			$content = null;
			$result['selected'] = $oldLang;
		}
		elseif(!isset($langArray[$newLang]) || !isset($langArray[$oldLang]) || !$langArray[$newLang]['allow'])
		{
			$content = null;
			$result['selected'] = $oldLang;
		}
		else
		{
			oseMscPublic::setLang($newLang);
			$result['selected'] = $newLang;
			
			$content_text = preg_split ('/<[^>]*>/', $content,-1);//'[oseslashcode]', 
			preg_match_all ('/(<[^>]*>)/', $content,$matches);
			
			$lang = oseMscPublic::instanceLang($oldLang);
			$lang->load('com_osemsc');
			
			$strings = $lang->getStrings();
			
			//$key = array_search('Oh done done done!',$strings);
			$lang->setLanguage($newLang);
			$lang->load('com_osemsc');
			
			$i = 0;
			$html = null;
			foreach($content_text as $key => $value)
			{
				if($i > 0)
				{
					$j = $i-1;
					$html .= $matches[0][$j];
					//$html .= $this->processString($value);
				}
				else
				{
					//$html .= $this->processString($value);
				}
				
				$html .= $this->processString($value,$strings,$lang);
				$i++;
			}
			$content = $html;
			//oseExit($content);
			$result['success'] = true;
		}
		
		$result['content'] = $content;
		
		return $result;
		/*
		$content_notag = preg_split ('/<[^>]*>/', $content,-1, PREG_SPLIT_OFFSET_CAPTURE);//'[oseslashcode]', 
		
		
		$contentArray = $content_notag;//explode('[oseslashcode]',$content_notag);
		//oseExit($contentArray);
		foreach($contentArray as $key => $array)
		{
			$trimResult = trim($array[0]);
			if(empty($trimResult))
			{
				//unset($contentArray[$key]);
			}
			else
			{
				//$contentArray[$key][0] = 'dds';
				preg_replace("/".$trimResult."/",'wahaha'.$key,$content,1);
			}
		}
		preg_match_all ('/(<[^>]*>)\s*([\w|\s|\*|\d|\.]*)/', $content,$matches);
		*/
	}
	
	private function processString($str,$strings,$lang)
	{
		// return the raw words
		$oldStr = trim($str);
		
		$newStrings = $lang->getStrings();
		
		$value = str_replace(array_values($strings),array_values($newStrings),$str);
	
		return $value;
		// clear the special characters
		/*
		$split = preg_split('/[\:]/',$oldStr);
		if(count($split) > 1)
		{
			$str = preg_replace('/([\w|\s|\d|\.]*)([\:])/','${1}',$oldStr );
		}
		else
		{
			$str = $oldStr;
		}
		*/
		// clear the number
		//$split = preg_split('/[\d|\.]*/',$oldStr);
		/*
		if(count($split) > 1)
		{
			$str = preg_replace('/([\w|\s|\d|\.]*)([\:])/','${1}',$oldStr );
		}
		else
		{
			$str = $oldStr;
		}
		*/
		/*
		$key = array_search($str,$strings);
		//oseExit($lang->getStrings());
		if(!empty($key))
		{
			$value = $lang->_($key);
		}
		else
		{
			$value = $str;
		}
		
		return $value;
		*/
	}
}
?>