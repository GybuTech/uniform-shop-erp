-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 08, 2026 at 09:17 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uniform_shop_erp`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `code`, `address`, `phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Main Shop', 'BR-01', 'Nairobi Central Business District', '+254700111222', 1, '2026-10-13 21:22:44', '2026-10-13 21:22:44'),
(2, 'STALL 35', 'BR-02', 'Kayole Market', '+254700333444', 1, '2026-10-13 21:22:44', '2026-10-14 14:12:12'),
(3, 'Kyoi stage(sweaters)', 'BR-03', 'Kayole Kyoi stage', '+254700555666', 1, '2026-10-13 21:22:44', '2026-10-14 14:15:22');

-- --------------------------------------------------------

--
-- Table structure for table `branch_stocks`
--

CREATE TABLE `branch_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED NOT NULL,
  `stock_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_stocks`
--

INSERT INTO `branch_stocks` (`id`, `branch_id`, `branch_name`, `product_variant_id`, `stock_quantity`, `created_at`, `updated_at`) VALUES
(2702, 2, 'STALL 35', 7707, 5.00, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(2703, 2, 'STALL 35', 7720, 10.00, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(2704, 2, 'STALL 35', 7721, 7.00, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(2705, 2, 'STALL 35', 7722, 5.00, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(2706, 2, 'STALL 35', 7723, 10.00, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(2707, 2, 'STALL 35', 7724, 10.00, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(2708, 2, 'STALL 35', 7725, 9.00, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(2709, 2, 'STALL 35', 7746, 9.00, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(2710, 2, 'STALL 35', 7747, 9.00, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(2711, 2, 'STALL 35', 7748, 10.00, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(2712, 2, 'STALL 35', 7749, 10.00, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(2713, 2, 'STALL 35', 7750, 3.00, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(2714, 2, 'STALL 35', 7751, 13.00, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(2715, 2, 'STALL 35', 7752, 14.00, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(2716, 2, 'STALL 35', 7753, 9.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2717, 2, 'STALL 35', 7754, 2.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2718, 2, 'STALL 35', 7755, 7.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2719, 2, 'STALL 35', 7756, 5.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2720, 2, 'STALL 35', 7768, 4.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2721, 2, 'STALL 35', 7769, 5.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2722, 2, 'STALL 35', 7770, 9.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2723, 2, 'STALL 35', 7796, 1.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2724, 2, 'STALL 35', 7805, 5.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2725, 2, 'STALL 35', 7806, 4.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2726, 2, 'STALL 35', 7807, 3.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2727, 2, 'STALL 35', 7808, 15.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2728, 2, 'STALL 35', 7809, 10.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2729, 2, 'STALL 35', 7810, 13.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2730, 2, 'STALL 35', 7811, 9.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2731, 2, 'STALL 35', 7819, 6.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2732, 2, 'STALL 35', 7820, 6.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2733, 2, 'STALL 35', 7855, 1.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2734, 2, 'STALL 35', 7856, 3.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2735, 2, 'STALL 35', 7860, 12.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2736, 2, 'STALL 35', 7861, 13.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2737, 2, 'STALL 35', 7872, 10.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2738, 2, 'STALL 35', 7873, 6.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2739, 2, 'STALL 35', 7874, 6.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2740, 2, 'STALL 35', 7876, 3.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2741, 2, 'STALL 35', 7888, 17.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2742, 2, 'STALL 35', 7889, 11.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2743, 2, 'STALL 35', 7890, 12.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2744, 2, 'STALL 35', 7891, 10.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2745, 2, 'STALL 35', 7892, 8.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2746, 2, 'STALL 35', 7893, 10.00, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(2747, 2, 'STALL 35', 7894, 9.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2748, 2, 'STALL 35', 7895, 10.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2749, 2, 'STALL 35', 7896, 10.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2750, 2, 'STALL 35', 7913, 5.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2751, 2, 'STALL 35', 7914, 6.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2752, 2, 'STALL 35', 7915, 7.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2753, 2, 'STALL 35', 7916, 6.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2754, 2, 'STALL 35', 7917, 6.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2755, 2, 'STALL 35', 7918, 6.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2756, 2, 'STALL 35', 7919, 6.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2757, 2, 'STALL 35', 7920, 3.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2758, 2, 'STALL 35', 7922, 15.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2759, 2, 'STALL 35', 7923, 6.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2760, 2, 'STALL 35', 7924, 7.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2761, 2, 'STALL 35', 7925, 8.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2762, 2, 'STALL 35', 7926, 8.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2763, 2, 'STALL 35', 7930, 4.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2764, 2, 'STALL 35', 7931, 10.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2765, 2, 'STALL 35', 7932, 5.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2766, 2, 'STALL 35', 7933, 4.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2767, 2, 'STALL 35', 7934, 1.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2768, 2, 'STALL 35', 7936, 6.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2769, 2, 'STALL 35', 7937, 12.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2770, 2, 'STALL 35', 7948, 8.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2771, 2, 'STALL 35', 7952, 2.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2772, 2, 'STALL 35', 8003, 4.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2773, 2, 'STALL 35', 8004, 3.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2774, 2, 'STALL 35', 8005, 3.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2775, 2, 'STALL 35', 8014, 4.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2776, 2, 'STALL 35', 8015, 2.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2777, 2, 'STALL 35', 8016, 3.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2778, 2, 'STALL 35', 8017, 3.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2779, 2, 'STALL 35', 8018, 2.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2780, 2, 'STALL 35', 8019, 3.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2781, 2, 'STALL 35', 8022, 8.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2782, 2, 'STALL 35', 8023, 6.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2783, 2, 'STALL 35', 8024, 17.00, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(2784, 2, 'STALL 35', 8029, 10.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2785, 2, 'STALL 35', 8030, 2.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2786, 2, 'STALL 35', 8031, 2.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2787, 2, 'STALL 35', 8032, 8.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2788, 2, 'STALL 35', 8034, 11.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2789, 2, 'STALL 35', 8035, 7.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2790, 2, 'STALL 35', 8036, 15.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2791, 2, 'STALL 35', 8091, 4.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2792, 2, 'STALL 35', 8092, 4.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2793, 2, 'STALL 35', 8093, 4.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2794, 2, 'STALL 35', 8094, 3.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2795, 2, 'STALL 35', 8095, 4.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2796, 2, 'STALL 35', 8098, 4.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2797, 2, 'STALL 35', 8099, 6.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2798, 2, 'STALL 35', 8100, 4.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2799, 2, 'STALL 35', 8194, 9.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2800, 2, 'STALL 35', 8195, 6.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2801, 2, 'STALL 35', 8196, 16.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2802, 2, 'STALL 35', 8197, 8.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2803, 2, 'STALL 35', 8201, 1.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2804, 2, 'STALL 35', 8202, 9.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2805, 2, 'STALL 35', 8203, 6.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2806, 2, 'STALL 35', 8204, 16.00, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(2807, 2, 'STALL 35', 8205, 8.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2808, 2, 'STALL 35', 8209, 1.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2809, 2, 'STALL 35', 8211, 11.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2810, 2, 'STALL 35', 8212, 14.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2811, 2, 'STALL 35', 8213, 11.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2812, 2, 'STALL 35', 8214, 6.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2813, 2, 'STALL 35', 8215, 6.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2814, 2, 'STALL 35', 8216, 6.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2815, 2, 'STALL 35', 8218, 8.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2816, 2, 'STALL 35', 8219, 4.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2817, 2, 'STALL 35', 8221, 4.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2818, 2, 'STALL 35', 8222, 1.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2819, 2, 'STALL 35', 8223, 3.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2820, 2, 'STALL 35', 8224, 3.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2821, 2, 'STALL 35', 8225, 10.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2822, 2, 'STALL 35', 8226, 7.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2823, 2, 'STALL 35', 8227, 10.00, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(2824, 2, 'STALL 35', 8276, 1.00, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(2825, 2, 'STALL 35', 8285, 6.00, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(2826, 2, 'STALL 35', 8286, 4.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2827, 2, 'STALL 35', 8287, 6.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2828, 2, 'STALL 35', 8288, 2.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2829, 2, 'STALL 35', 8289, 3.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2830, 2, 'STALL 35', 8290, 5.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2831, 2, 'STALL 35', 8291, 6.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2832, 2, 'STALL 35', 8452, 10.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2833, 2, 'STALL 35', 8453, 9.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2834, 2, 'STALL 35', 8454, 7.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2835, 2, 'STALL 35', 8455, 13.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2836, 2, 'STALL 35', 8456, 5.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2837, 2, 'STALL 35', 8457, 13.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2838, 2, 'STALL 35', 8458, 18.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2839, 2, 'STALL 35', 8459, 19.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2840, 2, 'STALL 35', 8471, 6.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2841, 2, 'STALL 35', 8472, 8.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2842, 2, 'STALL 35', 8473, 18.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2843, 2, 'STALL 35', 8474, 16.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2844, 2, 'STALL 35', 8475, 7.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2845, 2, 'STALL 35', 8476, 8.00, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(2846, 2, 'STALL 35', 8477, 12.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2847, 2, 'STALL 35', 8478, 9.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2848, 2, 'STALL 35', 8516, 1.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2849, 2, 'STALL 35', 8560, 8.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2850, 2, 'STALL 35', 8561, 7.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2851, 2, 'STALL 35', 8562, 6.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2852, 2, 'STALL 35', 8563, 7.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2853, 2, 'STALL 35', 8564, 8.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2854, 2, 'STALL 35', 8568, 9.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2855, 2, 'STALL 35', 8569, 7.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2856, 2, 'STALL 35', 8570, 8.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2857, 2, 'STALL 35', 8571, 1.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2858, 2, 'STALL 35', 8572, 9.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2859, 2, 'STALL 35', 8573, 3.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2860, 2, 'STALL 35', 8582, 1.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2861, 2, 'STALL 35', 8588, 1.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2862, 2, 'STALL 35', 8596, 10.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2863, 2, 'STALL 35', 8597, 10.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2864, 2, 'STALL 35', 8598, 9.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2865, 2, 'STALL 35', 8637, 7.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2866, 2, 'STALL 35', 8638, 2.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2867, 2, 'STALL 35', 8639, 8.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2868, 2, 'STALL 35', 8650, 4.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2869, 2, 'STALL 35', 8651, 3.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2870, 2, 'STALL 35', 8652, 4.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2871, 2, 'STALL 35', 8653, 3.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2872, 2, 'STALL 35', 8654, 7.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2873, 2, 'STALL 35', 8655, 4.00, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(2874, 2, 'STALL 35', 8663, 8.00, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(2875, 2, 'STALL 35', 8664, 13.00, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(2876, 2, 'STALL 35', 8665, 6.00, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(2877, 2, 'STALL 35', 8666, 5.00, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(2878, 2, 'STALL 35', 8667, 9.00, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(2879, 2, 'STALL 35', 8668, 7.00, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(2880, 2, 'STALL 35', 8669, 9.00, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(2881, 2, 'STALL 35', 8679, 7.00, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(2882, 2, 'STALL 35', 8680, 8.00, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(2883, 2, 'STALL 35', 8681, 6.00, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(2884, 2, 'STALL 35', 8682, 7.00, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(2885, 2, 'STALL 35', 8683, 6.00, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(2886, 2, 'STALL 35', 8684, 9.00, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(2887, 2, 'STALL 35', 8685, 4.00, '2026-11-06 19:04:32', '2026-11-06 19:04:32');

-- --------------------------------------------------------

--
-- Table structure for table `business_settings`
--

CREATE TABLE `business_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `business_settings`
--

