<?php
	require('admin/AcidRainDBLogin.php');
	session_start();
	switch ($_GET['option']) {
		case "getChemList":
			$cas = $_GET['cas'];
			//Query for locations based on $room
			$query = "SELECT chemical.name
								FROM chemical 
								JOIN inventory
								ON chemical.ID=inventory.ChemicalID
								WHERE chemical.CAS=?";
			$stmt = $db->stmt_init();
			if(!$stmt->prepare($query)) {
				echo("get_loc.php: getChemList: Error preparing query");
			} else {
				$stmt->bind_param('s', $cas);
				$stmt->execute();
				if ($result = $stmt->get_result()) {
					#Create buttons for each room
					while ($row = $result->fetch_array(MYSQLI_BOTH)) {
						echo "<option value='$row[0]' onclick='chemList(\"" . addslashes($row[0]) . "\")'>$row[0]</option>\n";
					}
					$stmt->close();
				} else {
					echo("get_loc.php: getChemList: Error getting result");
				}
			}
			$db->close();
			break;
		case "getDistinctChemList":
			$chem = $_GET['chemical'];
			//Query for information based on specific chemical chosen
			$query = "	SELECT chemical.Name, inventory.Room, inventory.location, inventory.ItemCount, inventory.Units, manufacturer.Name
						FROM inventory
						INNER JOIN chemical
						ON inventory.ChemicalID=chemical.ID
						INNER JOIN manufacturer
						ON inventory.MftrID=manufacturer.ID
						WHERE chemical.Name=?
					 ";
			$stmt = $db->stmt_init();
			if(!$stmt->prepare($query)) {
				echo("get_loc.php: getDistinctChemList: Error preparing query");
			} else {
				$stmt->bind_param('s', $chem);
				$stmt->execute();
				if ($result = $stmt->get_result()) {
					while($row = $result->fetch_array(MYSQLI_NUM)) {
						//Each iteration is new query result
						//Each query result is array of data in order:
						//Room, location, ItemCount, Size, Units
						//return query results so that we can parse as array in javascript
						echo json_encode($row);
						echo "|";
					}
				} else {
					echo("get_loc.php: getDistinctChemList: Error getting result");
				}
			}
			break;
		case "saveOrigValues":
			$_SESSION['chem'] = $_GET['chem'];
			$_SESSION['room'] = $_GET['room'];
			$_SESSION['loc'] = $_GET['loc'];
			$_SESSION['quant'] = $_GET['quant'];
			$_SESSION['unit'] = $_GET['unit'];
			$_SESSION['mftr'] = $_GET['mftr'];
			echo "<pre>";
			print_r($_SESSION);
			echo "</pre>";
			break;
	}
	
?>