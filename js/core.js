//Created by Stephen Quenzer, Josiah Driver, Isaac Tice, Charles Cooley
window.onload = function() {
	$('#delete').on("click", function(event) {
		deleteRecords();
	});
	$('#edit').on("click", function(event) {
		editRecords();
	});
	$('#normal').on("click", function(event) {
		normalMode();
	});
	$('#barcode').on("click", function(event) {
		getBarcodes();
	});
}

$(addTableEvents);

function postJSON(data, successFunction) {
	$.post("json.php", data, successFunction, "json").fail(function(event, status, msg ) { alert(status + ": " + msg); });
}

function getBarcodes() {
	//add 'normal mode' button
	$('#normal').attr('class', 'visible basicButton bgNormal');
	//remove 'barcodes' button if it exists
	$('#barcode').attr('class', 'invisible');
	//remove 'delete them!' button if it exists
	$('#submitDelete').attr('class', 'invisible');
	//remove 'edit' button if it exists
	$('#edit').attr('class', 'invisible');
	//remove 'delete' button if it exists
	$('#delete').attr('class', 'invisible');
	$('tbody tr').each(function() {
		var td = this.childNodes[5];
		var value = td.innerHTML;
		td.innerHTML = "<img src='barcodes/"+value+".png'>";
	});
}
//Revert to normal functionality of spreadsheet
function normalMode() {
	//change hover styles back to their normal state
	$('tbody tr').off("mouseenter");
	$('tbody tr td').off("mouseenter");
	//remove deletion checkboxes if they exist
	$("#chemical_spreadsheet tbody tr").each(function(index) {
		var cb = this.children[0].children[0];
		//If checkbox node exists, remove it
		if($(cb).length) cb.remove();
	});
	//revert barcode images to text if possible
	//if last column hold image elements
	if($('tbody tr td')[5].lastChild.nodeName == "IMG") {
		$('tbody tr').each(function() {
			var cas = this.lastChild.outerHTML.match(/[0-9]{2,6}-[0-9]{2}-[0-9]/g)[0];
			this.lastChild.innerHTML = cas;
		});
	}
	//remove 'normal mode' button at bottom of page
	$('#normal').attr('class', 'invisible');
	//remove 'delete them!' button if it exists
	$('#submitDelete').attr('class', 'invisible');
	//re-add 'delete' and 'edit' buttons at bottom of page
	$('#barcode').attr('class', 'visible basicButton bgNormal');
	$('#edit').attr('class', 'visible basicButton bgEdit');
	$('#delete').attr('class', 'visible basicButton bgDelete');
}

function changeRecord(data) {
	var td = data.parentNode;
	//ID of inventory record selected
	var ID = data.parentNode.parentNode.id.substring(4);
	//User input value
	var value = data.value;
	//If user inputs unit, remove-- we only want to send numeric value
	value = value.replace(/[ a-zA-Z]+$/,"");
	//Place (index) in table
	var index = data.id;
	//CAS number for current chemical
	var CAS = td.parentNode.lastChild.innerHTML;
	
	postJSON("update=individual&ID=" + ID + "&value=" + value + "&index=" + index + "&CAS=" + CAS, function(label) {
		//On Success
		//If editing amount column:
		if(td.cellIndex == 2) {
			td.innerHTML.replace(/[a-zA-Z]/,"");
			td.innerHTML = value + " " + label[0].Units;
		} else {
			td.innerHTML = value;
		}
		//Allow editing of other data again
		onClickEdit();
	});
}
function onClickEdit() {
	$('#chemical_spreadsheet tbody').on("click", "td:not(:nth-child(6))", function() {
	//Can't edit multiple values simultaneously
	$('#chemical_spreadsheet tbody').off("click");
		//Node Types
		var NT_ELEMENT = 1;
		var NT_TEXT		= 3;
		var td = this;
		//Node type and node name of inner value
		nt = td.firstChild.nodeType;
		nn = td.firstChild.nodeName;
		//This will return the index of the value
		//Based on its index as a child of its parent
		var index = Array.prototype.indexOf.call(this.parentNode.childNodes, td);
		var value = td.innerHTML;
		if(nn == "#text") {
			//If editing quantity column
			if(td.cellIndex == 2) {
				//Remove unit (mg, grams, etc) from value
				value = value.replace(/[a-zA-Z]+$/,"");
				td.innerHTML = "<input type='text' id='"+ index +"' value='"+ value +"' onblur='changeRecord(this)'>"+td.innerHTML;
			}
			td.innerHTML = "<input type='text' id='"+ index +"' value='"+ value +"' onblur='changeRecord(this)'>";
			this.firstChild.select();
		}
		//If user presses 'ENTER', update value
		//'keyup' so that only ONE function call is sent (otherwise held-down key will send multiple times)
		$(td).on("keyup", function(k) {
      if(k.which==13){
				changeRecord(td.childNodes[0]);
      }
    });
	});
}
function editRecords() {
	//Remove edit button after it's clicked
	$('#normal').attr('class', 'visible basicButton bgNormal');
	$('#barcode').attr('class', 'invisible');
	$('#edit').attr('class', 'invisible');
	$('#delete').attr('class', 'invisible');
	deactivateFilter();
	$("tbody tr td:not(:nth-child(6))").on("mouseenter", function() {
			$(this).css("background-color", "#DDDD9D");
	});
	$("tbody tr td:not(:nth-child(6))").on("mouseleave", function() {
			$(this).css("background-color", "");
	});
	onClickEdit();
}

