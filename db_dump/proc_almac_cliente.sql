USE taller;

DELIMITER $$

-- PROCEDIMIENTOS

DROP PROCEDURE IF EXISTS buscarCliente$$
CREATE PROCEDURE buscarCliente (_id INT, _idCliente VARCHAR(15))
BEGIN
    SELECT * FROM cliente WHERE id = _id OR idCliente = _idCliente;
END$$

DROP PROCEDURE IF EXISTS filtrarCliente$$
CREATE PROCEDURE filtrarCliente (
    _parametros VARCHAR(250), -- %idCliente%&%nombre%&%apellido1%&%apellido2%&
    _pagina SMALLINT UNSIGNED, 
    _cantRegs SMALLINT UNSIGNED)
BEGIN
    SELECT cadenaFiltro(_parametros, 'idCliente&nombre&apellido1&apellido2&') INTO @filtro;
    SELECT CONCAT("SELECT * FROM cliente WHERE ", @filtro, " LIMIT ", _pagina, ", ", _cantRegs) INTO @sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$

DROP PROCEDURE IF EXISTS numRegsCliente$$
CREATE PROCEDURE numRegsCliente (
    _parametros VARCHAR(250))
BEGIN
    SELECT cadenaFiltro(_parametros, 'idCliente&nombre&apellido1&apellido2&') INTO @filtro;
    SELECT CONCAT("SELECT COUNT(id) FROM cliente WHERE ", @filtro) INTO @sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$

-- FUNCIONES

DROP FUNCTION IF EXISTS nuevoCliente$$
CREATE FUNCTION nuevoCliente (
    _idCliente VARCHAR(15),
    _nombre VARCHAR(30),
    _apellido1 VARCHAR(15),
    _apellido2 VARCHAR(15),
    _telefono VARCHAR(9),
    _celular VARCHAR(9),
    _direccion VARCHAR(255),
    _correo VARCHAR(100))
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE _cant INT;
    SELECT COUNT(id) INTO _cant FROM cliente WHERE idCliente = _idCliente OR correo = _correo;
    IF _cant < 1 THEN
        INSERT INTO cliente(idCliente, nombre, apellido1, apellido2, telefono, celular, direccion, correo) 
        VALUES (_idCliente, _nombre, _apellido1, _apellido2, _telefono, _celular, _direccion, _correo);
    END IF;
    RETURN _cant;
END$$

DROP FUNCTION IF EXISTS editarCliente$$
CREATE FUNCTION editarCliente (
    _id INT, 
    _idCliente VARCHAR(15),
    _nombre VARCHAR(30),
    _apellido1 VARCHAR(15),
    _apellido2 VARCHAR(15),
    _telefono VARCHAR(9),
    _celular VARCHAR(9),
    _direccion VARCHAR(255),
    _correo VARCHAR(100)
) RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE _cant INT;
    DECLARE no_encontrado INT DEFAULT 0;
    IF NOT EXISTS(SELECT id FROM cliente WHERE id = _id) THEN
        SET no_encontrado = 1;
    ELSE
        UPDATE cliente SET
            idCliente = _idCliente,
            nombre = _nombre,
            apellido1 = _apellido1,
            apellido2 = _apellido2,
            telefono = _telefono,
            celular = _celular,
            direccion = _direccion,
            correo = _correo
        WHERE id = _id;
    END IF;
    RETURN no_encontrado;
END$$

DROP FUNCTION IF EXISTS eliminarCliente$$
CREATE FUNCTION eliminarCliente (_id INT) RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE _cant INT;
    DECLARE _resp INT DEFAULT 0;
    SELECT COUNT(id) INTO _cant FROM cliente WHERE id = _id;
    IF _cant > 0 THEN
        SET _resp = 1;
        SELECT COUNT(id) INTO _cant FROM artefacto WHERE idCliente = _id;
        IF _cant = 0 THEN
            DELETE FROM cliente WHERE id = _id;
        ELSE 
            SET _resp = 2;
        END IF;
    END IF;
    RETURN _resp;
END$$

-- TRIGGERS

DROP TRIGGER IF EXISTS actualizar_Cliente$$
CREATE TRIGGER actualizar_Cliente AFTER UPDATE ON cliente FOR EACH ROW
BEGIN
    UPDATE usuario
    SET idUsuario = NEW.idCliente, 
        correo = NEW.correo
    WHERE idUsuario = OLD.idCliente;
END$$

DROP TRIGGER IF EXISTS eliminar_Cliente$$
CREATE TRIGGER eliminar_Cliente AFTER DELETE ON cliente FOR EACH ROW
BEGIN
    DELETE FROM usuario
    WHERE idUsuario = OLD.idCliente;
END$$

DELIMITER ;
