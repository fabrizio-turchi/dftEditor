<img src='images/dfte.evidence.logo.arrow.png' >
<img  src='images/dfte.evidence.logo.acronym.png' alt='EVIDENCE Project' border='0'></a> European Project<br/>
<?php 
  // code catageory leaves without features: in these case the query can't contain tables tblFeatures and tblToolsFeatures
    $codesLeavesNoFeatures= array("01.05.AN", "07.AN", "07.02.AN", "08.02.AN", "08.03.AN", "08.04.AN", "08.05.AN", "01.02.AC", "AC", "AN");         

	switch($dftRequest) {
		case "query":	// query on Catalogue to identify the tool to be modified	
			ProcessQuery();
			break;

		case "edit":	// preparing form for updating the selected (after a query) tool
			$editorIdTool = $_POST["editorIdTool"];
			EditTool($editorIdTool, "beforeUpdating");
			break;

    case "new":   // preparing form for inserting a new tool
      EditTool(0, "beforeInsert");  // there is no idTool=0, so it will prepare an empty form
      break;

		case "commitUpdate":	// commit an update on existing tool
			$editorIdTool = $_POST["editorIdTool"];
			UpdateTool($editorIdTool, "U");
			break;

		case "showUpdated":	// show the values of the updated tool
			$editorIdTool = $_POST["editorIdToolCommitted"]; 
			EditTool($editorIdTool, "afterUpdating");
			break;

		case "commitNew":					
			// NewTool() relying on EditorTools table
			$editorIdTool = $_POST["editorIdTool"];
			UpdateTool($editorIdTool, "I");
			break;

    case "showNew": // show the values of the added tool
      $editorIdTool = $_POST["editorIdToolCommitted"];
      EditTool($editorIdTool, "afterInsert");
      break;

    case "approval": // show the values of the added tool
      ApprovalShow();
      break;

    case "approvalSingle": // commit a single updating/insert of a tool
      $idEditorTool = $_POST["idApprovalTool"];
      $msgAlert = True;
      ApprovalSingleTool($idEditorTool, $msgAlert); 
      break;

    case "approvalAll": // commit all pending updating/insert tools
      ApprovalAllTools();
      break;

		case "":		// no action has been requested
			echo "No query has been run!"; 
	}	
?>
	
<?php

// function ProcessQuery: view the results of a query and provide the edit icon on each retrieved tool
function ProcessQuery() {
// $POST variables	
	global $process, $toolName, $codeCategory, $category, $os, $sort, $direction, $qryCatalogue;
  global $license, $codesLeavesNoFeatures, $db_conn;
             
/*    
* --- catch values/features for filtering the values in the table tblToolsFeatures (see function valueFeature()
*/    
    $qryFeatures = 'SELECT IdFeature, DeeperLevel FROM tblFeatures WHERE CodeCategory="' . $codeCategory  . '" AND Visible="S" ORDER BY NumberFeature';
    //fwrite($debugFile, $qtyFeatures . "\n");
    $rsFeatures	     = $db_conn->query($qryFeatures);
    $nFeatures	 = $rsFeatures->rowCount();        
    if ($nFeatures == 0)   // codeCategory doesn't have feature or category wasn't selected, in this case there is no filter on FeaturesValues
        $valueFinalFilter = "";
    else {
        $valueFinalFilter = "";
        $valueFilter = "";
        for ($i=0; $i < $nFeatures; $i++) {
            $rowFeature = $rsFeatures->fetch();
                //fwrite($debugFile, "single/multi feature, line 96 \n");
                $idFeature = $rowFeature["IdFeature"];
                //$valueFilter = '(tblToolsFeatures.IdFeature=' . $idFeature . ' AND ( ';
                $qryValues = "SELECT Value FROM tblFeaturesValues WHERE IdFeature =" . $idFeature;
                $checkedValues = 0;
                $rsValues = $db_conn->query($qryValues);
                $nValues = $rsValues->rowCount();
                for ($m=0; $m < $nValues; $m++) {   // loop for managing the feature values that have been selected/checked
                    $varPost = (string)$idFeature . "_" . $m;
                    if (isset($_POST[$varPost])) {  
                        $checkedValues++;
                        // fwrite($debugFile, "varPost=" . $varPost . "\n");          
                        $valueFilter .= ' ValueFeature="' . trim($_POST[$varPost]) . '" OR ';
                    }
                }         //after each feature              
                if ($checkedValues > 0)  { // if all values are deselected there is no conditions on them!
                    $valueFilter = substr($valueFilter, 0, -4); 
                    $valueFinalFilter .= '(tblToolsFeatures.IdFeature=' . $idFeature . ' AND ( ' . $valueFilter;
                    $valueFinalFilter .=  ')) OR ';
                    $valueFilter = "";
                    //fwrite($debugFile, "valueFinalFilter=" . $valueFinalFilter . "\n");
                    
                }                     
            
        }                            
            $valueFinalFilter = substr($valueFinalFilter, 0, -3);            
    }
    
    
        if (strlen($valueFinalFilter) > 0) 
            $valueFinalFilter = " AND (" . $valueFinalFilter . ") ";            
        if (in_array($codeCategory . $process, $codesLeavesNoFeatures)) {    // weird case:  category without children, a leaf, and without features! 
            //fwrite($debugFile, "category " . $codeCategory . " no features \n");
            $qryToolsBase  = 'SELECT DISTINCT tblTools.IdTool, Tool, LicenseType, OperatingSystem, tblToolsCategories.Process, Developer, Description, Url, TestReport, ';
            $qryToolsBase .= 'Category, tblCategories.CodeCategory ';
            $qryToolsBase .= 'FROM tblTools, tblCategories, tblToolsCategories ';
            $qryToolsBase .= 'WHERE tblTools.IdTool=tblToolsCategories.IdTool AND tblCategories.CodeCategory=tblToolsCategories.CodeCategory ';
        }
        else {
            $qryToolsBase  = 'SELECT DISTINCT tblTools.IdTool, Tool, LicenseType, OperatingSystem, tblToolsCategories.Process, Developer, Description, Url, TestReport, ';
            $qryToolsBase .= 'Category, tblCategories.CodeCategory, Feature, tblToolsFeatures.IdFeature, DeeperLevel ';
            $qryToolsBase .= 'FROM tblTools, tblCategories, tblToolsCategories, tblToolsFeatures, tblFeatures ';
            $qryToolsBase .= 'WHERE tblTools.IdTool=tblToolsCategories.IdTool AND tblCategories.CodeCategory=tblToolsCategories.CodeCategory ';
            $qryToolsBase .= 'AND tblTools.IdTool = tblToolsFeatures.IdTool  AND tblCategories.CodeCategory = tblToolsFeatures.CodeCategory ';
            $qryToolsBase .= 'AND tblToolsFeatures.IdFeature=tblFeatures.IdFeature ' . $valueFinalFilter;
        }              
    
    $furtherWhereCondition = "";
    if ( $process == "")
        ;
    else
        $furtherWhereCondition .= ' AND tblToolsCategories.Process="' . $process . '"  AND tblCategories.Process = "' . $process . '"'; 
        
    if ( $toolName == "")
        ;
    else
        $furtherWhereCondition .= ' AND Tool LIKE "%' . $toolName . '%" ';      
        
    if ( $license == "")
        ;
    else
        $furtherWhereCondition .= ' AND LicenseType = "' . $license . '" ';              

    
    if ( $os == "")
        ;
    else
        $furtherWhereCondition .= ' AND OperatingSystem = "' . $os . '" ';      
            
   
                        
    $furtherWhereCondition .= '  AND tblCategories.CodeCategory LIKE "' . $codeCategory . '%"';
    
    
    $orderCondition = ' ORDER BY '     . $sort . ' ' . $direction;  
    
/*--- the order on Features shows the same tool in different not contiguous rows and the results will be wrong!

    $pos = strpos($qryToolsBase, "tblFeatures");
    if ($pos === false)
        ;
    else
        $orderCondition .= " , tblFeatures.Feature";
*/        
                    
    $qryToolsBase .=  $furtherWhereCondition;
        
    if ($qryCatalogue == '')
        $qryTools =  $qryToolsBase . $orderCondition;
    else
        $qryTools =  $qryCatalogue . $orderCondition;
    
    $rsTools	     = $db_conn->query($qryTools);
    $nTools = $rsTools->rowCount();
        
    if ($nTools == 0) {   // special cases to be managed in a different way: for example selecting only O.S. with value Hardware 
        $qryToolsBase  = 'SELECT DISTINCT tblTools.IdTool, Tool, LicenseType, OperatingSystem, tblToolsCategories.Process, Developer, Url, TestReport, Description, ';
        $qryToolsBase .= 'Category, tblCategories.CodeCategory ';
        $qryToolsBase .= 'FROM tblTools, tblCategories, tblToolsCategories ';
        $qryToolsBase .= 'WHERE tblTools.IdTool=tblToolsCategories.IdTool AND tblCategories.CodeCategory=tblToolsCategories.CodeCategory ';
        $qryToolsBase .= $furtherWhereCondition;
        $qryTools         = $qryToolsBase . $orderCondition;
        $rsTools	         = $db_conn->query($qryTools);
        $nTools = $rsTools->rowCount();
    }        
    
    $aTools = array();
    $arrayTools = $rsTools->fetchAll();

    foreach($arrayTools as $rowTool) {
        $value = $rowTool["IdTool"] . "@" . $rowTool["CodeCategory"]; 
         if (in_array($value, $aTools))
             ;
        else
            array_push($aTools, $value);             
    }
    $totTools = count($aTools);
    
/*    if ($nTools > 0)        // if the result set is empty the pointer move will show an error
        mysql_data_seek($rsTools, 0);
*/        
        
    if ($_SERVER['REMOTE_ADDR'] == "127.0.0.1")    // localhost
    	echo '<p class=dftText>*** start debug<br/>' . $qryTools . '<br/>***end debug</p>';

   
    echo '<p class="dftTextGrassetto">EVIDENCE project - Digital Forensics Tools Catalgoue Results: <span class=dftEnfasi5>found tools <span class=dftEnfasi2>' . $totTools . '</span></p>';
    
    echo "<div id=tblTools>";
    echo '<table  border=1 style="width:100%; table-layout-fixed; word-wrap:break-word;">';
    echo "<tr class=dftTextGrassetto align=center><td style='width:15%'>Tool&nbsp;";
    echo "<a href=javascript:Sort('Tool','DESC');><img src=images/dfte.order.down.png></a>&nbsp;";
    echo "<a href=javascript:Sort('Tool','ASC');><img src=images/dfte.order.up.png></a>";
    echo "<br/>(Developer)</td>";
    echo "<td style='width:20%'>Description</td>";
    echo "<td style='width:15%'>Category&nbsp;";
    echo "<a href=javascript:Sort('tblCategories.Category','DESC');><img src=images/dfte.order.down.png></a>&nbsp;";
    echo "<a href=javascript:Sort('tblCategories.Category','ASC');><img src=images/dfte.order.up.png></a></td>";
    echo "<td style='width:10%'>License&nbsp;";
    echo "<a href=javascript:Sort('LicenseType','DESC');><img src=images/dfte.order.down.png></a>&nbsp;";
    echo "<a href=javascript:Sort('LicenseType','ASC');><img src=images/dfte.order.up.png></a></td>";
    echo "<td style='width:10%'>O.S.&nbsp;";
    echo "<a href=javascript:Sort('OperatingSystem','DESC');><img src=images/dfte.order.down.png></a>&nbsp;";
    echo "<a href=javascript:Sort('OperatingSystem','ASC');><img src=images/dfte.order.up.png></a></td>";
    echo "<td style='width:15%'>Features / Values</td>";   
    echo "<td style='width:15%'>Useful<br/>References</td></tr>";      
    
    $idTool = "";
    
    $i = 0;
    foreach($arrayTools as $rowTool) {
        $sameTool = $rowTool["IdTool"] . $rowTool["CodeCategory"]; // same tool but different categories will produce different rows in the table! It's the case one tool many categories...
        if ($sameTool  == $idTool) {
        }                            
        else {            
            if ($i > 0)     // not first cycle
                echo  "</tr>";
  
            prepareRow($rowTool);   
            $idTool = $sameTool;         
        }                
        $i++;        
    }  
  echo "</table></div>";
}	

