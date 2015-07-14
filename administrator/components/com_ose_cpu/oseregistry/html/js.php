<?php
defined('_JEXEC') or die(";)");
class oseHtmlJs extends oseHTMLDraw
{
	protected $html = null;
	
	function __construct()
	{
		parent::__construct();
	}
	
	function addFunc($args = array())
	{
		$args = (array)$args;
		$args = implode(',',$args);
		$this->append("function({$args}){");
	}
	
	function endFunc($semicolon = '')
	{
		$this->append("}".$semicolon);
	}
	
	function onReady()
	{
		$html = 'Ext.onReady(function(){'."\r\n".'%s'."\r\n".'})';
		$this->html = sprintf($html,$this->html);
	}
	
	function makeItClosure()
	{
		$html = '(function(){'."\r\n".'%s'."\r\n".'})()';
		$this->html = sprintf($html,$this->html);
	}
	
	function wrap()
	{
		$html = '<script type="text/javascript">'."\r\n".'%s'."\r\n".'</script>';
		$this->html = sprintf($html,$this->html);
	}
}

class oseHtmlJsExt3 extends oseHtmlJs
{
	function create($xtype=false,$config = array())
	{
		$this->html = null;
		switch(strtolower($xtype))
		{
			case('arraystore'):
				$html = $this->arraystore($config);
			break;
			
			default:
				$html = $this->arraystore($config);
			break;
		}
		
		return $html;
	}
	
	function arraystore($config)
	{
		$fields = (is_array($config['fields']))?oseJson::encode($config['fields']):$config['fields'];
		$data = (is_array($config['data']))?oseJson::encode($config['data']):$config['data'];
		
		$config->root = oseObject::getValue($config,'root','results');
		$config->idProperty = oseObject::getValue($config,'idProperty','id');
		$config->totalProperty = oseObject::getValue($config,'totalProperty','total');
		$config->fields = oseObject::getValue($config,'fields',array());
		
		$c = oseJson::encode($config);
		$html = "new Ext.data.ArrayStore({$c})";
		return $html;
	}
	
	function storeData($data)
	{
		$html = null;
		$html .= '(function(){';
		$html .= 'var arr = ';
		$html .= oseJson::encode(array('total'=>count($data),'results'=>$data));
		$html .= 'return arr ';
		$html .= '}){}';
		return $html;
	}
	
	function monthCombo($config)
	{
		$config->root = oseObject::getValue($config,'root','results');
		$config->idProperty = oseObject::getValue($config,'idProperty','id');
		$config->totalProperty = oseObject::getValue($config,'totalProperty','total');
		$config->fields = oseObject::getValue($config,'fields',array());
		
		$c = oseJson::encode($config);
		$html = "var monthArray = Array();
			Ext.each(Date.monthNames,function(item,i,all)	{
				var n = String.leftPad(i+1,2,'0');
				monthArray[i] = [n,item];//item;
			});
			new Ext.form.ComboBox({
				hiddenName: '{$config->hiddenName}'
		    	,width: {$config->width}
		        // create the combo instance
			    ,typeAhead: true
			    ,editable: {$config->editable}
			    ,triggerAction: 'all'
			    ,mode: 'local'
			    ,forceSelection: {$config->forceSelection}
			    ,store: new Ext.data.ArrayStore({
			        idIndex: 0
			        ,fields: [
			            'myId',
			            'displayText'
			        ]
			        ,data: monthArray
			    })
			    ,valueField: 'myId'
			    ,displayField: 'displayText'
			    ,emptyText: 'Month'
			    ,allowBlank: {$config->allowBlank}
		    })";
		return $html;
	}
	
	function yearCombo($config)
	{
		$config->width = oseObject::getValue($config,'width','100');
		$config->editable = oseObject::getValue($config,'editable','false');
		$config->forceSelection = oseObject::getValue($config,'forceSelection','true');
		$config->allowBlank = oseObject::getValue($config,'allowBlank','false');
		
		$c = oseJson::encode($config);
		$html = "new Ext.form.ComboBox({
				hiddenName: '{$config->hiddenName}'
		    	,width: {$config->width}
			    ,typeAhead: true
			    ,triggerAction: 'all'
			    ,editable: {$config->editable}
			    ,lazyRender:true
			    ,mode: 'local'
			    ,forceSelection: {$config->forceSelection}
			    ,store: new Ext.data.ArrayStore({
			        idIndex: 0
			        ,fields: [
			            'myId',
			            'displayText'
			        ]
			        ,data: [
			        	 ['2011', '2011']
			        	,['2012', '2012']
			        	,['2013', '2013']
			        	,['2014', '2014']
			        	,['2015', '2015']
			        	,['2016', '2016']
			        	,['2017', '2017']
			        	,['2018', '2018']
			        	,['2019', '2019']
			        	,['2020', '2020']
			        	,['2021', '2021']
			        ]
			    })
			    ,valueField: 'myId'
			    ,displayField: 'displayText'
			    ,emptyText: 'Year'
			    ,allowBlank: {$config->allowBlank}
		    });";
		return $html;
	}
	
	/*
	 * hidden name
	 * width
	 */
	function combo($config)
	{
		$config->width = oseObject::getValue($config,'width',100);
		$config->editable = oseObject::getValue($config,'editable',false);
		$config->forceSelection = oseObject::getValue($config,'forceSelection',true);
		$config->allowBlank = oseObject::getValue($config,'allowBlank',false);
		$config->typeAhead = true;
		$config->triggerAction = 'all';
		$config->mode = 'local';
		
		$c = oseJson::encode($config);
		$html = "new Ext.form.ComboBox({$c})";
		
		return $html;
	}
}

class oseHtmlJsExt4 extends oseHtmlJsExt3
{
	
}
?>