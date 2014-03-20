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
		if($field == "Location") {
			echo "	<p class='message'>Are you sure you want to add $field '$value'?</p>";
			echo "
					<form>
						<input type='button' value='Yes' onclick='javascriptfunction()'>
					</form>
				 ";
		} elseif(isset($_GET['input']) && $_GET['input'] == "mftr") {
			echo "	<span id='innerPopupWrapper'><h1>Wait!</h1>";
			echo "	<p class='popupMsg'>The manufacturer you entered is already in the database. If '$value' is a new manufacturer you would like to add to the database, click continue. Otherwise, select an existing manufacturer:
					<form>
					<input type='button' value='continue' name='$value' onclick='addMftr(this.name, false)'>
					<select multiple>
					";
			#Create list of options
			if ($result = $db->query("SELECT DISTINCT Name FROM manufacturer")) {
				while ($row = $result->fetch_row()) {
					echo "<option value='$row[0]' onclick='addMftr(this.value, true)'>$row[0]</option>";
				}
				$result->close();
			}
			echo "	</select>
					</p><!--popupMsg-->
					</form>
					</span><!--innerPopupWrapper-->
				 ";
		}
	} elseif(isset($_GET['input']) && $_GET['input'] == "chem") {
		//The chemical being added IS already in the database
		// <<< indicates a HEREDOC string
		echo <<<HERE
<form>
<p class='message'>The chemical entered is already in the database. 
If you are adding a duplicate chemical to a new location, 
<input type="button" value="continue" name="continue">
otherwise, 
please enter a new chemical name.</p>
<input type="text" value="$value" name="newChem">
</form>
HERE;
	}
	$db->close();
?>