CREATE TABLE `sensitive_words_handle_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `log_type` mediumint DEFAULT NULL COMMENT '场景类型',
  `words` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '敏感词',
  `old_words` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '旧敏感词',
  `product_hidden_count` int DEFAULT NULL COMMENT '隐藏报告数量',
  `product_show_count` int DEFAULT NULL COMMENT '恢复报告数量',
  `subject_delete_count` int DEFAULT NULL COMMENT '删除课题数量',
  `product_hidden_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '隐藏报告详情',
  `product_show_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '恢复报告详情',
  `subject_delete_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '删除课题详情',
  `status` tinyint(1) DEFAULT '1',
  `sort` int DEFAULT '0' COMMENT '排序',
  `created_by` int DEFAULT NULL COMMENT '创建者',
  `updated_by` int DEFAULT NULL COMMENT '更新者',
  `created_at` int DEFAULT NULL COMMENT '创建时间',
  `updated_at` int DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;