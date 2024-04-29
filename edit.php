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

    // Traitement des données par le serveur
    // 1- Si la méthode d'envoi des données est POST
    if ( "POST" === $_SERVER['REQUEST_METHOD'] )
    {

        $postClean  = [];
        $formErrors = [];

        // 2- Pensons automatiquement à la cyber-sécurité
        // Protéger le serveur contre les failles de type XSS
        foreach ($_POST as $key => $value) 
        {
            $postClean[$key] = htmlspecialchars(trim($value));
        }

        if ( "PUT" !== $postClean['_method'] )
        {
            // Effectuons une redirection vers la page de laquelle proviennent les données
            // Puis arrêtons l'exécution du script
            return header("Location: " . $_SERVER['HTTP_REFERER']);
        }    

        // Protéger le serveur contre les failles de type CSRF
        if ( !isset($postClean['_csrf_token']) || !isset($_SESSION['_csrf_token']) ) 
        {
            // Effectuons une redirection vers la page de laquelle proviennent les données
            // Puis arrêtons l'exécution du script
            return header("Location: " . $_SERVER['HTTP_REFERER']);
        }

        if ( empty($postClean['_csrf_token']) || empty($_SESSION['_csrf_token']) ) 
        {
            // Effectuons une redirection vers la page de laquelle proviennent les données
            // Puis arrêtons l'exécution du script
            return header("Location: " . $_SERVER['HTTP_REFERER']);
        }

        if ( $postClean['_csrf_token'] !==  $_SESSION['_csrf_token'] ) 
        {
            // Effectuons une redirection vers la page de laquelle proviennent les données
            // Puis arrêtons l'exécution du script
            return header("Location: " . $_SERVER['HTTP_REFERER']);
        }

        unset($_SESSION['_csrf_token']);
        
        // Protéger le serveur contre les robots spameurs (HoneyPot)
        if ( !isset($postClean['_honey_pot']) || !empty($postClean['_honey_pot']) )
        {
            // Effectuons une redirection vers la page de laquelle proviennent les données
            // Puis arrêtons l'exécution du script
            return header("Location: " . $_SERVER['HTTP_REFERER']);
        }
        
        // 3- Définissons les contraintes de validation
        if ( isset($postClean['name']) ) 
        {
            if ( empty($postClean['name']) ) 
            {
                $formErrors['name'] = "Le nom du film est obligatoire.";
            }
            else if (mb_strlen($postClean['name']) > 255) 
            {
                $formErrors['name'] = "Le nom du film doit contenir au maximum 255 caractères.";
            }
        }

        if ( isset($postClean['actors']) )
        {
            if ( empty($postClean['actors']) ) 
            {
                $formErrors['actors'] = "Le nom du/des acteurs est obligatoire.";
            }
            else if (mb_strlen($postClean['actors']) > 255) 
            {
                $formErrors['actors'] = "Le nom du/des acteurs doit contenir au maximum 255 caractères.";
            }
        }
        
        if ( isset($postClean['review']) )
        {
            if ( ! empty($postClean['review']) ) 
            {
                if ( ! is_numeric($postClean['review']) ) 
                {
                    $formErrors['review'] = "La note  du film doit être un nombre.";
                }
                else if ( $postClean['review'] < "0" || $postClean['review'] > "5" ) 
                {
                    $formErrors['review'] = "La note  du film doit être comprise entre 0 et 5.";
                }
            }
        }

        if ( isset($postClean['comment']) )
        {
            if ( ! empty($postClean['comment']) ) 
            {
                if (mb_strlen($postClean['name']) > 500) 
                {
                    $formErrors['comment'] = "Le commentaire ne doit pas dépasser 500 caractères.";
                }
            }
        }
        
        // 4- Si les données sont invalides
        if ( count($formErrors) > 0 ) 
        {

            // Sauvegardons en session les anciennes données du formulaire
            $_SESSION['old'] = $postClean;

            // Sauvegardons le tableau des erreurs en session
            $_SESSION['form_errors'] = $formErrors;

            // Effectuons une redirection vers la page de laquelle proviennent les données
            // Puis arrêtons l'exécution du script
            return header("Location: " . $_SERVER['HTTP_REFERER']);
            
        }
        
        
        // Dans le cas contraire
        // 5- Arrondir la note du film à un chiffre après la virgule
        if ( isset($postClean['review']) && $postClean['review'] !== "" ) 
        {
            $reviewRounded = round($postClean['review'], 1);
        }
        
        // 6- Etablir une connexion avec la base de données
        require __DIR__ . "/db/connexion.php";
        
        // 7- Effectuer la requête d'insertion du nouveau film dans la table prévue pour.
        $request = $db->prepare("UPDATE film SET name=:name, actors=:actors, review=:review, comment=:comment, updated_at=now() WHERE id=:id");

        $request->bindValue(":name", $postClean['name']);
        $request->bindValue(":actors", $postClean['actors']);
        $request->bindValue(":review", isset($reviewRounded) ? $reviewRounded : NULL);
        $request->bindValue(":comment", $postClean['comment']);
        $request->bindValue(":id", $film['id']);

        $request->execute();

        $request->closeCursor();

        // 8- Effectuer une redirection vers la page d'accueil (liste des films)
        // Puis arrêtons l'exécution du script
        return header("Location: index.php");
    }

    // Générons une chaîne de cacractères aléatoire qui est le jéton de sécurité (token)
        // et sauvegardons-le en session
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(30));
?>
<?php
    $title = "Modifier ce nouveau film";
    $description = "Modifier ce film de la liste.";
    $keywords = "imie, php, modification de film";
