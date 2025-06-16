CREATE TABLE `order_export_log`
(
    `id`            int unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键ID',
    `file`          varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '最终文件名称/路径',
    `count`         smallint                                                               DEFAULT '0' COMMENT '导出总条数',
    `success_count` smallint                                                               DEFAULT '0' COMMENT '导出成功条数',
    `error_count`   smallint                                                               DEFAULT '0' COMMENT '导出失败条数',
    `details`       longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '详情',
    `created_at`    int                                                           NOT NULL COMMENT '创建时间',
    `created_by`    mediumint                                                     NOT NULL COMMENT '创建者',
    `updated_at`    int                                                           NOT NULL COMMENT '更新时间',
    `updated_by`    mediumint                                                              DEFAULT NULL COMMENT '更新者',
    `state`         tinyint(1) NOT NULL DEFAULT '0' COMMENT '记录任务状态',
    `sort`          smallint                                                      NOT NULL DEFAULT '100' COMMENT '排序',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