/*
*---	function EditTool(): shows the form fields to change the selected tool data
*
*---	Input: 
*							$idTool 	id tool before updating or after updating
*							$status 	it takes the values beforeUpdating or afterUpdating. In beforeUpdating status the editor DIV contains
*												the values of the tool that is being updated; in afterUpdating status the DIV must contain the updated
*												values so id tool is the idEditorTool and the other values come from tblEditorTools, tblEditorCategories,
*												tblEditorToolsFeatures, tblEditorToolsReports and the Update button is not shown!
*/
function EditTool($idTool, $status) {
	global $process, $db_conn, $arrayCategoriesAN, $arrayCategoriesAC;

	// query for extracting the general data of tool

	switch($status) {
		
    case "beforeUpdating":  // case "beforeUpdating" an existing tool is being updated
			$tblTools 					= "tblTools";
			$tblToolsCategories = "tblToolsCategories";
			$tblToolsFeatures 	= "tblToolsFeatures";			
			$tblToolsReports 		= "tblToolsReports";
      $tblUsefulReferences = "tblToolsUsefulReferences";
			$idToolField 				= "IdTool";
      $pastIdToolField    = "";
			break;

    case "beforeInsert":    // case "beforeInsert"  // a new tool is being added
      $tblTools           = "tblTools";
      $tblToolsCategories = "tblToolsCategories";
      $tblToolsFeatures   = "tblToolsFeatures";     
      $tblToolsReports    = "tblToolsReports";
      $tblUsefulReferences = "tblToolsUsefulReferences";
      $idToolField        = "IdTool";
      $pastIdToolField    = "";
      break;

    case "afterUpdating":   // case "afterUpdating" an existing tool has just been updated
			$tblTools 					= "tblEditorTools";
			$tblToolsCategories = "tblEditorToolsCategories";
			$tblToolsFeatures 	= "tblEditorToolsFeatures";
			$tblToolsReports 		= "tblEditorToolsReports";
      $tblUsefulReferences = "tblEditorToolsUsefulReferences";
			$idToolField 				= "IdEditorTool";				
      $pastIdToolField    = ", IdTool ";
			break;
		
    case "afterInsert":     // case "afterInsert" a new tool has just been added
      $tblTools           = "tblEditorTools";
      $tblToolsCategories = "tblEditorToolsCategories";
      $tblToolsFeatures   = "tblEditorToolsFeatures";
      $tblToolsReports    = "tblEditorToolsReports";
      $tblUsefulReferences = "tblEditorToolsUsefulReferences";
      $idToolField        = "IdEditorTool"; 
      $pastIdToolField    = "";      
      break;

		case "":
			echo "<p class=dftError>EditTool() wrong parameter!</p>";
			return(false);
	}

 
  $bannedValues = '';
  if ($status == "beforeUpdating") {
    $selectedCodeCategory = $_POST["editorSelectedCodeCategory"];
    $addWhere = " AND tblCategories.CodeCategory='" . $selectedCodeCategory . "'";

//  it retrieves all Categories for the selected tools: this is important when the selected tool belongs to more than one category.
//  If the tool has two categories Ca and Cb and it has been selected within the Ca category, the system won't allow to assign a 
//  different Category if it is Cb, because at that phase, the program does't know what are the features for the Category Cb and will 
//  show all possible Features. In this way the updating will generate  two Cb Categories with different Features: this doesn't 
//  make sense!

    $qryBanned   = 'SELECT CodeCategory FROM tblToolsCategories WHERE IdTool=' . $idTool . ' AND ';
    $qryBanned  .= 'CodeCategory <>"' . $selectedCodeCategory . '" AND Process="' . $process . '"';

    $rsBanned    = $db_conn->query($qryBanned);
    $nBanned   = $rsBanned->rowCount();
    
    for ($idx=0; $idx<$nBanned; $idx++) {
      $rowBanned = $rsBanned->fetch();
      $bannedValues .= $rowBanned["CodeCategory"] . "@";
    }

  }
  else
    $addWhere = "";
  
  echo "<input type=hidden name=CodeCategoriesBanned value='" . $bannedValues . "'>";

	$qryTool      = 'SELECT ' . $tblTools . '.' . $idToolField . $pastIdToolField . ', Tool, LicenseType, OperatingSystem, ';
	$qryTool     .= $tblToolsCategories . '.Process, Developer, Url, Description, Category, ';
	$qryTool     .= 'tblCategories.CodeCategory ';
  $qryTool     .= 'FROM ' . $tblTools . ', tblCategories, ' . $tblToolsCategories . ' WHERE ';
  $qryTool     .= $tblTools . '.' . $idToolField . '=' . $tblToolsCategories . '.' . $idToolField .' AND ';
  $qryTool     .= 'tblCategories.CodeCategory=' . $tblToolsCategories . '.CodeCategory AND ';
  $qryTool     .= $tblToolsCategories . '.Process="' . $process . '" AND ';
  $qryTool     .= 'tblCategories.Process = "' . $process . '" AND ';
  $qryTool     .= $tblTools . '.' . $idToolField . '=' . $idTool . $addWhere;
  $rsTool	      = $db_conn->query($qryTool);
  $rowTool		  = $rsTool->fetch();

// preparing the form fields for the updating
  echo "<table border=0 width=90% class=dftText cellspacing='10'>";
  echo "<tr class=dftText><td class=dftTextGrassetto>Type</td>";
  setJavascriptField("editorIdTool", $idTool);
  //echo "<input name=editorIdTool class=dftHidden value=" . $idTool . ">"; 
  if ($process == "AN")
  	echo '<td colspan=3><input text readonly name=editorToolType class=dftText value="Analysis"></td></tr>';
  else
  	echo '<td colspan=3><input text readonly name=editorToolType class=dftText value="Acquisition"></td></tr>';
  
  echo "<tr class=dftText><td class=dftTextGrassetto>Name</td>";
  echo '<td><input text name=editorToolName required placeholder="Tool name (required)" class=dftText size=40 value="' . $rowTool["Tool"] . '"></td>';
  echo '<td class=dftTextGrassetto>Description</td><td><textarea rows=3 cols=45 name=Description>' . $rowTool["Description"] . '</textarea></td></tr>';

  
  echo '<input type=hidden name=dbToolName value="' . $rowTool["Tool"] . '">';

  echo "<tr class='dftText'>";    
  showLicenseType($rowTool["LicenseType"], "editorLicense", "</td><td>");

  showOperatingSystem($rowTool["OperatingSystem"], "editorOs", "</td><td>");
  echo "</tr>";
  
  echo "<input name=editorCategory class=dftHidden value=''> "; 

  echo "<tr class=dftText><td class=dftTextGrassetto>Developer</td>";
  echo '<td><input text name=editorDeveloper placeholder="Company, organization, commnity, people that develop and mantain the tool";
  echo " class=dftText size=50 value="' . $rowTool["Developer"] . '"></td>';

  echo "<td class=dftTextGrassetto>Web site</td>";
  echo '<td><input text name=editorUrl size=50 required value="' . $rowTool["Url"] . '"></td></tr>';
 
  echo "<input type=hidden name=editorReportsValues>";
  echo "<tr class=dftText>";
  echo "<td class=dftTextGrassetto>Report Tests</td>";
  echo "<td><select name=editorReports size=4 onChange='ExtractTestUrl();'>";
  // query for extracting the Urls Test of the tool
  $qryReports   = 'SELECT * FROM ' . $tblToolsReports . ' WHERE ' . $idToolField . '=' . $idTool; 
  $rsReports    = $db_conn->query($qryReports);
  $nReports     = $rsReports->rowCount();
  for ($i=0; $i <$nReports; $i++) {
  	$rowReport = $rsReports->fetch();
  	echo '<option value="' . $rowReport["ReportUrl"] . " | " . $rowReport["NoteTest"] . '">';
  	echo $rowReport["ReportUrl"] . " | " . $rowReport["NoteTest"] . "</option>";
  }
  echo "</select></td>";
  echo "<td class=dftTextGrassetto>";
  echo "<br/>Url Test <br/>Note Test<br/>";
  echo "<a href=javascript:AddTestUrl() title='Add the Report Test to the current tool'>";
  echo "<img src=images/dfte.plus.png></a>&nbsp;&nbsp;&nbsp;";
  echo "<a href=javascript:SubtractTestUrl() title='Cancel the selected Report Test'>";
  echo "<img src=images/dfte.minus.png></a>";
  echo "</td>";
  echo "<td>";
  echo "<input type=text size=50 name=editorTestUrl placeholder='Test web site'><br/>";
  echo "<input type=text size = 50 name=editorTestNote placeholder='Note about the test'><br>&nbsp;";
  echo "</td>";
  echo "</tr>";
  

   // query for extracting the URLs test of the tool
  // query for extracting the Useful References of the tool
  $qryReferences   = 'SELECT * FROM ' . $tblUsefulReferences . ' WHERE ' . $idToolField . '=' . $idTool; 
  $rsReferences    = $db_conn->query($qryReferences);
  $nReferences     = $rsReferences->rowCount();
  echo "<input type=hidden name=editorReferencesValues>";
  echo "<tr class=dftText>";
  echo "<td class=dftTextGrassetto>Useful References</td>";
  echo "<td><select name=editorReferences size=4 onChange='ExtractReferences();'>";
  for ($i=0; $i <$nReferences; $i++) {
    $rowUR = $rsReferences->fetch();
    echo '<option value="' . $rowUR["ReferenceUrl"] . " | " . $rowUR["ReferenceNote"] . '">';
    echo $rowUR["ReferenceUrl"] . " | " . $rowUR["ReferenceNote"] . "</option>";
  }
  echo "</select></td>";
  echo "<td class=dftTextGrassetto>";
  echo "<br/>Reference Url<br/>Reference Note<br/>";
  echo "<a href=javascript:AddReferenceUrl() title='Add the Reference Url to the current tool'>";
  echo "<img src=images/dfte.plus.png></a>&nbsp;&nbsp;&nbsp;";
  echo "<a href=javascript:SubtractReferenceUrl() title='Cancel the selected Reference Url'>";
  echo "<img src=images/dfte.minus.png></a>";
  echo "</td>";
  echo "<td>";
  echo "<input type=text size=50 name=editorReferenceUrl placeholder='Reference web site'><br/>";
  echo "<input type=text size = 50 name=editorReferenceNote placeholder='Note about the Refernce'><br>&nbsp;";
  echo "</td>";
  echo "</tr>";

  echo '<input name=editorDbCategory class=dftHidden value="' . $rowTool["Category"] . '"">'; 
  echo '<input name=editorDbCodeCategory class=dftHidden value="' . $rowTool["CodeCategory"] . '"">'; 
  
  echo "<tr class='dftText'>";
  
  if ($process == "AN")
  	showCategory($arrayCategoriesAN, $rowTool["CodeCategory"], "editorCodeCategory", "editorCheckCategory", "AN", "</td><td>");
  else
		showCategory($arrayCategoriesAC, $rowTool["CodeCategory"], "editorCodeCategory", "editorCheckCategory", "AC", "</td><td>");  		

// query for extracting the Useful References of the tool. In updating the idTool to tale into consideration is the 
// pastIdTool stored in tblEditorTools.IdTool not in tblEditorTools.IdEditorTool  
  if ($pastIdToolField == "")
    $qryEvaluations   = "SELECT * FROM tblToolsEvaluations WHERE IdTool=" . $idTool . " AND EvaluationUserName='";
  else
    $qryEvaluations   = "SELECT * FROM tblToolsEvaluations WHERE IdTool=" . $rowTool["IdTool"] . " AND EvaluationUserName='";

  $qryEvaluations  .= $_SESSION["user_name"] . "' ";

  $rsEvaluations    = $db_conn->query($qryEvaluations);
  $nEvaluation      = $rsEvaluations->rowCount();
  $satisfaction     = -1;
  $frequencyUse     = -1;
  if ($nEvaluation  > 0) {
    $rowEvaluation    = $rsEvaluations->fetch();
    $satisfaction     = $rowEvaluation["Satisfaction"];
    $frequencyUse     = $rowEvaluation["FrequencyUse"];
  }    
  
  echo "<td class=dftTextGrassetto>Evaluation<br/><select name=toolEvaluation placeholder='Tool Evaluation'>";
  for($i=0; $i<TOOL_EVALUATION_GRADE_MAX; $i++) {
    if ($satisfaction == $i)
      echo "<option selected value=$i>" .  constant('TOOL_EVALUATION_GRADE_' . $i) . "</option>";
    else 
      echo "<option value=$i>" .  constant('TOOL_EVALUATION_GRADE_' . $i) . "</option>";
  }
  

  echo "</select></td>";
  echo "<td class=dftTextGrassetto>Frequency Use<br/><select name=toolUse placeholder='Tool Frequency Use'>";
  for($i=0; $i<TOOL_USE_MAX; $i++) {
    if ($frequencyUse == $i)
      echo "<option selected value=$i>" .  constant('TOOL_USE_' . $i) . "</option>";
    else
      echo "<option value=$i>" .  constant('TOOL_USE_' . $i) . "</option>";        
  }

  echo "</select></td>";
  echo "</tr>";


  echo "</table>";
  echo "<br/><br/>";
  // add the select hidden for managing Features panel in the editor DIV
   
/*
*---  only in the cases beforeUpdating and beforeInsert it will show the related buttons, in all the other cases 
*---  afterUpdating and afterInsert no buttons are shown, it is necessary for viewing the new data inserted or updated
*/  
  switch($status) {
    case "beforeUpdating": // it has been requested a modification of an esisting Tool from the editor DIV
  	echo '<span class="button-wrap">';
  	echo '<a href="javascript:UpdateTool()" class="button button-pill ">Update</a>';
  	echo "</span>";
    break;

    case "beforeInsert": 		// it has been requested a New Tool from the search DIV
  		echo '<span class="button-wrap">';
  		echo '<a href="javascript:CommitNewTool()" class="button button-pill ">Confirm New Tool</a>';
  		echo "</span>";
      break;

  	case  "afterUpdating":
      echo "<p class=dftCommit>Updating completed!</p>";
      break;

    case  "afterInsert":
      echo "<p class=dftCommit>Insert completed!</p>";
      break;
  }
  echo "<br/>";
  echo '<div id="editorContainer"><p class="dftTextItalic" align="center">Features Panel</p>';
  echo '<div id="editorBox">Box con testo</div>';
  echo '</div>';
  echo "<script> editorCheckCategory();</script>";
  echo "<script> CheckCategory();</script>";
  echo "<table>";  
  //echo "<tr class=dftPulsanti><td>&nbsp;</td>";
  //echo "<td class=dftPulsanti><input type=button class=dftUpdateButton onClick=UpdateTool(); value='Update tool'></td></tr>";
  echo "<td>"; 
  echo "</td></tr>";
  echo "</table>";

// extracts all Features related to the selected Category and next sets on/off the checkbox of the corresponding Tool Values stored into DB
  $qryFeaturesCategory 	= 'SELECT IdFeature FROM tblFeatures WHERE CodeCategory="' . $rowTool["CodeCategory"] . '" ORDER BY NumberFeature';
  $rsFeaturesCategory	  = $db_conn->query($qryFeaturesCategory);
  $nFeaturesCategory		= $rsFeaturesCategory->rowCount();
  
 	for ($i=0; $i<$nFeaturesCategory; $i++) {
// extracts the number of Values of the Feature
 		$rowFeature = $rsFeaturesCategory->fetch();
  	$qryFeatureValues = 'SELECT Count(IdFeature) AS TotValues FROM tblFeaturesValues WHERE IdFeature=' . $rowFeature["IdFeature"];
  	$rsFeatureValues  = $db_conn->query($qryFeatureValues);
  	$rowTotal		  		= $rsFeatureValues->fetch();
  	$maxFeatureValues = $rowTotal["TotValues"];

// extracts all the values of the Feature related to the current IdTool
  	$qryToolFeatures  = "SELECT ValueFeature FROM $tblToolsFeatures WHERE $idToolField =" . $rowTool[$idToolField] . " AND ";
  	$qryToolFeatures .= 'IdFeature=' . $rowFeature["IdFeature"] . ' ORDER BY ValueFeature';
    //echo "qryToolFeatures=$qryToolFeatures <br/>";
  	$rsToolFeatures   = $db_conn->query($qryToolFeatures);
  	$nToolFeatures		= $rsToolFeatures->rowCount();

  	$arrayToolValues = array();
  	for ($j=0; $j<$nToolFeatures; $j++) {
  		$rowToolValue = $rsToolFeatures->fetch();
  		$arrayToolValues[$j] = $rowToolValue["ValueFeature"];
  	}
    
  	echo "<script>ToolValuesCheckBoxesOnOff('" . $rowFeature["IdFeature"] . "','" . $maxFeatureValues . "'," . json_encode($arrayToolValues) . ");</script>";
  }
}  

