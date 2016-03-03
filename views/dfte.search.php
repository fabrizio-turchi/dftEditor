<!--body onLoad=javascript:SetData("Analysis");-->
<?php
    if (isset($_POST["dftRequest"]) && ($_POST["dftRequest"] != "")) {
        $dftRequest = $_POST["dftRequest"];
        $process = $_POST["process"];
        $toolName = $_POST["toolName"];    
        $license = $_POST["license"];
        $codeCategory = $_POST["CodeCategory"];
        $category = $_POST["Category"];
        $os = $_POST["os"];   
        $sort = $_POST["sort"]; 
        $direction = $_POST["direction"]; 
        $qryCatalogue = $_POST["qryCatalogue"];
    }
    else {
        $dftRequest = "";
        $process='AN';
        $toolName = "";
        $license = "";
        $codeCategory = "";
        $category = "All";
        $os = "";
        $sort='Tool';
        $direction = 'ASC';
        $qryCatalogue = '';
    }

?>    

<input type=hidden name=dftRequest value=<?php echo $dftRequest ?>>
<input name=editorIdTool class=dftHidden value="">
<input name=editorSelectedCodeCategory class=dftHidden value="">

<p class=dftPulsanti align=center>Forensics Tools Catalogue Editor (<a class=dftLink target="_blank" href=dfte.help.html
 title='Help on Catalogue Editing'>?</a>)<br/>
<span class="button-wrap">
<a href="javascript:EditNewTool()" title="Add a new tool" class="button button-pill ">+ Tool</a>
</span>  
<hr/>
<input type=hidden readonly name=dfTools class=dftEnfasi2 size=10 value='Analysis'></p>
<!--p class=dftTextGrassetto>EVIDENCE project</p-->
<!--p><a href=# onClick=removePanel();>Remove FeaturePanel</a></p-->
<!--p><a href=# onClick=addPanel();>Add FeaturePanel</a></p-->
<table border='0'>
<?php    
   
   global   $arrayCategoriesAN,   $arrayCategoriesAC;   
   
   $qryTools      = 'SELECT DISTINCT tblTools.IdTool, Tool, LicenseType, OperatingSystem, tblToolsCategories.Process, Developer, Url, TestReport, Category, tblCategories.CodeCategory ';
   $qryTools     .= ' FROM tblTools, tblCategories, tblToolsCategories WHERE tblTools.IdTool=tblToolsCategories.IdTool AND tblCategories.CodeCategory=tblToolsCategories.CodeCategory AND ';
   $qryTools     .= 'tblToolsCategories.Process="AN" AND tblCategories.Process = "AN" ';    // Analysis
   $rsTools	     = $db_conn->query($qryTools);
   $nToolsAN = $rsTools->rowCount();
   
   $qryTools      = 'SELECT DISTINCT tblTools.IdTool, Tool, LicenseType, OperatingSystem, tblToolsCategories.Process, Developer, Url, TestReport, Category, tblCategories.CodeCategory ';
   $qryTools     .= ' FROM tblTools, tblCategories, tblToolsCategories WHERE tblTools.IdTool=tblToolsCategories.IdTool AND tblCategories.CodeCategory=tblToolsCategories.CodeCategory AND ';
   $qryTools     .= 'tblToolsCategories.Process="AC" AND tblCategories.Process = "AC" ';    // Acquisition
   $rsTools	     = $db_conn->query($qryTools);
   $nToolsAC = $rsTools->rowCount();
 
  echo "<tr class=dftText><td class=dftEnfasi5 >Total tools: ";
  echo "<input class=dftEnfasi2 type=text name=totalTools readonly size=5 value=" . $nToolsAN . ">"; 
  echo "&nbsp;&nbsp;<span class=dftText>Select  criteria and  </span>";
  echo '<span class="button-wrap">';
  echo '<a href="javascript:Search()" title="Get tools on selected search criteria" class="button button-pill ">Search</a></td></tr>';
  echo '</span>';
  echo "<tr><td>&nbsp;</td></tr>";
  echo '<tr class=dftText align=center><td>';
  echo '<div id="radio">';
  echo '<input type="radio" id="rbAnalysis" name="rbProcess" value="AN" checked  onClick=javascript:SetData("Analysis");><label for="rbAnalysis">Analysis</label>';
  echo '<input type="radio" id="rbAcquisition" name="rbProcess" value="AC" onClick=javascript:SetData("Acquisition");><label for="rbAcquisition">Acquisition</label>';
  echo "<input type=hidden name=process value=" . $process . ">";
  echo '</div></td></tr>';
  echo "<tr><td>&nbsp;</td></tr>";
  echo "<tr class=dftText><td class=dftTextGrassetto>Name<br/>";
  echo '<input text name=toolName size=40 value="' . $toolName . '" onKeyPress=checkSubmit(event);></td></tr>';
  echo"<tr><td>&nbsp;</td></tr>";
  
  echo "<tr class='dftText'>";
  showLicenseType($license, "license", "<br/>");
  echo "</tr>";
    
  
  echo "<input name=Category class=dftHidden value=''> "; 
  //$aKeysFeatures = array();
  //$aNamesFeatures = array();

  
  $arrayCategoriesAN = createArrayCategory("AN");

  echo "<tr class='dftText'>";
  showCategory($arrayCategoriesAN, $codeCategory, "CodeCategory", "CheckCategory", "AN", "<br/>");
  echo "</tr>";

  
