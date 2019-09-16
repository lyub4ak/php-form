<?php

include('captcha'.DIRECTORY_SEPARATOR.'CaptchaVerify.php');

// Trims all values in $_POST.
foreach ($_POST as &$x_value)
    $x_value = trim($x_value);
unset($x_value);

$a_errors = check_form();
if(!$a_errors)
    $a_errors = save();

echo json_encode([
    'a_errors' => $a_errors,
    'html_name' => htmlspecialchars($_POST['name']), // XSS protection.
]);

/**
 * Checks form fields.
 *
 * @return array Array of errors.
 */
function check_form() {
    // Errors of form check.
    $a_errors = [];

    // Checks captcha.
    $o_captcha_verify = new CaptchaVerify();
    if (!$o_captcha_verify->verify_code())
        $a_errors[] = $o_captcha_verify->text_error;

    // Checks required fields.
    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['message']))
        $a_errors[] = 'Заполнены не все поля формы.';

    // Check length of fields.
    if (
        iconv_strlen($_POST['name']) > 255 || // Maximum characters for type "TINYTEXT" in MySQL.
        iconv_strlen($_POST['email']) > 255 ||
        iconv_strlen($_POST['message']) > 65535 // Maximum characters for type "TEXT" in MySQL.
    ) {
        $a_errors[] = 'В некоторых полях превышено допустимое количество символов';
    }

    // Checks email.
    if (
        !preg_match(
            '/^[A-Za-z0-9]+([\._A-Za-z0-9-]+)*@[A-Za-z0-9]+(\.[A-Za-z0-9]+)*(\.[A-Za-z]{2,})$/',
            $_POST['email']
        )
    ) {
        $a_errors[] = 'Не корректный E-mail.';
    }

    return $a_errors;
}

/**
 * Adds data from form to database.
 *
 * @return array Array of errors if errors are.
 */
function save() {
    $text_servername = "php-form.local";
    $text_database = "php-form";
    $text_username = "root";
    $text_password = "";
    $text_sql = "mysql:host=$text_servername;dbname=$text_database;";
    $a_dsn_Options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    $a_errors = [];

    // Creates a new connection to the MySQL database using PDO, $o_db_connection is an object
    try {
        $o_db_connection = new PDO($text_sql, $text_username, $text_password, $a_dsn_Options);
        // Connected successfully.
    } catch (PDOException $error) {
        $a_errors[] = 'Connection error: ' . $error->getMessage();
        return $a_errors;
    }

    // Prepares SQL query.
    $o_query = $o_db_connection->prepare("
        INSERT INTO 
            contact (text_name, text_email, text_message, dtu_create)
        VALUES
            (:text_name, :text_email, :text_message, NOW())
    ");
    // Binds data to query.
    $o_query->bindParam(':text_name', $_POST['name']);
    // All emails will be saved in database in lower chars.
    $o_query->bindParam(':text_email', mb_strtolower($_POST['email']));
    $o_query->bindParam(':text_message', $_POST['message']);

    // Executes the query using the data we just defined.
    if (!$o_query->execute())
       $a_errors[] = "Unable to create record.";

    return $a_errors;
}