/*
*---	function UpdateTool(): writes the new values in tblEditorTools and related tables
*
*---	Input: $idTool of the tool under updating
*            $operation: U for Update, I for Insert
*/
function UpdateTool($idTool, $operation) {
	global $process, $db_conn;

	date_default_timezone_set('Europe/Rome');

  $db_conn->beginTransaction();

	try { 
		$qryUpdate  = "INSERT INTO tblEditorTools (IdTool, Tool, LicenseType, OperatingSystem, Developer, Url, TestReport, ";
    $qryUpdate .= "Process, Description, EditingUserName, EditingDate, EditingTime, EditingType, EditingStatus) VALUES (";
    $qryUpdate .= ":IdTool, :Tool, :LicenseType, :OperatingSystem, :Developer, ";
		$qryUpdate .= ":Url, :TestReport, :Process, :Description, :EditingUserName, :EditingDate, :EditingTime, :EditingType, :EditingStatus)";

		$stmt = $db_conn->prepare($qryUpdate);
		$stmt->bindParam(':IdTool', $idTool, PDO::PARAM_INT);
    $p_toolName = filter_var($_POST["editorToolName"], FILTER_SANITIZE_STRING);
    
    
		$stmt->bindParam(':Tool', $p_toolName, PDO::PARAM_STR);
		$stmt->bindParam(':LicenseType', $_POST["editorLicense"], PDO::PARAM_STR);
		$stmt->bindParam(':OperatingSystem', $_POST["editorOs"], PDO::PARAM_STR);
    
    $p_developer = filter_var($_POST["editorDeveloper"], FILTER_SANITIZE_STRING);

    $stmt->bindParam(':Developer', $p_developer, PDO::PARAM_STR);

    /*if(filter_var($_POST["editorUrl"], FILTER_VALIDATE_URL) === False) { (it needs http:// !!!)
      throw new Exception('URL invalid!');
      exit;
    }*/

    $p_url = filter_var($_POST["editorUrl"], FILTER_SANITIZE_STRING);
		$stmt->bindParam(':Url', $p_url, PDO::PARAM_STR);

		if ($_POST["editorReportsValues"] == "")
			$stmt->bindValue(':TestReport', "N");
		else
			$stmt->bindValue(':TestReport', 'S');			


		$stmt->bindParam(':Process', $process, PDO::PARAM_STR);
    try {
      $stmt->bindParam(':Description', $_POST["Description"], PDO::PARAM_STR);
    }
    catch (Exception $e) {
      $error = $e->getMessage();
      echo "<p class=dftError>Line: " . __LINE__ . ": Error: " . $error . "</p>";
      $db_conn->rollback();
      exit; 
    }  

		$stmt->bindParam(':EditingUserName', $_SESSION['user_name'], PDO::PARAM_STR);
		$stmt->bindValue(':EditingDate', date("Ymd"), PDO::PARAM_STR);
		$stmt->bindValue(':EditingTime', date("Hi"), PDO::PARAM_STR);

		$stmt->bindParam(':EditingType', $operation);		// Insert or Update
		
		$stmt->bindValue(':EditingStatus', "Pending");	// all the updatings need to be approved, the initial state is alway pending 
		$stmt->execute();
  }
  catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
  } 
		
	try {
  	$idRecord = $db_conn->lastInsertId();

		$qryUpdate  = "INSERT INTO tblEditorToolsCategories (IdEditorTool, pastCodeCategory, CodeCategory, Process) VALUES ";
		$qryUpdate .= "(:IdEditorTool, :pastCodeCategory, :CodeCategory, :Process)";
		$stmt = $db_conn->prepare($qryUpdate);
		$stmt->bindParam(':IdEditorTool', $idRecord, PDO::PARAM_INT);
    $stmt->bindParam(':pastCodeCategory', $_POST["editorDbCodeCategory"], PDO::PARAM_STR);
		$stmt->bindParam(':CodeCategory', $_POST["editorCodeCategory"], PDO::PARAM_STR);
		$stmt->bindParam(':Process', $process, PDO::PARAM_STR);
		$stmt->execute();
 	}
 	catch (Exception $e) {
  	$error = $e->getMessage();
    echo "<p class=dftError>Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
 	}	

/*    
* --- catch Values/Features for writing data into tblEditorToolsFeatures
*/    
//  foreach($_POST as $k=> $v)
//  	echo $k . "=" . $v . "<br/>";

  $qryFeatures = 'SELECT IdFeature FROM tblFeatures WHERE CodeCategory=:CodeCategory AND Visible="S" ORDER BY NumberFeature';
//fwrite($debugFile, $qtyFeatures . "\n");
	$stmtF = $db_conn->prepare($qryFeatures);
	$stmtF->bindParam(':CodeCategory', $_POST["editorCodeCategory"], PDO::PARAM_STR);
	$stmtF->execute();
	$nFeatures	 = $stmtF->rowCount();        
	$error = "";
	if ($nFeatures == 0)   // codeCategory doesn't have feature or category wasn't selected, in this case there is no filter on FeaturesValues
    $valueFinalFilter = "";
  else {
    $valueFinalFilter = "";
    $valueFilter = "";
    for ($i=0; $i<$nFeatures; $i++) {
      $rowFeature = $stmtF->fetch(); 
			$qryValues = "SELECT Value FROM tblFeaturesValues WHERE IdFeature =" . $rowFeature["IdFeature"] . " ORDER BY Value";
      $rsValues = $db_conn->query($qryValues);
      $nValues = $rsValues->rowCount();
      for ($j=0; $j < $nValues; $j++) {   // loop for managing the feature values that have been selected/checked
      	$varPost = "editor" . (string)$rowFeature["IdFeature"] . "_" . $j;
      	$rowValue = $rsValues->fetch();
        if (isset($_POST[$varPost])) { 
        	try {
        		$qryUpdate  = "INSERT INTO tblEditorToolsFeatures (IdEditorTool, IdFeature, CodeCategory, ValueFeature) VALUES ";
						$qryUpdate .= "(:IdEditorTool, :IdFeature, :CodeCategory, :ValueFeature)";
						$stmtU = $db_conn->prepare($qryUpdate);
						$stmtU->bindParam(':IdEditorTool', $idRecord, PDO::PARAM_INT);
						$stmtU->bindParam(':IdFeature', $rowFeature["IdFeature"], PDO::PARAM_INT);
						$stmtU->bindParam(':CodeCategory', $_POST["editorCodeCategory"], PDO::PARAM_STR);
						$stmtU->bindParam(':ValueFeature', $rowValue["Value"], PDO::PARAM_INT);
						$stmtU->execute(); 
					}
					catch (PDOException $e) {
    				$error = $e->getMessage();
    				echo "<p class=dftError>Error: " . $error . "</p>";
            $db_conn->rollback();
            exit; 
 					}	
        }
      }                    
    }
  } 

// update tlEditorToolsReports
	$testReports = explode("@", $_POST["editorReportsValues"]);
	foreach($testReports as $testReport) {
		try {
			$values = explode("|", $testReport);
			$url = str_replace(" ", "", $values[0]);
				
			if ($url == "")
				continue;

			$qryUpdate  = "INSERT INTO tblEditorToolsReports (IdEditorTool, ReportUrl, NoteTest, Process) VALUES ";
			$qryUpdate .= "(:IdEditorTool, :ReportUrl, :NoteTest, :Process)";
			$stmt = $db_conn->prepare($qryUpdate);
			$stmt->bindParam(':IdEditorTool', $idRecord, PDO::PARAM_INT);
			$stmt->bindParam(':ReportUrl', $url, PDO::PARAM_STR);
			$stmt->bindParam(':NoteTest', $values[1], PDO::PARAM_STR);
			$stmt->bindParam(':Process', $process, PDO::PARAM_STR);
			$stmt->execute();
		}
		catch (PDOException $e) {
      $error = $e->getMessage();
    	echo "<p class=dftError>Error: " . $error . "</p>"; 
      $db_conn->rollback();
      exit;
 		}	
	}

  // update tblEditorUsefulReferences
  $references = explode("@", $_POST["editorReferencesValues"]);
  foreach($references as $reference) {
    try {
      $values = explode("|", $reference);
      $url = str_replace(" ", "", $values[0]);
        
      if ($url == "")
        continue;

      $qryUpdate  = "INSERT INTO tblEditorToolsUsefulReferences (IdEditorTool, ReferenceUrl, ReferenceNote, Process) VALUES ";
      $qryUpdate .= "(:IdEditorTool, :ReferenceUrl, :ReferenceNote, :Process)";
      $stmt = $db_conn->prepare($qryUpdate);
      $stmt->bindParam(':IdEditorTool', $idRecord, PDO::PARAM_INT);
      $stmt->bindParam(':ReferenceUrl', $url, PDO::PARAM_STR);
      $stmt->bindParam(':ReferenceNote', $values[1], PDO::PARAM_STR);
      $stmt->bindParam(':Process', $process, PDO::PARAM_STR);
      $stmt->execute();
    }
    catch (PDOException $e) {
      $error = $e->getMessage();
      echo "<p class=dftError>Error: " . $error . "</p>"; 
      $db_conn->rollback();
      exit;
    } 
  }

