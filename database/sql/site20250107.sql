
CREATE TABLE `nginx_ban_list`
(
    `id`         int NOT NULL AUTO_INCREMENT COMMENT '主键ID',
    `ban_str`    text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL COMMENT 'ban名称',
    `ban_type`   smallint DEFAULT '1' COMMENT '1:ip封禁;2:UA封禁',
    `status`     smallint DEFAULT '1' COMMENT '1正常',
    `unban_time` int      DEFAULT '0' COMMENT '解禁时间',
    `created_at` int      DEFAULT '0' COMMENT '创建时间',
    `updated_at` int      DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY          `unban_time` (`unban_time`),
    KEY          `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='nginx封禁列表';
