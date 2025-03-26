CREATE TABLE `auto_post_config`
(
    `id`                   int                                                           NOT NULL AUTO_INCREMENT COMMENT '主键',
    `name`                 varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
    `code`                 varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
    `title_template_ids`   varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '标题模板ids',
    `content_template_ids` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '内容模板ids',
    `product_category_ids` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '报告分类ids',
    `start_product_id`     int NULL DEFAULT NULL COMMENT '抽取的报告起始id',
    `post_num`             int NULL DEFAULT NULL COMMENT '抽取的发帖数量',
    `db_host`              varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '数据库地址',
    `db_name`              varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '数据库名',
    `db_username`          varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '登录用户名',
    `db_password`          varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '登录密码',
    `db_charset`           varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '编码',
    `domain`               varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '域名',
    `created_by`           int NULL DEFAULT NULL COMMENT '创建者',
    `created_at`           int NULL DEFAULT NULL COMMENT '创建时间',
    `updated_by`           int NULL DEFAULT NULL COMMENT '修改者',
    `updated_at`           int NULL DEFAULT NULL COMMENT '修改时间',
    `sort`                 tinyint NULL DEFAULT NULL COMMENT '排序:整数,数值越小,排序越靠前。',
    `status`               tinyint(1) NULL DEFAULT 1 COMMENT '状态',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;



CREATE TABLE `auto_post_log`
(
    `id`                  int NOT NULL AUTO_INCREMENT COMMENT '主键',
    `code`                varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '0',
    `product_id`          int                                                           DEFAULT '0' COMMENT '报告id',
    `wp_link`             varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '0' COMMENT 'wrdpress链接',
    `title_template_id`   int                                                           DEFAULT '0' COMMENT '使用的标题模板id',
    `content_template_id` int                                                           DEFAULT '0' COMMENT '使用的报告模板id',
    `created_at`          int                                                           DEFAULT NULL COMMENT '创建时间',
    `post_status`         smallint                                                      DEFAULT NULL COMMENT '发帖状态',
    `detail`              text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



