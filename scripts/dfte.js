function checkSubmit(e) {

    if(e && e.keyCode == 13) {
        document.frmSearch.dftRequest.value="query";
        document.frmSearch.submit();
    }        
}

function Help() {
    parent.content.window.location.href = "dftc.help.html";    
}

function MindMap() {
    fProcess                    = document.frmSearch.process;

    if (fProcess.value == "AN")         // case Analysis
        parent.content.window.location.href = "dftc.analysis.pdf";
    else
        parent.content.window.location.href = "dftc.acquisition.pdf";      
        
}

function TagsCloud() {
    fProcess                    = document.frmSearch.process;

    if (fProcess.value == "AN")         // case Analysis
        parent.content.window.location.href = "dftc.tags.cloud.AN.html";
    else
        parent.content.window.location.href = "dftc.tags.cloud.AC.html";              
}

function SetData(process) {
  
    fCodeCategory     = document.frmSearch.CodeCategory;
    fCodeCategoryAN = document.frmSearch.CodeCategoryAN;    // Categories related to Analysis
    fCodeCategoryAC = document.frmSearch.CodeCategoryAC;    // Categories related to Acquisition
    
    if (process == "Analysis") {
        document.frmSearch.process.value="AN";
        document.frmSearch.totalTools.value = document.frmSearch.nToolsAN.value;
        selectLength = fCodeCategoryAN.length;
        fSelect = fCodeCategoryAN;
        document.frmSearch.dfTools.value = "Analysis";
        }            
    else {
        document.frmSearch.process.value="AC";
        document.frmSearch.totalTools.value = document.frmSearch.nToolsAC.value;
        selectLength = fCodeCategoryAC.length;
        fSelect = fCodeCategoryAC;
        document.frmSearch.dfTools.value = "Acquisition";
    }        
    
    fCodeCategory.length = selectLength
    for (i=0; i < selectLength; i++) {
        fCodeCategory.options[i].value = fSelect.options[i].value;
        fCodeCategory.options[i].text = fSelect.options[i].text;
    }    
    document.frmSearch.CodeCategory.selectedIndex = 0;
    document.frmSearch.license.selectedIndex = 0;
    document.frmSearch.toolName.value = "";
    document.frmSearch.os.selectedIndex = 0;
    //document.frmSearch.developer.value = "";
    
                
    $("#box").hide("slow") 
}

/*
*--- CheckCategory(): sets and shows the Features Panel on the basis of the onChange event of the Category SELECT form field
*
*--- Input: divSource: represents the DIV element from which the request is sent: possible values are DIV=search or DIV=editor
*/

function CheckCategory() {
    fCategoryName         = document.frmSearch.Category;
    fCategories           = document.frmSearch.CodeCategory; 
    CheckGeneral(fCategoryName,fCategories, "search");
}

