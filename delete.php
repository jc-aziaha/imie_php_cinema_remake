<?php
session_start();

    // 1- Si l'identifiant du film n'existe pas dans $_GET
    if ( !isset($_GET['film_id']) || empty($_GET['film_id']) ) 
    {
        // Effectuer une redirection vers la page d'accueil
            // Puis arrêter l'exécution du script
        return header("Location: index.php");
    }

    
    // 2- Dans le cas contraire,
    // Récupérer l'identifiant tout en prenant de protéger le serveur contre les failles de type XSS 
    // puis le convertir en entier
    $filmId = (int) htmlspecialchars($_GET['film_id']);


    // 3- Etablir une connexion avec la base de données
    require __DIR__ . "/db/connexion.php";

    // 4- Effectuer la requête afin de vérifier si l'identifiant du film récupéré 
        // correspondant à un film qui existe vraiment dans la base
    $request = $db->prepare("SELECT * FROM film WHERE id=:id LIMIT 1");
    $request->bindValue(":id", $filmId);
    $request->execute();

    // 5- Si ce n'est pas le cas, 
        // Effectuer une redirection vers la page d'accueil
            // Puis arrêter l'exécution du script
    if ( $request->rowCount() != 1 ) 
    {
        // Effectuer une redirection vers la page d'accueil
            // Puis arrêter l'exécution du script
        return header("Location: index.php");
    }

    // 6- Dans le cas contraire, récupérons le film à modifier
    $film = $request->fetch();


    // 7- Effectuer une seconde requête afin de  supprimer le film de la base de données
    $deleteRequest = $db->prepare("DELETE FROM film WHERE id=:id");
    $deleteRequest->bindValue(":id", $film['id']);
    $deleteRequest->execute();

    $deleteRequest->closeCursor();

    return header("Location: index.php");