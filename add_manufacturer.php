<?php
	require('admin/AcidRainDBLogin.php');
	//Add new manufacturer to database
	$query = $db->prepare("SELECT ID FROM manufacturer WHERE Name=?");
			$query->bind_param('s', $_GET['mftr']);
			$query->execute();
			$query->store_result();
			#If manufacturer not in database insert it
			#Do we need this? (3/19/14)
			if($query->num_rows() < 1 ) {
				$query->close();
				$query = $db->prepare("INSERT INTO manufacturer (Name) VALUES (?)");
				$query->bind_param('s', $_GET['mftr']);
				if ( !$query->execute() )
					error_log($query->error);
				$query->close();
								
				#Now fetch manufacturer ID 
				$query = $db->prepare("SELECT ID FROM manufacturer WHERE Name=?");
				$query->bind_param('s', $_POST['mftr']);
				$query->execute();
			}
			$query->bind_result($mftrID);
			$query->fetch();
			echo $mftrID;
			$query->close();
?>