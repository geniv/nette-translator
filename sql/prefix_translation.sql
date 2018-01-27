-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Počítač: localhost:3306
-- Vytvořeno: Sob 27. led 2018, 20:28
-- Verze serveru: 10.1.26-MariaDB-0+deb9u1
-- Verze PHP: 7.0.27-0+deb9u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `netteweb`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `prefix_translation`
--

CREATE TABLE `prefix_translation` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_locale` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'vazba na jazyk',
  `id_ident` bigint(20) UNSIGNED NOT NULL COMMENT 'vazba na ident',
  `translate` text COMMENT 'preklad'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='preklady';

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `prefix_translation`
--
ALTER TABLE `prefix_translation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `locale_ident_UNIQUE` (`id_locale`,`id_ident`),
  ADD KEY `fk_translation_locale_idx` (`id_locale`),
  ADD KEY `fk_translation_translation_ident_idx` (`id_ident`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `prefix_translation`
--
ALTER TABLE `prefix_translation`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `prefix_translation`
--
ALTER TABLE `prefix_translation`
  ADD CONSTRAINT `fk_translation_locale` FOREIGN KEY (`id_locale`) REFERENCES `prefix_locale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_translation_translation_ident` FOREIGN KEY (`id_ident`) REFERENCES `prefix_translation_ident` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
