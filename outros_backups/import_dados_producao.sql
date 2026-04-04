-- =====================================================
-- Script de importação dos DADOS da produção
-- Mantém o schema local (colunas novas preservadas)
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

-- 1. Limpar tabelas de dados (ordem: filhas primeiro)
TRUNCATE TABLE `apfd_pessoas_detalhes`;
TRUNCATE TABLE `administrativo_pessoas`;
TRUNCATE TABLE `boe_pessoas_vinculos`;
TRUNCATE TABLE `cadcelular`;
TRUNCATE TABLE `cadveiculo`;
TRUNCATE TABLE `cadintimacao`;
TRUNCATE TABLE `administrativo`;
TRUNCATE TABLE `cadprincipal`;
TRUNCATE TABLE `cadpessoa`;
TRUNCATE TABLE `sequencias_oficio`;
TRUNCATE TABLE `usuario`;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
