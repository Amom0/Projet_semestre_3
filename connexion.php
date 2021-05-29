<?php 
    session_start();
    include("global/header.php"); 
    // Selection de tout les utilisateurs
    $users = $bdd->prepare("SELECT * FROM user");
    $users->execute(); 
?>

<body>
    <!-- Box Login -->
    <div class="box_connec" id="Login">
        <!-- Titre -->
        <h2 class='titre_connec'>Login</h2>
        <form method='POST'>
            <!-- Pseudo -->
            <input class="box_grande" type="text" name="login" placeholder="Identifiant" required>
            <!-- Mot de passe  -->
            <input class="box_grande" type="password" name="passwrd" placeholder="Mot de passe" required>
            <!-- Bouton se connecter -->
            <button class="bouton_submit" type="submit" name="action" value="connexion">Se connecter</button>
        </form>

        <?php
            // Quand on clique sur se connecter :
            if(isset($_POST['action']) && $_POST['action'] == "connexion" && isset($_POST['login']) && isset($_POST['passwrd'])){
                // On parcourt tout les utilisateurs
                foreach($users as $user){
                    // Si le pseudo et le mdp rentrés correspondent au pseudo et au mdp d'un utilisateur dans la base de donnée alors :
                    if($_POST['login'] == $user['pseudo'] && md5($_POST['passwrd']) == $user['passwd']){
                        // On assigne toutes les variables de SESSION
                        $_SESSION['id'] = $user['id_user'];
                        $_SESSION['pseudo'] = $user['pseudo'];
                        $_SESSION['prenom'] = $user['prenom'];
                        $_SESSION['nom'] = $user['nom'];
                        $_SESSION['mdp'] = $user['passwd'];
                        $_SESSION['date_n'] = $user['date_birth'];
                        $_SESSION['mail'] = $user['email'];
                        $_SESSION['grade'] = $user['grade'];
                        // Puis on redirige l'utilisateur au menu une fois connecté
                        echo "<script type='text/javascript'>document.location.replace('menu.php');</script>";
                        exit();	
                    } 
                    // Si le pseudo ou le mot de passe n'est pas bon on ne connecte pas l'utilisateur
                    else{
                        $wrong = 1;
                    }
                }
                // Si le pseudo ou le mot de passe était mauvais, on informe l'utilisateur
                if($wrong==1){
                    echo "<script>wrong();</script>";
                }
            }            
        ?>
    </div>

    <!-- Box Register -->
    <div class="box_connec" id="Register">
        <h2 class='titre_connec'>Register</h2>
        <!-- Formulaire d'inscription -->
        <form method='POST'>
            <input class="box_petite" type="text" name="prenom" placeholder="Prénom" required>
            <input class="box_petite" type="text" name="nom" placeholder="Nom" required>
            <br>
            <label for="date_naissance" id="date_label">Date de naissance :</label>
            <input class="box_petite" id="register_date" type="date" name="date_naissance" required>
            <input class="box_grande" type="email" name="email" placeholder="E-mail" required>
            <input class="box_grande" type="text" name="pseudo" placeholder="Votre Pseudo / Identifiant" required>
            <p id='message' class='verif_mdp'></p>
            <input class="box_grande" type="password" name="password" id="password1" placeholder="Mot de passe" required>
            <input class="box_grande" onmouseout="check_pass('password1','password2','inscription');" type="password" name="password2" id="password2" placeholder="Vérification du mot de passe" required> 
            <button class="bouton_submit" id="inscription" type="submit" name="action" value="Inscription">S'inscrire</button>
        </form>

        <!-- Quand on clique sur Inscription : -->
        <?php
            if(isset($_POST['action']) && $_POST['action'] == "Inscription"){
                // On prépare l'ajout les données dans la base de donnée
                $inscription = $bdd->prepare("INSERT INTO user (nom, prenom, pseudo, passwd, email, date_birth) VALUES ('".$_POST['nom']."','".$_POST['prenom']."','".$_POST['pseudo']."','".md5($_POST['password'])."','".$_POST['email']."','".$_POST['date_naissance']."')");

                // On parcourt tout les utilisateurs 
                foreach($users as $user){
                    // Si le pseudo existe déjà, on devra prévenir l'utilisateur
                    if($_POST['pseudo']==$user['pseudo']){
                        $exist = 1;
                    // Sinon on peut l'inscrire
                    }else{
                        $exist = 0;
                    }
                }
                // Si le pseudo n'existe pas, on ajoute l'utilisateur
                if($exist == 0){
                $inscription->execute();
                $id = $bdd->lastInsertId(); 
                $_SESSION['id'] = $id;
                $_SESSION['pseudo'] = $_POST['pseudo'];
                $_SESSION['prenom'] = $_POST['prenom'];
                $_SESSION['nom'] = $_POST['nom'];
                $_SESSION['mdp'] = $_POST['password'];
                $_SESSION['date_n'] = $_POST['date'];
                $_SESSION['mail'] = $_POST['email'];
                $_SESSION['grade'] = 0;
                // On redirige l'utilisateur vers le menu une fois qu'il est inscrit
                echo "<script type='text/javascript'>document.location.replace('menu.php');</script>";
                exit;
                }
                // Si le pseudo existe, on avertie l'utilisateur
                else{ 
                    echo "<script>exist();</script>";
                }
            }
        ?>
    </div>
    <span class='clear'><span>
<?php
    // Affichage du footer
    include("global/footer.php"); 
?>