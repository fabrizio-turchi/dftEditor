<html>
<head>
<title>EVIDENCE project: Results  frame</title>
<link rel="stylesheet" href="dftc.css" type="text/css"> 
<script>
function Sort(field, direction) {
    document.frmResults.direction.value = direction;
    document.frmResults.sort.value = field;
    document.frmResults.submit();    
}

function OpenWin(url) {
    var options = "width=500, height=300, scrollbars, alwaysRaised";
    var newWindow = window.open(url, "WebTool", options);
}

function ViewToolOs(value) {
    fOsSearch = parent.search.document.frmSearch.os;
    
    document.frmResults.os.value = value;

    document.frmResults.qryCatalogue.value = ''; // qryCatalogue  is used only for sorting previous results
    document.frmResults.sort.value = "Tool";
    document.frmResults.direction.value= "ASC";
    
    nOs = fOsSearch.length;
    for(i=0; i <nOs; i++) {
        if (fOsSearch.options[i].value == value) {
            fOsSearch.selectedIndex = i;
            break;
        }            
    }
    document.frmResults.submit();
}

function ViewToolCategory(code) {
    fCode = document.frmResults.formCategories;
    fCategory = parent.search.document.frmSearch.CodeCategory;
    
    for (i=0; i < fCode.length; i++) {
        if (fCode.options[i].value == code) {
            document.frmResults.CodeCategory.value = code;
            document.frmResults.Category.value = fCode.options[i].text;
            break;            
        }
    }
    
    for (i=0; i<fCategory.length; i++) {
        if (fCategory.options[i].value == code) {
            fCategory.selectedIndex = i;
            break;
        }            
    }        
    
    window.parent.search.CheckCategory();
    
    document.frmResults.qryCatalogue.value = ''; // qryCatalogue  is used only for sorting previous results
    document.frmResults.sort.value = "Tool";
    document.frmResults.direction.value= "ASC";
        
    document.frmResults.submit();                 
}    

function ViewToolLicense(value) {
    fLicenseSearch = parent.search.document.frmSearch.license;
    
    document.frmResults.license.value = value;

    document.frmResults.qryCatalogue.value = ''; // qryCatalogue  is used only for sorting previous results
    document.frmResults.sort.value = "Tool";
    document.frmResults.direction.value= "ASC";
    
    nLicenses = fLicenseSearch.length;
    for(i=0; i <nLicenses; i++) {
        if (fLicenseSearch.options[i].value == value) {
            fLicenseSearch.selectedIndex = i;
            break;
        }            
    }
    document.frmResults.submit();
}

</script>
</head>
<body>
<a target="Evidence web site" href="http;//evidenceproject.eu">
<img div="logo" src="dftc.evidence.logo.png" alt="EVIDENCE project" border="0" />
</a>
<form name="frmResults" method="post" action="dftc.results.php" target=content>

<?php
    include "dftc.Db.php";
    //$debugFile = fopen("results.txt", "w");

    $process = $_POST["process"];
    $toolName = $_POST["toolName"];    
    $license = $_POST["license"];
    $codeCategory = $_POST["CodeCategory"];
    $category = $_POST["Category"];
    $os = $_POST["os"];   
    $developer = $_POST["developer"];
    $url = $_POST["url"];
    $sort = $_POST["sort"]; 
    $direction = $_POST["direction"]; 
    $qryCatalogue = $_POST["qryCatalogue"]; 
    
// code catageory leaves without features: in these case the query can't contain tables tblFeatures and tblToolsFeatures
    $codesLeavesNoFeatures= array("01.05.AN", "07.AN", "07.02.AN", "08.02.AN", "08.03.AN", "08.04.AN", "08.05.AN", "01.02.AC", "AC", "AN");         
             
