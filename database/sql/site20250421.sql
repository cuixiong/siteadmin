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

ALTER TABLE `news`
    MODIFY COLUMN `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '内容' AFTER `description`;



CREATE TABLE `site_map_conf`
(
    `id`         smallint                                                      NOT NULL AUTO_INCREMENT COMMENT 'id',
    `name`       varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
    `code`       varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '地图code码(唯一)',
    `loc`        varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '链接',
    `xml_name`   varchar(255) COLLATE utf8mb4_unicode_ci                       DEFAULT NULL COMMENT 'xml文件昵称',
    `sort`       int                                                           DEFAULT '0' COMMENT '排序',
    `status`     tinyint(1) DEFAULT '1' COMMENT '状态:1代表有效,0代表无效。',
    `created_by` int                                                           DEFAULT '0' COMMENT '数据创建者',
    `created_at` int                                                           DEFAULT '0' COMMENT '数据创建时',
    `updated_by` int                                                           DEFAULT '0' COMMENT '数据修改者',
    `updated_at` int                                                           DEFAULT '0' COMMENT '数据修改时',
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE KEY `code` (`code`) USING BTREE COMMENT '编码索引',
    KEY          `status` (`status`) USING BTREE COMMENT '状态索引'
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


#测试要求,修改字段长度
ALTER TABLE `system_values`
    MODIFY COLUMN `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称' AFTER `parent_id`;
