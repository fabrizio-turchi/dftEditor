<?php
	
	define("DB_HOST", "localhost");
	define("DB_NAME", "dftCatalogue");
	define("DB_USER", "dft_uuCatalogue");
	define("DB_PASS", "dft.88.ABx");

	
	try {
	    $db_conn = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
	    $db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} 
	catch (PDOException $e) {
	   	echo "Could not connect to database";
	}
	
	if (count($argv) < 3) {
	  	echo "Error! Use: " . $argv[0] . " oldIdTool newIdTool\n\n";
	  	exit;
	}

	$idOld 	= $argv[1];		
	$idNew	= $argv[2];		
	
	try {
		$qryDelete	= "DELETE FROM tblTools WHERE IdTool=" . $idOld;
		echo "$qryDelete \n";
		$nRecs 	= $db_conn->exec($qryDelete);
		$qryUpdate	= "UPDATE tblToolsCategories SET IdTool=" . $idNew . " WHERE IdTool=" . $idOld;
		echo "$qryUpdate \n";
		$nRecs 	= $db_conn->exec($qryUpdate);
		$qryUpdate	= "UPDATE tblToolsFeatures SET IdTool=" . $idNew . " WHERE IdTool=" . $idOld;
		echo "$qryUpdate \n";
		$nRecs 	= $db_conn->exec($qryUpdate);
	}
	catch (PDOException $e) {
		echo "ERROR in Insert FeaturesValues";
	   	echo $e->getMessage();
	   	exit;
	}
	echo "Update completed!\n\n";
?>
