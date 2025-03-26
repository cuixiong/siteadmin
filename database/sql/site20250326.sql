ALTER TABLE `access_log`
    ADD COLUMN `type` smallint NOT NULL DEFAULT 0 COMMENT '路由类型' AFTER `log_date`,
ADD COLUMN `service_id` int NOT NULL DEFAULT 0 COMMENT '业务id' AFTER `type`;
