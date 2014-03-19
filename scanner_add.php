<?php
########################
# File: scanner_add.php
# Description: Upon submit of ADD form, adds new chemical and corresponding data to database
# Author: Stephen Quenzer
# Date Created: March 19, 2014
########################
session_start();
require('admin/AcidRainDBLogin.php');
include 'logger.php';
$cas 		= $_GET['cas'];
$chemical 	= $_GET['chemical'];
//Manufacturer is optional
if($_GET['manufacturer'] != "") $mftr = $_GET['manufacturer'];
$room		= $_GET['room'];
$loc 		= $_GET['location'];
$quant		= (int) $_GET['quant'];
$unitSize	= (int) $_GET['unitSize'];
$unit		= $_GET['unit'];

#Get chemical id ( need to create better query with join later )
#----- QUICKFIX: add manufacturer, chemical if not present ----#
#Authors: Stephen Quenzer, Isaac Tice, Josiah Driver
$query = $db->prepare("SELECT ID FROM manufacturer WHERE Name=?");
$query->bind_param('s', $_POST['manufacturer']);
$query->execute();
$query->store_result();
#If manufacturer not in database insert it
if($query->num_rows() < 1 ) {
	$query->close();
	$query = $db->prepare("INSERT INTO manufacturer (Name) VALUES (?)");
	$query->bind_param('s', $_POST['manufacturer']);
	if ( !$query->execute() )
		error_log($query->error);
	$query->close();
					
	#Now fetch manufacturer ID 
	$query = $db->prepare("SELECT ID FROM manufacturer WHERE Name=?");
	$query->bind_param('s', $_POST['manufacturer']);
	$query->execute();
}
$query->bind_result($manufacturerID);
$query->fetch();
$query->close();

#Now, if chemical not in database insert it
$query = $db->prepare("SELECT ID FROM chemical WHERE Name=?");
$query->bind_param('s', $_POST['chemical']);
$query->execute();
$query->store_result();
#If chemical not in database insert it
if ($query->num_rows() < 1 ) {
	$query->close();
	$query = $db->prepare("INSERT INTO chemical (Name, MfrID) VALUES (?, ?)");
	$query->bind_param('ss', $_POST['chemical'], $manufacturerID);
	if( !$query->execute() )
		error_log($query->error);
	$query->close();

	#Now fetch chemical ID 
	$query = $db->prepare("SELECT ID FROM chemical WHERE Name=?");
	$query->bind_param('s', $_POST['chemical']);
	$query->execute();
}
$query->bind_result($chemID);
$query->fetch();
$query->close();

#----------------------- END OF QUICK FIX --------------------#
			
#Insert record
$query = $db->prepare("INSERT INTO inventory (Room, Location, ItemCount, ChemicalID, Size, Units, LastUpdated) VALUES (?, ?, ?, ?, ?, ?, ?)");
$currentDate = date("Y-m-d H:i:s");
$_POST['quant'] = (int) $_POST['quant'];
$_POST['unitSize'] = (int) $_POST['unitSize'];
$query->bind_param('ssiiiss', $_POST['room'], $_POST['location'], $_POST['quant'], $chemID, $_POST['unitSize'], $_POST['unit'], $currentDate);
if (!$query->execute()) 
	error_log('problem inserting data: ' . $db->error);
else
	$message = "Your record has been added.";
?>