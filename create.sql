SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS `haushalt`;
CREATE DATABASE `haushalt` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `haushalt`;

DROP TABLE IF EXISTS `history`;
CREATE TABLE `history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL,
  `was` varchar(255) NOT NULL,
  `art` varchar(255) NOT NULL,
  `wo` varchar(255) NOT NULL,
  `betrag` decimal(10,2) NOT NULL,
  `info` varchar(255) NOT NULL,
  `debit` varchar(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
