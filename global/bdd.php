<?php
    session_start();

    // Liaison avec la base de donnée
    try{
        $bdd = new PDO('mysql:host=localhost;dbname=Blog;charset=utf8', 'root', 'biscuit2');
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(Exception $e){
        die('Erreur : '.$e->getMessage());
    }

?>