?>
<?php require __DIR__ . "/partials/head.php"; ?>

    <?php require __DIR__ . "/partials/nav.php"; ?>
    
    <!-- Le contenu spécifique à cette page -->
    <main class="container">
        <h1 class="text-center my-3 display-5">Modifier ce film</h1>

        <div class="container my-3">
            <div class="row">
                <div class="col-md-8 col-lg-4 mx-auto shadow p-4 bg-white">

                    <?php if( isset($_SESSION['form_errors']) && !empty($_SESSION['form_errors']) ) : ?>
                        <div class="alert alert-danger" role="alert">
                            <ul>
                                <?php foreach($_SESSION['form_errors'] as $error) : ?>
                                    <li><?= $error; ?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['form_errors']); ?>
                    <?php endif ?>

                    <form method="post">
                        <div class="mb-3">
                            <label title="Le nom du film est obligatoire" for="name">Nom du film <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" autofocus value="<?= isset($_SESSION['old']['name']) ? $_SESSION['old']['name'] : $film['name']; unset($_SESSION['old']['name']); ?>">
                        </div>
                        <div class="mb-3">
                            <label title="Le nom du ou des acteurs est obigatoire" for="actors">Nom du/des acteur(s) <span class="text-danger">*</span></label>
                            <input type="text" name="actors" id="actors" class="form-control" value="<?= isset($_SESSION['old']['actors']) ? $_SESSION['old']['actors'] : $film['actors']; unset($_SESSION['old']['actors']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="review">La note du film / 5</label>
                            <input type="number" step="0.1" min="0" max="5" name="review" id="review" class="form-control" value="<?= isset($_SESSION['old']['review']) ? $_SESSION['old']['review'] : $film['review']; unset($_SESSION['old']['review']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="comment">Laissez un commentaire</label>
                            <textarea name="comment" id="comment" class="form-control" rows="4"><?= isset($_SESSION['old']['comment']) ? $_SESSION['old']['comment'] : $film['comment']; unset($_SESSION['old']['comment']); ?></textarea>
                        </div>
                        <input type="hidden" name="_csrf_token" value="<?= $_SESSION['_csrf_token']; ?>">
                        <input type="hidden" name="_honey_pot" value="">
                        <input type="hidden" name="_method" value="PUT">
                        <div>
                            <input formnovalidate type="submit" class="btn btn-primary shadow" value="Modifier">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <?php require __DIR__ . "/partials/footer.php"; ?>

<?php require __DIR__ . "/partials/foot.php"; ?>



    