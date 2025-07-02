use taller;
DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `verificarTokenR` (IN `_idUsuario` VARCHAR(100), IN `_tkRef` VARCHAR(255))   
begin
    select rol from usuario where (idUsuario = _idUsuario OR correo = _idUsuario) and tkRef = _tkRef;
end$$
CREATE DEFINER=`root`@`localhost` FUNCTION `modificarToken` (`_idUsuario` VARCHAR(100), `_tkRef` VARCHAR(255)) 
    RETURNS INT DETERMINISTIC READS SQL DATA begin
    declare _cant int;
    select count(idUsuario) into _cant from usuario where idUsuario = _idUsuario OR correo = _idUsuario;
    if _cant > 0 then
        update usuario set
                tkRef = _tkRef
                where idUsuario = _idUsuario OR correo = _idUsuario;
        if _tkRef <> "" then
            update usuario set
                ultimoAcceso = now()
                where idUsuario = _idUsuario OR correo = _idUsuario;
        end if;
    end if;
    return _cant;
end$$
DELIMITER ;