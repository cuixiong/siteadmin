ALTER TABLE `problems`
    ADD COLUMN `img` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL COMMENT '问题图片' AFTER `category_id`;


ALTER TABLE `partners`
    ADD COLUMN `type` tinyint(1) NULL DEFAULT 1 COMMENT '类型' AFTER `status`;


