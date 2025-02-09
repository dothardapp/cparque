-- Tabla de Clientes
CREATE TABLE clientes (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo BIGINT(20) UNSIGNED NOT NULL UNIQUE,  -- Código heredado de la base de datos antigua
    nombre VARCHAR(255) NOT NULL,
    documento VARCHAR(20) UNIQUE,
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT
);

-- Tabla de Expensas
CREATE TABLE expensas (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_cliente BIGINT(20) UNSIGNED NOT NULL,  -- Relación con clientes usando el código antiguo
    anio INT NOT NULL,
    mes INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    saldo DECIMAL(10,2) NOT NULL DEFAULT 0, -- Saldo restante de la expensa
    estado ENUM('pendiente', 'pagado parcialmente', 'pagado') NOT NULL DEFAULT 'pendiente',
    user_id BIGINT(20) UNSIGNED NOT NULL, -- Usuario que registró la expensa
    CONSTRAINT unique_expensa UNIQUE (codigo_cliente, anio, mes),
    FOREIGN KEY (codigo_cliente) REFERENCES clientes(codigo) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla de Pagos
CREATE TABLE pagos (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_cliente BIGINT(20) UNSIGNED NOT NULL,  -- Relación con clientes usando el código
    expensa_id BIGINT(20) UNSIGNED NOT NULL,  -- Relación con la expensa correspondiente
    fecha_pago DATETIME DEFAULT CURRENT_TIMESTAMP,
    monto_pagado DECIMAL(10,2) NOT NULL,
    user_id BIGINT(20) UNSIGNED NOT NULL, -- Usuario que registró el pago
    FOREIGN KEY (codigo_cliente) REFERENCES clientes(codigo) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (expensa_id) REFERENCES expensas(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla de Servicios Adicionales
CREATE TABLE servicios_adicionales (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(255) NOT NULL,
    precio DECIMAL(10,2) NOT NULL
);

-- Registro de Ventas de Servicios Adicionales
CREATE TABLE ventas_servicios (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_cliente BIGINT(20) UNSIGNED NOT NULL,
    servicio_id BIGINT(20) UNSIGNED NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    monto DECIMAL(10,2) NOT NULL,
    user_id BIGINT(20) UNSIGNED NOT NULL, -- Usuario que registró la venta
    FOREIGN KEY (codigo_cliente) REFERENCES clientes(codigo) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios_adicionales(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
);
