-- phpMyAdmin SQL Dump
-- Estrutura do banco urbanafix sem dados

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Tabela `usuarios`
-- --------------------------------------------------------
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `senha` varchar(100) DEFAULT NULL,
  `usuario` varchar(100) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT 'pfp/default.jpg',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- --------------------------------------------------------
-- Tabela `denuncias`
-- --------------------------------------------------------
CREATE TABLE `denuncias` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `midia` varchar(255) NOT NULL,
  `tipo_midia` enum('imagem','video') NOT NULL,
  `localizacao` varchar(255) NOT NULL,
  `data_envio` datetime DEFAULT current_timestamp(),
  `aprovados` int(11) DEFAULT 0,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `usuarios_aprovar` text DEFAULT '',
  `likes` int(11) DEFAULT 0,
  `users_liked` text DEFAULT '[]',
  `peso` varchar(20) DEFAULT 'pequeno',
  `status` varchar(20) DEFAULT 'em andamento',
  PRIMARY KEY (`id`),
  KEY `fk_denuncias_usuario` (`id_usuario`),
  KEY `fk_peso_denuncia` (`peso`),
  KEY `fk_status_denuncia` (`status`),
  CONSTRAINT `fk_denuncias_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `denuncias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

COMMIT;
