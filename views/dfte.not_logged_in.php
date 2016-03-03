<?php
  include('views/dfte._header.php'); 
?>

<form method="post" action="dfte.index.php" name="loginform"><br/>
    <label for="user_name"><?php echo WORDING_USERNAME; ?></label><br/>
    <input id="user_name" type="text" name="user_name" required /><br/>
    <label for="user_password"><?php echo WORDING_PASSWORD; ?></label><br/>
    <input id="user_password" type="password" name="user_password" autocomplete="off" required /><br/>
    <input type="checkbox" id="user_rememberme" name="user_rememberme" value="1" /><br/>
    <label for="user_rememberme"><?php echo WORDING_REMEMBER_ME; ?></label><br/>
    <input type="submit" name="login" value="<?php echo WORDING_LOGIN; ?>" /><br/>
</form>

<a href="dfte.register.php"><?php echo WORDING_REGISTER_NEW_ACCOUNT; ?></a>
<a href="dfte.password_reset.php"><?php echo WORDING_FORGOT_MY_PASSWORD; ?></a>

<?php include('views/dfte._footer.php'); ?>