// SELECT CodeCategoryAN, categories related to Analysis
  createSelectHidden($arrayCategoriesAN, "CodeCategoryAN");
  
  $arrayCategoriesAC =   createArrayCategory("AC");

  // SELECT CodeCategoryAC, categories related to Acqusition
  createSelectHidden($arrayCategoriesAC, "CodeCategoryAC"); 

// Features related to Categories of Analysis
  createSelectHiddenFeatures($arrayCategoriesAN, "FeaturesAN", "AN");
  
   
// Features related to Categories of Acquisition
  createSelectHiddenFeatures($arrayCategoriesAC, "FeaturesAC", "AC");


     
  $qryValues = "SELECT IdFeature, Value FROM tblFeaturesValues ORDER BY IdFeature, Value";
  $rsValues	     = $db_conn->query($qryValues);
  $nValues	 = $rsValues->rowCount();
    
  $arrayValues = $rsValues->fetchAll();
  $rowValue = $arrayValues[0];
  $idOld = $rowValue["IdFeature"];
  $value = "";
  echo "<select name=FeaturesValues class=dftHidden>";
  foreach($arrayValues as $rowValue) {
      if ($idOld == $rowValue["IdFeature"])
          $value .=  trim($rowValue["Value"])  . "#";
      else {
          $value = substr($value, 0, -1);
          echo '<option value=' . $idOld . '>' . $value . '</option>';
          $idOld = $rowValue["IdFeature"];
          $value =  trim($rowValue["Value"]) . "#";
      }                        
  }
  $value = substr($value, 0, -1);
  echo '<option value=' . $idOld . '>' . $value . '</option>';
  echo "</select>";   
    

  echo"<tr><td>&nbsp;</td></tr>";
  
  echo "<tr class='dftText'>";
  showOperatingSystem($os, "os", "<br/>");
  echo "</tr>";

  //echo "<tr><td>&nbsp;</td></tr>";
  
  require_once("config/dfte.features.panel.php");

?>
</table>
<p>&nbsp;</p>
<div id="container"><p class='dftTextItalic' align='center'>Features Panel</p>
<div id="box">Box con testo</div>
</div>
<input type=hidden name=sort value='Tool'>
<input type=hidden name=direction value='ASC'>
<input type=hidden name=qryCatalogue value=''>
<!-- idTool for the editor DIV -->

<?php
    echo '<input type=hidden name=nToolsAN value=' . $nToolsAN . '>';
    echo '<input type=hidden name=nToolsAC value=' . $nToolsAC . '>';
