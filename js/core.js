//Created by Stephen Quenzer, Josiah Driver, Isaac Tice, Charles Cooley
$(addTableEvents);

function postJSON(data, successFunction) {
	$.post("json.php", data, successFunction, "json").fail(function(event, status, msg ) { alert(status + ": " + msg); });
}

function processDelete() {
	var ajax_calls = [];
    $('input[type=checkbox]').each(function () {
		var cb = this;
	if (this.checked) {
			var row = cb.parentNode.parentNode.parentNode;
			var num = row.id && row.id.substr(4)
			if (num) {
				ajax_calls.push(ajax_caller(num));
				$.when.apply(this, ajax_calls).done(function() {
					$(row).fadeOut(300, function() { $(this).remove(); });
				});
			}
        }
    });
}

function addTableEvents() {
	var searchrow = $('#chemical_spreadsheet thead tr:first-child')[0]
	function fillSearch(event) {
		var searchbox = searchrow.cells[this.cellIndex].firstChild;
		if (searchbox.value == this.textContent) {
			searchbox.value = "";
		} else {
			searchbox.value = this.textContent;
		}
		filterThem();
	}

	function filterThem() {
		var rowsHidden = 0;
		var rows = $('#chemical_spreadsheet tbody')[0].rows
		for (var r = 0; r < rows.length; r++) {
			rows[r].style.display = ""
			for (var c=0; c < 5; c++) {
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

	function fillTable(data) {
		$('#chemical_spreadsheet_body').html("");
		var rows = [];
		for (var i = 0; i < data.length; i++) {
			var record = data[i];
			rows.push("<tr id='item"+ record.ID +"'><td>");
			rows.push([record.Room, record.Location,  "<span class='right'>" + record.ItemCount * record.Size + " " + record.Units + "</span>(" + record.ItemCount + " x " + record.Size + ")", record.Name, record.mfr].join("</td><td>"));
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
		location = "addChem.php";
	});
	$('#addMfr').on("click", function(event) { 
		postJSON("fetch=all", fillTable);
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
	});

}