/*
*--- CheckGeneral():    it dynamically creates the checkbox elements in the Featues Panel. The Index (dfte.index) contains two different panels.
*                       for the search DIV and for the editor DIV. For avoiding the code duplication the  function is called for both Panels. 
*                       the parameters suffix allows to distinguish the cases
*
*   Input:
*                       CategoryName:   the name of the selected Category
*                       fCategories:    all the Categories realted to the selected branch (Analysis or Acquisition) 
*                       whichDiv:       the DIV element from which the function is called, it may be search or editor DIV, Both DIV contain
*                                       a box for showing the Features Panel and the two boxes are indipendent
*
*   Task:               Creates the dynamic checkbox elements for the Features and the corresponding Values. 
*                       The Feature checkbox has name=IdFeature, and values=IdFeature_0, _1, ... 
*/
function CheckGeneral(fCategoryName, fCategories, whichDiv) {
    
    
// set the current Features Panel
    if (whichDiv == "search") {
        containerName = "#container";
        boxName = "#box";
        cbSuffix = "";
    }
    else {
        containerName = "#editorContainer";
        boxName = "#editorBox";   
        cbSuffix = "editor";
    }

// if a Feature has more then maxValuesFeature, then in the Features Panel, two values are shown for each row    
    maxValuesFeature     = 1     
    fProcess              = document.frmSearch.process;
    
    if (fProcess.value == "AN")         // case Analysis
        fFeatures   = document.frmSearch.FeaturesAN;
    else                                // case Acquisition
        fFeatures   = document.frmSearch.FeaturesAC;
    
    fFeaturesValues   = document.frmSearch.FeaturesValues;

    idxCategory = fCategories.selectedIndex;
    vCategory = fCategories.options[idxCategory].value;
    idFeatures = fFeatures.options[idxCategory].value; //idFeatures contains all the IdFeatures of the selected Category
    nameFeatures = fFeatures.options[idxCategory].text; //nameFeatures contains all the Features of the selected Category
    fCategoryName.value = fCategories.options[idxCategory].text;
    
    //window.alert("*debug*idx=" + idxCategory + "category=" + vCategory + ", \nidF=" + idFeatures + "\nnameF=" + nameFeatures);

    removePanel(boxName); // clear the Features Panel of search DIV
    
    if (idFeatures == "")  // if there isn't any feature  related to the selected Category the Features Panel is not shown
        ;
    else {   // if there is, at least, one feature, the Features Panel is shown!
        if (whichDiv == "search")
            divBox = $('<div id="box"></div>');
        else
            divBox = $('<div id="editorBox"></div>');
        divBox.appendTo(containerName);
    }        
    
    aIdFeatures = idFeatures.split("#");           // extract all IdFeatures that are separated by #
    aNamesFeatures = nameFeatures.split("#");      // extract all Features that are separated by #
    checkboxNumber = 0;

    for (k=0; k < aIdFeatures.length; k++) {          // loop over all IdFeatures of the selected Category
        idFeatureValues = aIdFeatures[k].split("@");  // the value contains idFeature, DeeperLevel and Visible separated by @
        idFeature = idFeatureValues[0];               // idFeature
        idSubFeature = idFeatureValues[1];            // DeeperLevel, not used anymore
        visibleFeature = idFeatureValues[2];          // Visible
        nameFeature = aNamesFeatures[k];
        valueFound = false;

        for (i=0; i < fFeaturesValues.length; i++) {
            if (fFeaturesValues.options[i].value == idFeature) {
                checkboxNumber ++;
//if the Feature is not visibile, for instance Category=03. and Feature=Function, all values are checked on but the Feature is not shown on the Panel
                if (visibleFeature =="N") {
                    classFeature  = " class=dftHidden ";
                    classRadio     = " class=dftHidden ";
                    sName = "";
                    checkboxNumber = 0;
                }                        
                else {              // Feature is visible
                    classFeature =" ";                    
                    classRadio = " class=dftTextGrassetto ";
                    sName = nameFeature
                }                        
                           
                if (checkboxNumber > 1) // if there are more than one Features, for the selected Category, they are divided by <hr>
                    labelRadio = "<hr><input type=checkbox " + classFeature + " name=" + cbSuffix + idFeature;
                else                    // the first Feature is shown without the <hr> separator
                    labelRadio = "<input type=checkbox " + classFeature + " name=" + cbSuffix + idFeature;    
                        
// check box on the left side of the Feature for the selection/deselection of all the related Values                
                labelRadio = labelRadio + " onClick=javascript:SelectDeselectValues('" + cbSuffix + idFeature + "');>"; 
// create check box for the Feature with the related label                
                labelFeature = $(labelRadio + '<span ' + classRadio + '> ' + sName + '</span><br/>');
// append the check box of the Feature to the Features Panel                
                labelFeature.appendTo(boxName);

                aValues = fFeaturesValues.options[i].text.split("#");    // extract all values separated by #
// if a Feature has more then maxValuesFeature, then the Features Panel layout contains two values for each row                 
                if (aValues.length > maxValuesFeature) { 
                    for (j=0; j < aValues.length; j++) {
                        nameCheckOne = idFeature + "_" + j;
                        valueCheckOne = aValues[j];
                        if (j == aValues.length - 1)
                            create_box_double_end(nameCheckOne, valueCheckOne, classRadio, sName, boxName, cbSuffix); 
                        else {
                            idx = j + 1;
                            nameCheckTwo = idFeature + "_" + idx;
                            valueCheckTwo = aValues[idx];
                            create_box_double(nameCheckOne, valueCheckOne, nameCheckTwo, valueCheckTwo, classRadio, sName, boxName, cbSuffix);
                            j = j + 1;
                        }                                                                
                    }
                }
                else {
                    for (j=0; j < aValues.length; j++) {
                        nameCheck = idFeature + "_" + j;
                        valueCheck = aValues[j];
                        create_box_special(nameCheck, valueCheck, classRadio, sName, boxName, cbSuffix); 
                    }
                }                        
                valueFound = true;
                break;
            }
        } 
        if (!valueFound){
            labelFeature = $('<span class=dftTextGrassetto><input type=checkbox name=' + cbSuffix + idFeature + ' value="Yes"> ' + nameFeature + '</span><br/>');
            labelFeature.appendTo(boxName);
        }
    }        
    
    if (whichDiv == "search")
        idx = document.frmSearch.CodeCategory.selectedIndex;
    else 
        idx = document.frmSearch.editorCodeCategory.selectedIndex;

   if (document.frmSearch.process.value == "AN")
        sizeBox = document.frmSearch.boxSizesAN.options[idx].value;
    else
        sizeBox = document.frmSearch.boxSizesAC.options[idx].value;               
        
    sizeContainer = (parseInt(sizeBox) + 30).toString();
    $(containerName).css("height",sizeContainer);
    $(boxName).css("height",sizeBox);
    $(boxName).toggle("slow");
 }
 
 function SelectDeselect(idFeature) {        // check box for selecting/deselecting all subfeature values
    var elementsLength = document.frmSearch.elements.length;
    //fSubFeatures = document.frmSearch.SubFeatures;
    
    lFound = false;
    for (i=0; i < elementsLength; i++) {
        if (frmSearch.elements[i].name == idFeature) {
            fFeature = frmSearch.elements[i];
            lFound = true;
            break;
        }            
    }
    if (lFound) {
       for (j=0; j<fSubFeatures.length; j++)  {
            if (fSubFeatures.options[j].value == fFeature.name) {
                //window.alert("before split #, " + fSubFeatures.options[j].text);
                valuesSubFeatures = fSubFeatures.options[j].text.split("#");
                for (k=0; k < valuesSubFeatures.length; k++) {
                    idSubF = valuesSubFeatures[k].split("@");
                    id = idSubF[1];
                    for (m=0; m < elementsLength; m++) {
                        if (frmSearch.elements[m].name == id) {
                            fSubFeature = frmSearch.elements[m];     
                            break;
                        }
                    }       
                    if (fFeature.checked)
                        fSubFeature.checked = true;
                    else
                        fSubFeature.checked = false;                                                
                }                    
            }                
        }           
    }        
}        

 function SelectDeselectValues(idFeature) {            // check box for selecting/deselecting all feature values
    var elementsLength = document.frmSearch.elements.length;
    var maxValuesFeature = 20;        // there isn't more than 20 values for each feature
    
    lFound = false;
    for (i=0; i < elementsLength; i++) {
        if (frmSearch.elements[i].name == idFeature) {
            fFeature = frmSearch.elements[i];
            lFound = true;
            break;
        }            
    }
    if (lFound) {
        value = fFeature.checked; 
        //window.alert(value); 
        for (j=0; j<maxValuesFeature; j++)  {
            valueFeature = idFeature + "_" + j;
            for (k=0; k < elementsLength; k++) {
                if (frmSearch.elements[k].name == valueFeature) {
                    fValue = frmSearch.elements[k];     
                    fValue.checked = value;
                    break;      
                } 
            }                                           
        }           
    }        
}        
  