// Insert/Update tblToolsEvaluations
  try {
    if ($idTool == 0)  {   // new tool added
      $qryEvaluation    = "INSERT INTO tblToolsEvaluations (IdTool, Satisfaction, FrequencyUse, Process, EvaluationUserName, ";
      $qryEvaluation   .= "EvaluationDate, EvaluationTime) VALUES (:IdTool, :Satisfaction, :FrequencyUse, :Process, ";
      $qryEvaluation   .= ":EvaluationUserName,:EvaluationDate, :EvaluationTime)";
      $tmpQry  = "INSERT INTO tblToolsEvaluations (IdTool, Satisfaction, FrequencyUse, Process, EvaluationUserName, ";
      $tmpQry .="EvaluationDate, EvaluationTime) VALUES (" . $idRecord . ", " . $_POST["toolEvaluation"] . ", ";
      $tmpQry .= $_POST["toolUse"] . ", '" . $process . "', '" . $_SESSION['user_name'] . "', '" . date("Ymd") . "', '" . date("Hi") . "')";
      $stmt = $db_conn->prepare($qryEvaluation);
      $stmt->bindParam(':IdTool', $idRecord, PDO::PARAM_INT);
      $stmt->bindParam(':Satisfaction', $_POST["toolEvaluation"], PDO::PARAM_INT);
      $stmt->bindParam(':FrequencyUse', $_POST["toolUse"], PDO::PARAM_INT);
      $stmt->bindParam(':Process', $process, PDO::PARAM_STR);
      $stmt->bindParam(':EvaluationUserName', $_SESSION['user_name'], PDO::PARAM_STR);
      $stmt->bindValue(':EvaluationDate', date("Ymd"), PDO::PARAM_STR);
      $stmt->bindValue(':EvaluationTime', date("Hi"), PDO::PARAM_STR);
      $stmt->execute(); 
    }
    else {
      $qryEvaluation = "SELECT * FROM tblToolsEvaluations WHERE IdTool=:IdTool AND EvaluationUserName=:EvaluationUserName";
      $stmtEv = $db_conn->prepare($qryEvaluation);
      $stmtEv->bindParam(':IdTool', $idTool, PDO::PARAM_INT);
      $stmtEv->bindParam(':EvaluationUserName', $_SESSION['user_name'], PDO::PARAM_STR);
      $stmtEv->execute();
      $nEvaluations = $stmtEv->rowCount();
      if ($nEvaluations > 0) {
        $rowEvaluation  = $stmtEv->fetch();
        $qryEvaluation  = "UPDATE tblToolsEvaluations SET Satisfaction=:Satisfaction, FrequencyUse=:FrequencyUse WHERE ";
        $qryEvaluation .= "IdToolEvaluation=:IdToolEvaluation";
        $stmt = $db_conn->prepare($qryEvaluation);
        $stmt->bindParam(':Satisfaction', $_POST["toolEvaluation"], PDO::PARAM_INT);
        $stmt->bindParam(':FrequencyUse', $_POST["toolUse"], PDO::PARAM_INT);
        $stmt->bindParam(':IdToolEvaluation', $rowEvaluation['IdToolEvaluation'], PDO::PARAM_INT);
        $stmt->execute();            
      }
      else {
        $qryEvaluation    = "INSERT INTO tblToolsEvaluations (IdTool, Satisfaction, FrequencyUse, Process, EvaluationUserName, ";
        $qryEvaluation   .= "EvaluationDate, EvaluationTime) VALUES (:IdTool, :Satisfaction, :FrequencyUse, :Process, ";
        $qryEvaluation   .= ":EvaluationUserName,:EvaluationDate, :EvaluationTime)";
        $stmt = $db_conn->prepare($qryEvaluation);
        $stmt->bindParam(':IdTool', $idTool, PDO::PARAM_INT);
        $stmt->bindParam(':Satisfaction', $_POST["toolEvaluation"], PDO::PARAM_INT);
        $stmt->bindParam(':FrequencyUse', $_POST["toolUse"], PDO::PARAM_INT);
        $stmt->bindParam(':Process', $process, PDO::PARAM_STR);
        $stmt->bindParam(':EvaluationUserName', $_SESSION['user_name'], PDO::PARAM_STR);
        $stmt->bindValue(':EvaluationDate', date("Ymd"), PDO::PARAM_STR);
        $stmt->bindValue(':EvaluationTime', date("Hi"), PDO::PARAM_STR);
        $stmt->execute(); 
      }        
    }
  }
  catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
  } 

  $db_conn->commit();

  //$mail = new PHPMailer;
  //sendEmailCommittee($mail, $_POST["dbToolName"], $_SESSION["user_name"], $_SESSION["user_email"], $operation);
  // sending email with an external program for avoiding to keep waiting the script until the end of the email sending
  // The $cmd execution in the background without PHP waiting for it to finish

  $currentFolder = getcwd();
  //$cmd  = $currentFolder . "/dfte.send.email.ssl.py audit ciccio ciccio@ciccio.it U";
  $cmd  = $currentFolder  . "/tools/dfte.send.mail.ssl.py ";

  if ($operation == "I")
    $cmd .= $p_toolName . " "; 
  else 
    $cmd .= '"' . $_POST["dbToolName"] . '" ';

  $cmd .= $_SESSION["user_name"] . " " . $_SESSION["user_email"] . " " . $operation . " ";

// extracts all Administrator users and add all to the destination address
  $rsAdmins   = $db_conn->query('SELECT user_email FROM tblUsers WHERE user_role="admin" ');
  $nAdmins    = $rsAdmins->rowCount();
  $listAdmins = "";
  for ($k=0; $k<$nAdmins; $k++) {
    $rowAdmin = $rsAdmins->fetch();
    $cmdEmail = $cmd . $rowAdmin["user_email"];
    exec($cmdEmail . " > /dev/null &");
  }

  echo "<input type=hidden name=editorIdToolCommitted value='" . $idRecord . "'>";
  switch($operation) {
    case "U":
      echo "<script>window.alert('" . "Update complete!');</script>";                            
      echo "<script>CommitUpdatingTool();</script>";
      break;

    case "I":
      echo "<script>window.alert('" . "Insert complete!');</script>";                            
      echo "<script>AddedTool();</script>";
      break;

    case "":
      echo "<p class=dftError>wrong operation: $operation </p>";
  }
}	

/*
*---- (): it sends an email to the Catalogue Commmittee members
*
*     $toolName: the tool name juts updated/inserted
*     $user: the user name who has proposed the modification
*     $operation: it may assume U for Updating or A for Adding
*
*/
function sendEmailCommittee($mail, $toolName, $user, $userEmail, $operation) {
  
  //writeLog("sendEmailCommittee, toolName=" . $toolName . ", user=" . $user . ", operation=" . $operation);
// Set mailer to use SMTP
  $mail->IsSMTP();
//useful for debugging, shows full SMTP errors
//$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
// Enable SMTP authentication
  $mail->SMTPAuth = EMAIL_SMTP_AUTH;
// Enable encryption, usually SSL/TLS
  if (defined(EMAIL_SMTP_ENCRYPTION)) 
    $mail->SMTPSecure = EMAIL_SMTP_ENCRYPTION;

// Specify host server
  $mail->Host = EMAIL_SMTP_HOST;
  $mail->Username = EMAIL_SMTP_USERNAME;
  $mail->Password = EMAIL_SMTP_PASSWORD;
  $mail->Port = EMAIL_SMTP_PORT;

  $mail->From = EMAIL_EDITOR_FROM;
  $mail->FromName = EMAIL_EDITOR_FROM_NAME;

// $mail->AddAddress($user_email);
  $mail->AddAddress("fabrizio.turchi@ittig.cnr.it");
  
  $emailEditor  = "The following tool \n\n" . $toolName;
  $emailEditor .= "\n\nhas just been ";
  
  if ($operation == "U") {
    $mail->Subject = EMAIL_EDITOR_UPDATE_SUBJECT;
    $emailEditor  .= "updated by \n\n";
  }
  else {
    $mail->Subject = EMAIL_EDITOR_INSERT_SUBJECT;
    $emailEditor  .="inserted by \n\n" ;
  }

  $emailEditor .= $user . " ($userEmail) \n\n Regards";

// the link to your dfte.register.php, please set this value in config/email_verification.php
  $mail->Body = $emailEditor;

  if(!$mail->Send()) {
    $this->errors[] = MESSAGE_VERIFICATION_MAIL_NOT_SENT . $mail->ErrorInfo;
    return false;
  } 
  else 
    return true;
}

/*
*---- sendEmailApproval(): it sends an email to the Editor User for noticing of the Approval/Refuse from Catalogue Commmittee
*
*     $mail: the object for managing the email
*     $toolName: the tool name juts updated/inserted
*     $user: the user name who has proposed the modification
*     $operation: it may assume U for Updating or A for Adding
*
*/
function sendEmailApproval($mail, $toolName, $user, $operation) {

  //writeLog("sendEmailApproval, toolName=" . $toolName . ", user=" . $user . ",  operation=" . $operation);
// Set mailer to use SMTP
  $mail->IsSMTP();
//useful for debugging, shows full SMTP errors
//$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
// Enable SMTP authentication
  $mail->SMTPAuth = EMAIL_SMTP_AUTH;
// Enable encryption, usually SSL/TLS
  if (defined(EMAIL_SMTP_ENCRYPTION)) 
    $mail->SMTPSecure = EMAIL_SMTP_ENCRYPTION;

// Specify host server
  $mail->Host     = EMAIL_SMTP_HOST;
  $mail->Username = EMAIL_SMTP_USERNAME;
  $mail->Password = EMAIL_SMTP_PASSWORD;
  $mail->Port     = EMAIL_SMTP_PORT;

  $mail->From     = EMAIL_EDITOR_FROM;
  $mail->FromName = EMAIL_EDITOR_FROM_NAME;

// $mail->AddAddress($user_email);
  $mail->AddAddress($user);

  
  $emailEditor  = "The following tool \n\n" . $toolName;
  $emailEditor .= "\n\nhas just been approved, from now on it is available on the online Forensics Catalogue \n\t ";
  $emailEditor .="wp4.evidenceproject.eu \n\n Regards";

  if ($operation == "U")
    $mail->Subject = EMAIL_APPROVAL_UPDATE_SUBJECT;
  else
    $mail->Subject = EMAIL_APPROVAL_INSERT_SUBJECT;

// the link to your dfte.register.php, please set this value in config/email_verification.php
  $mail->Body = $emailEditor;



  if(!$mail->Send()) {
    $this->errors[] = MESSAGE_VERIFICATION_MAIL_NOT_SENT . $mail->ErrorInfo;
    return false;
  } 
  else 
    return true;
    
}

/*
*---- ApprovalShow(): it shows the list of all pending proposal for updating/insert tools to be approved
*/

