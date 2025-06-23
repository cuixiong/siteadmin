ALTER TABLE `nginx_ban_list`
    ADD COLUMN `content` text NULL COMMENT '封禁日志' AFTER `service_type`;


CREATE TABLE `sync_site_log`
(
    `id`         int                                                           NOT NULL AUTO_INCREMENT COMMENT '主键ID',
    `site_name`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'IP',
    `site_id`    varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'DB公网ip',
    `event_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '事件名称',
    `event_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '事件类型',
    `status`     tinyint(1) DEFAULT '1' COMMENT '状态',
    `sort`       int                                                           DEFAULT '100' COMMENT '排序',
    `updated_by` int                                                           DEFAULT '0' COMMENT '更新者',
    `updated_at` int                                                           DEFAULT '0' COMMENT '更新时间',
    `created_by` int                                                           DEFAULT '0' COMMENT '创建者',
    `created_at` int                                                           DEFAULT '0' COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;
