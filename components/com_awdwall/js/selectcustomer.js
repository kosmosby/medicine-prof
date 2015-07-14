var xmlHttp
function showCustomer(str,wid_tmp){
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Your browser does not support AJAX!");
		return;
	}
	
	//var url = document.URL;
	//var i = 'index';
	//var d = url.indexOf (i);
	//url = url.substr(0,d);
	var url = document.getElementById("url_root").value;
	var url = url+"joomla.php";
	//url = (_redirecturl)+"sql.php";
	url = url+ "?q=" + str + "&q_wid_tmp=" + wid_tmp;
	url = url + "&sid=" + Math.random();
	xmlHttp.onreadystatechange = stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);	
}
function stateChanged(){
	if (xmlHttp.readyState == 4){
		document.getElementById("txtHint").innerHTML = xmlHttp.responseText;		
	}
}
function GetXmlHttpObject(){
	var xmlHttp = null;
	try{
		// Firefox, Opera 8.0+, Safari
		xmlHttp = new XMLHttpRequest();
	}
	catch (e){
		// Internet Explorer
		try{
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");}
		catch (e){
			xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xmlHttp;
}