-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Genereertijd: 03 Aug 2011 om 19:44
-- Serverversie: 5.5.8
-- PHP-Versie: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `uren`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `tasks`
--

CREATE TABLE IF NOT EXISTS `tasks` (
  `task_id` int(255) NOT NULL AUTO_INCREMENT,
  `task_name` varchar(255) NOT NULL,
  PRIMARY KEY (`task_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=35 ;

--
-- Gegevens worden uitgevoerd voor tabel `tasks`
--

INSERT INTO `tasks` (`task_id`, `task_name`) VALUES
(34, 'Chillen');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `uren`
--

CREATE TABLE IF NOT EXISTS `uren` (
  `entry_id` int(250) NOT NULL AUTO_INCREMENT,
  `datum` int(250) NOT NULL,
  `aantal_uren` int(250) NOT NULL,
  `user` int(250) NOT NULL,
  `commentaar` varchar(250) NOT NULL,
  `taak_id` int(255) DEFAULT '0',
  PRIMARY KEY (`entry_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=342 ;

--
-- Gegevens worden uitgevoerd voor tabel `uren`
--


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(255) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(1000) NOT NULL,
  `user_uren` int(255) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Gegevens worden uitgevoerd voor tabel `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `user_uren`) VALUES
(9, 'Romy', 0),
(10, 'Jelle', 0),
(11, 'Samen', 0);
