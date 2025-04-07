ALTER TABLE `access_log`
    ADD COLUMN `type` smallint NOT NULL DEFAULT 0 COMMENT '路由类型' AFTER `log_date`,
ADD COLUMN `service_id` int NOT NULL DEFAULT 0 COMMENT '业务id' AFTER `type`;


ALTER TABLE `offices`
    ADD COLUMN `time_zone_copy` varbinary(255) NULL DEFAULT '' COMMENT '时区当前时间' AFTER `time_zone`;
