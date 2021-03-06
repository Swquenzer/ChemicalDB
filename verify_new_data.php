<?php
	require('admin/AcidRainDBLogin.php');
	$field = $_GET['field'];
	$table = $_GET['table'];
	$value = $_GET['value'];
	$match = false;
	//Query for locations based on $room
	$query = "SELECT DISTINCT $field FROM $table WHERE $field=?";
	if(!$stmt = $db->prepare($query)) {
		echo("verify_new_data.php: Error preparing query");
	} else {
		$stmt->bind_param("s", $value);
		$stmt->execute();
		$results = $stmt->get_result();
		$numRows = $results->num_rows;
		if($numRows == 0) {
			//If entry is currently NOT in database
			$match = false;
		} else {
			//Entry IS currently in the database
			$match = true;
		}
	}
	if (!$match) {
		// The entry being checked is NOT already in the database
		if($field == "Location" || $field == "Room") {
			echo "	<span id='innerPopupWrapper'><h1>Are you sure?</h1>";
			echo "	<p class='popupMessage'>The $field '$value' is not currently in the database.</p>";
			echo "
					<form>
						<input type='button' value='I understand' onclick='deactivatePopup()'>
					</form>
					</span><!--innerPopupWrapper-->
				 ";
		} elseif(isset($_GET['input']) && $_GET['input'] == "mftr") {
			echo "	<span id='innerPopupWrapper'><h1>Are you sure?</h1>";
			echo "	<p class='popupMsg'>The manufacturer you entered is not yet in the database. Are you sure you want to add $value as a new manufacturer?</p><!--popupMsg-->
					<form>
					<p>
						<label>Add new manufacturer '$value':
						<input type='button' value='Add' name='$value' onclick='addMftr(this.name, false)'>
						</label>
					</p>
					-OR-
					<p>
					<label>Use an existing manufacturer:
					<select multiple>
					";
			#Create list of options
			if ($result = $db->query("SELECT DISTINCT Name FROM manufacturer")) {
				while ($row = $result->fetch_row()) {
					echo "<option value='$row[0]' onclick='addMftr(this.value, true)'>$row[0]</option>";
				}
				$result->close();
			}
			echo "	</select></label></p>
					-OR-
					<p><label>Add different manufacturer <br>
					<input type='text' id='newMftr'>
					<input type='button' value='Add' onclick='addMftr(document.getElementById(\"newMftr\").value, false)'>
					</label></p>
					</form>
					</span><!--innerPopupWrapper-->
				 ";
		}
	} elseif(isset($_GET['input']) && $_GET['input'] == "chem") {
		//The chemical being added IS already in the database
		// <<< indicates a HEREDOC string
		echo <<<HERE
<h1>Are you sure?</h1>
<p class='popupMessage'>The chemical entered is already in the database. 
If you are adding a duplicate chemical to a new location, press continue. 
Otherwise, enter a new chemical name</p>
<form>
<p><label>Use chemical: '$value'
<input type="button" value="continue" onclick='deactivatePopup(); autoFillMftr();'>
</label></p>
-OR-
<p><label>Enter new chemical:
<input type="text" id="newChem">
<input type="button" value="Add" onclick="document.getElementById('chemical').value=document.getElementById('newChem').value; deactivatePopup()">
</form>
HERE;
	}
	$db->close();
?>