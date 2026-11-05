CREATE DATABASE IF NOT EXISTS chatonline
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE chatonline;


CREATE TABLE IF NOT EXISTS usuarios (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username         VARCHAR(30)  NOT NULL,
    nombre_real      VARCHAR(100) NOT NULL,
    email            VARCHAR(150) NOT NULL,
    password_hash    VARCHAR(255) NOT NULL,
    rol              ENUM('usuario','admin') NOT NULL DEFAULT 'usuario',
    avatar           VARCHAR(255) NULL DEFAULT NULL,
    fecha_registro   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultima_conexion  DATETIME     NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_username (username),
    UNIQUE KEY uq_usuarios_email (email),
    KEY idx_usuarios_nombre_real (nombre_real)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS amistades (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    solicitante_id   INT UNSIGNED NOT NULL,
    destinatario_id  INT UNSIGNED NOT NULL,
    estado           ENUM('pendiente','aceptada','rechazada') NOT NULL DEFAULT 'pendiente',
    fecha_solicitud  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_respuesta  DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_amistades_par (solicitante_id, destinatario_id),
    KEY idx_amistades_destinatario_estado (destinatario_id, estado),
    KEY idx_amistades_solicitante_estado (solicitante_id, estado),
    CONSTRAINT fk_amistades_solicitante
        FOREIGN KEY (solicitante_id) REFERENCES usuarios (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_amistades_destinatario
        FOREIGN KEY (destinatario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS mensajes (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    emisor_id    INT UNSIGNED NOT NULL,
    receptor_id  INT UNSIGNED NOT NULL,
    tipo         ENUM('texto','imagen','archivo','audio','video') NOT NULL DEFAULT 'texto',
    contenido    VARCHAR(250) NULL DEFAULT NULL,
    fecha_envio  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    leido        TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_mensajes_conversacion (emisor_id, receptor_id, fecha_envio),
    KEY idx_mensajes_receptor_leido (receptor_id, leido),
    CONSTRAINT fk_mensajes_emisor
        FOREIGN KEY (emisor_id) REFERENCES usuarios (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_mensajes_receptor
        FOREIGN KEY (receptor_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS adjuntos (
    id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    mensaje_id         BIGINT UNSIGNED NOT NULL,
    nombre_original    VARCHAR(255) NOT NULL,
    nombre_almacenado  VARCHAR(255) NOT NULL,
    mime_type          VARCHAR(100) NOT NULL,
    tamano_bytes       INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    KEY idx_adjuntos_mensaje (mensaje_id),
    CONSTRAINT fk_adjuntos_mensaje
        FOREIGN KEY (mensaje_id) REFERENCES mensajes (id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE OR REPLACE VIEW vista_amigos AS
    SELECT solicitante_id AS usuario_id, destinatario_id AS amigo_id
    FROM amistades
    WHERE estado = 'aceptada'
    UNION ALL
    SELECT destinatario_id AS usuario_id, solicitante_id AS amigo_id
    FROM amistades
    WHERE estado = 'aceptada';

INSERT INTO usuarios (username, nombre_real, email, password_hash, rol, avatar) VALUES ('admin', 'Administrador', 'admin@gmail.com', '$2y$10$YdyMG1s8bxKgLRHwtaFrRuazrfk0HjWXGnBI8sPKVd/bg06pl7f6O', 'admin', NULL);