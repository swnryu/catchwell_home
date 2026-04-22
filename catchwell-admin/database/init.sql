/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `TB_ESTIMATE_KAKAO` (
  `SENDING` text NOT NULL,
  `DATE` text NOT NULL,
  `CMID` text NOT NULL,
  `STATUS` text NOT NULL,
  `TID_NO` varchar(20) DEFAULT NULL,
  `PHONE` text NOT NULL,
  `ESTIMATE` text NOT NULL,
  `RSLT` text NOT NULL,
  `MSG_RSLT` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `TB_EVENT_KAKAO` (
  `SENDING` text DEFAULT NULL,
  `DATE` text,
  `CMID` text DEFAULT NULL,
  `STATUS` text DEFAULT NULL,
  `OID_NO` varchar(40) DEFAULT NULL,
  `PHONE` text DEFAULT NULL,
  `CALLBACK` text DEFAULT NULL,
  `RSLT` text DEFAULT NULL,
  `MSG_RSLT` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `TB_INICIS_NOTI` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `P_TID` text NOT NULL,
  `P_MID` text NOT NULL,
  `P_AUTH_DT` text NOT NULL,
  `P_STATUS` text NOT NULL,
  `P_TYPE` text NOT NULL,
  `P_OID` text NOT NULL,
  `P_FN_CD1` text NOT NULL,
  `P_FN_CD2` text NOT NULL,
  `P_FN_NM` text NOT NULL,
  `P_AMT` text NOT NULL,
  `P_UNAME` text NOT NULL,
  `P_RMESG1` text NOT NULL,
  `P_RMESG2` text NOT NULL,
  `P_NOTI` text NOT NULL,
  `P_AUTH_NO` text NOT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=8534 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `TB_INICIS_RETURN` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `P_STATUS` text NOT NULL,
  `P_RMESG1` text NOT NULL,
  `P_TID` text NOT NULL,
  `P_UNAME` text NOT NULL,
  `P_OID` text NOT NULL,
  `P_AMT` text NOT NULL,
  `P_AUTH_DT` text NOT NULL,
  `P_FN_NM` text NOT NULL,
  `P_VACT_NUM` text NOT NULL,
  `P_VACT_NAME` text NOT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=8889 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `TB_REPORT_KAKAO` (
  `SENDING` text DEFAULT NULL,
  `DATE` text DEFAULT NULL,
  `CMID` text DEFAULT NULL,
  `STATUS` text DEFAULT NULL,
  `REG_NO` varchar(20) DEFAULT NULL,
  `PHONE` text DEFAULT NULL,
  `CALLBACK` text DEFAULT NULL,
  `RSLT` text DEFAULT NULL,
  `MSG_RSLT` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `TB_SHIPMENT_KAKAO` (
  `SENDING` text DEFAULT NULL,
  `DATE` text,
  `CMID` text DEFAULT NULL,
  `STATUS` text DEFAULT NULL,
  `TID_NO` varchar(20) DEFAULT NULL,
  `PHONE` text DEFAULT NULL,
  `CALLBACK` text DEFAULT NULL,
  `RSLT` text DEFAULT NULL,
  `MSG_RSLT` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_account` (
  `idx` int(11) NOT NULL,
  `admin_userid` varchar(20) NOT NULL,
  `admin_passwd` varchar(256) NOT NULL,
  `admin_name` varchar(20) NOT NULL,
  `admin_phone` varchar(20) DEFAULT NULL,
  `admin_email` varchar(50) DEFAULT NULL,
  `permission` int(11) DEFAULT NULL,
  `pw_last_update` timestamp NULL DEFAULT NULL,
  `reserved` int(11) DEFAULT NULL,
  PRIMARY KEY (`admin_userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_log` (
  `userid` varchar(20) NOT NULL,
  `contents` varchar(40) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `udate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `comment` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `as_parcel_service` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `reg_num` varchar(20) NOT NULL,
  `reg_date` date NOT NULL DEFAULT (CURRENT_DATE),
  `update_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `process_state` int(11) NOT NULL,
  `parcel_num` varchar(20) DEFAULT NULL,
  `customer_name` varchar(50) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_addr` varchar(50) NOT NULL,
  `customer_addr_detail` varchar(50) NOT NULL,
  `customer_zipcode` int(11) NOT NULL,
  `customer_addr_return` varchar(50) DEFAULT NULL,
  `customer_addr_detail_return` varchar(50) DEFAULT NULL,
  `customer_zipcode_return` int(11) DEFAULT NULL,
  `parcel_num_return` varchar(20) DEFAULT NULL,
  `customer_desc` text DEFAULT NULL,
  `broken_type` varchar(20) DEFAULT NULL,
  `attached_files` varchar(50) DEFAULT NULL,
  `parcel_memo` varchar(50) DEFAULT NULL,
  `parcel_memo_return` varchar(50) DEFAULT NULL,
  `parcel_date` date DEFAULT NULL,
  `product_type` varchar(20) DEFAULT NULL,
  `product_name` varchar(20) NOT NULL,
  `product_date` date DEFAULT '2020-01-01',
  `admin_memo` text DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `admin_desc` text DEFAULT NULL,
  `pic_name` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`idx`),
  UNIQUE KEY `reg_num` (`reg_num`),
  KEY `idx` (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=81114 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `as_parcel_service_backup` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `reg_num` varchar(20) NOT NULL,
  `reg_date` date NOT NULL DEFAULT (CURRENT_DATE),
  `update_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `process_state` int(11) NOT NULL,
  `parcel_num` varchar(20) DEFAULT NULL,
  `customer_name` varchar(50) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_addr` varchar(50) NOT NULL,
  `customer_addr_detail` varchar(50) NOT NULL,
  `customer_zipcode` int(11) NOT NULL,
  `customer_addr_return` varchar(50) DEFAULT NULL,
  `customer_addr_detail_return` varchar(50) DEFAULT NULL,
  `customer_zipcode_return` int(11) DEFAULT NULL,
  `parcel_num_return` varchar(20) DEFAULT NULL,
  `customer_desc` text DEFAULT NULL,
  `broken_type` varchar(20) DEFAULT NULL,
  `attached_files` varchar(50) DEFAULT NULL,
  `parcel_memo` varchar(50) DEFAULT NULL,
  `parcel_memo_return` varchar(50) DEFAULT NULL,
  `parcel_date` date DEFAULT NULL,
  `product_type` varchar(20) DEFAULT NULL,
  `product_name` varchar(20) NOT NULL,
  `product_date` date DEFAULT '2020-01-01',
  `admin_memo` text DEFAULT NULL,
  `price` int(11) DEFAULT 0,
  `admin_desc` text DEFAULT NULL,
  `pic_name` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`idx`),
  UNIQUE KEY `reg_num` (`reg_num`),
  KEY `idx` (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=6104 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cancellation_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cancellation_idx` int(10) unsigned NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `saved_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cancellation_order` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `model_name` varchar(40) NOT NULL,
  `order_id` varchar(40) NOT NULL,
  `order_id_sabangnet` varchar(40) DEFAULT NULL,
  `customer_name` varchar(40) NOT NULL,
  `shopping_mall` varchar(40) NOT NULL,
  `address` varchar(200) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `tracking` varchar(20) DEFAULT NULL,
  `type` varchar(80) NOT NULL,
  `reason` text NOT NULL,
  `serial` varchar(40) NOT NULL,
  `memo` text NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `date_completed` date NOT NULL,
  `result_type` int(11) DEFAULT NULL,
  `result_memo` varchar(80) DEFAULT NULL,
  `admin_name` varchar(20) NOT NULL,
  `exchange_order` varchar(16) DEFAULT NULL,
  `company_name` varchar(40) DEFAULT NULL,
  `shipping_date` date DEFAULT NULL,
  `exchange_tracking_number` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=4193 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cancellation_order_sales` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `model_name` varchar(40) NOT NULL,
  `order_id` varchar(40) NOT NULL,
  `order_id_sabangnet` varchar(40) DEFAULT NULL,
  `customer_name` varchar(40) NOT NULL,
  `shopping_mall` varchar(40) NOT NULL,
  `address` varchar(200) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `tracking` varchar(20) DEFAULT NULL,
  `type` varchar(80) NOT NULL,
  `reason` text NOT NULL,
  `serial` varchar(40) NOT NULL,
  `memo` text NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `date_completed` date NOT NULL,
  `result_type` int(11) DEFAULT NULL,
  `result_memo` varchar(80) DEFAULT NULL,
  `admin_name` varchar(20) NOT NULL,
  `exchange_order` varchar(16) DEFAULT NULL,
  `company_name` varchar(40) DEFAULT NULL,
  `shipping_date` date DEFAULT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=271 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cs_inbound_call` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `reg_datetime` datetime NOT NULL DEFAULT (CURRENT_DATE),
  `product_name` varchar(40) DEFAULT NULL,
  `inquiry_type` int(11) DEFAULT NULL,
  `black_consumer` int(11) NOT NULL DEFAULT 0,
  `black_consumer_desc` text DEFAULT NULL,
  `pic_name` varchar(40) DEFAULT NULL,
  `pic_memo` text DEFAULT NULL,
  `customer_name` varchar(40) DEFAULT NULL,
  `customer_phone` varchar(40) DEFAULT NULL,
  `admin_result` int(11) DEFAULT 0,
  `admin_desc` text DEFAULT NULL,
  `admin_name` varchar(40) DEFAULT NULL,
  `reserved1` int(11) DEFAULT NULL,
  `reserved2` int(11) DEFAULT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=80834 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cs_internal_orders` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `reg_datetime` datetime NOT NULL DEFAULT (CURRENT_DATE),
  `product_name` varchar(40) NOT NULL,
  `parts_name_ex` varchar(128) NOT NULL,
  `parts_name` varchar(256) DEFAULT NULL,
  `parts_count` int(11) NOT NULL,
  `parts_price` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `reason` text NOT NULL,
  `pic_memo` text NOT NULL,
  `pic_name` varchar(40) NOT NULL,
  `customer_name` varchar(40) NOT NULL,
  `customer_phone` varchar(40) NOT NULL,
  `customer_addr` varchar(128) NOT NULL,
  `customer_addr_detail` varchar(128) NOT NULL,
  `customer_zipcode` varchar(8) NOT NULL,
  `delivery_memo` varchar(128) NOT NULL,
  `delivery_num` varchar(40) NOT NULL,
  `reserved1` varchar(80) DEFAULT NULL,
  UNIQUE KEY `idx` (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cs_online_event` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `zip_new` varchar(10) DEFAULT NULL,
  `add1` varchar(100) DEFAULT NULL,
  `add2` varchar(100) DEFAULT NULL,
  `japum` varchar(30) DEFAULT NULL,
  `gdate` varchar(20) DEFAULT NULL,
  `shoppingmall` varchar(30) DEFAULT NULL,
  `id` varchar(40) DEFAULT NULL,
  `nickname` varchar(40) DEFAULT NULL,
  `oid` varchar(40) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `bbs_file` varchar(300) DEFAULT NULL,
  `udate` varchar(30) DEFAULT NULL,
  `tracking_num` varchar(20) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `admin_memo` text DEFAULT NULL,
  `pin_num` varchar(20) DEFAULT NULL,
  `gift` varchar(40) DEFAULT NULL,
  `gift_price` varchar(20) DEFAULT NULL,
  `gift_quantity` int(11) DEFAULT NULL,
  `expiry_date` varchar(40) DEFAULT NULL,
  `mms_senddate` varchar(30) DEFAULT NULL,
  `company_name` varchar(40) DEFAULT NULL,
  `delivery_message` varchar(100) DEFAULT NULL,
  `gift_date` varchar(30) DEFAULT NULL,
  `gift_add` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=43415 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cs_online_event_backup` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `zip_new` varchar(10) DEFAULT NULL,
  `add1` varchar(100) DEFAULT NULL,
  `add2` varchar(100) DEFAULT NULL,
  `japum` varchar(30) DEFAULT NULL,
  `gdate` varchar(20) DEFAULT NULL,
  `shoppingmall` varchar(30) DEFAULT NULL,
  `id` varchar(40) DEFAULT NULL,
  `nickname` varchar(20) DEFAULT NULL,
  `oid` varchar(40) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `bbs_file` varchar(100) DEFAULT NULL,
  `udate` varchar(30) DEFAULT NULL,
  `tracking_num` varchar(20) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `admin_memo` text DEFAULT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cs_online_event_sniper` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `zip_new` varchar(10) DEFAULT NULL,
  `add1` varchar(100) DEFAULT NULL,
  `add2` varchar(100) DEFAULT NULL,
  `japum` varchar(30) DEFAULT NULL,
  `gdate` varchar(20) DEFAULT NULL,
  `shoppingmall` varchar(30) DEFAULT NULL,
  `id` varchar(40) DEFAULT NULL,
  `nickname` varchar(40) DEFAULT NULL,
  `oid` varchar(40) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `bbs_file` varchar(100) DEFAULT NULL,
  `udate` varchar(30) DEFAULT NULL,
  `tracking_num` varchar(20) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `admin_memo` text DEFAULT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=182 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cs_shipping_parts` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `reg_datetime` datetime NOT NULL DEFAULT (CURRENT_DATE),
  `product_name` varchar(40) NOT NULL,
  `parts_name_ex` varchar(128) NOT NULL,
  `parts_name` varchar(256) DEFAULT NULL,
  `parts_count` int(11) NOT NULL,
  `parts_price` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `reason` text NOT NULL,
  `pic_memo` text NOT NULL,
  `pic_name` varchar(40) NOT NULL,
  `customer_name` varchar(40) NOT NULL,
  `customer_phone` varchar(40) NOT NULL,
  `customer_addr` varchar(128) NOT NULL,
  `customer_addr_detail` varchar(128) NOT NULL,
  `customer_zipcode` varchar(8) NOT NULL,
  `delivery_memo` varchar(128) NOT NULL,
  `delivery_num` varchar(40) NOT NULL,
  `reserved1` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=13610 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `delivery_package` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL DEFAULT 1,
  `model_name` varchar(128) NOT NULL,
  `box_size` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `reserved` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lab_online_event` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL DEFAULT (CURRENT_DATE),
  `event_name` varchar(100) DEFAULT NULL,
  `homepage_id` varchar(50) DEFAULT NULL,
  `customer_name` varchar(30) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_zipcode` varchar(10) DEFAULT NULL,
  `customer_addr` varchar(100) DEFAULT NULL,
  `customer_addr_detail` varchar(100) DEFAULT NULL,
  `model_name` varchar(30) DEFAULT NULL,
  `market_name` varchar(30) DEFAULT NULL,
  `market_id` varchar(40) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `order_id` varchar(100) DEFAULT NULL,
  `serial_no` varchar(40) DEFAULT NULL,
  `file_name` varchar(100) DEFAULT NULL,
  `tracking_num` varchar(20) DEFAULT NULL,
  `status` int(11) DEFAULT 0,
  `gift` varchar(100) DEFAULT NULL,
  `memo` text DEFAULT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=460 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lab_online_event_backup` (
  `idx` int(11) NOT NULL,
  `date` date NOT NULL DEFAULT (CURRENT_DATE),
  `event_name` varchar(100) DEFAULT NULL,
  `homepage_id` varchar(50) DEFAULT NULL,
  `customer_name` varchar(30) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_zipcode` varchar(10) DEFAULT NULL,
  `customer_addr` varchar(100) DEFAULT NULL,
  `customer_addr_detail` varchar(100) DEFAULT NULL,
  `model_name` varchar(30) DEFAULT NULL,
  `market_name` varchar(30) DEFAULT NULL,
  `market_id` varchar(40) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `order_id` varchar(100) DEFAULT NULL,
  `serial_no` varchar(40) DEFAULT NULL,
  `file_name` varchar(100) DEFAULT NULL,
  `tracking_num` varchar(20) DEFAULT NULL,
  `status` int(11) DEFAULT 0,
  `gift` varchar(100) DEFAULT NULL,
  `memo` text DEFAULT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_category` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(40) NOT NULL,
  `model_name` text NOT NULL,
  `model_name_as` text NOT NULL,
  `type` int(11) DEFAULT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products_internal_sales` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(100) NOT NULL,
  `product_price` int(11) NOT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipping_date_new` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `model` varchar(80) DEFAULT NULL,
  `serial` varchar(50) DEFAULT NULL,
  `date` varchar(10) DEFAULT NULL,
  `memo` varchar(40) DEFAULT NULL,
  `mall` varchar(40) DEFAULT NULL,
  `orderid` varchar(40) DEFAULT NULL,
  `orderid_sabangnet` varchar(40) DEFAULT NULL,
  `name` varchar(40) DEFAULT NULL,
  `phone1` varchar(20) DEFAULT NULL,
  `phone2` varchar(20) DEFAULT NULL,
  `address` varchar(160) DEFAULT NULL,
  `tracking` varchar(20) DEFAULT NULL,
  `deliverymemo` varchar(300) DEFAULT NULL,
  `status` int(11) DEFAULT 0,
  `filename` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=390111 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
