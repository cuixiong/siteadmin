CREATE TABLE `post_platform` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '发帖平台自增编号',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `sort` int DEFAULT '100' COMMENT '排序',
  `created_by` mediumint DEFAULT '0' COMMENT '创建者',
  `created_at` int DEFAULT '0' COMMENT '创建时间',
  `updated_by` mediumint DEFAULT '0' COMMENT '更新者',
  `updated_at` int DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `post_subject` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '发帖课题自增编号',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '报告名称',
  `product_id` int NOT NULL COMMENT '报告id',
  `product_category_id` int DEFAULT NULL COMMENT '报告分类id',
  `version` int DEFAULT NULL COMMENT '版本',
  `analyst` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '分析师',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `sort` int DEFAULT '100' COMMENT '排序',
  `created_by` mediumint DEFAULT '0' COMMENT '创建者',
  `created_at` int DEFAULT '0' COMMENT '创建时间',
  `updated_by` mediumint DEFAULT '0' COMMENT '更新者',
  `updated_at` int DEFAULT '0' COMMENT '更新时间',
  `propagate_status` tinyint(1) DEFAULT '0' COMMENT '宣传状态',
  `last_propagate_time` int DEFAULT NULL COMMENT '最后宣传时间/最新帖子的时间',
  `accepter` mediumint DEFAULT NULL COMMENT '领取者',
  `accept_time` int DEFAULT NULL COMMENT '领取时间',
  `accept_status` tinyint(1) DEFAULT '0' COMMENT '领取状态',
  `change_status` tinyint(1) DEFAULT '0' COMMENT '修改状态，重要数据是否有变化',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `product_id` (`product_id`),
  KEY `product_category_id` (`product_category_id`),
  KEY `accept_status` (`accept_status`),
  KEY `accepter` (`accepter`),
  KEY `propagate_status` (`propagate_status`),
  KEY `change_status` (`change_status`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `post_subject_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '发帖链接自增编号',
  `post_subject_id` int(11) NOT NULL COMMENT '发帖课题id',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '发帖链接',
  `post_platform_id` int(11) NOT NULL COMMENT '发帖平台id',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `sort` int(11) DEFAULT '100' COMMENT '排序',
  `created_by` mediumint(9) DEFAULT '0' COMMENT '创建者',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `updated_by` mediumint(9) DEFAULT '0' COMMENT '更新者',
  `updated_at` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `post_subject_id` (`post_subject_id`),
  KEY `post_platform_id` (`post_platform_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `post_subject_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '操作记录自增编号',
  `type` tinyint(1) DEFAULT NULL COMMENT '操作记录类型',
  `post_subject_id` int(11) DEFAULT NULL COMMENT '关联的课题ID',
  `success_count` int(11) DEFAULT NULL COMMENT '成功数量',
  `ingore_count` int(11) DEFAULT NULL COMMENT '忽略数量',
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '详情',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `created_by` mediumint(9) DEFAULT '0' COMMENT '创建者',
  `updated_at` int(11) DEFAULT '0' COMMENT '修改时间',
  `updated_by` mediumint(9) DEFAULT '0' COMMENT '修改者',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `post_subject_id` (`post_subject_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


