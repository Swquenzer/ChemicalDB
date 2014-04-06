<?php
session_start();

// Don't proceed unless the user is logged in!

if (@$_SESSION["AccessLevel"] <= 0) {
	http_response_code(403);
	echo "You must be logged in to use this function.";
        exit;
}


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 01 Jan 1996 00:00:00 GMT');

function fail($msg) {
	http_response_code(500);
	exit($msg);
	if ($stmt) exit($stmt->error . $db->error . $customError);
	exit($db->error);
}

function fetch_all($handle) {   
    $rows = array();
    if ($handle instanceof mysqli_result) {
        while ($row = $handle->fetch_assoc())
            $rows[] = $row;
    } else if ($handle instanceof mysqli_stmt) {
	$handle->store_result();
        $variables = array();
        $data = array();
        $meta = $handle->result_metadata();
        while ($field = $meta->fetch_field())
            $variables[] = &$data[$field->name]; // pass by reference
	$meta->close();
        call_user_func_array(array($handle, 'bind_result'), $variables);
        $i=0;
        while ($handle->fetch()) {
            $rows[$i] = array();
            foreach($data as $k=>$v)
                $rows[$i][$k] = $v;
            $i++;
        }
	$handle->free_result();
    }
    return $rows;
}

require('admin/AcidRainDBLogin.php');