INSERT INTO `business_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'business_name', 'Tshaprint Solutions', '2026-10-14 13:58:44', '2026-11-01 23:14:28'),
(2, 'business_email', 'sales@tshaprint.co.ke', '2026-10-14 13:58:44', '2026-10-14 13:58:44'),
(3, 'business_phone', '+254 720725889', '2026-10-14 13:58:44', '2026-11-02 12:56:24'),
(4, 'business_address', 'Kayole stage No. 20, Kenya', '2026-10-14 13:58:44', '2026-11-02 12:56:24'),
(5, 'business_currency', 'KES', '2026-10-14 13:58:44', '2026-10-14 13:58:44'),
(6, 'system_time_offset_seconds', '0', '2026-10-14 13:58:44', '2026-10-14 14:00:43'),
(7, 'business_tagline', 'School Uniforms & Quality Apparel', '2026-11-01 23:14:27', '2026-11-01 23:14:28'),
(8, 'kra_pin', 'P051998877Z', '2026-11-01 23:14:27', '2026-11-01 23:14:27'),
(9, 'vat_number', 'VAT-998877', '2026-11-01 23:14:27', '2026-11-01 23:14:27'),
(10, 'receipt_header_note', NULL, '2026-11-01 23:14:27', '2026-11-02 12:56:24'),
(11, 'receipt_footer_note', 'Goods once sold cannot be returned.', '2026-11-01 23:14:27', '2026-11-02 12:56:24'),
(12, 'pricing_tier_bulk_discount', '10', '2026-11-02 12:56:24', '2026-11-02 12:56:24'),
(13, 'pricing_tier_wholesale_discount', '15', '2026-11-02 12:56:25', '2026-11-02 12:56:25'),
(14, 'pricing_tier_bulk_label', 'School Bulk Tier', '2026-11-02 12:56:25', '2026-11-02 12:56:25'),
(15, 'pricing_tier_wholesale_label', 'Wholesale Contract Tier', '2026-11-02 12:56:25', '2026-11-02 12:56:25'),
(16, 'mpesa_till_number', '892301', '2026-11-02 14:33:19', '2026-11-02 14:33:19'),
(17, 'mpesa_paybill_number', '247247', '2026-11-02 14:33:20', '2026-11-02 14:33:20'),
(18, 'standard_uom_list', 'Piece, Pair, Kg, Meter, Litre, Bag, Box, Dozen, Packet, Roll, Set, Bottle', '2026-11-02 14:43:46', '2026-11-02 14:43:46'),
(19, 'currency', 'KES', '2026-11-03 08:39:27', '2026-11-03 08:39:27');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'School Sweaters', 'Official School Sweaters & Pullovers', NULL, '2026-08-26 20:41:36', '2026-08-26 20:41:36'),
(2, 'Tracksuits', 'School & Sports Tracksuits', NULL, '2026-08-26 20:41:36', '2026-08-26 20:41:36'),
(3, 'Fleece Jackets', 'Heavy Duty Warm Fleece Jackets', NULL, '2026-08-26 20:41:36', '2026-08-26 20:41:36'),
(4, 'T-Shirts & Polo', 'School Games T-Shirts & Polos', NULL, '2026-08-26 20:41:37', '2026-08-26 20:41:37'),
(9, 'General Uniforms', NULL, NULL, '2026-11-05 07:31:14', '2026-11-06 18:40:08');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `first_name`, `last_name`, `phone`, `email`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Real', 'Peak', '+254712345678', NULL, NULL, '2026-09-28 11:24:56', '2026-09-28 11:24:56'),
(2, 'Walk-in', 'Customer', '0700000000', 'walkin@pos.local', NULL, '2026-11-02 14:43:46', '2026-11-02 14:43:46');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `expense_number` varchar(50) NOT NULL,
  `branch_name` varchar(100) NOT NULL DEFAULT 'Main Branch',
  `category` varchar(100) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `note` text DEFAULT NULL,
  `expense_date` date NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
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
-- Table structure for table `jobs`
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
-- Table structure for table `job_batches`
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
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_07_03_230320_create_permission_tables', 1),
(5, '2026_07_04_002232_add_description_to_roles_table', 1),
(6, '2026_07_15_190946_create_categories_table', 1),
(7, '2026_07_15_223543_create_products_table', 1),
(8, '2026_07_15_230732_create_product_variants_table', 1),
(9, '2026_07_15_235241_create_customers_table', 1),
(10, '2026_07_15_235256_create_sales_table', 1),
(11, '2026_07_16_004801_create_sale_items_table', 1),
(12, '2026_07_23_000002_add_is_active_to_users_table', 1),
(13, '2026_07_27_185456_create_stock_entries_table', 1),
(14, '2026_07_27_185457_create_stock_entry_items_table', 1),
(15, '2026_08_25_000000_add_branch_name_to_sales_table', 1),
(16, '2026_10_10_000000_add_username_to_users_table', 2),
(17, '2026_10_10_000001_create_expenses_table', 2),
(18, '2026_10_10_000002_create_quotations_table', 2),
(19, '2026_10_10_000003_create_stock_transfers_table', 2),
(20, '2026_10_10_000004_create_stock_adjustments_table', 2),
(21, '2026_10_14_000000_create_branches_table', 3),
(22, '2026_10_14_000001_create_purchases_table', 3),
(23, '2026_10_15_000000_add_branch_to_users_table', 4),
(24, '2026_10_15_000001_create_business_settings_table', 4),
(25, '2026_10_26_164404_add_transfer_date_and_status_to_stock_transfers_table', 5),
(26, '2026_10_26_214500_make_purchase_items_flexible_for_raw_materials', 6),
(27, '2026_11_02_000001_create_shifts_table', 7),
(28, '2026_11_02_000002_add_shift_id_to_sales_table', 7),
(29, '2026_11_02_000003_make_variant_attributes_flexible', 8),
(30, '2026_11_02_000004_make_quantities_decimal', 9),
(31, '2026_11_02_000005_add_payment_reference_to_sales_table', 10),
(32, '2026_11_02_000006_create_branch_stocks_table', 11),
(33, '2026_11_03_000007_add_doorway_fields_to_stock_entries_table', 12),
(34, '2026_11_04_000008_add_cash_rollover_fields_to_shifts_table', 13),
(35, '2026_11_04_000009_add_closed_at_to_shifts_table', 14);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(4, 'App\\Models\\User', 2),
(4, 'App\\Models\\User', 3),
(4, 'App\\Models\\User', 4),
(6, 'App\\Models\\User', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view-dashboard', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(2, 'view-categories', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(3, 'create-categories', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(4, 'edit-categories', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(5, 'delete-categories', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(6, 'view-products', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(7, 'create-products', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(8, 'edit-products', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(9, 'delete-products', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(10, 'view-product-variants', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(11, 'create-product-variants', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(12, 'edit-product-variants', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(13, 'delete-product-variants', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(14, 'view-customers', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(15, 'create-customers', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(16, 'edit-customers', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(17, 'delete-customers', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(18, 'view-sales', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(19, 'create-sales', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(20, 'refund-sales', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(21, 'void-sales', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(22, 'view-stock-entries', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(23, 'create-stock-entries', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(24, 'edit-stock-entries', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(25, 'delete-stock-entries', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(26, 'view-inventory', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(27, 'adjust-inventory', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(28, 'view-reports', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(29, 'view-sales-reports', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(30, 'view-inventory-reports', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(31, 'view-financial-reports', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(32, 'view-user-management', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(33, 'create-users', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(34, 'edit-users', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(35, 'delete-users', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(36, 'assign-roles', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(37, 'assign-permissions', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(38, 'manage-system-settings', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(39, 'view-audit-logs', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(40, 'perform-backups', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(41, 'restore-backups', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(42, 'approve-stock-entries', 'web', '2026-11-05 18:07:38', '2026-11-05 18:07:38'),
(43, 'adjust-stock', 'web', '2026-11-05 18:07:39', '2026-11-05 18:07:39');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `sku_prefix` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `sku_prefix`, `description`, `deleted_at`, `created_at`, `updated_at`) VALUES
(983, 9, 'Accurate Sleeveless', 'ACCSLE', 'Uniform item: Accurate Sleeveless', NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(984, 1, 'Accurate Sweaters badge.', 'ACCSWEBAD', 'Uniform item: Accurate Sweaters badge.', NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(985, 2, 'Accurate Tracksuit', 'ACCTRA', 'Uniform item: Accurate Tracksuit', NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(986, 1, 'Accurate Jss Girls sweaters', 'ACCJSSGIRSWE', 'Uniform item: Accurate Jss Girls sweaters', NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(987, 1, 'Accurate Junior Sweaters badge.', 'ACCJUNSWEBAD', 'Uniform item: Accurate Junior Sweaters badge.', NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(988, 2, 'Accurate Junior Tracksuit', 'ACCJUNTRA', 'Uniform item: Accurate Junior Tracksuit', NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(989, 2, 'Accurate Jss Tracksuit', 'ACCJSSTRA', 'Uniform item: Accurate Jss Tracksuit', NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(990, 1, 'Accurate Sleeveless Sweaters', 'ACCSLESWE', 'Uniform item: Accurate Sleeveless Sweaters', NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(991, 1, 'Black /Cream Sweater', 'BLACRESWE', 'Uniform item: Black /Cream Sweater', NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(992, 1, 'Beige Chocolate Sweater', 'BEICHOSWE', 'Uniform item: Beige Chocolate Sweater', NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(993, 1, 'Bookland Jss Sweaters badge.', 'BOOJSSSWEBAD', 'Uniform item: Bookland Jss Sweaters badge.', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(994, 2, 'Bookland Junior Tracksuit', 'BOOJUNTRA', 'Uniform item: Bookland Junior Tracksuit', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(995, 1, 'Bondeni Sweaters badge.', 'BONSWEBAD', 'Uniform item: Bondeni Sweaters badge.', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(996, 1, 'Bookland Sweaters badge.', 'BOOSWEBAD', 'Uniform item: Bookland Sweaters badge.', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(997, 1, 'Black Plain Sweaters.', 'BLAPLASWE', 'Uniform item: Black Plain Sweaters.', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(998, 1, 'Black Plain Sweater', 'BLAPLASWE2', 'Uniform item: Black Plain Sweater', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(999, 1, 'Black Red Sweater', 'BLAREDSWE', 'Uniform item: Black Red Sweater', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(1000, 1, 'Black White Sweater', 'BLAWHISWE', 'Uniform item: Black White Sweater', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(1001, 1, 'C/Brown Beige Sweater', 'CBROBEISWE', 'Uniform item: C/Brown Beige Sweater', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(1002, 3, 'C/Brown Fleece Jacket', 'CBROFLEJAC', 'Uniform item: C/Brown Fleece Jacket', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(1003, 2, 'C/Brown White Tracksuits.', 'CBROWHITRA', 'Uniform item: C/Brown White Tracksuits.', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(1004, 4, 'Cream White T-Shirt', 'CREWHITSHI', 'Uniform item: Cream White T-Shirt', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(1005, 1, 'D/Green Plain Sweaters', 'DGREPLASWE', 'Uniform item: D/Green Plain Sweaters', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(1006, 1, 'D/Green White Sweaters', 'DGREWHISWE', 'Uniform item: D/Green White Sweaters', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(1007, 2, 'D/Green White Tracksuit', 'DGREWHITRA', 'Uniform item: D/Green White Tracksuit', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(1008, 1, 'Doonholm Jss Sweaters badge.', 'DOOJSSSWEBAD', 'Uniform item: Doonholm Jss Sweaters badge.', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(1009, 1, 'Edelvale Junior Sweaters badge.', 'EDEJUNSWEBAD', 'Uniform item: Edelvale Junior Sweaters badge.', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(1010, 1, 'Edelvale Jss Sweaters badge.', 'EDEJSSSWEBAD', 'Uniform item: Edelvale Jss Sweaters badge.', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(1011, 1, 'Edelvale Pri Sweaters badge.', 'EDEPRISWEBAD', 'Uniform item: Edelvale Pri Sweaters badge.', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(1012, 1, 'Grey Blue White Sweater', 'GREBLUWHISWE', 'Uniform item: Grey Blue White Sweater', NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(1013, 1, 'Great Commission Sweaters badge.', 'GRECOMSWEBAD', 'Uniform item: Great Commission Sweaters badge.', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1014, 3, 'Grey Fleece Jacket', 'GREFLEJAC', 'Uniform item: Grey Fleece Jacket', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1015, 1, 'Grey Maroon White Sweater', 'GREMARWHISWE', 'Uniform item: Grey Maroon White Sweater', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1016, 1, 'Grey Plain Sweater', 'GREPLASWE', 'Uniform item: Grey Plain Sweater', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1017, 1, 'Green Plain Sweater', 'GREPLASWE2', 'Uniform item: Green Plain Sweater', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1018, 1, 'Grey/Red Sweater', 'GREREDSWE', 'Uniform item: Grey/Red Sweater', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1019, 1, 'Grey/white sweaters', 'GREWHISWE', 'Uniform item: Grey/white sweaters', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1020, 2, 'Grey White Tracksuits.', 'GREWHITRA', 'Uniform item: Grey White Tracksuits.', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1021, 2, 'Grey Yellow Tracksuit', 'GREYELTRA', 'Uniform item: Grey Yellow Tracksuit', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1022, 2, 'Hekima Boys Tracksuit', 'HEKBOYTRA', 'Uniform item: Hekima Boys Tracksuit', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1023, 1, 'Hekima junior Sweaters badge.', 'HEKJUNSWEBAD', 'Uniform item: Hekima junior Sweaters badge.', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1024, 1, 'Hekima Sweaters badge.', 'HEKSWEBAD', 'Uniform item: Hekima Sweaters badge.', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1025, 2, 'Hekima Girls Tracksuit', 'HEKGIRTRA', 'Uniform item: Hekima Girls Tracksuit', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1026, 1, 'Horizon Junior Sweaters badge.', 'HORJUNSWEBAD', 'Uniform item: Horizon Junior Sweaters badge.', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1027, 2, 'Halisi Junior Tracksuit', 'HALJUNTRA', 'Uniform item: Halisi Junior Tracksuit', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1028, 2, 'Halisi Junior Tracksuits.', 'HALJUNTRA2', 'Uniform item: Halisi Junior Tracksuits.', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1029, 1, 'Horizon Sweaters badge.', 'HORSWEBAD', 'Uniform item: Horizon Sweaters badge.', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1030, 1, 'Imara Junior Sweaters badge.', 'IMAJUNSWEBAD', 'Uniform item: Imara Junior Sweaters badge.', NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(1031, 9, 'Imara Junior Sleeveless', 'IMAJUNSLE', 'Uniform item: Imara Junior Sleeveless', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1032, 1, 'Imara Sweaters badge.', 'IMASWEBAD', 'Uniform item: Imara Sweaters badge.', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1033, 2, 'Kibukosya Boys Tracksuit', 'KIBBOYTRA', 'Uniform item: Kibukosya Boys Tracksuit', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1034, 2, 'Kibukosya Boys Tracksuits.', 'KIBBOYTRA2', 'Uniform item: Kibukosya Boys Tracksuits.', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1035, 2, 'Kibukosya Girls Tracksuit', 'KIBGIRTRA', 'Uniform item: Kibukosya Girls Tracksuit', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1036, 2, 'Kibukosya Girls Tracksuits.', 'KIBGIRTRA2', 'Uniform item: Kibukosya Girls Tracksuits.', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1037, 1, 'Komarock Junior Sweaters badge.', 'KOMJUNSWEBAD', 'Uniform item: Komarock Junior Sweaters badge.', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1038, 1, 'Komarock Jss Sweaters badge.', 'KOMJSSSWEBAD', 'Uniform item: Komarock Jss Sweaters badge.', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1039, 1, 'C/brown plain Sweaters.', 'CBROPLASWE', 'Uniform item: C/brown plain Sweaters.', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1040, 1, 'Kifaru Primary Sweaters badge.', 'KIFPRISWEBAD', 'Uniform item: Kifaru Primary Sweaters badge.', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1041, 1, 'Komarock South Jss Sweaters badge.', 'KOMSOUJSSSWEBAD', 'Uniform item: Komarock South Jss Sweaters badge.', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1042, 2, 'Komarock South Junior Tracksuit', 'KOMSOUJUNTRA', 'Uniform item: Komarock South Junior Tracksuit', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1043, 1, 'Komarock South Sweaters badge.', 'KOMSOUSWEBAD', 'Uniform item: Komarock South Sweaters badge.', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1044, 2, 'Komarock south Tracksuits.', 'KOMSOUTRA', 'Uniform item: Komarock south Tracksuits.', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1045, 1, 'Lakewood Sweaters badge.', 'LAKSWEBAD', 'Uniform item: Lakewood Sweaters badge.', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1046, 2, 'L/Blue/Navy Tracksuit', 'LBLUNAVTRA', 'Uniform item: L/Blue/Navy Tracksuit', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1047, 2, 'L/Blue Royal Tracksuit', 'LBLUROYTRA', 'Uniform item: L/Blue Royal Tracksuit', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1048, 1, 'L/Blue White Sweaters.', 'LBLUWHISWE', 'Uniform item: L/Blue White Sweaters.', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1049, 2, 'L/Blue White Tracksuit', 'LBLUWHITRA', 'Uniform item: L/Blue White Tracksuit', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1050, 4, 'Ldb Red T-Shirt', 'LDBREDTSHI', 'Uniform item: Ldb Red T-Shirt', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1051, 1, 'Lakewood Sleev Sweater', 'LAKSLESWE', 'Uniform item: Lakewood Sleev Sweater', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1052, 4, 'Ldb White T-Shirt', 'LDBWHITSHI', 'Uniform item: Ldb White T-Shirt', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1053, 1, 'Mwangaza Jss Sweaters badge.', 'MWAJSSSWEBAD', 'Uniform item: Mwangaza Jss Sweaters badge.', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1054, 4, 'Maroon Normal T-Shirt', 'MARNORTSHI', 'Uniform item: Maroon Normal T-Shirt', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1055, 1, 'Maroon/Plain Sweater', 'MARPLASWE', 'Uniform item: Maroon/Plain Sweater', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1056, 1, 'Maroon Plain Sweater', 'MARPLASWE2', 'Uniform item: Maroon Plain Sweater', NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(1057, 1, 'Mwangaza Senior Sweaters badge.', 'MWASENSWEBAD', 'Uniform item: Mwangaza Senior Sweaters badge.', NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(1058, 1, 'Mwangaza Senior Sleeveless Sweater', 'MWASENSLESWE', 'Uniform item: Mwangaza Senior Sleeveless Sweater', NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(1059, 1, 'Mwangaza School Sweaters badge.', 'MWASCHSWEBAD', 'Uniform item: Mwangaza School Sweaters badge.', NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(1060, 1, 'Maroon White Sweater', 'MARWHISWE', 'Uniform item: Maroon White Sweater', NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(1061, 2, 'Maroon/White Tracksuit', 'MARWHITRA', 'Uniform item: Maroon/White Tracksuit', NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(1062, 2, 'Maroon white Tracksuits.', 'MARWHITRA2', 'Uniform item: Maroon white Tracksuits.', NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(1063, 2, 'Maroon/Yellow Tracksuit', 'MARYELTRA', 'Uniform item: Maroon/Yellow Tracksuit', NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(1064, 2, 'Maroon Yellow Tracksuits.', 'MARYELTRA2', 'Uniform item: Maroon Yellow Tracksuits.', NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(1065, 3, 'N/Blue Fleece Jacket', 'NBLUFLEJAC', 'Uniform item: N/Blue Fleece Jacket', NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(1066, 1, 'Navy Blue Plain sweaters.', 'NAVBLUPLASWE', 'Uniform item: Navy Blue Plain sweaters.', NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(1067, 1, 'N/Blue Purple Sweater', 'NBLUPURSWE', 'Uniform item: N/Blue Purple Sweater', NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(1068, 1, 'N/Blue Plain Sweater', 'NBLUPLASWE', 'Uniform item: N/Blue Plain Sweater', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1069, 2, 'N/Blue Red Tracksuit', 'NBLUREDTRA', 'Uniform item: N/Blue Red Tracksuit', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1070, 2, 'Navy Blue Red Tracksuits.', 'NAVBLUREDTRA', 'Uniform item: Navy Blue Red Tracksuits.', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1071, 1, 'N/Blue Sky Sweater', 'NBLUSKYSWE', 'Uniform item: N/Blue Sky Sweater', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1072, 2, 'N/Blue Sky Tracksuit', 'NBLUSKYTRA', 'Uniform item: N/Blue Sky Tracksuit', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1073, 2, 'Navy Blue Sky Tracksuits.', 'NAVBLUSKYTRA', 'Uniform item: Navy Blue Sky Tracksuits.', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1074, 1, 'N/Blue White Sweater', 'NBLUWHISWE', 'Uniform item: N/Blue White Sweater', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1075, 2, 'Navy Blue White Tracksuits.', 'NAVBLUWHITRA', 'Uniform item: Navy Blue White Tracksuits.', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1076, 1, 'P.C.E.A Sosian Sweaters badge.', 'PCESOSSWEBAD', 'Uniform item: P.C.E.A Sosian Sweaters badge.', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1077, 2, 'P.C.E.A Sosian Tracksuit', 'PCESOSTRA', 'Uniform item: P.C.E.A Sosian Tracksuit', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1078, 2, 'P.C.E.A Sosian Tracksuits.', 'PCESOSTRA2', 'Uniform item: P.C.E.A Sosian Tracksuits.', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1079, 2, 'Potters Tracksuit', 'POTTRA', 'Uniform item: Potters Tracksuit', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1080, 4, 'Pink T-Shirt', 'PINTSHI', 'Uniform item: Pink T-Shirt', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1081, 2, 'Royal Blue White Tracksuits.', 'ROYBLUWHITRA', 'Uniform item: Royal Blue White Tracksuits.', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1082, 3, 'Red Fleece Jacket', 'REDFLEJAC', 'Uniform item: Red Fleece Jacket', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1083, 2, 'Red Navy Blue Tracksuits.', 'REDNAVBLUTRA', 'Uniform item: Red Navy Blue Tracksuits.', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1084, 2, 'Red/Navy Tracksuit', 'REDNAVTRA', 'Uniform item: Red/Navy Tracksuit', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1085, 4, 'Red Normal T-Shirt', 'REDNORTSHI', 'Uniform item: Red Normal T-Shirt', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1086, 1, 'Red Plain Sweater', 'REDPLASWE', 'Uniform item: Red Plain Sweater', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1087, 2, 'Real Peak Tracksuits.', 'REAPEATRA', 'Uniform item: Real Peak Tracksuits.', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1088, 1, 'Red White Sweater', 'REDWHISWE', 'Uniform item: Red White Sweater', NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(1089, 2, 'Red White Tracksuit', 'RW-TR', 'Uniform item: Red White Tracksuit', NULL, '2026-11-06 19:04:31', '2026-11-07 23:48:48'),
(1090, 2, 'Red White Tracksuits.', 'REDWHITRA2', 'Uniform item: Red White Tracksuits.', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1091, 2, 'Red/Yellow Tracksuit', 'REDYELTRA', 'Uniform item: Red/Yellow Tracksuit', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1092, 1, 'St. Anold Jss Sweaters badge.', 'SAJS-BA', 'Uniform item: St. Anold Jss Sweaters badge.', NULL, '2026-11-06 19:04:31', '2026-11-07 23:51:29'),
(1093, 2, 'St. Anold Junior Tracksuit', 'STANOJUNTRA', 'Uniform item: St. Anold Junior Tracksuit', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1094, 1, 'St. Anold Sweaters badge.', 'STANOSWEBAD', 'Uniform item: St. Anold Sweaters badge.', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1095, 1, 'St. Anold Sleeveless Junior Sweaters', 'STANOSLEJUNSWE', 'Uniform item: St. Anold Sleeveless Junior Sweaters', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1096, 2, 'Shalom Ed. Centre Tracksuit', 'SEC-TR', 'Uniform item: Shalom Ed. Centre Tracksuit', NULL, '2026-11-06 19:04:31', '2026-11-07 23:49:04'),
(1097, 4, 'S/Green Normal T-Shirt', 'SGRENORTSHI', 'Uniform item: S/Green Normal T-Shirt', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1098, 2, 'S/Green Tracksuit', 'SGRETRA', 'Uniform item: S/Green Tracksuit', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1099, 2, 'Safaricom Green White Tracksuits.', 'SAFGREWHITRA', 'Uniform item: Safaricom Green White Tracksuits.', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1100, 1, 'Shalom Sweaters badge.', 'SHASWEBAD', 'Uniform item: Shalom Sweaters badge.', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1101, 1, 'St. Justino Sweaters badge.', 'STJUSSWEBAD', 'Uniform item: St. Justino Sweaters badge.', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1102, 1, 'St. Patricks Junior Sweaters badge.', 'STPATJUNSWEBAD', 'Uniform item: St. Patricks Junior Sweaters badge.', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1103, 2, 'St. Peter Junior Tracksuit', 'STPETJUNTRA', 'Uniform item: St. Peter Junior Tracksuit', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1104, 1, 'St. Patricks Sweaters badge.', 'STPATSWEBAD', 'Uniform item: St. Patricks Sweaters badge.', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1105, 1, 'St. Teresa Sweaters badge.', 'STTERSWEBAD', 'Uniform item: St. Teresa Sweaters badge.', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1106, 1, 'St. Peter junior sweaters badge.', 'SPJS-BA', 'Uniform item: St. Peter junior sweaters badge.', NULL, '2026-11-06 19:04:31', '2026-11-06 22:59:24'),
(1107, 2, 'Thawabu Boys Junior Tracksuit', 'THABOYJUNTRA', 'Uniform item: Thawabu Boys Junior Tracksuit', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1108, 2, 'Thawabu Boys Tracksuit', 'THABOYTRA', 'Uniform item: Thawabu Boys Tracksuit', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1109, 4, 'Thawabu Boys T-Shirt', 'THABOYTSHI', 'Uniform item: Thawabu Boys T-Shirt', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1110, 2, 'Thawabu Girls Junior Tracksuit', 'THAGIRJUNTRA', 'Uniform item: Thawabu Girls Junior Tracksuit', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1111, 2, 'Thawabu Girls Tracksuit', 'THAGIRTRA', 'Uniform item: Thawabu Girls Tracksuit', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1112, 4, 'Thawabu Girls T-Shirt', 'THAGIRTSHI', 'Uniform item: Thawabu Girls T-Shirt', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1113, 2, 'Thawabu Junior Girls Tracksuits.', 'THAJUNGIRTRA', 'Uniform item: Thawabu Junior Girls Tracksuits.', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1114, 1, 'Thawabu Junior Sweaters badge.', 'THAJUNSWEBAD', 'Uniform item: Thawabu Junior Sweaters badge.', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1115, 2, 'Thawabu Junior  Boys Tracksuits.', 'THAJUNBOYTRA', 'Uniform item: Thawabu Junior  Boys Tracksuits.', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1116, 1, 'Thawabu Primary Sweater', 'THAPRISWE', 'Uniform item: Thawabu Primary Sweater', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1117, 1, 'Tumaini Primary Sweaters badge.', 'TUMPRISWEBAD', 'Uniform item: Tumaini Primary Sweaters badge.', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1118, 2, 'Unity Centre Junior Tracksuits.', 'UNICENJUNTRA', 'Uniform item: Unity Centre Junior Tracksuits.', NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(1119, 1, 'Unity Centre Sweater', 'UC-SW', 'Uniform item: Unity Centre Sweater', NULL, '2026-11-06 19:04:32', '2026-11-06 22:56:00'),
(1120, 1, 'Unity Sweater', 'UNI-SW', 'Uniform item: Unity Sweater', NULL, '2026-11-06 19:04:32', '2026-11-07 23:48:25'),
(1121, 1, 'Unity Primary Sweaters badge.', 'UPS-BA', 'Uniform item: Unity Primary Sweaters badge.', NULL, '2026-11-06 19:04:32', '2026-11-06 22:56:09'),
(1122, 2, 'Unity Primary Tracksuits.', 'UP-TR', 'Uniform item: Unity Primary Tracksuits.', NULL, '2026-11-06 19:04:32', '2026-11-06 22:59:34'),
(1123, 2, 'Yellow Tracksuit', 'YEL-TR', 'Uniform item: Yellow Tracksuit', NULL, '2026-11-06 19:04:32', '2026-11-07 23:51:05'),
(1124, 4, 'Yellow Normal T-Shirt', 'YN-TS', 'Uniform item: Yellow Normal T-Shirt', NULL, '2026-11-06 19:04:32', '2026-11-07 23:48:36'),
(1125, 2, 'Yellow White Tracksuits.', 'YW-TR', 'Uniform item: Yellow White Tracksuits.', NULL, '2026-11-06 19:04:32', '2026-11-07 23:04:53');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `sku` varchar(100) NOT NULL,
  `barcode` varchar(100) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `colour` varchar(50) DEFAULT NULL,
  `unit_of_measure` varchar(30) DEFAULT 'Piece',
  `brand_name` varchar(100) DEFAULT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `stock_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `barcode`, `size`, `colour`, `unit_of_measure`, `brand_name`, `selling_price`, `stock_quantity`, `deleted_at`, `created_at`, `updated_at`) VALUES
