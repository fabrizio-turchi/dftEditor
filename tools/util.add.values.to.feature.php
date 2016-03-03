<?php
	
	define("DB_HOST", "localhost");
	define("DB_NAME", "dftCatalogue2016");
	define("DB_USER", "dft_uuCatalogue");
	define("DB_PASS", "dft.88.ABx");

	
	try {
	    $db_conn = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
	    $db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} 
	catch (PDOException $e) {
	   	echo "Could not connect to database";
	}
	
	if (count($argv) < 2) {
	  	echo "Error! Use: " . $argv[0] . " idFeature Values (separeted by comma)\n\n";
	  	exit;
	}

	$arrayValues = explode(",", $argv[2]);			// Values are separated by comma
	
	foreach ($arrayValues as $value) {
		try { 
			$qryInsert  = "INSERT INTO tblFeaturesValues (IdFeature, Value) VALUES (";
	    	$qryInsert .= ":IdFeature, :Value) ";
			
			$stmt = $db_conn->prepare($qryInsert);
			$stmt->bindParam(':IdFeature', $argv[1], PDO::PARAM_INT);
			$stmt->bindParam(':Value', $value, PDO::PARAM_STR);
			$stmt->execute();
		}
		catch (Exception $e) {
			echo "ERROR in Insert FeaturesValues";
		   	echo $e->getMessage();
		   	exit;
		}
	}
	echo "Insert accomplished!\n\n";
?>
