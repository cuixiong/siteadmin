CREATE TABLE `post_subject_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '观点文章自增编号',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '报告名称',
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `sort` int(11) DEFAULT '100' COMMENT '排序',
  `created_by` mediumint(9) DEFAULT '0' COMMENT '创建者',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `updated_by` mediumint(9) DEFAULT '0' COMMENT '更新者',
  `updated_at` int(11) DEFAULT '0' COMMENT '更新时间',
  `propagate_status` tinyint(1) DEFAULT '0' COMMENT '宣传状态',
  `last_propagate_time` int(11) DEFAULT NULL COMMENT '最后宣传时间/最新帖子的时间',
  `accepter` mediumint(9) DEFAULT NULL COMMENT '领取者',
  `accept_time` int(11) DEFAULT NULL COMMENT '领取时间',
  `accept_status` tinyint(1) DEFAULT '0' COMMENT '领取状态',
  PRIMARY KEY (`id`),
  KEY `accept_status` (`accept_status`) USING BTREE,
  KEY `accepter` (`accepter`) USING BTREE,
  KEY `propagate_status` (`propagate_status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `post_subject_article_link` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;