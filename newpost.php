<?php
session_start();
include("global/header.php"); 
// Si l'utilisateur est connecté :
if(isset($_SESSION['id'])){
?>
    <body> 
        <!-- Titre -->   
        <h1 id='titre_newpost'>Ecrire un nouveau post :</h1>
        <!-- Box nouveau post -->   
        <div id='box_newpost'>
            <form id="form_poster" method='POST' enctype='multipart/form-data'>
                <!-- box titre -->   
                <input id="box_titre" type="text" name="titre" placeholder="*Titre" maxlength=100 required>
                <!-- box résumé -->   
                <textarea class="box_text" name="resumee" cols="80" rows="5" placeholder="Résumé" form="form_poster" maxlength=300 ></textarea>
                <!-- Bouton pour ajout d'une image -->   
                <div class="parent-div">
                    <button class="btn-upload">Choisir une image</button>
                    <input id='btn_up' type="file" name="picture" accept=".png, .jpg, .jpeg, .jfif, .pjpeg, .pjp, .svg, .webp" onchange="choix_image();">
                </div>
                <p id='fichier'></p>
                <!-- box contenu -->   
                <textarea class="box_text" name="contenu" cols="80" rows="20" placeholder="*Contenu" form="form_poster" maxlength=30000 required></textarea>
                <!-- Bouton pour publier -->   
                <button class="bouton_submit" type="submit" name="action" value="Publier">Publier !</button>
            </form>
            

            <?php
                // Déclaration du fichier de destination des images
                $upload_folder = "./img/";
                
                // Si on Publie
                if(isset($_POST['action']) && $_POST['action'] == "Publier"){
                    $_randomID = '';
                    // Si il y a une image choisit :
                    if (isset($_FILES['picture']) && UPLOAD_ERR_NO_FILE != $_FILES['picture']['error']){
                        // Téléchargement de l'image vers le fichier cible
                        $_randomID = uniqid();
                        move_uploaded_file($_FILES['picture']['tmp_name'],$upload_folder.$_randomID);
                    }
                    // On ajoute le post dans la base de donnée
                    $newpost = $bdd->prepare('INSERT INTO posts (id_user, titre, resumee, post_image, contenu) VALUES ('.$_SESSION["id"].',"'.htmlspecialchars($_POST["titre"], ENT_QUOTES).'","'.htmlspecialchars($_POST["resumee"], ENT_QUOTES).'","'.$_randomID.'","'.htmlspecialchars($_POST["contenu"],ENT_QUOTES).'")');
                    $newpost->execute();
                    echo "<script type='text/javascript'>document.location.replace('menu.php');</script>";
                    exit();	
                }
            ?>
        </div>
    </body>
<?php
    include("global/footer.php"); 
}
// Si l'utilisateur n'est pas connecté on le redirige vers l'acceuil
else{
    echo "<script type='text/javascript'>document.location.replace('menu.php');</script>";
    exit();	
}
?>