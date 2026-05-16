CREATE TABLE Blogs (
    blog_id INT PRIMARY KEY AUTO_INCREMENT,
    main_cover_image VARCHAR(255),
    title VARCHAR(255) NOT NULL,
    content TEXT,
    author_id INT,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES Users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;


CREATE TABLE BlogSections (
    section_id INT PRIMARY KEY AUTO_INCREMENT,
    blog_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    image VARCHAR(255),
    section_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_id) REFERENCES Blogs(blog_id) ON DELETE CASCADE
) ENGINE=InnoDB;


ALTER TABLE Blogs
ADD COLUMN category_id INT NULL,
ADD FOREIGN KEY (category_id) REFERENCES BlogCategories(category_id) ON DELETE SET NULL;
