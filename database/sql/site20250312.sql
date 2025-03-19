CREATE TABLE `questions` (
                             `id` int NOT NULL AUTO_INCREMENT,
                             `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '标题',
                             `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '关键词,用来搜索推荐报告',
                             `sort` int DEFAULT '0' COMMENT '排序',
                             `status` tinyint(1) DEFAULT '1' COMMENT '状态',
                             `created_by` int DEFAULT '0' COMMENT '创建者',
                             `created_at` int DEFAULT '0' COMMENT '创建时间',
                             `updated_by` int DEFAULT '0' COMMENT '更新者',
                             `updated_at` int DEFAULT '0' COMMENT '更新时间',
                             `user_id` int DEFAULT NULL COMMENT '提问人',
                             `ask_at` int DEFAULT NULL COMMENT '提问时间',
                             PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `answers` (
                           `id` int NOT NULL AUTO_INCREMENT,
                           `question_id` int NOT NULL,
                           `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
                           `sort` int DEFAULT '0' COMMENT '排序',
                           `status` tinyint(1) DEFAULT '1' COMMENT '状态',
                           `created_by` int DEFAULT '0' COMMENT '创建者',
                           `created_at` int DEFAULT '0' COMMENT '创建时间',
                           `updated_by` int DEFAULT '0' COMMENT '更新者',
                           `updated_at` int DEFAULT '0' COMMENT '更新时间',
                           `user_id` int DEFAULT NULL COMMENT '回答人',
                           `answer_at` int DEFAULT NULL COMMENT '回答时间',
                           PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
