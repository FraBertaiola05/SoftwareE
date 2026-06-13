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

--
-- Dump dei dati per la tabella `airports`
--

INSERT INTO `airports` (`id`, `name`, `code`, `nation`, `city`) VALUES
(1, 'Povo International Airport', 'POV', 'Italy', 'Povo'),
(2, 'John F. Kennedy International Airport', 'JFK', 'United States of America', 'New York'),
(3, 'Heathrow Airport', 'LHR', 'United Kingdom', 'London'),
(4, 'Beijing Capital International Airport', 'PEK', 'China', 'Beijing');

-- --------------------------------------------------------

--
-- Struttura della tabella `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `companies`
--

INSERT INTO `companies` (`id`, `company_name`) VALUES
(10, 'Air China'),
(2, 'American Airlines'),
(6, 'China Eastern Airlines'),
(4, 'China Southern Airlines'),
(3, 'Delta Air Lines'),
(7, 'Ryanair'),
(8, 'SkyWest Airlines'),
(5, 'Southwest Airlines'),
(9, 'Turkish Airlines'),
(1, 'United Airlines');

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

--
-- Dump dei dati per la tabella `flights`
--

INSERT INTO `flights` (`id`, `priority`, `scheduled_time`, `validation`, `plane_id`, `pilot_id`, `departure_airport_id`, `arrival_airport_id`, `status_id`, `modify_id`) VALUES
(1, 1, '2026-07-02 09:00:00', 'CONFIRMED', 'RYR32KE', 2, 3, 1, 5, NULL),
(2, 1, '2026-07-02 14:20:00', 'CONFIRMED', 'RYR32KE', 2, 1, 3, 1, NULL),
(3, 2, '2026-07-03 10:30:00', 'CONFIRMED', 'DAL222', 3, 1, 2, 3, NULL),
(4, 2, '2026-07-03 21:00:00', 'CONFIRMED', 'DAL222', 3, 2, 1, 1, NULL),
(16, NULL, '2026-06-17 18:02:00', 'DELETED', 'RYR32KE', 2, 1, 3, 3, NULL),
(20, NULL, '2026-06-26 01:08:00', 'CONFIRMED', 'RYR32KE', 2, 1, 3, 1, NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `flight_status`
--

CREATE TABLE `flight_status` (
  `id` int(11) NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `flight_status`
--

INSERT INTO `flight_status` (`id`, `status`) VALUES
(2, 'Boarding'),
(6, 'Cancelled'),
(7, 'Finished'),
(4, 'InQueueLanding'),
(3, 'InQueueTakeOff'),
(5, 'Landed'),
(1, 'Scheduled');

-- --------------------------------------------------------

--
-- Struttura della tabella `gates`
--

CREATE TABLE `gates` (
  `id` int(11) NOT NULL,
  `gate_number` varchar(8) NOT NULL,
  `flight_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `gates`
--

