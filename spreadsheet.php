<?php 
session_start();

// Code for Simple Authentication System
$errorMessage = "";
require('admin/AcidRainDBLogin.php');
if (isset($_POST["Logout"])) {
	$_SESSION["UserID"] = 0;
	$_SESSION["AccessLevel"] = 0;
} elseif (isset($_POST["Login"])) {
	$query = $db->prepare("SELECT ID, AccessLevel FROM authorization WHERE Username=? AND Password=?");
	$query->bind_param('ss', $_POST['username'], $_POST['password']);
	$query->execute();
	$query->store_result();
	if($query->num_rows() == 1 ) {
		$query->bind_result($_SESSION["UserID"], $_SESSION['AccessLevel']);
		$query->fetch();
	} else {
		$errorMessage = "Invalid username or password.";
		$_SESSION["UserID"] = 0;
		$_SESSION["AccessLevel"] = 0;
	}
	$query->close();
}

$AccessLevel = @$_SESSION["AccessLevel"];

// Currently only two levels are used:
//   0 - Not logged in - deny access to everything except help
//   not zero - Logged in - allow queries and changes

// In theory more levels can be used for privileged operations:
//   1 - Only query access to view chemical data
//   2 - Add/Move/Delete chemicals
//   3 - Add Manufacturers and assign safety sheet info.
//   4 - Add new users and change user access level codes. etc.

