<?php
########################
# File: scanner_add.php
# Description: Upon submit of ADD form, adds new chemical and corresponding data to database
# Author: Stephen Quenzer
# Date Created: March 19, 2014
#
########################
########################
# Variable list in file (not including $GET's or query variables)
# NAME:			DESCRIPTION:					 TYPE:				
# $db:			Database Handle 				{msqli object}
# $cas: 		Chemical CAS numbers 			{string}
# $chemical: 	Chemical name 					{string}
# $mftr: 		Manufacturer name [optional]	{string}
# $room: 		Room name 						{string}
# $loc:			Location name					{string}
# $quant:		Quantity of chemical			{int}
# $unitSize:	Unit size of chemical			{int}
# $unit:		Unit type (e.g. ml, g, etc)		{string}
# $mftrID: 		Manufacturer ID (may be null)	{string | null}
# $chemID:		Chemical ID						{string}
# $currentDate:	Current date					{string in 'date()' format}
########################
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
#Only add and get manufacturerID if chemical has a manufacturer!
if(isset($mftr)) {
	$query = $db->prepare("SELECT ID FROM manufacturer WHERE Name=?");
	$query->bind_param('s', $mftr);
	$query->execute();
	$query->store_result();
	/*
	#If manufacturer not in database insert it
	#Do we need this? Mftr check in scanner.js should do it for us
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
	*/
	$query->bind_result($mftrID);
	$query->fetch();
	# $mftrID now holds the manufacturer ID
	$query->close();
}
#if chemical has no mftr, set the $mftrID to null
if(!isset($mftrID)) $mftrID = null;
#Now, if chemical not in database insert it
$query = $db->prepare("SELECT ID FROM chemical WHERE Name=?");
$query->bind_param('s', $chemical);
$query->execute();
$query->store_result();
#If chemical not in database insert it
if ($query->num_rows() < 1 ) {
	$query->close();
	tlog("manufacturerID, int or string: $mftrID");
	$query = $db->prepare("INSERT INTO chemical (CAS, Name, MfrID) VALUES (?, ?, ?)");
	$query->bind_param('sss', $cas, $chemical, $mftrID);
	if( !$query->execute() )
		error_log($query->error);
	$query->close();

	#Now fetch chemical ID 
	$query = $db->prepare("SELECT ID FROM chemical WHERE Name=?");
	$query->bind_param('s', $chemical);
	$query->execute();
}
$query->bind_result($chemID);
$query->fetch();
$query->close();

#----------------------- END OF QUICK FIX --------------------#
			
#Everything ready, now insert into database
$query = $db->prepare("INSERT INTO inventory (Room, Location, ItemCount, ChemicalID, Size, Units, LastUpdated) VALUES (?, ?, ?, ?, ?, ?, ?)");
$currentDate = date("Y-m-d H:i:s");
$query->bind_param('ssiiiss', $room, $loc, $quant, $chemID, $unitSize, $unit, $currentDate);
if (!$query->execute()) 
	error_log('problem inserting data: ' . $db->error);
else
	$message = "Your record has been added.";
?>