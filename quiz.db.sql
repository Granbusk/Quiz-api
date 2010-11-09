-- phpMyAdmin SQL Dump
-- version 3.2.5
-- http://www.phpmyadmin.net
--
-- Vert: localhost
-- Generert den: 09. Nov, 2010 22:48 PM
-- Tjenerversjon: 5.1.44
-- PHP-Versjon: 5.3.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `quiz`
--

-- --------------------------------------------------------

--
-- Tabellstruktur for tabell `alternative`
--

CREATE TABLE `alternative` (
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `qid` int(11) NOT NULL,
  `text` varchar(255) CHARACTER SET latin1 NOT NULL,
  `correct` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=61 ;

-- --------------------------------------------------------

--
-- Tabellstruktur for tabell `group`
--

CREATE TABLE `group` (
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) CHARACTER SET latin1 NOT NULL,
  `password` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
  PRIMARY KEY (`gid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Tabellstruktur for tabell `question`
--

CREATE TABLE `question` (
  `qid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `text` varchar(255) NOT NULL,
  `answer_explanation` text NOT NULL,
  `removed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`qid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Tabellstruktur for tabell `question_group`
--

CREATE TABLE `question_group` (
  `qid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  UNIQUE KEY `qid` (`qid`,`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur for tabell `user`
--

CREATE TABLE `user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(32) NOT NULL,
  `gravatar` varchar(255) NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Tabellstruktur for tabell `user_answer`
--

CREATE TABLE `user_answer` (
  `uid` int(11) NOT NULL,
  `qid` int(11) NOT NULL,
  `correct` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `uid` (`uid`,`qid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur for tabell `user_group`
--

CREATE TABLE `user_group` (
  `uid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `moderator` tinyint(1) NOT NULL DEFAULT '0',
  `administrator` tinyint(1) NOT NULL DEFAULT '0',
  `left` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `uid` (`uid`,`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