?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Chemical Database | Eastern Mennonite University</title>
	<meta name="description" content="Chemical Database">
	<meta name="author" content="Stephen Quenzer">
	<link rel="stylesheet" href="css/main.css">
	<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script> -->
	<script src="js/jquery.1.10.2.min.js"></script>
	<script src="js/jquery.tablesorter.min.js"></script>
        <?php if ($AccessLevel > 0) { ?>
	<script src="js/core.js"></script>
        <?php } ?>
	<!--[if lt IE 9]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body>
	<header class="center">
		<img id="banner" src="gfx/spreadsheet_header.png">
		<nav id="navMenu">
			<form class="inputField" action="" method="POST">
			<?php if ($AccessLevel > 0) {  // logged in ?>
				<ul><li><input type="button" name="addChem" id="addChem" value="Add Chemicals (Scanner)"/></li>
				<li><input name="Logout" value="Logout" type="submit" /></li></ul>
			<?php } else {  // not logged in ?>
				<input size="12" type="text" id="username" name="username" placeholder="Username" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Username'" 
					<?php if (isset($_POST['username'])) { echo  "value='" . $_POST['username'] . "'"; } ?> />
				<input size="12" type="password" id="password" name="password" placeholder="Password" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Password'" />
				<input type="submit" name="Login" value="Login" />
			<?php } ?>
			</form>
		</nav>
	</header>
	<main <?php if(!isset($AccessLevel) || $AccessLevel===0) echo "class='splash'"; ?>>
	<?php if ($AccessLevel > 0) { // logged in ?>
		<div id="table_wrapper">
			<p id="errorMessage" class="center"><span><?php echo $errorMessage; ?></span></p>
			<div id="tableOps" class="center">
							<form>
									<input type="button" class="invisible basicButton bgNormal" name="normal" id="normal" value="Back to Normal Mode">
									<input type="button" class="visible basicButton bgNormal" name="barcode" id="barcode" value="Get Barcodes">
									<input type="button" class="visible basicButton bgEdit" name="edit" id="edit" value="Edit Records">
									<input type="button" class="visible basicButton bgDelete" name="delete" id="delete" value="Delete Records">
							</form>
			</div>
			<table id="chemical_spreadsheet" class="tablesorter">
				<thead>
					<tr>
						<td scope="col"><input type="search" name="roomsearch" title="Room Filter" size="6" list="roomList" onchange="filterThem()"/></td>
						<td scope="col"><input type="search" name="locationsearch" title="Location Filter" list="locationList" onchange="filterThem()"/></td>
						<td scope="col"><input type="button" name="clearsearch" id="clearsearch" class="basicButton" value="‹ Clear Filters ›"></td>
						<td scope="col"><input type="search" name="namesearch" title="Chemical Name Filter" list="chemList" onchange="filterThem()"/></td>
						<td scope="col"><input type="search" name="mfrsearch" title="Manufacturer Filter" list="mfrList" onchange="filterThem()"/></td>
						<td scope="col"><input type="search" name="cassearch" title="CAS number filter" list="casList" onchange="filterThem()"/></td>
					</tr>
					<tr>
						<th scope="col" id="th_left">Room </th>
						<th scope="col">Location </th>
						<th scope="col">Amount </th>
						<th scope="col">Name </th>
						<th scope="col">Manufacturer </th>
						<th scope="col" id="th_right">CAS </th>
					</tr>
				</thead>
				<tbody id="chemical_spreadsheet_body">
					<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
				</tbody>
			</table>
			<div id="chemMsg">
				<div id="chemHiddenRowsMsg"></div>
			</div>
		</div><!--End table_wrapper-->
		<div id="popup"><div>
			<p id="popupName"></p>
			<p id="popupPlace"></p>
			<form action="" onsubmit="return false" class="inputField">
				<input type="hidden" name="popupID">
				<input type="hidden" name="popupOrigQuantity">
				<input type="hidden" name="popupOrigSize">
				<input type="hidden" name="popupOrigUnits">
				<p>
				<label>Quantity: <span><input name="popupQuantity"></span></label>
				<label title="Examples: 200 ml, 60 g, etc.">Unit Size: <span><input size="7" name="popupSize" title="Numerical value"><input size="6" name="popupUnits" title="Units (ml, g, etc.)"></span></label>
				<input type="button" value="Update Amount" name="updateAmount" id="updateAmount">
				</p>
				<p>
				<label>Quantity: <span><input name="popupMoveQuantity"></span></label>
				<label>New Room: <span><input type="text" name="popupRoom" list="roomList" title="Type space to show room list."></span>
		<!-- The room list is being hand-filled here, but should be pulled from the database!
			There isn't a list of rooms with descriptions at this point though. -->
		<datalist id="roomList"> 
			<select>
			<option value="35a"> 35a - Stockroom Front</option>
			<option value="35b"> 35b - Stockroom Back</option>
			<option value="27"> 27 - Tara's Office</option>
			<option value="10"> 10 - Main Office</option>
			</select>
		</datalist></label>
				<label>Location: <span><input name="popupLocation" list="locationList" title="Type space to show standard locations."></span>
		<datalist id="locationList">
			<select>
			<!--Is empty option necessary? <option value="" label=""> -->
			<?php if ($result = $db->query("SELECT DISTINCT Location FROM inventory")) {
				while ($row = $result->fetch_row())
					echo '<option value="' . $row[0] . '" label=" ' . $row[0] . '">';
				$result->close();
			} ?>
			</select>
		</datalist></label>
				<input type="button" value="Move to Location" name="transfer" id="transfer">
				</p>
			</form>
		<datalist id="chemList">
			<?php if ($result = $db->query("SELECT DISTINCT Name FROM chemical")) {
				while ($row = $result->fetch_row())
					echo '<option value="' . $row[0] . '" label=" ' . $row[0] . '">';
				$result->close();
			} ?>
		</datalist>
		<datalist id="mfrList">
			<?php if ($result = $db->query("SELECT DISTINCT Name FROM manufacturer")) {
				while ($row = $result->fetch_row())
					echo '<option value="' . $row[0] . '" label=" ' . $row[0] . '">';
				$result->close();
			} ?>
		</datalist>
		<datalist id="casList">
			<?php if ($result = $db->query("SELECT DISTINCT CAS FROM chemical")) {
				while ($row = $result->fetch_row())
					echo '<option value="' .$row[0] . '" label=" ' . $row[0] . '">';
				$result->close();
			} ?>
		</datalist>
		</div></div><!--End Popup-->

	<?php } else { // not logged in ?>
		<div id="content">
			<h2>General Info</h2>
			<p>Welcome. AcidRain is a database application project that allows the EMU Science Departments 
				the ability to keep track of their chemicals in an orderly, systematic way. 
				Rather than working with hard copies of thousands of records, AcidRain gives you easy, 
				efficient processing of chemical records. It is designed in a simple way to allow
				you to view, add, delete, or modify records.</p>
			<h2>Who is it for?</h2>
			<p>In order to preserve the integrity of the data and keep it secure, AcidRain can only be used 
				by EMU faculty and students that have been given the correct authorization
				and credentials. If you have these, then go ahead and log in to view what we call the 
				"spreadsheet" (the table of records) if you haven't already.</p>
			<h2>Fire Department</h2>
			<p>In case of emergency, fire-code regulation requires that necessary chemical information be 
				provided to members of the Harrisonburg Fire Department to equip them with
				knowledge about the presence, location, and amount of any and all chemicals in the 
				building to ensure their safety and effectiveness before they enter the building.
				AcidRain is designed to help with this in case the need arises.</p>
			<h2>AcidRain Team</h2>
			<p>AcidRain is a project created in the Fall of 2013 by EMU students Stephen Quenzer, Isaac 
				Tice, and Josiah Driver, for their semester project in Software Engineering
				with Charles Cooley. For additional help and information, visit the <a href="./help">documentation page</a>.</p>
		</div>
	<?php } ?>

	</main>
	<footer id="footerContent">
					<a href="http://www.emu.edu"><img src="gfx/emu_dark.png" width="200px" height="80px"></a>
	</footer>
</body>
</html>

