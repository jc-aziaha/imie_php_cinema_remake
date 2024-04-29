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
                            <p><strong>Le nom du film</strong> : <?= htmlspecialchars($film['name']); ?></p>
                            <p><strong>Le/les acteurs</strong> : <?= htmlspecialchars($film['actors']); ?></p>
                            <hr>
                            <a data-bs-toggle="modal" data-bs-target="#modal_<?=htmlspecialchars($film['id']);?>" href="" class="text-dark mx-2"><i class="fa-solid fa-eye"></i></a>

                            <a title="Modifier le film: <?= htmlspecialchars($film['name']); ?>" href="edit.php?film_id=<?=$film['id']?>" class="text-secondary mx-2"><i class="fa-solid fa-pen-to-square"></i></a>
                            <a 
                                onclick="event.preventDefault(); return confirm('Confirmer la supression?') && document.querySelector('#film_delete_form_<?= htmlspecialchars($film['id']); ?>').submit();" 
                                title="Supprimer le film: <?= htmlspecialchars($film['name']) ?>" 
                                href="#" 
                                class="text-danger mx-2"
                            >
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                            <form action="delete.php?film_id=<?=$film['id']?>" method="post" class="d-none" id="film_delete_form_<?= $film['id']; ?>">
                                <input type="hidden" name="_csrf_token" value="<?= $_SESSION['_csrf_token']; ?>">
                                <input type="hidden" name="_honey_pot" value="">
                                <input type="hidden" name="_method" value="DELETE">
                            </form>
                        </div>
                        <!-- Modal -->
                        <div class="modal fade" id="modal_<?=$film['id'];?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel"><?= htmlspecialchars($film['name']) ?></h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Acteurs</strong>: <?=htmlspecialchars($film['actors']);?>
                                    <p><strong>Note</strong>: <?= isset($film['review']) && $film['review'] != '' ? htmlspecialchars($film['review']) : 'non renseignée'; ?>
                                    <p><strong>Commentaire</strong>: <?= isset($film['comment']) && $film['comment'] != '' ? nl2br(htmlspecialchars($film['comment'])) : 'non renseigné'; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                </div>
                                </div>
                            </div>
                            </div>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php require __DIR__ . "/partials/footer.php"; ?>

<?php require __DIR__ . "/partials/foot.php"; ?>