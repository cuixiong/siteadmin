ALTER TABLE `yadmin`.`sites`
    ADD COLUMN `site_logo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '站点logo' AFTER `third_domain`;
