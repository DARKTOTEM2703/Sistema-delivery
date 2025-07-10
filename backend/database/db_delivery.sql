-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-07-2025 a las 13:36:28
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `db_delivery`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `employee_settings`
--

CREATE TABLE `employee_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `restaurant_id` bigint(20) UNSIGNED NOT NULL,
  `hourly_rate` decimal(8,2) DEFAULT NULL,
  `commission_rate` decimal(5,2) DEFAULT NULL,
  `work_schedule` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`work_schedule`)),
  `can_receive_orders` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(255) NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_06_21_161553_create_restaurants_table', 1),
(5, '2025_06_21_191419_create_personal_access_tokens_table', 1),
(6, '2025_06_21_192305_create_orders_table', 1),
(7, '2025_06_28_161613_create_roles_and_permissions_tables', 1),
(8, '2025_06_28_192300_create_products_table', 1),
(9, '2025_06_28_194203_create_reviews_table', 1),
(10, '2025_06_28_220924_fix_migration_order', 1),
(11, '2025_06_30_191551_create_review_votes_table', 1),
(12, '2025_06_30_191557_create_user_favorites_table', 1),
(13, '2025_06_30_192306_create_order_items_table', 1),
(14, '2025_07_03_002324_add_entrepreneurship_fields_to_users_table', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `restaurant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `delivery_person_id` bigint(20) UNSIGNED DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tip_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `address` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `payment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `order_type` varchar(255) NOT NULL DEFAULT 'delivery',
  `special_instructions` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `prepared_at` timestamp NULL DEFAULT NULL,
  `picked_up_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `restaurant_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `rating` decimal(3,1) NOT NULL DEFAULT 5.0,
  `time` varchar(255) DEFAULT NULL,
  `servings` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `preparation_time` int(11) DEFAULT NULL,
  `allergens` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `restaurant_id`, `name`, `description`, `price`, `image`, `category`, `rating`, `time`, `servings`, `is_available`, `preparation_time`, `allergens`, `created_at`, `updated_at`) VALUES
(1, 1, 'Pizza Margherita Clásica', 'Salsa de tomate San Marzano, mozzarella di bufala, albahaca fresca', 18.99, 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 'pizzas', 4.5, '15-20 min', '1 persona', 1, 15, '', '2025-07-04 11:12:06', '2025-07-04 11:12:06'),
(2, 1, 'Espaguetis Carbonara', 'Pasta fresca con pancetta, huevos y queso pecorino romano', 16.99, 'https://images.unsplash.com/photo-1598866594230-a7c12756260f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 'pastas', 4.7, '12-17 min', '1 persona', 1, 12, '', '2025-07-04 11:12:06', '2025-07-04 11:12:06'),
(3, 2, 'Burger Clásica Angus', 'Carne de res Angus 200g, lechuga, tomate, cebolla', 15.99, 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 'hamburguesas', 4.4, '12-17 min', '1 persona', 1, 12, '', '2025-07-04 11:12:06', '2025-07-04 11:12:06'),
(4, 2, 'Buffalo Wings', 'Alitas de pollo bañadas en salsa buffalo', 18.99, 'https://images.unsplash.com/photo-1527477396000-e27163b481c2?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 'aperitivos', 4.4, '18-23 min', '1 persona', 1, 18, '', '2025-07-04 11:12:06', '2025-07-04 11:12:06'),
(5, 3, 'Sushi Roll California', 'Rollo con cangrejo, aguacate, pepino y masago', 22.99, 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 'sushi', 4.8, '15-20 min', '1 persona', 1, 15, '', '2025-07-04 11:12:06', '2025-07-04 11:12:06'),
(6, 3, 'Ramen Tonkotsu', 'Caldo cremoso de hueso de cerdo, chashu y huevo', 19.99, 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 'ramen', 4.5, '20-25 min', '1 persona', 1, 20, '', '2025-07-04 11:12:06', '2025-07-04 11:12:06'),
(7, 4, 'Tacos de Pastor', 'Carne de cerdo marinada con piña, cebolla y cilantro', 12.99, 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 'tacos', 4.8, '8-13 min', '1 persona', 1, 8, '', '2025-07-04 11:12:06', '2025-07-04 11:12:06'),
(8, 4, 'Guacamole con Totopos', 'Aguacate fresco con tomate, cebolla y cilantro', 8.99, 'https://images.unsplash.com/photo-1553909489-cd47e0ef937f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 'aperitivos', 4.2, '5-10 min', '1 persona', 1, 5, '', '2025-07-04 11:12:06', '2025-07-04 11:12:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restaurants`
--

