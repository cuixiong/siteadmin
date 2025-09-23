CREATE TABLE `offices_phone` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '全球办公室电话表的主键',
  `office_id` smallint NOT NULL,
  `attribute` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `value` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) NOT NULL,
  `sort` tinyint NOT NULL DEFAULT '100',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;