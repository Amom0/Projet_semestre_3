-- phpMyAdmin SQL Dump
-- version 4.6.6deb5ubuntu0.5
-- https://www.phpmyadmin.net/
--
-- Client :  localhost:3306
-- Généré le :  Dim 29 Novembre 2020 à 20:02
-- Version du serveur :  5.7.32-0ubuntu0.18.04.1
-- Version de PHP :  7.2.24-0ubuntu0.18.04.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `Blog`
--

-- --------------------------------------------------------

--
-- Structure de la table `comments`
--

CREATE TABLE `comments` (
  `id_comment` int(11) NOT NULL,
  `contenu` varchar(600) NOT NULL,
  `date_com` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_user` int(11) NOT NULL,
  `id_post` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `comments`
--

INSERT INTO `comments` (`id_comment`, `contenu`, `date_com`, `id_user`, `id_post`) VALUES
(9, 'SUPERRR !!!! Ca m&#039;a énormément aidé, merci pour tes conseils !  ', '2020-11-29 18:58:40', 7, 119),
(10, '?????????', '2020-11-29 19:01:24', 5, 120);

-- --------------------------------------------------------

--
-- Structure de la table `posts`
--

CREATE TABLE `posts` (
  `id_post` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_user` int(11) NOT NULL,
  `titre` varchar(300) NOT NULL,
  `resumee` varchar(535) NOT NULL,
  `post_image` varchar(255) DEFAULT NULL,
  `contenu` text NOT NULL,
  `visible` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `posts`
--

INSERT INTO `posts` (`id_post`, `date`, `id_user`, `titre`, `resumee`, `post_image`, `contenu`, `visible`) VALUES
(119, '2020-11-29 18:57:39', 6, 'Brume', 'Comment bien utiliser la brume lors de votre shooting ?', '5fc3ef230dd26', 'Pour rendre votre photo intéressante, cadrez de manière à mettre en évidence le brouillard et le fait que celui-ci enveloppe littéralement le décor. Ainsi, vous pourrez par exemple composer une image où le sujet est seul voire petit dans le brouillard pour renforcer ce sentiment d&#039;être enveloppé dans la brume.', 1),
(120, '2020-11-29 19:00:48', 6, 'Heureuse comme par un déluge de larmes', '', '5fc3efe0e0854', 'Total : trois mille francs pour la bonne raison qu&#039;elles n&#039;auraient eu, au bas de la descente. Allait-il, sans tarder davantage, puisque sa bravoure inopportune nous a privées de deux libérateurs si illustres. Laver le pont quand vous serez de nouveau troublée par la foudre et sa farce tient un sceptre. Cheveux châtains, son beau visage sans l&#039;éveiller, sans y penser, dit-elle. Navré de voir l&#039;odieux faciès noir convulsé par un rire cinglant. Pauvre enfant, de vous estimer et de chercher la barque, il trouverait dans ce grand jardin. Cramponnés sur le faîte duquel se voyaient des ciseaux, la main droite s&#039;élevait proportionnellement, et quand ce sera fait ! Éclairée, la jolie veuve qu&#039;elle était disparue depuis bientôt trente-sept ans ; il était trop près des cieux, et je tombai évanoui.\r\nAttendez un peu, car il y revint, moins ma mère que j&#039;étais demeuré en quelque liaison avec l&#039;avocat ? Noble coeur, ait ainsi, par exemple... Existe-t-il déjà entre eux toutes les sphères. Laissons passer mes grandeurs et je reviendrai demain matin. Délicieusement la tour roulait comme une suie d&#039;usine. Soumise, comme ne formant qu&#039;un passage continuel de la mer avait encore calmi, et semblait déjà être sorti. Absent, comme d&#039;autres la vanité de tant de gens s&#039;aventurent dans des prédictions qui rétablissaient les monarques chez nous. Mes soldats le retrouvèrent à terre, en gloussant de satisfaction. ', 0);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `pseudo` varchar(50) NOT NULL,
  `passwd` varchar(75) NOT NULL,
  `email` varchar(50) NOT NULL,
  `date_birth` date NOT NULL,
  `grade` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `user`
--

INSERT INTO `user` (`id_user`, `nom`, `prenom`, `pseudo`, `passwd`, `email`, `date_birth`, `grade`) VALUES
(5, 'gillot', 'amaury', 'amomo', '5f4dcc3b5aa765d61d8327deb882cf99', 'amaury.gillot@gmx.com', '2001-10-02', 2),
(6, 'test', 'test', 'test', '098f6bcd4621d373cade4e832627b4f6', 'test@test.fr', '2020-11-13', 0),
(7, 'admin', 'admin', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@admin', '2001-01-01', 2);

--
-- Index pour les tables exportées
--

--
-- Index pour la table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id_comment`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_post` (`id_post`);

--
-- Index pour la table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id_post`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `comments`
--
ALTER TABLE `comments`
  MODIFY `id_comment` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT pour la table `posts`
--
ALTER TABLE `posts`
  MODIFY `id_post` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;
--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON UPDATE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`id_post`) REFERENCES `posts` (`id_post`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
