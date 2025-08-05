CREATE TABLE `search_products_list_log` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '报告列表搜索记录表-主键',
  `ip` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ip地址',
  `ip_addr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ip所在地',
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '关键词',
  `status` smallint NOT NULL DEFAULT '1' COMMENT '状态 1:正常',
  `sort` smallint DEFAULT '100' COMMENT '排序',
  `created_by` mediumint DEFAULT '0' COMMENT '数据创建者ID',
  `created_at` int DEFAULT '0' COMMENT '数据创建时（时间戳）',
  `updated_by` mediumint DEFAULT '0' COMMENT '数据修改者ID',
  `updated_at` int DEFAULT '0' COMMENT '数据修改时（时间戳）',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `ip` (`ip`),
  KEY `keywords` (`keywords`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;