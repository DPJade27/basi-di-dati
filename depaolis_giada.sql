-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Gen 09, 2025 alle 10:03
-- Versione del server: 10.4.21-MariaDB
-- Versione PHP: 8.0.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `depaolis_giada`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `blog_`
--

CREATE TABLE `blog_` (
  `id` int(4) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `id_user` int(4) NOT NULL,
  `id_category` int(4) DEFAULT NULL,
  `id_subcategory` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `blog_`
--

INSERT INTO `blog_` (`id`, `title`, `description`, `image`, `created_at`, `id_user`, `id_category`, `id_subcategory`) VALUES
(96, 'che belli i gatti grassi', 'ecco una foto di un bel gattone', '../img/user_upload/gattograsso.jpeg', '2025-01-08 20:41:10', 11, 19, 8),
(97, 'i film sottovalutati di sempre', 'Consigli su quali film guardare', '', '2025-01-08 20:42:36', 11, 18, 9),
(98, 'alimentazione per combattere il cambiamento climatico', 'proponete dei piatti a basso impatto ambientale', '../img/user_upload/download (1).jpeg', '2025-01-08 20:48:11', 10, 1, 1),
(99, 'forza romaaa', 'daje la maggica', '../img/user_upload/images (1).jpeg', '2025-01-08 20:54:23', 14, 2, 5),
(100, 'hip hop is life', 'let\'s talk about some real radical stuff, btw the guy in the pic it\'s me ehyyoo', '../img/user_upload/download (4).jpeg', '2025-01-08 21:06:33', 15, 77, 7);

-- --------------------------------------------------------

--
-- Struttura della tabella `blog_coauthor`
--

CREATE TABLE `blog_coauthor` (
  `id_blog` int(4) NOT NULL,
  `id_user` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `blog_coauthor`
--

INSERT INTO `blog_coauthor` (`id_blog`, `id_user`) VALUES
(96, 10),
(99, 11),
(100, 10),
(100, 11),
(100, 14);

-- --------------------------------------------------------

--
-- Struttura della tabella `category`
--

CREATE TABLE `category` (
  `id` int(4) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `category`
--

INSERT INTO `category` (`id`, `name`) VALUES
(1, 'cucina'),
(2, 'sport'),
(3, 'arte'),
(10, 'cultura'),
(17, 'casa'),
(18, 'cinema'),
(19, 'animali'),
(76, 'meteo'),
(77, 'musica');

-- --------------------------------------------------------

--
-- Struttura della tabella `comment`
--

CREATE TABLE `comment` (
  `id` int(9) NOT NULL,
  `text` varchar(255) NOT NULL,
  `id_user` int(4) NOT NULL,
  `id_post` int(9) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `comment`
--

INSERT INTO `comment` (`id`, `text`, `id_user`, `id_post`, `created`) VALUES
(24, 'molto bello!!', 10, 106, '2025-01-08 20:48:37'),
(25, 'sono super d\'accordo!!', 14, 108, '2025-01-08 20:52:53'),
(26, 'si dai carino', 14, 106, '2025-01-08 20:53:12'),
(27, 'eccolo francone il 50enne medio', 10, 109, '2025-01-08 20:55:43'),
(28, 'bella frenkkk sei un grande', 11, 109, '2025-01-08 20:56:26'),
(29, 'ecco un\'altra VEGANA', 11, 107, '2025-01-08 20:58:26'),
(30, 'damn broo!! i love volleyball', 15, 110, '2025-01-08 21:04:49'),
(31, 'fortissimmaaaa!!', 11, 112, '2025-01-08 21:11:08'),
(32, 'ma che vuol dire? D:', 14, 113, '2025-01-08 21:13:24'),
(33, 'ma un pò di musica italiana?', 14, 112, '2025-01-08 21:13:40'),
(34, 'dai franco aggiornati!!', 10, 114, '2025-01-08 21:16:28'),
(35, 'grazie finalmente qualcuno lo doveva dire', 10, 110, '2025-01-08 21:20:39');

-- --------------------------------------------------------

--
-- Struttura della tabella `like_`
--

CREATE TABLE `like_` (
  `id_user` int(4) NOT NULL,
  `id_post` int(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `like_`
--

INSERT INTO `like_` (`id_user`, `id_post`) VALUES
(10, 106),
(14, 108),
(11, 109),
(11, 107),
(15, 110),
(15, 109),
(15, 107),
(11, 112),
(11, 111),
(14, 113),
(14, 111),
(14, 114),
(10, 111),
(10, 110);

-- --------------------------------------------------------

--
-- Struttura della tabella `post_`
--

CREATE TABLE `post_` (
  `id` int(9) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `id_user` int(4) NOT NULL,
  `id_blog` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `post_`
--

INSERT INTO `post_` (`id`, `title`, `content`, `created_at`, `id_user`, `id_blog`) VALUES
(106, 'silence', 'film del 2016, molto bello', '2025-01-08 20:46:12', 11, 97),
(107, 'passare ad una alimentazione vegetale', 'ecco la soluzione al problema appena posto', '2025-01-08 20:50:01', 10, 98),
(108, 'no dai non scherziamo', 'se un gatto è grasso vuol dire che non è in buona salute', '2025-01-08 20:51:17', 10, 96),
(109, 'il derby lo abbiamo vinto nooiii', 'semo i mejo', '2025-01-08 20:55:08', 14, 99),
(110, 'basta il calcio mi ha stufato', 'molto meglio la pallavolo non credete?', '2025-01-08 20:57:38', 11, 99),
(111, 'damnnn', 'no wayy, i can\'t believe notorious big joined my blog!!', '2025-01-08 21:08:37', 10, 100),
(112, 'lauryn hill', 'lei si che è la madre dell\'hip pop', '2025-01-08 21:10:29', 10, 100),
(113, 'wu tang clan', 'dai allora elenchiamo tutti i big dell\'hip pop', '2025-01-08 21:12:33', 11, 100),
(114, 'la cara vecchia musica italiana', 'invece di questi generi che non si capiscono, ecco un pò di foto di veri artisti', '2025-01-08 21:15:55', 14, 100);

-- --------------------------------------------------------

--
-- Struttura della tabella `post_image`
--

CREATE TABLE `post_image` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `post_id` int(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `post_image`
--

INSERT INTO `post_image` (`id`, `image_path`, `post_id`) VALUES
(6, '../img/user_upload/download.jpeg', 106),
(7, '../img/user_upload/images.jpeg', 106),
(8, '../img/user_upload/download (2).jpeg', 110),
(9, '../img/user_upload/download (3).jpeg', 110),
(10, '../img/user_upload/download (5).jpeg', 111),
(11, '../img/user_upload/download (6).jpeg', 112),
(12, '../img/user_upload/images (2).jpeg', 112),
(13, '../img/user_upload/images (3).jpeg', 113),
(14, '../img/user_upload/download (7).jpeg', 114),
(15, '../img/user_upload/download (8).jpeg', 114),
(16, '../img/user_upload/download (9).jpeg', 114);

-- --------------------------------------------------------

--
-- Struttura della tabella `subcategory`
--

CREATE TABLE `subcategory` (
  `id` int(4) NOT NULL,
  `name` varchar(20) NOT NULL,
  `id_category` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `subcategory`
--

INSERT INTO `subcategory` (`id`, `name`, `id_category`) VALUES
(1, 'pasta', 1),
(2, 'pittura', 3),
(3, 'disegno', 3),
(4, 'museo', 3),
(5, 'calcio', 2),
(6, 'scultura', 3),
(7, 'patrimonio', 10),
(8, 'gatti', 19),
(9, 'attore', 18);

-- --------------------------------------------------------

--
-- Struttura della tabella `user`
--

CREATE TABLE `user` (
  `id` int(4) NOT NULL,
  `username` varchar(20) NOT NULL,
  `name` varchar(20) NOT NULL,
  `surname` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `date_of_birth` datetime NOT NULL DEFAULT current_timestamp(),
  `password` varchar(255) NOT NULL,
  `is_premium` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `user`
--

INSERT INTO `user` (`id`, `username`, `name`, `surname`, `email`, `date_of_birth`, `password`, `is_premium`) VALUES
(10, 'jade', 'giada', 'depa', 'jade@jd.it', '0000-00-00 00:00:00', 'e807f1fcf82d132f9bb018ca6738a19f', 1),
(11, 'collins', 'jade', 'collins', 'collins@jade.it', '2009-02-04 00:00:00', 'e807f1fcf82d132f9bb018ca6738a19f', 0),
(14, 'franco', 'franco', 'francone', 'francone@fra.it', '1970-06-18 00:00:00', 'e807f1fcf82d132f9bb018ca6738a19f', 0),
(15, 'notorious', 'notorious', 'big', 'big@bg.it', '0000-00-00 00:00:00', 'e807f1fcf82d132f9bb018ca6738a19f', 0);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `blog_`
--
ALTER TABLE `blog_`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_category_blog` (`id_category`),
  ADD KEY `id_user_blog` (`id_user`),
  ADD KEY `id_subcategory_blog` (`id_subcategory`);

--
-- Indici per le tabelle `blog_coauthor`
--
ALTER TABLE `blog_coauthor`
  ADD KEY `id_blog_coauthor` (`id_blog`),
  ADD KEY `id_user_coauthor` (`id_user`);

--
-- Indici per le tabelle `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_post_comment` (`id_post`),
  ADD KEY `id_user_comment` (`id_user`);

--
-- Indici per le tabelle `like_`
--
ALTER TABLE `like_`
  ADD KEY `id_post_like` (`id_post`),
  ADD KEY `id_user_like` (`id_user`);

--
-- Indici per le tabelle `post_`
--
ALTER TABLE `post_`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_blog_post` (`id_blog`),
  ADD KEY `id_user_post` (`id_user`);

--
-- Indici per le tabelle `post_image`
--
ALTER TABLE `post_image`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_image_post` (`post_id`);

--
-- Indici per le tabelle `subcategory`
--
ALTER TABLE `subcategory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_category_subcategory` (`id_category`);

--
-- Indici per le tabelle `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `blog_`
--
ALTER TABLE `blog_`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT per la tabella `category`
--
ALTER TABLE `category`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT per la tabella `comment`
--
ALTER TABLE `comment`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT per la tabella `post_`
--
ALTER TABLE `post_`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT per la tabella `post_image`
--
ALTER TABLE `post_image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT per la tabella `subcategory`
--
ALTER TABLE `subcategory`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `user`
--
ALTER TABLE `user`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `blog_`
--
ALTER TABLE `blog_`
  ADD CONSTRAINT `id_category_blog` FOREIGN KEY (`id_category`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_subcategory_blog` FOREIGN KEY (`id_subcategory`) REFERENCES `subcategory` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_user_blog` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `blog_coauthor`
--
ALTER TABLE `blog_coauthor`
  ADD CONSTRAINT `id_blog_coauthor` FOREIGN KEY (`id_blog`) REFERENCES `blog_` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_user_coauthor` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `id_post_comment` FOREIGN KEY (`id_post`) REFERENCES `post_` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_user_comment` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `like_`
--
ALTER TABLE `like_`
  ADD CONSTRAINT `id_post_like` FOREIGN KEY (`id_post`) REFERENCES `post_` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_user_like` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `post_`
--
ALTER TABLE `post_`
  ADD CONSTRAINT `id_blog_post` FOREIGN KEY (`id_blog`) REFERENCES `blog_` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_user_post` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `post_image`
--
ALTER TABLE `post_image`
  ADD CONSTRAINT `post_image_post` FOREIGN KEY (`post_id`) REFERENCES `post_` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `subcategory`
--
ALTER TABLE `subcategory`
  ADD CONSTRAINT `id_category_subcategory` FOREIGN KEY (`id_category`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
