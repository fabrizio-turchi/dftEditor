<html>
<head>
<title>EVIDENCE project: Digital Forensics Tools Editor - Export into Calc Sheet Format</title>
<link rel="stylesheet" href="./scripts/dfte.css" type="text/css"> 
<body>
<?php   
    date_default_timezone_set('Europe/Rome');


    try {
        $db_conn = new PDO('mysql:host=localhost;dbname=dftCatalogue;charset=utf8', 'sabato', 'umby97');
        $db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } 
    catch (PDOException $e) {
        echo "Could not connect to database";
    }


    define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

/** Include PHPExcel */
    require_once('../classes/PHPExcel.php');


// Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

// Set document properties
    $objPHPExcel->getProperties()->setCreator("Fabrizio Turchi")
                             ->setLastModifiedBy("Fabrizio Turchi")
                             ->setTitle("DF Catalogue")
                             ->setSubject("EVIDENCE Project (GA 608185)")
                             ->setDescription("DFTools - Catalogue")
                             ->setKeywords("office PHPExcel php")
                             ->setCategory("Test ...");


// Add some data
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Tool')
            ->setCellValue('B1', 'Url')
            ->setCellValue('C1', 'Description')
            ->setCellValue('D1', 'Categeory')
            ->setCellValue('E1', 'License')
            ->setCellValue('F1', 'Operating System')
            ->setCellValue('G1', 'Features/Values')
            ->setCellValue('H1', 'Test')
            ->setCellValue('I1', 'Useful References');

    
    createSheet("Analysis", "AN");
    
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(1)
                ->setCellValue('A1', 'Tool')
                ->setCellValue('B1', 'Url')
                ->setCellValue('C1', 'Description')
                ->setCellValue('D1', 'Categeory')
                ->setCellValue('E1', 'License')
                ->setCellValue('F1', 'Operating System')
                ->setCellValue('G1', 'Features/Values')
                ->setCellValue('H1', 'Test')
                ->setCellValue('I1', 'Useful References'); 
   
    createSheet("Acquisition", "AC");

    //$objPHPExcel->getActiveSheet()->setTitle('DFT Catalogue');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    //$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
    $objWriter->save('../debug/dftc.Catalogue.xlsx');   
    
?>
<p class=dftTextItalic>Download the Catalogue in Calc Sheet format (<a class=dftLink target="_blank" href=../debug/dftc.Catalogue.xlsx>DF Catalogue</a>)</p>
</body>
</html>

<?php

function createSheet($branch, $process) {
    global $objPHPExcel, $db_conn;

    $objPHPExcel->getActiveSheet()->setTitle($branch);

    $qryTools  = "SELECT DISTINCT tblTools.IdTool, Tool, LicenseType, OperatingSystem,
        tblToolsCategories.Process, Description, Developer, Url, TestReport, Category,
        tblCategories.CodeCategory FROM tblTools, tblCategories, tblToolsCategories WHERE 
        tblTools.IdTool=tblToolsCategories.IdTool AND 
        tblCategories.CodeCategory=tblToolsCategories.CodeCategory AND 
        tblToolsCategories.Process='" . $process . "' AND 
        tblCategories.Process = '" . $process . "' ORDER BY Tool ASC";

    //echo "qryTools=$qryTools <br/>";

    $rsTools   = $db_conn->query($qryTools);
    $nTools    = $rsTools->rowCount(); 

    $aTools = array();
    $arrayTools = $rsTools->fetchAll();

    foreach($arrayTools as $rowTool) {
        $value = $rowTool["IdTool"] . "@" . $rowTool["CodeCategory"]; 
        if (in_array($value, $aTools))
            ;
        else
            array_push($aTools, $value);             
    }

    $totTools = count($aTools); // it's for counting the total tools
    $idTool = "";
  
    $i = 1;
    foreach($arrayTools as $rowTool) {
        $sameTool = $rowTool["IdTool"] . $rowTool["CodeCategory"]; // same tool but different categories will produce different rows in the table! It's the case one tool many categories...
        if ($sameTool  == $idTool) 
            ;  
        else {            
            $i++;
            prepareRow($rowTool, $i);   
            $idTool = $sameTool;         
        }                
    }  
}    


