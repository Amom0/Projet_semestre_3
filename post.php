<?php
    session_start();
    include("global/header.php");
    
    // Si l'id en GET n'est pas définie, on redirige l'utilisateur vers le menu 
    if(!isset($_GET['id'])){
        echo "<script type='text/javascript'>document.location.replace('menu.php');</script>";
        exit;
    }

    // Clique sur la croix : Suppression du post
    if (isset($_POST['idADelete'])) {
        $idDelete = $_POST['idADelete'];
        // Suppression de l'image du post
        $get_image = $bdd->prepare("SELECT post_image FROM posts WHERE id_post=$idDelete");
        $get_image->execute();
        $lien = $get_image->fetchAll();
        $fichier = 'img/'.$lien[0]['post_image'];
        unlink($fichier); 
        // Suppression des commentaires du post
        $delete_allcom = $bdd->prepare("DELETE c FROM comments c WHERE c.id_post=$idDelete");
        $delete_allcom->execute();
        // Suppression du post 
        $delete_post = $bdd->prepare("DELETE FROM posts WHERE id_post=$idDelete");
        $delete_post->execute();
        // Redirection
        header('location: menu.php');
        }
    // Clique sur l'oeil barré : Rendre le post "invisble"
    if (isset($_POST['idACacher'])) {
        $idHidden = $_POST['idACacher'];
        // Update la visibilité du post
        $hidde_post = $bdd->prepare("UPDATE posts SET visible=0 WHERE id_post=$idHidden");
        $hidde_post->execute();
        // Rafraîchir la page
        header('location:'.$_SERVER['REQUEST_URI']);
    }
    // Clique sur l'oeil : Rendre le post "visble"
    if (isset($_POST['idAMontrer'])) {
        $idShowed = $_POST['idAMontrer'];
        // Update la visibilité du post
        $show_post = $bdd->prepare("UPDATE posts SET visible=1 WHERE id_post=$idShowed");
        $show_post->execute();
        // Rafraîchir la page
        header('location:'.$_SERVER['REQUEST_URI']);
    }
    // Clique sur le bouton de modification
    if (isset($_POST['idAModifier'])) {
        // Redirection vers la page de modification
        $idModified = 'modifier.php?id='.$_POST['idAModifier'];
        // Redirection vers la page de modifiction du post sélectionné
        echo "<script type='text/javascript'>document.location.replace('$idModified');</script>";
        exit();	
    }
    // Clique sur croix (commentaire) : Suppression du commentaire
    if (isset($_POST['idComADelete'])) {
        $idComDelete = $_POST['idComADelete'];
        $delete_com = $bdd->prepare("DELETE FROM comments WHERE id_comment=$idComDelete");
        $delete_com->execute();  
    }

    // Select les info du post choisit
    $infopost = $bdd->prepare("SELECT * FROM posts p INNER JOIN user u ON u.id_user = p.id_user  WHERE p.id_post=?"); 
    $infopost -> execute([(int)$_GET['id']]); 
    $lepost = $infopost->fetchAll();
    // Si l'id n'est pas valide, on redirige l'utilisateur vers le menu
    if(!$lepost[0]){
        echo "<script type='text/javascript'>document.location.replace('menu.php');</script>";
        exit();	   
    }

    // Adaptation date française
    $jour = substr($lepost[0]['date'],8,2);
    $mois = substr($lepost[0]['date'],5,2);
    $annee = substr($lepost[0]['date'],0,4);

    // Verifie si le post est visible quand l'utilisateur est "simple" ou si l'utilisateur est admin 
    if($lepost[0]['visible']==1 || ($lepost[0]["visible"]==0 && $_SESSION['grade']==2)){
?>
<body>
    <!-- Post -->
    <div id='box_post'>
        <!-- Verifie si l'utilisateur est "simple" et si c'est celui qui à écrit le post pour afficher les boutons d'options  -->
        <?php if($_SESSION['grade'] == 0 && $_SESSION['id']==$lepost[0]["id_user"]){?>
            <form method='POST'>
                <!-- Bouton croix -->
                <button id='bouton_admin' type="submit" name="idADelete" value="<?=$lepost[0]["id_post"]?>"><img id='croix' src="icones/x-button.png" alt="croix"></button>
                <!-- Bouton modifier -->
                <button id='bouton_admin' type="submit" name="idAModifier" value="<?=$lepost[0]["id_post"]?>"><img id='croix' src="icones/modifier.png" alt="editer"></button>
            </form>
        <?php }?>
        <!-- Soit c'est un admin est on affiche les boutons d'options pour l'admin -->
        <?php if($_SESSION['grade'] == 2){?>
            <form method='POST'>
                <!-- Bouton croix -->
                <button id='bouton_admin' type="submit" name="idADelete" value="<?=$lepost[0]["id_post"]?>"><img id='croix' src="icones/x-button.png" alt="croix"></button>
                <!-- Bouton cacher si le post est visible -->
                <?php if($lepost[0]["visible"]==1){ ?>
                    <button id='bouton_admin' type="submit" name="idACacher" value="<?=$lepost[0]["id_post"]?>"><img id='croix' src="icones/montrer.png" alt="montrer"></button>
                <!-- Bouton montrer si le post n'est pas visible -->
                <?php }else{?>
                    <button id='bouton_admin' type="submit" name="idAMontrer" value="<?=$lepost[0]["id_post"]?>"><img id='croix' src="icones/cacher.png" alt="cacher"></button>
                <?php }?>
                <!-- Bouton modifier -->
                <button id='bouton_admin' type="submit" name="idAModifier" value="<?=$lepost[0]["id_post"]?>"><img id='croix' src="icones/modifier.png" alt="editer"></button>   
            </form>
        <?php }?>
        <!-- Titre -->
        <h1 id='post_titre'><?= $lepost[0]["titre"] ?></h1> 
        <!-- Affiche l'image si il y en a une -->
        <?php if($lepost[0]["post_image"] != ''){?>
            <img id='img_plein_post' src="img/<?php echo $lepost[0]["post_image"] ?>" alt="image introuvable">
        <?php }?>
        <!-- Contenu -->
        <p id='post_contenu'><?= nl2br($lepost[0]["contenu"]) ?></p>  
        <!-- Détails du post -->
        <h5 class='post_detail'>Ecrit par : <?= $lepost[0]["pseudo"] ?></h5>
        <h5 class='post_detail'>Publié le : <?=$jour?>-<?=$mois?>-<?=$annee?></h5>
    </div>

    <!-- Commentaire(s) -->
    <div id='box_comment'>
            <!-- titre -->
            <h1 id='titre_comment'>Commentaire(s)</h1>
            <!-- Affichage du bouton pour ajouter un commentaire si l'utilisateur est connecté -->
            <?php if(isset($_SESSION["pseudo"])){ ?>
                    <button id='bouton_comment' type="submit" onclick='afficher();'><i class="fas fa-plus fa-2x"></i></button>
            <?php }?>
            <!-- Box pour écrire un nouvea commentaire -->
            <div id='new_comment'>
                <!-- titre -->
                <h2>New comment :</h2>
                <form method='POST' >
                    <!-- Boite pour écrire le contenu du commentaire -->
                    <textarea id='text_new_comment' name="commentaire"  placeholder="Commentaire..." maxlength=500 ></textarea>
                    <!-- Bouton pour envoyer le commentaire -->
                    <button id='btn_new_comment' type="submit" name="action" value="Commenter"><i class="fas fa-paper-plane fa-2x"></i></button>
                </form>
                
                <!-- Si on envoie le commentaire, on l'ajoute à la base de donnée -->
                <?php
                    if(isset($_POST['action']) && $_POST['action'] == "Commenter"){
                        $newcomment = $bdd->prepare('INSERT INTO comments (contenu, id_user, id_post) VALUES ("'.htmlspecialchars($_POST["commentaire"], ENT_QUOTES).'",'.$_SESSION['id'].','.$_GET['id'].')');
                        $newcomment->execute();
                    }
                ?>
            </div>

            <?php
                // Select tout les commentaires
                $all_comment = $bdd->prepare("SELECT * FROM comments c WHERE c.id_post = ? ORDER BY date_com DESC");
                $all_comment->execute(Array($_GET['id']));
                //Compte le nombre de commentaire pour le post
                $compteur = $bdd->prepare("SELECT COUNT(*) FROM comments WHERE id_post=? ");
                $compteur->execute(Array($_GET['id']));
                foreach($compteur as $test){
                    //Si aucun commentaire n'est présent alors :
                    if($test[0]==0){
                        ?><h1 id='no_post'>Aucun commentaire </h1><?php
                    }
                }
                foreach($all_comment as $comment){
                    // Simplification de la date 
                    $jour = substr($comment['date_com'],8,2);
                    $mois = substr($comment['date_com'],5,2);
                    $annee = substr($comment['date_com'],0,4);
                    $heure = substr($comment['date_com'],11,5);
            ?>
                    <!-- Affichage des commentaires -->
                    <div id='comment'>    
                        <!-- Contenu -->   
                        <p id='contenu_comment'><?=$comment['contenu']?></p> 
                        <!-- Info du commentaire -->              
                        <div id='info_comment'>
                            <h2><?=$comment['pseudo']?></h2>
                            <h3><?=$jour?>-<?=$mois?>-<?=$annee?></h3>
                            <h3><?=$heure?></h3>
                            <!-- Si le commentaire est celui de l'utilisateur ou l'utilisateur est un admin, affiche le bouton supprimer -->   
                            <?php if(($comment['id_user']==$_SESSION['id']) || $_SESSION['grade']==2){?>
                                <form method='POST'>
                                    <button id='bouton_admin' type="submit" name="idComADelete" value="<?= $comment['id_comment']?>"><img id='croix' src="icones/x-button.png" alt="croix"></button>
                                </form>
                            <?php }?>
                        </div>                  
                    </div>
            <?php }?>
    </div>   
<?php
    
?>  

<?php
    // Affichage du footer
    include("global/footer.php"); 
}
// Si le post n'est pas visible et que l'utilisateur n'est pas un admin alors on redirige vers l'acceuil
else{
    echo "<script type='text/javascript'>document.location.replace('menu.php');</script>";
    exit;
}
?>