<?php include('views/dfte._header.php'); ?>
<form name="frmSearch" id="dftForm" method="post" action="dfte.index.php">
<div id="containerEditor">
	<div id="login">
		<?php require_once("views/dfte.logged_in.php"); ?>
	</div>
	<div id="search">
		<?php require_once("views/dfte.search.php"); ?>
	</div>
<?php
// include the PHPMailer library for sending notice of all modificaitons proposed for the Catalogue
	require_once('libraries/PHPMailer.php');	
?>	
	<div id="editor">
		<?php require_once("views/dfte.editor.php"); ?>	
	</div>
</div>
</form>

<?php include('views/dfte._footer.php'); ?>
