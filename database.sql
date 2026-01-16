
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user'
);


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
    comment TEXT,

    CONSTRAINT fk_reviews_destination
        FOREIGN KEY (destination_id)
        REFERENCES destinations(destination_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_reviews_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
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


INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$anPnS1M3p6HQ4pLqDobY/OX2L7CAvQou3Ctv6hhQrCXVtqvdqZ1nu', 'admin'),
('alice', '$2y$10$anPnS1M3p6HQ4pLqDobY/OX2L7CAvQou3Ctv6hhQrCXVtqvdqZ1nu', 'user'),
('david', '$2y$10$anPnS1M3p6HQ4pLqDobY/OX2L7CAvQou3Ctv6hhQrCXVtqvdqZ1nu', 'user'),
('emma', '$2y$10$anPnS1M3p6HQ4pLqDobY/OX2L7CAvQou3Ctv6hhQrCXVtqvdqZ1nu', 'user'),
('frank', '$2y$10$anPnS1M3p6HQ4pLqDobY/OX2L7CAvQou3Ctv6hhQrCXVtqvdqZ1nu', 'user'),
('grace', '$2y$10$anPnS1M3p6HQ4pLqDobY/OX2L7CAvQou3Ctv6hhQrCXVtqvdqZ1nu', 'user'),
('henry', '$2y$10$anPnS1M3p6HQ4pLqDobY/OX2L7CAvQou3Ctv6hhQrCXVtqvdqZ1nu', 'user'),
('irene', '$2y$10$anPnS1M3p6HQ4pLqDobY/OX2L7CAvQou3Ctv6hhQrCXVtqvdqZ1nu', 'user'),
('jack', '$2y$10$anPnS1M3p6HQ4pLqDobY/OX2L7CAvQou3Ctv6hhQrCXVtqvdqZ1nu', 'user'),
('linda', '$2y$10$anPnS1M3p6HQ4pLqDobY/OX2L7CAvQou3Ctv6hhQrCXVtqvdqZ1nu', 'user'),
('michael', '$2y$10$anPnS1M3p6HQ4pLqDobY/OX2L7CAvQou3Ctv6hhQrCXVtqvdqZ1nu', 'user');


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

(1, 2, 5, 'Absolutely stunning! The beaches are pristine and the temples are magical. A must-visit destination!'),
(1, 3, 4, 'Great experience overall. The culture is rich and people are friendly. Just a bit crowded in tourist areas.'),


(2, 3, 5, 'The most romantic city I have ever visited! Every corner is like a postcard. The food is incredible!'),
(2, 4, 5, 'Eiffel Tower at night is breathtaking. Museums are world-class. Cannot wait to go back!'),


(3, 5, 5, 'One of the most beautiful natural wonders I have seen. The cruise experience was unforgettable!'),
(3, 6, 4, 'Amazing scenery! The karsts are spectacular. Would recommend staying overnight on a boat.'),


(4, 7, 5, 'Such a charming town! The lanterns at night create a magical atmosphere. Great food too!'),
(4, 8, 5, 'Perfect blend of history and beauty. The tailors here are amazing, got custom clothes made!'),


(5, 9, 4, 'Beautiful beaches with clear water. Perfect for relaxation. Some areas are still developing.'),
(5, 10, 5, 'Paradise on earth! Snorkeling was incredible. Fresh seafood every day!'),


(6, 8, 5, 'The rice terraces are absolutely stunning! Trekking through villages was an amazing cultural experience.'),
(6, 7, 4, 'Beautiful landscapes and friendly local people. Can get quite cold, so pack warm clothes!'),

(7, 5, 4, 'Love the cool weather and beautiful flowers everywhere! Great place to escape the heat.'),
(7, 3, 5, 'Romantic city with stunning waterfalls and unique architecture. The coffee here is amazing!'),


(8, 2, 5, 'Mind-blowing city! So clean, efficient, and the food scene is out of this world. Cherry blossoms were beautiful!'),
(8, 7, 5, 'Perfect mix of tradition and modernity. Every neighborhood offers something different. Highly recommend!'),


(9, 10, 5, 'The temples and gardens are peaceful and beautiful. Felt like stepping back in time. Amazing experience!'),
(9, 5, 5, 'Best place to experience traditional Japanese culture. The bamboo forest is magical!'),

(10, 6, 5, 'The most picturesque place ever! Sunsets are legendary. Every view is Instagram-worthy!'),
(10, 3, 4, 'Beautiful island but quite touristy and expensive. Still worth it for the views!'),


(11, 8, 5, 'Gaudi architecture is mind-blowing! Great beaches, food, and nightlife. Perfect city break!'),
(11, 6, 5, 'Loved everything about Barcelona! Sagrada Familia is a must-see. The tapas are delicious!'),


(12, 10, 5, 'Skiing in the Alps was a dream come true! The mountain scenery is absolutely breathtaking!'),
(12, 4, 4, 'Beautiful mountains and charming villages. Can be quite expensive though.'),


(13, 6, 5, 'Ultimate luxury paradise! Overwater villa was amazing. Perfect for honeymoon!'),
(13, 3, 5, 'Crystal clear water and incredible marine life. Best snorkeling and diving I have ever done!'),


(14, 2, 4, 'So much energy and things to do! Times Square, Central Park, museums - never boring!'),
(14, 10, 5, 'The city has it all! Amazing food from every culture, world-class shows, iconic landmarks!'),


(15, 9, 5, 'Absolutely incredible! The hike was challenging but worth every step. Such an amazing historical site!'),
(15, 5, 5, 'One of the most impressive places I have ever visited. The history and views are unmatched!'),


(16, 6, 5, 'The underwater world is spectacular! Saw so many colorful fish and corals. Diving here is a must!'),
(16, 3, 4, 'Amazing marine life! The reef is beautiful but we need to protect it. Great experience overall.'),


(17, 7, 4, 'Impressive skyscrapers and luxury everywhere! Burj Khalifa view is stunning. Very hot though!'),
(17, 10, 5, 'Futuristic city with amazing shopping and entertainment. Desert safari was a highlight!');

INSERT INTO blogs (title, content, user_id, destination_id) VALUES
(   'My Amazing Journey to Ha Long Bay',
    'Last month, I had the incredible opportunity to visit Ha Long Bay, one of Vietnam''s most stunning natural wonders. The limestone karsts rising from the emerald waters created a surreal landscape that looked like something out of a fantasy movie.
    We took a two-day cruise through the bay, stopping at various caves and islands. The Sung Sot Cave was particularly impressive with its massive chambers and beautiful stalactites. Swimming in the bay''s crystal-clear waters was an unforgettable experience.
    The highlight was watching the sunset from the deck of our boat, with the karsts silhouetted against the orange and pink sky. If you''re planning a trip to Vietnam, Ha Long Bay should definitely be on your list!',
    5,3
),
(   'A Foodie''s Guide to Tokyo',
    'Tokyo is a paradise for food lovers! During my week-long stay, I made it my mission to explore as much of the city''s incredible food scene as possible.
    From the freshest sushi at Tsukiji Market to authentic ramen in tiny neighborhood shops, every meal was an adventure. I was amazed by the attention to detail and quality - even convenience store food was delicious!
    Don''t miss trying okonomiyaki in Asakusa, tempura in Ginza, and the famous Ichiran ramen. The department store food halls (depachika) are also incredible, offering everything from wagyu beef to beautiful desserts.
    Pro tip: Download Google Translate''s camera feature - it''s a lifesaver for reading menus!',
    6,8
),
(
    'Romantic Escape to Paris: A 5-Day Itinerary',
    'Paris truly lives up to its reputation as the city of love! My partner and I spent 5 magical days exploring this beautiful city, and I wanted to share our perfect itinerary.
    Day 1-2: Start with the classics - Eiffel Tower, Louvre Museum, and a Seine River cruise. Book your Eiffel Tower tickets in advance!
    Day 3: Explore Montmartre, visit Sacré-Cœur, and have dinner in a cozy bistro. The views from the top are spectacular.
    Day 4: Day trip to Versailles - the palace and gardens are absolutely breathtaking.
    Day 5: Leisurely stroll through Le Marais, visit Notre-Dame (exterior), and enjoy pastries at a local boulangerie.
    Every corner of Paris is picture-perfect. Don''t rush - take time to sit at cafes and soak in the atmosphere!',
    3,2
),
(   'Backpacking Through Vietnam: Hoi An to Sapa',
    'Vietnam has become one of my favorite countries after a month-long backpacking adventure! Here''s my experience traveling from the ancient town of Hoi An to the mountainous region of Sapa.
    Hoi An was enchanting with its lantern-lit streets and rich history. I spent three days getting custom clothes made, exploring the old town, and enjoying incredible cao lau noodles.
    After a quick stop in Hanoi, I took an overnight train to Sapa. The contrast was striking - from coastal charm to misty mountain peaks. Trekking through the rice terraces and staying with a local family in a homestay was the highlight of my trip.
    The warmth and hospitality of Vietnamese people made this journey unforgettable. Budget travelers will love Vietnam - it''s affordable, safe, and incredibly beautiful.
    Next on my list: Phu Quoc Island for some beach relaxation!',
    8,NULL
);

ALTER TABLE users 
ADD COLUMN remember_token VARCHAR(255) NULL DEFAULT NULL,
ADD UNIQUE INDEX idx_token (remember_token);


ALTER TABLE reviews
ADD parent_id INT NULL,
ADD CONSTRAINT fk_review_parent
FOREIGN KEY (parent_id) REFERENCES reviews(review_id)
ON DELETE CASCADE;

