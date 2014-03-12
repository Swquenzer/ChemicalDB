<?php
	require('admin/AcidRainDBLogin.php');
	$cas = $_GET['cas'];
	//Query for locations based on $room
	$query = "SELECT Name FROM `chemical` WHERE CAS=?";
	$stmt = $db->stmt_init();
	if(!$stmt->prepare($query)) {
		echo("get_loc.php: Error preparing query");
	} else {
		$stmt->bind_param('s', $cas);
		$stmt->execute();
		if ($result = $stmt->get_result()) {
			#Create buttons for each room
			while ($row = $result->fetch_array(MYSQLI_BOTH)) {
				echo "<option value='$row[0]' onclick='chemSelect(\"" . addslashes($row[0]) . "\")'>$row[0]</option>\n";
			}
			$stmt->close();
		} else {
			echo("get_loc.php: Error getting result");
		}
	}
	$db->close();
?>