function deactivateFilter() {
	$('#chemical_spreadsheet tbody').off("click");
}
function activateFilter() {
	$('#chemical_spreadsheet tbody').off("click");
}
function loadDelete() {
	$("#chemical_spreadsheet tbody tr").each(function(index) {
		var tr = $(this);
		var cb = "<input type='checkbox' name='deleteBox' value='" + index + "' >";
		//Select first element (td), and prepend the checkbox inside it
		tr.children().first().prepend(cb);
	});
	$('#chemical_spreadsheet tbody').on("click", "tr", function() {
		var cb = $(this).find("input[type='checkbox']");
		//Select or Deselect clicked checkbox depending on current state
		cb.prop( "checked" ) ? cb.prop("checked", false) : cb.prop("checked", true)
	});
}
function ajax_caller(ID) {
	postJSON("delete=inventory&ID=" + ID, function() {
		//Individual Successes
	});
}
function processDelete() {
	var ajax_calls = [];
    $('input[type=checkbox]').each(function () {
		var cb = this;
		if (this.checked) {
			var row = cb.parentNode.parentNode;
			var num = row.id && row.id.substr(4)
			if (num) {
				ajax_calls.push(ajax_caller(num));
				$.when.apply(this, ajax_calls).done(function() {
					$(row).fadeOut(300, function() { $(this).remove(); });
					$('#errorMessage span').html("<h1>Records successfully deleted!</h1>");
				});
			}
     }
    });
}
function deleteRecords() {
	//Remove '#delete' Button
	$('#normal').attr('class', 'visible basicButton bgNormal');
	$('#barcode').attr('class', 'invisible');
	$('#delete').attr('class', 'invisible');
	$('#edit').attr('class', 'invisible');
	//Add submit button for to-be-deleted records
	$('#tableOps form').prepend("<input type='button' class='basicButton bgDeleteWarning bgDelete' name='submitDelete' id='submitDelete' value='Delete Them!'>");
	//Block filtering when row is selected
	deactivateFilter();
	//Load deletion checkboxes
	loadDelete();
	//On hover, rows become red
	$("tbody tr").on("mouseenter", function() {
			$(this).children().filter("td").css("background-color", "#FF9999");
	});
	$("tbody tr").on("mouseleave", function() {
			$(this).children().filter("td").css("background-color", "");
	});
	$("#submitDelete").on("click", processDelete);
}

function filterThem() {
		var searchrow = $('#chemical_spreadsheet thead tr:first-child')[0]
		var NUM_RECORDS = 6;
		var rowsHidden = 0;
		var rows = $('#chemical_spreadsheet tbody')[0].rows
		for (var r = 0; r < rows.length; r++) {
			rows[r].style.display = ""
			//
			for (var c=0; c < NUM_RECORDS; c++) {
				if (c == 2) continue;
				var pattern = searchrow.cells[c].firstChild.value.replace(/^\s+/,"")
				if (rows[r].cells[c].innerHTML.search(pattern, "i") < 0) {
					rows[r].style.display = "none"
					rowsHidden++;
					continue;
				}
			}
		}
		$('#chemHiddenRowsMsg').text(rowsHidden > 0 ? "Entries not shown: " + rowsHidden : "");
	}

