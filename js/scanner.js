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
	popup.innerHTML+="<span id='popupMessage'>"+message+"</span>";
}
function deactivatePopup() {
	var popupBG = document.getElementById('popupBG');
	var popup = document.getElementById('popup');
	popupBG.className='';
	popup.className='';
	popup.innerHTML="";
}
function popupConfirm() {
	
}
function getData(cas) {
	var chems = document.getElementById("chems");
	//Pre-load indicator
	chems.innerHTML+="<img src='gfx/loader.gif'>";
	ajaxRequest("get_data.php?option=getChemList&cas="+cas, function() {
		if(request.readyState == 4 && request.status == 200) {
			var loader = chems.getElementsByTagName("img")[0];
			chems.removeChild(loader);
			var response = request.responseText;
			chems.getElementsByTagName("select")[0].innerHTML=response;
		}
	});
}
function removeHiddenInput(form) {
	var inputArr = form.childNodes;
	console.log(inputArr[0].nodeType);
	for (var i=0; i<inputArr.length; i++) {
		if(inputArr[0].nodeName=="input") {
			console.log("INPUT");
		}
	}
}
function hiddenInput(form, name, value) {
	removeHiddenInput(form);
	var input = document.createElement('input');
	input.type="hidden";
	input.name=name;
	input.value=value;
	form.appendChild(input);
}
function autofill(arr, multiple) {
	if(multiple) {
		//When multiple options to choose from
		var chem	= arr[0].innerHTML;
		var room 	= arr[1].innerHTML;
		var loc 	= arr[2].innerHTML;
		var quant 	= arr[3].innerHTML;
		var unitSize = arr[4].innerHTML;
		var unit 	= arr[5].innerHTML;
		var mftr 	= arr[6].innerHTML;
	} else {
		//When only one option to choose from
		var chem 	= arr[0];
		var room 	= arr[1];
		var loc 	= arr[2];
		var quant 	= arr[3];
		var unitSize = arr[4];
		var unit 	= arr[5];
		var mftr 	= arr[6];
	}
	document.getElementById('chemical').value = chem;
	document.getElementById('room').value = room;
	document.getElementById('loc').value = loc;
	document.getElementById('quant').value = quant;
	document.getElementById('size').value = unitSize;
	document.getElementById('unit').value=unit;
	document.getElementById('manufacturer').value=mftr;
	deactivatePopup();
	//Display filled fields
	document.getElementById('lowerFieldsWrapper').style="display: auto;";
	//Add session variables for original values
	ajaxRequest("get_data.php?option=saveOrigValues&chem="+chem+"&room="+room+"&loc="+loc+"&quant="+quant+"&size="+unitSize+"&unit="+unit+"&mftr="+mftr, function() {
		if(request.readyState == 4 && request.status == 200) {
			var response = request.responseText;
			console.log(response);
		}
	});
}
function chemSelect(index) {
	//console.log("index = " + index);
	var arr = document.getElementById(index).childNodes;
	autofill(arr, true);
}
function chemList(chem) {
	var chemical = document.getElementById('chemical');
	chemical.value=chem;
	//Get data information
	ajaxRequest("get_data.php?option=getDistinctChemList&chemical="+chem, function() {
		if(request.readyState == 4 && request.status == 200) {
			var response = request.responseText;
			//"|" is separator between arrays (in JSON format)
			var splitArr = response.split("|");
			//Loop through each array - each array is a query result
			var queryArr = new Array();
			var returnMessage = "<h3>Choose chemical to update</h3><table><tr><th>Chemical</th><th>Room</th><th>Location</th><th>Quantity</th><th>Size</th><th>Unit</th><th>Mftr</th></tr>";
			var popup = document.getElementById('popup');
			if(splitArr.length == 2) {
					//One option
					queryArr = JSON.parse(splitArr[0]);
					autofill(queryArr, false);
			} else {
				for (var i=0; i<splitArr.length-1; i++) {
					queryArr = JSON.parse(splitArr[i]);
					//queryArr now holds a single array with individual query results
					returnMessage = returnMessage + "<tr onclick='chemSelect(this.id)' id='chem"+[i]+"'><td>"+queryArr[0]+"</td><td>"+queryArr[1]+"</td><td>"+queryArr[2]+"</td><td>"+queryArr[3]+"</td><td>"+queryArr[4]+"</td><td>"+queryArr[5]+"</td><td>"+queryArr[6]+"</td></tr>";
				}
				returnMessage = returnMessage + "</table>";
				activatePopup(returnMessage);
			}
		}
	});
}