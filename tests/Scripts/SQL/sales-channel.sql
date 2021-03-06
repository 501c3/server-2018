-- MySQL dump 10.13  Distrib 5.7.22, for Linux (x86_64)
--
-- Host: localhost    Database: sales
-- ------------------------------------------------------
-- Server version	5.7.22-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `channel`
--

LOCK TABLES `channel` WRITE;
/*!40000 ALTER TABLE `channel` DISABLE KEYS */;
INSERT INTO `channel` VALUES (1,'Georgia DanceSport','georgia-dancesport',NULL,NULL,'2018-07-23 19:11:54','{\"date\": \"Sat Sep 15, 2018\", \"name\": \"Georgia DanceSport\", \"location\": \"Ballroom Impact, Sandy Springs, GA\", \"competition\": \"Georgia DanceSport Competition And Medal Exams\"}','data:image/png;base64,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',NULL);
/*!40000 ALTER TABLE `channel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `contact`
--

LOCK TABLES `contact` WRITE;
/*!40000 ALTER TABLE `contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `contact_has_workarea`
--

LOCK TABLES `contact_has_workarea` WRITE;
/*!40000 ALTER TABLE `contact_has_workarea` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_has_workarea` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `form`
--

LOCK TABLES `form` WRITE;
/*!40000 ALTER TABLE `form` DISABLE KEYS */;
/*!40000 ALTER TABLE `form` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
INSERT INTO `inventory` VALUES (1,2,'Per Dance Child','2018-07-23 23:11:54'),(2,2,'Per Dance Adult','2018-07-23 23:11:54'),(3,2,'Exam Per Dance Child','2018-07-23 23:11:54'),(4,2,'Exam Per Dance Adult','2018-07-23 23:11:54'),(5,3,'Spectator Donation Adult','2018-07-23 23:11:54'),(6,3,'Printed Program','2018-07-23 23:11:54');
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `parameters`
--

LOCK TABLES `parameters` WRITE;
/*!40000 ALTER TABLE `parameters` DISABLE KEYS */;
INSERT INTO `parameters` VALUES (1,1,'Mark Garber','mgarber@georgiadancesport.org','2018-07-23 23:11:54');
/*!40000 ALTER TABLE `parameters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `picture`
--

LOCK TABLES `picture` WRITE;
/*!40000 ALTER TABLE `picture` DISABLE KEYS */;
/*!40000 ALTER TABLE `picture` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `picture_has_form`
--

LOCK TABLES `picture_has_form` WRITE;
/*!40000 ALTER TABLE `picture_has_form` DISABLE KEYS */;
/*!40000 ALTER TABLE `picture_has_form` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `pricing`
--

LOCK TABLES `pricing` WRITE;
/*!40000 ALTER TABLE `pricing` DISABLE KEYS */;
INSERT INTO `pricing` VALUES (1,1,'2018-07-01 00:00:00',7.00),(1,1,'2018-09-01 00:00:00',12.00),(1,2,'2018-07-01 00:00:00',12.00),(1,2,'2018-09-01 00:00:00',18.00),(1,3,'2018-07-01 00:00:00',21.00),(1,3,'2018-09-01 00:00:00',30.00),(1,4,'2018-07-01 00:00:00',21.00),(1,4,'2018-09-01 00:00:00',30.00),(1,5,'2018-07-01 00:00:00',15.00),(1,6,'2018-07-01 00:00:00',7.00);
/*!40000 ALTER TABLE `pricing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `processor`
--

LOCK TABLES `processor` WRITE;
/*!40000 ALTER TABLE `processor` DISABLE KEYS */;
INSERT INTO `processor` VALUES (1,'PayPal');
/*!40000 ALTER TABLE `processor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `receipts`
--

LOCK TABLES `receipts` WRITE;
/*!40000 ALTER TABLE `receipts` DISABLE KEYS */;
/*!40000 ALTER TABLE `receipts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,1,4,'\"{\\\"mode\\\":\\\"sandbox\\\",\\\"acct1.clientId\\\":\\\"CLIENT_ID_TEST_PAYPAL\\\",\\\"acct1.clientSecret\\\":\\\"CLIENT_SECRET_TEST_PAYPAL\\\",\\\"http.ConnectionTimeout\\\":2000,\\\"http.Retry\\\":1,\\\"service.EndPoint\\\":\\\"https:\\\\/\\\\/api.sandbox.paypal.com\\\",\\\"log.LogEnabled\\\":true,\\\"log.FileName\\\":\\\"PayPal.log\\\",\\\"log.LogLevel\\\":\\\"DEBUG\\\"}\"'),(1,1,5,'\"{\\\"mode\\\":\\\"live\\\",\\\"acct1.clientId\\\":\\\"CLIENT_ID_PROD_PAYPAL\\\",\\\"acct1.clientSecret\\\":\\\"CLIENT_SECRET_PROD_PAYPAL\\\",\\\"http.ConnectionTimeout\\\":2000,\\\"http.Retry\\\":1,\\\"service.EndPoint\\\":\\\"https:\\\\/\\\\/api.paypal.com\\\",\\\"log.LogEnabled\\\":true,\\\"log.FileName\\\":\\\"PayPal.log\\\",\\\"log.LogLevel\\\":\\\"FINE\\\"}\"');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tag`
--

LOCK TABLES `tag` WRITE;
/*!40000 ALTER TABLE `tag` DISABLE KEYS */;
INSERT INTO `tag` VALUES (1,'monitor'),(2,'participant'),(3,'extra'),(4,'test'),(5,'prod');
/*!40000 ALTER TABLE `tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `workarea`
--

LOCK TABLES `workarea` WRITE;
/*!40000 ALTER TABLE `workarea` DISABLE KEYS */;
/*!40000 ALTER TABLE `workarea` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-23 19:14:22
