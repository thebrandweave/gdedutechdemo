CREATE TABLE Events (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    main_cover_image VARCHAR(255),                 -- Main event banner or poster
    title VARCHAR(255) NOT NULL,                   -- Event title
    description TEXT,                              -- Optional event overview
    location VARCHAR(255),                         -- Event location
    event_date DATE,                               -- Main event date
    event_time TIME,                               -- Main event time
    event_link VARCHAR(255),                       -- Optional event or registration URL
    organizer_id INT,                              -- Linked to Users table
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES Users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

ALTER TABLE Events
ADD COLUMN category_id INT NULL,
ADD FOREIGN KEY (category_id) REFERENCES EventCategories(category_id) ON DELETE SET NULL;
