
-- Users table 
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user'
);

-- Destinations table
CREATE TABLE IF NOT EXISTS destinations (
    destination_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(10000),
     category VARCHAR(50),
    province VARCHAR(100)
);

CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    destination_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment  TEXT,
    FOREIGN KEY (destination_id) REFERENCES destinations(destination_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
CREATE TABLE IF NOT EXISTS cookies (
    cookie_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS blogs (
    blog_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    user_id INT NOT NULL,
    destination_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (destination_id) REFERENCES destinations(destination_id) ON DELETE SET NULL
);

-- Sample users (including 1 admin)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$anPnS1M3p6HQ4pLqDobY/OX2L7CAvQou3Ctv6hhQrCXVtqvdqZ1nu', 'admin'),
('alice', '$2y$10$anPnS1M3p6HQ4pLqDobY/OX2L7CAvQou3Ctv6hhQrCXVtqvdqZ1nu', 'user'),
('bob', '$2y$10$anPnS1M3p6HQ4pLqDobY/OX2L7CAvQou3Ctv6hhQrCXVtqvdqZ1nu', 'user');

-- Thêm destinations
INSERT INTO destinations (name, country, description, image_url, category, province) VALUES
('Bali', 'Indonesia', 'Beautiful beaches, temples, and rice terraces.', 'https://th.bing.com/th/id/R.797f10311478899b73037d563d76b7f4?rik=YFVY3Y0Qrr%2fiGA&pid=ImgRaw&r=0', 'Beach', 'Bali Province'),
('Paris', 'France', 'The city of love with Eiffel Tower, museums, and cafes.', 'https://www.ana.co.jp/www2/plan-book/where-we-travel/paris/paris-01.jpg', 'City', 'Île-de-France'),
('Ha Long Bay', 'Vietnam', 'UNESCO World Heritage site with stunning limestone karsts and emerald waters.', 'https://th.bing.com/th/id/R.add756f6f7c6361b71a8307f53b3da79?rik=Yd9cn1CO0F5emQ&riu=http%3a%2f%2fhdqwalls.com%2fwallpapers%2fha-long-bay-1d.jpg&ehk=Ags1lxH2zRSmlD0bTS70UYr8xF2jAtLpTdX6dvujrnc%3d&risl=1&pid=ImgRaw&r=0', 'Nature', 'Quang Ninh'),
('Hoi An Ancient Town', 'Vietnam', 'Charming historic town with lanterns, ancient architecture, and riverside beauty.', 'https://vietnamtour.com/images/Vietnam_Attractions/Quang_Nam/Hoi-An-Ancient-Town-2_477af.jpg', 'Cultural', 'Quang Nam'),
('Phu Quoc Island', 'Vietnam', 'Tropical paradise with pristine beaches and clear blue waters.', 'https://phuquoctrip.com/files/images/daily_tour/land_tour_1/land_tour_01_05.jpg', 'Beach', 'Kien Giang'),
('Sapa', 'Vietnam', 'Mountain town with terraced rice fields and ethnic minority villages.', 'https://impresstravel.com/wp-content/uploads/2021/04/Sapa-Banner.jpg', 'Mountain', 'Lao Cai'),
('Da Lat', 'Vietnam', 'Cool highland city known for flowers, waterfalls, and French colonial architecture.', 'https://i0.wp.com/vndiscovery.org/wp-content/uploads/2023/02/da-lat-mountain-tour.jpg?ssl=1', 'Mountain', 'Lam Dong'),
('Tokyo', 'Japan', 'Vibrant metropolis blending traditional culture with modern technology.', 'https://tse1.mm.bing.net/th/id/OIP.IurV-F_R418c8JDGOs2WzwHaEK?rs=1&pid=ImgDetMain&o=7&rm=3', 'City', 'Tokyo Prefecture'),
('Kyoto', 'Japan', 'Ancient capital with beautiful temples, gardens, and geisha districts.','https://content.r9cdn.net/rimg/dimg/83/d4/85f68013-city-20339-16489ec9b8b.jpg?width=1750&height=1000&xhint=1444&yhint=1011&crop=true', 'Cultural', 'Kyoto Prefecture'),
('Santorini', 'Greece', 'Iconic white-washed buildings with blue domes overlooking the Aegean Sea.', 'https://th.bing.com/th/id/R.8bab0e8ee044b11f90a6ca927d259308?rik=38WH3eiS7cR7sg&pid=ImgRaw&r=0', 'Beach', 'South Aegean'),
('Barcelona', 'Spain', 'Artistic city with Gaudi architecture, beaches, and vibrant nightlife.', 'https://th.bing.com/th/id/R.a9a6de9f5f859c3dbd555c636a23787e?rik=6UNIhNjGGS6Clw&riu=http%3a%2f%2fcdn.wallpapersafari.com%2f61%2f89%2fXha7ol.jpg&ehk=iPm1EnEOuQDRFnhJwp22UbBbfrPiSg%2fiv%2bFKa4fN4h4%3d&risl=&pid=ImgRaw&r=0', 'City', 'Catalonia'),
('Swiss Alps', 'Switzerland', 'Majestic mountains perfect for skiing, hiking, and breathtaking views.', 'https://th.bing.com/th/id/R.fbe6c99732a9658f304b6b79489c5cba?rik=G%2b%2fcdYPIIVIQcQ&pid=ImgRaw&r=0', 'Mountain', 'Various Cantons'),
('Maldives', 'Maldives', 'Luxury overwater bungalows and crystal-clear tropical waters.', 'https://www.agoda.com/wp-content/uploads/2023/09/Hero-image-Maldives.jpg', 'Beach', 'Male Atoll'),
('New York City', 'USA', 'The city that never sleeps with iconic landmarks and diverse culture.', 'https://tse2.mm.bing.net/th/id/OIP.oa-osefimDHwX0zc0BHSEwHaE8?rs=1&pid=ImgDetMain&o=7&rm=3', 'City', 'New York'),
('Machu Picchu', 'Peru', 'Ancient Incan citadel set high in the Andes Mountains.', 'https://upload.wikimedia.org/wikipedia/commons/8/8a/Machu_Picchu_Peru.JPG', 'Cultural', 'Cusco'),
('Great Barrier Reef', 'Australia', 'World largest coral reef system with incredible marine life.', 'https://www.australiangeographic.com.au/wp-content/uploads/2018/06/great-barrier-reef-hardy.jpg', 'Nature', 'Queensland'),
('Dubai', 'UAE', 'Futuristic city with luxury shopping, ultramodern architecture, and desert adventures.', 'https://a.cdn-hotels.com/gdcs/production121/d772/6b5a9a4c-fd06-4bcf-b2f0-d979e3704cf9.jpg', 'City', 'Dubai Emirate');


INSERT INTO reviews (destination_id, user_id, rating, comment) VALUES
-- Reviews cho Bali (destination_id = 1)
(1, 2, 5, 'Absolutely stunning! The beaches are pristine and the temples are magical. A must-visit destination!'),
(1, 3, 4, 'Great experience overall. The culture is rich and people are friendly. Just a bit crowded in tourist areas.'),

-- Reviews cho Paris (destination_id = 2)
(2, 2, 5, 'The most romantic city I have ever visited! Every corner is like a postcard. The food is incredible!'),
(2, 3, 5, 'Eiffel Tower at night is breathtaking. Museums are world-class. Cannot wait to go back!'),

-- Reviews cho Ha Long Bay (destination_id = 3)
(3, 2, 5, 'One of the most beautiful natural wonders I have seen. The cruise experience was unforgettable!'),
(3, 3, 4, 'Amazing scenery! The karsts are spectacular. Would recommend staying overnight on a boat.'),

-- Reviews cho Hoi An (destination_id = 4)
(4, 2, 5, 'Such a charming town! The lanterns at night create a magical atmosphere. Great food too!'),
(4, 3, 5, 'Perfect blend of history and beauty. The tailors here are amazing, got custom clothes made!'),

-- Reviews cho Phu Quoc (destination_id = 5)
(5, 2, 4, 'Beautiful beaches with clear water. Perfect for relaxation. Some areas are still developing.'),
(5, 3, 5, 'Paradise on earth! Snorkeling was incredible. Fresh seafood every day!'),

-- Reviews cho Sapa (destination_id = 6)
(6, 2, 5, 'The rice terraces are absolutely stunning! Trekking through villages was an amazing cultural experience.'),
(6, 3, 4, 'Beautiful landscapes and friendly local people. Can get quite cold, so pack warm clothes!'),

-- Reviews cho Da Lat (destination_id = 7)
(7, 2, 4, 'Love the cool weather and beautiful flowers everywhere! Great place to escape the heat.'),
(7, 3, 5, 'Romantic city with stunning waterfalls and unique architecture. The coffee here is amazing!'),

-- Reviews cho Tokyo (destination_id = 8)
(8, 2, 5, 'Mind-blowing city! So clean, efficient, and the food scene is out of this world. Cherry blossoms were beautiful!'),
(8, 3, 5, 'Perfect mix of tradition and modernity. Every neighborhood offers something different. Highly recommend!'),

-- Reviews cho Kyoto (destination_id = 9)
(9, 2, 5, 'The temples and gardens are peaceful and beautiful. Felt like stepping back in time. Amazing experience!'),
(9, 3, 5, 'Best place to experience traditional Japanese culture. The bamboo forest is magical!'),

-- Reviews cho Santorini (destination_id = 10)
(10, 2, 5, 'The most picturesque place ever! Sunsets are legendary. Every view is Instagram-worthy!'),
(10, 3, 4, 'Beautiful island but quite touristy and expensive. Still worth it for the views!'),

-- Reviews cho Barcelona (destination_id = 11)
(11, 2, 5, 'Gaudi architecture is mind-blowing! Great beaches, food, and nightlife. Perfect city break!'),
(11, 3, 5, 'Loved everything about Barcelona! Sagrada Familia is a must-see. The tapas are delicious!'),

-- Reviews cho Swiss Alps (destination_id = 12)
(12, 2, 5, 'Skiing in the Alps was a dream come true! The mountain scenery is absolutely breathtaking!'),
(12, 3, 4, 'Beautiful mountains and charming villages. Can be quite expensive though.'),

-- Reviews cho Maldives (destination_id = 13)
(13, 2, 5, 'Ultimate luxury paradise! Overwater villa was amazing. Perfect for honeymoon!'),
(13, 3, 5, 'Crystal clear water and incredible marine life. Best snorkeling and diving I have ever done!'),

-- Reviews cho New York City (destination_id = 14)
(14, 2, 4, 'So much energy and things to do! Times Square, Central Park, museums - never boring!'),
(14, 3, 5, 'The city has it all! Amazing food from every culture, world-class shows, iconic landmarks!'),

-- Reviews cho Machu Picchu (destination_id = 15)
(15, 2, 5, 'Absolutely incredible! The hike was challenging but worth every step. Such an amazing historical site!'),
(15, 3, 5, 'One of the most impressive places I have ever visited. The history and views are unmatched!'),

-- Reviews cho Great Barrier Reef (destination_id = 16)
(16, 2, 5, 'The underwater world is spectacular! Saw so many colorful fish and corals. Diving here is a must!'),
(16, 3, 4, 'Amazing marine life! The reef is beautiful but we need to protect it. Great experience overall.'),

-- Reviews cho Dubai (destination_id = 17)
(17, 2, 4, 'Impressive skyscrapers and luxury everywhere! Burj Khalifa view is stunning. Very hot though!'),
(17, 3, 5, 'Futuristic city with amazing shopping and entertainment. Desert safari was a highlight!');
ALTER TABLE users 
ADD COLUMN remember_token VARCHAR(255) NULL DEFAULT NULL,
ADD UNIQUE INDEX idx_token (remember_token);

-- 1. Thêm reply review
ALTER TABLE reviews
ADD parent_id INT NULL,
ADD CONSTRAINT fk_review_parent
FOREIGN KEY (parent_id) REFERENCES reviews(review_id)
ON DELETE CASCADE;

-- 2. Fix lỗi không xóa destination
ALTER TABLE reviews DROP FOREIGN KEY reviews_ibfk_1;

ALTER TABLE reviews
ADD CONSTRAINT reviews_ibfk_1
FOREIGN KEY (destination_id)
REFERENCES destinations(destination_id)
ON DELETE CASCADE;
