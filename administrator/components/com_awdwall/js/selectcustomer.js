var xmlHttp
function showCustomer(str){
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Your browser does not support AJAX!");
		return;
	}
	var url = document.URL;
	var i = 'administrator';
	var d = url.indexOf (i);
	url = url.substr(0,d);
	url = url+"joomla.php";
	
	//var url = "/joomla.php";
	//url = (_redirecturl)+"sql.php";
	url = url+ "?q=" + str;
	url = url + "&sid=" + Math.random();	
	xmlHttp.onreadystatechange = stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);	
}
function stateChanged(){
	if (xmlHttp.readyState == 4){
		document.getElementById("txtHint").innerHTML = xmlHttp.responseText;
		window.location.reload();		
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