/*
*--- function prepareRow(): 
*/
function prepareRow($rowTool, $row) {
    global $objPHPExcel, $process, $db_conn;
    

     $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $row, $rowTool["Tool"])
                ->setCellValue('B' . $row, $rowTool["Url"])
                ->setCellValue('C' . $row, $rowTool["Description"])
                ->setCellValue('D' . $row, $rowTool["Category"])
                ->setCellValue('E' . $row, $rowTool["LicenseType"])
                ->setCellValue('F' . $row, $rowTool["OperatingSystem"]);

    $objPHPExcel->getActiveSheet()
                ->getStyle('C' . $row)->getAlignment()->setWrapText(true);   
    
    $lineTest = "";

    if ($rowTool["TestReport"] == 'S') {
        $qryTests = "SELECT ReportUrl, NoteTest FROM tblToolsReports WHERE IdTool=" . $rowTool["IdTool"] . ' AND Process="' . $rowTool["Process"]  . '" ';
        //windowAlert($qryTests);
        $rsTests = $db_conn->query($qryTests);
        $nTests = $rsTests->rowCount();
                  
        for ($t=0; $t<$nTests; $t++) {
            $rowTest = $rsTests->fetch();
            $lineTest .= $rowTest["ReportUrl"] . "\r";
        }
    }      
     
    $objPHPExcel->getActiveSheet()
                ->setCellValue('H' . $row, $lineTest); 
    $objPHPExcel->getActiveSheet()
                ->getStyle('H' . $row)->getAlignment()->setWrapText(true);   
    
    valueFeature($rowTool, $row);

    $qryReferences = "SELECT * FROM tblToolsUsefulReferences WHERE IdTool=" . $rowTool["IdTool"];
    $rsReferences = $db_conn->query($qryReferences);
    $nReferences = $rsReferences->rowCount();
    $line = "";
    for ($i=0; $i<$nReferences; $i++) {
        $rowReference = $rsReferences->fetch();
        $note = $rowReference["ReferenceNote"];
        if ($note == "")
            $note = "*reference*";

        $line .= $note;
    }
    $objPHPExcel->getActiveSheet()
                ->setCellValue('I' . $row, $line);    
                
}

/*
*--- function valueFeature() 
*/

 function valueFeature($rowTool, $row) {
    global $objPHPExcel, $process, $db_conn;
        
    $qryValues   = "SELECT ValueFeature, Feature FROM tblTools, tblToolsFeatures, tblFeatures WHERE ";
    $qryValues  .= "tblTools.IdTool=tblToolsFeatures.IdTool AND ";
    $qryValues  .= "tblToolsFeatures.IdFeature=tblFeatures.IdFeature AND ";
    $qryValues  .= "tblTools.IdTool=" . $rowTool["IdTool"] . " AND ";
    $qryValues  .= "tblTools.Process='" . $rowTool["Process"] . "' ORDER BY tblFeatures.IdFeature, ValueFeature ";
    $rsValues = $db_conn->query($qryValues);
    $nValues = $rsValues->rowCount();    
   
    $oldFeature = "";
    $sValue = "";
    $cellContent = "";
    if ($nValues > 0) {
        for ($j=0; $j < $nValues; $j++) {
            $rowValue = $rsValues->fetch();
            if ($oldFeature == $rowValue["Feature"])
                ;
            else {               
                if (strlen($sValue)  > 0) {    // id sValue is not empty, it contains the Values related to the previous Feature, and it's time to show them
                    $sValue = substr($sValue, 0, -2);
                    $cellContent .= $sValue . "\r";
                }
                $cellContent .= "[* " . $rowValue["Feature"] . " *]\r";
                $oldFeature = $rowValue["Feature"];
                $sValue = "";
            }                   
            $sValue .= $rowValue["ValueFeature"] . ', ';
        }               
        $sValue = substr($sValue, 0, -2);
        $cellContent .= $sValue . "\r";
    }
     $objPHPExcel->getActiveSheet()
                ->setCellValue('G' . $row, $cellContent); 

    $objPHPExcel->getActiveSheet()
                ->getStyle('G' . $row)->getAlignment()->setWrapText(true);
}

?>