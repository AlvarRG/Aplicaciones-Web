SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Base de datos: `bistro_fdi`

-- Volcado de datos para la tabla `categorias`
INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `imagen`) VALUES
(1, 'Entrantes', 'Platos perfectos para abrir el apetito', 'cat_1_1771869641.jpg'),
(9, 'Bebidas', 'Refrescos, zumos naturales y cafés recién hechos para acompañar tu plato favorito.', 'cat_1773181544.jpg'),
(10, 'Postres', 'Tentaciones dulces y repostería artesanal para disfrutar después de tu plato principal.', 'cat_1773181630.jpg'),
(11, 'Platos principales', 'Platos contundentes y especialidades gourmet. La esencia de Bistro FDI en cada bocado.', 'cat_1773181815.jpg');

-- Volcado de datos para la tabla `roles`
INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'cliente'),
(2, 'camarero'),
(3, 'cocinero'),
(4, 'gerente');

-- Volcado de datos para la tabla `usuarios`
INSERT INTO `usuarios` (`id`, `nombreUsuario`, `password`, `avatar`, `nombre`, `apellidos`, `email`, `rol`) VALUES
(1, 'gerente', '$2y$10$O3c1kBFa2yDK5F47IUqusOJmIANjHP6EiPyke5dD18ldJEow.e0eS', 'gerente_1771866688.png', 'Jefe', 'Supremo', 'gerente@bistrofdi.es', 4),
(2, 'Ethan', '$2y$10$0HmvdO5xJUR6ZzKa6py/K.2qu6iS.laiNnGX4gkdLnEIeSb47urCW', 'ethan.jpg', 'Ethan', 'Carrillo', 'ethancar@ucm.es', 3),
(4, 'Alvar', '$2y$10$YjAOgLy5rqPhcH8av64vP.baYIWP81jdwokhKPoCD637LuTTMASzO', 'alvar.jpg', 'Alvar', 'Rodriguez', 'alvarr17@ucm.es', 1),
(5, 'Yago', '$2y$10$JaoNVJ3j5pw.jWVnT27hkuvoBWC7Oh1HAtyWmEvqokNvxL5V0WsQq', 'yago.jpg', 'Yago', 'Vaquero', 'yvaquero@ucm.es', 4),
(6, 'Zhirun', '$2y$10$kFGhGks0ATGbskwLy/vzv.RJtohld4C3K7kOGeEafwQHTqJUuJrpa', 'zhirun.jpg', 'Zhirun', 'Huang', 'zhihuang@ucm.es', 2);

-- Volcado de datos para la tabla `productos`
INSERT INTO `productos` (`id`, `id_categoria`, `nombre`, `descripcion`, `precio_base`, `iva`, `disponible`, `ofertado`, `imagen`) VALUES
(2, 1, 'Nachos Especiales', 'Nuestros nachos especiales con la receta secreta que nos caracteriza', 6.20, 21, 1, 1, 'prod_2_1772643347.jpg'),
(5, 1, 'Tequeños', 'Nuestros mundialmente famosos tequeños rellenos del mejor queso para el disfrute de todos.', 6.61, 21, 1, 1, 'prod_1773052066.jpg'),
(10, 1, 'Tabla de quesos y embutidos', 'Selección de quesos locales, jamón ibérico y tostas de pan artesanal con tomate', 18.40, 10, 1, 1, 'prod_1773182188.jpg'),
(11, 1, 'Croquetas de la casa', 'Nuestras famosas croquetas de jamón o setas, crujientes por fuera y cremosas por dentro.', 8.00, 10, 1, 1, 'prod_1773182319.jpg'),
(13, 9, 'Zumo de naranja', 'Zumo recién exprimido con naranjas seleccionadas de temporada. 100% natural y sin azúcares añadidos.', 3.00, 10, 1, 1, 'prod_1773182663.jpeg'),
(14, 9, 'Coca-Cola', 'El refresco clásico por excelencia, servido bien frío con hielo y una rodaja de limón.', 2.00, 10, 1, 1, 'prod_1773182698.jpg'),
(15, 9, 'Sprite', 'Refresco de lima-limón con burbujas intensas y un toque cítrico natural.', 2.00, 10, 1, 1, 'prod_1773182737.jpg'),
(16, 9, 'Cerveza', 'Cerveza de barril premium, equilibrada y refrescante, con el punto justo de amargor.', 2.00, 10, 1, 1, 'prod_1773182767.jpg'),
(17, 10, 'Coulant de Chocolate Belga', 'Bizcocho caliente con corazón de chocolate fundido, acompañado de una bola de helado de vainilla bourbon.', 6.50, 10, 1, 1, 'prod_1773182889.jpg'),
(18, 10, 'Tarta de Queso Bistro Fdi', 'Nuestra famosa tarta de queso fluida al horno, con base de galleta artesana y mermelada de frutos rojos.', 5.90, 10, 1, 1, 'prod_1773182938.jpg'),
(19, 10, 'Apple Crumble con Canela', 'Manzana asada con especias bajo una capa crujiente de galleta y mantequilla, servido tibio', 5.00, 10, 1, 1, 'prod_1773183046.gif'),
(20, 10, 'Mousse de Limón y Merengue', 'Crema ligera y refrescante de cítricos con trozos de merengue seco y ralladura de lima.', 4.20, 10, 1, 1, 'prod_1773183103.jpg'),
(21, 11, 'Hamburguesa "Bistro Deluxe"', '200g de carne de buey madurada, queso brie fundido, cebolla caramelizada al Pedro Ximénez y rúcula en pan brioche.', 13.90, 10, 1, 1, 'prod_1773183231.webp'),
(22, 11, 'Risotto de Setas Silvestres y Trufa', 'Arroz cremoso con variedad de setas de temporada, lascas de parmesano reggiano y esencia de trufa negra.', 15.50, 10, 1, 1, 'prod_1773183272.jpg'),
(23, 11, 'Salmón al Horno con Costra de Hierbas', 'Lomo de salmón fresco con costra de finas hierbas, servido sobre una cama de espárragos trigueros y puré de patata trufado.', 16.90, 10, 1, 1, 'prod_1773183329.jpg'),
(24, 11, 'Raviolis Artesanos de Calabaza', 'Pasta fresca rellena de calabaza asada y ricotta, con salsa de mantequilla de salvia y piñones tostados.', 12.50, 10, 1, 1, 'prod_1773183368.jpg');

-- Volcado de datos para la tabla `pedidos`
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
(12, 1, 6, '2026-03-09 12:55:55', 'Cancelado', 'Llevar', 15.50),
(13, 5, 1, '2026-03-10 23:16:17', 'En preparacion', 'Local', 22.51);

-- Volcado de datos para la tabla `pedidos_productos`
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
(12, 5, 1, 6.61, 21),
(13, 2, 3, 6.20, 21);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
