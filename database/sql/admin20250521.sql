ALTER TABLE `databases`
    ADD COLUMN `public_host` varchar(255) NULL COMMENT 'DB公网ip' AFTER `ip`;