//  if a query has been run, the Features Panels must be initialized, otherwise it occurs only when the event onChange is triggered
    if ($dftRequest == "query")
        echo "<script>CheckCategory();</script>";

?>

<?php
/*
*--- Utilities: functions for a concise structure of the code
*/

//
//--- showLicenseType(): shows the form field LicenseType
//--- Input:
//        $license is the value to be selected
//        $fieldName is the name of the field
//        $separator is the way to show the label and the field, for a single cell of table the value is <br/> for two cells the value is </td><td>


function showLicenseType($license, $fieldName, $separator) {
  global $db_conn;

  $qryLicense    = " SELECT DISTINCT LicenseType FROM tblTools";
  $rsLicense         = $db_conn->query($qryLicense);
  $nLicense  = $rsLicense->rowCount();
  
  echo "<td class=dftTextGrassetto>License type&nbsp;&nbsp;&nbsp;" . $separator;
  echo "<select name=" . $fieldName . " class=dftText>";
  echo "<option class='mainOption' value=''>All</option>";
  for($i=0; $i<$nLicense; $i++) {
    $rowLicense = $rsLicense->fetch();
    $selected = " ";
    if ($rowLicense["LicenseType"] == $license)
        $selected = " selected ";

    if (trim($rowLicense["LicenseType"] ) == "")
        ;
    else        
        echo "<option " . $selected . "value='" . $rowLicense["LicenseType"] . "'>" . trim($rowLicense["LicenseType"]) . "</option>";       
  }
  echo "</td>";
}


//
//--- showOperatingSystem(): shows the form field LicenseType
//--- Input:
//        $os is the value to be selected
//        $fieldName is the name of the field
//        $separator is the way to show the label and the field, for a single cell od table the value is <br/> for two cells the value is </td><td>
function showOperatingSystem($os, $fieldName, $separator) {
  global $db_conn;

  $qryOS    = " SELECT DISTINCT OperatingSystem FROM tblTools";
  $rsOSs       = $db_conn->query($qryOS);
  $nOSs  = $rsOSs->rowCount();
  echo "<td class=dftTextGrassetto>O.S.&nbsp;&nbsp;&nbsp;" . $separator;
  echo "<select name=" . $fieldName . " class=dftText>";
  echo "<option class='mainOption' value=''>All</option>";
  for($i=0; $i<$nOSs; $i++) {
    $rowOS = $rsOSs->fetch();
    $selected = " ";
    if ($rowOS["OperatingSystem"] == $os)
        $selected = " selected ";

    if (trim($rowOS["OperatingSystem"] ) == "")
        ;
    else        
        echo "<option " . $selected . "value='" . $rowOS["OperatingSystem"] . "'>" . trim($rowOS["OperatingSystem"]) . "</option>";   
  }
  echo "</select>";
  echo "</td>";
} 

/*
*---- createArray(): creates an array of Categories retrieved from the database
* 
*     Input
*           $process: it assumes the values AN (Analysis) or AC (Acquisition)
*/
function createArrayCategory($process) {
  global $db_conn;

  $qryCategories    = "SELECT * FROM tblCategories WHERE Process='" . $process . "' ORDER BY CodeCategory";
  $rsCategoriesAN      = $db_conn->query($qryCategories);
  $nCategoriesAN   = $rsCategoriesAN->rowCount();
  $arrayCategoriesAN = $rsCategoriesAN->fetchAll();
  return $arrayCategoriesAN;
}