/*
*---  EditTool(): creates the form for preparing an update on the tool, uniquely identified by idTool, the form fields are shon in the editor DIV
*
*---  Input: idTool, the unique id of the tool under updating
*
*/
function EditTool(idTool, codeCategory) {
  document.frmSearch.dftRequest.value="edit";
  document.frmSearch.editorIdTool.value=idTool;
  document.frmSearch.editorSelectedCodeCategory.value=codeCategory;
  document.frmSearch.submit();
}  

function CommitUpdatingTool() {
  document.frmSearch.dftRequest.value="showUpdated";
  document.frmSearch.submit();
}

function AddedTool() {
  document.frmSearch.dftRequest.value="showNew";
  document.frmSearch.submit();
}

function EditNewTool() {
  document.frmSearch.dftRequest.value="new";
  document.frmSearch.submit();
}  

function CommitNewTool() {
    
  var fCheck, fReports, fValues, formError;

  formError = true;
  fCheck = document.frmSearch.editorToolName;
  fCheck.value  = fCheck.value.trim();
  

  if (fCheck.value == "") {
    window.alert("Tool name is mandatory");
    formError= false;
  }

  fCheck = document.frmSearch.editorUrl;
  fCheck.value  = fCheck.value.trim();

  if (fCheck.value == "") {
    window.alert("Tool web site is mandatory");
    formError= false;
  }

  fCheck = document.frmSearch.editorCodeCategory;

  if (fCheck.selectedIndex == 0) {
    window.alert("Tool must have a specific Category");
    formError= false;
  }

  fReports = document.frmSearch.editorReports;
  fReferences = document.frmSearch.editorReferences;
  
  fValues  = document.frmSearch.editorReportsValues;
  fValues.value = '';
    
  for(i=0; i<fReports.length; i++)
    fValues.value += fReports.options[i].value + "@";

  fValues  = document.frmSearch.editorReferencesValues;
  fValues.value = '';
    
  for(i=0; i<fReferences.length; i++)
    fValues.value += fReferences.options[i].value + "@";

  //window.alert(fValues.value);

  if (formError) {
    document.frmSearch.dftRequest.value="commitNew";
    document.frmSearch.submit();
  }
}  

