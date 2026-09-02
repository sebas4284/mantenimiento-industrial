-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: mantenimiento_industrial
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `areas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plant_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `areas_plant_id_foreign` (`plant_id`),
  CONSTRAINT `areas_plant_id_foreign` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `areas`
--

LOCK TABLES `areas` WRITE;
/*!40000 ALTER TABLE `areas` DISABLE KEYS */;
INSERT INTO `areas` VALUES (1,1,'Línea 1','2026-09-02 03:42:39','2026-09-02 03:42:39'),(2,1,'Utilities','2026-09-02 03:42:39','2026-09-02 03:42:39'),(3,1,'Línea 1','2026-09-02 03:42:39','2026-09-02 03:42:39'),(4,2,'Almacén','2026-09-02 03:42:40','2026-09-02 03:42:40'),(5,2,'Utilities','2026-09-02 03:42:40','2026-09-02 03:42:40'),(6,2,'Línea 1','2026-09-02 03:42:40','2026-09-02 03:42:40');
/*!40000 ALTER TABLE `areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `area_id` bigint(20) unsigned NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `criticality` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assets_code_unique` (`code`),
  KEY `assets_area_id_foreign` (`area_id`),
  CONSTRAINT `assets_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assets`
--

LOCK TABLES `assets` WRITE;
/*!40000 ALTER TABLE `assets` DISABLE KEYS */;
INSERT INTO `assets` VALUES (1,1,'EQ-2301','Motor eléctrico 4','Windler-Batz','IS-8832','SN-45459920','C','operativo','qrcodes/EQ-2301.svg',NULL,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(2,1,'EQ-8518','Robot soldador 6','Fadel-Anderson','TJ-7696','SN-85320773','B','operativo','qrcodes/EQ-8518.svg',NULL,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(3,1,'EQ-7973','Robot soldador 6','Graham-Eichmann','BW-4460','SN-35337367','B','operativo','qrcodes/EQ-7973.svg',NULL,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(4,1,'EQ-3502','Horno industrial 5','Zulauf, Wolff and Nader','SB-8926','SN-59708957','C','operativo','qrcodes/EQ-3502.svg',NULL,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(5,2,'EQ-9840','Compresor 3','Nolan-Bergnaum','QG-2467','SN-43004918','A','operativo','qrcodes/EQ-9840.svg',NULL,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(6,2,'EQ-5462','Banda transportadora 5','Mayert, Schinner and Anderson','XN-5667','SN-76353223','C','operativo','qrcodes/EQ-5462.svg',NULL,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(7,2,'EQ-7593','Bomba centrífuga 2','Bins-Reynolds','HK-4032','SN-62222234','C','operativo','qrcodes/EQ-7593.svg',NULL,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(8,2,'EQ-7750','Compresor 9','Cole, Zieme and Hane','OQ-3727','SN-70487677','B','operativo','qrcodes/EQ-7750.svg',NULL,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(9,3,'EQ-5156','Robot soldador 2','Parisian-Schmitt','QI-6205','SN-90732601','A','operativo','qrcodes/EQ-5156.svg',NULL,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(10,3,'EQ-9969','Compresor 3','Windler-Maggio','KT-8269','SN-59189265','A','operativo','qrcodes/EQ-9969.svg',NULL,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(11,3,'EQ-5894','Bomba centrífuga 7','Wolf-Littel','DT-5596','SN-91866919','A','operativo','qrcodes/EQ-5894.svg',NULL,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(12,3,'EQ-8953','Robot soldador 6','Marvin-Nicolas','OK-1368','SN-95755432','A','operativo','qrcodes/EQ-8953.svg',NULL,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(13,4,'EQ-8947','Motor eléctrico 6','Dibbert, Skiles and Auer','YF-3082','SN-35210358','B','operativo','qrcodes/EQ-8947.svg',NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(14,4,'EQ-9970','Bomba centrífuga 7','Bartoletti, Rippin and Schuppe','DG-8100','SN-31780127','C','operativo','qrcodes/EQ-9970.svg',NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(15,4,'EQ-9234','Compresor 3','Kuhlman Inc','CV-4937','SN-85396418','C','operativo','qrcodes/EQ-9234.svg',NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(16,4,'EQ-5411','Banda transportadora 7','Raynor, Mertz and Kub','GO-6651','SN-40160135','A','operativo','qrcodes/EQ-5411.svg',NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(17,5,'EQ-2356','Compresor 8','Hill-Nolan','MF-5476','SN-16126734','B','operativo','qrcodes/EQ-2356.svg',NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(18,5,'EQ-2544','Robot soldador 1','Cummings LLC','ME-2495','SN-83761757','B','operativo','qrcodes/EQ-2544.svg',NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(19,5,'EQ-7818','Horno industrial 9','Gorczany-Wolf','WT-5506','SN-04142153','A','operativo','qrcodes/EQ-7818.svg',NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(20,5,'EQ-9936','Robot soldador 1','Langosh, Langosh and Wintheiser','FM-4320','SN-00061230','B','operativo','qrcodes/EQ-9936.svg',NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(21,6,'EQ-5329','Banda transportadora 7','Konopelski Group','XK-2755','SN-88545520','C','operativo','qrcodes/EQ-5329.svg',NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(22,6,'EQ-4545','Horno industrial 7','Raynor-Morissette','GA-4249','SN-53654797','B','operativo','qrcodes/EQ-4545.svg',NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(23,6,'EQ-5710','Bomba centrífuga 9','Bernhard, Kuhn and King','VM-0171','SN-09478140','A','operativo','qrcodes/EQ-5710.svg',NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(24,6,'EQ-4610','Compresor 7','Bartoletti Inc','TP-0844','SN-73027725','B','operativo','qrcodes/EQ-4610.svg',NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40');
/*!40000 ALTER TABLE `assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attachable_type` varchar(255) NOT NULL,
  `attachable_id` bigint(20) unsigned NOT NULL,
  `uploaded_by` bigint(20) unsigned NOT NULL,
  `path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attachments_attachable_type_attachable_id_index` (`attachable_type`,`attachable_id`),
  KEY `attachments_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `attachments_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attachments`
--

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checklist_items`
--

DROP TABLE IF EXISTS `checklist_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `checklist_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `checklist_template_id` bigint(20) unsigned NOT NULL,
  `label` varchar(255) NOT NULL,
  `order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `checklist_items_checklist_template_id_foreign` (`checklist_template_id`),
  CONSTRAINT `checklist_items_checklist_template_id_foreign` FOREIGN KEY (`checklist_template_id`) REFERENCES `checklist_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checklist_items`
