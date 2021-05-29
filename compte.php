<?php
session_start();
include("global/header.php"); 

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
    // Rafraîchir la page
    header('location:'.$_SERVER['REQUEST_URI']);
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

// Vérifie si l'utilisateur est bien connecté
if(isset($_SESSION['id'])){
?>

<body>
    <!-- Box de modification des informations du compte -->
    <div class="box_compte">
        <h2 class='titre_compte'>Modifier mes données</h2>
        <!-- Formulaire pré-rempli des infos de l'utilisateur -->
        <form method='POST'>
            <input class="box_petite" type="text" name="prenom" value="<?php echo $_SESSION['prenom']?>" placeholder="Prénom" required>
            <input class="box_petite" type="text" name="nom" value="<?php echo $_SESSION['nom']?>" placeholder="Nom" required>
            <br>
            <label for="date_naissance" id="date_label">Date de naissance :</label>
            <input class="box_petite" id="register_date" type="date" value="<?php echo $_SESSION['date_n']?>" name="date_naissance" required>
            <input class="box_grande" type="email" name="email"  value="<?php echo $_SESSION['mail']?>" placeholder="E-mail" required>
            <input class="box_grande" type="text" name="pseudo" value="<?php echo $_SESSION['pseudo']?>" placeholder="Votre Pseudo / Identifiant" required>
            <p id='alerte_mdp' class='verif_mdp'></p>
            <input class="box_grande" type="password" name="password_O" id="password_O" placeholder="Mot de passe">
            <p id='message' class='verif_mdp'></p>
            <input class="box_grande" type="password" name="password_N" id="password_N" placeholder="Nouveau mot de passe"> 
            <input class="box_grande" onmouseout="check_pass('password_N','password_N2','modifier_compte');"  type="password" name="password_N2" id="password_N2" placeholder="Vérification du nouveau mot de passe"> 
            <button class="bouton_submit" id="modifier_compte" type="submit" name="action" value="Modifier_compte">Modifier !</button>
        </form>

        <?php
            // Quand on clique sur Modifier :
            if(isset($_POST['action']) && $_POST['action'] == "Modifier_compte"){
                // Si le mot de passe rempli n'est pas le bon :
                if(isset($_POST['password_O']) && md5($_POST['password_O']) != $_SESSION['mdp']){
                    // On affiche une erreur
                    echo '<script>mdp_error();</script>'; 
                }
                // Si le mot de passe est bon :
                else{
                    // On définie les variables
                    $nom = $_POST['nom'];
                    $prenom = $_POST['prenom'];
                    $pseudo = $_POST['pseudo'];
                    $email = $_POST['email'];
                    $date_n = $_POST['date_naissance'];
                    $id = $_SESSION['id'];
                    // Si les nouveaux mots de passe sont complétés :
                    if($_POST['password_N']!='' && $_POST['password_N2']!=''){
                        $passwd = md5($_POST['password_N']);
                        // On modifie les information de l'utilisateur dont le mot de passe
                        $modification = $bdd->prepare('UPDATE user SET nom =?, prenom=?, pseudo=?, passwd=?, email=?, date_birth=? WHERE id_user=?');
                        $modification ->execute(Array($nom,$prenom,$pseudo,$passwd,$email,$date_n,$id));
                        // On update le mot de passe de SESSION
                        $_SESSION['mdp'] = $passwd;
                    }
                    // Si on ne modifie pas le mot de passe :
                    else{
                        // On modifie les information de l'utilisateur sauf le mot de passe
                        $modificationwmdp = $bdd->prepare('UPDATE user SET nom =?, prenom=?, pseudo=?, email=?, date_birth=? WHERE id_user=?');
                        $modificationwmdp ->execute(Array($nom,$prenom,$pseudo,$email,$date_n,$id));
                    }
                    // On update les informations de SESSION
                    $_SESSION['nom'] = $_POST['nom'];
                    $_SESSION['prenom'] = $_POST['prenom'];
                    $_SESSION['pseudo'] = $_POST['pseudo'];
                    $_SESSION['mail'] = $_POST['email'];
                    $_SESSION['date_n'] = $_POST['date_naissance'];
                    // Rafraîchir la page
                    echo "<script type='text/javascript'>document.location.replace('compte.php');</script>";
                    exit();
                }  
            }
        ?>
    </div>
    <!-- Box des posts de l'utilisateur -->
    <div class="box_compte_post" >
        <h2 class='titre_connec'>Vos posts</h2>
        <div id="all_post_user">
            <div id='child'>
                <?php
                    // Si l'utilisateur est admin, alors on affiche tout ses posts
                    if($_SESSION['grade']==2){
                        $post_user = $bdd->prepare("SELECT * FROM posts p WHERE p.id_user=? ORDER BY p.date DESC");
                        //Compte le nombre de post de l'utilisateur
                        $compteur = $bdd->prepare("SELECT COUNT(*) FROM posts p WHERE p.id_user=?");
                        $compteur->execute(Array($_SESSION['id']));
                        foreach($compteur as $test){
                            //Si aucun post n'est présent alors :
                            if($test[0]==0){
                                ?><h1 id='no_post'>Vous n'avez aucun post !</h1><?php
                            }
                        }
                    // Si l'utililsateur est simple, alors on affiche tout ses posts visibles
                    }else{
                        $post_user = $bdd->prepare("SELECT * FROM posts p WHERE visible=1 AND p.id_user=? ORDER BY p.date DESC");
                        //Compte le nombre de post de l'utilisateur
                        $compteur = $bdd->prepare("SELECT COUNT(*) FROM posts p WHERE visible=1 AND p.id_user=?");
                        $compteur->execute(Array($_SESSION['id']));
                        foreach($compteur as $test){
                            //Si aucun post n'est présent alors :
                            if($test[0]==0){
                                ?><h1 id='no_post'>Vous n'avez aucun post !</h1><?php
                            }
                        }
                    }
                    // On execute la selection des posts 
                    $post_user->execute(Array($_SESSION['id'])); 
                    
                    // Affichage de chacun des posts
                    foreach($post_user as $posts){
                        //Compte le nombre de commentaire pour chaque post
                        $cmpt_coms = $bdd->prepare("SELECT COUNT(*) FROM comments c WHERE id_post = ?");
                        $cmpt_coms->execute(Array($posts['id_post']));
                        foreach($cmpt_coms as $cmpt_com){
                            $nbr_com = $cmpt_com[0];
                        }  
                        ?>

                        <div class='menu_post' onclick='document.location.href="post.php?id=<?=$posts["id_post"]?>";'>
                            <!-- Boutons d'option de l'utilisateur simple -->
                            <?php if($_SESSION['grade'] == 0 && $_SESSION['id']==$posts['id_user']){?>
                                <form method='POST'>
                                    <!-- Bouton croix -->
                                    <button id='bouton_admin' type="submit" name="idADelete" value="<?=$posts["id_post"]?>"><img id='croix' src="icones/x-button.png" alt="croix"></button>
                                    <!-- Bouton modifier -->
                                    <button id='bouton_admin' type="submit" name="idAModifier" value="<?=$posts["id_post"]?>"><img id='croix' src="icones/modifier.png" alt="editer"></button>
                                </form>
                            <?php }?>
                            <!-- Boutons d'option d'un administrateur-->
                            <?php if($_SESSION['grade'] == 2){?>
                                <form method='POST'>
                                    <!-- Bouton croix -->
                                    <button id='bouton_admin' type="submit" name="idADelete" value="<?=$posts["id_post"]?>"><img id='croix' src="icones/x-button.png" alt="croix"></button>
                                    <!-- Bouton cacher si le post est visible -->
                                    <?php if($posts['visible']==1){?>
                                        <button id='bouton_admin' type="submit" name="idACacher" value="<?=$posts["id_post"]?>"><img id='croix' src="icones/montrer.png" alt="montrer"></button>
                                    <!-- Bouton montrer si le post n'est pas visible -->
                                    <?php }else{?>
                                        <button id='bouton_admin' type="submit" name="idAMontrer" value="<?=$posts["id_post"]?>"><img id='croix' src="icones/cacher.png" alt="cacher"></button>
                                    <?php }?>
                                    <!-- Bouton modifier -->
                                    <button id='bouton_admin' type="submit" name="idAModifier" value="<?=$posts["id_post"]?>"><img id='croix' src="icones/modifier.png" alt="editer"></button>   
                                </form>
                            <?php }?>
                            <!-- Titre -->
                            <h2 id='post_compte_text'><?=$posts['titre']?> </h2>
                            <!-- Affiche l'image si il y en a une -->
                            <?php if($posts["post_image"] != ''){?>
                                <img class='img_post' src="img/<?php echo $posts["post_image"] ?>" alt="image introuvable">
                            <?php }?>
                            <span class="clear"></span>
                            <!-- Détails du post -->
                            <p id='menu_com'><i class="far fa-comment"></i> <?=$nbr_com?></p>
                            <p>Le : <?=$posts['date']?></p>
                        </div>
                    <?php
                    }
                ?>
            </div>
        </div>
    </div>
    <span class='clear'></span>
<?php
    // Affichage du footer
    include("global/footer.php"); 
}
// Si l'utilisateur n'est pas connecté, on le redirige vers le menu
else{
    echo "<script type='text/javascript'>document.location.replace('menu.php');</script>";
    exit();	
}
?>   