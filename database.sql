CREATE DATABASE IF NOT EXISTS login_demo;
USE login_demo;

-- Users table for login only
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Destinations table
CREATE TABLE IF NOT EXISTS destinations (
    destination_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Sample users
INSERT INTO users (username, password) VALUES
('alice', '$2y$10$6u9XK9C.4e1YkX0mYx3sxeDgM1vZpXkK7rPZiYc6OFlPk6OJZb1we'),  -- 123456
('bob', '$2y$10$GQ9efy5uF9g2HdeS/T5KQu6pGPOZKpZc5RwxZaiF9L0AqZxvfjA7S');   -- abc123

-- Sample destinations
INSERT INTO destinations (name, country, description, image_url) VALUES
('Bali', 'Indonesia', 'Beautiful beaches, temples, and rice terraces.', 'https://example.com/bali.jpg'),
('Paris', 'France', 'The city of love with Eiffel Tower, museums, and cafes.', 'https://example.com/paris.jpg');
