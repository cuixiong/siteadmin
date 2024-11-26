ALTER TABLE `access_log`
    ADD COLUMN `content_size` double(10, 2) NOT NULL DEFAULT 0.00 COMMENT '流量大小, 单位bytes' AFTER `ip_muti_third`;






