<?php include('views/dfte._header.php'); ?>

<?php if ($login->passwordResetLinkIsValid() == true) { ?>
<form method="post" action="dfte.password_reset.php" name="new_password_form">
    <input type='hidden' name='user_name' value='<?php echo htmlspecialchars($_GET['user_name']); ?>' />
    <input type='hidden' name='user_password_reset_hash' value='<?php echo htmlspecialchars($_GET['verification_code']); ?>' />

    <label for="user_password_new"><?php echo WORDING_NEW_PASSWORD; ?></label><br/>
    <input id="user_password_new" type="password" name="user_password_new" pattern=".{6,}" required autocomplete="off" />

    <label for="user_password_repeat"><?php echo WORDING_NEW_PASSWORD_REPEAT; ?></label><br/>
    <input id="user_password_repeat" type="password" name="user_password_repeat" pattern=".{6,}" required autocomplete="off" />
    <input type="submit" name="submit_new_password" value="<?php echo WORDING_SUBMIT_NEW_PASSWORD; ?>" />
</form>
<!-- no data from a password-reset-mail has been provided, so we simply show the request-a-password-reset form -->
<?php } else { ?>
<form method="post" action="dfte.password_reset.php" name="password_reset_form">
    <label for="user_name"><?php echo WORDING_REQUEST_PASSWORD_RESET; ?></label><br/>
    <input id="user_name" type="text" name="user_name" required />
    <input type="submit" name="request_password_reset" value="<?php echo WORDING_RESET_PASSWORD; ?>" />
</form>
<?php } ?>

<a href="dfte.login.php"><?php echo WORDING_BACK_TO_LOGIN; ?></a>

<?php include('views/dfte._footer.php'); ?>