function addTableEvents() {
	var searchrow = $('#chemical_spreadsheet thead tr:first-child')[0]
	function fillSearch(event) {
		var searchbox = searchrow.cells[this.cellIndex].firstChild;
		var searchContent = this.textContent;
		//If filtering by barcode (as img tag)
		if(this.firstChild.localName == "img") {
			//take cas number out of image tag
			var cas = this.firstChild.outerHTML.match(/[0-9]{2,6}-[0-9]{2}-[0-9]/g)[0];
			searchContent = cas;
		}
		if (searchbox.value == searchContent) {
			searchbox.value = "";
		} else {
			searchbox.value = searchContent;
		}
		filterThem();
	}

	function fillTable(data) {
		$('#chemical_spreadsheet_body').html("");
		var rows = [];
		for (var i = 0; i < data.length; i++) {
			var record = data[i];
			rows.push("<tr id='item"+ record.ID +"'><td>");
			rows.push([record.Room, record.Location, record.ItemCount + " " + record.Units, record.Name, record.mfr, record.CAS].join("</td><td>"));
			rows.push("</td></tr>");
		}
		$('#chemical_spreadsheet_body').html(rows.join(""));
		$("#chemical_spreadsheet").trigger("update", [true]); // resort 
		filterThem();
	}

	$('#chemical_spreadsheet tbody').on("click", 'td:not(:nth-child(3))', fillSearch);
	$('#chemical_spreadsheet thead').on("keyup", 'input[type=search]', filterThem);
	$('#chemical_spreadsheet thead input[type=button]').on("click", function(event) { $('input[type=search]',searchrow).val(""); filterThem() });
	$('#addChem').on("click", function(event) { 
		location = "scanner.php";
	});
	
	// Provides client-side table sorting. Must come after table loading
	$.tablesorter.addParser({ id:"nums", is: function(s) { return false; }, format: function(s) { return parseFloat(s.replace(/^<span[^>]*>/,"")) }, type: 'numeric' });

	$("#chemical_spreadsheet").tablesorter( { selectorHeaders: "> thead tr:nth-child(2) th", sortList: [[3,0]], headers: { 2: { sorter: 'nums' } } } );

	var popupFields = $('#popup form')[0];
	function popupVal(name, value) {
		if (undefined != value) {
			popupFields["popup" + name].value = value
		}
		return popupFields["popup" + name].value
	}
	/*
	var popupData = { }
	$('#chemical_spreadsheet tbody').on("click", "td:nth-child(3)", function(event) {
		var vals = this.textContent.match(/(\d+)\s(\w+)\D+(\d+)\D+(\d+)/);
		var row = this.parentNode;
		popupVal("ID", row.id.substr(4));
		popupData.Quantity = popupVal("Quantity", vals[3]);
		popupVal("MoveQuantity", vals[3]);
		popupData.Size = popupVal("Size", vals[4]);
		popupData.Units = popupVal("Units", vals[2]);
		$('#popupName').html(row.cells[3].innerHTML + "  <small>" + row.cells[4].innerHTML + "</small>")
		$('#popupPlace').text("Room: " + row.cells[0].innerHTML + " - " + row.cells[1].textContent)
		$('#popup').toggleClass("active")
	});

	$('#popup').click(function(event) { 
		$('#popup').toggleClass("active");
	});

	$('#popup div').click(function(event) {
		return false;
	});
	*/

	// faking the datalist feature by showing the select box!
  	if (!document.createElement('datalist') || !window.HTMLDataListElement) {
		$("datalist").addClass("fake")
		$("datalist select").on("change", function(e) {
			$('input',this.parentNode.parentNode)[0].value = this.value;
		});
	
	}

	postJSON("fetch=all", fillTable);

	$('#updateAmount').on("click", function(event) {
		var quantity = popupVal('Quantity');
		var size = popupVal('Size');
		var ID = popupVal('ID');
		if (quantity == "0") {
			postJSON("delete=inventory&ID=" + ID, function(data) {
				$('#popup').toggleClass("active"); $('#item'+ID).fadeOut(500, function() { $(this).remove(); });
			});
		} else if (parseInt(quantity) > 0 && parseInt(size) > 0) {
			if (quantity != popupData.Quantity || size != popupData.Size || popupVal('Units') != popupData.Units) 
				postJSON("update=inventory&ID=" + ID + "&quantity=" + quantity + "&size=" + size + "&units=" + popupVal('Units'),
					function(data) { $('#popup').toggleClass("active"); postJSON("fetch=all", fillTable);
				});
			else $('#popup').toggleClass("active");
		} else {
			alert("Quantity and unit size must be integers. Unit size must be positive.")
		}
	});

	$('#transfer').on("click", function(event) {
		var room = popupVal('Room').replace(/^\s*/,"").replace(/\s*$/,"");
		var loc = popupVal('Location').replace(/^\s*/,"").replace(/\s*$/,"");
		var moveQuantity = parseInt(popupVal('MoveQuantity'));
		var origQuantity = parseInt(popupData.Quantity);
		var ID = popupVal('ID');
		if (moveQuantity <= 0) {
			alert("Can't transfer zero of it.");
		} else if (parseInt(moveQuantity) > origQuantity) {
			alert("Can't transfer more than " + origQuantity + " of it.");
		} else if (room == "") {
			alert("Need to specify a new room.");
		} else {
			postJSON("transfer=inventory&ID=" + ID + "&quantity=" + moveQuantity + "&room=" + room + "&location=" + loc, function(data) {
				if (moveQuantity == origQuantity) {
					postJSON("delete=inventory&ID=" + ID);
				} else {
					moveQuantity = origQuantity - moveQuantity
					postJSON("update=inventory&ID=" + ID + "&quantity=" + moveQuantity + "&size=" + popupData.Size + "&units=" + popupData.Units);
				}
				$('#popup').toggleClass("active"); postJSON("fetch=all", fillTable);
			});
		}
	});0
}
