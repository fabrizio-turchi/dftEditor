<?php
/*
*--- it imports Category, Features and Values into dftCatalogue database from a text file, whose format is the folllowing:
*
*	Process=AC
*	CodeCategory=05.
*	Category=Internet/Cloud
*	FeatureValues=Cloud Storage@Box,Dropbox,Google Drive,iCloud Drive,OneDrive
*	FeatureValues=Social Network@Facebook,Instagram,Linkedin,Twitter,YouTube
*
*	Each text file contains data related to a single Category but it may contains multiple Features with the corresponding Value. 
*	The tag FeatureValues= contains Feature and its Values separated by @; each single Value is separated from the other by comma.
*/
	define("DB_HOST", "localhost");
	define("DB_NAME", "dftCatalogue");
	define("DB_USER", "dft_uuCatalogue");
	define("DB_PASS", "dft.88.ABx");

	define("FILE_IMPORT", "process.category.features.values.txt");

	try {
	    $db_conn = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
	    $db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} 
	catch (PDOException $e) {
	   	echo "Could not connect to database";
	}
	

	try {
		$fImport = fopen(FILE_IMPORT, "r");
	}
	catch (Exception $e) {
	   	echo "Could not open the import file";
	   	echo $e->getMessage();
	   	exit;
	}	

	$numberFeature = 0;

	while(!feof($fImport)){
    	$line = fgets($fImport);
    	$pos = strpos($line, "=");
    	
    	$before = substr($line, 0, $pos);
    	$before = str_replace("\t", '', $before);
    	$before = str_replace("\r", '', $before);
    	$before = str_replace("\n", '', $before);
    	
    	$after  = substr($line, $pos + 1);
    	$after = str_replace("\t", '', $after);
    	$after = str_replace("\r", '', $after);
    	$after = str_replace("\n", '', $after);

    	switch($before) {
    		case "Process":
    		$process = $after;
    		continue;

    		case "CodeCategory":
    		$codeCategory= $after;
    		continue;

    		case "Category":
    		$category= $after;
    		continue;

    		case "FeatureLastNumber":
    		$numberFeature=intval($after);
    		continue;

    		case "FeatureValues":
    		$featureValues = $after;
    		$numberFeature++;
    		storeIntoDb($process, $codeCategory, $category, $featureValues, $numberFeature);
    	}
   }
   fclose($fImport);

/*
*	storeIntoDb: Insert Category, Features and Values into dftCatalogue database
*/   

function storeIntoDb($process, $codeCategory, $category, $featureValues, $numberFeature) {
	global $db_conn;

	echo "process=[$process]\ncodeCategory=[$codeCategory]\nCategory=[$category]\nfeatureValues=[$featureValues]\n\n";
	try { 
		// if the Category already exists, the next insert stetement is skipped
		$qryCategory  = 'SELECT CodeCategory FROM tblCategories WHERE CodeCategory="' . $codeCategory . '" AND ';
		$qryCategory .= 'Process="' . $process . '"';
		$stmt = $db_conn->prepare($qryCategory); 
		$stmt->execute();
		$nCategories	 = $stmt->rowCount(); 
		if ($nCategories > 0)
			;
		else {
			$qryInsert  = "INSERT INTO tblCategories VALUES (";
		    $qryInsert .= ":CodeCategory, :Category, :Process)";
			$stmt = $db_conn->prepare($qryInsert);
			$stmt->bindParam(':CodeCategory', $codeCategory, PDO::PARAM_STR);
			$stmt->bindParam(':Category', $category, PDO::PARAM_STR);
			$stmt->bindParam(':Process', $process, PDO::PARAM_STR);
			$stmt->execute();
		}			
	}
	catch (Exception $e) {
		echo "ERROR in Insert Category";
	   	echo $e->getMessage();
	   	exit;
	}

	if (strlen(trim($featureValues)) > 0)
		list($feature, $values) = explode("@", $featureValues);
	else 						// Category without features
		exit;				

	try { 
		// if the Feature already exists, the next insert usethe last NumberFeature for adding the new Feature
		$qryFeature  = 'SELECT NumberFeature FROM tblFeatures WHERE CodeCategory="' . $codeCategory . '" AND ';
		$qryFeature .= 'Process="' . $process . '" ORDER BY NumberFeature DESC';
		$stmt = $db_conn->prepare($qryFeature); 
		$stmt->execute();
		$nFeatures	 = $stmt->rowCount(); 
		if ($nFeatures > 0) {
			$rowFeature = $stmt->fetch();
			$numberFeature = intval($rowFeature["NumberFeature"]);
			$numberFeature++;
		}
		$qryInsert  = "INSERT INTO tblFeatures (CodeCategory, NumberFeature, Feature, DeeperLevel, process, Visible) VALUES (";
    	$qryInsert .= ":CodeCategory, :NumberFeature, :Feature, :DeeperLevel, :Process, :Visible) ";
		
		$stmt = $db_conn->prepare($qryInsert);
		$stmt->bindParam(':CodeCategory', $codeCategory, PDO::PARAM_STR);
		$stmt->bindParam(':NumberFeature', $numberFeature, PDO::PARAM_INT);
		$stmt->bindValue(':DeeperLevel', "N");
		$stmt->bindParam(':Feature', $feature, PDO::PARAM_STR);
		$stmt->bindParam(':Process', $process, PDO::PARAM_STR);
		$stmt->bindValue(':Visible', "S");
		$stmt->execute();
		$idRecord = $db_conn->lastInsertId();
	}
	catch (Exception $e) {
		echo "ERROR in Insert Feature";
	   	echo $e->getMessage();
	   	exit;
	}

	
	$arrayValues = explode(",", $values);			// values are separated by comma
	
	foreach ($arrayValues as $value) {
		try { 
			$qryInsert  = "INSERT INTO tblFeaturesValues (IdFeature, Value) VALUES (";
	    	$qryInsert .= ":IdFeature, :Value) ";
			
			$stmt = $db_conn->prepare($qryInsert);
			$stmt->bindParam(':IdFeature', $idRecord, PDO::PARAM_INT);
			$stmt->bindParam(':Value', $value, PDO::PARAM_STR);
			$stmt->execute();
		}
		catch (Exception $e) {
			echo "ERROR in Insert FeaturesValues";
		   	echo $e->getMessage();
		   	exit;
		}
	}
}	

?>