CREATE TABLE `black_ban_list`
(
    `id`         int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
    `ban_str`    text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL COMMENT 'ban名称',
    `ban_type`   smallint DEFAULT '1' COMMENT '1:ip封禁;2:UA封禁',
    `created_at` int      DEFAULT '0' COMMENT '创建时间',
    `updated_at` int      DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='黑名单列表';

