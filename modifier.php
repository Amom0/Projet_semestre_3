<?php
session_start();
include("global/header.php"); 

// Si l'utilisateur n'est pas connecté on le redirige vers le menu 
if(!isset($_GET['id'])){
    echo "<script type='text/javascript'>document.location.replace('menu.php');</script>";
    exit;
}

// Select les info du post à modifier
$infopost = $bdd->prepare("SELECT * FROM posts p INNER JOIN user u ON u.id_user = p.id_user  WHERE p.id_post=?"); 
$infopost -> execute(Array((int)$_GET['id'])); 
$lepost = $infopost->fetchAll();

if(!$lepost[0]){
    echo "<script type='text/javascript'>document.location.replace('menu.php');</script>";
    exit();	   
}

// Verifie si le post est visible et si l'utilisateur est bien celui qui l'a écrit ou si l'utilisateur est un admin 
if(($lepost[0]['visible']== 1 && $lepost[0]['id_user']==$_SESSION['id']) || $_SESSION['grade']==2){
?>

<body>
    <h1 id='titre_newpost'>Modifier votre post :</h1>
    <div id='box_newpost'>
        <form id="form_poster" method='POST' enctype='multipart/form-data' name='modif'>
            <!-- Titre -->
            <input id="box_titre" type="text" name="titre" value="<?php echo $lepost[0]["titre"] ?>" placeholder="*Titre" maxlength=100 required>
            <!-- Résumée -->
            <textarea class="box_text" name="resumee" cols="80" rows="5" placeholder="Résumé" form="form_poster" maxlength=300 ><?php if(isset($lepost[0]["resumee"])){echo $lepost[0]["resumee"];}?></textarea>
            <!-- Boutons liée à l'image -->
            <div class="parent-div">
                <!-- Si il y en a une on peut la modifier : -->
                <?php if($lepost[0]["post_image"]!=NULL){?>
                    <button class="btn-upload">Modifier l'image</button>  
                <!-- Sinon, on peut en ajouter une -->
                <?php }else{?>
                    <button class="btn-upload">Ajouter une image</button>
                <?php }?>
                <input id='btn_up' type="file" name="picture" accept=".png, .jpg, .jpeg, .jfif, .pjpeg, .pjp, .svg, .webp" onchange="choix_image();">
            </div>
                <p id='fichier'></p>
            <span class='clear'></span>
            <!-- Si il y a une image on peut également la supprimer -->
            <?php if($lepost[0]["post_image"]!=NULL){?>
                <h1>OU</h1>
                <span class='clear'></span>
                <div id='btn_del_parent'>
                    <button class="btn-upload">Supprimer l'image</button> 
                    <input id='btn_del' type='checkbox' name="delete_img" onclick='sup_image();' value="<?php echo $lepost[0]["post_image"] ?>">
                </div>
                <p id='check'></p>
            <?php }?>
            <!-- Contenu -->
            <textarea class="box_text" name="contenu" cols="80" rows="20" placeholder="*Contenu" form="form_poster" maxlength=30000 required><?php echo $lepost[0]["contenu"]?></textarea>
            <!-- Bouton submit -->
            <button class="bouton_submit" type="submit" name="action" value="Modifier">Modifier !</button>
        </form>
            

        <?php
            // Déclaration du fichier de destination des images
            $upload_folder = "./img/";
            
            // Si on Modifie
            if(isset($_POST['action']) && $_POST['action'] == "Modifier"){
                // Si il y a une image choisie :
                if(isset($_FILES['picture']) && UPLOAD_ERR_NO_FILE != $_FILES['picture']['error']){
                    // Si il y avait déjà une image :
                    if($lepost[0]['post_image'] != NULL){
                        // On supprime l'ancienne
                        $fichier = 'img/'.$lepost[0]['post_image'];
                        unlink($fichier); 
                        // On ajoute une nouvelle
                        $_randomID = uniqid();
                        move_uploaded_file($_FILES['picture']['tmp_name'],$upload_folder.$_randomID);
                    //Sinon on en ajoute une :
                    }else{
                        $_randomID = uniqid();
                        move_uploaded_file($_FILES['picture']['tmp_name'],$upload_folder.$_randomID);
                    }  
                // Si il n'y a pas d'image choisie : 
                }else{
                    // Si on a selectionné "supprimer l'image"
                    if(isset($_POST['delete_img'])){
                        // On supprime l'image actuelle
                        $_randomID = '';
                        $fichier = 'img/'.$lepost[0]['post_image'];
                        unlink($fichier); 
                    // Sinon on laisse l'image actuelle
                    }else{
                        $_randomID = $lepost[0]['post_image'];
                    }
                }
                // Déclaration des variables à ajouter
                $titre = htmlspecialchars($_POST["titre"], ENT_QUOTES);
                $resumee = htmlspecialchars($_POST["resumee"], ENT_QUOTES);
                $post_image = $_randomID;
                $contenu = htmlspecialchars($_POST["contenu"],ENT_QUOTES);
                $id_post = $lepost[0]["id_post"];

                // Ajout des variables
                $newpost = $bdd->prepare('UPDATE posts SET titre =?, resumee=?, post_image=?, contenu=? WHERE id_post=?');
                $newpost ->execute(Array($titre,$resumee,$post_image,$contenu,$id_post));

                // Redirection vers la page du post
                echo "<script type='text/javascript'>document.location.replace('post.php?id=$id_post');</script>";
                exit();	
            }  
        ?>
    </div>
<?php
    // Affichage du footer
    include("global/footer.php"); 
}
// Si le post est caché et que l'utilisateur n'est pas un admin ou que l'utilisateur n'est pas l'auteur : 
else{
    // Redirection au menu
    echo "<script type='text/javascript'>document.location.replace('menu.php');</script>";
    exit();	
}
?>   