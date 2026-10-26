-- v3: 게시물 다중 이미지(최대 3장) 지원
-- 사용법: mysql -u [계정] -p dogstagram < sql/migration_v3.sql
USE dogstagram;

CREATE TABLE IF NOT EXISTS post_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_post_images_post ON post_images(post_id);
