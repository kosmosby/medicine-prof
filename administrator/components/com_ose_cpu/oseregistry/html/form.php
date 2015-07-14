<?php
defined('_JEXEC') or die(";)");
class oseHtmlForm
{
	protected $html = null;
	protected $level = 0;
	
	function append($html)
	{
		$this->html .= $html;
	}
	
	function addBreak()
	{
		$this->append("\r\n");
	}
	
	function addLevel($i = 1)
	{
		$this->level += $i; 
	}
	
	function subLevel($i = 1)
	{
		$this->level -= $i; 
		
		if($this->level < 0)
		{
			$this->level = 0;
		}
	}
	
	function setLevel($level)
	{
		$this->level = $level; 
	}
	
	function addTab($num = 1)
	{
		$string = str_repeat("\t",$num);
		$this->append($string);
	}
	
	// shortcut
	function sc($event)
	{
		switch($event)
		{
			case('e'):
				$this->addBreak();
			break;
			
			case('t'):	
				$this->addTab($this->level+1);
			break;
			
			case('et'):	
				$this->sc('e');
				$this->sc('t');
			break;
			
			// et add level
			case('eta'):	
				$this->addLevel();
				$this->sc('e');
				$this->sc('t');
			break;
			
			// et sub level
			case('ets'):	
				$this->subLevel();
				$this->sc('e');
				$this->sc('t');
			break;
		}
	}
	
	function createForm($action,$id=null, $name = null,$method = 'POST',$target = "_self")
	{
		$html = '<form';
		
		if(!empty($name))
		{
			$html .= ' name="'.$name.'"';
		}
		
		if(!empty($id))
		{
			$html .= ' id="'.$id.'"';
		}
		
		if(!empty($id))
		{
			$html .= ' target="'.$target.'"';
		}
		
		$html .= ' method="'.$method.'"';
		$html .= ' action="'.$action.'"';
		$html .= '>';
		
		$this->append($html);
	}
	
	function endForm()
	{
		$this->append('</form>');
	}
	
	function textfield($name,$value = null,$id = null,$class = null)
	{
		
	}
	
	function hidden($name,$value = null,$id = null,$class = null)
	{
		$html = '<input type="hidden"';
		$html .= ' name="'.$name.'"';
		$html .= ' value="'.$value.'"';
		$html .= '>';
		
		return $html;
	}
	
	function image($name,$id,$src,$alt = null)
	{
		$html = '<input type="image"';
		$html .= ' name="'.$name.'"';
		$html .= ' src="'.$src.'"';
		$html .= ' alt="'.$alt.'"';
		$html .= '>';
		
		return $html;
	}
	
	function submit($name,$value = null,$id = null,$class = null)
	{
		$html = '<button type="submit"';
		$html .= ' name="'.$name.'"';
		
		if(!empty($id))
		{
			$html .= ' id="'.$id.'"';
		}
		
		$html .= '>';
		$html .= $value;
		$html .= '</button>';
		return $html;
	}
	
	function output()
	{
		return $this->html;
	}
}
?>