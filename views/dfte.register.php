<?php 
    include('views/dfte._header.php'); 
// include the to-be-used language, english by default. feel free to translate your project and include something else
    require_once('translations/en.php');    

?>

<!-- show registration form, but only if we didn't submit already -->
<?php if (!$registration->registration_successful && !$registration->verification_successful) { ?>
<form method="post" action="dfte.register.php" name="registerform"><br/>
    <label for="user_name"><?php echo WORDING_REGISTRATION_USERNAME; ?></label><br/>
    <input id="user_name" type="text" pattern="[a-zA-Z0-9]{2,64}" name="user_name" required /><br/>

    <label for="user_email"><?php echo WORDING_REGISTRATION_EMAIL; ?></label><br/>
    <input id="user_email" type="email" name="user_email" required /><br/>

    <label for="user_password_new"><?php echo WORDING_REGISTRATION_PASSWORD; ?></label><br/>
    <input id="user_password_new" type="password" name="user_password_new" pattern=".{6,}" required autocomplete="off" /><br/>

    <label for="user_password_repeat"><?php echo WORDING_REGISTRATION_PASSWORD_REPEAT; ?></label><br/>
    <input id="user_password_repeat" type="password" name="user_password_repeat" pattern=".{6,}" required autocomplete="off" /><br/>

    <img src="tools/showCaptcha.php" alt="captcha" /><br/>

    <label><?php echo WORDING_REGISTRATION_CAPTCHA; ?></label><br/>
    <input type="text" name="captcha" required /><br/>

    <input type="submit" name="register" value="<?php echo WORDING_REGISTER; ?>" /><br/>
</form>
<?php } ?>

    <a href="dfte.login.php"><?php echo WORDING_BACK_TO_LOGIN; ?></a>

<?php include('views/dfte._footer.php'); ?>