function ApprovalShow() {
  global $db_conn, $codesLeavesNoFeatures;

  $qryApproval   = "SELECT tblEditorTools.*, tblCategories.CodeCategory, Category, pastCodeCategory FROM tblEditorTools, ";
  $qryApproval  .= "tblEditorToolsCategories, tblCategories ";
  $qryApproval  .= "WHERE tblEditorTools.IdEditorTool = tblEditorToolsCategories.IdEditorTool AND ";
  $qryApproval  .= "tblEditorToolsCategories.CodeCategory = tblCategories.CodeCategory AND ";
  $qryApproval  .= "EditingStatus='Pending' ORDER BY EditingDate DESC";

  $rsApprovals    = $db_conn->query($qryApproval);
  $nApprovals   = $rsApprovals->rowCount(); 

  if ($nApprovals > 0) {
    echo "<table border=0 width=95%><tr><td align=right>";
    echo '<span class="button-wrap">';
    echo '<a href="javascript:ApprovalAllTools()" title="Approve all updating/inserted tools" class="button button-pill ">';
    echo 'Approve All!</a></span>';
    echo "</a></td></tr></table>";
    echo "<div id=tblTools>";
    echo '<table  border=1 width="95%">';
    echo "<tr class=dftTextGrassetto align=center><td width='20%'>Tool&nbsp;</td>";
    echo "<td width='15%'>Category&nbsp</td>";
    echo "<td width='10%'>License&nbsp</td>";
    echo "<td width='10%'>O.S.&nbsp;</td>";
    echo "<td width='20%'>Features / Values</td>"; 
    echo "<td width='10%'>User/Date</td>"; 
    echo "<td width='5%'>Approval</td></tr>"; 
  } 
  else {
    echo "No updating/insert tools in pending state";
    return;
  }

  for ($i=0; $i<$nApprovals; $i++) {
    $rowTool = $rsApprovals->fetch();
    prepareApprovalRow($rowTool);   
  }    

  echo "</table></div>";
  echo "<input type=hidden name=idApprovalTool value=''>";
}

function ApprovalAllTools() {
  global $db_conn;

  $qryIds  = "SELECT * FROM tblEditorTools WHERE EditingStatus = 'Pending' ORDER BY EditingDate";
  $stmtIds = $db_conn->query($qryIds);
  $nIds    = $stmtIds->rowCount();
  $msgAlert = False;
  for ($i=0; $i< $nIds; $i++) {
    $rowId = $stmtIds->fetch();
    $idEditorTool = $rowId["IdEditorTool"];    
    ApprovalSingleTool($idEditorTool, $msgAlert);    
  }       
  windowAlert("All Tools approved and updated/inserted in the Tools Catalogue, thanks!");
//  call Approva() to show the rest of the tools in pending status for approval 
  echo "<script>Approval();</script>";

}

/*
*---- ApprovalSingleTool(): it approves a single tool and update the content of the Tools Catalogue
*
*** UPDATE tool case
*--- 1) UPDATE tblEditorTools: ApprovalDate, ApprovalTime, ApprovalUser are set and EditingSttaus=approved
*--- 2) IF the updating tool has tblTools.Provenance="First import", it means that it was moved into the Catalogue 
*       without using the Editor, so that Tool must be saved/copies into tblEditorTools, tblEditorToolsCategories and
*       tblEditorToolsFeatures tables in the following way (a), b) and c) are carried out only in this case):
*       a) INSERT INTO tblEditTools [insertEditorTools()]
*       b) INSERT INTO tblEditToolsCategories [insertEditorToolsCategories()]
*       c) INSERT INTO tblEditToolsFeatures [insertEditorToolsFeatures()]
*    3) 
*       a) DELETE FROM tblToolsCategories and DELETE FROM tblToolsFeatures [deleteCategoriesFeatures()]
*       b) DELETE tblToolsReports with Idtool=tblEditorTools.IdTool
*          IF tblEditorTools.TestReport='S' INSERT INTO tblToolsReports data from tblEditorToolsReports
*    4) UPDATE tblTools with data from tblEditorTools [updateRecordTools()]
*    5) INSERT INTO tblToolsCategories and INSERT INO tblToolsFeatures with data from tblEditorToolsCategories and
*       tblEditorToolsFeatures respectively [insertCategoriesFeatures()]
*
*** NEW tool case
*--- 1) UPDATE tblEditorTools: ApprovalDate, ApprovalTime, ApprovalUser are set and EditingSttaus=approved
*    2) INSERT INTO tblTools with data from tblEditorTools [insertRecordTools()]
*
*/
function ApprovalSingleTool($idEditorTool, $msgAlert) {
  global $db_conn;

  date_default_timezone_set('Europe/Rome');

  $db_conn->beginTransaction();

  try { 
// update tblEditorTools: set Approval fields: ApprovalUser, ApprovalDate, ApprovalTime and EditingStatus=Approved
    $qryUpdate  = "UPDATE tblEditorTools SET  ApprovalDate=:ApprovalDate, ApprovalTime=:ApprovalTime, ";
    $qryUpdate .= "ApprovalUser=:ApprovalUser, EditingStatus=:EditingStatus WHERE IdEditorTool=:IdEditorTool";

    $stmt = $db_conn->prepare($qryUpdate);

    $stmt->bindValue(':ApprovalDate', date("Ymd"), PDO::PARAM_STR);
    $stmt->bindValue(':ApprovalTime', date("Hi"), PDO::PARAM_STR);
    $stmt->bindParam(':ApprovalUser', $_SESSION['user_name'], PDO::PARAM_STR);
    $stmt->bindParam(':IdEditorTool', $idEditorTool, PDO::PARAM_INT);
    $stmt->bindValue(':EditingStatus', "Approved", PDO::PARAM_STR);
    $stmt->execute();

    $qrypastIdTool  = "SELECT tblEditorTools.*, pastCodeCategory, CodeCategory  ";
    $qrypastIdTool .= "FROM tblEditorTools, tblEditorToolsCategories WHERE ";
    $qrypastIdTool .= "tblEditorTools.IdEditorTool=tblEditorToolsCategories.IdEditorTool AND ";
    $qrypastIdTool .= "tblEditorTools.IdEditorTool=:IdEditorTool";
    $stmt = $db_conn->prepare($qrypastIdTool);
    $stmt->bindParam(':IdEditorTool', $idEditorTool, PDO::PARAM_INT);
    $stmt->execute();
    $rowEditorTool = $stmt->fetch();
    $pastIdTool           = $rowEditorTool["IdTool"];
    $pastCodeCategory     = $rowEditorTool["pastCodeCategory"];
    $editorCodeCategory   = $rowEditorTool["CodeCategory"];
  }
  catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
  } 

  switch($rowEditorTool["EditingType"]) {
    
    case "I":       // Insert new tool
    //deleteReports($pastIdTool, $rowEditorTool["Process"]);
    insertRecordTools($rowEditorTool);
    windowAlert("Tool approved and inserted in the Tools Catalogue, thanks!");
    break;

    case "U":       // Update existing tool

// MOVE pastIdTool from tblTools into tblEditorTools only if tblTools.Provenance="First import"
//
// 1) read all data from Tools tables (Tols, ToolsCategories, ToolsFeatures, ToolsReports, ToolUsefulReferences) and
// write into the corresponding EditorTools tables
// 2) delete from tblToolsCategories, tblToolsFeatures, tblToolsReports, tblToolsUsefulReferences

    $qryTools = "SELECT * FROM tblTools WHERE IdTool=:IdTool";
    $stmtOld = $db_conn->prepare($qryTools);
    $stmtOld->bindParam(':IdTool', $pastIdTool, PDO::PARAM_INT);
    $stmtOld->execute();
    $rowTool = $stmtOld->fetch();

// the copy of data tool in tblTools is necessary only if the tool has been imported in the Catalgoue (tblTools) for the first time    
    if ($rowTool["Provenance"] == "First import") { 

/*
---   the "First import" records are tools imported in the Catalogue without the use of the Editor. For those records it is necessary
---   to mantain a trace/history in the tblEditorTools, tblEditorToolsCategories and tblEditorToolsFeatures tables.
---   insertEditorTools() inserts a record in tblEditorTools from tblTools
---   insertEditorToolsCategories() inserts a record in tblEditorToolsCategories from tblEditorToolsCategories
---   insertEditorToolsFeatures() inserts a record in tblEditorToolsfeatures from tblEditorToolsFeatures
*/

      $lastId = insertEditorTools($rowTool); 
      insertEditorToolsCategories($lastId, $pastIdTool, $pastCodeCategory, $rowTool["Process"]);
      insertEditorToolsFeatures($lastId, $pastIdTool, $pastCodeCategory, $rowTool["Process"]);
    } 

    deleteCategoriesFeatures($pastIdTool, $pastCodeCategory);
    deleteReports($pastIdTool, $rowEditorTool["Process"]);
    deleteReferences($pastIdTool, $rowEditorTool["Process"]);

//  UPDATE record tblTools: UPDATE tblTools from tblEditorTools, and SET Provenance=Update", DataInsert=current data 
//  INSERT tblToolsCategories and tblToolsFeatues  from tblEditorToolsCategories and tblEditorToolsFeatures into
//  tblTools  
    updateRecordTools($rowEditorTool);
    insertCategoriesFeatures($rowEditorTool["IdEditorTool"], $editorCodeCategory, $pastIdTool, $rowEditorTool["Process"] );
    if ($rowEditorTool["TestReport"] == "S")
      insertReports($rowEditorTool["IdEditorTool"], $pastIdTool, $rowEditorTool["Process"]);

    insertReferences($rowEditorTool["IdEditorTool"], $pastIdTool);
    
    if ($msgAlert)
      windowAlert("Tool approved and updated in the Tools Catalogue, thanks!");

    break;
  }      

  $db_conn->commit();


// retrieve email EditingUserName: it could be stored in the tblEditorTools table, 
// but the user can change his/her own email address

  $qryUser = 'SELECT user_email FROM tblUsers WHERE user_name=:user_name';
  $stmt = $db_conn->prepare($qryUser);
  $stmt->bindParam(':user_name', $rowEditorTool["EditingUserName"], PDO::PARAM_STR);
  $stmt->execute();
  $rowUser   = $stmt->fetch();    
  $userEmail = $rowUser["user_email"];

// send email message to the user for noticing the approval of his/her modification
  $currentFolder = getcwd();
  $cmd  = $currentFolder  . "/tools/dfte.send.mail.ssl.approval.py ";
  $cmd .= '"' . $rowEditorTool["Tool"] . '" ';
  $cmd .= $rowEditorTool["EditingUserName"] . " " . $userEmail;
  exec($cmd . " > /dev/null &");
 

  //$mail = new PHPMailer;
  //sendEmailApproval($mail, $rowEditorTool["Tool"], $userEmail, $rowEditorTool["EditingType"]);
  //$mail = null;
  
//  call Approva() to show the rest of the tools in pending status for approval oly in Single Approval case 
  if ($msgAlert)
    echo "<script>Approval();</script>"; 
}

function deleteCategoriesFeatures($pastIdTool, $pastCodeCategory) {
  global $db_conn;

  $qryDeleteCategory = "DELETE FROM tblToolsCategories WHERE IdTool=:IdTool AND CodeCategory=:CodeCategory";
  $stmt = $db_conn->prepare($qryDeleteCategory);
  $stmt->bindParam(':IdTool', $pastIdTool, PDO::PARAM_INT);
  $stmt->bindParam(':CodeCategory', $pastCodeCategory, PDO::PARAM_INT);
  try {
    $stmt->execute();
    $qryDeleteFeatures = "DELETE FROM tblToolsFeatures WHERE IdTool=:IdTool AND CodeCategory=:CodeCategory";
    $stmt = $db_conn->prepare($qryDeleteFeatures);
    $stmt->bindParam(':IdTool', $pastIdTool, PDO::PARAM_INT);
    $stmt->bindParam(':CodeCategory', $pastCodeCategory, PDO::PARAM_INT);
    $stmt->execute();
  }
  catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Line: " . __LINE__ . ": Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
  }  
}

function deleteReports($pastIdTool, $process) {
  global $db_conn;

  $qryDeleteReports = "DELETE FROM tblToolsReports WHERE IdTool=:IdTool AND Process=:Process";
  $stmt = $db_conn->prepare($qryDeleteReports);
  $stmt->bindParam(':IdTool', $pastIdTool, PDO::PARAM_INT);
  $stmt->bindParam(':Process', $process, PDO::PARAM_STR);
  try {
    $stmt->execute();
  }
  catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Line: " . __LINE__ . ": Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
  } 
}

function deleteReferences($pastIdTool, $process) {
  global $db_conn;

  $qryDeleteReferences = "DELETE FROM tblToolsUsefulReferences WHERE IdTool=:IdTool AND Process=:Process";
  $stmt = $db_conn->prepare($qryDeleteReferences);
  $stmt->bindParam(':IdTool', $pastIdTool, PDO::PARAM_INT);
  $stmt->bindParam(':Process', $process, PDO::PARAM_STR);
  try {
    $stmt->execute();
  }
  catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Line: " . __LINE__ . ": Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
  } 
}

