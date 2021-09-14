CREATE TABLE `attributes` (
  `attributes_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`attributes_id`),
  UNIQUE KEY `name_uniq` (`name`(512))
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `options` (
  `options_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`options_id`),
  UNIQUE KEY `name_uniq` (`name`(512))
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `entities` (
  `entities_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`entities_id`),
  UNIQUE KEY `name_uniq` (`name`(512))
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entities_id` int(10) unsigned NOT NULL,
  `attributes_id` int(10) unsigned NOT NULL,
  `options_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_entities_id_IDX` (`entities_id`,`attributes_id`,`options_id`) USING BTREE,
  KEY `products_FK_1` (`attributes_id`),
  KEY `products_FK_2` (`options_id`),
  CONSTRAINT `products_FK` FOREIGN KEY (`entities_id`) REFERENCES `entities` (`entities_id`),
  CONSTRAINT `products_FK_1` FOREIGN KEY (`attributes_id`) REFERENCES `attributes` (`attributes_id`),
  CONSTRAINT `products_FK_2` FOREIGN KEY (`options_id`) REFERENCES `options` (`options_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci