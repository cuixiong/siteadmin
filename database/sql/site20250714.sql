ALTER TABLE `product_routine`
    ADD COLUMN `price_values` varchar(255) NULL COMMENT '价格版本' AFTER `discount_time_end`;
