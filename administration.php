<?php
session_start();
include("global/header.php"); 
// Si l'utilisateur est un admin :
if($_SESSION['grade']==2){
?>
<body>
    <h1 id='titre_newpost'>Administration</h1>

    <!-- Barre de recherche -->
    <form method="_GET">
        <input id="menu_reherche" type="search" id="recherche" name="search" value="<?php echo $_GET['search']?>" placeholder='Rechercher...'>
        <button id='bouton_search'><i class="fas fa-search"></i></button>
    </form>

    <?php 

    // Si aucune recherche n'est faite :
    if($_GET['search']==''){     
        // Selectionne tout les posts non visible
        $hiddenpost = $bdd->prepare("SELECT * FROM posts p INNER JOIN user u ON u.id_user = p.id_user  WHERE visible=0 ORDER BY p.date DESC");
        $hiddenpost->execute(); 
        //Compte le nombre de post non visble
        $compteur = $bdd->prepare("SELECT COUNT(*) FROM posts p INNER JOIN user u ON u.id_user = p.id_user  WHERE visible=0");
        $compteur->execute();
        foreach($compteur as $test){
            //Si aucun post n'est présent alors :
            if($test[0]==0){
                ?><h1 id='no_post'>Aucun post caché, pour le moment...</h1><?php
            }
        }
            
    // Si une recherche est effectuée
    }else{  
        // Selectionne tout les posts cachés qui contiennent X                                                                                   
        $hiddenpost = $bdd->prepare("SELECT * FROM posts p INNER JOIN user u ON u.id_user = p.id_user  WHERE (titre LIKE CONCAT('%', ?, '%') OR resumee LIKE CONCAT('%', ?, '%') OR contenu LIKE CONCAT('%', ?, '%') OR pseudo LIKE CONCAT('%', ?, '%'))AND visible=0 ORDER BY p.date DESC");
        $hiddenpost->execute(Array($_GET['search'],$_GET['search'],$_GET['search'],$_GET['search'])); 

        // Compte le nombre de post caché contenant le mot recherché
        $compteur = $bdd->prepare("SELECT COUNT(*) FROM posts p INNER JOIN user u ON u.id_user = p.id_user  WHERE (titre LIKE CONCAT('%', ?, '%') OR resumee LIKE CONCAT('%', ?, '%') OR contenu LIKE CONCAT('%', ?, '%') OR pseudo LIKE CONCAT('%', ?, '%'))AND visible=0");
        $compteur->execute(Array($_GET['search'],$_GET['search'],$_GET['search'],$_GET['search']));
        foreach($compteur as $test){
            //Si aucun post n'est présent alors :
            if($test[0]==0){
                ?><h1 id='no_post'>Aucun résultat !</h1><?php
            }
        }
    }

    // Verifie les bouttons de l'admin sur les posts
    // Bouton supprimer :
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
        // Raffraichissement de la page
        echo "<script type='text/javascript'>document.location.replace('administration.php');</script>";
        exit();	
    }
    // Clique sur l'oeil : Rendre le post "visible"
    if (isset($_POST['idAMontrer'])) {
        $idShowed = $_POST['idAMontrer'];
        // Update la visibilité du post
        $show_post = $bdd->prepare("UPDATE posts SET visible=1 WHERE id_post=$idShowed");
        $show_post->execute();
        // Rafraîchir la page
        echo "<script type='text/javascript'>document.location.replace('administration.php');</script>";
        exit();	
    }
    // Clique sur le bouton de modification
    if (isset($_POST['idAModifier'])) {
        $idModified = 'modifier.php?id='.$_POST['idAModifier'];
        // Redirection vers la page de modification
        echo "<script type='text/javascript'>document.location.replace('$idModified');</script>";
        exit();	
    }

    // Affichage pour chaque post caché
    foreach($hiddenpost as $hiddenposts){
        //Compte le nombre de commentaire pour chaque post
        $cmpt_coms = $bdd->prepare("SELECT COUNT(*) FROM comments c WHERE id_post = ?");
        $cmpt_coms->execute(Array($hiddenposts['id_post']));
        foreach($cmpt_coms as $cmpt_com){
            $nbr_com = $cmpt_com[0];
        }
    ?>
        <!-- DIV pour tous les posts -->
        <div class='menu_post' onclick='document.location.href="post.php?id=<?=$hiddenposts["id_post"]?>";'>
            <!-- Bouton de l'admin -->
            <form method='POST'>
                <!-- Boutons pour supprimer -->
                <button id='bouton_admin' type="submit" name="idADelete" value="<?=$hiddenposts["id_post"]?>"><img id='croix' src="icones/x-button.png" alt="croix"></button>
                <!-- Boutons pour re-montrer le post -->
                <button id='bouton_admin' type="submit" name="idAMontrer" value="<?=$hiddenposts["id_post"]?>"><img id='croix' src="icones/cacher.png" alt="cacher"></button>
                <!-- Boutons pour modifier -->
                <button id='bouton_admin' type="submit" name="idAModifier" value="<?=$hiddenposts["id_post"]?>"><img id='croix' src="icones/modifier.png" alt="editer"></button>
            </form>
            <!-- Affichage des posts -->
            <h2 id='menu_text'><?=$hiddenposts['titre']?> </h2>
            <!-- On verifie si il existe un résumé sinon on commence à afficher le contenu -->
            <?php if($hiddenposts['resumee']!=''){?>
                <p id='menu_text'><?=nl2br($hiddenposts['resumee'])?> </p>
            <?php }elseif(strlen($hiddenposts['contenu'])<=300){?>
                <p id='menu_text'><?=nl2br($hiddenposts['contenu'])?> </p>
            <?php }else{
                $str = nl2br(substr($hiddenposts['contenu'],0,300));     
            ?>
                <p id='menu_text'><?= nl2br(substr($str,0,strrpos($str,' '))).'...'?> </p> 
            <?php }?>
            <!-- On verifie si il y a une image et on l'affiche -->
            <?php if($hiddenposts["post_image"] != ''){?>
                <img class='img_post' src="img/<?php echo $hiddenposts["post_image"] ?>" alt="image introuvable">
            <?php }?>
            <span class="clear"></span>
            <!-- Details du post -->
            <p id='menu_com'><i class="far fa-comment"></i> <?=$nbr_com?></p>
            <br><br>
            <p id='menu_pseudo'>Ecrit par : <?=$hiddenposts['pseudo']?></p>
            <p id='menu_date'>Le : <?=$hiddenposts['date']?></p>
        </div>
        <?php
        }
        ?>
        
    

</body>
  


<?php
    // Affichage du footer
    include("global/footer.php"); 
}
// Si ce n'est pas un admin on le redirige vers le menu
else{
    echo "<script type='text/javascript'>document.location.replace('menu.php');</script>";
    exit;
}
?>