/*
*--- showCategory():  shows the Category SELECT form field 
*
*   Input
*         $array: contains the Code and Category records
*         $codeCategory: is the Code to be selected
*         $fieldName: the SELECT field name 
*         $jsFunction: tha javascript function in charge for managing the onChange even of the SELECT field
*         $process: it assumes the values AN (Analysis) or AC (Acquisition)
*         $separator: it assumes the values <br/> in case of label and field on two different row, </td><td> on the same row
*/
function showCategory($array, $codeCategory, $fieldName, $jsFunction, $process, $separator) {
  global $db_conn;

  echo "<td class=dftTextGrassetto>Category" . $separator;
  echo "<select name=" . $fieldName . " class=dftText onChange=". $jsFunction . "();>";
  echo "<option class='mainOption' value=''>All</option>";
  foreach($array as $rowCategory) {
    $codeLen = strlen($rowCategory["CodeCategory"] );
    $space = str_repeat("&nbsp;",  $codeLen - 3);
    $sCategory = $rowCategory["Category"];
    $sCode = $rowCategory["CodeCategory"];
    if ($sCode == $codeCategory)
        $selected = " selected ";
    else
        $selected = " ";
    //$middot = str_repeat("&middot;",  strlen($sCode));

    if ($codeLen == 3)    // main category
        //echo "<option class='mainOption' value='" . $rowCategory["CodeCategory"] . "'>" . $space .  $sCode . '&nbsp;' . $sCategory . "</option>";   
        echo "<option class='mainOption' " . $selected . "value='" . $rowCategory["CodeCategory"] . "'>" . $space .  $sCode . '&nbsp;' . $sCategory . "</option>";    
    else
        echo "<option class='dftText'" . $selected . "value='" . $rowCategory["CodeCategory"] . "'>" . $space . $sCode . '&nbsp;' .  $sCategory . "</option>";    
  }
  echo "</select></td>";
  // return $arrayCategoriesAN;
} 


/*
*---- createSelectHidden: creates a form field of SELECT type 
*
*---- Input
*           $array tha array containing the value and the text of the SELECT field
*           $nameField tha name of the SELECT field
*
*/
function createSelectHidden($array, $nameField) {

  echo "<select name=". $nameField . " class=dftHidden>";
  echo "<option class='mainOption' value=''>All</option>";
  foreach($array as $rowCategory) {
    $codeLen = strlen($rowCategory["CodeCategory"] );
    $space = str_repeat("&nbsp;",  $codeLen - 3);
    $sCategory = $rowCategory["Category"];
    $sCode = $rowCategory["CodeCategory"];
    if ($codeLen == 3)    // main category
        echo "<option value='" . $rowCategory["CodeCategory"] . "'>" . $space .  $sCode . '&nbsp;' . $sCategory . "</option>";    
    else
        echo "<option value='" . $rowCategory["CodeCategory"] . "'>" . $space . $sCode . '&nbsp;' .  $sCategory . "</option>";    
  }
  echo "</select>";
}

/*
*---- createSelectHiddenFeatures: creates a form field of SELECT type for Features
*
*---- Input
*           $array tha array containing the value and the text of the SELECT field
*           $nameField tha name of the SELECT field
*           $process it assumes the values AN, for Analysis, or AC for Acqusiition
*
*/
function createSelectHiddenFeatures($array, $fieldName, $process) {
  global $db_conn;

  echo "<select name=" . $fieldName . " class=dftHidden>";
  echo "<option value=''></option>";
  foreach($array as $rowCategory){
    $qryFeatures  = 'SELECT IdFeature, Feature, DeeperLevel, Visible FROM tblFeatures WHERE ';
    $qryFeatures .= 'CodeCategory="' . $rowCategory["CodeCategory"]  . '" AND Process="' . $process . '" ORDER BY NumberFeature';
    $rsFeatures      = $db_conn->query($qryFeatures);
    $nFeatures   = $rsFeatures->rowCount();
    $keysFeatures = "";
    $namesFeatures = "";
        
    if ($nFeatures > 0) {
      for ($j=0; $j < $nFeatures; $j++) {
        $rowFeature = $rsFeatures->fetch();
        $keysFeatures .= $rowFeature[0] . "@" . $rowFeature[2] . "@" .  $rowFeature[3] .  "#" ;
        $namesFeatures .= $rowFeature[1] . "#" ;
      }
      $keysFeatures = substr($keysFeatures, 0, -1);
      $namesFeatures = substr($namesFeatures, 0, -1);    
      echo '<option value="'   .  $keysFeatures . '">' . $namesFeatures . '</option>';
    }           
    else 
      echo "<option value=''></option>";
  }            
  echo "</select>";  
}  
?>