<?php
require('admin/AcidRainDBLogin.php');
session_start();
date_default_timezone_set("America/New_York");
###Form Levels###
#Level 1: Spash Screen 	-- CAS Input
#Level 2: "Update" Form	-- Update current chemical information
#Level 3: "Add" Form 	-- Add new chemical to DB
$_SESSION['formLevel'] = 1;
if(isset($_GET['submit'])) {
	#if not checked: update, else: new
	if(!isset($_GET['chemAction'])) {
		$_SESSION['formLevel'] = 2;
	} else {
		$_SESSION['formLevel'] = 3;
	}
}
/*
if (@$_SESSION["formLevel"] <= 0) {
	header( "Location: ." );
}
*/
$message = "";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
	<!--Slider Scripts-->
	<script>window.dhx_globalImgPath = "slider/imgs/";</script>
	<script  src="slider/dhtmlxcommon.js"></script>
	<script  src="slider/dhtmlxslider.js"></script>
	<script  src="slider/ext/dhtmlxslider_start.js"></script>
	<link rel="stylesheet" type="text/css" href="slider/dhtmlxslider.css">
	
  <title>Chemical Database Scanner</title>
  <meta name="description" content="Chemical Database Scanner">
  <meta name="author" content="Chemical Database">
  <link rel="stylesheet" href="css/scanner.css">
  <script src="js/scanner.js"></script>
