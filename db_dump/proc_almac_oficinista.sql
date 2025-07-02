USE taller;
DELIMITER $$

-- =================================================================
-- VISTA PARA LEER DATOS DE OFICINISTAS
-- Nota: Esta vista asume que el correo en 'usuario' es el principal para el login.
-- =================================================================
CREATE OR REPLACE VIEW vista_oficinistas AS
SELECT 
    o.id,
    o.idOficinista,
    o.nombre,
    o.apellido1,
    o.apellido2,
    o.telefono,
    o.celular,
    o.direccion,
    u.correo, -- Correo usado para el login
    u.rol
FROM oficinista o
JOIN usuario u ON o.idOficinista = u.idUsuario;
$$

-- =================================================================
-- PROCEDIMIENTO PARA CREAR UN NUEVO OFICINISTA (CORREGIDO)
-- Ahora es un PROCEDIMIENTO y maneja la creación del usuario y del oficinista.
-- =================================================================
DROP PROCEDURE IF EXISTS nuevoOficinista$$
CREATE PROCEDURE nuevoOficinista (
    _idOficinista VARCHAR(15),
    _nombre VARCHAR(30),
    _apellido1 VARCHAR(15),
    _apellido2 VARCHAR(15),
    _telefono VARCHAR(9),
    _celular VARCHAR(9),
    _direccion VARCHAR(255),
    _correo VARCHAR(100),
    _passw VARCHAR(255) -- Se añade el parámetro para la contraseña
)
BEGIN
    -- Manejador de errores para revertir la transacción si algo falla
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error: No se pudo crear el oficinista. Se revirtieron los cambios.' AS Resultado;
    END;

    START TRANSACTION;
    
    -- 1. Crear el registro en la tabla 'usuario' para la autenticación
    -- Se asume que el ROL para oficinista es '2'
    INSERT INTO usuario(idUsuario, correo, rol, passw) 
    VALUES (_idOficinista, _correo, 2, _passw);
    
    -- 2. Crear el registro en la tabla 'oficinista'
    INSERT INTO oficinista(idOficinista, nombre, apellido1, apellido2, telefono, celular, direccion, correo) 
    VALUES (_idOficinista, _nombre, _apellido1, _apellido2, _telefono, _celular, _direccion, _correo);
    
    COMMIT;
    SELECT 'Oficinista creado con éxito.' AS Resultado;
END$$

-- =================================================================
-- PROCEDIMIENTO PARA ELIMINAR UN OFICINISTA (CORREGIDO)
-- Ahora es un PROCEDIMIENTO con transacción para mayor seguridad.
-- =================================================================
DROP PROCEDURE IF EXISTS eliminarOficinista$$
CREATE PROCEDURE eliminarOficinista (_id_oficinista INT)
BEGIN
    DECLARE _idUsuarioABorrar VARCHAR(15);

    -- Manejador de errores
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error: No se pudo eliminar el oficinista. Se revirtieron los cambios.' AS Resultado;
    END;

    -- Obtener el idUsuario a partir del id de la tabla oficinista
    SELECT idOficinista INTO _idUsuarioABorrar FROM oficinista WHERE id = _id_oficinista;
    
    IF _idUsuarioABorrar IS NOT NULL THEN
        START TRANSACTION;
        
        -- 1. Eliminar de la tabla oficinista
        DELETE FROM oficinista WHERE id = _id_oficinista;
        
        -- 2. Eliminar de la tabla usuario
        DELETE FROM usuario WHERE idUsuario = _idUsuarioABorrar;
        
        COMMIT;
        SELECT 'Oficinista eliminado con éxito.' AS Resultado;
    ELSE
        SELECT 'Error: No se encontró un oficinista con el ID proporcionado.' AS Resultado;
    END IF;
END$$

-- =================================================================
-- PROCEDIMIENTO PARA EDITAR UN OFICINISTA (CORREGIDO)
-- Ahora sincroniza el correo en la tabla 'usuario'.
-- =================================================================
DROP PROCEDURE IF EXISTS editarOficinista$$
CREATE PROCEDURE editarOficinista (
    _id INT,
    _idOficinista VARCHAR(15),
    _nombre VARCHAR(30),
    _apellido1 VARCHAR(15),
    _apellido2 VARCHAR(15),
    _telefono VARCHAR(9),
    _celular VARCHAR(9),
    _direccion VARCHAR(255),
    _correo VARCHAR(100)
)
BEGIN
    -- Manejador de errores
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error: No se pudo editar el oficinista. Se revirtieron los cambios.' AS Resultado;
    END;

    START TRANSACTION;

    -- 1. Actualizar la tabla principal de oficinista
    UPDATE oficinista SET
        idOficinista = _idOficinista,
        nombre = _nombre,
        apellido1 = _apellido1,
        apellido2 = _apellido2,
        telefono = _telefono,
        celular = _celular,
        direccion = _direccion,
        correo = _correo
    WHERE id = _id;
    
    -- 2. Actualizar también la tabla de usuario para mantener la consistencia del correo
    UPDATE usuario SET
        correo = _correo
    WHERE idUsuario = _idOficinista;

    COMMIT;
    SELECT 'Oficinista actualizado con éxito.' AS Resultado;
END$$

-- =================================================================
-- PROCEDIMIENTO PARA FILTRAR OFICINISTAS (SIN CAMBIOS)
-- Este procedimiento ya estaba bien implementado.
-- =================================================================
DROP PROCEDURE IF EXISTS filtrarOficinistas$$
CREATE PROCEDURE filtrarOficinistas(IN p_filtro VARCHAR(255), IN p_pagina INT, IN p_limite INT)
BEGIN
    SET @offset = (p_pagina - 1) * p_limite;
    SET @filtro_like = CONCAT('%', REPLACE(p_filtro, '%&%', '%'), '%');
    
    -- Usar la vista es más limpio
    SET @sql = 'SELECT * FROM vista_oficinistas WHERE CONCAT_WS(" ", idOficinista, nombre, apellido1, correo) LIKE ? LIMIT ?, ?';
    
    PREPARE stmt FROM @sql;
    EXECUTE stmt USING @filtro_like, @offset, p_limite;
    DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;