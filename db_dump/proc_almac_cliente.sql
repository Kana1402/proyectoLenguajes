USE taller;

DELIMITER $$
--
-- Funciones
--
DROP PROCEDURE IF EXISTS buscarCliente$$
CREATE PROCEDURE buscarCliente (_id int, _idCliente varchar(15))
begin
    select * from cliente where id = _id or idCliente = _idCliente;
end$$

DROP PROCEDURE IF EXISTS filtrarCliente$$
CREATE PROCEDURE filtrarCliente (
    _parametros varchar(250), -- %idCliente%&%nombre%&%apellido1%&%apellido2%&
    _pagina SMALLINT UNSIGNED, 
    _cantRegs SMALLINT UNSIGNED)
begin
    SELECT cadenaFiltro(_parametros, 'idCliente&nombre&apellido1&apellido2&') INTO @filtro;
    SELECT concat("SELECT * from cliente where ", @filtro, " LIMIT ", 
        _pagina, ", ", _cantRegs) INTO @sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
end$$

DROP PROCEDURE IF EXISTS numRegsCliente$$
CREATE PROCEDURE numRegsCliente (
    _parametros varchar(250))
begin
    SELECT cadenaFiltro(_parametros, 'idCliente&nombre&apellido1&apellido2&') INTO @filtro;
    SELECT concat("SELECT count(id) from cliente where ", @filtro) INTO @sql;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
end$$

DROP FUNCTION IF EXISTS nuevoCliente$$
CREATE FUNCTION nuevoCliente (
    _idCliente Varchar(15),
    _nombre Varchar (30),
    _apellido1 Varchar (15),
    _apellido2 Varchar (15),
    _telefono Varchar (9),
    _celular Varchar (9),
    _direccion Varchar (255),
    _correo Varchar (100))
    RETURNS INT(1) 
begin
    declare _cant int;
    select count(id) into _cant from cliente where idCliente = _idCliente OR correo = _correo;
    if _cant < 1 then
        insert into cliente(idCliente, nombre, apellido1, apellido2, telefono, 
            celular, direccion, correo) 
            values (_idCliente, _nombre, _apellido1, _apellido2, _telefono, 
            _celular, _direccion, _correo);
    end if;
    return _cant;
end$$

DROP FUNCTION IF EXISTS editarCliente$$
CREATE FUNCTION editarCliente (
    _id int, 
    _idCliente Varchar(15),
    _nombre Varchar (30),
    _apellido1 Varchar (15),
    _apellido2 Varchar (15),
    _telefono Varchar (9),
    _celular Varchar (9),
    _direccion Varchar (255),
    _correo Varchar (100)
    ) RETURNS INT(1) 
begin
    declare _cant int;
    declare no_encontrado int default 0;
    if not exists(select id from cliente where id = _id) then
        set no_encontrado = 1;
    else
        update cliente set
            idCliente = _idCliente,
            nombre = _nombre,
            apellido1 = _apellido1,
            apellido2 = _apellido2,
            telefono = _telefono,
            celular = _celular,
            direccion = _direccion,
            correo = _correo
        where id = _id;
    end if;
    return no_encontrado;
end$$

DROP FUNCTION IF EXISTS eliminarCliente$$
CREATE FUNCTION eliminarCliente (_id INT(1)) RETURNS INT(1)
begin
    declare _cant int;
    declare _resp int;
    set _resp = 0;
    select count(id) into _cant from cliente where id = _id;
    if _cant > 0 then
        set _resp = 1;
        select count(id) into _cant from artefacto where idCliente = _id;
        if _cant = 0 then
            delete from cliente where id = _id;
        else 
            -- select 2 into _resp;
            set _resp = 2;
        end if;
    end if;
    return _resp;
end$$


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