/*
*---- updateRecordTools(): it updates tblTools with data form tblEditorTools
*/    
function updateRecordTools($rowEditorTool) {
  global $db_conn;

  date_default_timezone_set('Europe/Rome');

  //writeLog("Tool=" . $rowEditorTool["Tool"] . ", IdTool=" . $rowEditorTool["IdTool"]);
  try {
    $qryUpdate  = "UPDATE tblTools SET Tool=:Tool, LicenseType=:LicenseType, OperatingSystem=:OperatingSystem, ";
    $qryUpdate .= "Developer=:Developer, Url=:Url, DateInsert=:DateInsert, TestReport=:TestReport, Process=:Process, ";
    $qryUpdate .= "Description=:Description, Provenance=:Provenance WHERE IdTool=:IdTool";
    //writeLog("update tblTools: $qryUpdate");
    $stmtUpdate = $db_conn->prepare($qryUpdate);
    $stmtUpdate->bindParam(':Tool', $rowEditorTool["Tool"], PDO::PARAM_STR);
    $stmtUpdate->bindParam(':LicenseType', $rowEditorTool["LicenseType"], PDO::PARAM_STR);
    $stmtUpdate->bindParam(':OperatingSystem', $rowEditorTool["OperatingSystem"], PDO::PARAM_STR);
    $stmtUpdate->bindParam(':Developer', $rowEditorTool["Developer"], PDO::PARAM_STR);
    $stmtUpdate->bindParam(':Url', $rowEditorTool["Url"], PDO::PARAM_STR);
    $stmtUpdate->bindValue(':DateInsert', date("Ymd"), PDO::PARAM_STR);
    $stmtUpdate->bindParam(':TestReport', $rowEditorTool["TestReport"], PDO::PARAM_STR);
    $stmtUpdate->bindParam(':Process', $rowEditorTool["Process"], PDO::PARAM_STR);
    $stmtUpdate->bindParam(':Description', $rowEditorTool["Description"], PDO::PARAM_STR);
    $stmtUpdate->bindValue(':Provenance', "Update");
    $stmtUpdate->bindParam(':IdTool', $rowEditorTool["IdTool"], PDO::PARAM_INT);
    $stmtUpdate->execute();  
  }
  catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Line: " . __LINE__ . ": Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
  } 
  
}

/*
*---- updateRecordTools(): it inserts a tool into tblTools tables with data form tblEditorTools tables
*
*                           tables involved: Tools, ToolsReports, ToolsUsefulReferences, ToolsCategories, ToolsFeatures
*                           the total number of tables are 10, because each table is formed by a couple, for example
*                           (tblTools, tblEditorTools), (tblToolsCategories, tblEditorToolsCategories), ...
*/    
function insertRecordTools($rowEditorTool) {
  global $db_conn;

  date_default_timezone_set('Europe/Rome');

  try {
    $qryInsert  = "INSERT INTO tblTools (Tool, LicenseType, OperatingSystem, Developer, Url, DateInsert, TestReport, ";
    $qryInsert .= "Process, Provenance, Description) VALUES (";
    $qryInsert .= ":Tool, :LicenseType, :OperatingSystem, :Developer, :Url, :DateInsert, :TestReport, :Process, :Provenance, :Description)";
    $stmtTool = $db_conn->prepare($qryInsert);
    $stmtTool->bindParam(':Tool', $rowEditorTool["Tool"], PDO::PARAM_STR);
    $stmtTool->bindParam(':LicenseType', $rowEditorTool["LicenseType"], PDO::PARAM_STR);
    $stmtTool->bindParam(':OperatingSystem', $rowEditorTool["OperatingSystem"], PDO::PARAM_STR);
    $stmtTool->bindParam(':Developer', $rowEditorTool["Developer"], PDO::PARAM_STR);
    $stmtTool->bindParam(':Url', $rowEditorTool["Url"], PDO::PARAM_STR);
    $stmtTool->bindValue(':DateInsert', date("Ymd"), PDO::PARAM_STR);
    $stmtTool->bindParam(':TestReport', $rowEditorTool["TestReport"], PDO::PARAM_STR);
    $stmtTool->bindParam(':Process', $rowEditorTool["Process"], PDO::PARAM_STR);
    $stmtTool->bindParam(':Description', $rowEditorTool["Description"], PDO::PARAM_STR);
    $stmtTool->bindValue(':Provenance', "Insert");
    $stmtTool->execute(); 
  }
  catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Line: " . __LINE__ . ": Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
  }  

  try {
    $lastId = $db_conn->lastInsertId();

    $qryInsertCategory  = "INSERT INTO tblToolsCategories (IdTool, CodeCategory, Process) VALUES ";
    $qryInsertCategory .= "(:IdTool, :CodeCategory, :Process)";
    $stmtCat = $db_conn->prepare($qryInsertCategory);
    $stmtCat->bindParam(':IdTool', $lastId, PDO::PARAM_INT);
    $stmtCat->bindParam(':CodeCategory', $rowEditorTool["CodeCategory"], PDO::PARAM_STR);
    $stmtCat->bindParam(':Process', $rowEditorTool["Process"], PDO::PARAM_STR);
    $stmtCat->execute();

    $qryEditorFeatures  = "SELECT IdFeature, ValueFeature FROM tblEditorToolsFeatures WHERE IdEditorTool=:IdEditorTool AND ";
    $qryEditorFeatures .= "CodeCategory=:CodeCategory";
    $stmtEditor = $db_conn->prepare($qryEditorFeatures);
    $stmtEditor->bindParam(':IdEditorTool', $rowEditorTool["IdEditorTool"], PDO::PARAM_INT);
    $stmtEditor->bindParam(':CodeCategory', $rowEditorTool["CodeCategory"], PDO::PARAM_STR);
    $stmtEditor->execute();
    $nFeatures = $stmtEditor->rowCount();
    for ($i=0; $i<$nFeatures; $i++) {
      $rowFeature = $stmtEditor->fetch();
      $qryInsertFeature  = "INSERT INTO tblToolsFeatures (IdTool, IdFeature, CodeCategory, ValueFeature) VALUES (";
      $qryInsertFeature .= ":IdTool, :IdFeature, :CodeCategory, :ValueFeature)";
      $stmtF = $db_conn->prepare($qryInsertFeature);
      $stmtF->bindParam(':IdTool', $lastId, PDO::PARAM_INT);
      $stmtF->bindParam(':IdFeature', $rowFeature["IdFeature"], PDO::PARAM_INT);
      $stmtF->bindParam(':CodeCategory', $rowEditorTool["CodeCategory"], PDO::PARAM_STR);
      $stmtF->bindParam(':ValueFeature', $rowFeature["ValueFeature"], PDO::PARAM_STR);
      $stmtF->execute();
    }
  }
  catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Line: " . __LINE__ . ": Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
  } 
   
  try {
    $qryEditorReports  = "SELECT ReportUrl, NoteTest, Process FROM tblEditorToolsReports WHERE IdEditorTool=:IdEditorTool AND ";
    $qryEditorReports .= "Process=:Process";
    $stmtRep = $db_conn->prepare($qryEditorReports);
    $stmtRep->bindParam(':IdEditorTool', $rowEditorTool["IdEditorTool"], PDO::PARAM_INT);
    $stmtRep->bindParam(':Process', $rowEditorTool["Process"], PDO::PARAM_STR);
    $stmtRep->execute();
    $nReports = $stmtRep->rowCount();
    for ($i=0; $i<$nReports; $i++) {
      $rowReport = $stmtRep->fetch();
      $qryInsertReport  = "INSERT INTO tblToolsReports (IdTool, ReportUrl, NoteTest, Process) VALUES (";
      $qryInsertReport .= ":IdTool, :ReportUrl, :NoteTest, :Process)";
      $stmtR = $db_conn->prepare($qryInsertReport);
      $stmtR->bindParam(':IdTool', $lastId, PDO::PARAM_INT);
      $stmtR->bindParam(':ReportUrl', $rowReport["ReportUrl"], PDO::PARAM_STR);
      $stmtR->bindParam(':NoteTest', $rowReport["NoteTest"], PDO::PARAM_STR);
      $stmtR->bindParam(':Process', $rowEditorTool["Process"], PDO::PARAM_STR);
      $stmtR->execute();
    }
  }
  catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Line: " . __LINE__ . ": Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
  } 

  try {
    $qryEditorReferences  = "SELECT ReferenceUrl, ReferenceNote, Process FROM tblEditorToolsUsefulReferences WHERE IdEditorTool=:IdEditorTool AND Process=:Process";
    $stmtRef = $db_conn->prepare($qryEditorReferences);
    $stmtRef->bindParam(':IdEditorTool', $rowEditorTool["IdEditorTool"], PDO::PARAM_INT);
    $stmtRef->bindParam(':Process', $rowEditorTool["Process"], PDO::PARAM_STR);
    $stmtRef->execute();
    $nReferences = $stmtRef->rowCount();
    for ($i=0; $i<$nReferences; $i++) {
      $rowReference = $stmtRef->fetch();
      $qryInsertReference  = "INSERT INTO tblToolsUsefulReferences (IdTool, ReferenceUrl, ReferenceNote, Process) VALUES (";
      $qryInsertReference .= ":IdTool, :ReferenceUrl, :ReferenceNote, :Process)";
      $stmtUR = $db_conn->prepare($qryInsertReference);
      $stmtUR->bindParam(':IdTool', $lastId, PDO::PARAM_INT);
      $stmtUR->bindParam(':ReferenceUrl', $rowReference["ReferenceUrl"], PDO::PARAM_STR);
      $stmtUR->bindParam(':ReferenceNote', $rowReference["ReferenceNote"], PDO::PARAM_STR);
      $stmtUR->bindParam(':Process', $rowReference["Process"], PDO::PARAM_STR);
      $stmtUR->execute();
    }
  }
  catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Line: " . __LINE__ . ": Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
  } 
  
}

/*
*---- insertEditorTools():  it copies data of tool in tblTools, imported as first time, into tblEditor... for mantaining the history of
*                           all modifications
*/
function insertEditorTools($rowTool) {
  global $db_conn;

  try{
    $qryInsertEditorTools  = "INSERT INTO tblEditorTools (IdTool, Tool, LicenseType, OperatingSystem, Developer, Url, ";
    $qryInsertEditorTools .= "TestReport, Process, Description, EditingUserName, EditingDate, EditingTime, EditingType, EditingStatus, ApprovalUser, ";
    $qryInsertEditorTools .= "ApprovalDate, ApprovalTime) VALUES (:IdTool, :Tool, :LicenseType, :OperatingSystem, :Developer, ";
    $qryInsertEditorTools .= ":Url, :TestReport, :Process, :Description, :EditingUserName, :EditingDate, :EditingTime, :EditingType,:EditingStatus, ";
    $qryInsertEditorTools .= ":ApprovalUser, :ApprovalDate, :ApprovalTime)";
    $stmtET = $db_conn->prepare($qryInsertEditorTools);

    $stmtET->bindParam(':IdTool', $rowTool["IdTool"], PDO::PARAM_INT);
    $stmtET->bindParam(':Tool', $rowTool["Tool"], PDO::PARAM_STR);
    $stmtET->bindParam(':LicenseType', $rowTool["LicenseType"], PDO::PARAM_STR);
    $stmtET->bindParam(':OperatingSystem', $rowTool["OperatingSystem"], PDO::PARAM_STR);
    $stmtET->bindParam(':Developer', $rowTool["Developer"], PDO::PARAM_STR);
    $stmtET->bindParam(':Url', $rowTool["Url"], PDO::PARAM_STR);
    $stmtET->bindParam(':TestReport', $rowTool["TestReport"], PDO::PARAM_STR);
    $stmtET->bindParam(':Process', $rowTool["Process"], PDO::PARAM_STR);
    $stmtET->bindParam(':Description', $rowTool["Description"], PDO::PARAM_STR);
    $stmtET->bindParam(':EditingUserName', $_SESSION['user_name'], PDO::PARAM_STR);
    $stmtET->bindValue(':EditingDate', "00000000", PDO::PARAM_STR);
    $stmtET->bindValue(':EditingTime', "0000", PDO::PARAM_STR);
    $stmtET->bindValue(':EditingType', "", PDO::PARAM_STR);
    $stmtET->bindValue(':EditingStatus', "", PDO::PARAM_STR);
    $stmtET->bindValue(':ApprovalUser', "", PDO::PARAM_STR);
    $stmtET->bindValue(':ApprovalDate', "00000000", PDO::PARAM_STR);
    $stmtET->bindValue(':ApprovalTime', "0000", PDO::PARAM_STR);
    $stmtET->execute();

    $idNewRec = $db_conn->lastInsertId();
    return($idNewRec);
  }
  catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Line: " . __LINE__ . ": Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
  }      
}

