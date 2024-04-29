<?php
    // Etablir une connexion avec la abse de données
    require __DIR__ . "/db/connexion.php";

    // Effectuer la requête de selection des données de la table "film"
    $request = $db->prepare("SELECT * FROM film ORDER BY created_at DESC");
    $request->execute();
    $films = $request->fetchAll();
    $request->closeCursor();
?>
<?php
    $title = "Liste des films";
    $description = "La liste des films que je kif.";
    $keywords = "imie, php, films, cinema";
?>
<?php require __DIR__ . "/partials/head.php"; ?>

    <?php require __DIR__ . "/partials/nav.php"; ?>
    
    <!-- Le contenu spécifique à cette page -->
    <main class="container">
        <h1 class="text-center my-3 display-5">Liste des films</h1>

        <div class="d-flex justify-content-end align-items-center my-3">
            <a href="create.php" class="btn btn-primary shadow">Ajouter film</a>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-md-7 col-lg-5 mx-auto">
                    <?php foreach($films as $film) : ?>
                        <div class="film-card my-3 shadow p-3 border bg-white">
                            <p><strong>Le nom du film</strong> : <?= $film['name']; ?></p>
                            <p><strong>Le/les acteurs</strong> : <?= $film['actors']; ?></p>
                            <hr>
                            <a href="" class="text-dark mx-2"><i class="fa-solid fa-eye"></i></a>
                            <a title="Modifier le film: <?= $film['name'] ?>" href="edit.php?film_id=<?=$film['id']?>" class="text-secondary mx-2"><i class="fa-solid fa-pen-to-square"></i></a>
                            <a href="" class="text-danger mx-2"><i class="fa-solid fa-trash-can"></i></a>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php require __DIR__ . "/partials/footer.php"; ?>

<?php require __DIR__ . "/partials/foot.php"; ?>