-- phpMyAdmin SQL Dump
-- version 3.2.5
-- http://www.phpmyadmin.net
--
-- Vert: localhost
-- Generert den: 07. Nov, 2010 21:11 PM
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
  `alternative` varchar(255) NOT NULL,
  `correct` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Tabellstruktur for tabell `category`
--

CREATE TABLE `category` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(30) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur for tabell `question`
--

CREATE TABLE `question` (
  `qid` int(11) NOT NULL AUTO_INCREMENT,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `removed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`qid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellstruktur for tabell `question_category`
--

CREATE TABLE `question_category` (
  `qid` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  UNIQUE KEY `qid` (`qid`,`cid`)
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
  PRIMARY KEY (`uid`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

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
