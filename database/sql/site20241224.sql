CREATE TABLE `faq_category`
(
    `id`         int                                                           NOT NULL AUTO_INCREMENT COMMENT '问答类型表的主键',
    `name`       varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
    `status`     tinyint(1) DEFAULT '1' COMMENT '状态',
    `sort`       int       DEFAULT '100' COMMENT '排序',
    `created_by` mediumint DEFAULT '0' COMMENT '创建者',
    `created_at` int       DEFAULT '0' COMMENT '创建时间',
    `updated_by` mediumint DEFAULT '0' COMMENT '更新者',
    `updated_at` int       DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `problems`
    ADD COLUMN `category_id` int NOT NULL DEFAULT 0 COMMENT '分类id' AFTER `id`;
