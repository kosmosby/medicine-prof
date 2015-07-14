<?php
defined('_JEXEC') or die(";)");

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.path');

class oseDebug extends oseObject
{
	protected $type = null, $path=null,$files = array();
	
	function __construct($path,$type)
	{
		$this->set('type',$type);
		$this->set('name',$path);
		$this->tree();
	}
	
	function search($key,$exact = false)
	{
		$html = oseHtml::getInstance('form');
		$html->append('<table width="1000px">');
		$html->sc('et');
		$html->append('<thead>');
		$html->sc('eta');
		$html->append('<tr>');
		$html->sc('eta');
		$html->append('<th width="5%">ID</th><th width="33%">File name</th><th>Path</th>');
		$html->sc('ets');
		$html->append('</tr>');
		$html->sc('ets');
		$html->append('</thead>');
		$html->sc('et');
		$i = 0;
		
		$html->append('<tbody>');
		$html->sc('eta');
		foreach($this->get('files') as $file)
		{
			$data = JFile::read($file);
			
			preg_match_all("/{$key}/",$data,$matches);
			
			
			if(count($matches[0]) > 0)
			{
				$html->append('<tr>');
				$html->sc('eta');
				$html->append('<td align="center">'.$i.'</td><td align="right">'.basename($file).'</td><td style="padding-left:20px">'.$file.'</td>');
				$html->sc('ets');
				$html->append('</tr>');
				$html->sc('et');
				
				$i++;
			}
		}
		$html->append('</tbody>');
		$html->sc('ets');
		$html->append('</table>');
		
		return $html->output();
	}
	
	function tree()
	{
		$path = $this->getPath();
		$this->set('files',JFolder::files($path,'.',true,true));
	}
	
	function getPath()
	{
		return $this->get('name');
	}
}