/*
*---  UpdateTool(): run an update on the tool, uniquely identified by idTool, the form fields are shon in the editor DIV
*
*---  Input: idTool, the unique id of the tool under updating
*
*/
function UpdateTool() {
    var fReports, fValues, idx;

    document.frmSearch.dftRequest.value="commitUpdate";
  
    fReports = document.frmSearch.editorReports;
    fReferences = document.frmSearch.editorReferences;
    fValues  = document.frmSearch.editorReportsValues;

    fValues.value = '';
    
    for(i=0; i<fReports.length; i++)
        fValues.value += fReports.options[i].value + "@";

    fValues  = document.frmSearch.editorReferencesValues;
    fValues.value = '';
    
    for(i=0; i<fReferences.length; i++)
        fValues.value += fReferences.options[i].value + "@";

    document.frmSearch.submit();
} 

function Approval() {
  document.frmSearch.dftRequest.value="approval";
  document.frmSearch.submit();
}  

function ApprovalSingleTool(idEditorTool) {
  document.frmSearch.dftRequest.value="approvalSingle";
  document.frmSearch.idApprovalTool.value = idEditorTool;
  document.frmSearch.submit();
}

function ApprovalAllTools() {
  document.frmSearch.dftRequest.value="approvalAll";
  document.frmSearch.submit();
}

function ApprovalAllTool() {
  document.frmSearch.dftRequest.value="approvalAll";
  document.frmSearch.submit();
}

