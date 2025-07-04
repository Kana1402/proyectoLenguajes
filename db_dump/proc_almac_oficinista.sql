USE taller;
DELIMITER $$

-- =================================================================
-- VISTA PARA LEER DATOS DE OFICINISTAS
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
    u.correo,
    u.rol
FROM oficinista o
JOIN usuario u ON o.idOficinista = u.idUsuario;
$$

-- =================================================================
-- PROCEDIMIENTO PARA CREAR UN NUEVO OFICINISTA
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
    _passw VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error: No se pudo crear el oficinista. Se revirtieron los cambios.' AS Resultado;
    END;

    START TRANSACTION;

    INSERT INTO usuario(idUsuario, correo, rol, passw) 
    VALUES (_idOficinista, _correo, 2, _passw);

    INSERT INTO oficinista(idOficinista, nombre, apellido1, apellido2, telefono, celular, direccion, correo) 
    VALUES (_idOficinista, _nombre, _apellido1, _apellido2, _telefono, _celular, _direccion, _correo);

    COMMIT;
    SELECT 'Oficinista creado con éxito.' AS Resultado;
END$$

-- =================================================================
-- PROCEDIMIENTO PARA ELIMINAR UN OFICINISTA
-- =================================================================
DROP PROCEDURE IF EXISTS eliminarOficinista$$
CREATE PROCEDURE eliminarOficinista (_id_oficinista INT)
BEGIN
    DECLARE _idUsuarioABorrar VARCHAR(15);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error: No se pudo eliminar el oficinista. Se revirtieron los cambios.' AS Resultado;
    END;

    SELECT idOficinista INTO _idUsuarioABorrar FROM oficinista WHERE id = _id_oficinista;

    IF _idUsuarioABorrar IS NOT NULL THEN
        START TRANSACTION;

        DELETE FROM oficinista WHERE id = _id_oficinista;
        DELETE FROM usuario WHERE idUsuario = _idUsuarioABorrar;

        COMMIT;
        SELECT 'Oficinista eliminado con éxito.' AS Resultado;
    ELSE
        SELECT 'Error: No se encontró un oficinista con el ID proporcionado.' AS Resultado;
    END IF;
END$$

-- =================================================================
-- PROCEDIMIENTO PARA EDITAR UN OFICINISTA
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
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error: No se pudo editar el oficinista. Se revirtieron los cambios.' AS Resultado;
    END;

    START TRANSACTION;

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

    UPDATE usuario SET
        correo = _correo
    WHERE idUsuario = _idOficinista;

    COMMIT;
    SELECT 'Oficinista actualizado con éxito.' AS Resultado;
END$$

-- =================================================================
-- PROCEDIMIENTO PARA FILTRAR OFICINISTAS (CORREGIDO)
-- =================================================================
DROP PROCEDURE IF EXISTS filtrarOficinistas$$
CREATE PROCEDURE filtrarOficinistas(
    IN p_filtro VARCHAR(255), 
    IN p_pagina INT, 
    IN p_limite INT
)
BEGIN
    DECLARE v_offset INT;
    DECLARE v_filtro_like VARCHAR(255);

    SET v_offset = (p_pagina - 1) * p_limite;
    SET v_filtro_like = CONCAT('%', REPLACE(p_filtro, '%&%', '%'), '%');

    SET @sql = '
        SELECT * 
        FROM vista_oficinistas 
        WHERE CONCAT_WS(" ", idOficinista, nombre, apellido1, correo) LIKE ? 
        LIMIT ?, ?
    ';

    SET @v1 = v_filtro_like;
    SET @v2 = v_offset;
    SET @v3 = p_limite;

    PREPARE stmt FROM @sql;
    EXECUTE stmt USING @v1, @v2, @v3;
    DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;
