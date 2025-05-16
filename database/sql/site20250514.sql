ALTER TABLE `nginx_ban_list`
    ADD COLUMN `service_type` smallint NULL DEFAULT 1 COMMENT '业务类型 1:指标异常封禁' AFTER `status`;
