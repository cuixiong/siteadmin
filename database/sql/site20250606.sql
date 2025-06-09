CREATE TABLE `post_subject_strategy` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '课题分配策略自增编号',
  `type` smallint(6) DEFAULT NULL COMMENT '策略类型',
  `category_ids` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '行业筛选',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `sort` int(11) DEFAULT '100' COMMENT '排序',
  `created_by` mediumint(9) DEFAULT '0' COMMENT '创建者',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `updated_by` mediumint(9) DEFAULT '0' COMMENT '更新者',
  `updated_at` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `post_subject_strategy_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `strategy_id` int(11) DEFAULT NULL COMMENT '策略id',
  `num` int(11) DEFAULT '0' COMMENT '每次分配数量',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `sort` int(11) DEFAULT '100' COMMENT '排序',
  `created_by` mediumint(9) DEFAULT '0' COMMENT '创建者',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `updated_by` mediumint(9) DEFAULT '0' COMMENT '更新者',
  `updated_at` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `post_subject_filter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) DEFAULT '1' COMMENT '状态',
  `sort` int(11) DEFAULT '100' COMMENT '排序',
  `created_by` mediumint(9) DEFAULT '0' COMMENT '创建者',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `updated_by` mediumint(9) DEFAULT '0' COMMENT '更新者',
  `updated_at` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;