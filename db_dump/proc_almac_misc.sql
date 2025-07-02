USE taller;
DELIMITER $$
DROP FUNCTION IF EXISTS cadenaFiltro$$
CREATE FUNCTION cadenaFiltro (
    _parametros varchar(250),
    _campos varchar(50)) RETURNS VARCHAR (250)
begin
    declare _salida varchar (250);
    set @param = _parametros;
    set @campos = _campos;
    set @filtro = "";
    WHILE (LOCATE('&', @param) > 0) DO
        set @valor = SUBSTRING_INDEX(@param, '&', 1);
        set @param = substr(@param, LOCATE('&', @param)+1);
        set @campo = SUBSTRING_INDEX(@campos, '&', 1);
        set @campos = substr(@campos, LOCATE('&', @campos)+1);
        set @filtro = concat(@filtro, " `", @campo, "` LIKE '", @valor, "' and");       
    END WHILE;
    set @filtro = TRIM(TRAILING 'and' FROM @filtro);  
    return @filtro;
end$$
DELIMITER ;