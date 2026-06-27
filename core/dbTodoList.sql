CREATE DATABASE IF NOT EXISTS todoList;
USE todoList;

DROP TABLE IF EXISTS tarea;
DROP TABLE IF EXISTS carpeta;
DROP TABLE IF EXISTS usuario;

CREATE TABLE usuario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    correo VARCHAR(255) NOT NULL UNIQUE,
    contra VARCHAR(255) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    token VARCHAR(255) NULL,
    admin BOOLEAN DEFAULT FALSE
);

CREATE TABLE carpeta (
    idCarpeta INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    idUsuario INT NOT NULL,
    FOREIGN KEY (idUsuario) REFERENCES usuario(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE tarea (
    idTarea INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255) NULL,
    contenido VARCHAR(511) NULL,
    estado ENUM('pendiente', 'en_curso', 'finalizada', 'cancelada') NOT NULL DEFAULT 'pendiente',
    tipo ENUM('privada', 'general', 'asignada') NOT NULL DEFAULT 'privada',
    fechaIni DATE,
    fechaFin DATE NULL,
    idCarpeta INT NULL,
    idUsuario INT NOT NULL,

    FOREIGN KEY (idCarpeta) REFERENCES carpeta(idCarpeta)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    FOREIGN KEY (idUsuario) REFERENCES usuario(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

INSERT INTO usuario (correo, contra, nombre, admin) 
VALUES (
    'admin@admin.com',
    '$2y$10$yY0MiVzAbdlwzsjiSSw1yul9/fd2fDA09FbnVwKPkaeGGIthCV8Ce',
    'Ananda',
    TRUE
);