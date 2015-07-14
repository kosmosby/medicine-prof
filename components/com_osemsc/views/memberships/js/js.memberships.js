Ext.onReady(function(){

	var mscSelectPaymentMode = function(msc_id,msc_option)	{

		Ext.get('osemsc-list').mask(Joomla.JText._('Loading'));

		Ext.Ajax.request({
			clientValidation: true,
			url: 'index.php?option=com_osemsc&controller=register',
			params:{task: 'subscribe', 'msc_id': msc_id, 'msc_option': msc_option, 'Itemid': Ext.get("Itemid").dom.value},
			success: function(response,opt){
				var msg = Ext.decode(response.responseText);
				if(msg.success)	{
					Ext.get('osemsc-list').unmask();
					Ext.Msg.wait(Joomla.JText._('Please_Wait'), Joomla.JText._('Redirecting'));
					window.location = msg.link;
				}	else	{
					Ext.get('osemsc-list').unmask();
				}

			}
		})
	}

	Ext.select('.msc-desc-box').setVisibilityMode(2);
	Ext.select('.msc-intro-box').setVisibilityMode(2);
	Ext.select('.msc-desc-box').setVisible(false);


	Ext.select('.msc-intro-box .show-detail').on('click',function(e,t,o){
		//alert(Ext.get(t).toSource())
		var intro = Ext.get(t).parent('.msc-intro-box');
		//alert(intro.toSource())
		intro.setVisible(false)
		intro.next().setVisible(true)
	});

	Ext.select('.msc-desc-box .show-detail').on('click',function(e,t,o){
		//alert(Ext.get(t).toSource())
		var full = Ext.get(t).parent('.msc-desc-box');
		full.setVisible(false)
		full.prev().setVisible(true)
	});

	Ext.select('.msc-button-select-m').on('click',function(e,t,o){
		var msc_id = t.id.replace('msc-button-select-m-','')
		var msc_option = Ext.get(t.id).findParent('.msc-first',50,true).child('.msc_options').getValue();
		mscSelectPaymentMode(msc_id,msc_option);
	});

	Ext.select('.msc-button-select-a').on('click',function(e,t,o){
		var msc_id = t.id.replace('msc-button-select-a-','')
		var msc_option = Ext.get(t.id).findParent('.msc-first',50,true).child('.msc_options').getValue();
		mscSelectPaymentMode(msc_id,msc_option);

	});

	var t = new Ext.Template(
	    '<table cellspacing="2px" width="100%">',
	    	'<thead class="osemsc-table-header">',
	    		'<tr>',
	    			'<th width = "80%"><b>'+Joomla.JText._('Subscription')+'</b></th>',
	    			'<th width = "20%"><b>'+Joomla.JText._('Price')+'</b></th>',
	    		'</tr>',
	    	'</thead>',
	        '<tbody>',
	        	'<tr class="membershipSummary-price">',
	    			'<td>{title}</td>',
	    			'<td>{standard_price}</td>',
	    		'</tr>',
	        '</tbody>',
	    '</table>',
	    {
	        compiled: true,      // compile immediately
	        disableFormats: true // See Notes below.
	    }
	);

	var t2 = new Ext.XTemplate(
	    '<table cellspacing="2px" width="100%">',
	    		'<tr>',
	    			'<td width = "20%"><b>'+Joomla.JText._('Subscription')+'</b></th>',
	    			'<td width = "20%">{title}</td>',
	    		'</tr>',
	        	'<tr class="membershipSummary-price">',
	    			'<td width = "20%">'+Joomla.JText._('TRIAL_PERIOD')+'</th>',
	    			'<td width = "20%">{trial_price} for {trial_recurrence}</td>',
	    		'</tr>',
	        	'<tr class="membershipSummary-price">',
	    			'<td width = "20%">'+Joomla.JText._('Standard_Price_After_Trial')+'</th>',
	    			'<td width = "20%">{standard_price} / {standard_recurrence}</td>',
	    		'</tr>',
	    '</table>',
	    {
	        compiled: true,      // compile immediately
	        disableFormats: true // See Notes below.
	    }
	);

	var showinfo = function(el)	{

		//alert('*[value='+el.getValue()+']')
		//var oArr = el.query('*[value='+el.getValue()+']');
		//var option = Ext.get(oArr[0]);
		var option=document.getElementById(el.getValue());
		if (option.getAttribute('has_trial') > 0)
		{
			var data = {
				trial_price: option.getAttribute('trial_price')
				,trial_recurrence: option.getAttribute('trial_recurrence')
				,standard_price: option.getAttribute('standard_price')
				,standard_recurrence: option.getAttribute('standard_recurrence')
				,title: option.getAttribute('title')
			}

			el.next().update(t2.apply(data));
		}
		else
		{
			var data = {
				standard_price: option.getAttribute('standard_price')
				,title: option.getAttribute('title')
			};
			el.next().update(t.apply(data));
		}
	}

	Ext.each(Ext.select('.msc_options').elements,function(item,i,all)	{
		var el = Ext.get(item);
		showinfo(el)

	})

	Ext.select('.msc_options').on('change',function(e,ht,o){
		var el = Ext.get(ht);
		showinfo(el)
	});
});

var showDetail = function(el,isFull)	{
	alert(Ext.get(el).dom);
}