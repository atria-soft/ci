-- phpMyAdmin SQL Dump
-- version 4.4.13.1
-- http://www.phpmyadmin.net
--
-- Client :  atriasofroot.mysql.db
-- Généré le :  Mar 20 Octobre 2015 à 10:26
-- Version du serveur :  5.5.44-0+deb7u1-log
-- Version de PHP :  5.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Structure de la table `CI_group`
--

CREATE TABLE IF NOT EXISTS `CI_group` (
  `id` int(11) NOT NULL,
  `user-name` varchar(256) NOT NULL,
  `lib-name` varchar(256) NOT NULL,
  `lib-branch` varchar(256) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

--
-- Structure de la table `COVERAGE_list`
--

CREATE TABLE IF NOT EXISTS `COVERAGE_list` (
  `id` int(11) NOT NULL,
  `id-group` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `sha1` varchar(256) NOT NULL,
  `executed` int(11) NOT NULL,
  `executable` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=latin1;

--
-- Structure de la table `COVERAGE_snapshot`
--

CREATE TABLE IF NOT EXISTS `COVERAGE_snapshot` (
  `id` int(11) NOT NULL,
  `id-group` int(11) NOT NULL,
  `id-list` int(11) NOT NULL,
  `json` text NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Structure de la table `TEST_list`
--

CREATE TABLE IF NOT EXISTS `TEST_list` (
  `id` int(11) NOT NULL,
  `id-group` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `sha1` varchar(256) CHARACTER SET latin1 NOT NULL,
  `passed` int(11) NOT NULL,
  `total` int(11) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=131 DEFAULT CHARSET=utf8;

--
-- Structure de la table `TEST_snapshot`
--

CREATE TABLE IF NOT EXISTS `TEST_snapshot` (
  `id` int(11) NOT NULL,
  `id-group` int(11) NOT NULL,
  `id-list` int(11) NOT NULL,
  `json` text NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Structure de la table `WARNING_list`
--

CREATE TABLE IF NOT EXISTS `WARNING_list` (
  `id` int(11) NOT NULL,
  `id-group` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `sha1` varchar(256) CHARACTER SET latin1 NOT NULL,
  `warning` int(11) NOT NULL,
  `error` int(11) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=168 DEFAULT CHARSET=utf8;

--
-- Structure de la table `WARNING_snapshot`
--

CREATE TABLE IF NOT EXISTS `WARNING_snapshot` (
  `id` int(11) NOT NULL,
  `id-group` int(11) NOT NULL,
  `id-list` int(11) NOT NULL,
  `json` text NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Index pour la table `CI_group`
--
ALTER TABLE `CI_group`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `COVERAGE_list`
--
ALTER TABLE `COVERAGE_list`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `COVERAGE_snapshot`
--
ALTER TABLE `COVERAGE_snapshot`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id-group` (`id-group`);

--
-- Index pour la table `TEST_list`
--
ALTER TABLE `TEST_list`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `TEST_snapshot`
--
ALTER TABLE `TEST_snapshot`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `WARNING_list`
--
ALTER TABLE `WARNING_list`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `WARNING_snapshot`
--
ALTER TABLE `WARNING_snapshot`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `CI_group`
--
ALTER TABLE `CI_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT pour la table `COVERAGE_list`
--
ALTER TABLE `COVERAGE_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=52;
--
-- AUTO_INCREMENT pour la table `COVERAGE_snapshot`
--
ALTER TABLE `COVERAGE_snapshot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT pour la table `TEST_list`
--
ALTER TABLE `TEST_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=131;
--
-- AUTO_INCREMENT pour la table `TEST_snapshot`
--
ALTER TABLE `TEST_snapshot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT pour la table `WARNING_list`
--
ALTER TABLE `WARNING_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=168;
--
-- AUTO_INCREMENT pour la table `WARNING_snapshot`
--
ALTER TABLE `WARNING_snapshot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