/*    
* --- catch vaules/features for filtering the values in the table tblToolsFeatures (see function valueFeature()
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
            $qryToolsBase  = 'SELECT DISTINCT tblTools.IdTool, Tool, LicenseType, OperatingSystem, tblToolsCategories.Process, Developer, Url, TestReport, ';
            $qryToolsBase .= 'Category, tblCategories.CodeCategory ';
            $qryToolsBase .= 'FROM tblTools, tblCategories, tblToolsCategories ';
            $qryToolsBase .= 'WHERE tblTools.IdTool=tblToolsCategories.IdTool AND tblCategories.CodeCategory=tblToolsCategories.CodeCategory ';
        }
        else {
            $qryToolsBase  = 'SELECT DISTINCT tblTools.IdTool, Tool, LicenseType, OperatingSystem, tblToolsCategories.Process, Developer, Url, TestReport, ';
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
            
    if ( $developer == "")
        ;
    else
        $furtherWhereCondition .= ' AND Developer LIKE "%' . $developer . '%" ';      
        
    if ( $url == "")
        ;
    else
        $furtherWhereCondition .= ' AND Url LIKE "%' . $url . '%" ';              
                        
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
        $qryToolsBase  = 'SELECT DISTINCT tblTools.IdTool, Tool, LicenseType, OperatingSystem, tblToolsCategories.Process, Developer, Url, TestReport, ';
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
        echo '<p class=dftText>*** start debug<br/>' . $qryTools . '<br/>***end debug<br/>';

   
    echo '<p class="dftTextGrassetto">EVIDENCE project: results - <span class=dftEnfasi5>Found tools: <span class=dftEnfasi2>' . $totTools . '</span></p>';
    echo  '<table border=1 width="90%">';
    echo "<tr class=dftTextGrassetto align=center><td width='20%'>Tool&nbsp;";
    echo "<a href=javascript:Sort('Tool','DESC');><img src=dftc.order.down.png></a>&nbsp;";
    echo "<a href=javascript:Sort('Tool','ASC');><img src=dftc.order.up.png></a></td>";
    echo "<td width='20%'>Category&nbsp;";
    echo "<a href=javascript:Sort('tblCategories.Category','DESC');><img src=dftc.order.down.png></a>&nbsp;";
    echo "<a href=javascript:Sort('tblCategories.Category','ASC');><img src=dftc.order.up.png></a></td>";
    echo "<td width='15%'>License&nbsp;";
    echo "<a href=javascript:Sort('LicenseType','DESC');><img src=dftc.order.down.png></a>&nbsp;";
    echo "<a href=javascript:Sort('LicenseType','ASC');><img src=dftc.order.up.png></a></td>";
    echo "<td width='10%'>O.S.&nbsp;";
    echo "<a href=javascript:Sort('OperatingSystem','DESC');><img src=dftc.order.down.png></a>&nbsp;";
    echo "<a href=javascript:Sort('OperatingSystem','ASC');><img src=dftc.order.up.png></a></td>";
    echo "<td width='15%'>Developer&nbsp;";
    echo "<a href=javascript:Sort('Developer','DESC');><img src=dftc.order.down.png></a>&nbsp;";
    echo "<a href=javascript:Sort('Developer','ASC');><img src=dftc.order.up.png></a></td>";
    echo "<td width='20%'>Features / Values</td></tr>";   
    
    $idTool = "";
    
    $i = 0;
    foreach($arrayTools as $rowTool) {
        $sameTool = $rowTool["IdTool"] . $rowTool["CodeCategory"]; // same tool but different categories will produce different rows in the table! It's the case one tool many categories...
        if ($sameTool  == $idTool) {
        }                            
        else {            
            if ($i > 0)     // not first cycle
                echo  "</td></tr>";
  
            prepareRow();   
            $idTool = $sameTool;         
        }                
        $i++;        
    }       
?>
</table>
<?php
    echo '<input type=hidden name=process value="' . $process . '">';
    echo '<input type=hidden name=toolName value="' . $toolName . '">';
    echo '<input type=hidden name=license value="' . $license . '">';
    echo '<input type=hidden name=CodeCategory value="' . $codeCategory . '">';
    echo '<input type=hidden name=Category value="' . $category . '">';
    echo '<input type=hidden name=os value="' . $os . '">';
    echo '<input type=hidden name=developer value="' . $developer . '">';
    echo '<input type=hidden name=url value="' . $url . '">';
    $qryCategories = 'SELECT *  FROM tblCategories WHERE Process="' . $process . '"';
    $rsCategories = $db_conn->query($qryCategories);
    $numCategories = $rsCategories->rowCount();
    echo "<select class=dftHidden name=formCategories>";
    for ($idx=0; $idx < $numCategories; $idx++) {
        $rowCategory = $rsCategories->fetch();
        echo '<option value="' . $rowCategory["CodeCategory"] . '">' .  $rowCategory["Category"] . '</option>';
    }        
    echo '</select>';     
    echo "<input type=hidden name=qryCatalogue value='" . $qryToolsBase . "' >";
    echo '<input type=hidden name=sort value="">';
    echo '<input type=hidden name=direction value="">';
    
   

    //fclose($debugFile);   
?>
</form>
</body>
</html>
<?php
/*
*--- function valueFeature() 
*/

 function valueFeature() {
    global $codeCategory, $rowTool, $valueFilter, $debugFile, $codesLeavesNoFeatures, $process, $qryTools, $db_conn;
            
    If (in_array($rowTool["CodeCategory"], $codesLeavesNoFeatures))
        return(0);
        
    $qryValues  = 'SELECT ValueFeature, Feature FROM tblToolsFeatures, tblFeatures WHERE tblToolsFeatures.IdFeature=tblFeatures.IdFeature AND ';

// it extracts every Feature/Values even though a Feature with filed Visibile set to 'N' is not part of the query

    $qryValues .= 'IdTool =' . $rowTool["IdTool"] . ' AND tblToolsFeatures.CodeCategory="' . $rowTool["CodeCategory"] . '" ' . $valueFilter; 

    
    //$line = $qryValues . "\n";
    //fwrite($debugFile, $line);
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
function prepareRow() {
    global $codeCategory, $category, $rowTool, $nFeatures, $process, $os, $license, $db_conn;
    
    $offset = -3;
    echo '<tr class=dftText>';
    echo '<td><a class="dftLink"  target="_blank" title="Tool web address" href="' . $rowTool["Url"] . '">' . $rowTool["Tool"] . '</a>';

    if ($rowTool["TestReport"] == 'S') {
        $qryTests = "SELECT ReportUrl, NoteTest FROM tblToolsReports WHERE IdTool=" . $rowTool["IdTool"] . ' AND Process="' . $rowTool["Process"]  . '" ';
        //windowAlert($qryTests);
        $rsTests = $db_conn->query($qryTests);
        $nTests = $rsTests->rowCount();
        
        if ($nTests >0)
            $lineTest = "<br/><span class=dftEnfasi5>Test &rarr;&nbsp;</span>";
            
        for ($t=0; $t<$nTests; $t++) {
            $imgName = $t + 1;
            $imgName = 'dftc.' . $imgName . '.small';
            $rowTest = $rsTests->fetch();
            $lineTest .= '<a title="Test ' . $rowTest["NoteTest"] . '" target="_blank" href="' . $rowTest["ReportUrl"] . '"><img align=top border=0 src=' . $imgName . '.png></a>&nbsp;';
        }
        echo $lineTest;
    }        
        
    if ($_SERVER['REMOTE_ADDR'] == "127.0.0.1")    // localhost
        echo ' (' . $rowTool["IdTool"] . ')';

    echo '</td>';
    
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
    else        
        echo  '<td><a class="dftEnfasi2" title="Tools of the same o.s." href=javascript:ViewToolOs("' . $rowTool["OperatingSystem"] . '");>' . $rowTool["OperatingSystem"]. '</a></td>';
        
    echo  '<td class="dftText">' . $rowTool["Developer"]. '</td>';
    echo '<td class=dftEnfasi5>';
    
        valueFeature();            
}

function windowAlert($msg) {
    echo "<script>window.alert('" . $msg .  "');</script>";
}    
?>
