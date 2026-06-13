-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Giu 08, 2026 alle 02:55
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `airport`
--
CREATE DATABASE IF NOT EXISTS `airport` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `airport`;

-- --------------------------------------------------------

--
-- Struttura della tabella `airports`
--

CREATE TABLE `airports` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(4) NOT NULL,
  `nation` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `flights`
--

CREATE TABLE `flights` (
  `id` int(11) NOT NULL,
  `priority` int(11) DEFAULT NULL,
  `scheduled_time` datetime NOT NULL,
  `validation` enum('NOT_ACCEPTED','ACCEPTED','REJECTED','CONFIRMED','DELETED') NOT NULL DEFAULT 'NOT_ACCEPTED',
  `plane_id` varchar(8) NOT NULL,
  `pilot_id` int(11) NOT NULL,
  `departure_airport_id` int(11) NOT NULL,
  `arrival_airport_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `modify_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `flight_status`
--

CREATE TABLE `flight_status` (
  `id` int(11) NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `gates`
--

CREATE TABLE `gates` (
  `id` int(11) NOT NULL,
  `gate_number` varchar(8) NOT NULL,
  `flight_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `parking_spots`
--

CREATE TABLE `parking_spots` (
  `id` int(11) NOT NULL,
  `spot_number` varchar(8) NOT NULL,
  `plane_id` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `planes`
--

CREATE TABLE `planes` (
  `plane_number` varchar(8) NOT NULL,
  `model` varchar(255) NOT NULL,
  `company_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `runways`
--

CREATE TABLE `runways` (
  `id` int(11) NOT NULL,
  `runway_number` varchar(8) NOT NULL,
  `flight_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `taxiways`
--

CREATE TABLE `taxiways` (
  `id` int(11) NOT NULL,
  `taxiway_number` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `taxiway_flight`
--

CREATE TABLE `taxiway_flight` (
  `id` int(11) NOT NULL,
  `flight_id` int(11) NOT NULL,
  `taxiway_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` char(128) NOT NULL,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `password_reset` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `airports`
--
ALTER TABLE `airports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indici per le tabelle `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_name` (`company_name`);

--
-- Indici per le tabelle `flights`
--
ALTER TABLE `flights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pilot_fk` (`pilot_id`),
  ADD KEY `airport_fk1` (`departure_airport_id`),
  ADD KEY `airport_fk2` (`arrival_airport_id`),
  ADD KEY `fstatus_fk` (`status_id`),
  ADD KEY `plane_fk` (`plane_id`),
  ADD KEY `modify_id` (`modify_id`);

--
-- Indici per le tabelle `flight_status`
--
ALTER TABLE `flight_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `status` (`status`);

--
-- Indici per le tabelle `gates`
--
ALTER TABLE `gates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gate_number` (`gate_number`),
  ADD KEY `flight_fk2` (`flight_id`);

--
-- Indici per le tabelle `parking_spots`
--
ALTER TABLE `parking_spots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plane_fk2` (`plane_id`);

--
-- Indici per le tabelle `planes`
--
ALTER TABLE `planes`
  ADD PRIMARY KEY (`plane_number`),
  ADD KEY `company_fk2` (`company_id`);

--
-- Indici per le tabelle `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indici per le tabelle `runways`
--
ALTER TABLE `runways`
  ADD PRIMARY KEY (`id`),
  ADD KEY `flight_fk4` (`flight_id`);

--
-- Indici per le tabelle `taxiways`
--
ALTER TABLE `taxiways`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `taxiway_flight`
--
ALTER TABLE `taxiway_flight`
  ADD PRIMARY KEY (`id`),
  ADD KEY `flight_fk` (`flight_id`),
  ADD KEY `taxiway_id` (`taxiway_id`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_fk` (`role_id`),
  ADD KEY `company_fk` (`company_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `airports`
--
ALTER TABLE `airports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `flights`
--
ALTER TABLE `flights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `flight_status`
--
ALTER TABLE `flight_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `gates`
--
ALTER TABLE `gates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `parking_spots`
--
ALTER TABLE `parking_spots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `runways`
--
ALTER TABLE `runways`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `taxiways`
--
ALTER TABLE `taxiways`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `taxiway_flight`
--
ALTER TABLE `taxiway_flight`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `flights`
--
ALTER TABLE `flights`
  ADD CONSTRAINT `airport_fk1` FOREIGN KEY (`departure_airport_id`) REFERENCES `airports` (`id`),
  ADD CONSTRAINT `airport_fk2` FOREIGN KEY (`arrival_airport_id`) REFERENCES `airports` (`id`),
  ADD CONSTRAINT `fstatus_fk` FOREIGN KEY (`status_id`) REFERENCES `flight_status` (`id`),
  ADD CONSTRAINT `modify_id` FOREIGN KEY (`modify_id`) REFERENCES `flights` (`id`),
  ADD CONSTRAINT `pilot_fk` FOREIGN KEY (`pilot_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `plane_fk` FOREIGN KEY (`plane_id`) REFERENCES `planes` (`plane_number`);

--
-- Limiti per la tabella `gates`
--
ALTER TABLE `gates`
  ADD CONSTRAINT `flight_fk2` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`);

--
-- Limiti per la tabella `parking_spots`
--
ALTER TABLE `parking_spots`
  ADD CONSTRAINT `plane_fk2` FOREIGN KEY (`plane_id`) REFERENCES `planes` (`plane_number`);

--
-- Limiti per la tabella `planes`
--
ALTER TABLE `planes`
  ADD CONSTRAINT `company_fk2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Limiti per la tabella `runways`
--
ALTER TABLE `runways`
  ADD CONSTRAINT `flight_fk4` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`);

--
-- Limiti per la tabella `taxiway_flight`
--
ALTER TABLE `taxiway_flight`
  ADD CONSTRAINT `flight_fk` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`),
  ADD CONSTRAINT `taxiway_id` FOREIGN KEY (`taxiway_id`) REFERENCES `taxiways` (`id`);

--
-- Limiti per la tabella `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `company_fk` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `role_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