function RefusalSingleTool(idEditorTool) {
  document.frmSearch.dftRequest.value="refusalSingle";
  document.frmSearch.idApprovalTool.value = idEditorTool;
  document.frmSearch.submit();
}

function ToolValuesCheckBoxesOnOff(feature, maxValues, arrayToolValues) {
    var i, j, str, cbFeature;
    var numToolValues

    /*
    var elem = document.getElementById('dftForm').elements;
    str = "";

    for(var i = 0; i < elem.length; i++) {
        if (elem[i].type == "checkbox") {
            //str += "<b>Type:</b>" + elem[i].type + "&nbsp&nbsp";
            str += "<b>Name:</b>" + elem[i].name + "&nbsp;&nbsp;";
            str += "<b>Value:</b>" + elem[i].value + "&nbsp;&nbsp;";
            str += "<BR>";
        }
    } 
    window.alert(str);
    */
   
    numToolValues = arrayToolValues.length;
    //window.alert("ToolValuesCheckBoxesOnOff, maxValues=" + maxValues + ", numToolValues=" + numToolValues);

    for (i=0; i<maxValues; i++) {
        cbFeature = eval("document.frmSearch.editor" + feature.toString() + "_" + i);
        cbFeature.checked = false;
        for (j=0; j<numToolValues; j++){
            if (cbFeature.value == arrayToolValues[j]) {
                cbFeature.checked = true;
                break;
            }
        }
    }   
}

/*
*---  Search(): run a query on the Catalogue, the results are shown in the editor DIV
*
*/

function Search() {
    document.frmSearch.dftRequest.value="query";
    /*
    idx = document.frmSearch.CodeCategory.selectedIndex;
    if (idx == 0) {                     // Category is set on All, it is not allowed
        window.alert("Please, select a Category");
        return false;
    }
    */
    document.frmSearch.submit();
}

function showStatusBar() {
    $('#ajax-status-message').html(msg).addClass('success-notice').show().delay(50000).fadeOut();
}

/*
*---  NewTool(): prepare the form for adding a new tool, the fields are shown in the editor DIV
*
*/

function NewTool() {
  document.frmSearch.dftRequest.value="new";
  document.frmSearch.submit();
}

/*
// Functions for managing the editor DIV
*/

/*
*--- editorCheckCategory(): sets and shows the Features Panel on the basis of the onChange event of the Category SELECT form field
*
*--- Input: divSource: represents the DIV element from which the request is sent: possible values are DIV=search or DIV=editor
*/

function editorCheckCategory() {
    fCategoryName         = document.frmSearch.editorCategory;
    fCategories           = document.frmSearch.editorCodeCategory; 
    
    idx = fCategories.selectedIndex;
    CodeCategory = fCategories.options[idx].value;

    aBannedCategories = document.frmSearch.CodeCategoriesBanned.value.split("@");
    banned = false;
    //window.alert("banned values:" + document.frmSearch.CodeCategoriesBanned.value);
    
    for (k=0; k<aBannedCategories.length; k++) {
        if (aBannedCategories[k].trim() == "")
            continue;

        if (CodeCategory == aBannedCategories[k]) {
            msg = "The current tool has already Features in the selected Category, ";
            msg += "for the updating, please search the Tool in that Category and select it for editing";
            window.alert(msg);
            banned = true;
            fCategories.value = document.frmSearch.editorDbCodeCategory.value;
            break;
        }
    }   
    if (!banned)
        CheckGeneral(fCategoryName,fCategories, "editor");
 }

/*
*--- SubtractTestUrl(): in the editing phase, delete a Test Report item from the Reports list
*
*/

function SubtractTestUrl() {
    var idx, fReports;

    fReports = document.frmSearch.editorReports;

    idx = fReports.selectedIndex;

    if (idx >= 0)
        fReports.remove(idx);
    else
      window.alert("No Report Test selected!");
}

