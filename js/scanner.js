/********************************************************
*********************************************************
 * File: Scanner.js
 * Description: General purpose javascript methods for scanner.php
 * Author: Stephen Quenzer
 * Date Modified: March 21, 2014
 * List of methods:
	# ajaxRequest(url, callback)
	# verifyCAS()
	# updateLabelName(chemName)
	# 
	# 
	# 
********************************************************
********************************************************
 
===========================================================================================*/
function createSlider(quant) {
	var slider = new dhtmlxSlider("updateSlider", 300);
	slider.linkTo('quant');
	slider.init();
	slider.setSkin("dhx_skyblue");
	slider.setValue(quant);
}
 /******************************************
 * Name: ajaxRequest(url, callback)
 * Description: A general purpose ajax request callback function. 
 * 				Allows many methods to make ajax requests through 
 *				the use of 'callback' parameter
 * Parameters:
	# url		[string]: Url where data is being sent (and recieved from)
	# callback	[function]: General callback function that processes results of ajax request
 ******************************************/
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

 /******************************************
 * Name: verifyCAS()
 * Description: Verifies that CAS number entered is of the proper form
 ******************************************/
function verifyCAS() {
	var cas = new RegExp("^[0-9]{2,6}-[0-9]{2}-[0-9]$"); //CAS regular expression
	var input = document.getElementById('cas').value;
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

 /******************************************
 * Name: verifyNewData(field, table, value, input)
 * Description: Verifies that user wants to add new data to the DB
 * Parameters:
	# field	[string || undefined]: Column name in database to query against
	# table [string]: Table name in database to query against
	# value [string]: Value of user input
	# input [string]: Specifies the type of input
 ******************************************/
function verifyNewData(field, table, value, input) {
	//input parameter is optional: Default value does nothing, otherwise verify_new_data.php receives $_GET['input']=input
	input = typeof input !== 'undefined' ? "&input="+input : "";
	//if value is "", return
	if(value === "") return;
	ajaxRequest("verify_new_data.php?field="+field+"&table="+table+"&value="+value+input, function() {
		if(request.readyState == 4 && request.status == 200) {
			var response = request.responseText;
			//If response returns text, put in popup, otherwise do nothing
			if(response != "") {
			activatePopup(response);
			}
		}
	});
}

 /******************************************
 * Name: updateLabelName(chemName)
 * Description: Adds a chemical name as label under barcode
				when user inputs new chemical name
 * Parameters:
	# chemName	[string]: Name of user-input chemical
 * Note: Currently unused- now using CAS numbers under barcode
 ******************************************/
//Updates label to include chemical name
//Currently unused
function updateLabelName(chemName) {
	if(chemName != "") {
		var label = document.getElementById('barcodeLabel');
		label.src = label.src + "&label=" + chemName;
	}
}

 /******************************************
 * Name: changeQuantity(amount, field)
 * Description: Adds or subtracts a quantity from an input field
 * Parameters:
	# amount [string]:  Amount and sign for addition or subtraction
	# field [string]:	Input field to add or subtract from
 ******************************************/
function changeQuantity(amount, field) {
	var quant = document.getElementById(field).value;
	//If no value in input, make value=0
	if(isNaN(parseInt(quant))) {
		document.getElementById(field).value = "0";
	}
	quant = document.getElementById(field).value;
	// substr(0,1) grabs sign from front of value
	// substr(1) removes sign from front of value 
	if (amount.substr(0,1) == "+") {
		var temp = parseInt(quant) + parseInt(amount.substr(1));
	} else {
		var temp = parseInt(quant) - parseInt(amount.substr(1));
	}
	
	//Why can't 'quant' be used here? 
	document.getElementById(field).value = temp.toString(); 
}

 /******************************************
 * Name: activateField(field)
 * Description: Displays a given field by setting display:auto (inline css)
 * Parameters:
	# field [string]:  ID for field to display
 ******************************************/
function activateField(field) {
	var buttonsWrapper = document.getElementsById(field);
	buttonsWrapper.styles.display="auto";
}

 /******************************************
 * Name: deactivateField(field)
 * Description: Hides a given field by setting display:none (inline css)
 * Parameters:
	# field [string]: ID for field to hide
 ******************************************/
function deactivateField(field) {
	var buttonsWrapper = document.getElementById(field);
	buttonsWrapper.style.display="none";
}

 /******************************************
 * Name: getLocations(room)
 * Description: Takes in a room name and returns all locations
				corresponding to the given room
 * Parameters:
	# room [string]: Room name used to find corresponding locations
 ******************************************/
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

 /******************************************
 * Name: createLocations(room)
 * Description: Creates all locations corresponding to the given room name
 * Parameters:
	# room [string]: Room name used to find corresponding locations
 * Notes: Used in conjunction with getLocations(room)
 ******************************************/
function createLocations(room) {
	//Add selected room to text input
	document.getElementById('room').value = room;
	getLocations(room);
	deactivateField('roomsWrapper');
	var locationsWrapper = document.getElementById('locWrapper');
}

 /******************************************
 * Name: addLocation(loc)
 * Description: Adds the given location into the location input
 * Parameters:
	# loc [string]:	Location name
 * Notes: Used in conjunction with return values of getLocations(room)
 ******************************************/
function addLocation(loc) {
	document.getElementById('loc').value=loc;
	deactivateField('locWrapper');
}

 /******************************************
 * Name: activatePopup(message)
 * Description: Creates a popup on top of all other elements and inserts
				'message' into the popup
 * Parameters:
	# message [string]:	Message to be inserted into popup
 ******************************************/
function activatePopup(message) {
	var popupBG = document.getElementById('popupBG');
	popupBG.className="active";
	var popup = document.getElementById('popup');
	popup.className="active";
	popup.innerHTML+="<span id='popupMessage'>"+message+"</span>";
}

 /******************************************
 * Name: deactivatePopup()
 * Description: Removes popup and all content inside of popup
 ******************************************/
function deactivatePopup() {
	var popupBG = document.getElementById('popupBG');
	var popup = document.getElementById('popup');
	popupBG.className='';
	popup.className='';
	popup.innerHTML="";
}

/* ----Unused------
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
*/

 /******************************************
 * Name: autofill(arr, multiple)
 * Description: Fills update form inputs automatically based on chosen chemical.
				Also sets session variables for current values. This ensures we can 
				find the correct original record to update with new values
 * Parameters:
	# arr [array]: Array of values to insert into corresponding input fields
	# multiple [boolean]: True/false value deciding whether there are multiple instances
						  of the same chemical in the DB. This might occur when two of the
						  same chemical are in different locations.
 ******************************************/
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
	createSlider(quant);
	document.getElementById('size').value = unitSize;
	document.getElementById('unit').value=unit;
	document.getElementById('manufacturer').value=mftr;
	deactivatePopup();
	//Display filled fields
	console.log("Change Display: ");
	document.getElementById('lowerFieldsWrapper').style.display = "";
	console.log("Display Changed");
	//Add session variables for original values
	ajaxRequest("get_data.php?option=saveOrigValues&chem="+chem+"&room="+room+"&loc="+loc+"&quant="+quant+"&size="+unitSize+"&unit="+unit+"&mftr="+mftr, function() {
		if(request.readyState == 4 && request.status == 200) {
			var response = request.responseText;
		}
	});
}

 /******************************************
 * Name: chemSelect(index)
 * Description: Finds array of chemicals to autofill in their respective input fields
				based on id of chemical chosen (not cas number). Id's are created in 
				index form (chem1, chem2, etc).
 * Parameters:
	# index [string]: Id of chemical chosen (not cas number)
 ******************************************/