INSERT INTO `gates` (`id`, `gate_number`, `flight_id`) VALUES
(1, 'A1', NULL),
(2, 'A2', NULL),
(3, 'A3', NULL),
(4, 'A4', NULL),
(5, 'A5', NULL),
(6, 'B1', NULL),
(7, 'B2', NULL),
(8, 'B3', NULL),
(9, 'B4', NULL),
(10, 'B5', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `parking_spots`
--

CREATE TABLE `parking_spots` (
  `id` int(11) NOT NULL,
  `spot_number` varchar(8) NOT NULL,
  `plane_id` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `parking_spots`
--

INSERT INTO `parking_spots` (`id`, `spot_number`, `plane_id`) VALUES
(1, 'PA1', 'RYR32KE'),
(2, 'PA2', NULL),
(3, 'PA3', NULL),
(4, 'PA4', NULL),
(5, 'PA5', NULL),
(6, 'PA6', NULL),
(7, 'PA7', NULL),
(8, 'PA8', NULL),
(9, 'PA9', NULL),
(10, 'PA10', NULL),
(11, 'PA11', NULL),
(12, 'PA12', NULL),
(13, 'PA13', NULL),
(14, 'PB1', NULL),
(15, 'PB2', NULL),
(16, 'PB3', NULL),
(17, 'PB4', NULL),
(18, 'PB5', NULL),
(19, 'PB6', NULL),
(20, 'PB7', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `planes`
--

CREATE TABLE `planes` (
  `plane_number` varchar(8) NOT NULL,
  `model` varchar(255) NOT NULL,
  `company_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `planes`
--

INSERT INTO `planes` (`plane_number`, `model`, `company_id`) VALUES
('DAL222', 'Airbus A330-941', 3),
('RYR32KE', 'Boeing 737 MAX 8-200', 7);

-- --------------------------------------------------------

--
-- Struttura della tabella `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(6, 'Airline Manager'),
(7, 'Airport Analyst'),
(4, 'Gate Agent'),
(3, 'Ground Crew'),
(2, 'Pilot'),
(5, 'System Admin'),
(1, 'Tower Controller');

-- --------------------------------------------------------

--
-- Struttura della tabella `runways`
--

CREATE TABLE `runways` (
  `id` int(11) NOT NULL,
  `runway_number` varchar(8) NOT NULL,
  `flight_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `runways`
--

INSERT INTO `runways` (`id`, `runway_number`, `flight_id`) VALUES
(1, 'A1', NULL),
(2, 'A2', NULL),
(3, 'B1', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `taxiways`
--

CREATE TABLE `taxiways` (
  `id` int(11) NOT NULL,
  `taxiway_number` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `taxiways`
--

INSERT INTO `taxiways` (`id`, `taxiway_number`) VALUES
(1, 'A1'),
(2, 'A2');

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
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `name`, `surname`, `role_id`, `company_id`, `password_reset`) VALUES
(1, 'admin@admin.com', 'c7ad44cbad762a5da0a452f9e854fdc1e0e7a52a38015f23f3eab1d80b931dd472634dfac71cd34ebc35d16ab7fb8a90c81f975113d6c7538dc69dd8de9077ec', 'Albert', 'Robinson', 5, NULL, 0),
(2, 'pilot@pilot.com', 'a4317322c01ef6d39c2ec0d46cb5070d973c7ea8c4f744a80c17d8a7b318d8799410f0149bdeca412dcf1510df369b34da3067eb5d4e5b880142ce0013589aff', 'Lucas', 'Fly', 2, 7, 0),
(3, 'pilota@pilota.com', '1d575c9b088d664e83b9c9c04d38a107e6dd75edfed386b4357b1f3578bdd0e8ade0731b4a81a2153e9bf9095251ba4f2aa2bd8f18ff1266aa63886f48b2ff60', 'Luca', 'Volo', 2, 3, 0),
(14, 'analyst@analyst.com', 'cc731411d0924f9ac456d49df60e525cea0d7aad1a652577859d794bfc830276b668f78421a8ed3dbc66f83b0d8e69a49b4779f43f2a56750ea7e25c8718be87', 'ana', 'list', 7, NULL, 0),
(15, 'airline@airline.com', '20e335154f790d5ac446712b2511b83c7292efa88665d9968289c2cdd83c7bbe74f87174faf2340cd6368b76453d592591bddbb4a3d417f71069ca205f8c05a7', 'air', 'line', 6, 7, 0),
(16, 'ground@crew.com', 'fff7beb75e9ed4080857ffdf74e074a7b8a63923ce81606bc914c3cf89838091e54082060a54d32a45e9e560ad4bbea85689ae44581556e6d5203dc6fa08df2e', 'ground', 'crew', 3, NULL, 0),
(17, 'tower@controller.com', '00c031b136a903266a44540cb4846430bfd91c8673b40bdeca50c017e9220e4dda3b429da92fb572da3a52681628686b86853f7d5d75d230031b6769593e8351', 'tower', 'controller', 1, NULL, 0),
(18, 'gate@agent.com', 'bc2a235f2e2cb06b3c9df4932fc60146dd0b4771943ae86a6612d222f3aaf8027b357383569245aabd8508b0b6804a2bb5663bbde49567d663d3c9c737983f0b', 'gate', 'agent', 4, NULL, 0);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `flights`
--
ALTER TABLE `flights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT per la tabella `flight_status`
--
ALTER TABLE `flight_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT per la tabella `gates`
--
ALTER TABLE `gates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `parking_spots`
--
ALTER TABLE `parking_spots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT per la tabella `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT per la tabella `runways`
--
ALTER TABLE `runways`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT per la tabella `taxiways`
--
ALTER TABLE `taxiways`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `taxiway_flight`
--
ALTER TABLE `taxiway_flight`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

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
