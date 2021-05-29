<?php
    session_start();
    include("global/header.php"); 
    if($_SESSION['grade']==2){
        // Boutons d'option pour chaque utilisateur
        if (isset($_POST['iduserADelete'])) {
            $idDelete = $_POST['iduserADelete'];
            // Suppression des images de ses posts
            $get_image = $bdd->prepare("SELECT post_image FROM posts WHERE id_user=$idDelete");
            $get_image->execute();
            foreach($get_image as $delete_image){
                $fichier = 'img/'.$delete_image['post_image'];
                unlink($fichier); 
            }
            // Suppression de ses commentaires
            $delete_com = $bdd->prepare("DELETE FROM comments WHERE id_user=$idDelete");
            $delete_com->execute();

            // Suppression des commentaires de ses posts
            $delete_allcom = $bdd->prepare("DELETE c FROM comments c INNER JOIN posts p ON p.id_post = c.id_post WHERE p.id_user=$idDelete");
            $delete_allcom->execute();

            // Suppression de ses posts
            $delete_posts = $bdd->prepare("DELETE FROM posts WHERE id_user=$idDelete");
            $delete_posts->execute();

            // Suppression de l'utilisateur
            $delete_user = $bdd->prepare("DELETE FROM user WHERE id_user=$idDelete");
            $delete_user->execute();
            
            // Si l'utilisateur se supprime, on détruit la session
            if($_POST['iduserADelete']==$_SESSION['id']){
                session_destroy();
            }
            header('location: moderation.php');
        }

        // Promouvoir un utilisateur en admin
        if (isset($_POST['idAUpgrade'])) {
            $idUpgrade = $_POST['idAUpgrade'];
            $upgrade_user = $bdd->prepare("UPDATE user SET grade=2 WHERE id_user=$idUpgrade");
            $upgrade_user->execute();
            header('location:'.$_SERVER['REQUEST_URI']);
        }
        // Rétrogader un admin en utilisateur simple
        if (isset($_POST['idADowngrade'])) {
            $idDowngrade = $_POST['idADowngrade'];
            $downgrade_user = $bdd->prepare("UPDATE user SET grade=0 WHERE id_user=$idDowngrade");
            $downgrade_user->execute();
            header('location:'.$_SERVER['REQUEST_URI']);
        }

        // Boutons d'option pour chaque posts des utilisateurs
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
            $delete_allcom = $bdd->prepare("DELETE c FROM comments c INNER JOIN posts p ON p.id_post = c.id_post WHERE p.id_post=$idDelete");
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
            echo "<script type='text/javascript'>document.location.replace('$idModified');</script>";
            exit();	
        }

        //Selectionne tout les utilisateurs si aucunes recherche est faite
        if($_GET['search']==''){     
            $users = $bdd->prepare("SELECT * FROM user u ORDER BY u.grade DESC");
            $users->execute();
             
        //Selectionne tout les utilisateurs qui contiennent X
        }else{                                                                                     
            $users = $bdd->prepare("SELECT * FROM user u WHERE pseudo LIKE CONCAT('%', ?, '%') ORDER BY u.grade DESC");
            $users->execute(Array($_GET['search']));   
        }
