ALTER TABLE `product_export_log`
    MODIFY COLUMN `count` int NULL DEFAULT 0 COMMENT '导出总条数' AFTER `file`,
    MODIFY COLUMN `success_count` int NULL DEFAULT 0 COMMENT '导出成功条数' AFTER `count`,
    MODIFY COLUMN `error_count` int NULL DEFAULT 0 COMMENT '导出失败条数' AFTER `success_count`;


#水印配置
INSERT INTO `systems` (`id`, `name`, `english_name`, `alias`, `sort`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES (46, '水印配置', 'watermark', 'newsWatermark', 1, 1, 25, 25, 1728539618, 1728539656);
INSERT INTO `system_values` ( `parent_id`, `name`, `english_name`, `key`, `value`, `type`, `status`, `switch`, `hidden`, `sort`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES (46, '水印图', 'image', 'newsWatermarkImage', '/site/gircn/setting/network/watermark.png', 3, 1, 1, 1, 0, 1728539743, 25, 1728547006, 25);
INSERT INTO `system_values` ( `parent_id`, `name`, `english_name`, `key`, `value`, `type`, `status`, `switch`, `hidden`, `sort`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES (46, '透明度', 'opacity', 'newsWatermarkOpacity', '30', 1, 1, 1, 1, 0, 1728542000, 25, 1728546778, 25);
INSERT INTO `system_values` ( `parent_id`, `name`, `english_name`, `key`, `value`, `type`, `status`, `switch`, `hidden`, `sort`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES (46, '水印位置', 'location', 'newsWatermarkLocation', 'fit', 1, 1, 1, 1, 0, 1728542061, 25, 1728546761, 25);
INSERT INTO `system_values` ( `parent_id`, `name`, `english_name`, `key`, `value`, `type`, `status`, `switch`, `hidden`, `sort`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES ( 46, '偏移宽度', 'offset width', 'newsWatermarkOffsetWidth', '20', 1, 1, 1, 1, 0, 1728542119, 25, 1728542119, 0);
INSERT INTO `system_values` ( `parent_id`, `name`, `english_name`, `key`, `value`, `type`, `status`, `switch`, `hidden`, `sort`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES ( 46, '偏移高度', 'offset height', 'newsWatermarkOffsetHeight', '10', 1, 1, 1, 1, 0, 1728542152, 25, 1728542152, 0);