/*
*---- insertEditorToolsCategories():  it copies data of tool in tblToolsCategories, imported as first time, into 
*                                     tblEditor... for keeping history of all modificaitons during its life
*/
function insertEditorToolsCategories($IdEditorTool, $pastIdTool, $pastCodeCategory, $Process) {
  global $db_conn;

  try{
    // $qryTools = "SELECT * FROM tblToolsCategories WHERE IdTool=:IdTool AND CodeCategory=:CodeCategory";
    // $stmtC = $db_conn->prepare($qryTools);
    // $stmtC->bindParam(':IdTool', $pastIdTool, PDO::PARAM_INT);
    // $stmtC->bindParam(':CodeCategory', $pastCodeCategory, PDO::PARAM_STR);
    // $stmtC->execute();
    // $rowTool = $stmtC->fetch();

    $qryInsertEditorCategories  = "INSERT INTO tblEditorToolsCategories (IdEditorTool, pastCodeCategory, CodeCategory, Process) ";
    $qryInsertEditorCategories .= "VALUES (:IdEditorTool, :pastCodeCategory, :CodeCategory, :Process)";
    $stmtTC = $db_conn->prepare($qryInsertEditorCategories);

    $stmtTC->bindParam(':IdEditorTool', $IdEditorTool, PDO::PARAM_INT);
    $stmtTC->bindValue(':pastCodeCategory', "", PDO::PARAM_STR);
    $stmtTC->bindParam(':CodeCategory', $pastCodeCategory, PDO::PARAM_STR);
    $stmtTC->bindParam(':Process', $Process, PDO::PARAM_STR);
    $stmtTC->execute();
  }
  catch(PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Line: " . __LINE__ . ": Error: " . $error . "</p>";
    $db_conn->rollback();
    exit;     
  }
    
}

/*
*---- insertEditorToolsFeatures():  it copies data of tool in tblToolsFeatures, imported as first time, into 
*                                     tblEditor... for keeping history of all modificaitons during its life
*/
function insertEditorToolsFeatures($lastId, $pastIdTool, $pastCodeCategory, $Process) {
  global $db_conn;

  try {
    $qryFeatures  = "SELECT * FROM tblToolsFeatures WHERE IdTool=:IdTool AND CodeCategory=:CodeCategory";
    $stmtF = $db_conn->prepare($qryFeatures);
    $stmtF->bindParam(':IdTool', $pastIdTool, PDO::PARAM_INT);
    $stmtF->bindParam(':CodeCategory', $pastCodeCategory, PDO::PARAM_STR);
    $stmtF->execute();
    $nFeatures = $stmtF->rowCount();
    for ($i=0; $i<$nFeatures; $i++) {
      $rowFeature = $stmtF->fetch();
      $qryEditorFeatures  = "INSERT INTO tblEditorToolsFeatures (IdEditorTool, IdFeature, CodeCategory, ValueFeature) ";
      $qryEditorFeatures .= "VALUES (:IdEditorTool, :IdFeature, :CodeCategory, :ValueFeature)";
      $stmtETF = $db_conn->prepare($qryEditorFeatures);
      $stmtETF->bindParam(':IdEditorTool', $lastId, PDO::PARAM_INT);
      $stmtETF->bindParam(':IdFeature', $rowFeature["IdFeature"], PDO::PARAM_INT);
      $stmtETF->bindParam(':CodeCategory', $pastCodeCategory, PDO::PARAM_STR);
      $stmtETF->bindParam(':ValueFeature', $rowFeature["ValueFeature"], PDO::PARAM_STR);
      $stmtETF->execute();
    }
  }
  catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Line: " . __LINE__ . ": Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
  }   
}  

/*
*   insertCategoriesFeatures(): insert Category and Features from data stored in 
*                               tblEditorToolsCategories and tblEditorToolsFeatures
*/
function insertCategoriesFeatures($IdEditorTool, $editorCodeCategory, $pastIdTool, $process) {
  
  global $db_conn;

  try {
    $qryEditorCategory  = "SELECT CodeCategory, Process FROM tblEditorToolsCategories WHERE IdEditorTool=:IdEditorTool AND ";
    $qryEditorCategory .= "CodeCategory=:CodeCategory";
    $stmtEC = $db_conn->prepare($qryEditorCategory);
    $stmtEC->bindParam(':IdEditorTool', $IdEditorTool, PDO::PARAM_INT);
    $stmtEC->bindParam(':CodeCategory', $editorCodeCategory, PDO::PARAM_STR);
    $stmtEC->execute();
    $rowEditorCategory = $stmtEC->fetch();

    $qryInsertCategory  = "INSERT INTO tblToolsCategories (IdTool, CodeCategory, Process) VALUES ";
    $qryInsertCategory .= "(:IdTool, :CodeCategory, :Process)";
    $stmtTC = $db_conn->prepare($qryInsertCategory);
    $stmtTC->bindParam(':IdTool', $pastIdTool, PDO::PARAM_INT);
    $stmtTC->bindParam(':CodeCategory', $rowEditorCategory["CodeCategory"], PDO::PARAM_STR);
    $stmtTC->bindParam(':Process', $process, PDO::PARAM_STR);
    $stmtTC->execute();

    $qryEditorFeatures  = "SELECT IdFeature, ValueFeature FROM tblEditorToolsFeatures WHERE IdEditorTool=:IdEditorTool AND ";
    $qryEditorFeatures .= "CodeCategory=:CodeCategory";
    $stmtTF = $db_conn->prepare($qryEditorFeatures);
    $stmtTF->bindParam(':IdEditorTool', $IdEditorTool, PDO::PARAM_INT);
    $stmtTF->bindParam(':CodeCategory', $editorCodeCategory, PDO::PARAM_STR);
    $stmtTF->execute();
    $nFeatures = $stmtTF->rowCount();
    for ($i=0; $i<$nFeatures; $i++) {
      $rowFeature = $stmtTF->fetch();
      $qryInsertFeature  = "INSERT INTO tblToolsFeatures (IdTool, IdFeature, CodeCategory, ValueFeature) VALUES (";
      $qryInsertFeature .= ":IdTool, :IdFeature, :CodeCategory, :ValueFeature)";
      $stmtF = $db_conn->prepare($qryInsertFeature);
      $stmtF->bindParam(':IdTool', $pastIdTool, PDO::PARAM_INT);
      $stmtF->bindParam(':IdFeature', $rowFeature["IdFeature"], PDO::PARAM_INT);
      $stmtF->bindParam(':CodeCategory', $editorCodeCategory, PDO::PARAM_STR);
      $stmtF->bindParam(':ValueFeature', $rowFeature["ValueFeature"], PDO::PARAM_STR);
      $stmtF->execute();
    }
  }
  catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Line: " . __LINE__ . ": Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
  }  
}

function insertReports($IdEditorTool, $pastIdTool, $process) {
  global $db_conn;

  try {
    $qryEditorReports  = "SELECT ReportUrl, NoteTest, Process FROM tblEditorToolsReports WHERE IdEditorTool=:IdEditorTool AND ";
    $qryEditorReports .= "Process=:Process";
    $stmtETR = $db_conn->prepare($qryEditorReports);
    $stmtETR->bindParam(':IdEditorTool', $IdEditorTool, PDO::PARAM_INT);
    $stmtETR->bindParam(':Process', $process, PDO::PARAM_STR);
    $stmtETR->execute();
    $nReports = $stmtETR->rowCount();
    for ($i=0; $i<$nReports; $i++) {
      $rowReport = $stmtETR->fetch();
      $qryInsertReport  = "INSERT INTO tblToolsReports (IdTool, ReportUrl, NoteTest, Process) VALUES (";
      $qryInsertReport .= ":IdTool, :ReportUrl, :NoteTest, :Process)";
      $stmtTR = $db_conn->prepare($qryInsertReport);
      $stmtTR->bindParam(':IdTool', $pastIdTool, PDO::PARAM_INT);
      $stmtTR->bindParam(':ReportUrl', $rowReport["ReportUrl"], PDO::PARAM_STR);
      $stmtTR->bindParam(':NoteTest', $rowReport["NoteTest"], PDO::PARAM_STR);
      $stmtTR->bindParam(':Process', $process, PDO::PARAM_STR);
      $stmtTR->execute();
    }
  }    
  catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Line: " . __LINE__ . ": Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
  }    

}

function insertReferences($IdEditorTool, $pastIdTool) {
  global $db_conn;

  try {
    $qryEditorReferences  = "SELECT ReferenceUrl, ReferenceNote, Process FROM tblEditorToolsUsefulReferences WHERE IdEditorTool=:IdEditorTool";
    $stmtEUR = $db_conn->prepare($qryEditorReferences);
    $stmtEUR->bindParam(':IdEditorTool', $IdEditorTool, PDO::PARAM_INT);
    $stmtEUR->execute();
    $nReferences = $stmtEUR->rowCount();
    for ($i=0; $i<$nReferences; $i++) {
      $rowReference = $stmtEUR->fetch();
      $qryInsertReference  = "INSERT INTO tblToolsUsefulReferences (IdTool, ReferenceUrl, ReferenceNote, Process) VALUES (";
      $qryInsertReference .= ":IdTool, :ReferenceUrl, :ReferenceNote, :Process)";
      $stmtTUR = $db_conn->prepare($qryInsertReference);
      $stmtTUR->bindParam(':IdTool', $pastIdTool, PDO::PARAM_INT);
      $stmtTUR->bindParam(':ReferenceUrl', $rowReference["ReferenceUrl"], PDO::PARAM_STR);
      $stmtTUR->bindParam(':ReferenceNote', $rowReference["ReferenceNote"], PDO::PARAM_STR);
      $stmtTUR->bindParam(':Process', $rowReference["Process"], PDO::PARAM_STR);
      $stmtTUR->execute();
    }
  }    
  catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p class=dftError>Line: " . __LINE__ . ": Error: " . $error . "</p>";
    $db_conn->rollback();
    exit; 
  }    

}
?>

<?php
/*
*--- Functions for preparing the results table
*/


/*
*--- function valueFeature() 
*/

 function valueFeature($rowTool) {
    global $codeCategory, $valueFilter, $debugFile, $codesLeavesNoFeatures, $process, $qryTools, $db_conn;
            
    if (in_array($rowTool["CodeCategory"], $codesLeavesNoFeatures))
        return(0);
       
    $qryValues  = 'SELECT ValueFeature, Feature FROM tblToolsFeatures, tblFeatures WHERE tblToolsFeatures.IdFeature=tblFeatures.IdFeature AND ';

// it extracts every Feature/Values even though a Feature with filed Visibile set to 'N' is not part of the query

    $qryValues .= 'IdTool =' . $rowTool["IdTool"] . ' AND tblToolsFeatures.CodeCategory="' . $rowTool["CodeCategory"] . '" ' . $valueFilter; 
   	$qryValues .= ' AND Process="' . $process . '" ORDER BY tblFeatures.IdFeature, ValueFeature ';
    $rsValues = $db_conn->query($qryValues);
    $nValues = $rsValues->rowCount();    
   
    $oldFeature = "";
    $sValue = "";
    if ($nValues > 0) {
        for ($j=0; $j < $nValues; $j++) {
            $rowValue = $rsValues->fetch();
            if ($oldFeature == $rowValue["Feature"])
                ;
            else {               
                if (strlen($sValue)  > 0) {    // id sValue is not empty, it contains the Values related to the previous Feature, and it's time to show them
                    $sValue = substr($sValue, 0, -2);
                    $str = '<span class=dftTextItalic>' . $sValue . '</span>';        
                    echo $str . '<br/>';
                }
                echo $rowValue["Feature"] . '<br/>';
                $oldFeature = $rowValue["Feature"];
                $sValue = "";
            }                   
            $sValue .= $rowValue["ValueFeature"] . ', ';
        }               
        $sValue = substr($sValue, 0, -2);
        $str = '<span class=dftTextItalic>' . $sValue . '</span>';        
        echo $str . '<br/>';
    }
}


