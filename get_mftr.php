<?php
	require('admin/AcidRainDBLogin.php');
	$chem = $_GET['chem'];
	$query = "SELECT manufacturer.Name FROM manufacturer JOIN chemical ON manufacturer.ID=chemical.MfrID WHERE chemical.Name=(?)";
	$stmt = $db->prepare($query);
	$stmt->bind_param("s", $chem);
	$stmt->execute();
	if ($result = $stmt->get_result()) {
		if ($row = $result->fetch_array(MYSQLI_BOTH)) {
			echo $row[0];
		}
	}
	$stmt->close();
	$db->close();
?>