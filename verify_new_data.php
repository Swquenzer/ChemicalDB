<?php
	require('admin/AcidRainDBLogin.php');
	$field = $_GET['field'];
	$table = $_GET['table'];
	$value = $_GET['value'];
	//Query for locations based on $room
	$query = "SELECT DISTINCT ? FROM ? WHERE ?=?";
	$stmt = $db->stmt_init();
	$match=true;
	if(!$stmt->prepare($query)) {
		echo("get_loc.php: Error preparing query");
	} else {
		$stmt->bind_param('ssss', $field, $table, $field, $value);
		$stmt->execute();
		$numRows = $stmt->num_rows;
		if($numRows == 0) {
			$match = false;
		} else $match = true;
	}
	if ($match) {
		//Are you sure you want to add item?
		echo "<p>ARE YOU SURE YOU WANT TO ADD ITEM?</p>";
	}
	$db->close();
?>