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
	  	echo "Error! Use: " . $argv[0] . " idTool_to_be_deleted\n\n";
	  	exit;
	}

	$id 	= $argv[1];				
	
	try {
		$qryDelete	= "DELETE FROM tblTools WHERE IdTool=" . $id;
		echo "$qryDelete \n";
		$nRecs 	= $db_conn->exec($qryDelete);
		$qryDelete	= "DELETE FROM tblToolsCategories WHERE IdTool=" . $id;
		echo "$qryDelete \n";
		$nRecs 	= $db_conn->exec($qryDelete);
		$qryDelete	= "DELETE FROM tblToolsFeatures WHERE IdTool=" . $id;
		echo "$qryDelete \n";
		$nRecs 	= $db_conn->exec($qryDelete);
	}
	catch (PDOException $e) {
		echo "ERROR in Insert FeaturesValues";
	   	echo $e->getMessage();
	   	exit;
	}
	echo "Delete completed!\n\n";
?>
