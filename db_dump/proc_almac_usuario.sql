USE taller;

DELIMITER $$

-- PROCEDIMIENTO buscarUsuario
DROP PROCEDURE IF EXISTS buscarUsuario$$
CREATE PROCEDURE buscarUsuario (_id INT(11), _idUsuario VARCHAR(15))
BEGIN
    SELECT * FROM usuario WHERE idUsuario = _idUsuario OR id = _id;
END$$

-- FUNCIÓN nuevoUsuario
DROP FUNCTION IF EXISTS nuevoUsuario$$
CREATE FUNCTION nuevoUsuario (
    _idUsuario VARCHAR(15),
    _correo VARCHAR(100),
    _rol INT,
    _passw VARCHAR(255)
)
RETURNS INT(1)
DETERMINISTIC
MODIFIES SQL DATA
BEGIN
    DECLARE _cant INT;
    SELECT COUNT(id) INTO _cant FROM usuario WHERE idUsuario = _idUsuario;
    IF _cant < 1 THEN
        INSERT INTO usuario(idUsuario, correo, rol, passw)
        VALUES (_idUsuario, _correo, _rol, _passw);
    END IF;
    RETURN _cant;
END$$

-- FUNCIÓN eliminarUsuario
DROP FUNCTION IF EXISTS eliminarUsuario$$
CREATE FUNCTION eliminarUsuario (_id INT)
RETURNS INT(1)
DETERMINISTIC
MODIFIES SQL DATA
BEGIN
    DECLARE _cant INT;
    SELECT COUNT(id) INTO _cant FROM usuario WHERE id = _id;
    IF _cant > 0 THEN
        DELETE FROM usuario WHERE id = _id;
    END IF;
    RETURN _cant;
END$$

-- PROCEDIMIENTO rolUsuario
DROP PROCEDURE IF EXISTS rolUsuario$$
CREATE PROCEDURE rolUsuario (
    _idUsuario VARCHAR(15), 
    _rol INT
)
BEGIN
    UPDATE usuario SET rol = _rol WHERE idUsuario = _idUsuario;
END$$

-- PROCEDIMIENTO passwUsuario
DROP PROCEDURE IF EXISTS passwUsuario$$
CREATE PROCEDURE passwUsuario (
    _idUsuario VARCHAR(15), 
    _passw VARCHAR(255)
)
BEGIN
    UPDATE usuario SET passw = _passw WHERE idUsuario = _idUsuario;
END$$

DELIMITER ;
