CREATE TABLE `site_nginx_conf`
(
    `id`             int                                     NOT NULL AUTO_INCREMENT,
    `name`           varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `conf_temp_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `conf_real_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `status`         smallint                                NOT NULL DEFAULT '1',
    `created_at`     timestamp NULL DEFAULT NULL,
    `updated_at`     timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


