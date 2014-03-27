<?php 
	session_start();
	require('admin/AcidRainDBLogin.php');
	include 'logger.php';
	$cas 		= $_GET['cas'];
	$chemical 	= $_GET['chemical'];
	//Manufacturer is optional
	if($_GET['manufacturer'] != "") $mftr = $_GET['manufacturer'];
	//Set default mftrID value (corresponds to 'other')
	$mftrID = 1;
	$room		= $_GET['room'];
	$loc 		= $_GET['location'];
	$quant		= (int) $_GET['quant'];
	$unitSize	= (int) $_GET['unitSize'];
	$unit		= $_GET['unit'];
	
	#Validate NEEDS TO BE JAVASCRIPT
	$errors = array();		
	#Validate 'Room': Only letters and numbers aloud, no spaces or symbols
	if(!preg_match('/^[a-zA-Z0-9]+$/', $room)) {
		$errors[] = "Room must be alphanumeric with no spaces or symbols";
	}
	#Validate 'quantity': integers only
	if(!preg_match('/^[0-9]+$/', $quant)) {
		$errors[] = "The item quantity must be an integer number";
	}
	#Validate 'size': float-decimal and sign optional
	if(!preg_match('/^-?([0-9])+([\.|,]([0-9])*)?$/', $unitSize)) {
		$errors[] = "The Unit Size must be an decimal number";
	}
	
	//Update DB
	#First get MftrID if it exists
	if(isset($mftr)) {
	$query = $db->prepare("SELECT ID FROM manufacturer WHERE Name=?");
	$query->bind_param('s', $mftr);
	$query->execute();
	$query->store_result();
	$query->bind_result($mftrID);
	$query->fetch();
	# $mftrID now holds the manufacturer ID
	$query->close();
	}
	#Now, update inventory and manufacturer
	$query = "UPDATE inventory i JOIN chemical c
				ON i.ChemicalID = c.ID
				SET i.Room=?, i.Location=?, i.ItemCount=?, i.Size=?, i.Units=?, i.MftrID=?
				WHERE i.Room=? && i.Location=? && i.ItemCount=? && i.Size=? && i.Units=? && c.Name=?
			 ";
	$stmt = $db->stmt_init();
	if(!$stmt->prepare($query)) {
		header ("Location: scanner.php?message=error");
	} else {
		$_SESSION['quant'] = (int) $_SESSION['quant'];
		$_SESSION['size'] = (int) $_SESSION['size'];
		$stmt->bind_param('ssiisissiiss', $room, $loc, $quant, $unitSize, $unit, $mftrID, $_SESSION['room'], $_SESSION['loc'], $_SESSION['quant'], $_SESSION['size'], $_SESSION['unit'], $_SESSION['chem']);
		$stmt->execute();
		header ("Location: scanner.php?message=Chemical updated successfully!");
	}
?>