function AddTestUrl() {
    var idx, fNote, fReports, fUrl;
    
    fReports = document.frmSearch.editorReports;
    fUrl = document.frmSearch.editorTestUrl;
    fNote = document.frmSearch.editorTestNote;

    fUrl.value = fUrl.value.replace(" ", "");    // rule out all blank spaces

    if (fUrl.value == "") {
        window.alert("Error: Test Url field is required!")
        return false;
    }

    fReports.length++;
    fReports.options[fReports.length - 1].value  = fUrl.value + "|" + fNote.value;
    fReports.options[fReports.length - 1].text  = fUrl.value + "|" + fNote.value;
    fUrl.value = "";
    fNote.value = "";
}

function ExtractTestUrl() {
    var idx, fNote, fReports, fUrl, reportValues;
    
    fReports = document.frmSearch.editorReports;
    fUrl = document.frmSearch.editorTestUrl;
    fNote = document.frmSearch.editorTestNote;

    idx = fReports.selectedIndex;

    if (idx > -1) {
        reportValues = fReports.options[idx].value.split("|");
        fUrl.value = reportValues[0];
        fNote.value = reportValues[1];
        //window.alert("Url: " + reportValues[0] + ", Note: " + reportValues[1]);
    }
}

function ExtractReferences() {
    var idx, fNote, fReports, fUrl, reportValues;
    
    fReferences = document.frmSearch.editorReferences;
    fUrl = document.frmSearch.editorReferenceUrl;
    fNote = document.frmSearch.editorReferenceNote;

    idx = fReferences.selectedIndex;

    if (idx > -1) {
        referenceValues = fReferences.options[idx].value.split("|");
        fUrl.value  = referenceValues[0];
        fNote.value = referenceValues[1];
        //window.alert("Url: " + reportValues[0] + ", Note: " + reportValues[1]);
    }

}

/*
*--- SubtractRefsUrl(): in the editing phase, delete a Refrence Url item from the Useful References list
*
*/

function SubtractReferenceUrl() {
    var idx, fReferences;

    fReferences = document.frmSearch.editorReferences;

    idx = fReferences.selectedIndex;

    if (idx >= 0)
        fReferences.remove(idx);
    else
      window.alert("No Reference has been selected!");
}

function AddReferenceUrl() {
    var idx, fNote, fReferences, fUrl;
    
    fReferences = document.frmSearch.editorReferences;
    fUrl = document.frmSearch.editorReferenceUrl;
    fNote = document.frmSearch.editorReferenceNote;

    fUrl.value = fUrl.value.replace(" ", "");    // rule out all blank spaces

    if (fUrl.value == "") {
        window.alert("Error: Reference Url field is required!")
        return false;
    }

    fReferences.length++;
    fReferences.options[fReferences.length - 1].value  = fUrl.value + "|" + fNote.value;
    fReferences.options[fReferences.length - 1].text  = fUrl.value + "|" + fNote.value;
    fUrl.value = "";
    fNote.value = "";
}

function ExtractReferenceUrl() {
    var idx, fNote, fReports, fUrl, reportValues;
    
    fReferences = document.frmSearch.editorReferences;
    fUrl = document.frmSearch.editorRefrenceUrl;
    fNote = document.frmSearch.editorReferenceNote;

    idx = fReferences.selectedIndex;

    if (idx > -1) {
        referenceValues = fReferences.options[idx].value.split("|");
        fUrl.value = referenceValues[0];
        fNote.value = referenceValues[1];
        //window.alert("Url: " + reportValues[0] + ", Note: " + reportValues[1]);
    }
}

/*
// Functions for managing the results of a query
*/

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
    fOsSearch = document.frmSearch.os;
    
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
    fCategory = frmSearch.CodeCategory;
    
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
    
    CheckCategory();
    
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

/*
// Functions for managing the Features Panel
*/

function alterna_box () {
	$("#box").toggle("slow");
//	      $("#box").hide("slow")
}