CREATE TABLE `restaurants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `slug` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `delivery_fee` decimal(8,2) NOT NULL DEFAULT 0.00,
  `delivery_time_min` int(11) NOT NULL DEFAULT 30,
  `delivery_time_max` int(11) NOT NULL DEFAULT 60,
  `minimum_order` decimal(8,2) NOT NULL DEFAULT 0.00,
  `rating` decimal(3,1) NOT NULL DEFAULT 5.0,
  `total_reviews` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `accepts_cash` tinyint(1) NOT NULL DEFAULT 1,
  `accepts_card` tinyint(1) NOT NULL DEFAULT 1,
  `business_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`business_hours`)),
  `delivery_zones` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`delivery_zones`)),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `owner_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `restaurants`
--

INSERT INTO `restaurants` (`id`, `name`, `description`, `slug`, `logo`, `cover_image`, `address`, `phone`, `email`, `category`, `delivery_fee`, `delivery_time_min`, `delivery_time_max`, `minimum_order`, `rating`, `total_reviews`, `is_active`, `accepts_cash`, `accepts_card`, `business_hours`, `delivery_zones`, `latitude`, `longitude`, `owner_id`, `created_at`, `updated_at`) VALUES
(1, 'Pizza Italia Auténtica', 'Auténtica pizza italiana con ingredientes frescos', 'pizza-italia-autentica', NULL, NULL, 'Via Roma 45, Centro Histórico', '+1234567891', 'mario@pizzaitalia.com', 'italiana', 3.50, 25, 35, 15.00, 4.8, 247, 1, 1, 1, '{\"monday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"23:00\"},\"tuesday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"23:00\"},\"wednesday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"23:00\"},\"thursday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"23:00\"},\"friday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"00:00\"},\"saturday\":{\"is_open\":true,\"open\":\"12:00\",\"close\":\"00:00\"},\"sunday\":{\"is_open\":true,\"open\":\"12:00\",\"close\":\"22:00\"}}', '[{\"name\":\"Centro\",\"radius\":3},{\"name\":\"Norte\",\"radius\":5}]', 40.77774400, -73.99738300, 2, '2025-07-04 11:12:06', '2025-07-04 11:12:06'),
(2, 'Burger House Premium', 'Las mejores hamburguesas gourmet de la ciudad', 'burger-house-premium', NULL, NULL, 'Av. Principal 789, Zona Comercial', '+1234567892', 'carlos@burgerhouse.com', 'americana', 2.99, 20, 30, 12.00, 4.6, 189, 1, 1, 1, '{\"monday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"23:00\"},\"tuesday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"23:00\"},\"wednesday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"23:00\"},\"thursday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"23:00\"},\"friday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"00:00\"},\"saturday\":{\"is_open\":true,\"open\":\"12:00\",\"close\":\"00:00\"},\"sunday\":{\"is_open\":true,\"open\":\"12:00\",\"close\":\"22:00\"}}', '[{\"name\":\"Centro\",\"radius\":3},{\"name\":\"Norte\",\"radius\":5}]', 40.75942800, -73.98951800, 3, '2025-07-04 11:12:06', '2025-07-04 11:12:06'),
(3, 'Sushi Zen', 'Sushi fresco preparado por maestros japoneses', 'sushi-zen', NULL, NULL, 'Calle Sakura 321, Distrito Japonés', '+1234567893', 'ana@sushizen.com', 'japonesa', 4.99, 30, 45, 20.00, 4.9, 156, 1, 1, 1, '{\"monday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"23:00\"},\"tuesday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"23:00\"},\"wednesday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"23:00\"},\"thursday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"23:00\"},\"friday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"00:00\"},\"saturday\":{\"is_open\":true,\"open\":\"12:00\",\"close\":\"00:00\"},\"sunday\":{\"is_open\":true,\"open\":\"12:00\",\"close\":\"22:00\"}}', '[{\"name\":\"Centro\",\"radius\":3},{\"name\":\"Norte\",\"radius\":5}]', 40.76260000, -73.97192100, 4, '2025-07-04 11:12:06', '2025-07-04 11:12:06'),
(4, 'Tacos Chingones', 'Auténticos tacos mexicanos con recetas familiares', 'tacos-chingones', NULL, NULL, 'Av. Revolución 456, Barrio Mexicano', '+1234567894', 'jose@tacoschingones.com', 'mexicana', 2.50, 15, 25, 8.00, 4.7, 203, 1, 1, 1, '{\"monday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"23:00\"},\"tuesday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"23:00\"},\"wednesday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"23:00\"},\"thursday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"23:00\"},\"friday\":{\"is_open\":true,\"open\":\"11:00\",\"close\":\"00:00\"},\"saturday\":{\"is_open\":true,\"open\":\"12:00\",\"close\":\"00:00\"},\"sunday\":{\"is_open\":true,\"open\":\"12:00\",\"close\":\"22:00\"}}', '[{\"name\":\"Centro\",\"radius\":3},{\"name\":\"Norte\",\"radius\":5}]', 40.76280800, -73.99569100, 5, '2025-07-04 11:12:06', '2025-07-04 11:12:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `restaurant_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `food_rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `service_rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `delivery_rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `response` text DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `review_votes`
--

CREATE TABLE `review_votes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `is_helpful` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`permissions`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `permissions`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'Super Administrador', 'Administrador de la plataforma', '[\"*\"]', '2025-07-04 11:12:05', '2025-07-04 11:12:05'),
(2, 'owner', 'Propietario', 'Propietario y administrador del restaurante', '[\"restaurant.manage\",\"menu.manage\",\"orders.view\",\"employees.manage\",\"reports.view\",\"settings.manage\"]', '2025-07-04 11:12:05', '2025-07-04 11:12:05'),
(3, 'manager', 'Gerente', 'Administra operaciones diarias', '[\"orders.manage\",\"inventory.manage\",\"employees.view\",\"reports.view\",\"menu.edit\"]', '2025-07-04 11:12:05', '2025-07-04 11:12:05'),
(4, 'cook', 'Cocinero', 'Prepara pedidos en cocina', '[\"orders.kitchen\",\"orders.update_status\",\"menu.view\"]', '2025-07-04 11:12:05', '2025-07-04 11:12:05'),
(5, 'delivery', 'Repartidor', 'Entrega pedidos a domicilio', '[\"orders.delivery\",\"orders.update_location\",\"orders.complete\"]', '2025-07-04 11:12:05', '2025-07-04 11:12:05'),
(6, 'waiter', 'Mesero/Atención', 'Atiende clientes y toma pedidos', '[\"orders.create\",\"orders.view\",\"customers.assist\",\"menu.view\"]', '2025-07-04 11:12:05', '2025-07-04 11:12:05'),
(7, 'customer', 'Cliente', 'Usuario final del sistema', '[\"orders.create\",\"orders.view_own\",\"profile.manage\",\"reviews.create\"]', '2025-07-04 11:12:05', '2025-07-04 11:12:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `role` enum('customer','owner','admin') NOT NULL DEFAULT 'customer',
  `owned_restaurant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `address` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `role`, `owned_restaurant_id`, `password`, `remember_token`, `created_at`, `updated_at`, `address`, `avatar`, `is_active`, `last_login_at`) VALUES
(1, 'Super Admin', 'admin@delivery.com', '+52 55 0000 0000', '2025-07-04 11:12:05', 'admin', NULL, '$2y$12$JHwQ0u0nAnUGsh6spjVsU.f8Qk.SRDK2rebyWJ0Nx1MmYLn3xi2Va', NULL, '2025-07-04 11:12:05', '2025-07-04 11:12:05', 'Oficinas Centrales', NULL, 1, NULL),
(2, 'Mario Rossi', 'mario@pizzaitalia.com', '+52 55 1111 1111', '2025-07-04 11:12:05', 'owner', 1, '$2y$12$qu/uSjGvEQIrxTG4gSE6BuRXw.ceyiKO3SOptgLUZJgh00P.knTBW', NULL, '2025-07-04 11:12:05', '2025-07-04 11:12:06', 'Via Roma 45, Centro Histórico', NULL, 1, NULL),
(3, 'Carlos Mendoza', 'carlos@burgerhouse.com', '+52 55 2222 2222', '2025-07-04 11:12:05', 'owner', 2, '$2y$12$NXZsxFCEx7uWkHLxzcZTZuFU14Himu1NpXkJEmdDxQ4KvY0W4JbGK', NULL, '2025-07-04 11:12:05', '2025-07-04 11:12:06', 'Av. Principal 789, Zona Comercial', NULL, 1, NULL),
(4, 'Ana Tanaka', 'ana@sushizen.com', '+52 55 3333 3333', '2025-07-04 11:12:05', 'owner', 3, '$2y$12$eOLN1HQGlLwrAVNzVBknmur3ResKXijAy8S6whr6O2AiVOkC9mdgC', NULL, '2025-07-04 11:12:05', '2025-07-04 11:12:06', 'Calle Sakura 321, Distrito Japonés', NULL, 1, NULL),
(5, 'José Hernández', 'jose@tacoschingones.com', '+52 55 4444 4444', '2025-07-04 11:12:05', 'owner', 4, '$2y$12$pGX.Z7AuGqiFL75fO5VZIO0Pd/B9xU2OZ2dOhtz2PsYc7jHiIwjji', NULL, '2025-07-04 11:12:06', '2025-07-04 11:12:06', 'Av. Revolución 456, Barrio Mexicano', NULL, 1, NULL),
(6, 'María García', 'customer@delivery.com', '+52 55 9876 5432', '2025-07-04 11:12:06', 'customer', NULL, '$2y$12$3eH6zBnMLm6XjcIYweEFMerM7rf0kPDPq6Oa8b09CfxgPmcNRQrY.', NULL, '2025-07-04 11:12:06', '2025-07-04 11:12:06', 'Calle Revolución 456, CDMX', NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_favorites`
--

CREATE TABLE `user_favorites` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `restaurant_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_roles`
--

CREATE TABLE `user_roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `restaurant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`, `restaurant_id`, `is_active`, `assigned_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 1, '2025-07-04 05:12:05', '2025-07-04 11:12:05', '2025-07-04 11:12:05'),
(2, 2, 2, NULL, 1, '2025-07-04 05:12:05', '2025-07-04 11:12:05', '2025-07-04 11:12:05'),
(3, 3, 2, NULL, 1, '2025-07-04 05:12:05', '2025-07-04 11:12:05', '2025-07-04 11:12:05'),
(4, 4, 2, NULL, 1, '2025-07-04 05:12:05', '2025-07-04 11:12:05', '2025-07-04 11:12:05'),
(5, 5, 2, NULL, 1, '2025-07-04 05:12:06', '2025-07-04 11:12:06', '2025-07-04 11:12:06'),
(6, 6, 7, NULL, 1, '2025-07-04 05:12:06', '2025-07-04 11:12:06', '2025-07-04 11:12:06');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `employee_settings`
--
ALTER TABLE `employee_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_settings_user_id_foreign` (`user_id`),
  ADD KEY `employee_settings_restaurant_id_foreign` (`restaurant_id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_user_id_foreign` (`user_id`),
  ADD KEY `orders_restaurant_id_foreign` (`restaurant_id`),
  ADD KEY `orders_delivery_person_id_foreign` (`delivery_person_id`);

--
-- Indices de la tabla `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_restaurant_id_foreign` (`restaurant_id`);

--
-- Indices de la tabla `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `restaurants_slug_unique` (`slug`),
  ADD KEY `restaurants_owner_id_foreign` (`owner_id`);

--
-- Indices de la tabla `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reviews_user_id_order_id_unique` (`user_id`,`order_id`),
  ADD KEY `reviews_order_id_foreign` (`order_id`),
  ADD KEY `reviews_restaurant_id_rating_index` (`restaurant_id`,`rating`),
  ADD KEY `reviews_user_id_created_at_index` (`user_id`,`created_at`);

--
-- Indices de la tabla `review_votes`
--
ALTER TABLE `review_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `review_votes_review_id_user_id_unique` (`review_id`,`user_id`),
  ADD KEY `review_votes_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indices de la tabla `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_favorites_user_id_restaurant_id_unique` (`user_id`,`restaurant_id`),
  ADD KEY `user_favorites_restaurant_id_foreign` (`restaurant_id`);

--
-- Indices de la tabla `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_roles_user_id_role_id_restaurant_id_unique` (`user_id`,`role_id`,`restaurant_id`),
  ADD KEY `user_roles_role_id_foreign` (`role_id`),
  ADD KEY `user_roles_restaurant_id_foreign` (`restaurant_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `employee_settings`
--
ALTER TABLE `employee_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `review_votes`
--
ALTER TABLE `review_votes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `user_favorites`
--
ALTER TABLE `user_favorites`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `employee_settings`
--
ALTER TABLE `employee_settings`
  ADD CONSTRAINT `employee_settings_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_delivery_person_id_foreign` FOREIGN KEY (`delivery_person_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `restaurants`
--
ALTER TABLE `restaurants`
  ADD CONSTRAINT `restaurants_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reviews_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `review_votes`
--
ALTER TABLE `review_votes`
  ADD CONSTRAINT `review_votes_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD CONSTRAINT `user_favorites_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