(7695, 983, 'ACC-SL-22', 'BC-ACC-SL-22', '22', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7696, 983, 'ACC-SL-24', 'BC-ACC-SL-24', '24', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7697, 983, 'ACC-SL-26', 'BC-ACC-SL-26', '26', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7698, 983, 'ACC-SL-28', 'BC-ACC-SL-28', '28', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7699, 983, 'ACC-SL-30', 'BC-ACC-SL-30', '30', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7700, 983, 'ACC-SL-32', 'BC-ACC-SL-32', '32', 'Standard', 'Pieces', NULL, 750.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7701, 983, 'ACC-SL-34', 'BC-ACC-SL-34', '34', 'Standard', 'Pieces', NULL, 750.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7702, 983, 'ACC-SL-36', 'BC-ACC-SL-36', '36', 'Standard', 'Pieces', NULL, 850.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7703, 984, 'ACC-SW-22', 'BC-ACC-SW-22', '22', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7704, 984, 'ACC-SW-24', 'BC-ACC-SW-24', '24', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7705, 984, 'ACC-SW-26', 'BC-ACC-SW-26', '26', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7706, 984, 'ACC-SW-28', 'BC-ACC-SW-28', '28', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7707, 984, 'ACC-SW-30', 'BC-ACC-SW-30', '30', 'Standard', 'Pieces', NULL, 700.00, 5.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7708, 984, 'ACC-SW-32', 'BC-ACC-SW-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7709, 984, 'ACC-SW-34', 'BC-ACC-SW-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7710, 984, 'ACC-SW-36', 'BC-ACC-SW-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7711, 985, 'ACC-TR-22', 'BC-ACC-TR-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7712, 985, 'ACC-TR-24', 'BC-ACC-TR-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7713, 985, 'ACC-TR-26', 'BC-ACC-TR-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7714, 985, 'ACC-TR-28', 'BC-ACC-TR-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7715, 985, 'ACC-TR-30', 'BC-ACC-TR-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7716, 985, 'ACC-TR-32', 'BC-ACC-TR-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7717, 985, 'ACC-TR-34', 'BC-ACC-TR-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7718, 985, 'ACC-TR-36', 'BC-ACC-TR-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7719, 985, 'ACC-TR-38', 'BC-ACC-TR-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7720, 986, 'AJGT-32', 'BC-AJGT-32', '32', 'Standard', 'Pieces', NULL, 1200.00, 10.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7721, 986, 'AJGT-34', 'BC-AJGT-34', '34', 'Standard', 'Pieces', NULL, 1200.00, 7.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7722, 986, 'AJGT-36', 'BC-AJGT-36', '36', 'Standard', 'Pieces', NULL, 1200.00, 5.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7723, 987, 'AJS-32', 'BC-AJS-32', '32', 'Standard', 'Pieces', NULL, 800.00, 10.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7724, 987, 'AJS-34', 'BC-AJS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 10.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7725, 987, 'AJS-36', 'BC-AJS-36', '36', 'Standard', 'Pieces', NULL, 900.00, 9.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7726, 988, 'AJT-34', 'BC-AJT-34', '34', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7727, 988, 'AJT-36', 'BC-AJT-36', '36', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7728, 988, 'AJT-38', 'BC-AJT-38', '38', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7729, 989, 'AJT2-34', 'BC-AJT2-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7730, 989, 'AJT2-36', 'BC-AJT2-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7731, 990, 'ASS-22-S', 'BC-ASS-22-S', '22', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7732, 990, 'ASS-24-S', 'BC-ASS-24-S', '24', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7733, 990, 'ASS-26-S', 'BC-ASS-26-S', '26', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7734, 990, 'ASS-28-S', 'BC-ASS-28-S', '28', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7735, 990, 'ASS-30-S', 'BC-ASS-30-S', '30', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7736, 990, 'ASS-32-S', 'BC-ASS-32-S', '32', 'Standard', 'Pieces', NULL, 750.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7737, 990, 'ASS-34-S', 'BC-ASS-34-S', '34', 'Standard', 'Pieces', NULL, 750.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7738, 991, 'BCS-22', 'BC-BCS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7739, 991, 'BCS-24', 'BC-BCS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7740, 991, 'BCS-26', 'BC-BCS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7741, 991, 'BCS-28', 'BC-BCS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7742, 991, 'BCS-30', 'BC-BCS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7743, 991, 'BCS-32', 'BC-BCS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7744, 991, 'BCS-34', 'BC-BCS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7745, 991, 'BCS-36', 'BC-BCS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7746, 992, 'BCS2-22', 'BC-BCS2-22', '22', 'Standard', 'Pieces', NULL, 500.00, 9.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7747, 992, 'BCS2-24', 'BC-BCS2-24', '24', 'Standard', 'Pieces', NULL, 500.00, 9.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7748, 992, 'BCS2-26', 'BC-BCS2-26', '26', 'Standard', 'Pieces', NULL, 550.00, 10.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7749, 992, 'BCS2-28', 'BC-BCS2-28', '28', 'Standard', 'Pieces', NULL, 600.00, 10.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7750, 992, 'BCS2-30', 'BC-BCS2-30', '30', 'Standard', 'Pieces', NULL, 600.00, 3.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7751, 992, 'BCS2-32', 'BC-BCS2-32', '32', 'Standard', 'Pieces', NULL, 700.00, 13.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:24'),
(7752, 992, 'BCS2-34', 'BC-BCS2-34', '34', 'Standard', 'Pieces', NULL, 700.00, 14.00, NULL, '2026-11-06 19:04:24', '2026-11-06 19:04:25'),
(7753, 992, 'BCS2-36', 'BC-BCS2-36', '36', 'Standard', 'Pieces', NULL, 800.00, 9.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7754, 993, 'BJS2-32', 'BC-BJS2-32', '32', 'Standard', 'Pieces', NULL, 800.00, 2.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7755, 993, 'BJS2-34', 'BC-BJS2-34', '34', 'Standard', 'Pieces', NULL, 800.00, 7.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7756, 993, 'BJS2-36', 'BC-BJS2-36', '36', 'Standard', 'Pieces', NULL, 900.00, 5.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7757, 994, 'BJT2-32-V2', 'BC-BJT2-32-V2', '32', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7758, 994, 'BJT2-34-V2', 'BC-BJT2-34-V2', '34', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7759, 994, 'BJT2-36-V2', 'BC-BJT2-36-V2', '36', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7760, 994, 'BJT2-38', 'BC-BJT2-38', '38', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7761, 994, 'BJT2-40', 'BC-BJT2-40', '40', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7762, 994, 'BJT2-42', 'BC-BJT2-42', '42', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7763, 995, 'BON-SW-22', 'BC-BON-SW-22', '22', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7764, 995, 'BON-SW-24', 'BC-BON-SW-24', '24', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7765, 995, 'BON-SW-26', 'BC-BON-SW-26', '26', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7766, 995, 'BON-SW-28', 'BC-BON-SW-28', '28', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7767, 995, 'BON-SW-30', 'BC-BON-SW-30', '30', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7768, 995, 'BON-SW-32', 'BC-BON-SW-32', '32', 'Standard', 'Pieces', NULL, 800.00, 4.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7769, 995, 'BON-SW-34', 'BC-BON-SW-34', '34', 'Standard', 'Pieces', NULL, 800.00, 5.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7770, 995, 'BON-SW-36', 'BC-BON-SW-36', '36', 'Standard', 'Pieces', NULL, 900.00, 9.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7771, 996, 'BOO-SW2-22', 'BC-BOO-SW2-22', '22', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7772, 996, 'BOO-SW2-24', 'BC-BOO-SW2-24', '24', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7773, 996, 'BOO-SW2-26', 'BC-BOO-SW2-26', '26', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7774, 996, 'BOO-SW2-28', 'BC-BOO-SW2-28', '28', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7775, 996, 'BOO-SW2-30', 'BC-BOO-SW2-30', '30', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7776, 996, 'BOO-SW2-32', 'BC-BOO-SW2-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7777, 996, 'BOO-SW2-34', 'BC-BOO-SW2-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7778, 996, 'BOO-SW2-36', 'BC-BOO-SW2-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7779, 997, 'BP-22-S', 'BC-BP-22-S', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7780, 997, 'BP-24-S', 'BC-BP-24-S', '24', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7781, 997, 'BP-26-S', 'BC-BP-26-S', '26', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7782, 997, 'BP-28-S', 'BC-BP-28-S', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7783, 997, 'BP-30-S', 'BC-BP-30-S', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7784, 997, 'BP-32-S', 'BC-BP-32-S', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7785, 997, 'BP-34-S', 'BC-BP-34-S', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7786, 997, 'BP-36-S', 'BC-BP-36-S', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7787, 997, 'BP-38-S', 'BC-BP-38-S', '38', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7788, 997, 'BP-40-S', 'BC-BP-40-S', '40', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7789, 998, 'BPS-22', 'BC-BPS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7790, 998, 'BPS-24', 'BC-BPS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7791, 998, 'BPS-26', 'BC-BPS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7792, 998, 'BPS-28', 'BC-BPS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7793, 998, 'BPS-30', 'BC-BPS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7794, 998, 'BPS-32', 'BC-BPS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7795, 998, 'BPS-34', 'BC-BPS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7796, 998, 'BPS-36', 'BC-BPS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 1.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7797, 999, 'BRS-22', 'BC-BRS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7798, 999, 'BRS-24', 'BC-BRS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7799, 999, 'BRS-26', 'BC-BRS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7800, 999, 'BRS-28', 'BC-BRS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7801, 999, 'BRS-30', 'BC-BRS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7802, 999, 'BRS-32', 'BC-BRS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7803, 999, 'BRS-34', 'BC-BRS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7804, 999, 'BRS-36', 'BC-BRS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7805, 1000, 'BWS-22', 'BC-BWS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 5.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7806, 1000, 'BWS-24', 'BC-BWS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 4.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7807, 1000, 'BWS-26', 'BC-BWS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 3.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7808, 1000, 'BWS-28', 'BC-BWS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 15.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7809, 1000, 'BWS-30', 'BC-BWS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 10.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7810, 1000, 'BWS-32', 'BC-BWS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 13.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7811, 1000, 'BWS-34', 'BC-BWS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 9.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7812, 1000, 'BWS-36', 'BC-BWS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7813, 1001, 'CBBS-22', 'BC-CBBS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7814, 1001, 'CBBS-24', 'BC-CBBS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7815, 1001, 'CBBS-26', 'BC-CBBS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7816, 1001, 'CBBS-28', 'BC-CBBS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7817, 1001, 'CBBS-30', 'BC-CBBS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7818, 1001, 'CBBS-32', 'BC-CBBS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7819, 1001, 'CBBS-34', 'BC-CBBS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 6.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7820, 1001, 'CBBS-36', 'BC-CBBS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 6.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7821, 1002, 'CBFJ-22', 'BC-CBFJ-22', '22', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7822, 1002, 'CBFJ-24', 'BC-CBFJ-24', '24', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7823, 1002, 'CBFJ-26', 'BC-CBFJ-26', '26', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7824, 1002, 'CBFJ-28', 'BC-CBFJ-28', '28', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7825, 1002, 'CBFJ-30', 'BC-CBFJ-30', '30', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7826, 1002, 'CBFJ-32', 'BC-CBFJ-32', '32', 'Standard', 'Pieces', NULL, 1400.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7827, 1002, 'CBFJ-34', 'BC-CBFJ-34', '34', 'Standard', 'Pieces', NULL, 1400.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7828, 1002, 'CBFJ-36', 'BC-CBFJ-36', '36', 'Standard', 'Pieces', NULL, 1500.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7829, 1003, 'CBWTS-22', 'BC-CBWTS-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7830, 1003, 'CBWTS-24', 'BC-CBWTS-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7831, 1003, 'CBWTS-26', 'BC-CBWTS-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7832, 1003, 'CBWTS-28', 'BC-CBWTS-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7833, 1003, 'CBWTS-30', 'BC-CBWTS-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7834, 1003, 'CBWTS-32', 'BC-CBWTS-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7835, 1003, 'CBWTS-34', 'BC-CBWTS-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7836, 1003, 'CBWTS-36', 'BC-CBWTS-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7837, 1003, 'CBWTS-38', 'BC-CBWTS-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7838, 1003, 'CBWTS-40', 'BC-CBWTS-40', '40', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7839, 1003, 'CBWTS-42', 'BC-CBWTS-42', '42', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7840, 1004, 'CWTS-10YRS', 'BC-CWTS-10YRS', '10yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7841, 1004, 'CWTS-12YRS', 'BC-CWTS-12YRS', '12yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7842, 1004, 'CWTS-2YRS', 'BC-CWTS-2YRS', '2yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7843, 1004, 'CWTS-4YRS', 'BC-CWTS-4YRS', '4yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7844, 1004, 'CWTS-6YRS', 'BC-CWTS-6YRS', '6yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7845, 1004, 'CWTS-8YRS', 'BC-CWTS-8YRS', '8yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7846, 1005, 'DGPS-22', 'BC-DGPS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7847, 1005, 'DGPS-24', 'BC-DGPS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7848, 1005, 'DGPS-26', 'BC-DGPS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7849, 1005, 'DGPS-28', 'BC-DGPS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7850, 1005, 'DGPS-30', 'BC-DGPS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7851, 1005, 'DGPS-32', 'BC-DGPS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7852, 1005, 'DGPS-34', 'BC-DGPS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7853, 1005, 'DGPS-36', 'BC-DGPS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7854, 1006, 'DGWS-22', 'BC-DGWS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7855, 1006, 'DGWS-24', 'BC-DGWS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 1.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7856, 1006, 'DGWS-26', 'BC-DGWS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 3.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7857, 1006, 'DGWS-28', 'BC-DGWS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7858, 1006, 'DGWS-30', 'BC-DGWS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7859, 1006, 'DGWS-32', 'BC-DGWS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7860, 1006, 'DGWS-34', 'BC-DGWS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 12.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7861, 1006, 'DGWS-36', 'BC-DGWS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 13.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7862, 1007, 'DGWT-22', 'BC-DGWT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7863, 1007, 'DGWT-24', 'BC-DGWT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7864, 1007, 'DGWT-26', 'BC-DGWT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7865, 1007, 'DGWT-28', 'BC-DGWT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7866, 1007, 'DGWT-30', 'BC-DGWT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7867, 1007, 'DGWT-32', 'BC-DGWT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7868, 1007, 'DGWT-34', 'BC-DGWT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7869, 1007, 'DGWT-36', 'BC-DGWT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7870, 1007, 'DGWT-38', 'BC-DGWT-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7871, 1008, 'DJS-32', 'BC-DJS-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7872, 1008, 'DJS-34', 'BC-DJS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 10.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7873, 1008, 'DJS-36', 'BC-DJS-36', '36', 'Standard', 'Pieces', NULL, 900.00, 6.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7874, 1009, 'EJS-32', 'BC-EJS-32', '32', 'Standard', 'Pieces', NULL, 800.00, 6.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7875, 1009, 'EJS-34', 'BC-EJS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7876, 1009, 'EJS-36', 'BC-EJS-36', '36', 'Standard', 'Pieces', NULL, 900.00, 3.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7877, 1010, 'EJS2-32', 'BC-EJS2-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7878, 1010, 'EJS2-34', 'BC-EJS2-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7879, 1010, 'EJS2-36', 'BC-EJS2-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7880, 1011, 'EPS-22', 'BC-EPS-22', '22', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7881, 1011, 'EPS-24', 'BC-EPS-24', '24', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7882, 1011, 'EPS-26', 'BC-EPS-26', '26', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7883, 1011, 'EPS-28', 'BC-EPS-28', '28', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7884, 1011, 'EPS-30', 'BC-EPS-30', '30', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7885, 1011, 'EPS-32', 'BC-EPS-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7886, 1011, 'EPS-34', 'BC-EPS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7887, 1011, 'EPS-36', 'BC-EPS-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7888, 1012, 'GBWS-22', 'BC-GBWS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 17.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7889, 1012, 'GBWS-24', 'BC-GBWS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 11.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7890, 1012, 'GBWS-26', 'BC-GBWS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 12.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7891, 1012, 'GBWS-28', 'BC-GBWS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 10.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7892, 1012, 'GBWS-30', 'BC-GBWS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 8.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:25'),
(7893, 1012, 'GBWS-32', 'BC-GBWS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 10.00, NULL, '2026-11-06 19:04:25', '2026-11-06 19:04:26'),
(7894, 1012, 'GBWS-34', 'BC-GBWS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 9.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7895, 1012, 'GBWS-36', 'BC-GBWS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 10.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7896, 1012, 'GBWS-38', 'BC-GBWS-38', '38', 'Standard', 'Pieces', NULL, 800.00, 10.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7897, 1013, 'GCS-22', 'BC-GCS-22', '22', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7898, 1013, 'GCS-24', 'BC-GCS-24', '24', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7899, 1013, 'GCS-26', 'BC-GCS-26', '26', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7900, 1013, 'GCS-28', 'BC-GCS-28', '28', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7901, 1013, 'GCS-30', 'BC-GCS-30', '30', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7902, 1013, 'GCS-32', 'BC-GCS-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7903, 1013, 'GCS-34', 'BC-GCS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7904, 1013, 'GCS-36', 'BC-GCS-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7905, 1014, 'GFJ-22', 'BC-GFJ-22', '22', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7906, 1014, 'GFJ-24', 'BC-GFJ-24', '24', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7907, 1014, 'GFJ-26', 'BC-GFJ-26', '26', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7908, 1014, 'GFJ-28', 'BC-GFJ-28', '28', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7909, 1014, 'GFJ-30', 'BC-GFJ-30', '30', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7910, 1014, 'GFJ-32', 'BC-GFJ-32', '32', 'Standard', 'Pieces', NULL, 1400.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7911, 1014, 'GFJ-34', 'BC-GFJ-34', '34', 'Standard', 'Pieces', NULL, 1400.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7912, 1014, 'GFJ-36', 'BC-GFJ-36', '36', 'Standard', 'Pieces', NULL, 1500.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7913, 1015, 'GMWS-22', 'BC-GMWS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 5.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7914, 1015, 'GMWS-24', 'BC-GMWS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 6.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7915, 1015, 'GMWS-26', 'BC-GMWS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 7.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7916, 1015, 'GMWS-28', 'BC-GMWS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 6.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7917, 1015, 'GMWS-30', 'BC-GMWS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 6.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7918, 1015, 'GMWS-32', 'BC-GMWS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 6.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7919, 1015, 'GMWS-34', 'BC-GMWS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 6.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7920, 1015, 'GMWS-36', 'BC-GMWS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 3.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7921, 1015, 'GMWS-38', 'BC-GMWS-38', '38', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7922, 1016, 'GPS-22', 'BC-GPS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 15.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7923, 1016, 'GPS-24', 'BC-GPS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 6.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7924, 1016, 'GPS-26', 'BC-GPS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 7.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7925, 1016, 'GPS-28', 'BC-GPS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 8.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7926, 1016, 'GPS-30', 'BC-GPS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 8.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7927, 1016, 'GPS-32', 'BC-GPS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7928, 1016, 'GPS-34', 'BC-GPS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7929, 1016, 'GPS-36', 'BC-GPS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7930, 1017, 'GPS2-22', 'BC-GPS2-22', '22', 'Standard', 'Pieces', NULL, 500.00, 4.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7931, 1017, 'GPS2-24', 'BC-GPS2-24', '24', 'Standard', 'Pieces', NULL, 500.00, 10.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7932, 1017, 'GPS2-26', 'BC-GPS2-26', '26', 'Standard', 'Pieces', NULL, 550.00, 5.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7933, 1017, 'GPS2-28', 'BC-GPS2-28', '28', 'Standard', 'Pieces', NULL, 600.00, 4.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7934, 1017, 'GPS2-30', 'BC-GPS2-30', '30', 'Standard', 'Pieces', NULL, 600.00, 1.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7935, 1017, 'GPS2-32', 'BC-GPS2-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7936, 1017, 'GPS2-34', 'BC-GPS2-34', '34', 'Standard', 'Pieces', NULL, 700.00, 6.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7937, 1017, 'GPS2-36', 'BC-GPS2-36', '36', 'Standard', 'Pieces', NULL, 800.00, 12.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7938, 1018, 'GRS-22', 'BC-GRS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7939, 1018, 'GRS-24', 'BC-GRS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7940, 1018, 'GRS-26', 'BC-GRS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7941, 1018, 'GRS-28', 'BC-GRS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7942, 1018, 'GRS-30', 'BC-GRS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7943, 1018, 'GRS-32', 'BC-GRS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7944, 1018, 'GRS-34', 'BC-GRS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7945, 1018, 'GRS-36', 'BC-GRS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7946, 1019, 'GRW-22-S', 'BC-GRW-22-S', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7947, 1019, 'GRW-24-S', 'BC-GRW-24-S', '24', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7948, 1019, 'GRW-26-S', 'BC-GRW-26-S', '26', 'Standard', 'Pieces', NULL, 550.00, 8.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7949, 1019, 'GRW-28-S', 'BC-GRW-28-S', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7950, 1019, 'GRW-30-S', 'BC-GRW-30-S', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7951, 1019, 'GRW-32-S', 'BC-GRW-32-S', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7952, 1019, 'GRW-34-S', 'BC-GRW-34-S', '34', 'Standard', 'Pieces', NULL, 700.00, 2.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7953, 1019, 'GRW-36-S', 'BC-GRW-36-S', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7954, 1019, 'GRW-38-S', 'BC-GRW-38-S', '38', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7955, 1019, 'GRW-40-S', 'BC-GRW-40-S', '40', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7956, 1020, 'GWTS-22', 'BC-GWTS-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7957, 1020, 'GWTS-24', 'BC-GWTS-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7958, 1020, 'GWTS-26', 'BC-GWTS-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7959, 1020, 'GWTS-28', 'BC-GWTS-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7960, 1020, 'GWTS-30', 'BC-GWTS-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7961, 1020, 'GWTS-32', 'BC-GWTS-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7962, 1020, 'GWTS-34', 'BC-GWTS-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7963, 1020, 'GWTS-36', 'BC-GWTS-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7964, 1020, 'GWTS-38', 'BC-GWTS-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7965, 1020, 'GWTS-40', 'BC-GWTS-40', '40', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7966, 1020, 'GWTS-42', 'BC-GWTS-42', '42', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7967, 1021, 'GYT-22', 'BC-GYT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7968, 1021, 'GYT-24', 'BC-GYT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7969, 1021, 'GYT-26', 'BC-GYT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7970, 1021, 'GYT-28', 'BC-GYT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7971, 1021, 'GYT-30', 'BC-GYT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7972, 1021, 'GYT-32', 'BC-GYT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7973, 1021, 'GYT-34', 'BC-GYT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7974, 1021, 'GYT-36', 'BC-GYT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7975, 1022, 'HBT-22', 'BC-HBT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7976, 1022, 'HBT-24', 'BC-HBT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7977, 1022, 'HBT-26', 'BC-HBT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7978, 1022, 'HBT-28', 'BC-HBT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7979, 1022, 'HBT-30', 'BC-HBT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7980, 1022, 'HBT-32', 'BC-HBT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7981, 1022, 'HBT-34', 'BC-HBT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7982, 1022, 'HBT-36', 'BC-HBT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7983, 1023, 'HEK-SW-32', 'BC-HEK-SW-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7984, 1023, 'HEK-SW-34', 'BC-HEK-SW-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7985, 1023, 'HEK-SW-36', 'BC-HEK-SW-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7986, 1024, 'HEK-SW2-22', 'BC-HEK-SW2-22', '22', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7987, 1024, 'HEK-SW2-24', 'BC-HEK-SW2-24', '24', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7988, 1024, 'HEK-SW2-26', 'BC-HEK-SW2-26', '26', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7989, 1024, 'HEK-SW2-28', 'BC-HEK-SW2-28', '28', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7990, 1024, 'HEK-SW2-30', 'BC-HEK-SW2-30', '30', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7991, 1024, 'HEK-SW2-32', 'BC-HEK-SW2-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7992, 1024, 'HEK-SW2-34', 'BC-HEK-SW2-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7993, 1024, 'HEK-SW2-36', 'BC-HEK-SW2-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7994, 1025, 'HGT-22', 'BC-HGT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7995, 1025, 'HGT-24', 'BC-HGT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7996, 1025, 'HGT-26', 'BC-HGT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7997, 1025, 'HGT-28', 'BC-HGT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7998, 1025, 'HGT-30', 'BC-HGT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(7999, 1025, 'HGT-32', 'BC-HGT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8000, 1025, 'HGT-34', 'BC-HGT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8001, 1025, 'HGT-36', 'BC-HGT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8002, 1026, 'HJS-30-NBP', 'BC-HJS-30-NBP', '30', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8003, 1026, 'HJS-32-NBP', 'BC-HJS-32-NBP', '32', 'Standard', 'Pieces', NULL, 800.00, 4.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8004, 1026, 'HJS-34-NBP', 'BC-HJS-34-NBP', '34', 'Standard', 'Pieces', NULL, 800.00, 3.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8005, 1026, 'HJS-36-NBP', 'BC-HJS-36-NBP', '36', 'Standard', 'Pieces', NULL, 900.00, 3.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8006, 1027, 'HJT-34', 'BC-HJT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8007, 1027, 'HJT-36', 'BC-HJT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8008, 1027, 'HJT-38', 'BC-HJT-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8009, 1028, 'HJTS-34', 'BC-HJTS-34', '34', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8010, 1028, 'HJTS-36', 'BC-HJTS-36', '36', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8011, 1028, 'HJTS-38', 'BC-HJTS-38', '38', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8012, 1028, 'HJTS-40', 'BC-HJTS-40', '40', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8013, 1028, 'HJTS-42', 'BC-HJTS-42', '42', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8014, 1029, 'HOR-SW-22', 'BC-HOR-SW-22', '22', 'Standard', 'Pieces', NULL, 600.00, 4.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8015, 1029, 'HOR-SW-24', 'BC-HOR-SW-24', '24', 'Standard', 'Pieces', NULL, 600.00, 2.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8016, 1029, 'HOR-SW-26', 'BC-HOR-SW-26', '26', 'Standard', 'Pieces', NULL, 650.00, 3.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8017, 1029, 'HOR-SW-28', 'BC-HOR-SW-28', '28', 'Standard', 'Pieces', NULL, 700.00, 3.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8018, 1029, 'HOR-SW-30', 'BC-HOR-SW-30', '30', 'Standard', 'Pieces', NULL, 700.00, 2.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8019, 1029, 'HOR-SW-32', 'BC-HOR-SW-32', '32', 'Standard', 'Pieces', NULL, 800.00, 3.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8020, 1029, 'HOR-SW-34', 'BC-HOR-SW-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8021, 1029, 'HOR-SW-36', 'BC-HOR-SW-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8022, 1030, 'IJS-32', 'BC-IJS-32', '32', 'Standard', 'Pieces', NULL, 800.00, 8.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8023, 1030, 'IJS-34', 'BC-IJS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 6.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8024, 1030, 'IJS-36', 'BC-IJS-36', '36', 'Standard', 'Pieces', NULL, 900.00, 17.00, NULL, '2026-11-06 19:04:26', '2026-11-06 19:04:26'),
(8025, 1031, 'IJS2-30', 'BC-IJS2-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8026, 1031, 'IJS2-32', 'BC-IJS2-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8027, 1031, 'IJS2-34', 'BC-IJS2-34', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8028, 1031, 'IJS2-36', 'BC-IJS2-36', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8029, 1032, 'IMA-SW-22', 'BC-IMA-SW-22', '22', 'Standard', 'Pieces', NULL, 600.00, 10.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8030, 1032, 'IMA-SW-24', 'BC-IMA-SW-24', '24', 'Standard', 'Pieces', NULL, 600.00, 2.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8031, 1032, 'IMA-SW-26', 'BC-IMA-SW-26', '26', 'Standard', 'Pieces', NULL, 650.00, 2.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8032, 1032, 'IMA-SW-28', 'BC-IMA-SW-28', '28', 'Standard', 'Pieces', NULL, 700.00, 8.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8033, 1032, 'IMA-SW-30', 'BC-IMA-SW-30', '30', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8034, 1032, 'IMA-SW-32', 'BC-IMA-SW-32', '32', 'Standard', 'Pieces', NULL, 800.00, 11.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8035, 1032, 'IMA-SW-34', 'BC-IMA-SW-34', '34', 'Standard', 'Pieces', NULL, 800.00, 7.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8036, 1032, 'IMA-SW-36', 'BC-IMA-SW-36', '36', 'Standard', 'Pieces', NULL, 900.00, 15.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8037, 1033, 'KBT-22', 'BC-KBT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8038, 1033, 'KBT-24', 'BC-KBT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8039, 1033, 'KBT-26', 'BC-KBT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8040, 1033, 'KBT-28', 'BC-KBT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8041, 1033, 'KBT-30', 'BC-KBT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8042, 1033, 'KBT-32', 'BC-KBT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8043, 1033, 'KBT-34', 'BC-KBT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8044, 1033, 'KBT-36', 'BC-KBT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8045, 1033, 'KBT-38', 'BC-KBT-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8046, 1034, 'KBTS-22-M', 'BC-KBTS-22-M', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8047, 1034, 'KBTS-24-M', 'BC-KBTS-24-M', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8048, 1034, 'KBTS-26-M', 'BC-KBTS-26-M', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8049, 1034, 'KBTS-28-M', 'BC-KBTS-28-M', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8050, 1034, 'KBTS-30-M', 'BC-KBTS-30-M', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8051, 1034, 'KBTS-32-M', 'BC-KBTS-32-M', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8052, 1034, 'KBTS-34-M', 'BC-KBTS-34-M', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8053, 1034, 'KBTS-36-M', 'BC-KBTS-36-M', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8054, 1034, 'KBTS-38-M', 'BC-KBTS-38-M', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8055, 1034, 'KBTS-40-M', 'BC-KBTS-40-M', '40', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `barcode`, `size`, `colour`, `unit_of_measure`, `brand_name`, `selling_price`, `stock_quantity`, `deleted_at`, `created_at`, `updated_at`) VALUES
(8056, 1034, 'KBTS-42-M', 'BC-KBTS-42-M', '42', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8057, 1035, 'KGT-22', 'BC-KGT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8058, 1035, 'KGT-24', 'BC-KGT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8059, 1035, 'KGT-26', 'BC-KGT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8060, 1035, 'KGT-28', 'BC-KGT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8061, 1035, 'KGT-30', 'BC-KGT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8062, 1035, 'KGT-32', 'BC-KGT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8063, 1035, 'KGT-34', 'BC-KGT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8064, 1035, 'KGT-36', 'BC-KGT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8065, 1036, 'KGTS-22', 'BC-KGTS-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8066, 1036, 'KGTS-24', 'BC-KGTS-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8067, 1036, 'KGTS-26', 'BC-KGTS-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8068, 1036, 'KGTS-28', 'BC-KGTS-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8069, 1036, 'KGTS-30', 'BC-KGTS-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8070, 1036, 'KGTS-32', 'BC-KGTS-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8071, 1036, 'KGTS-34', 'BC-KGTS-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8072, 1036, 'KGTS-36', 'BC-KGTS-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8073, 1036, 'KGTS-38', 'BC-KGTS-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8074, 1036, 'KGTS-40', 'BC-KGTS-40', '40', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8075, 1036, 'KGTS-42', 'BC-KGTS-42', '42', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8076, 1037, 'KJS-32', 'BC-KJS-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8077, 1037, 'KJS-34', 'BC-KJS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8078, 1037, 'KJS-36', 'BC-KJS-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8079, 1038, 'KJS2-32', 'BC-KJS2-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8080, 1038, 'KJS2-34', 'BC-KJS2-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8081, 1038, 'KJS2-36', 'BC-KJS2-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8082, 1039, 'KOM-SW-22', 'BC-KOM-SW-22', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8083, 1039, 'KOM-SW-24', 'BC-KOM-SW-24', '24', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8084, 1039, 'KOM-SW-26', 'BC-KOM-SW-26', '26', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8085, 1039, 'KOM-SW-28', 'BC-KOM-SW-28', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8086, 1039, 'KOM-SW-30', 'BC-KOM-SW-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8087, 1039, 'KOM-SW-32', 'BC-KOM-SW-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8088, 1039, 'KOM-SW-34', 'BC-KOM-SW-34', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8089, 1039, 'KOM-SW-36', 'BC-KOM-SW-36', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8090, 1040, 'KPS-22', 'BC-KPS-22', '22', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8091, 1040, 'KPS-24', 'BC-KPS-24', '24', 'Standard', 'Pieces', NULL, 600.00, 4.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8092, 1040, 'KPS-26', 'BC-KPS-26', '26', 'Standard', 'Pieces', NULL, 650.00, 4.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8093, 1040, 'KPS-28', 'BC-KPS-28', '28', 'Standard', 'Pieces', NULL, 700.00, 4.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8094, 1040, 'KPS-30', 'BC-KPS-30', '30', 'Standard', 'Pieces', NULL, 700.00, 3.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8095, 1040, 'KPS-32', 'BC-KPS-32', '32', 'Standard', 'Pieces', NULL, 800.00, 4.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8096, 1040, 'KPS-34', 'BC-KPS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8097, 1040, 'KPS-36', 'BC-KPS-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8098, 1041, 'KSJS2-32', 'BC-KSJS2-32', '32', 'Standard', 'Pieces', NULL, 800.00, 4.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8099, 1041, 'KSJS2-34', 'BC-KSJS2-34', '34', 'Standard', 'Pieces', NULL, 800.00, 6.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8100, 1041, 'KSJS2-36', 'BC-KSJS2-36', '36', 'Standard', 'Pieces', NULL, 900.00, 4.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8101, 1042, 'KSJT-34-V2', 'BC-KSJT-34-V2', '34', 'Standard', 'Pieces', NULL, 1800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8102, 1042, 'KSJT-36-V2', 'BC-KSJT-36-V2', '36', 'Standard', 'Pieces', NULL, 1800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8103, 1042, 'KSJT-38', 'BC-KSJT-38', '38', 'Standard', 'Pieces', NULL, 1800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8104, 1042, 'KSJT-40', 'BC-KSJT-40', '40', 'Standard', 'Pieces', NULL, 1900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8105, 1042, 'KSJT-42', 'BC-KSJT-42', '42', 'Standard', 'Pieces', NULL, 1900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8106, 1043, 'KSS2-22', 'BC-KSS2-22', '22', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8107, 1043, 'KSS2-24', 'BC-KSS2-24', '24', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8108, 1043, 'KSS2-26', 'BC-KSS2-26', '26', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8109, 1043, 'KSS2-28', 'BC-KSS2-28', '28', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8110, 1043, 'KSS2-30', 'BC-KSS2-30', '30', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8111, 1043, 'KSS2-32', 'BC-KSS2-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8112, 1043, 'KSS2-34', 'BC-KSS2-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8113, 1043, 'KSS2-36', 'BC-KSS2-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8114, 1044, 'KST-22', 'BC-KST-22', '22', 'Standard', 'Pieces', NULL, 1500.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8115, 1044, 'KST-24', 'BC-KST-24', '24', 'Standard', 'Pieces', NULL, 1500.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8116, 1044, 'KST-26', 'BC-KST-26', '26', 'Standard', 'Pieces', NULL, 1500.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8117, 1044, 'KST-28', 'BC-KST-28', '28', 'Standard', 'Pieces', NULL, 1600.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8118, 1044, 'KST-30', 'BC-KST-30', '30', 'Standard', 'Pieces', NULL, 1600.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8119, 1044, 'KST-32', 'BC-KST-32', '32', 'Standard', 'Pieces', NULL, 1700.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8120, 1044, 'KST-34', 'BC-KST-34', '34', 'Standard', 'Pieces', NULL, 1700.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8121, 1044, 'KST-36', 'BC-KST-36', '36', 'Standard', 'Pieces', NULL, 1800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8122, 1044, 'KST-38', 'BC-KST-38', '38', 'Standard', 'Pieces', NULL, 1800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8123, 1045, 'LAK-SW-22', 'BC-LAK-SW-22', '22', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8124, 1045, 'LAK-SW-24', 'BC-LAK-SW-24', '24', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8125, 1045, 'LAK-SW-26', 'BC-LAK-SW-26', '26', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8126, 1045, 'LAK-SW-28', 'BC-LAK-SW-28', '28', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8127, 1045, 'LAK-SW-30', 'BC-LAK-SW-30', '30', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8128, 1045, 'LAK-SW-32', 'BC-LAK-SW-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8129, 1045, 'LAK-SW-34', 'BC-LAK-SW-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8130, 1045, 'LAK-SW-36', 'BC-LAK-SW-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8131, 1046, 'LBNT-22', 'BC-LBNT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8132, 1046, 'LBNT-24', 'BC-LBNT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8133, 1046, 'LBNT-26', 'BC-LBNT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8134, 1046, 'LBNT-28', 'BC-LBNT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8135, 1046, 'LBNT-30', 'BC-LBNT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8136, 1046, 'LBNT-32', 'BC-LBNT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8137, 1046, 'LBNT-34', 'BC-LBNT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8138, 1046, 'LBNT-36', 'BC-LBNT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8139, 1046, 'LBNT-38', 'BC-LBNT-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8140, 1047, 'LBRT-22', 'BC-LBRT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8141, 1047, 'LBRT-24', 'BC-LBRT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8142, 1047, 'LBRT-26', 'BC-LBRT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8143, 1047, 'LBRT-28', 'BC-LBRT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8144, 1047, 'LBRT-30', 'BC-LBRT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8145, 1047, 'LBRT-32', 'BC-LBRT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8146, 1047, 'LBRT-34', 'BC-LBRT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8147, 1047, 'LBRT-36', 'BC-LBRT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8148, 1048, 'LBS-22', 'BC-LBS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8149, 1048, 'LBS-24', 'BC-LBS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8150, 1048, 'LBS-26', 'BC-LBS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8151, 1048, 'LBS-28', 'BC-LBS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8152, 1048, 'LBS-30', 'BC-LBS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8153, 1048, 'LBS-32', 'BC-LBS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8154, 1048, 'LBS-34', 'BC-LBS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8155, 1048, 'LBS-36', 'BC-LBS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8156, 1049, 'LBWT-22', 'BC-LBWT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8157, 1049, 'LBWT-24', 'BC-LBWT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8158, 1049, 'LBWT-26', 'BC-LBWT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8159, 1049, 'LBWT-28', 'BC-LBWT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8160, 1049, 'LBWT-30', 'BC-LBWT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8161, 1049, 'LBWT-32', 'BC-LBWT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8162, 1049, 'LBWT-34', 'BC-LBWT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8163, 1049, 'LBWT-36', 'BC-LBWT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8164, 1050, 'LRTS-60', 'BC-LRTS-60', '60', 'Standard', 'Pieces', NULL, 400.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8165, 1050, 'LRTS-65', 'BC-LRTS-65', '65', 'Standard', 'Pieces', NULL, 400.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8166, 1050, 'LRTS-70', 'BC-LRTS-70', '70', 'Standard', 'Pieces', NULL, 400.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8167, 1050, 'LRTS-75', 'BC-LRTS-75', '75', 'Standard', 'Pieces', NULL, 400.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8168, 1050, 'LRTS-80', 'BC-LRTS-80', '80', 'Standard', 'Pieces', NULL, 400.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8169, 1050, 'LRTS-85', 'BC-LRTS-85', '85', 'Standard', 'Pieces', NULL, 400.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8170, 1050, 'LRTS-90', 'BC-LRTS-90', '90', 'Standard', 'Pieces', NULL, 400.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8171, 1051, 'LSS-22', 'BC-LSS-22', '22', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8172, 1051, 'LSS-24', 'BC-LSS-24', '24', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8173, 1051, 'LSS-26', 'BC-LSS-26', '26', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8174, 1051, 'LSS-28', 'BC-LSS-28', '28', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8175, 1051, 'LSS-30', 'BC-LSS-30', '30', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8176, 1051, 'LSS-32', 'BC-LSS-32', '32', 'Standard', 'Pieces', NULL, 750.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8177, 1051, 'LSS-34', 'BC-LSS-34', '34', 'Standard', 'Pieces', NULL, 750.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8178, 1052, 'LWTS-60', 'BC-LWTS-60', '60', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8179, 1052, 'LWTS-65', 'BC-LWTS-65', '65', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8180, 1052, 'LWTS-70', 'BC-LWTS-70', '70', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8181, 1052, 'LWTS-75', 'BC-LWTS-75', '75', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8182, 1052, 'LWTS-80', 'BC-LWTS-80', '80', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8183, 1052, 'LWTS-85', 'BC-LWTS-85', '85', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8184, 1052, 'LWTS-90', 'BC-LWTS-90', '90', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8185, 1053, 'MJS-32', 'BC-MJS-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8186, 1053, 'MJS-34', 'BC-MJS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8187, 1053, 'MJS-36', 'BC-MJS-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8188, 1054, 'MNTS-10YRS', 'BC-MNTS-10YRS', '10yrs', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8189, 1054, 'MNTS-12YRS', 'BC-MNTS-12YRS', '12yrs', 'Standard', 'Pieces', NULL, 400.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8190, 1054, 'MNTS-2YRS', 'BC-MNTS-2YRS', '2yrs', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8191, 1054, 'MNTS-4YRS', 'BC-MNTS-4YRS', '4yrs', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8192, 1054, 'MNTS-6YRS', 'BC-MNTS-6YRS', '6yrs', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8193, 1054, 'MNTS-8YRS', 'BC-MNTS-8YRS', '8yrs', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8194, 1055, 'MPS-22', 'BC-MPS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 9.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8195, 1055, 'MPS-24', 'BC-MPS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 6.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8196, 1055, 'MPS-26', 'BC-MPS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 16.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8197, 1055, 'MPS-28', 'BC-MPS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 8.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8198, 1055, 'MPS-30', 'BC-MPS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8199, 1055, 'MPS-32', 'BC-MPS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8200, 1055, 'MPS-34', 'BC-MPS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8201, 1055, 'MPS-36', 'BC-MPS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 1.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8202, 1056, 'MPS2-22', 'BC-MPS2-22', '22', 'Standard', 'Pieces', NULL, 500.00, 9.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8203, 1056, 'MPS2-24', 'BC-MPS2-24', '24', 'Standard', 'Pieces', NULL, 500.00, 6.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8204, 1056, 'MPS2-26', 'BC-MPS2-26', '26', 'Standard', 'Pieces', NULL, 550.00, 16.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:27'),
(8205, 1056, 'MPS2-28', 'BC-MPS2-28', '28', 'Standard', 'Pieces', NULL, 600.00, 8.00, NULL, '2026-11-06 19:04:27', '2026-11-06 19:04:28'),
(8206, 1056, 'MPS2-30', 'BC-MPS2-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8207, 1056, 'MPS2-32', 'BC-MPS2-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8208, 1056, 'MPS2-34', 'BC-MPS2-34', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8209, 1056, 'MPS2-36', 'BC-MPS2-36', '36', 'Standard', 'Pieces', NULL, 800.00, 1.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8210, 1056, 'MPS2-38', 'BC-MPS2-38', '38', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8211, 1057, 'MSS-34', 'BC-MSS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 11.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8212, 1057, 'MSS-36', 'BC-MSS-36', '36', 'Standard', 'Pieces', NULL, 900.00, 14.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8213, 1057, 'MSS-38', 'BC-MSS-38', '38', 'Standard', 'Pieces', NULL, 900.00, 11.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8214, 1058, 'MSSS-34', 'BC-MSSS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 6.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8215, 1058, 'MSSS-36', 'BC-MSSS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 6.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8216, 1058, 'MSSS-38', 'BC-MSSS-38', '38', 'Standard', 'Pieces', NULL, 800.00, 6.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8217, 1059, 'MWA-SW-32', 'BC-MWA-SW-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8218, 1059, 'MWA-SW-34', 'BC-MWA-SW-34', '34', 'Standard', 'Pieces', NULL, 800.00, 8.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8219, 1059, 'MWA-SW-36', 'BC-MWA-SW-36', '36', 'Standard', 'Pieces', NULL, 900.00, 4.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8220, 1060, 'MWS-22', 'BC-MWS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8221, 1060, 'MWS-24', 'BC-MWS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 4.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8222, 1060, 'MWS-26', 'BC-MWS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 1.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8223, 1060, 'MWS-28', 'BC-MWS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 3.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8224, 1060, 'MWS-30', 'BC-MWS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 3.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8225, 1060, 'MWS-32', 'BC-MWS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 10.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8226, 1060, 'MWS-34', 'BC-MWS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 7.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8227, 1060, 'MWS-36', 'BC-MWS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 10.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8228, 1061, 'MWT-22', 'BC-MWT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8229, 1061, 'MWT-24', 'BC-MWT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:28', '2026-11-06 19:04:28'),
(8230, 1061, 'MWT-26', 'BC-MWT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8231, 1061, 'MWT-28', 'BC-MWT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8232, 1061, 'MWT-30', 'BC-MWT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8233, 1061, 'MWT-32', 'BC-MWT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8234, 1061, 'MWT-34', 'BC-MWT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8235, 1061, 'MWT-36', 'BC-MWT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8236, 1061, 'MWT-38', 'BC-MWT-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8237, 1062, 'MWTS-22', 'BC-MWTS-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8238, 1062, 'MWTS-24', 'BC-MWTS-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8239, 1062, 'MWTS-26', 'BC-MWTS-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8240, 1062, 'MWTS-28', 'BC-MWTS-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8241, 1062, 'MWTS-30', 'BC-MWTS-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8242, 1062, 'MWTS-32', 'BC-MWTS-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8243, 1062, 'MWTS-34', 'BC-MWTS-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8244, 1062, 'MWTS-36', 'BC-MWTS-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8245, 1062, 'MWTS-38', 'BC-MWTS-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8246, 1062, 'MWTS-40', 'BC-MWTS-40', '40', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8247, 1062, 'MWTS-42', 'BC-MWTS-42', '42', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8248, 1062, 'MWTS-44', 'BC-MWTS-44', '44', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8249, 1063, 'MYT-22', 'BC-MYT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8250, 1063, 'MYT-24', 'BC-MYT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8251, 1063, 'MYT-26', 'BC-MYT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8252, 1063, 'MYT-28', 'BC-MYT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8253, 1063, 'MYT-30', 'BC-MYT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8254, 1063, 'MYT-32', 'BC-MYT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8255, 1063, 'MYT-34', 'BC-MYT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8256, 1063, 'MYT-36', 'BC-MYT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8257, 1064, 'MYTS-22', 'BC-MYTS-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8258, 1064, 'MYTS-24', 'BC-MYTS-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8259, 1064, 'MYTS-26', 'BC-MYTS-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8260, 1064, 'MYTS-28', 'BC-MYTS-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8261, 1064, 'MYTS-30', 'BC-MYTS-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8262, 1064, 'MYTS-32', 'BC-MYTS-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8263, 1064, 'MYTS-34', 'BC-MYTS-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8264, 1064, 'MYTS-36', 'BC-MYTS-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8265, 1064, 'MYTS-38', 'BC-MYTS-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8266, 1064, 'MYTS-40', 'BC-MYTS-40', '40', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8267, 1064, 'MYTS-42', 'BC-MYTS-42', '42', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8268, 1065, 'NBFJ-22', 'BC-NBFJ-22', '22', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8269, 1065, 'NBFJ-24', 'BC-NBFJ-24', '24', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8270, 1065, 'NBFJ-26', 'BC-NBFJ-26', '26', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8271, 1065, 'NBFJ-28', 'BC-NBFJ-28', '28', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8272, 1065, 'NBFJ-30', 'BC-NBFJ-30', '30', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8273, 1065, 'NBFJ-32', 'BC-NBFJ-32', '32', 'Standard', 'Pieces', NULL, 1400.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8274, 1065, 'NBFJ-34', 'BC-NBFJ-34', '34', 'Standard', 'Pieces', NULL, 1400.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8275, 1065, 'NBFJ-36', 'BC-NBFJ-36', '36', 'Standard', 'Pieces', NULL, 1500.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8276, 1066, 'NBP-22-S', 'BC-NBP-22-S', '22', 'Standard', 'Pieces', NULL, 500.00, 1.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8277, 1066, 'NBP-24-S', 'BC-NBP-24-S', '24', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8278, 1066, 'NBP-26-S', 'BC-NBP-26-S', '26', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8279, 1066, 'NBP-28-S', 'BC-NBP-28-S', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8280, 1066, 'NBP-30-S', 'BC-NBP-30-S', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8281, 1066, 'NBP-32-S', 'BC-NBP-32-S', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8282, 1066, 'NBP-34-S', 'BC-NBP-34-S', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8283, 1066, 'NBP-36-S', 'BC-NBP-36-S', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8284, 1066, 'NBP-38-S', 'BC-NBP-38-S', '38', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8285, 1067, 'NBPS-22', 'BC-NBPS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 6.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:29'),
(8286, 1067, 'NBPS-24', 'BC-NBPS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 4.00, NULL, '2026-11-06 19:04:29', '2026-11-06 19:04:30'),
(8287, 1067, 'NBPS-26', 'BC-NBPS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 6.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8288, 1067, 'NBPS-28', 'BC-NBPS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 2.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8289, 1067, 'NBPS-30', 'BC-NBPS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 3.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8290, 1067, 'NBPS-32', 'BC-NBPS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 5.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8291, 1067, 'NBPS-34', 'BC-NBPS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 6.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8292, 1067, 'NBPS-36', 'BC-NBPS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8293, 1068, 'NBPS2-22', 'BC-NBPS2-22', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8294, 1068, 'NBPS2-24', 'BC-NBPS2-24', '24', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8295, 1068, 'NBPS2-26', 'BC-NBPS2-26', '26', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8296, 1068, 'NBPS2-28', 'BC-NBPS2-28', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8297, 1068, 'NBPS2-30', 'BC-NBPS2-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8298, 1068, 'NBPS2-32', 'BC-NBPS2-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8299, 1068, 'NBPS2-34', 'BC-NBPS2-34', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8300, 1068, 'NBPS2-36', 'BC-NBPS2-36', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8301, 1069, 'NBRT-22', 'BC-NBRT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8302, 1069, 'NBRT-24', 'BC-NBRT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8303, 1069, 'NBRT-26', 'BC-NBRT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8304, 1069, 'NBRT-28', 'BC-NBRT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8305, 1069, 'NBRT-30', 'BC-NBRT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8306, 1069, 'NBRT-32', 'BC-NBRT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8307, 1069, 'NBRT-34', 'BC-NBRT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8308, 1069, 'NBRT-36', 'BC-NBRT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8309, 1069, 'NBRT-38', 'BC-NBRT-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8310, 1070, 'NBRTS-22', 'BC-NBRTS-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8311, 1070, 'NBRTS-24', 'BC-NBRTS-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8312, 1070, 'NBRTS-26', 'BC-NBRTS-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8313, 1070, 'NBRTS-28', 'BC-NBRTS-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8314, 1070, 'NBRTS-30', 'BC-NBRTS-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8315, 1070, 'NBRTS-32', 'BC-NBRTS-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8316, 1070, 'NBRTS-34', 'BC-NBRTS-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8317, 1070, 'NBRTS-36', 'BC-NBRTS-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8318, 1070, 'NBRTS-38', 'BC-NBRTS-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8319, 1070, 'NBRTS-40', 'BC-NBRTS-40', '40', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8320, 1070, 'NBRTS-42', 'BC-NBRTS-42', '42', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8321, 1071, 'NBSS-22', 'BC-NBSS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8322, 1071, 'NBSS-24', 'BC-NBSS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8323, 1071, 'NBSS-26', 'BC-NBSS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8324, 1071, 'NBSS-28', 'BC-NBSS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8325, 1071, 'NBSS-30', 'BC-NBSS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8326, 1071, 'NBSS-32', 'BC-NBSS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8327, 1071, 'NBSS-34', 'BC-NBSS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8328, 1071, 'NBSS-36', 'BC-NBSS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8329, 1072, 'NBST-22', 'BC-NBST-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8330, 1072, 'NBST-24', 'BC-NBST-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8331, 1072, 'NBST-26', 'BC-NBST-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8332, 1072, 'NBST-28', 'BC-NBST-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8333, 1072, 'NBST-30', 'BC-NBST-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8334, 1072, 'NBST-32', 'BC-NBST-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8335, 1072, 'NBST-34', 'BC-NBST-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8336, 1072, 'NBST-36', 'BC-NBST-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8337, 1073, 'NBSTS-22', 'BC-NBSTS-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8338, 1073, 'NBSTS-24', 'BC-NBSTS-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8339, 1073, 'NBSTS-26', 'BC-NBSTS-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8340, 1073, 'NBSTS-28', 'BC-NBSTS-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8341, 1073, 'NBSTS-30', 'BC-NBSTS-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8342, 1073, 'NBSTS-32', 'BC-NBSTS-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8343, 1073, 'NBSTS-34', 'BC-NBSTS-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8344, 1073, 'NBSTS-36', 'BC-NBSTS-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8345, 1073, 'NBSTS-38', 'BC-NBSTS-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8346, 1073, 'NBSTS-40', 'BC-NBSTS-40', '40', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8347, 1073, 'NBSTS-42', 'BC-NBSTS-42', '42', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8348, 1074, 'NBWS-22', 'BC-NBWS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8349, 1074, 'NBWS-24', 'BC-NBWS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8350, 1074, 'NBWS-26', 'BC-NBWS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8351, 1074, 'NBWS-28', 'BC-NBWS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8352, 1074, 'NBWS-30', 'BC-NBWS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8353, 1074, 'NBWS-32', 'BC-NBWS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8354, 1074, 'NBWS-34', 'BC-NBWS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8355, 1074, 'NBWS-36', 'BC-NBWS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8356, 1075, 'NBWTR-22', 'BC-NBWTR-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8357, 1075, 'NBWTR-24', 'BC-NBWTR-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8358, 1075, 'NBWTR-26', 'BC-NBWTR-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8359, 1075, 'NBWTR-28', 'BC-NBWTR-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8360, 1075, 'NBWTR-30', 'BC-NBWTR-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8361, 1075, 'NBWTR-32', 'BC-NBWTR-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8362, 1075, 'NBWTR-34', 'BC-NBWTR-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8363, 1075, 'NBWTR-36', 'BC-NBWTR-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8364, 1075, 'NBWTR-38', 'BC-NBWTR-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8365, 1075, 'NBWTR-40', 'BC-NBWTR-40', '40', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8366, 1075, 'NBWTR-42', 'BC-NBWTR-42', '42', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8367, 1076, 'PCEASS2-22', 'BC-PCEASS2-22', '22', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8368, 1076, 'PCEASS2-24', 'BC-PCEASS2-24', '24', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8369, 1076, 'PCEASS2-26', 'BC-PCEASS2-26', '26', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8370, 1076, 'PCEASS2-28', 'BC-PCEASS2-28', '28', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8371, 1076, 'PCEASS2-30', 'BC-PCEASS2-30', '30', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8372, 1076, 'PCEASS2-32', 'BC-PCEASS2-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8373, 1076, 'PCEASS2-34', 'BC-PCEASS2-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8374, 1076, 'PCEASS2-36', 'BC-PCEASS2-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8375, 1077, 'PCEAST-22', 'BC-PCEAST-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8376, 1077, 'PCEAST-24', 'BC-PCEAST-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8377, 1077, 'PCEAST-26', 'BC-PCEAST-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8378, 1077, 'PCEAST-28', 'BC-PCEAST-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8379, 1077, 'PCEAST-30', 'BC-PCEAST-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8380, 1077, 'PCEAST-32', 'BC-PCEAST-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8381, 1077, 'PCEAST-34', 'BC-PCEAST-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8382, 1077, 'PCEAST-36', 'BC-PCEAST-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8383, 1078, 'PCEASTS-22', 'BC-PCEASTS-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8384, 1078, 'PCEASTS-24', 'BC-PCEASTS-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8385, 1078, 'PCEASTS-26', 'BC-PCEASTS-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8386, 1078, 'PCEASTS-28', 'BC-PCEASTS-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8387, 1078, 'PCEASTS-30', 'BC-PCEASTS-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8388, 1078, 'PCEASTS-32', 'BC-PCEASTS-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8389, 1078, 'PCEASTS-34', 'BC-PCEASTS-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8390, 1078, 'PCEASTS-36', 'BC-PCEASTS-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8391, 1078, 'PCEASTS-38', 'BC-PCEASTS-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8392, 1078, 'PCEASTS-40', 'BC-PCEASTS-40', '40', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8393, 1078, 'PCEASTS-42', 'BC-PCEASTS-42', '42', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8394, 1079, 'POT-TR-22', 'BC-POT-TR-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8395, 1079, 'POT-TR-24', 'BC-POT-TR-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8396, 1079, 'POT-TR-26', 'BC-POT-TR-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8397, 1079, 'POT-TR-28', 'BC-POT-TR-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8398, 1079, 'POT-TR-30', 'BC-POT-TR-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8399, 1079, 'POT-TR-32', 'BC-POT-TR-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8400, 1079, 'POT-TR-34', 'BC-POT-TR-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8401, 1079, 'POT-TR-36', 'BC-POT-TR-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8402, 1080, 'PTS-10YRS', 'BC-PTS-10YRS', '10yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8403, 1080, 'PTS-12YRS', 'BC-PTS-12YRS', '12yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8404, 1080, 'PTS-2YRS', 'BC-PTS-2YRS', '2yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8405, 1080, 'PTS-4YRS', 'BC-PTS-4YRS', '4yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8406, 1080, 'PTS-6YRS', 'BC-PTS-6YRS', '6yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8407, 1080, 'PTS-8YRS', 'BC-PTS-8YRS', '8yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8408, 1081, 'RBWTS-22', 'BC-RBWTS-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8409, 1081, 'RBWTS-24', 'BC-RBWTS-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8410, 1081, 'RBWTS-26', 'BC-RBWTS-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8411, 1081, 'RBWTS-28', 'BC-RBWTS-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8412, 1081, 'RBWTS-30', 'BC-RBWTS-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8413, 1081, 'RBWTS-32', 'BC-RBWTS-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8414, 1081, 'RBWTS-34', 'BC-RBWTS-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8415, 1081, 'RBWTS-36', 'BC-RBWTS-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `barcode`, `size`, `colour`, `unit_of_measure`, `brand_name`, `selling_price`, `stock_quantity`, `deleted_at`, `created_at`, `updated_at`) VALUES
(8416, 1081, 'RBWTS-38', 'BC-RBWTS-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8417, 1081, 'RBWTS-40', 'BC-RBWTS-40', '40', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8418, 1081, 'RBWTS-42', 'BC-RBWTS-42', '42', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8419, 1082, 'RFJ-22', 'BC-RFJ-22', '22', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8420, 1082, 'RFJ-24', 'BC-RFJ-24', '24', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8421, 1082, 'RFJ-26', 'BC-RFJ-26', '26', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8422, 1082, 'RFJ-28', 'BC-RFJ-28', '28', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8423, 1082, 'RFJ-30', 'BC-RFJ-30', '30', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8424, 1082, 'RFJ-32', 'BC-RFJ-32', '32', 'Standard', 'Pieces', NULL, 1400.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8425, 1082, 'RFJ-34', 'BC-RFJ-34', '34', 'Standard', 'Pieces', NULL, 1400.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8426, 1082, 'RFJ-36', 'BC-RFJ-36', '36', 'Standard', 'Pieces', NULL, 1500.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8427, 1083, 'RNBTS-22-S', 'BC-RNBTS-22-S', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8428, 1083, 'RNBTS-24-S', 'BC-RNBTS-24-S', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8429, 1083, 'RNBTS-26-S', 'BC-RNBTS-26-S', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8430, 1083, 'RNBTS-28-S', 'BC-RNBTS-28-S', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8431, 1083, 'RNBTS-30-S', 'BC-RNBTS-30-S', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8432, 1083, 'RNBTS-32-S', 'BC-RNBTS-32-S', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8433, 1083, 'RNBTS-34-S', 'BC-RNBTS-34-S', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8434, 1083, 'RNBTS-36-S', 'BC-RNBTS-36-S', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8435, 1083, 'RNBTS-38-S', 'BC-RNBTS-38-S', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8436, 1083, 'RNBTS-40-S', 'BC-RNBTS-40-S', '40', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8437, 1083, 'RNBTS-42-S', 'BC-RNBTS-42-S', '42', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8438, 1084, 'RNT-22', 'BC-RNT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8439, 1084, 'RNT-24', 'BC-RNT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8440, 1084, 'RNT-26', 'BC-RNT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8441, 1084, 'RNT-28', 'BC-RNT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8442, 1084, 'RNT-30', 'BC-RNT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8443, 1084, 'RNT-32', 'BC-RNT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8444, 1084, 'RNT-34', 'BC-RNT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8445, 1084, 'RNT-36', 'BC-RNT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8446, 1085, 'RNTS-10YRS', 'BC-RNTS-10YRS', '10yrs', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8447, 1085, 'RNTS-12YRS', 'BC-RNTS-12YRS', '12yrs', 'Standard', 'Pieces', NULL, 400.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8448, 1085, 'RNTS-2YRS', 'BC-RNTS-2YRS', '2yrs', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8449, 1085, 'RNTS-4YRS', 'BC-RNTS-4YRS', '4yrs', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8450, 1085, 'RNTS-6YRS', 'BC-RNTS-6YRS', '6yrs', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8451, 1085, 'RNTS-8YRS', 'BC-RNTS-8YRS', '8yrs', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8452, 1086, 'RPS-22', 'BC-RPS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 10.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8453, 1086, 'RPS-24', 'BC-RPS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 9.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8454, 1086, 'RPS-26', 'BC-RPS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 7.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8455, 1086, 'RPS-28', 'BC-RPS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 13.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8456, 1086, 'RPS-30', 'BC-RPS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 5.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8457, 1086, 'RPS-32', 'BC-RPS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 13.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8458, 1086, 'RPS-34', 'BC-RPS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 18.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8459, 1086, 'RPS-36', 'BC-RPS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 19.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8460, 1087, 'RPTS-22', 'BC-RPTS-22', '22', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8461, 1087, 'RPTS-24', 'BC-RPTS-24', '24', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8462, 1087, 'RPTS-26', 'BC-RPTS-26', '26', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8463, 1087, 'RPTS-28', 'BC-RPTS-28', '28', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8464, 1087, 'RPTS-30', 'BC-RPTS-30', '30', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8465, 1087, 'RPTS-32', 'BC-RPTS-32', '32', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8466, 1087, 'RPTS-34', 'BC-RPTS-34', '34', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8467, 1087, 'RPTS-36', 'BC-RPTS-36', '36', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8468, 1087, 'RPTS-38', 'BC-RPTS-38', '38', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8469, 1087, 'RPTS-40', 'BC-RPTS-40', '40', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8470, 1087, 'RPTS-42', 'BC-RPTS-42', '42', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8471, 1088, 'RWS-22', 'BC-RWS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 6.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8472, 1088, 'RWS-24', 'BC-RWS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 8.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8473, 1088, 'RWS-26', 'BC-RWS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 18.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8474, 1088, 'RWS-28', 'BC-RWS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 16.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8475, 1088, 'RWS-30', 'BC-RWS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 7.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8476, 1088, 'RWS-32', 'BC-RWS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 8.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:30'),
(8477, 1088, 'RWS-34', 'BC-RWS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 12.00, NULL, '2026-11-06 19:04:30', '2026-11-06 19:04:31'),
(8478, 1088, 'RWS-36', 'BC-RWS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 9.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8479, 1089, 'RWT-22', 'BC-RWT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8480, 1089, 'RWT-24', 'BC-RWT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8481, 1089, 'RWT-26', 'BC-RWT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8482, 1089, 'RWT-28', 'BC-RWT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8483, 1089, 'RWT-30', 'BC-RWT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8484, 1089, 'RWT-32', 'BC-RWT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8485, 1089, 'RWT-34', 'BC-RWT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8486, 1089, 'RWT-36', 'BC-RWT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8487, 1089, 'RWT-38', 'BC-RWT-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8488, 1090, 'RWTR-22', 'BC-RWTR-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8489, 1090, 'RWTR-24', 'BC-RWTR-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8490, 1090, 'RWTR-26', 'BC-RWTR-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8491, 1090, 'RWTR-28', 'BC-RWTR-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8492, 1090, 'RWTR-30', 'BC-RWTR-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8493, 1090, 'RWTR-32', 'BC-RWTR-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8494, 1090, 'RWTR-34', 'BC-RWTR-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8495, 1090, 'RWTR-36', 'BC-RWTR-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8496, 1090, 'RWTR-38', 'BC-RWTR-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8497, 1090, 'RWTR-40', 'BC-RWTR-40', '40', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8498, 1090, 'RWTR-42', 'BC-RWTR-42', '42', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8499, 1091, 'RYT-22', 'BC-RYT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8500, 1091, 'RYT-24', 'BC-RYT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8501, 1091, 'RYT-26', 'BC-RYT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8502, 1091, 'RYT-28', 'BC-RYT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8503, 1091, 'RYT-30', 'BC-RYT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8504, 1091, 'RYT-32', 'BC-RYT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8505, 1091, 'RYT-34', 'BC-RYT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8506, 1091, 'RYT-36', 'BC-RYT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8507, 1091, 'RYT-38', 'BC-RYT-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8508, 1092, 'SAJS-32', 'BC-SAJS-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8509, 1092, 'SAJS-34', 'BC-SAJS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8510, 1092, 'SAJS-36', 'BC-SAJS-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8511, 1093, 'SAJT-34-V2', 'BC-SAJT-34-V2', '34', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8512, 1093, 'SAJT-36-V2', 'BC-SAJT-36-V2', '36', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8513, 1093, 'SAJT-38-V2', 'BC-SAJT-38-V2', '38', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8514, 1094, 'SAS-22', 'BC-SAS-22', '22', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8515, 1094, 'SAS-24', 'BC-SAS-24', '24', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8516, 1094, 'SAS-26', 'BC-SAS-26', '26', 'Standard', 'Pieces', NULL, 650.00, 1.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8517, 1094, 'SAS-28', 'BC-SAS-28', '28', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8518, 1094, 'SAS-30', 'BC-SAS-30', '30', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8519, 1094, 'SAS-32', 'BC-SAS-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8520, 1094, 'SAS-34', 'BC-SAS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8521, 1095, 'SASJS-32-M', 'BC-SASJS-32-M', '32', 'Standard', 'Pieces', NULL, 750.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8522, 1095, 'SASJS-34-M', 'BC-SASJS-34-M', '34', 'Standard', 'Pieces', NULL, 750.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8523, 1095, 'SASJS-36-M', 'BC-SASJS-36-M', '36', 'Standard', 'Pieces', NULL, 850.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8524, 1095, 'SASJS-38-M', 'BC-SASJS-38-M', '38', 'Standard', 'Pieces', NULL, 850.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8525, 1096, 'SECT-22', 'BC-SECT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8526, 1096, 'SECT-24', 'BC-SECT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8527, 1096, 'SECT-26', 'BC-SECT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8528, 1096, 'SECT-28', 'BC-SECT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8529, 1096, 'SECT-30', 'BC-SECT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8530, 1096, 'SECT-32', 'BC-SECT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8531, 1096, 'SECT-34', 'BC-SECT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8532, 1096, 'SECT-36', 'BC-SECT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8533, 1096, 'SECT-38', 'BC-SECT-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8534, 1097, 'SGNTS-10YRS', 'BC-SGNTS-10YRS', '10yrs', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8535, 1097, 'SGNTS-12YRS', 'BC-SGNTS-12YRS', '12yrs', 'Standard', 'Pieces', NULL, 400.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8536, 1097, 'SGNTS-2YRS', 'BC-SGNTS-2YRS', '2yrs', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8537, 1097, 'SGNTS-4YRS', 'BC-SGNTS-4YRS', '4yrs', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8538, 1097, 'SGNTS-6YRS', 'BC-SGNTS-6YRS', '6yrs', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8539, 1097, 'SGNTS-8YRS', 'BC-SGNTS-8YRS', '8yrs', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8540, 1098, 'SGT-22', 'BC-SGT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8541, 1098, 'SGT-24', 'BC-SGT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8542, 1098, 'SGT-26', 'BC-SGT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8543, 1098, 'SGT-28', 'BC-SGT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8544, 1098, 'SGT-30', 'BC-SGT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8545, 1098, 'SGT-32', 'BC-SGT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8546, 1098, 'SGT-34', 'BC-SGT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8547, 1098, 'SGT-36', 'BC-SGT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8548, 1098, 'SGT-38', 'BC-SGT-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8549, 1099, 'SGWTS-22', 'BC-SGWTS-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8550, 1099, 'SGWTS-24', 'BC-SGWTS-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8551, 1099, 'SGWTS-26', 'BC-SGWTS-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8552, 1099, 'SGWTS-28', 'BC-SGWTS-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8553, 1099, 'SGWTS-30', 'BC-SGWTS-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8554, 1099, 'SGWTS-32', 'BC-SGWTS-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8555, 1099, 'SGWTS-34', 'BC-SGWTS-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8556, 1099, 'SGWTS-36', 'BC-SGWTS-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8557, 1099, 'SGWTS-38', 'BC-SGWTS-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8558, 1099, 'SGWTS-40', 'BC-SGWTS-40', '40', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8559, 1099, 'SGWTS-42', 'BC-SGWTS-42', '42', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8560, 1100, 'SHA-SW-22', 'BC-SHA-SW-22', '22', 'Standard', 'Pieces', NULL, 600.00, 8.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8561, 1100, 'SHA-SW-24', 'BC-SHA-SW-24', '24', 'Standard', 'Pieces', NULL, 600.00, 7.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8562, 1100, 'SHA-SW-26', 'BC-SHA-SW-26', '26', 'Standard', 'Pieces', NULL, 650.00, 6.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8563, 1100, 'SHA-SW-28', 'BC-SHA-SW-28', '28', 'Standard', 'Pieces', NULL, 700.00, 7.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8564, 1100, 'SHA-SW-30', 'BC-SHA-SW-30', '30', 'Standard', 'Pieces', NULL, 700.00, 8.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8565, 1100, 'SHA-SW-32', 'BC-SHA-SW-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8566, 1100, 'SHA-SW-34', 'BC-SHA-SW-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8567, 1100, 'SHA-SW-36', 'BC-SHA-SW-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8568, 1101, 'SJS-32', 'BC-SJS-32', '32', 'Standard', 'Pieces', NULL, 800.00, 9.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8569, 1101, 'SJS-34', 'BC-SJS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 7.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8570, 1101, 'SJS-36', 'BC-SJS-36', '36', 'Standard', 'Pieces', NULL, 900.00, 8.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8571, 1102, 'SPJS-32', 'BC-SPJS-32', '32', 'Standard', 'Pieces', NULL, 800.00, 1.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8572, 1102, 'SPJS-34', 'BC-SPJS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 9.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8573, 1102, 'SPJS-36', 'BC-SPJS-36', '36', 'Standard', 'Pieces', NULL, 900.00, 3.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8574, 1103, 'SPJT-34-V2', 'BC-SPJT-34-V2', '34', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8575, 1103, 'SPJT-36-V2', 'BC-SPJT-36-V2', '36', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8576, 1103, 'SPJT-38-V2', 'BC-SPJT-38-V2', '38', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8577, 1103, 'SPJT-40', 'BC-SPJT-40', '40', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8578, 1103, 'SPJT-42', 'BC-SPJT-42', '42', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8579, 1104, 'SPS-22', 'BC-SPS-22', '22', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8580, 1104, 'SPS-24', 'BC-SPS-24', '24', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8581, 1104, 'SPS-26', 'BC-SPS-26', '26', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8582, 1104, 'SPS-28', 'BC-SPS-28', '28', 'Standard', 'Pieces', NULL, 700.00, 1.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8583, 1104, 'SPS-30', 'BC-SPS-30', '30', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8584, 1104, 'SPS-32', 'BC-SPS-32', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8585, 1104, 'SPS-34', 'BC-SPS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8586, 1104, 'SPS-36', 'BC-SPS-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8587, 1105, 'STES-22-S', 'BC-STES-22-S', '22', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8588, 1105, 'STES-24-S', 'BC-STES-24-S', '24', 'Standard', 'Pieces', NULL, 600.00, 1.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8589, 1105, 'STES-26-S', 'BC-STES-26-S', '26', 'Standard', 'Pieces', NULL, 650.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8590, 1105, 'STES-28-S', 'BC-STES-28-S', '28', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8591, 1105, 'STES-30-S', 'BC-STES-30-S', '30', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8592, 1105, 'STES-32-S', 'BC-STES-32-S', '32', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8593, 1105, 'STES-34-S', 'BC-STES-34-S', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8594, 1105, 'STES-36-S', 'BC-STES-36-S', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8595, 1106, 'STPJ-30-S', 'BC-STPJ-30-S', '30', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8596, 1106, 'STPJ-32-S', 'BC-STPJ-32-S', '32', 'Standard', 'Pieces', NULL, 800.00, 10.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8597, 1106, 'STPJ-34-S', 'BC-STPJ-34-S', '34', 'Standard', 'Pieces', NULL, 800.00, 10.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8598, 1106, 'STPJ-36-S', 'BC-STPJ-36-S', '36', 'Standard', 'Pieces', NULL, 900.00, 9.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8599, 1106, 'STPJ-38-S', 'BC-STPJ-38-S', '38', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8600, 1106, 'STPJ-40-S', 'BC-STPJ-40-S', '40', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8601, 1107, 'TBJT-34', 'BC-TBJT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8602, 1107, 'TBJT-36', 'BC-TBJT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8603, 1107, 'TBJT-38', 'BC-TBJT-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8604, 1108, 'TBT-22', 'BC-TBT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8605, 1108, 'TBT-24', 'BC-TBT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8606, 1108, 'TBT-26', 'BC-TBT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8607, 1108, 'TBT-28', 'BC-TBT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8608, 1108, 'TBT-30', 'BC-TBT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8609, 1108, 'TBT-32', 'BC-TBT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8610, 1108, 'TBT-34', 'BC-TBT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8611, 1108, 'TBT-36', 'BC-TBT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8612, 1108, 'TBT-38', 'BC-TBT-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8613, 1109, 'TBTS-L', 'BC-TBTS-L', 'L', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8614, 1109, 'TBTS-M', 'BC-TBTS-M', 'M', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8615, 1109, 'TBTS-S', 'BC-TBTS-S', 'S', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8616, 1110, 'TGJT-34', 'BC-TGJT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8617, 1110, 'TGJT-36', 'BC-TGJT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8618, 1110, 'TGJT-38', 'BC-TGJT-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8619, 1111, 'TGT-22', 'BC-TGT-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8620, 1111, 'TGT-24', 'BC-TGT-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8621, 1111, 'TGT-26', 'BC-TGT-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8622, 1111, 'TGT-28', 'BC-TGT-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8623, 1111, 'TGT-30', 'BC-TGT-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8624, 1111, 'TGT-32', 'BC-TGT-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8625, 1111, 'TGT-34', 'BC-TGT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8626, 1111, 'TGT-36', 'BC-TGT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8627, 1111, 'TGT-38', 'BC-TGT-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8628, 1112, 'TGTS-L', 'BC-TGTS-L', 'L', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8629, 1112, 'TGTS-M', 'BC-TGTS-M', 'M', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8630, 1112, 'TGTS-S', 'BC-TGTS-S', 'S', 'Standard', 'Pieces', NULL, 350.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8631, 1113, 'TJGTS-32-S', 'BC-TJGTS-32-S', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8632, 1113, 'TJGTS-34-S', 'BC-TJGTS-34-S', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8633, 1113, 'TJGTS-36-S', 'BC-TJGTS-36-S', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8634, 1113, 'TJGTS-38-S', 'BC-TJGTS-38-S', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8635, 1113, 'TJGTS-40-S', 'BC-TJGTS-40-S', '40', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8636, 1113, 'TJGTS-42-S', 'BC-TJGTS-42-S', '42', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8637, 1114, 'TJS-32', 'BC-TJS-32', '32', 'Standard', 'Pieces', NULL, 800.00, 7.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8638, 1114, 'TJS-34', 'BC-TJS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 2.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8639, 1114, 'TJS-36', 'BC-TJS-36', '36', 'Standard', 'Pieces', NULL, 900.00, 8.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8640, 1115, 'TJT-34', 'BC-TJT-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8641, 1115, 'TJT-36', 'BC-TJT-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8642, 1116, 'TPS-22', 'BC-TPS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8643, 1116, 'TPS-24', 'BC-TPS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8644, 1116, 'TPS-26', 'BC-TPS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8645, 1116, 'TPS-28', 'BC-TPS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8646, 1116, 'TPS-30', 'BC-TPS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8647, 1116, 'TPS-32', 'BC-TPS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8648, 1116, 'TPS-34', 'BC-TPS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8649, 1116, 'TPS-36', 'BC-TPS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8650, 1117, 'TPS2-22', 'BC-TPS2-22', '22', 'Standard', 'Pieces', NULL, 600.00, 4.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8651, 1117, 'TPS2-24', 'BC-TPS2-24', '24', 'Standard', 'Pieces', NULL, 600.00, 3.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8652, 1117, 'TPS2-26', 'BC-TPS2-26', '26', 'Standard', 'Pieces', NULL, 650.00, 4.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8653, 1117, 'TPS2-28', 'BC-TPS2-28', '28', 'Standard', 'Pieces', NULL, 700.00, 3.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8654, 1117, 'TPS2-30', 'BC-TPS2-30', '30', 'Standard', 'Pieces', NULL, 700.00, 7.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8655, 1117, 'TPS2-32', 'BC-TPS2-32', '32', 'Standard', 'Pieces', NULL, 800.00, 4.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8656, 1117, 'TPS2-34', 'BC-TPS2-34', '34', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8657, 1117, 'TPS2-36', 'BC-TPS2-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8658, 1118, 'UCJT-34', 'BC-UCJT-34', '34', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8659, 1118, 'UCJT-36', 'BC-UCJT-36', '36', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8660, 1118, 'UCJT-38', 'BC-UCJT-38', '38', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8661, 1118, 'UCJT-40', 'BC-UCJT-40', '40', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:31', '2026-11-06 19:04:31'),
(8662, 1118, 'UCJT-42', 'BC-UCJT-42', '42', 'Standard', 'Pieces', NULL, 1300.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8663, 1119, 'UCS-22', 'BC-UCS-22', '22', 'Standard', 'Pieces', NULL, 500.00, 8.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8664, 1119, 'UCS-24', 'BC-UCS-24', '24', 'Standard', 'Pieces', NULL, 500.00, 13.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8665, 1119, 'UCS-26', 'BC-UCS-26', '26', 'Standard', 'Pieces', NULL, 550.00, 6.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8666, 1119, 'UCS-28', 'BC-UCS-28', '28', 'Standard', 'Pieces', NULL, 600.00, 5.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8667, 1119, 'UCS-30', 'BC-UCS-30', '30', 'Standard', 'Pieces', NULL, 600.00, 9.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8668, 1119, 'UCS-32', 'BC-UCS-32', '32', 'Standard', 'Pieces', NULL, 700.00, 7.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8669, 1119, 'UCS-34', 'BC-UCS-34', '34', 'Standard', 'Pieces', NULL, 700.00, 9.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8670, 1119, 'UCS-36', 'BC-UCS-36', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8671, 1120, 'UNI-SW-22', 'BC-UNI-SW-22', '22', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8672, 1120, 'UNI-SW-24', 'BC-UNI-SW-24', '24', 'Standard', 'Pieces', NULL, 500.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8673, 1120, 'UNI-SW-26', 'BC-UNI-SW-26', '26', 'Standard', 'Pieces', NULL, 550.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8674, 1120, 'UNI-SW-28', 'BC-UNI-SW-28', '28', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8675, 1120, 'UNI-SW-30', 'BC-UNI-SW-30', '30', 'Standard', 'Pieces', NULL, 600.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8676, 1120, 'UNI-SW-32', 'BC-UNI-SW-32', '32', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8677, 1120, 'UNI-SW-34', 'BC-UNI-SW-34', '34', 'Standard', 'Pieces', NULL, 700.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8678, 1120, 'UNI-SW-36', 'BC-UNI-SW-36', '36', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8679, 1121, 'UPS-22', 'BC-UPS-22', '22', 'Standard', 'Pieces', NULL, 600.00, 7.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8680, 1121, 'UPS-24', 'BC-UPS-24', '24', 'Standard', 'Pieces', NULL, 600.00, 8.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8681, 1121, 'UPS-26', 'BC-UPS-26', '26', 'Standard', 'Pieces', NULL, 650.00, 6.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8682, 1121, 'UPS-28', 'BC-UPS-28', '28', 'Standard', 'Pieces', NULL, 700.00, 7.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8683, 1121, 'UPS-30', 'BC-UPS-30', '30', 'Standard', 'Pieces', NULL, 700.00, 6.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8684, 1121, 'UPS-32', 'BC-UPS-32', '32', 'Standard', 'Pieces', NULL, 800.00, 9.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8685, 1121, 'UPS-34', 'BC-UPS-34', '34', 'Standard', 'Pieces', NULL, 800.00, 4.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8686, 1121, 'UPS-36', 'BC-UPS-36', '36', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8687, 1122, 'UPTS-22', 'BC-UPTS-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8688, 1122, 'UPTS-24', 'BC-UPTS-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8689, 1122, 'UPTS-26', 'BC-UPTS-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8690, 1122, 'UPTS-28', 'BC-UPTS-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8691, 1122, 'UPTS-30', 'BC-UPTS-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8692, 1122, 'UPTS-32', 'BC-UPTS-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8693, 1122, 'UPTS-34', 'BC-UPTS-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8694, 1122, 'UPTS-36', 'BC-UPTS-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8695, 1122, 'UPTS-38', 'BC-UPTS-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8696, 1123, 'YEL-TR-22', 'BC-YEL-TR-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8697, 1123, 'YEL-TR-24', 'BC-YEL-TR-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8698, 1123, 'YEL-TR-26', 'BC-YEL-TR-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8699, 1123, 'YEL-TR-28', 'BC-YEL-TR-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8700, 1123, 'YEL-TR-30', 'BC-YEL-TR-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8701, 1123, 'YEL-TR-32', 'BC-YEL-TR-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8702, 1123, 'YEL-TR-34', 'BC-YEL-TR-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8703, 1123, 'YEL-TR-36', 'BC-YEL-TR-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8704, 1123, 'YEL-TR-38', 'BC-YEL-TR-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8705, 1124, 'YNTS-10YRS', 'BC-YNTS-10YRS', '10yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8706, 1124, 'YNTS-12YRS', 'BC-YNTS-12YRS', '12yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8707, 1124, 'YNTS-2YRS', 'BC-YNTS-2YRS', '2yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8708, 1124, 'YNTS-4YRS', 'BC-YNTS-4YRS', '4yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8709, 1124, 'YNTS-6YRS', 'BC-YNTS-6YRS', '6yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8710, 1124, 'YNTS-8YRS', 'BC-YNTS-8YRS', '8yrs', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8711, 1125, 'YWTS-22', 'BC-YWTS-22', '22', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8712, 1125, 'YWTS-24', 'BC-YWTS-24', '24', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8713, 1125, 'YWTS-26', 'BC-YWTS-26', '26', 'Standard', 'Pieces', NULL, 800.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8714, 1125, 'YWTS-28', 'BC-YWTS-28', '28', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8715, 1125, 'YWTS-30', 'BC-YWTS-30', '30', 'Standard', 'Pieces', NULL, 900.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8716, 1125, 'YWTS-32', 'BC-YWTS-32', '32', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8717, 1125, 'YWTS-34', 'BC-YWTS-34', '34', 'Standard', 'Pieces', NULL, 1000.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8718, 1125, 'YWTS-36', 'BC-YWTS-36', '36', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8719, 1125, 'YWTS-38', 'BC-YWTS-38', '38', 'Standard', 'Pieces', NULL, 1100.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8720, 1125, 'YWTS-40', 'BC-YWTS-40', '40', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8721, 1125, 'YWTS-42', 'BC-YWTS-42', '42', 'Standard', 'Pieces', NULL, 1200.00, 0.00, NULL, '2026-11-06 19:04:32', '2026-11-06 19:04:32'),
(8722, 1119, 'UNICENSWE-JIK-38-N', 'BC-UNICENSWE-JIK-38-N', '38', 'navy', 'Piece', 'jikaze', 900.00, 0.00, '2026-11-06 20:04:24', '2026-11-06 20:04:05', '2026-11-06 20:04:24');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_number` varchar(50) NOT NULL,
  `supplier_name` varchar(150) NOT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `branch_name` varchar(100) NOT NULL DEFAULT 'Main Shop',
  `status` varchar(30) NOT NULL DEFAULT 'received',
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `purchase_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

CREATE TABLE `purchase_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_id` bigint(20) UNSIGNED NOT NULL,
  `item_name` varchar(200) DEFAULT NULL,
  `unit` varchar(50) DEFAULT 'Pcs',
  `product_variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quotation_number` varchar(50) NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(150) NOT NULL,
  `branch_name` varchar(100) NOT NULL DEFAULT 'Main Branch',
  `total_amount` decimal(12,2) NOT NULL,
  `valid_until` date NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quotation_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `colour` varchar(50) DEFAULT NULL,
  `unit_of_measure` varchar(50) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'owner', 'Business owner with full system access', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(2, 'manager', 'Manages products, inventory, and staff operations', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(3, 'store_keeper', 'Receives finished products and updates inventory', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(4, 'cashier', 'Processes sales and prints receipts', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(5, 'accountant', 'Views financial reports and sales summaries', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34'),
(6, 'system_administrator', 'Manages users, permissions, and system configuration', 'web', '2026-08-26 20:41:34', '2026-08-26 20:41:34');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 6),
(3, 1),
(3, 2),
(3, 6),
(4, 1),
(4, 2),
(4, 6),
(5, 1),
(5, 6),
(6, 1),
(6, 2),
(6, 3),
(6, 4),
(6, 6),
(7, 1),
(7, 2),
(7, 6),
(8, 1),
(8, 2),
(8, 6),
(9, 1),
(9, 6),
(10, 1),
(10, 2),
(10, 3),
(10, 4),
(10, 6),
(11, 1),
(11, 2),
(11, 6),
(12, 1),
(12, 2),
(12, 6),
(13, 1),
(13, 6),
(14, 1),
(14, 2),
(14, 4),
(14, 5),
(14, 6),
(15, 1),
(15, 2),
(15, 4),
(15, 6),
(16, 1),
(16, 2),
(16, 4),
(16, 6),
(17, 1),
(17, 6),
(18, 1),
(18, 2),
(18, 4),
(18, 5),
(18, 6),
(19, 1),
(19, 2),
(19, 4),
(19, 6),
(20, 1),
(20, 2),
(20, 6),
(21, 1),
(21, 6),
(22, 1),
(22, 2),
(22, 3),
(22, 6),
(23, 1),
(23, 2),
(23, 3),
(23, 6),
(24, 1),
(24, 6),
(25, 1),
(25, 6),
(26, 1),
(26, 2),
(26, 3),
(26, 5),
(26, 6),
(27, 1),
(27, 2),
(27, 6),
(28, 1),
(28, 2),
(28, 5),
(28, 6),
(29, 1),
(29, 2),
(29, 5),
(29, 6),
(30, 1),
(30, 2),
(30, 3),
(30, 6),
(31, 1),
(31, 5),
(31, 6),
(32, 1),
(32, 6),
(33, 1),
(33, 6),
(34, 1),
(34, 6),
(35, 1),
(35, 6),
(36, 1),
(36, 6),
(37, 1),
(37, 6),
(38, 1),
(38, 6),
(39, 1),
(39, 6),
(40, 1),
(40, 6),
(41, 1),
(41, 6),
(42, 1),
(42, 2),
(42, 6),
(43, 1),
(43, 6);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `shift_id` bigint(20) UNSIGNED DEFAULT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_reference` varchar(50) DEFAULT NULL,
  `branch_name` varchar(100) NOT NULL DEFAULT 'Main Branch',
  `payment_status` varchar(20) NOT NULL DEFAULT 'paid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('bGgiQNCSk0eZgeTUaoNf3YKL6E0gEZI3JW2vNh0M', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36 Edg/157.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVFlEUnNaV1NEZE4zZ3VEYjI5eTJpUmdVSWJvemp1RFU0VzlUOVp5cCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1794120194),
('KZTF2lwJdWcJeCCcbga5iqkWC8DtT31Xpbi4d7vV', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36 Edg/157.0.0.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoibDBoVlJjNEFDQWw0YWpPVk9hVmxkSDFyem1STzhEa2V1OWQ2ejBkQiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjU2OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvcG9zL3NlYXJjaD9icmFuY2g9U1RBTEwlMjAzNSZxPW13YSI7czo1OiJyb3V0ZSI7czoxMDoicG9zLnNlYXJjaCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6IjA2ZWU2NmIyY2E5OWUwMjA2YjQ2MDM5ZDA4Y2NmYmNmNzMzZjRiOGY5ODRkZjgzODI0MDcwMzYyOWYyZDAxZGQiO30=', 1794151605),
('wHG2tjq9vDEKmEelxdzQ7JcxFuSYyXF7tvnDISqR', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36 Edg/157.0.0.0', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiYUpsMFl5TnZCMXBxWTFKanJpR3BuVXhlV1BsRTdlbzRQWkt0Q283OSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9wb3Mvc2VhcmNoP2JyYW5jaD1TVEFMTCUyMDM1JnE9bXdhbiI7czo1OiJyb3V0ZSI7czoxMDoicG9zLnNlYXJjaCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjY0OiIwNmVlNjZiMmNhOTllMDIwNmI0NjAzOWQwOGNjZmJjZjczM2Y0YjhmOTg0ZGY4MzgyNDA3MDM2MjlmMmQwMWRkIjtzOjEzOiJhY3RpdmVfYnJhbmNoIjtzOjg6IlNUQUxMIDM1Ijt9', 1794120390),
('WtTevDLAWnZ7pL7imiIDKScMjVGDkpYAb6wPNEC7', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36 Edg/156.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieThoSUt2SHFMT2d0ZzdVTnZjSTAyVnlNa2lNODBKOVlrbW5qWVd5TSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6MzoidXJsIjthOjE6e3M6ODoiaW50ZW5kZWQiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO319', 1794096917);

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `branch_name` varchar(100) DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `opening_float` decimal(10,2) NOT NULL DEFAULT 0.00,
  `expected_cash` decimal(10,2) NOT NULL DEFAULT 0.00,
  `actual_cash` decimal(10,2) DEFAULT NULL,
  `cash_collected_by_owner` tinyint(1) NOT NULL DEFAULT 0,
  `amount_collected` decimal(10,2) DEFAULT NULL,
  `float_left_in_drawer` decimal(10,2) DEFAULT NULL,
  `collected_by_name` varchar(150) DEFAULT NULL,
  `override_reason` varchar(255) DEFAULT NULL,
  `override_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `variance` decimal(10,2) DEFAULT NULL,
  `mpesa_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `card_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `credit_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_sales_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_sales_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `handover_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `user_id`, `branch_name`, `branch_id`, `started_at`, `ended_at`, `closed_at`, `opening_float`, `expected_cash`, `actual_cash`, `cash_collected_by_owner`, `amount_collected`, `float_left_in_drawer`, `collected_by_name`, `override_reason`, `override_by_user_id`, `variance`, `mpesa_total`, `card_total`, `credit_total`, `total_sales_count`, `total_sales_amount`, `status`, `handover_notes`, `created_at`, `updated_at`) VALUES
(2, 3, 'Main Shop', NULL, '2026-11-04 01:28:51', NULL, NULL, 0.00, 0.00, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 'open', NULL, '2026-11-03 22:28:51', '2026-11-03 22:28:51'),
(7, 1, 'Main Shop', 1, '2026-11-04 10:13:45', '2026-11-04 10:47:32', '2026-11-04 10:47:32', 0.00, 1200.00, 1200.00, 0, 0.00, 1200.00, NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 1, 1200.00, 'closed', NULL, '2026-11-04 07:13:45', '2026-11-04 07:47:32'),
(8, 1, 'Main Shop', 1, '2026-11-04 10:48:42', NULL, NULL, 1200.00, 1200.00, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 'open', NULL, '2026-11-04 07:48:42', '2026-11-04 07:48:42'),
(11, 2, 'STALL 35', 2, '2026-11-04 12:22:56', NULL, NULL, 0.00, 0.00, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 'open', NULL, '2026-11-04 09:22:56', '2026-11-04 09:22:56'),
(12, 4, 'Kyoi stage(sweaters)', 3, '2026-11-08 02:09:41', NULL, NULL, 0.00, 0.00, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0, 0.00, 'open', NULL, '2026-11-07 23:09:41', '2026-11-07 23:09:41');

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustments`
--

CREATE TABLE `stock_adjustments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `adjustment_number` varchar(50) NOT NULL,
  `branch_name` varchar(100) NOT NULL DEFAULT 'Main Branch',
  `reason` varchar(100) NOT NULL,
  `notes` text DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustment_items`
--

CREATE TABLE `stock_adjustment_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stock_adjustment_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('add','subtract') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_entries`
--

CREATE TABLE `stock_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `source_type` enum('opening_stock','purchase','factory_receipt','adjustment') NOT NULL DEFAULT 'opening_stock',
  `delivery_note_no` varchar(100) DEFAULT NULL,
  `origin_workshop` varchar(150) DEFAULT NULL,
  `branch_name` varchar(100) DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_entry_items`
--

CREATE TABLE `stock_entry_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stock_entry_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfers`
--

CREATE TABLE `stock_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transfer_number` varchar(50) NOT NULL,
  `from_branch` varchar(100) NOT NULL,
  `to_branch` varchar(100) NOT NULL,
  `transfer_date` date DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Completed',
  `notes` text DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfer_items`
--

CREATE TABLE `stock_transfer_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stock_transfer_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `email_verified_at`, `password`, `is_active`, `remember_token`, `created_at`, `updated_at`, `branch_id`, `branch_name`) VALUES
(1, 'System Administrator', 'admin', 'admin@tshaprint.co.ke', NULL, '$2y$12$lSPDU1f/BmvK7EHq6CAdaeCGzVJ3RZLismkXroc7/9zuSW11icqru', 1, NULL, '2026-08-26 20:41:36', '2026-11-02 14:43:46', NULL, 'Main Shop'),
(2, 'Joyce', 'joy', 'joy@tshaprint.co.ke', NULL, '$2y$12$vX5.iUXjJXnB69rp2sEr1.OqnugNm1QW6rsWy4GvfGV0vVkamclQO', 1, NULL, '2026-10-01 06:42:21', '2026-11-04 09:09:21', 2, 'STALL 35'),
(3, 'Gabriel', 'gabriel', 'gabrielmbugua3@gmail.com', NULL, '$2y$12$S5uAmuvVN9bqkpEoybSwBeopYYYyprby.wCKIM89qOlFPD/eg1CcC', 1, NULL, '2026-10-09 20:01:10', '2026-10-14 13:58:44', 1, 'Main Shop'),
(4, 'Simon', 'simo', 'simo@tshaprint.co.ke', NULL, '$2y$12$tTH4dV9YFXqICKWLTLJxW.nAb6L1fnhKgYRxMckh4jSt159XDkxkq', 1, NULL, '2026-11-04 09:04:03', '2026-11-07 23:08:25', 3, 'Kyoi stage(sweaters)');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branches_name_unique` (`name`),
  ADD UNIQUE KEY `branches_code_unique` (`code`);

--
-- Indexes for table `branch_stocks`
--
ALTER TABLE `branch_stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_stocks_branch_id_product_variant_id_unique` (`branch_id`,`product_variant_id`),
  ADD KEY `branch_stocks_product_variant_id_foreign` (`product_variant_id`),
  ADD KEY `branch_stocks_branch_name_index` (`branch_name`);

--
-- Indexes for table `business_settings`
--
ALTER TABLE `business_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `business_settings_key_unique` (`key`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_name_unique` (`name`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customers_phone_unique` (`phone`),
  ADD UNIQUE KEY `customers_email_unique` (`email`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `expenses_expense_number_unique` (`expense_number`),
  ADD KEY `expenses_user_id_foreign` (`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_sku_prefix_unique` (`sku_prefix`),
  ADD KEY `products_category_id_foreign` (`category_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_variants_sku_unique` (`sku`),
  ADD UNIQUE KEY `product_variants_barcode_unique` (`barcode`),
  ADD KEY `product_variants_product_id_foreign` (`product_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchases_purchase_number_unique` (`purchase_number`),
  ADD KEY `purchases_user_id_foreign` (`user_id`);

--
-- Indexes for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_items_purchase_id_foreign` (`purchase_id`),
  ADD KEY `purchase_items_product_variant_id_foreign` (`product_variant_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quotations_quotation_number_unique` (`quotation_number`),
  ADD KEY `quotations_customer_id_foreign` (`customer_id`),
  ADD KEY `quotations_user_id_foreign` (`user_id`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_items_quotation_id_foreign` (`quotation_id`),
  ADD KEY `quotation_items_product_variant_id_foreign` (`product_variant_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sales_receipt_number_unique` (`receipt_number`),
  ADD KEY `sales_customer_id_foreign` (`customer_id`),
  ADD KEY `sales_user_id_foreign` (`user_id`),
  ADD KEY `sales_shift_id_foreign` (`shift_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_items_sale_id_foreign` (`sale_id`),
  ADD KEY `sale_items_product_variant_id_foreign` (`product_variant_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shifts_user_id_foreign` (`user_id`);

--
-- Indexes for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_adjustments_adjustment_number_unique` (`adjustment_number`),
  ADD KEY `stock_adjustments_user_id_foreign` (`user_id`);

--
-- Indexes for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_adjustment_items_stock_adjustment_id_foreign` (`stock_adjustment_id`),
  ADD KEY `stock_adjustment_items_product_variant_id_foreign` (`product_variant_id`);

--
-- Indexes for table `stock_entries`
--
ALTER TABLE `stock_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_entries_user_id_foreign` (`user_id`);

--
-- Indexes for table `stock_entry_items`
--
ALTER TABLE `stock_entry_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_entry_items_stock_entry_id_foreign` (`stock_entry_id`),
  ADD KEY `stock_entry_items_product_variant_id_foreign` (`product_variant_id`);

--
-- Indexes for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_transfers_transfer_number_unique` (`transfer_number`),
  ADD KEY `stock_transfers_user_id_foreign` (`user_id`),
  ADD KEY `stock_transfers_received_by_foreign` (`received_by`);

--
-- Indexes for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_transfer_items_stock_transfer_id_foreign` (`stock_transfer_id`),
  ADD KEY `stock_transfer_items_product_variant_id_foreign` (`product_variant_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD KEY `users_branch_id_foreign` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `branch_stocks`
--
ALTER TABLE `branch_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2888;

--
-- AUTO_INCREMENT for table `business_settings`
--
ALTER TABLE `business_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1128;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1126;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8723;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `stock_entries`
--
ALTER TABLE `stock_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `stock_entry_items`
--
ALTER TABLE `stock_entry_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=506;

--
-- AUTO_INCREMENT for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `branch_stocks`
--
ALTER TABLE `branch_stocks`
  ADD CONSTRAINT `branch_stocks_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_stocks_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD CONSTRAINT `purchase_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_items_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `quotations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quotations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD CONSTRAINT `quotation_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quotation_items_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`),
  ADD CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shifts`
--
ALTER TABLE `shifts`
  ADD CONSTRAINT `shifts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD CONSTRAINT `stock_adjustments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  ADD CONSTRAINT `stock_adjustment_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_adjustment_items_stock_adjustment_id_foreign` FOREIGN KEY (`stock_adjustment_id`) REFERENCES `stock_adjustments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_entries`
--
ALTER TABLE `stock_entries`
  ADD CONSTRAINT `stock_entries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `stock_entry_items`
--
ALTER TABLE `stock_entry_items`
  ADD CONSTRAINT `stock_entry_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`),
  ADD CONSTRAINT `stock_entry_items_stock_entry_id_foreign` FOREIGN KEY (`stock_entry_id`) REFERENCES `stock_entries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD CONSTRAINT `stock_transfers_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_transfers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  ADD CONSTRAINT `stock_transfer_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_transfer_items_stock_transfer_id_foreign` FOREIGN KEY (`stock_transfer_id`) REFERENCES `stock_transfers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