function create_box (nameCheck, valueCheck, boxName, cbSuffix) {
	cb01 = $('<input type=checkbox checked name=' + cbSuffix + nameCheck + ' value="' + valueCheck + '">' + valueCheck + '<br/>')
    cb01.appendTo(boxName);
}

function create_box_special (nameCheck, valueCheck, classRadio, sLabel, boxName, cbSuffix) {
    if (sLabel == "") // if Feature is not visible, in this case classRadio is class=dftHidden
        cb01 = $('<input type=checkbox ' + classRadio + ' name=' + cbSuffix + nameCheck + ' value="' + valueCheck + '">')
    else
        cb01 = $('<input type=checkbox ' + classRadio + ' name=' + cbSuffix + nameCheck + ' value="' + valueCheck + '">' + valueCheck + '<br/>')        
    //cb01.appendTo("#box");
    cb01.appendTo(boxName);
}

function create_box_double (nameCheck1, valueCheck1, nameCheck2, valueCheck2, classRadio, sLabel, boxName, cbSuffix) {
    if (sLabel == "") {
        cbOne = '<tr><td><input type=checkbox ' + classRadio + ' name=' + cbSuffix + nameCheck1 + ' value="' + valueCheck1 + '"></td>';
        cbTwo = '<td><input type=checkbox ' + classRadio + ' name=' + cbSuffix + nameCheck2 + ' value="' + valueCheck2 + '"></td></tr>' ; 
    }        
    else {
        cbOne = '<tr><td><input type=checkbox ' + classRadio + ' name=' + cbSuffix + nameCheck1 + ' value="' + valueCheck1 + '">' + valueCheck1 + '</td>';
        cbTwo = '<td><input type=checkbox ' + classRadio + ' name=' + cbSuffix + nameCheck2 + ' value="' + valueCheck2 + '">' + valueCheck2 + '</td></tr>';
    }                
    
    cbDouble = $(cbOne + cbTwo)        
    cbDouble.appendTo(boxName);
}

function create_box_double_end (nameCheck, valueCheck, classRadio, sLabel, boxName, cbSuffix) {
    if (sLabel == "")
        cb01 = $('<tr><td colspan=2><input type=checkbox ' + classRadio + ' name=' + cbSuffix + nameCheck + ' value="' + valueCheck + '"></td></tr>')
    else
        cb01 = $('<tr><td colspan=2><input type=checkbox ' + classRadio + ' name=' + cbSuffix + nameCheck + ' value="' + valueCheck + '">' + valueCheck + '</td></tr>')  
    cb01.appendTo(boxName);
}


function create_box_bold (nameCheck, valueCheck, boxName, cbSuffix) {
	cb01 = $("<hr><strong><input type=checkbox   onClick=javascript:SelectDeselect('" + cbSuffix + nameCheck + "'); name=" + nameCheck + ' value="' + valueCheck + '">' + valueCheck + '</strong><br/>')
    cb01.appendTo(boxName);
}

function create_box_subfeatures (aName, aValue, boxName) {
    s = '<input type=checkbox    name=' + aName[0] + ' value="'  + aValue[0] + '">' + aValue[0];
    s += '<input type=checkbox  name=' + aName[1] + ' value="' + aValue[1] + '">' + aValue[1]; 
    s += '<input type=checkbox  name=' + aName[2] + ' value="' + aValue[2] + '">' + aValue[2] + '<br/>'; 
	cb01 = $(s)
    cb01.appendTo(boxName); 
}

function removePanel(boxName) {
    $(boxName).remove() 
}

function addPanel() {
   divBox = $('<div id="box">Box con nuovo testo</div>');
    divBox.appendTo("#container");
   $("#box").toggle("slow");
        
}

$(function() {
    $( "#radio" ).buttonset();   
//    $(".dftTextRadio label").css("color","green");
//    $(".dftTextRadio label").css("background-image","none");
  });  