?>
<body>
    <!-- Titre -->
    <h1 id='titre_newpost'>Modération</h1>
    
    <!-- Barre de recherche -->
    <form method="_GET">
        <input id="menu_reherche" type="search" id="recherche" name="search" value="<?php echo $_GET['search']?>" placeholder='Rechercher...'>
        <button id='bouton_search'><i class="fa fa-search"></i></button>
    </form>

    <!-- Box de tous les utilisateurs -->
    <diV id='box_moderation'>
        <?php
            // Si aucune recherche on compte tous les utilisateurs
            if($_GET['search']==''){
                //Compte le nombre d'utilisateur
                $compteur = $bdd->prepare("SELECT COUNT(*) FROM user");
                $compteur->execute();
                foreach($compteur as $test){
                    //Si aucun utilisateur n'est présent alors :
                    if($test[0]==0){
                        ?><h1 id='no_post'>Aucun utilisateur, comment avait vous fait pour être la ???</h1><?php
                    }
                }
            }else{
                //Compte le nombre d'utilisateur avec la recherche
                $compteur = $bdd->prepare("SELECT COUNT(*) FROM user u INNER JOIN posts p ON u.id_user=p.id_user WHERE pseudo LIKE CONCAT('%', ?, '%') ORDER BY u.grade DESC");
                $compteur->execute(Array($_GET['search']));
                foreach($compteur as $test){
                    //Si aucun utilisateur n'est présent alors :
                    if($test[0]==0){
                        ?><h1 id='no_post'>Aucun résultat !</h1><?php    
                    }
                }   
            }
            
            // Selectionne tous les utilisateurs
            foreach($users as $user){
                // Compte le nombre de posts de chaque utilisateur
                $compte_post = $bdd->prepare("SELECT COUNT(*) FROM posts WHERE id_user = ?");
                $compte_post->execute(Array($user['id_user']));
                foreach($compte_post as $nbr_p){
                    $nbr_posts = $nbr_p[0];
                }    
        ?>
                <!-- Box individuelles des utilisateurs -->
                <div id='box_mode_user'>
                    <!-- Bouton de l'admin -->
                    <?php if($_SESSION['grade'] == 2){?>
                        <form id='little_button' method='POST'>
                            <!-- Pour supprimer -->
                            <button id='bouton_admin' type="submit" name="iduserADelete" value="<?=$user["id_user"]?>"><img id='croix' src="icones/x-button.png" alt="croix"></button>
                            <!-- Affiche le grade des utilisateurs -->
                            <!-- Si l'utilisateur est un admin alors on peut le rétrograder -->
                            <?php if($user['grade'] == 2){?>
                                <button id='bouton_admin' type="submit" name="idADowngrade" value="<?=$user["id_user"]?>"><img id='croix' src="icones/admin.png" alt="admin"></button>
                            <!-- Sinon, on peut l'upgrader -->
                            <?php }else{?>
                                <button id='bouton_admin' type="submit" name="idAUpgrade" value="<?=$user["id_user"]?>"><img id='croix' src="icones/user.png" alt="user"></button>
                            <?php }?>
                        </form>
                    <?php }?>
                    <!-- Affiche les info de chaque utilisateur -->
                    <h1><?php echo ($user['pseudo']);?></h1>
                    <h3>Prénom : <a><?php echo ($user['prenom']);?></a></h3>
                    <h3>Nom : <a><?php echo ($user['nom']);?></a></h3>
                    <h3>Date de naissance : <a><?php echo ($user['date_birth']);?></a></h3>
                    <span class='clear'></span>
                    <h3>Email : <a><?php echo ($user['email']);?></a></h3>
                    <h3>Nombre de posts : <a><?php echo $nbr_posts?></a></h3>
                    <span class='clear'></span> 
                    <i class="fas fa-angle-double-down"></i>

                     <!-- Div qui contient les posts -->
                    <div id='additional_box'>
                        <div id='child'>
                        <?php
                            // Selectionne tous les posts de chaque utilisateur
                            $post_user = $bdd->prepare("SELECT * FROM posts p INNER JOIN user u ON u.id_user = p.id_user  WHERE u.id_user=? ORDER BY p.date DESC");
                            $post_user->execute(Array($user['id_user'])); 
                            //Compte le nombre de post de chaque utilisateur 
                            $conteur = $bdd->prepare("SELECT COUNT(*) FROM posts p INNER JOIN user u ON u.id_user = p.id_user  WHERE u.id_user=?");
                            $conteur->execute(Array($user['id_user']));
                            foreach($conteur as $test){
                                //Si il n'y a aucun post :
                                if($test[0]==0){
                                    ?><h1 id='no_post'>Aucun post !</h1><?php
                                }
                            }
                            foreach($post_user as $posts){ 
                            // affichage de chaque post :  
                        ?>
                                <div class='menu_post' onclick='document.location.href="post.php?id=<?=$posts["id_post"]?>";'>
                                    <!-- Affiche les boutons d'options -->
                                    <form method='POST'>
                                        <!-- Pour supprimer le post -->
                                        <button id='bouton_admin' type="submit" name="idADelete" value="<?=$posts["id_post"]?>"><img id='croix' src="icones/x-button.png" alt="croix"></button>
                                        <!-- Si le post est visible -->
                                        <?php if($posts['visible']==1){?>
                                            <!-- Pour cacher le post -->
                                            <button id='bouton_admin' type="submit" name="idACacher" value="<?=$posts["id_post"]?>"><img id='croix' src="icones/montrer.png" alt="montrer"></button>
                                        <!-- Si le post est caché -->
                                        <?php }else{?>
                                            <!-- Pour montrer le post -->
                                            <button id='bouton_admin' type="submit" name="idAMontrer" value="<?=$posts["id_post"]?>"><img id='croix' src="icones/cacher.png" alt="cacher"></button>
                                        <?php }?>
                                        <!-- Pour modifier le post -->
                                        <button id='bouton_admin' type="submit" name="idAModifier" value="<?=$posts["id_post"]?>"><img id='croix' src="icones/modifier.png" alt="editer"></button>   
                                    </form>
                                    <!-- Affichage des infos du post -->
                                    <h2 id='post_compte_text'><?=$posts['titre']?> </h2>
                                    <!-- Si il y a une image, on l'affiche -->
                                    <?php if($posts["post_image"] != ''){?>
                                        <img class='img_post' src="img/<?php echo $posts["post_image"] ?>" alt="image introuvable">
                                    <?php }?>
                                    <span class="clear"></span>
                                    <p>Le : <?=$posts['date']?></p>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
        <?php
            }
        ?>
    </div> 
<?php
    // Affichage du footer
    include("global/footer.php"); 
}
// Si l'utilisateur n'est pas un admin, on le redirige vers le menu
else{
    echo "<script type='text/javascript'>document.location.replace('menu.php');</script>";
    exit();	
}
?>  