<?php
  include('views/dfte._header.php'); 
?>

<form method="post" action="dfte.index.php" name="loginform"><br/>
    <label for="user_name" class="dftTextGrassetto"><?php echo WORDING_USERNAME; ?></label><br/>
    <input class="dftText" id="user_name" type="text" name="user_name" required /><br/><br/>
    <label for="user_password" class="dftTextGrassetto"><?php echo WORDING_PASSWORD; ?></label><br/>
    <input class="dftText" id="user_password" type="password" name="user_password" autocomplete="off" required />
    <br/><br/>
    <input type="checkbox" checked style="display:none"  id="user_rememberme" name="user_rememberme" value="1" /><br/>
    <!--label for="user_rememberme"><?php echo WORDING_REMEMBER_ME; ?></label><br/-->
    <input type="submit" name="login" value="<?php echo WORDING_LOGIN; ?>" /><br/>
</form>

<a href="dfte.register.php"><?php echo WORDING_REGISTER_NEW_ACCOUNT; ?></a><br/><br/>
<a href="dfte.password_reset.php"><?php echo WORDING_FORGOT_MY_PASSWORD; ?></a>

<?php include('views/dfte._footer.php'); ?>
