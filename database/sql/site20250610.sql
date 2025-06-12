ALTER TABLE `product_routine`
    ADD COLUMN `product_class` varchar(255) NULL COMMENT '产品类别' AFTER `keywords_de`,
ADD COLUMN `segment` varchar(255) NULL COMMENT '细分市场' AFTER `product_class`,
ADD COLUMN `division` varchar(255) NULL COMMENT '产品划分' AFTER `segment`;



INSERT INTO `sync_field` (`name`, `as_name`, `type`, `order`, `description`, `status`, `is_required`, `sort`, `table`,
                          `created_at`, `created_by`, `updated_at`, `updated_by`)
VALUES ('ff33', 'product_class', 2, 100, '产品类别', 1, 0, 100, 1, 1718291800, 167, 1718593998, 167);
INSERT INTO `sync_field` (`name`, `as_name`, `type`, `order`, `description`, `status`, `is_required`, `sort`, `table`,
                          `created_at`, `created_by`, `updated_at`, `updated_by`)
VALUES ('ff34', 'segment', 2, 100, '细分市场', 1, 0, 100, 1, 1718291800, 167, 1718593998, 167);
INSERT INTO `sync_field` (`name`, `as_name`, `type`, `order`, `description`, `status`, `is_required`, `sort`, `table`,
                          `created_at`, `created_by`, `updated_at`, `updated_by`)
VALUES ('ff35', 'division', 2, 100, '产品划分', 1, 0, 100, 1, 1718291800, 167, 1718593998, 167);



INSERT INTO `product_excel_field` ( `name`, `field`, `sort`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES ( '产品类别', 'product_class', 100, 1, 25, 167, 1702281206, 1749029221);
INSERT INTO `product_excel_field` ( `name`, `field`, `sort`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES ( '细分市场', 'segment', 100, 1, 25, 167, 1702281206, 1749029221);
INSERT INTO `product_excel_field` ( `name`, `field`, `sort`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES ( '产品划分', 'division', 100, 1, 25, 167, 1702281206, 1749029221);







