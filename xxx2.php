cicloproduccion(id_cp, horainicio_cp, horafin_cp, cantidadproducida_cp, id_responsable, id_maquina)
// esta tabla es para saber que ciclos de produccion se han realizado, en que horas y que cantidad se produjo

cicloproduccion_operador(id, cantidadproducida_cpo, id_operador, id_cicloproduccion)
// esta tablas es para saber que usuarios operaran cada ciclo de produccion(en el se ve en que ciclo y en que maquina)


maquina(id_maquinaria, nombre_maquina, modelo, serial, tipodeproduccion, capacidad, unidadmedidacap, descripcion,
horas_operacion, frecuenciamant_dias, frecuenciamant_horasuso, fecha_ultimomantenimiento, horasuso_ultimomantenimiento)
// esta tabla es para saber que maquinas se tienen, que tipo de produccion hacen, su capacidad y su frecuencia de
mantenimiento

usuario(id, grado, nombre, appaterno, apmaterno, direccion, telefono, telefono2, sexo, correo_personal, correo_trabajo,
curp_dni, password, status_usuario, fregistro_usuario, observacion_usuario, fechaNac, fotoPerfil, rol, id_pais,
id_estado)
// esta tabla es para saber que usuarios se tienen, su nombre, apellidos, direccion, telefono, sexo, correo, curp y
password , el rol que tiene y el status_usuario en el que se encuentra