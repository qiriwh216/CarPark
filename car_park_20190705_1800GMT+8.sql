#
# SQL Export
# Created by Querious (201024)
# Created: July 5, 2019 at 6:00:58 PM GMT+8
# Encoding: Unicode (UTF-8)
#


SET @PREVIOUS_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;


DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `order`;
DROP TABLE IF EXISTS `favorite`;
DROP TABLE IF EXISTS `community`;
DROP TABLE IF EXISTS `car_park`;


CREATE TABLE `car_park` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `community_id` int(11) NOT NULL COMMENT '所属小区id',
  `no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '编号',
  `floor_number` tinyint(4) NOT NULL DEFAULT '1' COMMENT '楼层，1=负一层，2=负二层，3=负三层',
  `is_sale` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否售卖 1：在售  0 ：售完',
  `price` decimal(8,2) NOT NULL COMMENT '价格',
  `size` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '面积',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `car_parks_community_id_index` (`community_id`),
  KEY `car_parks_created_at_index` (`created_at`),
  KEY `car_parks_updated_at_index` (`updated_at`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `community` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '小区名称',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片',
  `province` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '所在省份',
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '所在城市',
  `community` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `building` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avg_price` decimal(8,2) NOT NULL COMMENT '平均价格',
  `back_cash` decimal(8,2) NOT NULL COMMENT '返现金额',
  `latitude` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '纬度',
  `longitude` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '经度',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `communities_created_at_index` (`created_at`),
  KEY `communities_updated_at_index` (`updated_at`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `favorite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0',
  `community_id` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no` varchar(128) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `user_id` int(11) DEFAULT '0',
  `car_park_id` int(11) DEFAULT '0',
  `paid_at` varchar(128) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '支付时间',
  `payment_method` varchar(128) CHARACTER SET utf8mb4 DEFAULT NULL,
  `payment_no` varchar(128) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '支付平台订单号',
  `pay_status` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refund_status` varchar(128) CHARACTER SET utf8mb4 DEFAULT NULL,
  `refund_no` varchar(128) CHARACTER SET utf8mb4 DEFAULT NULL,
  `closed` varchar(128) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '订单是否已关闭',
  `price` decimal(8,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no` (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `id_card` varchar(128) CHARACTER SET utf8mb4 DEFAULT NULL,
  `oa_id` varchar(128) CHARACTER SET utf8mb4 DEFAULT NULL,
  `floor` varchar(128) CHARACTER SET utf8mb4 DEFAULT NULL,
  `house_number` varchar(128) CHARACTER SET utf8mb4 DEFAULT NULL,
  `wx_phone` varchar(128) CHARACTER SET utf8mb4 DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `weixin_openid` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `weapp_openid` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `weixin_session_key` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `weixin_unionid` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `is_employee` tinyint(4) DEFAULT '0' COMMENT '是否是员工',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  UNIQUE KEY `users_weixin_openid_unique` (`weixin_openid`),
  UNIQUE KEY `users_weixin_unionid_unique` (`weixin_unionid`),
  UNIQUE KEY `users_weapp_openid_unique` (`weapp_openid`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




SET FOREIGN_KEY_CHECKS = @PREVIOUS_FOREIGN_KEY_CHECKS;


SET @PREVIOUS_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;


LOCK TABLES `car_park` WRITE;
ALTER TABLE `car_park` DISABLE KEYS;
ALTER TABLE `car_park` ENABLE KEYS;
UNLOCK TABLES;


LOCK TABLES `community` WRITE;
ALTER TABLE `community` DISABLE KEYS;
ALTER TABLE `community` ENABLE KEYS;
UNLOCK TABLES;


LOCK TABLES `favorite` WRITE;
ALTER TABLE `favorite` DISABLE KEYS;
ALTER TABLE `favorite` ENABLE KEYS;
UNLOCK TABLES;


LOCK TABLES `order` WRITE;
ALTER TABLE `order` DISABLE KEYS;
ALTER TABLE `order` ENABLE KEYS;
UNLOCK TABLES;


LOCK TABLES `users` WRITE;
ALTER TABLE `users` DISABLE KEYS;
ALTER TABLE `users` ENABLE KEYS;
UNLOCK TABLES;




SET FOREIGN_KEY_CHECKS = @PREVIOUS_FOREIGN_KEY_CHECKS;


