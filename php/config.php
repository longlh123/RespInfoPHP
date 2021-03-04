<?php
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'respondents_panel');

    /*Attempt to connect to MySql database*/
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    /*Check connet*/
    if($conn === false)
    {
        die("ERROR: Count not connect. " .mysqli_connect_error());
    }
?>
