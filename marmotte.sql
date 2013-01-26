-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Sam 26 Janvier 2013 à 13:47
-- Version du serveur: 5.1.63
-- Version de PHP: 5.3.3-7+squeeze14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `cn6`
--

-- --------------------------------------------------------

--
-- Structure de la table `candidats`
--

DROP TABLE IF EXISTS `candidats`;
CREATE TABLE IF NOT EXISTS `candidats` (
  `cle` text CHARACTER SET utf8 NOT NULL,
  `anneecandidature` int(11) NOT NULL,
  `nom` text CHARACTER SET utf8 NOT NULL,
  `prenom` text CHARACTER SET utf8 NOT NULL,
  `grade` enum('CR2','CR1','DR2','DR1','DRCE1','DRCE2','ChaireMC','ChairePR','Emerite','MC','PR','PhD','HDR','None') CHARACTER SET utf8 NOT NULL,
  `theseAnnee` text CHARACTER SET utf8 NOT NULL,
  `theseLieu` text CHARACTER SET utf8 NOT NULL,
  `HDRAnnee` text CHARACTER SET utf8 NOT NULL,
  `HDRLieu` text CHARACTER SET utf8 NOT NULL,
  `labo1` text CHARACTER SET utf8 NOT NULL,
  `labo2` text CHARACTER SET utf8 NOT NULL,
  `labo3` text CHARACTER SET utf8 NOT NULL,
  `theme1` text CHARACTER SET utf8 NOT NULL,
  `theme2` text CHARACTER SET utf8 NOT NULL,
  `theme3` text CHARACTER SET utf8 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `parcours` text CHARACTER SET utf8 NOT NULL,
  `productionResume` text CHARACTER SET utf8 NOT NULL,
  `projetrecherche` text CHARACTER SET utf8 NOT NULL,
  `concours` text CHARACTER SET utf8 NOT NULL,
  `fichiers` text CHARACTER SET utf8 NOT NULL,
  `date_recrutement` text CHARACTER SET utf8 NOT NULL,
  `avissousjury` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `chercheurs`
--

DROP TABLE IF EXISTS `chercheurs`;
CREATE TABLE IF NOT EXISTS `chercheurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(40) CHARACTER SET utf8 NOT NULL,
  `prenom` varchar(40) CHARACTER SET utf8 NOT NULL,
  `unite` varchar(50) CHARACTER SET utf8 NOT NULL,
  `grade` enum('CR2','CR1','DR2','DR1','DRCE1','DRCE2','ChaireMC','ChairePR','Emerite','MC','PR','PhD','HDR','None') CHARACTER SET utf8 NOT NULL,
  `date_recrutement` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=61 ;

-- --------------------------------------------------------

--
-- Structure de la table `evaluations`
--

DROP TABLE IF EXISTS `evaluations`;
CREATE TABLE IF NOT EXISTS `evaluations` (
  `statut` enum('vierge','prerapport','rapport','publie','supprime') CHARACTER SET utf8 NOT NULL,
  `id_session` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_origine` int(11) NOT NULL,
  `nom` varchar(40) CHARACTER SET utf8 NOT NULL,
  `prenom` varchar(40) CHARACTER SET utf8 NOT NULL,
  `unite` varchar(50) CHARACTER SET utf8 NOT NULL,
  `ecole` text CHARACTER SET utf8 NOT NULL,
  `grade` enum('CR2','CR1','DR2','DR1','DRCE1','DRCE2','ChaireMC','ChairePR','Emerite','MC','PR','PhD','HDR','None') CHARACTER SET utf8 NOT NULL,
  `type` enum('Evaluation-Vague','Evaluation-MiVague','Promotion','Equivalence','Candidature','Suivi-PostEvaluation','Titularisation','Affectation','Reconstitution','Changement-Directeur','Changement-Directeur-Adjoint','Renouvellement','Association','Ecole','Comite-Evaluation','Generique') CHARACTER SET utf8 NOT NULL,
  `concours` text CHARACTER SET utf8 NOT NULL,
  `rapporteur` text CHARACTER SET utf8 NOT NULL,
  `rapporteur2` text CHARACTER SET utf8 NOT NULL,
  `prerapport` text CHARACTER SET utf8 NOT NULL,
  `theseAnnee` text CHARACTER SET utf8 NOT NULL,
  `theseLieu` text CHARACTER SET utf8 NOT NULL,
  `HDRAnnee` text CHARACTER SET utf8 NOT NULL,
  `HDRLieu` text CHARACTER SET utf8 NOT NULL,
  `anneesequivalence` int(11) NOT NULL,
  `labo1` text CHARACTER SET utf8 NOT NULL,
  `labo2` text CHARACTER SET utf8 NOT NULL,
  `labo3` text CHARACTER SET utf8 NOT NULL,
  `theme1` text CHARACTER SET utf8 NOT NULL,
  `theme2` text CHARACTER SET utf8 NOT NULL,
  `theme3` text CHARACTER SET utf8 NOT NULL,
  `anciennete_grade` text CHARACTER SET utf8 NOT NULL,
  `date_recrutement` text CHARACTER SET utf8 NOT NULL,
  `production` text CHARACTER SET utf8 NOT NULL,
  `production_notes` text CHARACTER SET utf8 NOT NULL,
  `transfert` text CHARACTER SET utf8 NOT NULL,
  `transfert_notes` text CHARACTER SET utf8 NOT NULL,
  `encadrement` text CHARACTER SET utf8 NOT NULL,
  `encadrement_notes` text CHARACTER SET utf8 NOT NULL,
  `responsabilites` text CHARACTER SET utf8 NOT NULL,
  `responsabilites_notes` text CHARACTER SET utf8 NOT NULL,
  `mobilite` text CHARACTER SET utf8 NOT NULL,
  `mobilite_notes` text CHARACTER SET utf8 NOT NULL,
  `animation` text CHARACTER SET utf8 NOT NULL,
  `animation_notes` text CHARACTER SET utf8 NOT NULL,
  `rayonnement` text CHARACTER SET utf8 NOT NULL,
  `rayonnement_notes` text CHARACTER SET utf8 NOT NULL,
  `rapport` text CHARACTER SET utf8 NOT NULL,
  `avis` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `auteur` varchar(40) CHARACTER SET utf8 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prerapport2` text CHARACTER SET utf8 NOT NULL,
  `production2` text CHARACTER SET utf8 NOT NULL,
  `transfert2` text CHARACTER SET utf8 NOT NULL,
  `encadrement2` text CHARACTER SET utf8 NOT NULL,
  `responsabilites2` text CHARACTER SET utf8 NOT NULL,
  `mobilite2` text CHARACTER SET utf8 NOT NULL,
  `animation2` text CHARACTER SET utf8 NOT NULL,
  `rayonnement2` text CHARACTER SET utf8 NOT NULL,
  `avis1` text CHARACTER SET utf8 NOT NULL,
  `avis2` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10634 ;

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(60) CHARACTER SET utf8 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Structure de la table `units`
--

DROP TABLE IF EXISTS `units`;
CREATE TABLE IF NOT EXISTS `units` (
  `nickname` text CHARACTER SET utf8 NOT NULL,
  `code` text CHARACTER SET utf8 NOT NULL,
  `fullname` text CHARACTER SET utf8 NOT NULL,
  `directeur` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `login` varchar(40) CHARACTER SET utf8 NOT NULL,
  `passHash` varchar(40) CHARACTER SET utf8 NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  `permissions` int(11) NOT NULL DEFAULT '0',
  `email` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;