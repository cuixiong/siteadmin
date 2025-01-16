CREATE TABLE `publishers`
(
    `id`          int unsigned NOT NULL AUTO_INCREMENT,
    `name`        varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '出版商名称',
    `short_name`  varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  DEFAULT NULL COMMENT '简称',
    `company`     varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '公司',
    `content`     text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '公司内容',
    `logo`        varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'logo',
    `status`      tinyint                                                       DEFAULT '1' COMMENT '1正常 0关闭',
    `email`       varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '邮箱',
    `phone`       varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '电话',
    `province_id` int                                                           DEFAULT NULL COMMENT '省份id',
    `city_id`     int                                                           DEFAULT NULL COMMENT '城市id',
    `address`     varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '公司地址',
    `link`        varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '链接地址',
    `created_at`  int                                                           DEFAULT NULL,
    `updated_at`  int                                                           DEFAULT NULL,
    `updated_by`  int                                                           DEFAULT NULL,
    `created_by`  int                                                           DEFAULT NULL,
    `sort`        tinyint                                                       DEFAULT '100',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='出版商表';

ALTER TABLE `contact_us`
    ADD COLUMN `channel_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '提交来源文本' AFTER `channel`;
