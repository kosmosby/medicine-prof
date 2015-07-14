<?php
defined('_JEXEC') or die("Direct Access Not Allowed");

require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');

oseHTML::initCss();
oseHTML::initScript();

$com = OSECPU_PATH_JS.'/com_ose_cpu/extjs';
       	
oseHTML::script($com.'/ose/app.msg.js','1.5');
oseHTML::script(oseMscConfig::generateGmapScript(),'1.5');
oseHTML::script($com."/gmap/panel.js",'1.5');
?>
<div id="osemsc-mtree-gmap"></div>
<script type="text/javascript">
	var mapwin = new Ext.Panel({
        //layout: 'fit',
        border: false,
        //width:400,
        height:400,
        
        items: [{
            xtype: 'gmappanel',
            ref:'gmap',
            zoomLevel: 14,
            gmapType: 'map',
            mapConfOpts: ['enableScrollWheelZoom','enableDoubleClickZoom','enableDragging'],
            mapControls: ['GSmallMapControl','GMapTypeControl','NonExistantControl'],
            
			
            addAddrMarker : function(addr, marker){
				var s = this.setCenter;
				this.geocoder = new GClientGeocoder();

		        this.geocoder.getLocations(addr, function(response){
		        	place = response.Placemark[0];
		        	point = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
		        	
		        	Ext.applyIf(marker,G_DEFAULT_ICON);

			        if (marker.clear === true){
			            this.getMap().clearOverlays();
			        }
			        if (marker.center === true) {
			            this.getMap().setCenter(point, this.zoomLevel);
			        }
			
			        var mark = new GMarker(point,marker);
			        
			        GEvent.addListener(mark,"mouseover",function(){
			        	mark.openInfoWindowHtml("Title:"+marker.title+"<br>Name:"+marker.name+"<br>Email:"+marker.email);
			        });
			        
			        
			        /*
			        if (typeof marker.listeners === 'object'){
			            for (evt in marker.listeners) {
			                GEvent.bind(mark, evt, this, marker.listeners[evt]);
			            }
			        }
			        */
			        
			        this.getMap().addOverlay(mark);
		        	
					//this.addMarker(point,marker,marker.clear,marker.center,marker.listeners);

				}.createDelegate(this));
				
		    }
		    
        }],
    });
    
Ext.onReady(function(){
	var link_id = <?php echo JRequest::getInt('link_id',0);?>;
	
	if(link_id > 0)	{
		var addonMemberMtLocation_Store = new Ext.data.Store({
			proxy: new Ext.data.HttpProxy({
	        	url: 'index.php?option=com_osemsc&controller=member',
	        	method: 'POST'
	  		}),
		  	baseParams:{task: "action",action:"member.directory.getLocationsByMt",link_id:link_id}, 
			reader: new Ext.data.JsonReader({   
		              // we tell the datastore where to get his data from
		    	root: 'results',
		    	totalProperty: 'total',
		    	idProperty: 'location_id',
		 	},[ 
		    	{name: 'id', type: 'int', mapping: 'location_id'},
		    	{name: 'address', type: 'string', mapping: 'address'},
		    	{name: 'city', type: 'string', mapping: 'city'},
		    	{name: 'state', type: 'string', mapping: 'state'},
		    	{name: 'country', type: 'string', mapping: 'country'},
		    	{name: 'postcode', type: 'string', mapping: 'postcode'},
		    	{name: 'contact_title', type: 'string', mapping: 'contact_title'},
		    	{name: 'contact_name', type: 'string', mapping: 'contact_name'},
		    	{name: 'contact_email', type: 'string', mapping: 'contact_email'},
		    
		  	]),
		  	//sortInfo:{field: 'id', direction: "ASC"},
		  	autoLoad:{},
			  
	  		listeners: {
			  	load: function(store,records,options){
			  		if(records.length > 0){
				  		Ext.each(records,function(r,i,all){
				  			var data = r.data;
				  			//alert(r.data.toSource());
					   		var address = '"'+data.address+','+data.city+','+data.state+','+data.postcode+','+data.country+'"';
					   		
					   		mapwin.gmap.addAddrMarker(address,{
					        	title: data.contact_title,
					        	name: data.contact_name,
					        	email: data.contact_email,
					        	clear: false,
					        	center: (i == 0)?true:false,
					        	listeners: {
					        		mousedown: function(p,marker)	{
					        			alert(this.toSource());
					        			Ext.Msg.alert(data.contact_title,data.contact_name);
					        		},
					        		click: function(){
					        			alert('clcik');
					        		}
					        	}
					        });
				  		});
				  		
				  		mapwin.render('osemsc-mtree-gmap');
			  		}
			  	}
			}
		});
	}
	
    
})   
	
</script>