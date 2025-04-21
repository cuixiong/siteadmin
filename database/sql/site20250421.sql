CREATE TABLE `personal_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '个人设置表的主键',
  `user_id` int(11) DEFAULT '0' COMMENT '用户id',
  `key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置键名',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '配置键值',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态:1代表有效或显示,0代表无效或隐藏。',
  `sort` smallint(6) DEFAULT '100',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `created_by` mediumint(9) DEFAULT '0' COMMENT '创建者',
  `updated_at` int(11) DEFAULT '0' COMMENT '修改时间',
  `updated_by` mediumint(9) DEFAULT '0' COMMENT '修改者',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;