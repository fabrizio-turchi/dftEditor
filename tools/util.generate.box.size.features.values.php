    <?php   
    define("FILE_OUT_EDITOR", "config/dfte.features.panel.php");
    define("SPACE_FEATURE", 30);        // space taken by each check box of the  Feature in the Panel
    define("SPACE_VALUE", 25);          // space taken by each couple of Values on the Features Panel
    define("SPACE_HR", 25);             // space taken by <hr> for Features separator   
    define("SIZE_BASE", 50);            // default size Features Panel

    try {
        $db_conn = new PDO('mysql:host=localhost;dbname=dftCatalogue;charset=utf8', 'sabato', 'umby97');
        $db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } 
    catch (PDOException $e) {
        echo "Could not connect to database";
        exit;
    }

    
    try{
        $fEditor = fopen(FILE_OUT_EDITOR, "w");        
    }
    catch (Exception $e) {
        echo "Could not open the Editor file";
        exit; 
    }

      
    fwrite($fEditor, "<?php \n");
    generateBox($fEditor, "AN");
    generateBox($fEditor, "AC");
    fwrite($fEditor, "?>\n");

    close($fEditor);
?>    

<?php 
/*
*  generateBox(): generate the size of the Features box on the basis of the selected Category in the Caegory combo box
*
*                   $fOut: handle output file
*                   $process: it assumes AN (Analysis) or AC (Acquisiton) 
*
*/    
function generateBox($fEdit, $process) {
    global $db_conn;

    $qryCategories  = "SELECT CodeCategory, Category FROM tblCategories WHERE Process='" . $process . "' ";
    $qryCategories .= "ORDER BY CodeCategory";
    
    $rsCategories   = $db_conn->query($qryCategories);
    $nCategories    = $rsCategories->rowCount(); 
    fwrite($fEdit, "echo '<select class=dftHidden name=boxSizes" . $process . ">';\n");
    fwrite($fEdit, "echo '<option value=" . SIZE_BASE . ">0</option>';\n");
    
    for($i=0; $i<$nCategories; $i++) {
        $rowCategory = $rsCategories->fetch();
        $qryFeatures  = "SELECT IdFeature FROM tblFeatures WHERE CodeCategory='";
        $qryFeatures .= $rowCategory["CodeCategory"] . "' AND Process='" .$process . "' ";
        $rsFeatures   = $db_conn->query($qryFeatures);
        $nFeatures    = $rsFeatures->rowCount(); 
        $nValues = 0;

        for($j=0; $j<$nFeatures; $j++) {
            $rowFeature = $rsFeatures->fetch();
            $qryValues = "SELECT COUNT(IdFeatureValue) AS numValues FROM tblFeaturesValues WHERE IdFeature=" . $rowFeature["IdFeature"];
            $rsValues  = $db_conn->query($qryValues);
            $rowValues = $rsValues->fetch();
            $nValue    = intval($rowValues["numValues"]);

            $nValues += SPACE_FEATURE   ;                                                  // space taken by the Feature
            $nValues += (intval($nValue / 2) + intval($nValue) % 2) * SPACE_VALUE;  // space taken by Values
        }
    
        $nValues += ($nFeatures - 1) * SPACE_HR;         

        echo $rowCategory["CodeCategory"] . " - nF=" . $nFeatures . " - nV=" . $nValues .  "\n";

        fwrite($fEdit, "echo '<option value=" . $nValues . ">" . $rowCategory["CodeCategory"] . "</option>';\n");
    }
    fwrite($fEdit, "echo '</select>';\n");
}
?>