/*
*--- function prepareRow(): 
*/
function prepareRow($rowTool) {
    global $codeCategory, $category, $nFeatures, $process, $os, $license, $codesLeavesNoFeatures, $db_conn;
    
    $lineDebug = '';

    $offset = -3;
    echo '<tr class=dftText>';

/*
* if the Tool has already been updated by someone else and it has not still approved, the editing is disabled
*/
    $qryEditorTool   = 'SELECT IdEditorTool FROM tblEditorTools WHERE IdTool =' . $rowTool["IdTool"] . " AND ";
    $qryEditorTool  .= 'EditingStatus="Pending"';
    $rsEditorTool   = $db_conn->query($qryEditorTool);
    $nEditorTool    = $rsEditorTool->rowCount();

    if ($nEditorTool > 0)
      echo '<td><a class=dftLink href=#><img valign=top src="images/dfte.edit.disabled.png" title="Approval of updating tool is pending"/></a>&nbsp;&nbsp;';
    else {
      echo '<td><a class=dftLink href=javascript:EditTool("' . $rowTool["IdTool"];
      echo '","' . $rowTool["CodeCategory"] . '"); >';
      echo '<img valign=top src="images/dfte.edit.png" title="Edit tool" /></a>&nbsp;&nbsp;';
    }
      
    echo '<a class="dftLink"  target="_blank" title="Tool web address" href="' . $rowTool["Url"] . '">' . $rowTool["Tool"] . '</a>';

    if (trim($rowTool["Developer"]) == "")
        ;
    else
        echo "<br/>(" . $rowTool["Developer"] . ")<br/>";
    
    if ($rowTool["TestReport"] == 'S') {
        $qryTests = "SELECT ReportUrl, NoteTest FROM tblToolsReports WHERE IdTool=" . $rowTool["IdTool"] . ' AND Process="' . $rowTool["Process"]  . '" ';
        $rsTests = $db_conn->query($qryTests);
        $nTests = $rsTests->rowCount();
        
        if ($nTests >0)
            $lineTest = "<br/><span class=dftEnfasi5>Test &rarr;&nbsp;</span>";
            
        for ($t=0; $t<$nTests; $t++) {
            $imgName = $t + 1;
            $imgName = 'images/dfte.' . $imgName . '.small';
            $rowTest = $rsTests->fetch();
            $urlTest = $rowTest["ReportUrl"];
            if (substr($urlTest, 0, 5) == "http:")
              ;
            else
                $urlTest = "http://" . $urlTest;
            $lineTest .= '<a title="Test ' . $rowTest["NoteTest"] . '" target="_blank" href="' . $urlTest . '"><img align=top border=0 src=' . $imgName . '.png></a>&nbsp;';
        }
        echo $lineTest;
    }        
        
    if ($_SERVER['REMOTE_ADDR'] == "127.0.0.1")    // localhost
        echo ' (' . $rowTool["IdTool"] . ')';

    echo '</td>';

    $pos = strpos($rowTool["Description"], ".");
    $len = strlen($rowTool["Description"]);
    
    $sentence = $rowTool["Description"];

    if ($pos > -1) {        // if there is a full stop

        if ($len < $pos + MAX_OFFSET)
            $sentence = $rowTool["Description"];
        else {
            $sentence = substr($rowTool["Description"], 0, $pos + 1);
            $sentence .= ' ... <span class=dftEnfasi5><span class=tooltip title="';   
            $sentence .= $rowTool["Description"] . '">';
            $sentence .= "more</span></span>";
        }
    }

    echo '<td>' . $sentence . '</td>';
    
    $wholeCategory = $rowTool["Category"];
    $xCode = $rowTool["CodeCategory"];
    $sCode = substr($xCode, 0, $offset);   
        
    $repeat = 4;
    $indent = str_repeat('&nbsp;', $repeat);
    while (strlen($sCode) > 0) {
        $qryCategory = 'SELECT Category FROM tblCategories WHERE CodeCategory ="' . $sCode . '" AND Process="' . $process . '"';
        $rsCategory = $db_conn->query($qryCategory);
        $rowCategory = $rsCategory->fetch();
        $wholeCategory = $rowCategory["Category"] . '<br/>' . $indent .  '>' . $wholeCategory;
        $offset -= 3;
        $repeat -= 2;
        $indent = str_repeat('&nbsp;', $repeat);
        $sCode = substr($xCode, 0, $offset);
    }        
    
    if ($rowTool["CodeCategory"] == $codeCategory)   // all extracted tools belong to the same category
        echo  '<td>' . $wholeCategory. '</td>';
    else
        echo  '<td><a class="dftLink"  title="Tools of the same category" href=javascript:ViewToolCategory("' . $rowTool["CodeCategory"] . '");>' . $wholeCategory. '</a></td>';
    
    
     if ($rowTool["LicenseType"] == $license)   // all extracted tools belong to the same license type
        echo  '<td>' . $rowTool["LicenseType"] . '</td>';
    else        
        echo  '<td><a class="dftEnfasi2" title="Tools of the same o.s." href=javascript:ViewToolLicense("' . $rowTool["LicenseType"] . '");>' . $rowTool["LicenseType"]. '</a></td>';
    
    if ($rowTool["OperatingSystem"] == $os)   // all extracted tools belong to the same O.S.
        echo  '<td>' . $rowTool["OperatingSystem"]. '</td>';
    else {       
    		$sTemp = str_replace(" ", "_", $rowTool["OperatingSystem"]);
        echo  '<td><a class="dftEnfasi2" title="Tools of the same o.s." href=javascript:ViewToolOs("' . $sTemp . '");>' . $rowTool["OperatingSystem"]. '</a></td>';
		}        
        
    echo '<td class=dftEnfasi5>';    
    valueFeature($rowTool); 
    echo "</td>";

    $qryReferences = "SELECT * FROM tblToolsUsefulReferences WHERE IdTool=" . $rowTool["IdTool"];
    $rsReferences = $db_conn->query($qryReferences);
    $nReferences = $rsReferences->rowCount();
    $line = "";
    for ($i=0; $i<$nReferences; $i++) {
        $rowReference = $rsReferences->fetch();
        $note = $rowReference["ReferenceNote"];
        if ($note == "")
            $note = "*reference*";

        $line .= '<a title="Useful reference ' . $note . '" target="_blank" href="';

        if (substr($rowReference["ReferenceUrl"], 0, 4) == "http")
            $line .= $rowReference["ReferenceUrl"] . '">' . $note . '</a><br/>';
        else
            $line .= "http://" . $rowReference["ReferenceUrl"] . '">' . $note . '</a><br/>';
    }
    echo "<td>$line</td>";           
}


/*
*--- function prepareApprovalRow(): 
*/
function prepareApprovalRow($rowTool) {
    global $codesLeavesNoFeatures, $db_conn;
    
    $lineDebug = '';

    $offset = -3;
    echo '<tr class=dftText>';
    
    echo '<td><a class="dftLink"  target="_blank" title="Tool web address" href="' . $rowTool["Url"];
    echo '">' . $rowTool["Tool"] . '</a>';
 
    
    if ($rowTool["TestReport"] == 'S') {
        $qryTests = "SELECT ReportUrl, NoteTest FROM tblEditorToolsReports WHERE IdEditorTool=" . $rowTool["IdEditorTool"];
        $qryTests .= ' AND Process="' . $rowTool["Process"]  . '" ';
        $rsTests = $db_conn->query($qryTests);
        $nTests = $rsTests->rowCount();
        
        if ($nTests >0)
            $lineTest = "<br/><span class=dftEnfasi5>Test &rarr;&nbsp;</span>";
            
        for ($t=0; $t<$nTests; $t++) {
            $imgName = $t + 1;
            $imgName = 'images/dfte.' . $imgName . '.small';
            $rowTest = $rsTests->fetch();
            $lineTest .= '<a title="Test ' . $rowTest["NoteTest"] . '" target="_blank" href="' . $rowTest["ReportUrl"];
            $lineTest .= '"><img align=top border=0 src=' . $imgName . '.png></a>&nbsp;';
        }
        echo $lineTest;
    }        
        
    if ($_SERVER['REMOTE_ADDR'] == "127.0.0.1")    // localhost
        echo ' (' . $rowTool["IdEditorTool"] . ')';

    echo '</td>';
    
    $wholeCategory = $rowTool["Category"];
    $xCode = $rowTool["CodeCategory"];
    $sCode = substr($xCode, 0, $offset);   
        
    $repeat = 4;
    $indent = str_repeat('&nbsp;', $repeat);
    while (strlen($sCode) > 0) {
        $qryCategory  = 'SELECT Category FROM tblCategories WHERE CodeCategory ="' . $sCode;
        $qryCategory .= '" AND Process="' . $rowTool["Process"] . '"';
        $rsCategory = $db_conn->query($qryCategory);
        $rowCategory = $rsCategory->fetch();
        $wholeCategory = $rowCategory["Category"] . '<br/>' . $indent .  '>' . $wholeCategory;
        $offset -= 3;
        $repeat -= 2;
        $indent = str_repeat('&nbsp;', $repeat);
        $sCode = substr($xCode, 0, $offset);
    }        
    
    echo  '<td>' . $wholeCategory. '</td>';
    echo  '<td>' . $rowTool["LicenseType"] . '</td>';
    echo  '<td>' . $rowTool["OperatingSystem"]. '</td>';      
    echo '<td class=dftEnfasi5>';
    valueApprovalFeature($rowTool);
    echo "</td>";

    $editingDate = $rowTool["EditingDate"];
    $editingDate = substr($editingDate, 6, 2) . "/" . substr($editingDate, 4, 2) . "/" . substr($editingDate, 0, 4);

    echo "<td>" . $_SESSION['user_name'] . "<br/>" . $editingDate . "</td>";

    if ($rowTool["EditingType"] == "U") {//  Update
      $img = "dfte.confirm.png";
      $title="Approval update";
    }
    else {
      $img = "dfte.add.png"; 
      $title="Approval insert";
    }      

    echo "<td align=center><a title='" . $title . "' href=javascript:ApprovalSingleTool('" . $rowTool["IdEditorTool"] . "');>";
    echo "<img src=images/" . $img . "></a></td></tr>";            
}

/*
*--- function valueFeature() 
*/

 function valueApprovalFeature($rowTool) {
    global $codesLeavesNoFeatures, $db_conn;
            
    if (in_array($rowTool["CodeCategory"], $codesLeavesNoFeatures))
        return(0);
       
    $qryValues   = 'SELECT ValueFeature, Feature FROM tblEditorToolsFeatures, tblFeatures WHERE ';
    $qryValues  .= 'tblEditorToolsFeatures.IdFeature=tblFeatures.IdFeature AND ';

// it extracts every Feature/Values even though a Feature with filed Visibile set to 'N' is not part of the query

    $qryValues .= 'IdEditorTool =' . $rowTool["IdEditorTool"] . ' AND ';
    $qryValues .= 'tblEditorToolsFeatures.CodeCategory="' . $rowTool["CodeCategory"] . '" AND '; 
    $qryValues .= 'Process="' . $rowTool["Process"] . '" ORDER BY tblFeatures.IdFeature, ValueFeature ';   
    $rsValues = $db_conn->query($qryValues);
    $nValues = $rsValues->rowCount();    
   
    $oldFeature = "";
    $sValue = "";
    if ($nValues > 0) {
        for ($j=0; $j < $nValues; $j++) {
            $rowValue = $rsValues->fetch();
            if ($oldFeature == $rowValue["Feature"])
                ;
            else {               
                if (strlen($sValue)  > 0) {    // id sValue is not empty, it contains the Values related to the previous Feature, and it's time to show them
                    $sValue = substr($sValue, 0, -2);
                    $str = '<span class=dftTextItalic>' . $sValue . '</span>';        
                    echo $str . '<br/>';
                }
                echo $rowValue["Feature"] . '<br/>';
                $oldFeature = $rowValue["Feature"];
                $sValue = "";
            }                   
            $sValue .= $rowValue["ValueFeature"] . ', ';
        }               
        $sValue = substr($sValue, 0, -2);
        $str = '<span class=dftTextItalic>' . $sValue . '</span>';        
        echo $str . '<br/>';
    }
}

function setJavascriptField($field, $value) {
  //windowAlert("document.frmSearch." . $field . ".value=" . $value );
  echo "<script>document.frmSearch." . $field . ".value=" . $value . ";</script>" ;
}

function windowAlert($msg) {
    echo "<script>window.alert('" . $msg .  "');</script>";
}    

function writeLog($msg) {
  date_default_timezone_set('Europe/Rome');

  if (file_exists ("./debug/dfte.log"))
    $debugFile = fopen("./debug/dfte.log", "a");
  else
    $debugFile = fopen("./debug/dfte.log", "w");

  fwrite($debugFile, date("Ymd") . " - " . date("Hi") . " - " . $msg . "\n");

  fclose($debugFile);   
}
?>