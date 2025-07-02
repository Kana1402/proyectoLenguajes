SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
--
-- Base de datos: taller
--
-- DROP DATABASE IF EXISTS taller;
-- CREATE DATABASE taller DEFAULT CHARACTER SET utf8 COLLATE utf8_spanish_ci;
USE taller;
-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla Artefacto
--
DROP TABLE IF EXISTS artefacto;
CREATE TABLE artefacto (
  id int(11) NOT NULL AUTO_INCREMENT,
  idCliente int(11) NOT NULL,
  serie varchar(15) UNIQUE NOT NULL,
  marca varchar(15) COLLATE utf8_spanish_ci NOT NULL,
  modelo varchar(15) COLLATE utf8_spanish_ci NOT NULL,
  categoria varchar(15) COLLATE utf8_spanish_ci NOT NULL,
  descripcion varchar(50) COLLATE utf8_spanish_ci NOT NULL,  

  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

DROP TABLE IF EXISTS cliente;
CREATE TABLE cliente (
  id int(11) NOT NULL AUTO_INCREMENT,  
  idCliente Varchar(15) NOT NULL,
  nombre Varchar (30) COLLATE utf8_spanish_ci NOT NULL,
  apellido1 Varchar (15) COLLATE utf8_spanish_ci NOT NULL,
  apellido2 Varchar (15) COLLATE utf8_spanish_ci NOT NULL,
  telefono Varchar (9) NOT NULL,
  celular Varchar (9),
  direccion Varchar (255) COLLATE utf8_spanish_ci,
  correo Varchar (100) NOT NULL,
  fechaIngreso Datetime DEFAULT now(),
  PRIMARY KEY (id),
  UNIQUE KEY idx_Cliente (idCliente),
  UNIQUE KEY idx_Correo (correo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;


DROP TABLE IF EXISTS usuario;
CREATE TABLE usuario (
  id int(11) NOT NULL AUTO_INCREMENT,  
  idUsuario Varchar(15) NOT NULL,
  correo Varchar(100) NOT NULL,
  rol int not NULL,
  passw varchar(255) not NULL,
  ultimoAcceso Datetime,
  tkRef varchar(255) DEFAULT NULL, -- Token de referencia para la sesión
  PRIMARY KEY (id),
  UNIQUE KEY idx_Usuario (idUsuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

DROP TABLE IF EXISTS caso;
CREATE TABLE caso (
  id int(11) NOT NULL AUTO_INCREMENT,  
  idTecnico Varchar(15) NOT NULL,
  idCreador Varchar(15) NOT NULL, -- Se relaciona al empleado que creó el caso
  idArtefacto int(11) NOT NULL,
  descripcion Varchar (100) COLLATE utf8_spanish_ci NOT NULL,
  fechaEntrada Date,
  fechaSalida Date,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

DROP TABLE IF EXISTS historialCaso;
CREATE TABLE historialCaso (
  id int(11) NOT NULL AUTO_INCREMENT,  
  idCaso int(11) NOT NULL,
  idResponsable int(11) not NULL,
  estado int DEFAULT 0,  -- 0 Ingresado 1-Diagnostico 2-Espera Repuesto 3-Reparado 4-Entregado
  fechaCambio Date,
  descripcion VARCHAR(255) COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

DROP TABLE IF EXISTS administrador;
CREATE TABLE administrador (
  id INT(11) NOT NULL AUTO_INCREMENT,
  idAdministrador VARCHAR(15) NOT NULL,
  nombre VARCHAR(30) COLLATE utf8_spanish_ci NOT NULL,
  apellido1 VARCHAR(15) COLLATE utf8_spanish_ci NOT NULL,
  apellido2 VARCHAR(15) COLLATE utf8_spanish_ci NOT NULL,
  telefono VARCHAR(9) NOT NULL,
  celular VARCHAR(9),
  direccion VARCHAR(255) COLLATE utf8_spanish_ci,
  correo VARCHAR(100) NOT NULL,
  fechaIngreso DATETIME DEFAULT NOW(),
  PRIMARY KEY (id),
  UNIQUE KEY idx_Administrador (idAdministrador),
  UNIQUE KEY idx_CorreoAdministrador (correo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

COMMIT;