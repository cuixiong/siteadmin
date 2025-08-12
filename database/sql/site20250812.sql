-- 优化留言列表筛选中标的速度
ALTER TABLE post_subject MODIFY keywords VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE post_subject ADD INDEX idx_keywords (keywords);
ALTER TABLE post_subject_link ADD INDEX idx_post_subject_created (post_subject_id, created_at);


