DELETE FROM posts WHERE id = 1;

INSERT INTO posts (id, title, slug, text, created_on, updated_on, page, parent_id, display_in_nav)
VALUES
(
    1, 'Home Page', 'home',
    'Welcome to your new ChintzyCMS installation. Please edit this home page and insert content.',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP,
    't',
    0,
    't'
);