<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegister	
{
	/*
	 *  @params array(); inner means only run by system; field_name means check specific field
	 */
	function formValidate($params)
	{
		$result = array();
		
		$result['success']= true;
		$result['script']= false;
		$result['title']= JText :: _('Valid');
		$result['content']= JText :: _('It is valid on the value(s)');
		
		if(!$this->isInner($params))
		{
			return $this->getError(null);
		}
		
		$field = oseObject::getValue($params,'field_name',false);
		if($field)
		{
			$vField =  str_replace('.','_',$field);
			$fieldValue = JRequest::getCmd('field_value');
			$updated = call_user_func(array($this,"validate{$vField}"),$fieldValue);
			$updated['field'] = $field;
			
			$updated['script'] = empty($updated['success'])?true:false;
			return $updated;
		}
		else
		{
			$methods = get_class_methods($this);
			
			foreach($methods as $key => $method)
			{
				if(strpos( $method, 'validate') !== false)
				{
					$field = str_replace('validate','',strtolower($method));
					$fieldValue = JRequest::getCmd($field);
					$updated = call_user_func(array($this,$method),$fieldValue);
					$updated['field'] = $field;
					if(!$updated['success'])
					{
						$updated['script'] = true;
						return $updated;
					}
				}
				else
				{
					continue;
				}
			}
			
			return $result;
		}
		
	}
	
	protected function isInner($params)
	{
		if(oseObject::getValue($params,'inner',false))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	protected function getError($text)
	{
		$result['success']= false;
		$result['title']= JText :: _('Invalid');
		$result['content']= JText :: _($text);
		
		return $result;
	}
}
?>