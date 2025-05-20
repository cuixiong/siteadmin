ALTER TABLE `yadmin`.`site_nginx_conf`
    ADD COLUMN `access_ban_conf_path` text NOT NULL COMMENT '请求次数封禁文件地址' AFTER `conf_real_path`;
