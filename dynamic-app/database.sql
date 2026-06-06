CREATE DATABASE IF NOT EXISTS cinelist;
USE cinelist;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    genre VARCHAR(100),
    year INT,
    description TEXT,
    status ENUM('want_to_watch', 'watched') DEFAULT 'want_to_watch',
    rating TINYINT DEFAULT NULL COMMENT '1-5 rating, only for watched',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Seed: default admin user (password: admin123)
INSERT INTO users (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Seed: sample movies for admin (user_id = 1)
INSERT INTO movies (user_id, title, genre, year, description, status, rating) VALUES
(1, 'Oppenheimer', 'Drama / History', 2023, 'The story of J. Robert Oppenheimer and the Manhattan Project.', 'watched', 5),
(1, 'Dune: Part Two', 'Sci-Fi / Adventure', 2024, 'Paul Atreides unites with the Fremen to wage war against House Harkonnen.', 'watched', 5),
(1, 'Poor Things', 'Fantasy / Comedy', 2023, 'A young woman brought back to life goes on an adventure across Europe.', 'want_to_watch', NULL),
(1, 'Killers of the Flower Moon', 'Crime / Drama', 2023, 'Members of the Osage tribe are murdered in Oklahoma in the 1920s.', 'want_to_watch', NULL),
(1, 'Past Lives', 'Romance / Drama', 2023, 'Two childhood sweethearts reunite after decades apart.', 'watched', 4);
