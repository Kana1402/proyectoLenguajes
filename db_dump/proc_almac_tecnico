USE taller;
DELIMITER $$

-- =================================================================
-- VISTA PARA LEER DATOS DE TÉCNICOS
-- =================================================================
CREATE OR REPLACE VIEW vista_tecnicos AS
SELECT 
    t.id,
    t.idTecnico,
    t.nombre,
    t.apellido1,
    t.apellido2,
    t.telefono,
    t.celular,
    t.direccion,
    t.especialidad,
    u.correo, -- Correo usado para el login
    u.rol
FROM tecnico t
JOIN usuario u ON t.idTecnico = u.idUsuario;
$$

-- =================================================================
-- PROCEDIMIENTO PARA CREAR UN NUEVO TÉCNICO (CORREGIDO)
-- =================================================================
DROP PROCEDURE IF EXISTS nuevoTecnico$$
CREATE PROCEDURE nuevoTecnico (
    _idTecnico VARCHAR(15),
    _nombre VARCHAR(30),
    _apellido1 VARCHAR(15),
    _apellido2 VARCHAR(15),
    _telefono VARCHAR(9),
    _celular VARCHAR(9),
    _direccion VARCHAR(255),
    _correo VARCHAR(100),
    _especialidad VARCHAR(100),
    _passw VARCHAR(255) -- Se añade el parámetro para la contraseña
)
BEGIN
    -- Manejador de errores para revertir la transacción si algo falla
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error: No se pudo crear el técnico. Se revirtieron los cambios.' AS Resultado;
    END;

    START TRANSACTION;
    
    -- 1. Crear el registro en 'usuario' para la autenticación
    -- Se asume que el ROL para técnico es '3'
    INSERT INTO usuario(idUsuario, correo, rol, passw) 
    VALUES (_idTecnico, _correo, 3, _passw);
    
    -- 2. Crear el registro en 'tecnico'
    INSERT INTO tecnico(idTecnico, nombre, apellido1, apellido2, telefono, celular, direccion, correo, especialidad) 
    VALUES (_idTecnico, _nombre, _apellido1, _apellido2, _telefono, _celular, _direccion, _correo, _especialidad);
    
    COMMIT;
    SELECT 'Técnico creado con éxito.' AS Resultado;
END$$

-- =================================================================
-- PROCEDIMIENTO PARA ELIMINAR UN TÉCNICO (CORREGIDO)
-- =================================================================
DROP PROCEDURE IF EXISTS eliminarTecnico$$
CREATE PROCEDURE eliminarTecnico (_id_tecnico INT)
BEGIN
    DECLARE _idUsuarioABorrar VARCHAR(15);

    -- Manejador de errores
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error: No se pudo eliminar el técnico. Se revirtieron los cambios.' AS Resultado;
    END;

    -- Obtener el idUsuario a partir del id de la tabla tecnico
    SELECT idTecnico INTO _idUsuarioABorrar FROM tecnico WHERE id = _id_tecnico;
    
    IF _idUsuarioABorrar IS NOT NULL THEN
        START TRANSACTION;
        
        -- 1. Eliminar de la tabla tecnico
        DELETE FROM tecnico WHERE id = _id_tecnico;
        
        -- 2. Eliminar de la tabla usuario
        DELETE FROM usuario WHERE idUsuario = _idUsuarioABorrar;
        
        COMMIT;
        SELECT 'Técnico eliminado con éxito.' AS Resultado;
    ELSE
        SELECT 'Error: No se encontró un técnico con el ID proporcionado.' AS Resultado;
    END IF;
END$$

-- =================================================================
-- PROCEDIMIENTO PARA EDITAR UN TÉCNICO (CORREGIDO)
-- =================================================================
DROP PROCEDURE IF EXISTS editarTecnico$$
CREATE PROCEDURE editarTecnico (
    _id INT,
    _idTecnico VARCHAR(15),
    _nombre VARCHAR(30),
    _apellido1 VARCHAR(15),
    _apellido2 VARCHAR(15),
    _telefono VARCHAR(9),
    _celular VARCHAR(9),
    _direccion VARCHAR(255),
    _correo VARCHAR(100),
    _especialidad VARCHAR(100)
)
BEGIN
    -- Manejador de errores
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error: No se pudo editar el técnico. Se revirtieron los cambios.' AS Resultado;
    END;

    START TRANSACTION;

    -- 1. Actualizar la tabla principal de tecnico
    UPDATE tecnico SET
        idTecnico = _idTecnico,
        nombre = _nombre,
        apellido1 = _apellido1,
        apellido2 = _apellido2,
        telefono = _telefono,
        celular = _celular,
        direccion = _direccion,
        correo = _correo,
        especialidad = _especialidad
    WHERE id = _id;
    
    -- 2. Actualizar también la tabla de usuario para mantener la consistencia del correo
    UPDATE usuario SET
        correo = _correo
    WHERE idUsuario = _idTecnico;

    COMMIT;
    SELECT 'Técnico actualizado con éxito.' AS Resultado;
END$$

-- =================================================================
-- PROCEDIMIENTO PARA FILTRAR TÉCNICOS (SIN CAMBIOS)
-- =================================================================
DROP PROCEDURE IF EXISTS filtrarTecnicos$$
CREATE PROCEDURE filtrarTecnicos(IN p_filtro VARCHAR(255), IN p_pagina INT, IN p_limite INT)
BEGIN
    SET @offset = (p_pagina - 1) * p_limite;
    SET @filtro_like = CONCAT('%', REPLACE(p_filtro, '%&%', '%'), '%');
    
    -- Usar la vista es más limpio
    SET @sql = 'SELECT * FROM vista_tecnicos WHERE CONCAT_WS(" ", idTecnico, nombre, apellido1, especialidad, correo) LIKE ? LIMIT ?, ?';
    
    PREPARE stmt FROM @sql;
    EXECUTE stmt USING @filtro_like, @offset, p_limite;
    DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;