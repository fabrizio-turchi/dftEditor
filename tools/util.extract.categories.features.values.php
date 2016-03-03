<?php   

    define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

/** Include PHPExcel */
    require_once('classes/PHPExcel.php');


// Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

// Set document properties
    $objPHPExcel->getProperties()->setCreator("Fabrizio Turchi")
                             ->setLastModifiedBy("Fabrizio Turchi")
                             ->setTitle("DF Catalogue")
                             ->setSubject("EVIDENCE Project (GA 608185)")
                             ->setDescription("DFTools: Categories, Featuers, Values.")
                             ->setKeywords("office PHPExcel php")
                             ->setCategory("Test ...");


// Add some data
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Category')
            ->setCellValue('B1', 'Sub Category')
            ->setCellValue('C1', 'Sub sub Categeory')
            ->setCellValue('D1', 'Features')
            ->setCellValue('E1', 'values');


    try {
        $db_conn = new PDO('mysql:host=localhost;dbname=dftCatalogue;charset=utf8', 'sabato', 'umby97');
        $db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } 
    catch (PDOException $e) {
        echo "Could not connect to database";
    }

    $process ='AC';
    
    if (isset($_GET["p"]))
        $process = $_GET["p"];

    echo "<table width='100%'' border='1'>";
    $qryCategories  = "SELECT CodeCategory, Category FROM tblCategories WHERE Process='" . $process . "' ";
    $qryCategories .= "ORDER BY CodeCategory";
    $rsCategories   = $db_conn->query($qryCategories);
    $nCategories    = $rsCategories->rowCount(); 
    $idx = 1;
    for($i=0; $i<$nCategories; $i++) {
        $idx++;
        echo "<tr>";
        $rowCategory = $rsCategories->fetch();
        if (strlen($rowCategory["CodeCategory"]) == 3) {
            echo "<td>" . $rowCategory["CodeCategory"] . " " . $rowCategory["Category"] . "</td>";
            echo "<td>&nbsp;</td><td>&nbsp;</td>";
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $idx, $rowCategory["CodeCategory"] . " " . $rowCategory["Category"])
                ->setCellValue('B' . $idx, ' ')
                ->setCellValue('C' . $idx, ' ');
        }

        if (strlen($rowCategory["CodeCategory"]) == 6) {            
            echo "<td>&nbsp;</td>";
            echo "<td>" . $rowCategory["CodeCategory"] . " " . $rowCategory["Category"] . "</td>";
            echo "<td>&nbsp;</td>";
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $idx, ' ')
                ->setCellValue('B' . $idx, $rowCategory["CodeCategory"] . " " . $rowCategory["Category"])
                ->setCellValue('C' . $idx, ' ');
        }

        if (strlen($rowCategory["CodeCategory"]) == 9) {                        
            echo "<td>&nbsp;</td><td>&nbsp;</td>";
            echo "<td>" . $rowCategory["CodeCategory"] . " " . $rowCategory["Category"] . "</td>";
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $idx, ' ')
                ->setCellValue('B' . $idx, ' ')
                ->setCellValue('C' . $idx, $rowCategory["CodeCategory"] . " " . $rowCategory["Category"]);
            
        }

        $qryFeaturesValues  = "SELECT tblFeatures.IdFeature, Feature, Value FROM tblFeatures, tblFeaturesValues WHERE ";
        $qryFeaturesValues .= "tblFeatures.IdFeature=tblFeaturesValues.IdFeature AND ";
        $qryFeaturesValues .= "tblFeatures.Process='" . $process . "' AND ";
        $qryFeaturesValues .= "CodeCategory='" . $rowCategory["CodeCategory"] . "' ORDER BY Feature, Value";
        
        $rsFeaturesValues   = $db_conn->query($qryFeaturesValues);
        $nFeaturesValues    = $rsFeaturesValues->rowCount(); 
        if ($nFeaturesValues == 0) {                 // no Features, the last two columns contain Features and Values
            echo "<td>&nbsp;</td><td>&nbsp;</td>";
        }
        else {
            $line = "";
            $oldIdFeature = "";
            $oldFeature = "";
            $values = "";            
            for($j=0; $j<$nFeaturesValues; $j++) {
                $rowFeatureValues = $rsFeaturesValues->fetch();
                if ($rowFeatureValues["IdFeature"] != $oldIdFeature) {                                       
                    if ($oldIdFeature == "")          //first cycle
                       ;
                    else {
                        echo "<td><strong>" . $oldFeature . "</strong></td>";
                         $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('D' . $idx, "[* " . $oldFeature . " *]"); 
                        
                        echo "<td>" . $values . "</td></tr>";                        
                        $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('E' . $idx, $line);                     
                        
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('E' . $idx)->getAlignment()->setWrapText(true);

                        $values = "";
                        $line = "";
                        $idx++;
                       
                        echo "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
                    }
                        
                    $oldIdFeature   = $rowFeatureValues["IdFeature"];
                    $oldFeature     = $rowFeatureValues["Feature"];
                }
                $values .= $rowFeatureValues["Value"] . "<br/>";
                $line   .= $rowFeatureValues["Value"] . " \n";
            }
            echo "<td><strong>" . $oldFeature . "</strong></td>";
            echo "<td>" . $values . "</td></tr>";
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('D' . $idx, "[* " . $oldFeature . " *]"); 
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('E' . $idx, $line);
            $objPHPExcel->getActiveSheet()->getStyle('E' . $idx)->getAlignment()->setWrapText(true);
        }
    }
    echo "</table>";
    echo "</body>";
    echo "</html>";
    $objPHPExcel->getActiveSheet()->setTitle('DFT Catalogue');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    //$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
    $objWriter->save('dftc.Categories' . $process . '.xlsx');
?>                