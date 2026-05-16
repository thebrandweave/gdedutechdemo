-- Fix social_links table foreign key constraints
-- The current schema has conflicting foreign keys on the same column

-- Drop existing foreign key constraints
ALTER TABLE social_links DROP FOREIGN KEY IF EXISTS fk_blog;
ALTER TABLE social_links DROP FOREIGN KEY IF EXISTS fk_event;

-- Add conditional foreign key constraints using triggers instead
-- This approach allows the same target_id column to reference different tables
-- based on the target_type value

-- Create trigger to validate blog references
DELIMITER $$
CREATE TRIGGER tr_social_links_blog_check
BEFORE INSERT ON social_links
FOR EACH ROW
BEGIN
    IF NEW.target_type = 'blog' AND NEW.target_id IS NOT NULL THEN
        IF NOT EXISTS (SELECT 1 FROM blogs WHERE blog_id = NEW.target_id) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid blog_id reference';
        END IF;
    END IF;
END$$

CREATE TRIGGER tr_social_links_blog_update_check
BEFORE UPDATE ON social_links
FOR EACH ROW
BEGIN
    IF NEW.target_type = 'blog' AND NEW.target_id IS NOT NULL THEN
        IF NOT EXISTS (SELECT 1 FROM blogs WHERE blog_id = NEW.target_id) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid blog_id reference';
        END IF;
    END IF;
END$$

CREATE TRIGGER tr_social_links_event_check
BEFORE INSERT ON social_links
FOR EACH ROW
BEGIN
    IF NEW.target_type = 'event' AND NEW.target_id IS NOT NULL THEN
        IF NOT EXISTS (SELECT 1 FROM events WHERE event_id = NEW.target_id) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid event_id reference';
        END IF;
    END IF;
END$$

CREATE TRIGGER tr_social_links_event_update_check
BEFORE UPDATE ON social_links
FOR EACH ROW
BEGIN
    IF NEW.target_type = 'event' AND NEW.target_id IS NOT NULL THEN
        IF NOT EXISTS (SELECT 1 FROM events WHERE event_id = NEW.target_id) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid event_id reference';
        END IF;
    END IF;
END$$
DELIMITER ;
