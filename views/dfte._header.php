<html>
<head>
<title>EVIDENCE project: Digital Forensics Tools Editor</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" href="./scripts/reset.css" type="text/css" />
<link rel="stylesheet" href="./scripts/base.css" type="text/css" />
<link rel="stylesheet" href="./scripts/jquery-ui.css" type="text/css" />
<link rel="stylesheet" href="./scripts/font-awesome.css" type="text/css" />
<link rel="stylesheet" href="./scripts/buttons.css" type="text/css" />
<script type="text/javascript" src="./scripts/jquery-1.11.1.js"></script>
<script type="text/javascript" src="./scripts/jquery-ui.js"></script>
<script type="text/javascript" src="./scripts/buttons.js"></script>
<script type="text/javascript" src="./scripts/dfte.js"></script>
<link rel="stylesheet" href="./scripts/dfte.css" type="text/css"> 
</head>
<body>
<a target="Evidence web site" href="http://www.evidenceproject.eu">
<img div="logo" src="./images/dfte.evidence.logo.png" alt="EVIDENCE project" border="0" />
</a><br/>

<?php

// show potential errors / feedback (from login object)
if (isset($login)) {
    if ($login->errors) {
        foreach ($login->errors as $error) {
            echo $error;
        }
    }
    if ($login->messages) {
        foreach ($login->messages as $message) {
            echo $message;
        }
    }
}
?>

<?php
// show potential errors / feedback (from registration object)
if (isset($registration)) {
    if ($registration->errors) {
        foreach ($registration->errors as $error) {
            echo $error;
        }
    }
    if ($registration->messages) {
        foreach ($registration->messages as $message) {
            echo $message;
        }
    }
}
?>