function chemSelect(index) {
	var arr = document.getElementById(index).childNodes;
	autofill(arr, true);
}

 /******************************************
 * Name: chemList(chem)
 * Description: Sets up a POPUP list of chemicals (not the same as built-in selection list) 
				if there are multiple chemical options with the same name. 
				If no duplicates exist, automatically fills in fields with values
				corresponding to given chemical.
 * Parameters:
	# chem [string]: Name of chemical to query against
 * Notes: Used for autofill feature in update form
 ******************************************/
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
				//Multiple options
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

 /******************************************
 * Name: getData(cas)
 * Description: Gets the data corresponding with the given cas number
 * Parameters:
	# cas [string]:	CAS number 
 ******************************************/
function getData(cas) {
	var chems = document.getElementById("chems");
	//Pre-load indicator
	chems.innerHTML+="<img src='gfx/loader.gif'>";
	ajaxRequest("get_data.php?option=getChemList&cas="+cas, function() {
		if(request.readyState == 4 && request.status == 200) {
			var loader = chems.getElementsByTagName("img")[0];
			chems.removeChild(loader);
			var response = request.responseText;
			var chemListTag = chems.getElementsByTagName("select")[0];
			chemListTag.innerHTML=response;
			//Check if only one options
			// 2 ===> 1 for child node + 1 for inner text
			if(chemListTag.childNodes.length == 2) {
				chemList(chemListTag.childNodes[0].value);
				chemListTag.parentNode.innerHTML="";
			}
		}
	});
}

 /******************************************
 * Name: addMftr(mftr, exist)
 * Description: Adds a new manufacturer to database if user input(ed) manufacturer
				doesn't exist yet.
 * Parameters:
	# mftr [string]: Name of manufacturer
	# exist [boolean]: True/false value of whether the given manufacturer currently exists
 * Notes: Method is called from verify_new_data.php, where user decides IF they want the
		  new manufacturer to be added.
 ******************************************/
function addMftr(mftr, exist) {
	if(!exist) {
		//Add manufacturer to database
		ajaxRequest("add_manufacturer.php?mftr="+mftr, function() {
			if(request.readyState == 4 && request.status == 200) {
				var mftr = request.responseText;
			}
		});
	}
	//Add manufacturer to input
	var manu = document.getElementById('manufacturer');
	manu.value = mftr;
	deactivatePopup();
}

 /******************************************
 * Name: autoFillMftr()
 * Description: When user inputs a chemical name in 'add' form, if the
				chemical is already in the database, and if the chemical has
				a manufacturer, the manufacturer is automatically added
				as a value into the manufacturer input field.
 ******************************************/
function autoFillMftr() {
	var chem = document.getElementById('chemical').value;
	if (chem != "") {
		//If a chemical name has been entered
		//Pre-load indicator
		var mftrLoader = document.getElementById('mftrLoader');
		mftrLoader.innerHTML+="<img src='gfx/loader.gif'>";
		ajaxRequest("get_mftr.php?chem="+chem, function() {
			if(request.readyState == 4 && request.status == 200) {
				//Remove laoder
				loader = mftrLoader.getElementsByTagName("img")[0];
				mftrLoader.removeChild(loader);
				//Get response
				var response = request.responseText;
				document.getElementById('manufacturer').value=response;
			}
		});
	}
}