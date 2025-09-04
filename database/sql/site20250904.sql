ALTER TABLE `product_description`
    ADD COLUMN `definition_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '英文定义' AFTER `definition`;

-- 每个分表都要添加，视网站而定
ALTER TABLE `product_description_2025`
    ADD COLUMN `definition_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '英文定义' AFTER `definition`;

ALTER TABLE `product_description_2024`
    ADD COLUMN `definition_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '英文定义' AFTER `definition`;

ALTER TABLE `product_description_2023`
    ADD COLUMN `definition_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '英文定义' AFTER `definition`;

ALTER TABLE `product_description_2022`
    ADD COLUMN `definition_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '英文定义' AFTER `definition`;