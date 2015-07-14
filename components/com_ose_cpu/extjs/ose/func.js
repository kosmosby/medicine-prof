Ext.ns('ose');
	Ext.QuickTips.init();
	
	Ext.apply(Ext.form.VTypes,{
		noSpace: function(val,field)	{
			var match = /^[\w|\d+|#|\u0100-\u017F|\u0180-\u024F|\u0041-\u007A|\u00C0-\u00FF|\u0600-\u06FF|\u0370-\u03FF|\u1F00-\u1FFF][\w|\s|.'"@:;,#-|\/|\u0100-\u017F|\u0180-\u024F|\u0041-\u007A|\u00C0-\u00FF|\u0600-\u06FF|\u0370-\u03FF|\u1F00-\u1FFF]*[\w|.'"@:;,#-|\u0100-\u017F|\u0180-\u024F|\u0041-\u007A|\u00C0-\u00FF|\u0600-\u06FF|\u0370-\u03FF|\u1F00-\u1FFF|\s]$/.test(val);
			return match;
		}
		,noSpaceText: 'This field has space in beginning or in the end'
	})
	
	ose.msg = new Ext.App();
	
	ose.ajax = function()	{
		return {
			formSuccess:function(form,action)	{
				var msg = action.result;
				ose.msg.setAlert(msg.title,msg.content);
			}
			
			,formFailure:function(form,action)	{
				if (action.failureType === Ext.form.Action.CLIENT_INVALID){
					ose.msg.setAlert(Joomla.JText._('Notice'),Joomla.JText._('Please_check_the_notice_in_the_form'));
		        }
		
				if (action.failureType === Ext.form.Action.CONNECT_FAILURE) {
		           Ext.Msg.alert('Error',
		            'Status:'+action.response.status+': '+
		            action.response.statusText);
		
		        }
		
		        if (action.failureType === Ext.form.Action.SERVER_INVALID){
		            var msg = action.result;
					ose.msg.setAlert(msg.title,msg.content);
		        }
			}
			
			,formSuccessMB: function(form,action,func)	{
				var msg = action.result;
				Ext.Msg.show({
				   title: msg.title
				   ,msg: msg.content
				   ,width: 600
				   ,buttons: Ext.MessageBox.OK
				   //,multiline: true
				   ,fn: func
				   //,prompt
				   //,closable: false
				   //,animEl: 'addAddressBtn'
				   ,icon: Ext.MessageBox.INFO
				});
				//Ext.Msg.alert(msg.title,msg.content,func	);
			}
			
			,formFailureMB:function(form,action,func)	{
				if (action.failureType === Ext.form.Action.CLIENT_INVALID){
					Ext.Msg.alert('Notice','Please check the error notice in the form');
		        }
		
				if (action.failureType === Ext.form.Action.CONNECT_FAILURE) {
		           Ext.Msg.alert('Error',
		            'Status:'+action.response.status+': '+
		            action.response.statusText);
		
		        }
		
		        if (action.failureType === Ext.form.Action.SERVER_INVALID){
		            var msg = action.result;
		            Ext.Msg.alert(msg.title,msg.content	,func);
		
		            /*
					Ext.Msg.show({
						title: msg.title
						,msg: msg.content
						,buttons: Ext.Msg.OK,
						,function
					});
					*/
		        }
			}
			
			,ajaxSuccess:function(response,opt)	{
				var msg = Ext.decode(response.responseText);
				ose.msg.setAlert(msg.title,msg.content);
			}
		
			,ajaxFailure:function(response,opt)	{
				var msg = Ext.decode(response.responseText);
				ose.msg.setAlert(msg.title,msg.content);
			}
		}
	}()
			

	ose.reload = function()	{
		//alert('ddd');
		window.location.reload();
	}


	ose.combo = function(){
		return {
			getCountryCombo: function(title,name,number,mode)	{
				if(typeof(mode) == 'undefined'){
					var mode = 'local'
				}

				var combo = new Ext.form.ComboBox({
		            fieldLabel: title
		            ,codeNumber: number
		            ,hiddenName: name
		            ,itemId: name
		            ,xtype: 'combo'
		            //,editable: false
				    ,typeAhead: true
				    ,triggerAction: 'all'
				    ,lazyRender:false
				    ,listClass: 'combo-left'
				    ,lastQuery: ''
				    ,mode: mode//'remote'
				    ,forceSelection: true
				    ,store: new Ext.data.Store({
				  		proxy: new Ext.data.HttpProxy({
				            url: 'index.php?option=com_ose'
				            ,method: 'POST'
			      		})
					  	,baseParams:{task: "getCountry"}
					  	,reader: new Ext.data.JsonReader({
					    	root: 'results'
					    	,totalProperty: 'total'
					    	,idProperty: 'country_id'
					  	},[
					    {name: 'code_3', type: 'string', mapping: 'country_3_code'}
					    ,{name: 'code_2', type: 'string', mapping: 'country_2_code'}
					    ,{name: 'subject', type: 'string', mapping: 'country_name'}
					    ,{name: 'country_id', type: 'string', mapping: 'country_id'}
					  	])
					})
				    ,valueField: 'code_'+number
				    ,displayField: 'subject'
		        })

		        return combo;
			}
			,getStateCombo: function(title,name,number,mode)	{
				if(typeof(mode) == 'undefined'){
					var mode = 'local'
				}

				var combo = new Ext.form.ComboBox({
		            fieldLabel: title
		            ,hiddenName: name
		            ,itemId: name
		            ,country: ''
		            ,xtype: 'combo'
				    ,typeAhead: true
				    ,triggerAction: 'all'
				    ,lazyRender:false
				    ,listClass: 'combo-left'
				    //,lastQuery: ''
				    //,editable: false
				    //,emptyText:'None'
				    ,mode: mode//'remote'
				    ,forceSelection: true
				    ,store: new Ext.data.Store({
				  		proxy: new Ext.data.HttpProxy({
				            url: 'index.php?option=com_ose'
				            ,method: 'POST'
			      		})
					  	,baseParams:{task: "getState"}
					  	,reader: new Ext.data.JsonReader({
					    	root: 'results'
					    	,totalProperty: 'total'
					    	,idProperty: 'state_id'
					  	},[
					    {name: 'code_3', type: 'string', mapping: 'state_3_code'}
					    ,{name: 'code_2', type: 'string', mapping: 'state_2_code'}
					    ,{name: 'subject', type: 'string', mapping: 'state_name'}
					    ,{name: 'country_id', type: 'string', mapping: 'country_id'}
					  	])
					  	,listeners:{
					  		load: function(s,r){
					  			var defaultData = {
				                    state_id: 9999,
				                    code_3: '--',
				                    code_2: '--',
				                    subject: 'Not required',
				                    country_id: 'all'
				                };
				                var recId = s.getTotalCount(); // provide unique id
				                var p = new s.recordType(defaultData, 0); // create new record

				                s.insert(0,p);
					  		}
					  	}
					})
				    ,valueField: 'code_'+number
				    ,displayField: 'subject'
		        })

		        var combo = Ext.apply(combo,{
		        	doQuery : function(q, forceAll){
				        q = Ext.isEmpty(q) ? '' : q;
				        var qe = {
				            query: q,
				            forceAll: forceAll,
				            combo: this,
				            cancel:false
				        };
				        if(this.fireEvent('beforequery', qe)===false || qe.cancel){
				            return false;
				        }
				        q = qe.query;
				        forceAll = qe.forceAll;
				        if(forceAll === true || (q.length >= this.minChars)){
				            if(this.lastQuery !== q){
				                this.lastQuery = q;
				                if(this.mode == 'local'){
				                    this.selectedIndex = -1;
				                    if(forceAll){
				                        //this.store.clearFilter();
				                        this.store.filter([{
											fn   : function(record) {
												return  (record.get('country_id') == this.country || record.get('country_id') == 'all')
											},
											scope: this
										}])
				                    }else{
				                        this.store.filter([{
											fn   : function(record) {
												return (record.get('subject').toLowerCase().indexOf(q.toLowerCase()) == 0) && (record.get('country_id') == this.country || record.get('country_id') == 'all')
											},
											scope: this
										}])
				                    }
				                    this.onLoad();
				                }else{
				                    this.store.baseParams[this.queryParam] = q;
				                    this.store.load({
				                        params: this.getParams(q)
				                    });
				                    this.expand();
				                }
				            }else{
				                this.selectedIndex = -1;
				                this.onLoad();
				            }
				        }
				    }
		        })
		        return combo;
			}

			,relateCountryState: function(country,state,initValue)	{
			
				country.addListener('select',function(c,r,i){
				
					var sr = r;
					state.country = r.get('country_id')
					state.getStore().filter([{
						fn   : function(record) {
							return record.get('country_id') == sr.get('country_id') || record.get('country_id') == 'all'
						},
						scope: this
					}]);
					
					if(c.getValue() == 'AUS' || c.getValue() == 'AU')	{
						state.valueField = 'code_3';
					}	else	{
						state.valueField = 'code_2';
					}
					
					if(state.getStore().getCount() > 1)	{
						state.setValue(state.getStore().getAt(1).get(state.valueField))
					}	else	{
						state.setValue('--');
					}
				},this);
				
				country.setValue(initValue);
			}

			,relateMscIdOption: function(mscList,mscOptions)	{
				mscOptions.getStore().on('load',function(s,r){
					//mscList.setValue(initValue);
		  			//var i = mscList.getStore().findExact('code',initValue);
		  			//alert(country.getStore().getTotalCount());
		  			//country.fireEvent('select',country,country.getStore().getAt(i),i)
				})

				mscList.getStore().on('load',function(s,r){
					mscOptions.getStore().load();
				});


				mscList.on('select',function(c,r,i){
					var sr = r;
					mscOptions.getStore().filter([{
						fn   : function(record) {
							return record.get('msc_id') == c.getValue()
						},
						scope: this
					}]);
					if(mscOptions.getStore().getCount() > 0)	{
						//state.setValue(state.getStore().getAt(0).get('code'))
					}	else	{
						//state.setValue('all');
					}
				});
			}
			
			,getCountryCombo1: function(title,name,number,mode)	{
				if(typeof(mode) == 'undefined'){
					var mode = 'local'
				}

				var combo = new Ext.form.ComboBox({
		            fieldLabel: title
		            ,codeNumber: number
		            ,hiddenName: name
		            ,itemId: name
		            ,xtype: 'combo'
		            //,editable: false
				    ,typeAhead: true
				    ,triggerAction: 'all'
				    ,lazyRender:false
				    ,listClass: 'combo-left'
				    ,lastQuery: ''
				    ,mode: mode//'remote'
				    ,forceSelection: true
				    ,store: new Ext.data.ArrayStore({
				  		root: 'results'
				    	,totalProperty: 'total'
				    	,idProperty: 'country_id'
				  		,fields:[
						    {name: 'code_3', type: 'string', mapping: 'country_3_code'}
						    ,{name: 'code_2', type: 'string', mapping: 'country_2_code'}
						    ,{name: 'subject', type: 'string', mapping: 'country_name'}
						    ,{name: 'country_id', type: 'string', mapping: 'country_id'}
					  	]
					})
				    
				    ,valueField: 'code_'+number
				    ,displayField: 'subject'
		        })

		        return combo;
			}
			,getStateCombo1: function(title,name,number,mode)	{
				if(typeof(mode) == 'undefined'){
					var mode = 'local'
				}

				var combo = new Ext.form.ComboBox({
		            fieldLabel: title
		            ,hiddenName: name
		            ,itemId: name
		            ,country: ''
		            ,xtype: 'combo'
				    ,typeAhead: true
				    ,triggerAction: 'all'
				    ,lazyRender:false
				    ,listClass: 'combo-left'
				    //,lastQuery: ''
				    //,editable: false
				    //,emptyText:'None'
				    ,mode: mode//'remote'
				    ,forceSelection: true
				    ,store: new Ext.data.ArrayStore({
				  		root: 'results'
				    	,totalProperty: 'total'
				    	,idProperty: 'state_id'
				  		,fields:[
						    {name: 'code_3', type: 'string', mapping: 'state_3_code'}
						    ,{name: 'code_2', type: 'string', mapping: 'state_2_code'}
						    ,{name: 'subject', type: 'string', mapping: 'state_name'}
						    ,{name: 'country_id', type: 'string', mapping: 'country_id'}
					  	]
					  	,listeners:{
					  		load: function(s,r){
					  			var defaultData = {
				                    state_id: 9999,
				                    code_3: '--',
				                    code_2: '--',
				                    subject: 'Not required',
				                    country_id: 'all'
				                };
				                var recId = s.getTotalCount(); // provide unique id
				                var p = new s.recordType(defaultData, 0); // create new record

				                s.insert(0,p);
					  		}
					  	}
					})
				    ,valueField: 'code_'+number
				    ,displayField: 'subject'
		        })

		        var combo = Ext.apply(combo,{
		        	doQuery : function(q, forceAll){
				        q = Ext.isEmpty(q) ? '' : q;
				        var qe = {
				            query: q,
				            forceAll: forceAll,
				            combo: this,
				            cancel:false
				        };
				        if(this.fireEvent('beforequery', qe)===false || qe.cancel){
				            return false;
				        }
				        q = qe.query;
				        forceAll = qe.forceAll;
				        if(forceAll === true || (q.length >= this.minChars)){
				            if(this.lastQuery !== q){
				                this.lastQuery = q;
				                if(this.mode == 'local'){
				                    this.selectedIndex = -1;
				                    if(forceAll){
				                        //this.store.clearFilter();
				                        this.store.filter([{
											fn   : function(record) {
												return  (record.get('country_id') == this.country || record.get('country_id') == 'all')
											},
											scope: this
										}])
				                    }else{
				                        this.store.filter([{
											fn   : function(record) {
												return (record.get('subject').toLowerCase().indexOf(q.toLowerCase()) == 0) && (record.get('country_id') == this.country || record.get('country_id') == 'all')
											},
											scope: this
										}])
				                    }
				                    this.onLoad();
				                }else{
				                    this.store.baseParams[this.queryParam] = q;
				                    this.store.load({
				                        params: this.getParams(q)
				                    });
				                    this.expand();
				                }
				            }else{
				                this.selectedIndex = -1;
				                this.onLoad();
				            }
				        }
				    }
		        })
		        return combo;
			}

			,relateCountryState1: function(country,state,initValue)	{
			
				country.addListener('select',function(c,r,i){
				
					var sr = r;
					state.country = r.get('country_id')
					state.getStore().filter([{
						fn   : function(record) {
							return record.get('country_id') == sr.get('country_id') || record.get('country_id') == 'all'
						},
						scope: this
					}]);
					
					if(c.getValue() == 'AUS' || c.getValue() == 'AU')	{
						state.valueField = 'code_3';
					}	else	{
						state.valueField = 'code_2';
					}
					
					if(state.getStore().getCount() > 1)	{
						state.setValue(state.getStore().getAt(1).get(state.valueField))
					}	else	{
						state.setValue('--');
					}
				},this);
				
				country.setValue(initValue);
			}

			,getLocalJsonData: function(c,data)	{
				//c.mode = 'local';
				var store = c.getStore();
				var rs = store.reader.readRecords(data)
				store.add(rs.records);
			}
		}
	}()

	ose.refreshGrid = function(grid)	{
		//grid.getStore().removeAll();
		grid.getStore().reload();
		grid.getView().refresh();
	}
	
	ose.openTerms = function(el,app)	{
		var addonTermsWin = new Ext.Window({
			width: 800
			,height: 500
			,bodyStyle: 'padding: 10px'
			,autoScroll: true
			,modal: true
			,autoLoad: {
				url:getCurrentLocation()+'index.php?option=com_ose_commerce'
				,params:{'id' : el.getAttribute('subjectid'),task:"getTerms",app:app}
				,callback: function(el, success, response, opt)	{
					el.update('');

					var info = Ext.decode(response.responseText);
					addonTermsWin.setTitle(info.subject);
					addonTermsWin.update(info.body);
				}
			}
		}).show().alignTo(Ext.getBody(),'t-t');
	}

	ose.checkCookie = function()
	{
		var cookieEnabled = (navigator.cookieEnabled) ? true : false;

		if (typeof navigator.cookieEnabled == "undefined" && !cookieEnabled)
		{
			document.cookie="testcookie";
			cookieEnabled = (document.cookie.indexOf("testcookie") != -1) ? true : false;
		}
		return cookieEnabled;
	}
	
	ose.JText= {strings:{},_:function(a,b){return typeof this.strings[a.toUpperCase()]!=="undefined"?this.strings[a.toUpperCase()]:b},load:function(a){for(var b in a){this.strings[b.toUpperCase()]=a[b]}return this}};Joomla.replaceTokens=function(c){var b=document.getElementsByTagName("input");for(var a=0;a<b.length;a++){if((b[a].type=="hidden")&&(b[a].name.length==32)&&b[a].value=="1"){b[a].name=c}}};
	
Ext.onReady(function(){
	var cookieactive = ose.checkCookie();
	if (cookieactive==false )
	{
		Ext.fly('ose-reg').update('<span class="errormessage">Cookies must be allowed: Your browser is currently set to block cookies from OSE website. Please enable cookies in your browser to shop in our website.</span>');
	}
})