--

LOCK TABLES `checklist_items` WRITE;
/*!40000 ALTER TABLE `checklist_items` DISABLE KEYS */;
INSERT INTO `checklist_items` VALUES (1,1,'Revisar nivel de aceite',7,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(2,1,'Inspeccionar fugas',1,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(3,1,'Inspeccionar fugas',3,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(4,1,'Revisar nivel de aceite',6,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(5,1,'Verificar temperatura',2,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(6,2,'Revisar nivel de aceite',2,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(7,2,'Comprobar vibración',6,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(8,2,'Revisar nivel de aceite',1,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(9,2,'Revisar nivel de aceite',7,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(10,2,'Verificar temperatura',9,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(11,3,'Ajustar tornillería',2,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(12,3,'Ajustar tornillería',8,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(13,3,'Ajustar tornillería',2,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(14,3,'Ajustar tornillería',4,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(15,3,'Verificar temperatura',5,'2026-09-02 03:42:39','2026-09-02 03:42:39');
/*!40000 ALTER TABLE `checklist_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checklist_templates`
--

DROP TABLE IF EXISTS `checklist_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `checklist_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checklist_templates`
--

LOCK TABLES `checklist_templates` WRITE;
/*!40000 ALTER TABLE `checklist_templates` DISABLE KEYS */;
INSERT INTO `checklist_templates` VALUES (1,'Inspección de seguridad','2026-09-02 03:42:39','2026-09-02 03:42:39'),(2,'Lubricación general','2026-09-02 03:42:39','2026-09-02 03:42:39'),(3,'Lubricación general','2026-09-02 03:42:39','2026-09-02 03:42:39');
/*!40000 ALTER TABLE `checklist_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance_plans`
--

DROP TABLE IF EXISTS `maintenance_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintenance_plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` bigint(20) unsigned NOT NULL,
  `checklist_template_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `frequency_days` int(10) unsigned NOT NULL,
  `next_due_date` date NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maintenance_plans_asset_id_foreign` (`asset_id`),
  KEY `maintenance_plans_checklist_template_id_foreign` (`checklist_template_id`),
  CONSTRAINT `maintenance_plans_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `maintenance_plans_checklist_template_id_foreign` FOREIGN KEY (`checklist_template_id`) REFERENCES `checklist_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance_plans`
--

LOCK TABLES `maintenance_plans` WRITE;
/*!40000 ALTER TABLE `maintenance_plans` DISABLE KEYS */;
INSERT INTO `maintenance_plans` VALUES (1,2,1,'Revisión trimestral',90,'2026-12-01',1,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(2,5,3,'Mantenimiento preventivo mensual',7,'2026-09-09',1,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(3,6,2,'Revisión trimestral',30,'2026-10-02',1,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(4,7,1,'Lubricación programada',7,'2026-09-09',1,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(5,8,1,'Lubricación programada',7,'2026-09-09',1,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(6,10,2,'Revisión trimestral',7,'2026-09-09',1,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(7,13,2,'Lubricación programada',90,'2026-12-01',1,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(8,16,3,'Mantenimiento preventivo mensual',15,'2026-09-17',1,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(9,17,1,'Revisión trimestral',7,'2026-09-09',1,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(10,18,3,'Revisión trimestral',15,'2026-09-17',1,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(11,21,2,'Lubricación programada',7,'2026-09-09',1,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(12,22,1,'Revisión trimestral',30,'2026-10-02',1,'2026-09-02 03:42:40','2026-09-02 03:42:40');
/*!40000 ALTER TABLE `maintenance_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_09_01_214158_create_plants_table',1),(5,'2026_09_01_214159_create_areas_table',1),(6,'2026_09_01_214200_create_assets_table',1),(7,'2026_09_01_214201_create_checklist_templates_table',1),(8,'2026_09_01_214202_create_checklist_items_table',1),(9,'2026_09_01_214203_create_maintenance_plans_table',1),(10,'2026_09_01_214204_create_work_orders_table',1),(11,'2026_09_01_214205_create_work_order_checklist_results_table',1),(12,'2026_09_01_214206_create_attachments_table',1),(13,'2026_09_01_214214_add_role_and_plant_id_to_users_table',1),(14,'2026_09_01_232857_create_spare_parts_table',1),(15,'2026_09_01_232858_create_spare_part_usages_table',1),(16,'2026_09_02_022652_add_code_and_sequence_to_plants_table',1),(17,'2026_09_02_022653_add_order_number_to_work_orders_table',1),(18,'2026_09_02_030634_create_providers_table',1),(19,'2026_09_02_030637_add_execution_fields_to_work_orders_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plants`
--

DROP TABLE IF EXISTS `plants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `next_work_order_sequence` int(10) unsigned NOT NULL DEFAULT 1,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plants_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plants`
--

LOCK TABLES `plants` WRITE;
/*!40000 ALTER TABLE `plants` DISABLE KEYS */;
INSERT INTO `plants` VALUES (1,'Planta Norte','PN',49,'Metzside, Somalia','2026-09-02 03:42:39','2026-09-02 03:42:40'),(2,'Planta Sur','PS',51,'West Terry, Benin','2026-09-02 03:42:40','2026-09-02 03:55:22');
/*!40000 ALTER TABLE `plants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `providers`
--

DROP TABLE IF EXISTS `providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `providers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `specialty` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `providers`
--

LOCK TABLES `providers` WRITE;
/*!40000 ALTER TABLE `providers` DISABLE KEYS */;
INSERT INTO `providers` VALUES (1,'Watsica LLC','Kariane Ruecker','+12235161653','rigoberto46@mosciski.com','48982 Laurine Crossroad Apt. 328\nEast Janellemouth, ID 41762-6141','Eléctrico','2026-09-02 03:42:39','2026-09-02 03:42:39'),(2,'McDermott-Kiehn','Dr. Hadley Kozey','918.476.8322','iliana49@rutherford.org','28530 Lemuel Forest Suite 702\nGreenfelderburgh, WV 43522-0765','Soldadura','2026-09-02 03:42:39','2026-09-02 03:42:39'),(3,'Johnson-Skiles','Mr. Arvel Kutch','(870) 733-5437','shanelle.gerhold@windler.net','655 Torphy Greens\nEvangelinebury, AR 21313-1458','Hidráulico','2026-09-02 03:42:39','2026-09-02 03:42:39'),(4,'Schuster, Wyman and Bergstrom','Mrs. Maye Bernhard','+16299435294','davis.brooklyn@gleason.com','4651 O\'Hara Pine\nPort Zackary, NV 21954','Hidráulico','2026-09-02 03:42:39','2026-09-02 03:42:39');
/*!40000 ALTER TABLE `providers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('2moyruQadZBq5fUECJzodeQdMeoYJKvIe7W2C9Hd',3,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/151.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJHSGxuR3lzWUZPdVlNQ0gyb0ZvV1gwQ28xSUEyZ2JtSVFJNnFjeWZqIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDEwXC9wcm92ZWVkb3JlcyIsInJvdXRlIjoicHJvdmlkZXJzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjN9',1788321106),('D1rquSGkFlFKGYeqrFR5zkYYI8qsIqC0aHI5OVzf',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/151.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJiaEN6RjlwSFVJQWFPSHJ2SUVRZmNKVnpDaVphdTNoWUd0TXZUd2VMIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDEwXC9vcmRlbmVzXC8xNCIsInJvdXRlIjoid29yay1vcmRlcnMuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxfQ==',1788321064),('i6kANy3SLGDzBqN1QMFWp6K9Z1ErBMGAdgVXtCKu',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJKbkFoYW9vRWRIQWRVZmkyVGFJWnpKV2MxUEdXaWJudklNRFpZcW4xIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDEwXC9hY3Rpdm9zXC82Iiwicm91dGUiOiJhc3NldHMuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxfQ==',1788321407);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spare_part_usages`
--

DROP TABLE IF EXISTS `spare_part_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spare_part_usages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `work_order_id` bigint(20) unsigned NOT NULL,
  `spare_part_id` bigint(20) unsigned NOT NULL,
  `used_by` bigint(20) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `spare_part_usages_work_order_id_foreign` (`work_order_id`),
  KEY `spare_part_usages_spare_part_id_foreign` (`spare_part_id`),
  KEY `spare_part_usages_used_by_foreign` (`used_by`),
  CONSTRAINT `spare_part_usages_spare_part_id_foreign` FOREIGN KEY (`spare_part_id`) REFERENCES `spare_parts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `spare_part_usages_used_by_foreign` FOREIGN KEY (`used_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `spare_part_usages_work_order_id_foreign` FOREIGN KEY (`work_order_id`) REFERENCES `work_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spare_part_usages`
--

LOCK TABLES `spare_part_usages` WRITE;
/*!40000 ALTER TABLE `spare_part_usages` DISABLE KEYS */;
INSERT INTO `spare_part_usages` VALUES (1,2,2,5,5,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(2,13,1,5,4,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(3,15,7,5,5,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(4,27,6,5,2,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(5,29,8,5,5,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(6,52,11,8,2,'2026-09-02 03:42:41','2026-09-02 03:42:41'),(7,70,10,9,4,'2026-09-02 03:42:41','2026-09-02 03:42:41'),(8,81,9,8,2,'2026-09-02 03:42:41','2026-09-02 03:42:41'),(9,85,11,8,1,'2026-09-02 03:42:41','2026-09-02 03:42:41'),(10,89,11,8,3,'2026-09-02 03:42:41','2026-09-02 03:42:41');
/*!40000 ALTER TABLE `spare_part_usages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spare_parts`
--

DROP TABLE IF EXISTS `spare_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spare_parts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plant_id` bigint(20) unsigned NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `stock_quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `minimum_stock` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `spare_parts_plant_id_code_unique` (`plant_id`,`code`),
  CONSTRAINT `spare_parts_plant_id_foreign` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spare_parts`
--

LOCK TABLES `spare_parts` WRITE;
/*!40000 ALTER TABLE `spare_parts` DISABLE KEYS */;
INSERT INTO `spare_parts` VALUES (1,1,'RP-2988','Sello mecánico',10,12,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(2,1,'RP-8923','Manguera hidráulica',12,14,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(3,1,'RP-4535','Rodamiento 6205',10,5,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(4,1,'RP-6630','Filtro de aceite',9,12,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(5,1,'RP-8901','Correa en V',13,12,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(6,1,'RP-2957','Fusible 10A',11,6,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(7,1,'RP-2693','Sello mecánico',19,6,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(8,1,'RP-2546','Correa en V',0,14,'2026-09-02 03:42:39','2026-09-02 03:42:39'),(9,2,'RP-1116','Correa en V',13,15,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(10,2,'RP-8394','Manguera hidráulica',3,5,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(11,2,'RP-2771','Empaque de válvula',27,12,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(12,2,'RP-5094','Sensor de proximidad',21,15,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(13,2,'RP-8071','Rodamiento 6205',40,6,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(14,2,'RP-1075','Sensor de proximidad',31,11,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(15,2,'RP-2776','Sensor de proximidad',1,10,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(16,2,'RP-2862','Rodamiento 6205',12,8,'2026-09-02 03:42:40','2026-09-02 03:42:40');
/*!40000 ALTER TABLE `spare_parts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'tecnico',
  `plant_id` bigint(20) unsigned DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_plant_id_foreign` (`plant_id`),
  CONSTRAINT `users_plant_id_foreign` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin General','admin@mantenimiento.test','admin',NULL,'2026-09-02 03:42:39','$2y$12$DP40xS6nGabZGdJp3NMH7.KZEMdIcojpBVxpCUcaJNojOEgAX7fRy','bblXUy7WlC','2026-09-02 03:42:39','2026-09-02 03:42:39'),(2,'Dirección Corporativa','corporativo@mantenimiento.test','corporativo',NULL,'2026-09-02 03:42:39','$2y$12$DP40xS6nGabZGdJp3NMH7.KZEMdIcojpBVxpCUcaJNojOEgAX7fRy','0LbCC6apD7','2026-09-02 03:42:39','2026-09-02 03:42:39'),(3,'Supervisor Planta Norte','supervisor.norte@mantenimiento.test','supervisor',1,'2026-09-02 03:42:39','$2y$12$DP40xS6nGabZGdJp3NMH7.KZEMdIcojpBVxpCUcaJNojOEgAX7fRy','r4qOIisWPm','2026-09-02 03:42:39','2026-09-02 03:42:39'),(4,'Gunner Marvin','bahringer.jessyca@example.net','tecnico',1,'2026-09-02 03:42:39','$2y$12$DP40xS6nGabZGdJp3NMH7.KZEMdIcojpBVxpCUcaJNojOEgAX7fRy','mQvMX080wV','2026-09-02 03:42:39','2026-09-02 03:42:39'),(5,'Carmen Koepp','trenton24@example.org','tecnico',1,'2026-09-02 03:42:39','$2y$12$DP40xS6nGabZGdJp3NMH7.KZEMdIcojpBVxpCUcaJNojOEgAX7fRy','HLfGZ4vOBI','2026-09-02 03:42:39','2026-09-02 03:42:39'),(6,'Operador Planta Norte','operador.norte@mantenimiento.test','operador',1,'2026-09-02 03:42:39','$2y$12$DP40xS6nGabZGdJp3NMH7.KZEMdIcojpBVxpCUcaJNojOEgAX7fRy','1duARq9NFY','2026-09-02 03:42:39','2026-09-02 03:42:39'),(7,'Supervisor Planta Sur','supervisor.sur@mantenimiento.test','supervisor',2,'2026-09-02 03:42:40','$2y$12$DP40xS6nGabZGdJp3NMH7.KZEMdIcojpBVxpCUcaJNojOEgAX7fRy','RUajoNy5sm','2026-09-02 03:42:40','2026-09-02 03:42:40'),(8,'Mr. Alfonzo Hand V','fkunde@example.net','tecnico',2,'2026-09-02 03:42:40','$2y$12$DP40xS6nGabZGdJp3NMH7.KZEMdIcojpBVxpCUcaJNojOEgAX7fRy','ZJlwGV0syN','2026-09-02 03:42:40','2026-09-02 03:42:40'),(9,'Reese Turner','irma.russel@example.org','tecnico',2,'2026-09-02 03:42:40','$2y$12$DP40xS6nGabZGdJp3NMH7.KZEMdIcojpBVxpCUcaJNojOEgAX7fRy','IUrU8iOnma','2026-09-02 03:42:40','2026-09-02 03:42:40'),(10,'Operador Planta Sur','operador.sur@mantenimiento.test','operador',2,'2026-09-02 03:42:40','$2y$12$DP40xS6nGabZGdJp3NMH7.KZEMdIcojpBVxpCUcaJNojOEgAX7fRy','1uXPG88Qjg','2026-09-02 03:42:40','2026-09-02 03:42:40');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `work_order_checklist_results`
--

DROP TABLE IF EXISTS `work_order_checklist_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `work_order_checklist_results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `work_order_id` bigint(20) unsigned NOT NULL,
  `checklist_item_id` bigint(20) unsigned NOT NULL,
  `passed` tinyint(1) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `work_order_checklist_results_work_order_id_foreign` (`work_order_id`),
  KEY `work_order_checklist_results_checklist_item_id_foreign` (`checklist_item_id`),
  CONSTRAINT `work_order_checklist_results_checklist_item_id_foreign` FOREIGN KEY (`checklist_item_id`) REFERENCES `checklist_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `work_order_checklist_results_work_order_id_foreign` FOREIGN KEY (`work_order_id`) REFERENCES `work_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `work_order_checklist_results`
--

LOCK TABLES `work_order_checklist_results` WRITE;
/*!40000 ALTER TABLE `work_order_checklist_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `work_order_checklist_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `work_orders`
--

DROP TABLE IF EXISTS `work_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `work_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) NOT NULL,
  `asset_id` bigint(20) unsigned NOT NULL,
  `maintenance_plan_id` bigint(20) unsigned DEFAULT NULL,
  `reported_by` bigint(20) unsigned NOT NULL,
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `execution_type` varchar(255) NOT NULL DEFAULT 'interno',
  `provider_id` bigint(20) unsigned DEFAULT NULL,
  `priority` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `failure_description` text DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `opened_at` datetime NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `work_orders_order_number_unique` (`order_number`),
  KEY `work_orders_asset_id_foreign` (`asset_id`),
  KEY `work_orders_maintenance_plan_id_foreign` (`maintenance_plan_id`),
  KEY `work_orders_reported_by_foreign` (`reported_by`),
  KEY `work_orders_assigned_to_foreign` (`assigned_to`),
  KEY `work_orders_provider_id_foreign` (`provider_id`),
  CONSTRAINT `work_orders_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `work_orders_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `work_orders_maintenance_plan_id_foreign` FOREIGN KEY (`maintenance_plan_id`) REFERENCES `maintenance_plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `work_orders_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `work_orders_reported_by_foreign` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `work_orders`
--

LOCK TABLES `work_orders` WRITE;
/*!40000 ALTER TABLE `work_orders` DISABLE KEYS */;
INSERT INTO `work_orders` VALUES (1,'PN0001',1,NULL,6,4,'correctivo','interno',NULL,'alta','completada','Ruido anormal en el equipo','Reparación completada, equipo restaurado a condición operativa.','2026-07-01 10:08:41','2026-07-02 21:08:41','2026-07-03 17:08:41','2026-09-02 03:42:39','2026-09-02 03:42:39'),(2,'PN0002',1,NULL,6,4,'correctivo','interno',NULL,'urgente','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-08-30 04:21:05','2026-08-31 11:21:05','2026-08-31 12:21:05','2026-09-02 03:42:39','2026-09-02 03:42:39'),(3,'PN0003',1,NULL,6,4,'correctivo','interno',NULL,'urgente','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-07-18 00:04:26','2026-07-19 12:04:26','2026-07-21 01:04:26','2026-09-02 03:42:40','2026-09-02 03:42:40'),(4,'PN0004',2,NULL,4,4,'correctivo','interno',NULL,'alta','completada','El motor no enciende','Reparación completada, equipo restaurado a condición operativa.','2026-07-05 20:57:43','2026-07-07 07:57:43','2026-07-08 01:57:43','2026-09-02 03:42:40','2026-09-02 03:42:40'),(5,'PN0005',2,NULL,4,4,'correctivo','interno',NULL,'baja','completada','El motor no enciende','Reparación completada, equipo restaurado a condición operativa.','2026-07-26 21:13:51','2026-07-27 19:13:51','2026-07-29 01:13:51','2026-09-02 03:42:40','2026-09-02 03:42:40'),(6,'PN0006',2,NULL,4,4,'correctivo','interno',NULL,'urgente','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-06-05 19:52:48','2026-06-05 22:52:48','2026-06-08 03:52:48','2026-09-02 03:42:40','2026-09-02 03:42:40'),(7,'PN0007',2,NULL,4,4,'correctivo','interno',NULL,'baja','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-04-18 23:01:04','2026-04-20 20:01:04','2026-04-22 08:01:04','2026-09-02 03:42:40','2026-09-02 03:42:40'),(8,'PN0008',3,NULL,4,4,'correctivo','interno',NULL,'baja','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-08-02 11:32:07','2026-08-04 11:32:07','2026-08-05 04:32:07','2026-09-02 03:42:40','2026-09-02 03:42:40'),(9,'PN0009',3,NULL,4,4,'correctivo','interno',NULL,'media','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-08-27 00:07:53','2026-08-27 14:07:53','2026-08-28 14:07:53','2026-09-02 03:42:40','2026-09-02 03:42:40'),(10,'PN0010',4,NULL,5,4,'correctivo','interno',NULL,'urgente','completada','Sobrecalentamiento','Reparación completada, equipo restaurado a condición operativa.','2026-06-06 05:52:26','2026-06-06 18:52:26','2026-06-09 11:52:26','2026-09-02 03:42:40','2026-09-02 03:42:40'),(11,'PN0011',4,NULL,5,4,'correctivo','externo',1,'baja','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-07-08 02:52:41','2026-07-09 02:52:41','2026-07-10 15:52:41','2026-09-02 03:42:40','2026-09-02 03:42:40'),(12,'PN0012',5,NULL,6,5,'correctivo','interno',NULL,'baja','completada','Sobrecalentamiento','Reparación completada, equipo restaurado a condición operativa.','2026-03-15 04:48:15','2026-03-15 23:48:15','2026-03-16 14:48:15','2026-09-02 03:42:40','2026-09-02 03:42:40'),(13,'PN0013',5,NULL,6,5,'correctivo','interno',NULL,'alta','completada','Sobrecalentamiento','Reparación completada, equipo restaurado a condición operativa.','2026-06-24 03:18:19','2026-06-25 10:18:19','2026-06-26 19:18:19','2026-09-02 03:42:40','2026-09-02 03:42:40'),(14,'PN0014',5,NULL,4,5,'correctivo','interno',NULL,'alta','abierta','Vibración excesiva',NULL,'2026-08-28 03:42:40',NULL,NULL,'2026-09-02 03:42:40','2026-09-02 03:51:04'),(15,'PN0015',6,NULL,4,4,'correctivo','interno',NULL,'alta','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-06-14 09:54:38','2026-06-14 21:54:38','2026-06-15 21:54:38','2026-09-02 03:42:40','2026-09-02 03:42:40'),(16,'PN0016',6,NULL,4,4,'correctivo','interno',NULL,'baja','completada','Sobrecalentamiento','Reparación completada, equipo restaurado a condición operativa.','2026-06-01 01:33:06','2026-06-01 20:33:06','2026-06-03 07:33:06','2026-09-02 03:42:40','2026-09-02 03:42:40'),(17,'PN0017',6,NULL,4,4,'correctivo','interno',NULL,'baja','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-08-13 00:42:44','2026-08-13 11:42:44','2026-08-15 20:42:44','2026-09-02 03:42:40','2026-09-02 03:42:40'),(18,'PN0018',6,NULL,4,NULL,'correctivo','interno',NULL,'alta','abierta','Sobrecalentamiento',NULL,'2026-08-24 03:42:40',NULL,NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(19,'PN0019',7,NULL,4,5,'correctivo','interno',NULL,'media','completada','Sobrecalentamiento','Reparación completada, equipo restaurado a condición operativa.','2026-06-03 05:30:46','2026-06-04 10:30:46','2026-06-04 15:30:46','2026-09-02 03:42:40','2026-09-02 03:42:40'),(20,'PN0020',7,NULL,4,5,'correctivo','interno',NULL,'urgente','completada','Ruido anormal en el equipo','Reparación completada, equipo restaurado a condición operativa.','2026-07-15 08:13:01','2026-07-16 19:13:01','2026-07-17 03:13:01','2026-09-02 03:42:40','2026-09-02 03:42:40'),(21,'PN0021',7,NULL,4,5,'correctivo','interno',NULL,'baja','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-03-17 07:04:42','2026-03-18 00:04:42','2026-03-19 03:04:42','2026-09-02 03:42:40','2026-09-02 03:42:40'),(22,'PN0022',7,NULL,4,5,'correctivo','externo',3,'urgente','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-05-30 05:17:55','2026-05-31 02:17:55','2026-06-01 05:17:55','2026-09-02 03:42:40','2026-09-02 03:42:40'),(23,'PN0023',7,NULL,4,NULL,'correctivo','interno',NULL,'alta','abierta','El motor no enciende',NULL,'2026-08-25 03:42:40',NULL,NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(24,'PN0024',8,NULL,5,5,'correctivo','externo',4,'alta','completada','El motor no enciende','Reparación completada, equipo restaurado a condición operativa.','2026-06-09 09:33:41','2026-06-09 10:33:41','2026-06-12 00:33:41','2026-09-02 03:42:40','2026-09-02 03:42:40'),(25,'PN0025',8,NULL,5,5,'correctivo','interno',NULL,'media','completada','El motor no enciende','Reparación completada, equipo restaurado a condición operativa.','2026-03-14 05:06:01','2026-03-15 17:06:01','2026-03-16 12:06:01','2026-09-02 03:42:40','2026-09-02 03:42:40'),(26,'PN0026',9,NULL,4,5,'correctivo','externo',1,'baja','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-04-18 15:27:19','2026-04-19 04:27:19','2026-04-20 01:27:19','2026-09-02 03:42:40','2026-09-02 03:42:40'),(27,'PN0027',9,NULL,4,5,'correctivo','interno',NULL,'urgente','completada','Sobrecalentamiento','Reparación completada, equipo restaurado a condición operativa.','2026-07-22 20:43:39','2026-07-24 19:43:39','2026-07-26 15:43:39','2026-09-02 03:42:40','2026-09-02 03:42:40'),(28,'PN0028',9,NULL,4,5,'correctivo','interno',NULL,'media','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-03-11 04:37:27','2026-03-12 01:37:27','2026-03-12 03:37:27','2026-09-02 03:42:40','2026-09-02 03:42:40'),(29,'PN0029',9,NULL,4,5,'correctivo','externo',2,'baja','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-08-07 02:29:37','2026-08-07 06:29:37','2026-08-08 03:29:37','2026-09-02 03:42:40','2026-09-02 03:42:40'),(30,'PN0030',10,NULL,5,5,'correctivo','interno',NULL,'alta','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-05-31 06:32:37','2026-06-01 08:32:37','2026-06-04 01:32:37','2026-09-02 03:42:40','2026-09-02 03:42:40'),(31,'PN0031',10,NULL,5,5,'correctivo','interno',NULL,'media','completada','El motor no enciende','Reparación completada, equipo restaurado a condición operativa.','2026-06-10 18:51:52','2026-06-11 09:51:52','2026-06-14 03:51:52','2026-09-02 03:42:40','2026-09-02 03:42:40'),(32,'PN0032',10,NULL,5,5,'correctivo','interno',NULL,'urgente','completada','Sobrecalentamiento','Reparación completada, equipo restaurado a condición operativa.','2026-08-17 20:51:50','2026-08-19 03:51:50','2026-08-21 22:51:50','2026-09-02 03:42:40','2026-09-02 03:42:40'),(33,'PN0033',10,NULL,5,5,'correctivo','interno',NULL,'baja','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-04-30 13:34:11','2026-05-01 03:34:11','2026-05-02 14:34:11','2026-09-02 03:42:40','2026-09-02 03:42:40'),(34,'PN0034',10,NULL,5,5,'correctivo','interno',NULL,'alta','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-05-01 10:48:54','2026-05-02 14:48:54','2026-05-03 23:48:54','2026-09-02 03:42:40','2026-09-02 03:42:40'),(35,'PN0035',10,NULL,5,NULL,'correctivo','interno',NULL,'alta','abierta','Vibración excesiva',NULL,'2026-08-28 03:42:40',NULL,NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(36,'PN0036',11,NULL,5,4,'correctivo','interno',NULL,'baja','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-06-22 07:16:49','2026-06-23 09:16:49','2026-06-25 10:16:49','2026-09-02 03:42:40','2026-09-02 03:42:40'),(37,'PN0037',11,NULL,5,4,'correctivo','interno',NULL,'media','completada','Sobrecalentamiento','Reparación completada, equipo restaurado a condición operativa.','2026-09-01 14:58:41','2026-09-02 06:58:41','2026-09-03 12:58:41','2026-09-02 03:42:40','2026-09-02 03:42:40'),(38,'PN0038',11,NULL,5,4,'correctivo','interno',NULL,'alta','completada','El motor no enciende','Reparación completada, equipo restaurado a condición operativa.','2026-04-08 05:25:40','2026-04-08 22:25:40','2026-04-10 16:25:40','2026-09-02 03:42:40','2026-09-02 03:42:40'),(39,'PN0039',11,NULL,5,4,'correctivo','interno',NULL,'baja','completada','Sobrecalentamiento','Reparación completada, equipo restaurado a condición operativa.','2026-04-23 20:47:12','2026-04-25 09:47:12','2026-04-27 21:47:12','2026-09-02 03:42:40','2026-09-02 03:42:40'),(40,'PN0040',11,NULL,5,4,'correctivo','interno',NULL,'media','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-04-29 22:00:06','2026-04-30 17:00:06','2026-05-02 17:00:06','2026-09-02 03:42:40','2026-09-02 03:42:40'),(41,'PN0041',12,NULL,4,4,'correctivo','interno',NULL,'media','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-03-06 12:01:16','2026-03-07 19:01:16','2026-03-09 01:01:16','2026-09-02 03:42:40','2026-09-02 03:42:40'),(42,'PN0042',12,NULL,4,4,'correctivo','externo',1,'baja','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-03-08 01:21:34','2026-03-08 10:21:34','2026-03-09 17:21:34','2026-09-02 03:42:40','2026-09-02 03:42:40'),(43,'PN0043',2,1,4,5,'preventivo','interno',NULL,'media','completada',NULL,'Reparación completada, equipo restaurado a condición operativa.','2026-05-30 03:42:40','2026-05-31 03:42:40','2026-06-02 23:42:40','2026-09-02 03:42:40','2026-09-02 03:42:40'),(44,'PN0044',5,2,6,4,'preventivo','interno',NULL,'alta','completada',NULL,'Reparación completada, equipo restaurado a condición operativa.','2026-08-21 03:42:40','2026-08-22 11:42:40','2026-08-23 20:42:40','2026-09-02 03:42:40','2026-09-02 03:42:40'),(45,'PN0045',6,3,6,4,'preventivo','interno',NULL,'alta','completada',NULL,'Reparación completada, equipo restaurado a condición operativa.','2026-07-29 03:42:40','2026-07-29 21:42:40','2026-08-01 14:42:40','2026-09-02 03:42:40','2026-09-02 03:42:40'),(46,'PN0046',7,4,5,5,'preventivo','interno',NULL,'urgente','completada',NULL,'Reparación completada, equipo restaurado a condición operativa.','2026-08-21 03:42:40','2026-08-21 16:42:40','2026-08-23 01:42:40','2026-09-02 03:42:40','2026-09-02 03:42:40'),(47,'PN0047',8,5,4,5,'preventivo','interno',NULL,'media','completada',NULL,'Reparación completada, equipo restaurado a condición operativa.','2026-08-21 03:42:40','2026-08-21 10:42:40','2026-08-23 17:42:40','2026-09-02 03:42:40','2026-09-02 03:42:40'),(48,'PN0048',10,6,6,5,'preventivo','interno',NULL,'baja','completada',NULL,'Reparación completada, equipo restaurado a condición operativa.','2026-08-21 03:42:40','2026-08-22 06:42:40','2026-08-22 12:42:40','2026-09-02 03:42:40','2026-09-02 03:42:40'),(49,'PS0001',13,NULL,10,8,'correctivo','interno',NULL,'alta','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-06-03 16:59:47','2026-06-04 23:59:47','2026-06-05 22:59:47','2026-09-02 03:42:40','2026-09-02 03:42:40'),(50,'PS0002',13,NULL,10,8,'correctivo','interno',NULL,'alta','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-08-29 16:25:42','2026-08-30 12:25:42','2026-08-31 10:25:42','2026-09-02 03:42:40','2026-09-02 03:42:40'),(51,'PS0003',13,NULL,10,NULL,'correctivo','interno',NULL,'alta','abierta','Ruido anormal en el equipo',NULL,'2026-08-30 03:42:40',NULL,NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(52,'PS0004',14,NULL,10,9,'correctivo','interno',NULL,'urgente','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-05-27 22:45:34','2026-05-29 11:45:34','2026-05-31 09:45:34','2026-09-02 03:42:40','2026-09-02 03:42:40'),(53,'PS0005',14,NULL,10,9,'correctivo','interno',NULL,'media','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-06-01 20:50:51','2026-06-03 10:50:51','2026-06-06 09:50:51','2026-09-02 03:42:40','2026-09-02 03:42:40'),(54,'PS0006',14,NULL,10,9,'correctivo','interno',NULL,'baja','completada','Sobrecalentamiento','Reparación completada, equipo restaurado a condición operativa.','2026-08-16 19:36:40','2026-08-17 11:36:40','2026-08-19 21:36:40','2026-09-02 03:42:40','2026-09-02 03:42:40'),(55,'PS0007',14,NULL,10,9,'correctivo','interno',NULL,'alta','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-05-19 09:05:40','2026-05-20 18:05:40','2026-05-21 11:05:40','2026-09-02 03:42:40','2026-09-02 03:42:40'),(56,'PS0008',15,NULL,8,8,'correctivo','interno',NULL,'alta','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-04-14 10:03:00','2026-04-14 19:03:00','2026-04-15 11:03:00','2026-09-02 03:42:40','2026-09-02 03:42:40'),(57,'PS0009',15,NULL,8,8,'correctivo','interno',NULL,'alta','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-04-12 14:14:16','2026-04-13 02:14:16','2026-04-13 06:14:16','2026-09-02 03:42:40','2026-09-02 03:42:40'),(58,'PS0010',15,NULL,9,NULL,'correctivo','interno',NULL,'alta','abierta','Sobrecalentamiento',NULL,'2026-08-24 03:42:40',NULL,NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(59,'PS0011',16,NULL,8,9,'correctivo','interno',NULL,'baja','completada','Ruido anormal en el equipo','Reparación completada, equipo restaurado a condición operativa.','2026-03-14 11:41:22','2026-03-15 10:41:22','2026-03-15 19:41:22','2026-09-02 03:42:40','2026-09-02 03:42:40'),(60,'PS0012',16,NULL,8,9,'correctivo','interno',NULL,'baja','completada','El motor no enciende','Reparación completada, equipo restaurado a condición operativa.','2026-07-02 01:52:33','2026-07-03 10:52:33','2026-07-06 00:52:33','2026-09-02 03:42:40','2026-09-02 03:42:40'),(61,'PS0013',16,NULL,8,9,'correctivo','interno',NULL,'media','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-06-20 15:06:53','2026-06-22 12:06:53','2026-06-22 17:06:53','2026-09-02 03:42:40','2026-09-02 03:42:40'),(62,'PS0014',16,NULL,8,9,'correctivo','interno',NULL,'alta','completada','Ruido anormal en el equipo','Reparación completada, equipo restaurado a condición operativa.','2026-03-18 14:15:40','2026-03-19 02:15:40','2026-03-22 02:15:40','2026-09-02 03:42:40','2026-09-02 03:42:40'),(63,'PS0015',16,NULL,8,9,'correctivo','interno',NULL,'baja','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-06-27 22:17:38','2026-06-28 19:17:38','2026-06-30 09:17:38','2026-09-02 03:42:40','2026-09-02 03:42:40'),(64,'PS0016',17,NULL,10,9,'correctivo','interno',NULL,'baja','completada','El motor no enciende','Reparación completada, equipo restaurado a condición operativa.','2026-04-12 04:13:04','2026-04-13 18:13:04','2026-04-14 06:13:04','2026-09-02 03:42:40','2026-09-02 03:42:40'),(65,'PS0017',17,NULL,10,9,'correctivo','externo',1,'alta','completada','Sobrecalentamiento','Reparación completada, equipo restaurado a condición operativa.','2026-08-26 17:21:47','2026-08-26 18:21:47','2026-08-29 11:21:47','2026-09-02 03:42:40','2026-09-02 03:42:41'),(66,'PS0018',17,NULL,9,NULL,'correctivo','interno',NULL,'alta','abierta','Ruido anormal en el equipo',NULL,'2026-08-26 03:42:40',NULL,NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(67,'PS0019',18,NULL,8,9,'correctivo','interno',NULL,'baja','completada','Sobrecalentamiento','Reparación completada, equipo restaurado a condición operativa.','2026-03-26 02:08:17','2026-03-27 04:08:17','2026-03-28 10:08:17','2026-09-02 03:42:40','2026-09-02 03:42:40'),(68,'PS0020',18,NULL,8,9,'correctivo','interno',NULL,'baja','completada','El motor no enciende','Reparación completada, equipo restaurado a condición operativa.','2026-08-26 00:39:40','2026-08-27 06:39:40','2026-08-29 20:39:40','2026-09-02 03:42:40','2026-09-02 03:42:40'),(69,'PS0021',19,NULL,9,8,'correctivo','interno',NULL,'baja','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-06-15 17:42:24','2026-06-16 12:42:24','2026-06-16 15:42:24','2026-09-02 03:42:40','2026-09-02 03:42:40'),(70,'PS0022',19,NULL,9,8,'correctivo','externo',2,'urgente','completada','El motor no enciende','Reparación completada, equipo restaurado a condición operativa.','2026-06-20 11:29:51','2026-06-22 06:29:51','2026-06-24 06:29:51','2026-09-02 03:42:40','2026-09-02 03:42:41'),(71,'PS0023',19,NULL,9,8,'correctivo','interno',NULL,'urgente','completada','Sobrecalentamiento','Reparación completada, equipo restaurado a condición operativa.','2026-05-17 08:10:22','2026-05-18 14:10:22','2026-05-18 21:10:22','2026-09-02 03:42:40','2026-09-02 03:42:40'),(72,'PS0024',19,NULL,10,NULL,'correctivo','interno',NULL,'alta','abierta','El motor no enciende',NULL,'2026-08-26 03:42:40',NULL,NULL,'2026-09-02 03:42:40','2026-09-02 03:42:40'),(73,'PS0025',20,NULL,10,9,'correctivo','interno',NULL,'media','completada','El motor no enciende','Reparación completada, equipo restaurado a condición operativa.','2026-04-06 04:41:24','2026-04-07 13:41:24','2026-04-08 17:41:24','2026-09-02 03:42:41','2026-09-02 03:42:41'),(74,'PS0026',20,NULL,10,9,'correctivo','interno',NULL,'urgente','completada','Sobrecalentamiento','Reparación completada, equipo restaurado a condición operativa.','2026-06-19 09:16:03','2026-06-20 18:16:03','2026-06-22 17:16:03','2026-09-02 03:42:41','2026-09-02 03:42:41'),(75,'PS0027',20,NULL,10,9,'correctivo','externo',4,'baja','completada','El motor no enciende','Reparación completada, equipo restaurado a condición operativa.','2026-07-16 10:58:47','2026-07-17 12:58:47','2026-07-20 11:58:47','2026-09-02 03:42:41','2026-09-02 03:42:41'),(76,'PS0028',21,NULL,9,8,'correctivo','interno',NULL,'media','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-06-05 06:13:36','2026-06-06 21:13:36','2026-06-08 03:13:36','2026-09-02 03:42:41','2026-09-02 03:42:41'),(77,'PS0029',21,NULL,9,8,'correctivo','interno',NULL,'baja','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-08-08 04:31:11','2026-08-09 06:31:11','2026-08-11 10:31:11','2026-09-02 03:42:41','2026-09-02 03:42:41'),(78,'PS0030',21,NULL,9,8,'correctivo','interno',NULL,'alta','completada','Sobrecalentamiento','Reparación completada, equipo restaurado a condición operativa.','2026-06-13 18:26:04','2026-06-13 21:26:04','2026-06-13 23:26:04','2026-09-02 03:42:41','2026-09-02 03:42:41'),(79,'PS0031',22,NULL,9,9,'correctivo','interno',NULL,'media','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-04-20 13:22:37','2026-04-21 04:22:37','2026-04-23 14:22:37','2026-09-02 03:42:41','2026-09-02 03:42:41'),(80,'PS0032',22,NULL,9,9,'correctivo','interno',NULL,'media','completada','Ruido anormal en el equipo','Reparación completada, equipo restaurado a condición operativa.','2026-08-03 22:59:56','2026-08-04 16:59:56','2026-08-07 02:59:56','2026-09-02 03:42:41','2026-09-02 03:42:41'),(81,'PS0033',22,NULL,9,9,'correctivo','interno',NULL,'alta','completada','El motor no enciende','Reparación completada, equipo restaurado a condición operativa.','2026-05-06 20:58:22','2026-05-07 11:58:22','2026-05-10 06:58:22','2026-09-02 03:42:41','2026-09-02 03:42:41'),(82,'PS0034',23,NULL,8,8,'correctivo','externo',2,'urgente','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-03-18 22:24:56','2026-03-19 02:24:56','2026-03-20 12:24:56','2026-09-02 03:42:41','2026-09-02 03:42:41'),(83,'PS0035',23,NULL,8,8,'correctivo','interno',NULL,'urgente','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-03-22 21:54:45','2026-03-24 09:54:45','2026-03-24 10:54:45','2026-09-02 03:42:41','2026-09-02 03:42:41'),(84,'PS0036',23,NULL,8,8,'correctivo','interno',NULL,'alta','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-07-15 03:41:14','2026-07-16 05:41:14','2026-07-16 17:41:14','2026-09-02 03:42:41','2026-09-02 03:42:41'),(85,'PS0037',23,NULL,8,8,'correctivo','externo',1,'baja','completada','Ruido anormal en el equipo','Reparación completada, equipo restaurado a condición operativa.','2026-08-24 12:02:51','2026-08-25 16:02:51','2026-08-27 09:02:51','2026-09-02 03:42:41','2026-09-02 03:42:41'),(86,'PS0038',23,NULL,8,8,'correctivo','interno',NULL,'baja','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-03-20 04:06:07','2026-03-21 13:06:07','2026-03-21 21:06:07','2026-09-02 03:42:41','2026-09-02 03:42:41'),(87,'PS0039',23,NULL,10,NULL,'correctivo','interno',NULL,'alta','abierta','El motor no enciende',NULL,'2026-08-24 03:42:41',NULL,NULL,'2026-09-02 03:42:41','2026-09-02 03:42:41'),(88,'PS0040',24,NULL,9,8,'correctivo','externo',3,'urgente','completada','Fuga de aceite','Reparación completada, equipo restaurado a condición operativa.','2026-04-01 15:36:32','2026-04-03 07:36:32','2026-04-03 21:36:32','2026-09-02 03:42:41','2026-09-02 03:42:41'),(89,'PS0041',24,NULL,9,8,'correctivo','interno',NULL,'media','completada','Vibración excesiva','Reparación completada, equipo restaurado a condición operativa.','2026-03-04 07:21:51','2026-03-05 04:21:51','2026-03-05 19:21:51','2026-09-02 03:42:41','2026-09-02 03:42:41'),(90,'PS0042',24,NULL,9,8,'correctivo','interno',NULL,'alta','completada','El motor no enciende','Reparación completada, equipo restaurado a condición operativa.','2026-08-29 21:34:27','2026-08-30 03:34:27','2026-08-31 23:34:27','2026-09-02 03:42:41','2026-09-02 03:42:41'),(91,'PS0043',24,NULL,9,NULL,'correctivo','interno',NULL,'alta','abierta','Vibración excesiva',NULL,'2026-08-31 03:42:41',NULL,NULL,'2026-09-02 03:42:41','2026-09-02 03:42:41'),(92,'PS0044',13,7,9,8,'preventivo','interno',NULL,'media','completada',NULL,'Reparación completada, equipo restaurado a condición operativa.','2026-05-30 03:42:41','2026-05-30 05:42:41','2026-06-01 09:42:41','2026-09-02 03:42:41','2026-09-02 03:42:41'),(93,'PS0045',16,8,9,8,'preventivo','interno',NULL,'media','completada',NULL,'Reparación completada, equipo restaurado a condición operativa.','2026-08-13 03:42:41','2026-08-14 04:42:41','2026-08-15 21:42:41','2026-09-02 03:42:41','2026-09-02 03:42:41'),(94,'PS0046',17,9,8,8,'preventivo','interno',NULL,'media','completada',NULL,'Reparación completada, equipo restaurado a condición operativa.','2026-08-21 03:42:41','2026-08-22 15:42:41','2026-08-22 16:42:41','2026-09-02 03:42:41','2026-09-02 03:42:41'),(95,'PS0047',18,10,8,8,'preventivo','interno',NULL,'baja','completada',NULL,'Reparación completada, equipo restaurado a condición operativa.','2026-08-13 03:42:41','2026-08-13 11:42:41','2026-08-14 03:42:41','2026-09-02 03:42:41','2026-09-02 03:42:41'),(96,'PS0048',21,11,10,9,'preventivo','interno',NULL,'media','completada',NULL,'Reparación completada, equipo restaurado a condición operativa.','2026-08-21 03:42:41','2026-08-21 13:42:41','2026-08-22 09:42:41','2026-09-02 03:42:41','2026-09-02 03:42:41'),(97,'PS0049',22,12,9,9,'preventivo','interno',NULL,'media','completada',NULL,'Reparación completada, equipo restaurado a condición operativa.','2026-07-29 03:42:41','2026-07-29 16:42:41','2026-07-30 04:42:41','2026-09-02 03:42:41','2026-09-02 03:42:41'),(98,'PS0050',23,NULL,1,NULL,'preventivo','externo',2,'alta','abierta','ok',NULL,'2026-09-02 03:55:22',NULL,NULL,'2026-09-02 03:55:22','2026-09-02 03:55:22');
/*!40000 ALTER TABLE `work_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'mantenimiento_industrial'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-01 23:49:51
