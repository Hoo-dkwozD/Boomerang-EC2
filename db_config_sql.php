<?php
    // database config details
    $host = 'localhost';
    $dbname = 'BoomerangElectronics';
    $username = 'root';
    $password = '';

    // new PDO object - represents connection to database
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // setAttribute to throw exceptions instead of simply returning false or showing warning
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>