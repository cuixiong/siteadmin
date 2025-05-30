ALTER TABLE `auto_post_config`
    ADD COLUMN `news_category_id` int(14) DEFAULT NULL COMMENT '指定站内新闻类型' AFTER `type`;
