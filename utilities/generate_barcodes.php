<?php
	############################################
	# Author: Stephen Quenzer
	# Date Created: April 10, 2014
	# Description: 	Script that automatically generates barcodes
	# 							based on all CAS numbers in database
	############################################
	
	require('../admin/AcidRainDBLogin.php');
	require('barcode_to_file.php');
	include('../logger.php');
	
	//Constants
	$DESTINATION = "../barcodes";
	$HEIGHT = 40;
	$CODE_TYPE = "code128";
	$ORIENTATION = "horizontal";
	
	//Search for all distinct CAS numbers (no need generating duplicate barcodes)
	$query = "SELECT DISTINCT CAS FROM chemical";
	if ($result = $db->query($query)) {
		while($row = $result->fetch_row()) {
			//Create barcode for each CAS number
			generateBarcode($row[0], $DESTINATION, $HEIGHT, $CODE_TYPE, $ORIENTATION);
		}
		$result->close();
	}
	$db->close();
?>