if (@$_POST['fetch'] == "all") {
	$result = $db->query("CALL Get_Spreadsheet()")  OR fail($db->error);
	$data = fetch_all($result);
	$result->close();
	header('Content-type: application/json');
	exit(json_encode($data));

} elseif (@$_POST['delete'] == "inventory") {
	$ID = (int) $_POST['ID'];
	$stmt = $db->prepare("DELETE FROM inventory WHERE ID=?")  OR fail($db->error);
	$stmt->bind_param('i', $ID)  OR fail($stmt->error);
	$stmt->execute()  OR fail($stmt->error);
	$stmt->affected_rows == 1  OR fail("No such record found for delete."); 
	$stmt->close();
	exit("{}");

} elseif (@$_POST['update'] == "inventory") {
	$ID = (int) $_POST['ID'];        
	$quantity = (int) $_POST['quantity'];        
	$size = (int) $_POST['size'];        
	$stmt = $db->prepare("UPDATE inventory SET ItemCount=?, Size=?, Units=?, LastUpdated=now() WHERE ID=?")  OR fail($db->error);
	$stmt->bind_param('iisi', $quantity, $size, $_POST['units'], $ID)  OR fail($stmt->error);
	$stmt->execute()  OR fail($stmt->error);
	$stmt->affected_rows == 1  OR fail("No such records found for update.");
	$stmt->close();
	exit("{}");

} elseif(@$_POST['update'] == "individual") {
	$ID 	 = (int) $_POST['ID'];
	$index = $_POST['index'];
	$value = $_POST['value'];
	$CAS 	 = $_POST['CAS'];
	switch ($index) {
		case 0:
			// Room
			$query = "UPDATE inventory SET Room=? WHERE ID=?";
			$stmt = $db->prepare($query) OR fail($db->error);
			$stmt->bind_param('si', $value, $ID) OR fail($stmt->error);
			break;
		case 1:
			// Location
			$query = "UPDATE inventory SET Location=? WHERE ID=?";
			$stmt = $db->prepare($query) OR fail($db->error);
			$stmt->bind_param('si', $value, $ID) OR fail($stmt->error);
			break;
		case 2:
			// Amount
			break;
		case 3:
			// Chemical Name
			//Get Chemical ID
			$chemExists = false;
			$query = "SELECT ID FROM chemical WHERE Name=?";
			$stmt = $db->prepare($query) OR fail($db->error);
			$stmt->bind_param('s', $value) OR fail($stmt->error);
			$stmt->execute() OR fail($stmt->error);
			if($stmt->num_rows > 0) {
				$chemExists = true;
			}
			$stmt->bind_result($chemID);
			$stmt->fetch();
			$stmt->close();
			if(!$chemExists) {
				// If chemical does not yet exist
				// Insert Chemical into database
				$query = "INSERT INTO chemical (Name, CAS) VALUES (?,?)";
				$stmt = $db->prepare($query) OR fail($db->error);
				$stmt->bind_param('ss', $value, $CAS) OR fail($stmt->error);
				$stmt->execute() OR fail($stmt->error);
				// Then get chemical ID from newly inserted record
				$query = "SELECT ID FROM chemical WHERE Name=?";
				$stmt = $db->prepare($query) OR fail($db->error);
				$stmt->bind_param('s', $value) OR fail($stmt->error);
				$stmt->execute() OR fail($stmt->error);
				$stmt->bind_result($chemID);
				$stmt->fetch();
				$stmt->close();
			}
			//Now use chemicalID to update the record
			$query = "UPDATE inventory SET ChemicalID=? WHERE ID=?";
			$stmt = $db->prepare($query) OR fail($db->error);
			$stmt->bind_param('si', $chemID, $ID) OR fail($stmt->error);
			break;
		case 4:
			// Manufacturer
			//Get Mftr ID
			$mftrExists = false;
			$query = "SELECT ID FROM manufacturer WHERE Name=?";
			$stmt = $db->prepare($query) OR fail($db->error);
			$stmt->bind_param('s', $value) OR fail($stmt->error);
			$stmt->execute() OR fail($stmt->error);
			if($stmt->num_rows > 0) $mftrExists = true;
			$stmt->bind_result($mftrID);
			$stmt->fetch();
			$stmt->close();
			if(!$mftrExists) {
				// If manufacturer does not yet exist
				// Insert Mftr into database
				$query = "INSERT INTO manufacturer (Name) VALUES (?)";
				$stmt = $db->prepare($query) OR fail($db->error);
				$stmt->bind_param('s', $value) OR fail($stmt->error);
				$stmt->execute() OR fail($stmt->error);
				// Then get Mftr ID from newly inserted record
				$query = "SELECT ID FROM manufacturer WHERE Name=?";
				$stmt = $db->prepare($query) OR fail($db->error);
				$stmt->bind_param('s', $value) OR fail($stmt->error);
				$stmt->execute() OR fail($stmt->error);
				$stmt->bind_result($mftrID);
				$stmt->fetch();
				$stmt->close();
			}
			//Now use MftrID to update the record
			$query = "UPDATE inventory SET MftrID=? WHERE ID=?";
			$stmt = $db->prepare($query) OR fail($db->error);
			$stmt->bind_param('si', $mftrID, $ID) OR fail($stmt->error);
			break;
		default:
			fail("Unfortunately, an error has occurred. Please refresh the page and try again.");
	}
	$stmt->execute() OR fail($stmt->error);
	$stmt->close();
	exit("{}");

} elseif (@$_POST['transfer'] == "inventory") {
	$ID = (int) $_POST['ID'];        
	$quantity = (int) $_POST['quantity'];        
	$stmt = $db->prepare("SELECT ID, ChemicalID, Size, Units FROM inventory WHERE ID=?")  OR fail($db->error);
	$stmt->bind_param("i", $ID)  OR fail($stmt->error);
	$stmt->execute()  OR fail($stmt->error);
	$data = fetch_all($stmt);
	count($data) == 1  OR fail("No such records found for transfer.");
	$data = $data[0];
	$ID == $data['ID']  OR fail("Failed to find matching record.");
	$stmt->close();
	$stmt = $db->prepare("CALL Add_New_Inventory(?, ?, ?, ?, ?)")  OR fail($db->error);
	$stmt->bind_param('issis', $data['ChemicalID'], $_POST['room'], $_POST['location'], $quantity, $data['Units'])  OR fail($stmt->error);
	$stmt->execute()  OR fail($stmt->error);
	$stmt->affected_rows == 1  OR fail("Unable to transfer to new location."); 
	$stmt->close();
	exit(json_encode("{}"));
}

fail("Unrecognized value");
?>
