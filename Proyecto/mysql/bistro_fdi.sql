-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-03-2026 a las 13:00:18
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bistro_fdi`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT 'cat_default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `imagen`) VALUES
(1, 'Entrantes', 'Platos perfectos para abrir el apetito', 'cat_1_1771869641.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `numero_pedido` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `estado` enum('Recibido','En preparacion','Cocinando','Listo cocina','Terminado','Entregado','Cancelado') NOT NULL DEFAULT 'Recibido',
  `tipo` enum('Local','Llevar') NOT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `id_usuario`, `numero_pedido`, `fecha`, `estado`, `tipo`, `total`) VALUES
(1, 1, 1, '2026-02-25 10:42:27', 'Entregado', 'Local', 15.00),
(2, 1, 2, '2026-02-25 10:43:56', 'Entregado', 'Local', 7.50),
(3, 1, 3, '2026-02-25 10:44:29', 'Entregado', 'Llevar', 15.00),
(4, 1, 4, '2026-02-25 11:11:32', 'Entregado', 'Local', 7.50),
(5, 2, 5, '2026-02-25 11:17:07', 'Cancelado', 'Llevar', 7.50),
(6, 1, 6, '2026-02-25 12:05:53', 'Entregado', 'Local', 8.60),
(7, 1, 1, '2026-03-09 11:09:48', 'Cancelado', 'Llevar', 7.50),
(8, 1, 2, '2026-03-09 11:10:21', 'Entregado', 'Local', 7.50),
(9, 1, 3, '2026-03-09 11:14:04', 'Entregado', 'Llevar', 7.50),
(10, 1, 4, '2026-03-09 11:15:31', 'Cancelado', 'Local', 7.50),
(11, 1, 5, '2026-03-09 12:35:15', 'Cancelado', 'Llevar', 15.50),
(12, 1, 6, '2026-03-09 12:55:55', 'Cancelado', 'Llevar', 15.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos_productos`
--

CREATE TABLE `pedidos_productos` (
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `iva` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos_productos`
--

INSERT INTO `pedidos_productos` (`id_pedido`, `id_producto`, `cantidad`, `precio_unitario`, `iva`) VALUES
(1, 2, 2, 6.20, 21),
(2, 2, 1, 6.20, 21),
(3, 2, 2, 6.20, 21),
(4, 2, 1, 6.20, 21),
(5, 2, 1, 6.20, 21),
(6, 2, 1, 6.20, 21),
(7, 2, 1, 6.20, 21),
(8, 2, 1, 6.20, 21),
(9, 2, 1, 6.20, 21),
(10, 2, 1, 6.20, 21),
(11, 2, 1, 6.20, 21),
(11, 5, 1, 6.61, 21),
(12, 2, 1, 6.20, 21),
(12, 5, 1, 6.61, 21);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_base` decimal(10,2) NOT NULL,
  `iva` int(11) NOT NULL DEFAULT 10,
  `disponible` tinyint(1) NOT NULL DEFAULT 1,
  `ofertado` tinyint(1) NOT NULL DEFAULT 1,
  `imagen` varchar(255) DEFAULT 'prod_default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `id_categoria`, `nombre`, `descripcion`, `precio_base`, `iva`, `disponible`, `ofertado`, `imagen`) VALUES
(2, 1, 'Nachos Especiales', 'Nuestros nachos especiales con la receta secreta que nos caracteriza', 6.20, 21, 1, 1, 'prod_2_1772643347.jpg'),
(5, 1, 'Tequeños', 'Nuestros mundialmente famosos tequeños rellenos del mejor queso para el disfrute de todos.', 6.61, 21, 1, 1, 'prod_1773052066.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'cliente'),
(2, 'camarero'),
(3, 'cocinero'),
(4, 'gerente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombreUsuario` varchar(30) NOT NULL,
  `password` varchar(70) NOT NULL,
  `avatar` varchar(255) DEFAULT 'default.png',
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `rol` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombreUsuario`, `password`, `avatar`, `nombre`, `apellidos`, `email`, `rol`) VALUES
(1, 'gerente', '$2y$10$O3c1kBFa2yDK5F47IUqusOJmIANjHP6EiPyke5dD18ldJEow.e0eS', 'gerente_1771866688.png', 'Jefe', 'Supremo', 'gerente@bistrofdi.es', 4),
(2, 'Ethan', '$2y$10$0HmvdO5xJUR6ZzKa6py/K.2qu6iS.laiNnGX4gkdLnEIeSb47urCW', 'ethan.jpg', 'Ethan', 'Carrillo', 'ethancar@ucm.es', 3),
(4, 'Alvar', '$2y$10$YjAOgLy5rqPhcH8av64vP.baYIWP81jdwokhKPoCD637LuTTMASzO', 'alvar.jpg', 'Alvar', 'Rodriguez', 'alvarr17@ucm.es', 1),
(5, 'Yago', '$2y$10$JaoNVJ3j5pw.jWVnT27hkuvoBWC7Oh1HAtyWmEvqokNvxL5V0WsQq', 'yago.jpg', 'Yago', 'Vaquero', 'yvaquero@ucm.es', 4),
(6, 'Zhirun', '$2y$10$kFGhGks0ATGbskwLy/vzv.RJtohld4C3K7kOGeEafwQHTqJUuJrpa', 'zhirun.jpg', 'Zhirun', 'Huang', 'zhihuang@ucm.es', 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `pedidos_productos`
--
ALTER TABLE `pedidos_productos`
  ADD PRIMARY KEY (`id_pedido`,`id_producto`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombreUsuario` (`nombreUsuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos_productos`
--
ALTER TABLE `pedidos_productos`
  ADD CONSTRAINT `pedidos_productos_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedidos_productos_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
