CREATE TABLE `case_share`
(
    `id`                  int                                                           NOT NULL AUTO_INCREMENT,
    `product_id`          int NULL DEFAULT NULL,
    `name`                varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '报告名称',
    `product_name_suffix` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    `path`                varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'pdf文件路径',
    `sort`                smallint NULL DEFAULT NULL COMMENT '数据排序',
    `status`              tinyint(1) NULL DEFAULT NULL COMMENT '数据状态',
    `created_by`          mediumint                                                     NOT NULL DEFAULT '0' COMMENT '创建者',
    `created_at`          int                                                           NOT NULL DEFAULT '0' COMMENT '创建时间',
    `updated_by`          mediumint                                                              DEFAULT '0' COMMENT '更新者',
    `updated_at`          int                                                                    DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;
