// JavaScript Document
var xmlhttp = false;
if(window.XMLHttpRequest) {
	xmlhttp = new XMLHttpRequest();	
} else if(window.ActiveXObject) {
	try {
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");	
	} catch(e) {
		try {
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		} catch(e) {
			alert('XMLHttpRequest initialization failed.');
		}
	}
}