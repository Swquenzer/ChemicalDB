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

  <title>Chemical Database Scanner</title>
  <meta name="description" content="Chemical Database Scanner">
  <meta name="author" content="Chemical Database">
  <link rel="stylesheet" href="css/scanner.css">
  <script>
		//Verifies that CAS number is of correct form
		function verifyCAS() {
			var cas = new RegExp("^[0-9]{2,6}-[0-9]{2}-[0-9]$"); //CAS regular expression
			var input = document.getElementById('cas').value;
			console.log("Input.value: " + input + ", regex: " + cas);
			console.log(cas.test(input));
			if(cas.test(input)) { 
				//If CAS code is of correct format, do nothing
				return true;
			} else {
				//If CAS code of incorrect format, return error message
				var errorTag = document.getElementById("error");
				errorTag.innerHTML = "The input '" + input + "' is an invalid CAS number. Please re-scan.";
				return false;
			}
			return true;
		}
		//A general purpose ajax request callback function
		var request;
		function ajaxRequest(url, callback) {
			if (window.XMLHttpRequest) {
				//Modern Browsers
				request = new XMLHttpRequest();
			} else {
				//IE5 & 6
				request = new ActiveXObject("Microsoft.XMLHTTP");
			}
			request.onreadystatechange=callback;
			request.open("GET",url,true);
			request.send();
		}
		//Verifies that the user wants to add new data to the DB
		//field & table parameters refer to database, value is the input
		function verifyNewData(field, table, value) {
			//var field = document.getElementById(field);
			ajaxRequest("verify_new_data.php?field="+field+"&table="+table+"&value="+value, function() {
				if(request.readyState == 4 && request.status == 200) {
					locWrapper.innerHTML=request.responseText;
				}
			});
		}
		
		function incQuantity(amount) {
			amount.substr(1); //Remove the '+' from the number
			var quant = document.getElementById('quant').value;
			//If no value in input, make value=0
			if(isNaN(parseInt(quant))) {
				document.getElementById('quant').value = "0";
			}
			quant = document.getElementById('quant').value;
			var temp = parseInt(quant) + parseInt(amount);
			//Why can't 'quant' be used here? 
			document.getElementById('quant').value = temp.toString(); 
        }
		function getLocations(room) {
			var locWrapper = document.getElementById('locWrapper');
			//Pre-load indicator
			locWrapper.innerHTML="<img src='gfx/loader.gif'>";
			//If process is processed successfully
			ajaxRequest("get_loc.php?room="+room, function() {
				if(request.readyState == 4 && request.status == 200) {
					locWrapper.innerHTML=request.responseText;
				}
			});
		}
        function createLocations(room) {
			//Add selected room to text input
			document.getElementById('room').value = room;
            getLocations(room);
            var roomsWrapper = document.getElementById('roomsWrapper');
			roomsWrapper.style.display="none";
			var locationsWrapper = document.getElementById('locWrapper');
        }
		function addLocation(loc) {
			document.getElementById('loc').value=loc;
			locWrapper.style.display="none";
		}
	</script>
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
				<p>
					<label>Chemical Abstract Registry Number</label>
					<span id="error" style="display: block; color: red; padding: 6px; text-align: center;"></span>
					<input type="text" name="cas" id="cas" placeholder="Example CAS: 9000-01-5" required autofocus>
				</p><p id="bottom_wrapper">
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
			<form class="addInv" id="update" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="GET">
				<label>UPDATE CHEMICAL</label>
			</form>
		<?php
		} else { 
		### Else formLevel == 3 ###
		?>
			<p style="text-align: center;"><?php echo $message; ?></p>
			<form class="addInv" id="add" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="GET">
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
					<input type="text" name="cas" id="cas" value="<?php echo $_GET['cas']; ?>" placeholder="Example CAS: 9000-01-5" required>
				</p>
				<img src="barcode.php?codetype=code25&height=40&cas=<?php echo $_GET['cas']; ?>" style="display: block; margin: auto;" alt="<?php echo $_GET['cas']; ?>">
				<p>
					<label id="chemicalsLbl">Chemical Name
					<span><input list="chemicals" name="chemical" placeholder="Acetone" required autofocus/></span>
					</label>
					<label id="manufacturerLbl">Manufacturer
					<span><input list="manufacturers" name="manufacturer" tabinex="1" placeholder="Sigma" required /></span>
					</label>
					<label id="roomLbl">Room
					<span><input list="rooms" name="room" id="room" tabinex="2" placeholder="35b" required /></span>
					</label>
					<span id="roomsWrapper">
						<?php
							if ($result = $db->query("SELECT DISTINCT Room FROM inventory")) {
								while ($row = $result->fetch_row()) {
									echo "<input type='button' value='$row[0]' class='roomBut' onclick='createLocations(this.value)'>";
								}
								$result->close();
							}
						?>
					</span>
					<label id="locationLbl">Location
					<span><input list="location" name="location" id="loc" tabinex="3" placeholder="Storeroom Front" onblur="verifyNewData('location', 'inventory', this.value)"  required /></span>
					</label>
					<span id="locWrapper">
					</span>
					<label id="quantLbl">Quantity
					<span><input type="number" name="quant" id="quant" tabinex="4" placeholder="4" value="0" required /></span>
					</label>
					<input type="button" class="incQuant" value="+1" onclick="incQuantity(this.value)">
					<input type="button" class="incQuant" value="+5" onclick="incQuantity(this.value)">
					<input type="button" class="incQuant" value="+10" onclick="incQuantity(this.value)">
					<input type="button" class="incQuant" value="+50" onclick="incQuantity(this.value)">
					<input type="button" class="incQuant" value="Clear" onclick="document.getElementById('quant').value='0'">
					<label id="unitSizeLbl">Unit Size
					<span id="unitSize"><input type="number" name="unitSize" tabinex="5" placeholder="200" required />
					<input type="text" name="unit" tabinex="6" placeholder="mg" required ></span>
					</label>
					<!-- <label id="unitLbl">Unit of Measure
					<span></span>
					</label> -->
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
		<?php
		}
		?>
		</div><!--Form_Wrapper-->
	</section><!--main-->
	<div id="barcode"></div>
	<footer>
	<span id="copy">&copy; EMU 2014</span>
	</footer>
</body>
</html>
<?php $db->close(); ?>
