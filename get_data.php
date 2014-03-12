<?php
	require('admin/AcidRainDBLogin.php');
	$cas = $_GET['cas'];
	//Query for locations based on $room
	$query = "SELECT DISTINCT Location FROM inventory WHERE Room=?";
	$stmt = $db->stmt_init();
	if(!$stmt->prepare($query)) {
		echo("get_loc.php: Error preparing query");
	} else {
		$stmt->bind_param('s', $room);
		$stmt->execute();
		if ($result = $stmt->get_result()) {
			#Create buttons for each room
			while ($row = $result->fetch_array(MYSQLI_BOTH)) {
				echo "<input type='button' class='locBut' value='$row[0]' onclick='addLocation(this.value)'>";
			}
			$stmt->close();
		} else {
			echo("get_loc.php: Error getting result");
		}
	}
	$db->close();
?>