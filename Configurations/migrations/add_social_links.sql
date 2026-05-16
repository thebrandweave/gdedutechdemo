CREATE TABLE social_links (
    id INT PRIMARY KEY AUTO_INCREMENT,
    target_type ENUM('blog', 'event', 'website') NOT NULL,  -- what this link belongs to
    target_id INT DEFAULT NULL,                             -- blog_id or event_id (NULL for website)
    platform VARCHAR(100) NOT NULL,                         -- e.g., 'facebook', 'instagram', etc.
    url VARCHAR(255) NOT NULL,                              -- actual link
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Optional foreign keys
    CONSTRAINT fk_blog FOREIGN KEY (target_id)
        REFERENCES blogs(blog_id) ON DELETE CASCADE,

    CONSTRAINT fk_event FOREIGN KEY (target_id)
        REFERENCES events(event_id) ON DELETE CASCADE
) ENGINE=InnoDB;
