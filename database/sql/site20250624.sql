ALTER TABLE `menus`
    ADD COLUMN `banner_1280` varchar(255) NULL COMMENT '页面背景图1280分辨率' AFTER `banner_pc`,
    ADD COLUMN `redirect_url` varchar(255) NULL COMMENT '轮播图跳转地址' AFTER `updated_at`;