</head>
<body>
	<header>
		<a href="scanner.php"><img src="gfx/scanner_header.png"></a>
	</header>
	<section id="main">
		<div id="form_wrapper">
		<?php
		if($_SESSION['formLevel'] == 1) {
		?>
			<form class="addInv" id="splash" onsubmit="return verifyCAS();" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="GET">
				<span class="message"><?php if(isset($_GET['message'])) echo $_GET['message']; ?></span>
				<p>
					<label>Chemical Abstract Registry Number</label>
					<span id="error" style="display: block; color: red; padding: 6px; text-align: center;"></span>
					<input type="text" name="cas" id="cas" placeholder="Example CAS: 9000-01-5" required autofocus>
				</p>
				<p id="bottom_wrapper">
				    <label class="switch">
					  <input type="checkbox" name="chemAction" class="switch-input">
					  <span class="switch-label" data-on="Add New" data-off="Update"></span>
					  <span class="switch-handle"></span>
					</label>
					<input type="submit" name="submit" class="submitButton" value="GO">
					<span class="clear"></span>
				</p>
			</form>
		<?php
		} elseif($_SESSION['formLevel'] == 2) {
		?>
			<p class="center"><?php echo $message; ?></p>
		    <form class="addInv" id="add" action="scanner_update.php" method="GET">
			    <p>
				    <label class="center">Chemical Abstract Registry Number</label>
				    <input type="text" name="cas" id="cas" value="<?php echo $_GET['cas']; ?>" placeholder="Example CAS: 9000-01-5" readonly>
			    </p>
			    <p>
					<span id="chems">
						<select name="chems" multiple>
							
						</select>
					</span>
					<div id="lowerFieldsWrapper" class="center" style="display:none;">
						<label id="chemicalsLbl">Chemical Name
						<span><input type="text" list="chemicals" id="chemical" name="chemical" placeholder="Acetone" required autofocus/></span>
						</label>
						<label id="manufacturerLbl">Manufacturer
						<span><input type="text" list="manufacturers" name="manufacturer" id="manufacturer" tabinex="1" placeholder="Sigma" onblur="verifyNewData('Name', 'manufacturer', this.value, 'mftr')" /></span>
						</label>
						<label id="roomLbl">Room
						<span><input type="text" list="rooms" name="room" id="room" tabinex="2" placeholder="35b" onblur="verifyNewData('Room', 'inventory', this.value)" required /></span>
						</label>
						<span id="roomsWrapper">
							<?php
								if ($result = $db->query("SELECT DISTINCT Room FROM inventory")) {
									while ($row = $result->fetch_row()) {
										echo "<input type='button' value='$row[0]' class='basicButton' onclick='createLocations(this.value)'>";
									}
									$result->close();
								}
							?>
						</span>
						<label id="locationLbl">Location
						<span><input type="text" list="location" name="location" id="loc" tabinex="3" placeholder="Storeroom Front" onblur="verifyNewData('Location', 'inventory', this.value)"  required /></span>
						</label>
						<span id="locWrapper">
						</span>
						<label id="quantLbl">Quantity
						<span><input type="text" type="number" name="quant" id="quant" tabinex="4" placeholder="4" value="0" required /></span>
						</label>
						<div id="updateSlider"></div>
						<label id="unitTypeLbl">Unit Type
						<span id="unitSize">
						<input type="text" id="unit" name="unit" tabinex="6" placeholder="mg" required ></span>
						</label>
						<input type="submit" name="submit" class="submitButton" value="Update Chemical">
					</div><!--lowerFieldsWrapper-->
			    </p>
		    
			    <!--these will be filled by javascript when the page loads-->
			    <datalist id="manufacturers">
				    <?php
				    if ($result = $db->query("SELECT DISTINCT Name FROM manufacturer")) {
					    while ($row = $result->fetch_row()) {
						    echo '<option value="' . $row[0] . '" label=" ' . $row[0] . '">';
					    }
					    $result->close();
				    }
				    ?>
			    </datalist>
		    </form>
		<script>
			getData(<?php echo ("'" . $_GET['cas'] . "'") ?>);
		</script>
		<?php
		} else { 
		### Else formLevel == 3 ###
		?>
		    <p class="center"><?php echo $message; ?></p>
		    <form class="addInv" id="add" action="scanner_add.php" method="GET">
			    <?php
			    if (!empty($errors)) {
				    echo "<span class='errMsg'><h3 style='margin-left: 20px;'>Errors:</h3><ul>";
				    foreach ($errors as $e) {
					    echo "<li>$e</li>";
				    }
				    echo "</ul></span>";
			    }
			    ?>
			    <p>
				    <label class="center">Chemical Abstract Registry Number</label>
				    <input type="text" name="cas" id="cas" value="<?php echo $_GET['cas']; ?>" placeholder="Example CAS: 9000-01-5" readonly>
			    </p>
			    <img id="barcodeLabel" src="barcode.php?codetype=code128&height=40&cas=<?php echo $_GET['cas']; ?>" style="display: block; margin: auto;" alt="<?php echo $_GET['cas']; ?>">
			    <p>
				    <label id="chemicalsLbl">Chemical Name
				    <span><input list="chemicals" name="chemical" id="chemical" placeholder="Acetone" onblur="verifyNewData('Name', 'chemical', this.value, 'chem')" required autofocus/></span>
				    </label>
					<span id="mftrLoader"></span>
				    <label id="manufacturerLbl">Manufacturer
				    <span><input list="manufacturers" name="manufacturer" id="manufacturer" tabinex="1" placeholder="Sigma" onblur="verifyNewData('Name', 'manufacturer', this.value, 'mftr')" /></span>
				    </label>
				    <label id="roomLbl">Room
				    <span><input list="rooms" name="room" id="room" tabinex="2" placeholder="35b" onblur="verifyNewData('Room', 'inventory', this.value)" required /></span>
				    </label>
				    <span id="roomsWrapper">
					    <?php
						    if ($result = $db->query("SELECT DISTINCT Room FROM inventory")) {
							    while ($row = $result->fetch_row()) {
								    echo "<input type='button' value='$row[0]' class='basicButton' onclick='createLocations(this.value)'>";
							    }
							    $result->close();
						    }
					    ?>
				    </span>
				    <label id="locationLbl">Location
				    <span><input list="location" name="location" id="loc" tabinex="3" placeholder="Storeroom Front" onblur="verifyNewData('Location', 'inventory', this.value)"  required /></span>
				    </label>
				    <span id="locWrapper">
				    </span>
				    <label id="quantLbl">Quantity
				    <span><input type="number" name="quant" id="quant" tabinex="4" placeholder="4" value="0" required /></span>
				    </label>
				    <script>
							var slider = new dhtmlxSlider(null, 270);
							slider.linkTo('quant');
							slider.init();
							slider.setSkin("dhx_skyblue");
							slider.setStep(1);
							slider.setMin(0);
							slider.setMax(1000);
							slider.setValue(100);
						</script>
				    <label id="unitTypeLbl">Unit Type
						<span id="unitSize">
						<input type="text" id="unit" name="unit" tabinex="6" placeholder="mg" required ></span>
						</label>
				    <input type="submit" name="submit" class="submitButton" value="Add Chemical">
			    </p>
		    
			    <!--these will be filled by javascript when the page loads-->
			    <datalist id="manufacturers">
				    <?php
				    if ($result = $db->query("SELECT DISTINCT Name FROM manufacturer")) {
					    while ($row = $result->fetch_row()) {
						    echo '<option value="' . $row[0] . '" label=" ' . $row[0] . '">';
					    }
					    $result->close();
				    }
				    ?>
			    </datalist>
		    </form>
			<script>
			autoFillMftr();
			</script>
		<?php
		}
		?>
		</div><!--Form_Wrapper-->
		<span class="center"><a href="spreadsheet.php" class="basicLink">Go to chemical database spreadsheet</a></span>
	</section><!--main-->
	<div id="barcode"></div>
	<footer>
	<span id="copy">&copy; EMU 2014</span>
	</footer>	
	<div id="popupBG" class="" onclick="">
	</div><!--popupBG-->
	<span class="center">
		<div id="popup" class="">
			
		</div><!--popup-->
	</span><!--center-->
	<script>
		
	</script>
</body>
</html>
<?php $db->close(); ?>
