<?php

//include('views/dfte._header.php');

// if you need the user's information, just put them into the $_SESSION variable and output them here
echo WORDING_YOU_ARE_LOGGED_IN_AS . htmlspecialchars($_SESSION['user_name']) . "<br />";
//echo WORDING_PROFILE_PICTURE . '<br/><img src="' . $login->user_gravatar_image_url . '" />;
// echo WORDING_PROFILE_PICTURE . '<br/>' . $login->user_gravatar_image_tag;
?>

<div>
    <a href="dfte.login.php?logout"><?php echo WORDING_LOGOUT; ?></a> | 
    <a class="dftLink" href="dfte.edit.php"><?php echo WORDING_EDIT_USER_DATA; ?></a>
<?php
	/*
	$qryUserRole  = "SELECT user_role FROM tblUsers WHERE user_name=:user_name";
	$stmt = $db_conn->prepare($qryUserRole);
	$stmt->bindParam(':user_name', $_SESSION['user_name'], PDO::PARAM_STR); 
    $stmt->execute();
    $rowUserRole = $stmt->fetch();
    */

	if ($_SESSION["user_role"] == 'admin')
    	echo ' | <a class="dftLink" href="javascript:Approval();">' . WORDING_APPROVAL_EDITING . '</a>';
?>
</div>
<?php
    include('views/dfte._footer.php');
?>    

