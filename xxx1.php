CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre_producto VARCHAR(255) NOT NULL,
    descripcion_producto TEXT,
    categoria_id INT,
    stock_actual INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL,
    stock_maximo INT NOT NULL,
    precio_producto DECIMAL(10,2) NOT NULL,
    unidadmedida VARCHAR(50) NOT NULL,
    almacen_id INT
);

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE vehiculos (
    id_vehiculo INT AUTO_INCREMENT PRIMARY KEY,
    modelo VARCHAR(255),
    placa_serial VARCHAR(255) UNIQUE NOT NULL,
    descripcion TEXT
);

CREATE TABLE maquinarias (
    id_maquinaria INT AUTO_INCREMENT PRIMARY KEY,
    modelo VARCHAR(255),
    serial VARCHAR(255) UNIQUE NOT NULL,
    descripcion TEXT
);

create table proveedor_categorias(
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_categoria INT NOT NULL
)

CREATE TABLE almacenes (
    id_almacen INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    ubicacion TEXT NOT NULL
);

CREATE TABLE mantenimientos_vehiculos (
    id_mv INT AUTO_INCREMENT PRIMARY KEY,
    id_vehiculo INT NOT NULL,
    fecha DATE NOT NULL,
    descripcion TEXT,
    id_responsable INT
);

CREATE TABLE mantenimientos_maquinaria (
    id_mm INT AUTO_INCREMENT PRIMARY KEY,
    id_maquinaria INT NOT NULL,
    fecha DATE NOT NULL,
    descripcion TEXT,
    id_responsable INT
);

CREATE TABLE mantenimiento_renglon (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_mantenimiento INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL
);

CREATE TABLE rutas_entrega (
    id INT AUTO_INCREMENT PRIMARY KEY,
    origen VARCHAR(255) NOT NULL,
    destino VARCHAR(255) NOT NULL,
    responsable_unidad INT,
    fecha_salida DATETIME NOT NULL,
    fecha_llegada DATETIME
);

CREATE TABLE evidencia_entregas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ruta_id INT NOT NULL,
    foto_url VARCHAR(255) NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

create table rolesusuario(
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_roles INT NOT NULL,
    status_ru VARCHAR(255)  NOT NULL
)


CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    area VARCHAR(255) NOT NULL,
    cargo VARCHAR(255) NOT NULL
);


create table usuario(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    appaterno VARCHAR(255) NOT NULL,
    apmaterno VARCHAR(255) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    telefono VARCHAR(255) NOT NULL,
    telefono2 VARCHAR(255) NOT NULL,
    sexo VARCHAR(255) NOT NULL,
    correopersonal VARCHAR(255) NOT NULL,
    correotrabajo VARCHAR(255) NOT NULL,
    curp VARCHAR(255) NOT NULL,
    DNI VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status VARCHAR(255) NOT NULL,
    fregistro DATETIME NOT NULL,
    observacion LONGTEXT NOT NULL,
    fechaNac DATE NOT NULL,
    fotoPerfil VARCHAR(255) NOT NULL,
    id_pais INT NOT NULL,
    id_estado INT NOT NULL, 
)

CREATE TABLE paises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL
);

CREATE TABLE estados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    id_pais INT NOT NULL
);

create table opciones(
    id_opc INT AUTO_INCREMENT PRIMARY KEY,
    tipo varchar(50) NOT NULL
)

create table opciones_valores(
    id_opc_val INT AUTO_INCREMENT PRIMARY KEY,
    id_opc INT NOT NULL,
    valor varchar(50) NOT NULL
)