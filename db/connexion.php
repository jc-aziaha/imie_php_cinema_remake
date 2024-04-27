<?php

    $dsn = 'mysql:dbname=imie_cinema_remake;host=127.0.0.1';
    $user = 'root';
    $password = '';

    try 
    {
        $db = new PDO($dsn, $user, $password);
    } 
    catch (\PDOException $e) 
    {
        die("Error: {$e->getMessage()}");
    }