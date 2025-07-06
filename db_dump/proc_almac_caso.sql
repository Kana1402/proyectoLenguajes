USE taller;
DELIMITER $$

-- =================================================================
-- VISTA PARA CASOS (MODIFICADA)
-- Ahora obtiene el estado más reciente desde la tabla 'historialCaso'.
-- =================================================================
CREATE OR REPLACE VIEW vista_casos AS
SELECT 
    c.id,
    c.descripcion,
    c.fechaEntrada,
    c.fechaSalida,
    cli.nombre AS nombre_cliente,
    cli.apellido1 AS apellido1_cliente,
    art.marca AS marca_artefacto,
    art.modelo AS modelo_artefacto,
    tec.nombre AS nombre_tecnico,
    tec.apellido1 AS apellido1_tecnico,
    (SELECT estado FROM historialCaso hc WHERE hc.idCaso = c.id ORDER BY hc.fechaCambio DESC LIMIT 1) AS estado_actual
FROM caso c
JOIN artefacto art ON c.idArtefacto = art.id
JOIN cliente cli ON art.idCliente = cli.id
LEFT JOIN tecnico tec ON c.idTecnico = tec.idTecnico;
$$

-- =================================================================
-- PROCEDIMIENTO PARA CREAR UN NUEVO CASO (ADAPTADO)
-- =================================================================
DROP PROCEDURE IF EXISTS nuevoCaso$$
CREATE PROCEDURE nuevoCaso (
    _idTecnico VARCHAR(15),
    _idCreador VARCHAR(15),
    _idArtefacto INT,
    _descripcion VARCHAR(100)
)
BEGIN
    DECLARE last_case_id INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error: No se pudo crear el caso.' AS Resultado;
    END;

    START TRANSACTION;

    INSERT INTO caso(idTecnico, idCreador, idArtefacto, descripcion, fechaEntrada) 
    VALUES (_idTecnico, _idCreador, _idArtefacto, _descripcion, CURDATE());

    SET last_case_id = LAST_INSERT_ID();

    -- Insertar el primer estado en el historial
    INSERT INTO historialCaso(idCaso, idResponsable, estado, fechaCambio, descripcion)
    VALUES (last_case_id, _idCreador, 0, CURDATE(), 'Caso ingresado al sistema.');

    COMMIT;
    SELECT 'Caso creado con éxito.' AS Resultado;
END$$

-- =================================================================
-- PROCEDIMIENTO PARA EDITAR UN CASO (ADAPTADO)
-- =================================================================
DROP PROCEDURE IF EXISTS editarCaso$$
CREATE PROCEDURE editarCaso (
    _id INT,
    _idTecnico VARCHAR(15),
    _descripcion VARCHAR(100)
)
BEGIN
    UPDATE caso SET
        idTecnico = _idTecnico,
        descripcion = _descripcion
    WHERE id = _id;
    SELECT 'Caso actualizado con éxito.' AS Resultado;
END$$

-- =================================================================
-- PROCEDIMIENTO PARA ELIMINAR UN CASO (ADAPTADO)
-- =================================================================
DROP PROCEDURE IF EXISTS eliminarCaso$$
CREATE PROCEDURE eliminarCaso (_id INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error: No se pudo eliminar el caso.' AS Resultado;
    END;

    START TRANSACTION;
    -- Eliminar historial
    DELETE FROM historialCaso WHERE idCaso = _id;
    -- Eliminar caso
    DELETE FROM caso WHERE id = _id;
    COMMIT;

    SELECT 'Caso y su historial eliminados con éxito.' AS Resultado;
END$$

-- =================================================================
-- PROCEDIMIENTO PARA CAMBIAR ESTADO DE CASO (ADAPTADO)
-- =================================================================
DROP PROCEDURE IF EXISTS cambiarEstadoCaso$$
CREATE PROCEDURE cambiarEstadoCaso (_idCaso INT, _idResponsable VARCHAR(15), _nuevo_estado INT, _descripcion_cambio VARCHAR(255))
BEGIN
    INSERT INTO historialCaso(idCaso, idResponsable, estado, fechaCambio, descripcion)
    VALUES (_idCaso, _idResponsable, _nuevo_estado, CURDATE(), _descripcion_cambio);

    -- Si estado es entregado (4), actualizar fechaSalida
    IF _nuevo_estado = 4 THEN
        UPDATE caso SET fechaSalida = CURDATE() WHERE id = _idCaso;
    END IF;

    SELECT 'Estado del caso actualizado en el historial.' AS Resultado;
END$$

-- =================================================================
-- PROCEDIMIENTO PARA FILTRAR CASOS (ADAPTADO)
-- =================================================================
DROP PROCEDURE IF EXISTS filtrarCasos$$
CREATE PROCEDURE filtrarCasos(IN p_filtro VARCHAR(255), IN p_pagina INT, IN p_limite INT)
BEGIN
    SET @offset = (p_pagina - 1) * p_limite;
    SET @filtro_like = CONCAT('%', REPLACE(p_filtro, '%&%', '%'), '%');
    SET @limite = p_limite;

    SET @sql = 'SELECT * FROM vista_casos WHERE CONCAT_WS(" ", id, nombre_cliente, marca_artefacto, estado_actual) LIKE ? ORDER BY id DESC LIMIT ?, ?';
    PREPARE stmt FROM @sql;
    EXECUTE stmt USING @filtro_like, @offset, @limite;
    DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;
