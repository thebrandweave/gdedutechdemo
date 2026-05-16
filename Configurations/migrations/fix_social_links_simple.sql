-- Fix social_links table foreign key constraints
-- Drop existing foreign key constraints
ALTER TABLE social_links DROP FOREIGN KEY IF EXISTS fk_blog;
ALTER TABLE social_links DROP FOREIGN KEY IF EXISTS fk_event;
