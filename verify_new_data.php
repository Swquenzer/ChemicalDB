<?php
	require('admin/AcidRainDBLogin.php');
	$field = $_GET['field'];
	$table = $_GET['table'];
	$value = $_GET['value'];
	$match = true;
	//Query for locations based on $room
	$query = "SELECT DISTINCT $field FROM $table WHERE $field=?";
	$stmt = $db->stmt_init();
	if(!$stmt->prepare($query)) {
		echo("verify_new_data.php: Error preparing query");
	} else {
		$stmt->bind_param('s', $value);
		$stmt->execute();
		$numRows = $stmt->num_rows;
		if($numRows == 0) {
			//If entry is currently NOT in database
			$match = true;
		} else $match = false;
	}
	if ($match) {
		echo "<p style='color: red;'>Are you sure you want to add $field '$value'?</p>";
		echo "
				<form>
					<input type='button' value='Yes' onclick='javascriptfunction()'>
				</form>
			 ";
	}
	$db->close();
?>