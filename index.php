<?php

include('captcha'.DIRECTORY_SEPARATOR.'CaptchaField.php');

$captcha_field = new CaptchaField();
// Generates name for captcha verify input.
$captcha_code = $captcha_field->generate_code();

include_once('form.html');