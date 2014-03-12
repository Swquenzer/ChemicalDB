//Verifies that CAS number is of correct form
function verifyCAS() {
	var cas = new RegExp("^[0-9]{2,6}-[0-9]{2}-[0-9]$"); //CAS regular expression
	var input = document.getElementById('cas').value;
	console.log("Input.value: " + input + ", regex: " + cas);
	console.log(cas.test(input));
	if(cas.test(input)) { 
		//If CAS code is of correct format, do nothing
		return true;
	} else {
		//If CAS code of incorrect format, return error message
		var errorTag = document.getElementById("error");
		errorTag.innerHTML = "The input '" + input + "' is an invalid CAS number. Please re-scan.";
		return false;
	}
	return true;
}
//A general purpose ajax request callback function
var request;
function ajaxRequest(url, callback) {
	if (window.XMLHttpRequest) {
		//Modern Browsers
		request = new XMLHttpRequest();
	} else {
		//IE5 & 6
		request = new ActiveXObject("Microsoft.XMLHTTP");
	}
	request.onreadystatechange=callback;
	request.open("GET",url,true);
	request.send();
}
//Verifies that the user wants to add new data to the DB
//field & table parameters refer to database, value is the input
function verifyNewData(field, table, value) {
	//var field = document.getElementById(field);
	ajaxRequest("verify_new_data.php?field="+field+"&table="+table+"&value="+value, function() {
		if(request.readyState == 4 && request.status == 200) {
			var response = request.responseText;
			activatePopup(response);
		}
	});
}
function incQuantity(amount) {
	amount.substr(1); //Remove the '+' from the number
	var quant = document.getElementById('quant').value;
	//If no value in input, make value=0
	if(isNaN(parseInt(quant))) {
		document.getElementById('quant').value = "0";
	}
	quant = document.getElementById('quant').value;
	var temp = parseInt(quant) + parseInt(amount);
	//Why can't 'quant' be used here? 
	document.getElementById('quant').value = temp.toString(); 
}
function getLocations(room) {
	var locWrapper = document.getElementById('locWrapper');
	//Pre-load indicator
	locWrapper.innerHTML="<img src='gfx/loader.gif'>";
	//If process is processed successfully
	ajaxRequest("get_loc.php?room="+room, function() {
		if(request.readyState == 4 && request.status == 200) {
			locWrapper.innerHTML=request.responseText;
		}
	});
}
function createLocations(room) {
	//Add selected room to text input
	document.getElementById('room').value = room;
	getLocations(room);
	var roomsWrapper = document.getElementById('roomsWrapper');
	roomsWrapper.style.display="none";
	var locationsWrapper = document.getElementById('locWrapper');
}
function addLocation(loc) {
	document.getElementById('loc').value=loc;
	locWrapper.style.display="none";
}
function activatePopup(message) {
	var popupBG = document.getElementById('popupBG');
	popupBG.className="active";
	var popup = document.getElementById('popup');
	popup.className="active";
	popup.innerHTML+="<span id='confirmation'>"+message+"</span>";
}
function deactivatePopup() {
	var popupBG = document.getElementById('popupBG');
	var popup = document.getElementById('popup');
	popupBG.className='';
	popup.className='';
}
function popupConfirm() {
	
}
function getData(cas) {
	//Pre-load indicator
	var chems = document.getElementById("chems");
	console.log("CHEMS: " + chems);
	chems.innerHTML+="<img src='gfx/loader.gif'>";
	ajaxRequest("get_data.php?cas="+cas, function() {
		if(request.readyState == 4 && request.status == 200) {
			var loader = chems.getElementsByTagName("img")[0];
			chems.removeChild(loader);
			var response = request.responseText;
			console.log(response);
			chems.getElementsByTagName("select")[0].innerHTML=response;
		}
	});
}
function chemSelect(chem) {
	var chemical = document.getElementById('chemical');
	chemical.value=chem;
}