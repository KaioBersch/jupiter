-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 28/08/2026 às 22:46
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `jupter_dados`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `login_admin` varchar(6) NOT NULL,
  `senha_admin` varchar(255) NOT NULL,
  `senha_visivel` varchar(20) NOT NULL,
  `tipo_adm` tinyint(1) NOT NULL DEFAULT 0,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias_salao`
--

CREATE TABLE `categorias_salao` (
  `id_categoria` int(11) NOT NULL,
  `nome_categoria` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `imagem_salao`
--

CREATE TABLE `imagem_salao` (
  `id_imagem` int(11) NOT NULL,
  `id_salao` int(11) NOT NULL,
  `caminho` varchar(255) NOT NULL,
  `imagem_principal` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `salao`
--

CREATE TABLE `salao` (
  `id_salao` int(11) NOT NULL,
  `id_dono` int(11) NOT NULL,
  `nome_salao` varchar(255) NOT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cep` varchar(9) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `valor` float DEFAULT NULL,
  `situacao` enum('pendente','aceito','recusado') NOT NULL DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `salao_categoria`
--

CREATE TABLE `salao_categoria` (
  `id_salao` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nome_usuario` varchar(255) NOT NULL,
  `cpf` bigint(11) NOT NULL,
  `telefone` bigint(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `login_admin_unico` (`login_admin`);

--
-- Índices de tabela `categorias_salao`
--
ALTER TABLE `categorias_salao`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Índices de tabela `imagem_salao`
--
ALTER TABLE `imagem_salao`
  ADD PRIMARY KEY (`id_imagem`),
  ADD KEY `id_salao` (`id_salao`);

--
-- Índices de tabela `salao`
--
ALTER TABLE `salao`
  ADD PRIMARY KEY (`id_salao`),
  ADD KEY `fk_salao_usuario` (`id_dono`);

--
-- Índices de tabela `salao_categoria`
--
ALTER TABLE `salao_categoria`
  ADD PRIMARY KEY (`id_salao`,`id_categoria`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `categorias_salao`
--
ALTER TABLE `categorias_salao`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de tabela `imagem_salao`
--
ALTER TABLE `imagem_salao`
  MODIFY `id_imagem` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `salao`
--
ALTER TABLE `salao`
  MODIFY `id_salao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `imagem_salao`
--
ALTER TABLE `imagem_salao`
  ADD CONSTRAINT `imagem_salao_ibfk_1` FOREIGN KEY (`id_salao`) REFERENCES `salao` (`id_salao`) ON DELETE CASCADE;

--
-- Restrições para tabelas `salao`
--
ALTER TABLE `salao`
  ADD CONSTRAINT `fk_salao_usuario` FOREIGN KEY (`id_dono`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `salao_categoria`
--
ALTER TABLE `salao_categoria`
  ADD CONSTRAINT `fk_sc_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias_salao` (`id_categoria`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sc_salao` FOREIGN KEY (`id_salao`) REFERENCES `salao` (`id_salao`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
