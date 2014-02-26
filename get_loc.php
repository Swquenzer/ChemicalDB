<?php
require('admin/AcidRainDBLogin.php');
$room = $_GET['room'];
//Query for locations based on $room
$query = $db->prepare("SELECT DISTINCT Location FROM inventory WHERE Room=?");
	$query->bind_param('s', $room);
	$query->execute();
	if ($result = $query->get_result()) {
		#Create buttons for each room
		while ($row = $result->fetch_row()) {
			echo "<input type='button' class='locBut' value='$row[0]' onclick='addLocation(this.value)'>";
		}
		$result->close();
	}
?>