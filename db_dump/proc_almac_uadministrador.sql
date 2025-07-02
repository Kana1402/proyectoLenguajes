USE taller;

DELIMITER $$


DROP PROCEDURE IF EXISTS buscarAdministrador$$
CREATE PROCEDURE buscarAdministrador (_id INT, _idAdministrador VARCHAR(15))
BEGIN
    SELECT * FROM administrador WHERE id = _id OR idAdministrador = _idAdministrador;
END$$


DROP PROCEDURE IF EXISTS filtrarAdministrador$$
CREATE PROCEDURE filtrarAdministrador (
    _parametros VARCHAR(250), -- %idAdministrador%&%nombre%&%apellido1%&%apellido2%&
    _pagina SMALLINT UNSIGNED, 
    _cantRegs SMALLINT UNSIGNED)
BEGIN
    SELECT cadenaFiltro(_parametros, 'idAdministrador&nombre&apellido1&apellido2&') INTO @filtro;
    SELECT CONCAT("SELECT * FROM administrador WHERE ", @filtro, " LIMIT ", 
        _pagina, ", ", _cantRegs) INTO @sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$


DROP PROCEDURE IF EXISTS numRegsAdministrador$$
CREATE PROCEDURE numRegsAdministrador (
    _parametros VARCHAR(250))
BEGIN
    SELECT cadenaFiltro(_parametros, 'idAdministrador&nombre&apellido1&apellido2&') INTO @filtro;
    SELECT CONCAT("SELECT COUNT(id) FROM administrador WHERE ", @filtro) INTO @sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$


DROP FUNCTION IF EXISTS nuevoAdministrador$$
CREATE FUNCTION nuevoAdministrador (
    _idAdministrador VARCHAR(15),
    _nombre VARCHAR(30),
    _apellido1 VARCHAR(15),
    _apellido2 VARCHAR(15),
    _telefono VARCHAR(9),
    _celular VARCHAR(9),
    _direccion VARCHAR(255),
    _correo VARCHAR(100))
RETURNS INT(1)
BEGIN
    DECLARE _cant INT;
    SELECT COUNT(id) INTO _cant FROM administrador 
    WHERE idAdministrador = _idAdministrador OR correo = _correo;
    IF _cant < 1 THEN
        INSERT INTO administrador(idAdministrador, nombre, apellido1, apellido2, telefono, 
                                  celular, direccion, correo)
        VALUES (_idAdministrador, _nombre, _apellido1, _apellido2, _telefono, 
                _celular, _direccion, _correo);
    END IF;
    RETURN _cant;
END$$


DROP FUNCTION IF EXISTS editarAdministrador$$
CREATE FUNCTION editarAdministrador (
    _id INT, 
    _idAdministrador VARCHAR(15),
    _nombre VARCHAR(30),
    _apellido1 VARCHAR(15),
    _apellido2 VARCHAR(15),
    _telefono VARCHAR(9),
    _celular VARCHAR(9),
    _direccion VARCHAR(255),
    _correo VARCHAR(100))
RETURNS INT(1)
BEGIN
    DECLARE _cant INT;
    SELECT COUNT(id) INTO _cant FROM administrador WHERE id = _id;
    IF _cant > 0 THEN
        UPDATE administrador SET
            idAdministrador = _idAdministrador,
            nombre = _nombre,
            apellido1 = _apellido1,
            apellido2 = _apellido2,
            telefono = _telefono,
            celular = _celular,
            direccion = _direccion,
            correo = _correo
        WHERE id = _id;
    END IF;
    RETURN _cant;
END$$


DROP FUNCTION IF EXISTS eliminarAdministrador$$
CREATE FUNCTION eliminarAdministrador (_id INT(1)) RETURNS INT(1)
BEGIN
    DECLARE _cant INT;
    DECLARE _resp INT;
    SET _resp = 0;
    
    SELECT COUNT(id) INTO _cant FROM administrador WHERE id = _id;
    IF _cant > 0 THEN
        SET _resp = 1;
        DELETE FROM administrador WHERE id = _id;
    END IF;
    
    RETURN _resp;
END$$

DELIMITER ;
