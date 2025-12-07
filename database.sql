CREATE DATABASE tourism_management;
USE tourism_management;

-- Bảng USER
CREATE TABLE USER (
    USER_ID INT PRIMARY KEY AUTO_INCREMENT,
    USERNAME VARCHAR(50) NOT NULL UNIQUE,
    PASSWORD VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer' NOT NULL
);

-- Bảng CATEGORY
CREATE TABLE CATEGORY (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL
);

-- Bảng ACTIVITY
CREATE TABLE ACTIVITY (
    activity_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL
);

-- Bảng DESTINATION
CREATE TABLE DESTINATION (
    DESTINATION_ID INT PRIMARY KEY AUTO_INCREMENT,
    NAME VARCHAR(200) NOT NULL,
    COUNTRY VARCHAR(100) NOT NULL,
    DESCRIPTION TEXT,
    IMAGE_URL VARCHAR(500),
    category_id INT,
    MODIFIED_BY INT,
    FOREIGN KEY (category_id) REFERENCES CATEGORY(category_id) ON DELETE SET NULL,
    FOREIGN KEY (MODIFIED_BY) REFERENCES USER(USER_ID) ON DELETE SET NULL
);

-- Bảng REVIEW
CREATE TABLE REVIEW (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    destination_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    FOREIGN KEY (user_id) REFERENCES USER(USER_ID) ON DELETE CASCADE,
    FOREIGN KEY (destination_id) REFERENCES DESTINATION(DESTINATION_ID) ON DELETE CASCADE
);

-- Bảng DESTINATION_ACTIVITIES (bảng trung gian)
CREATE TABLE DESTINATION_ACTIVITIES (
    destination_id INT NOT NULL,
    activity_id INT NOT NULL,
    PRIMARY KEY (destination_id, activity_id),
    FOREIGN KEY (destination_id) REFERENCES DESTINATION(DESTINATION_ID) ON DELETE CASCADE,
    FOREIGN KEY (activity_id) REFERENCES ACTIVITY(activity_id) ON DELETE CASCADE
);

-- USERNAME: admin     | PASSWORD GỐC: admin123
-- USERNAME: user1     | PASSWORD GỐC: user123
-- USERNAME: user2     | PASSWORD GỐC: user456


INSERT INTO USER (USERNAME, PASSWORD, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('user1', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'customer'),
('user2', '$2y$10$kZWQYF8qL8qhQqQwYvNj6OuQQhZqL8qhQqQwYvNj6OuQQhZqL8qhQ', 'customer');

-- Categories
INSERT INTO CATEGORY (name) VALUES
('Bãi biển'),
('Núi non'),
('Di tích lịch sử'),
('Công viên giải trí'),
('Chùa đền');

-- Activities
INSERT INTO ACTIVITY (name) VALUES
('Bơi lội'),
('Leo núi'),
('Chụp ảnh'),
('Cắm trại'),
('Tham quan'),
('Thể thao nước');

-- Destinations
INSERT INTO DESTINATION (NAME, COUNTRY, DESCRIPTION, IMAGE_URL, category_id, MODIFIED_BY) VALUES
('Vịnh Hạ Long', 'Việt Nam', 'Di sản thiên nhiên thế giới với hàng nghìn đảo đá vôi', 'https://example.com/halong.jpg', 1, 1),
('Phố cổ Hội An', 'Việt Nam', 'Thành phố cổ được bảo tồn nguyên vẹn', 'https://example.com/hoian.jpg', 3, 1),
('Đỉnh Fansipan', 'Việt Nam', 'Nóc nhà Đông Dương cao 3143m', 'https://example.com/fansipan.jpg', 2, 2);

-- Destination Activities
INSERT INTO DESTINATION_ACTIVITIES (destination_id, activity_id) VALUES
(1, 1), -- Hạ Long - Bơi lội
(1, 3), -- Hạ Long - Chụp ảnh
(1, 6), -- Hạ Long - Thể thao nước
(2, 3), -- Hội An - Chụp ảnh
(2, 5), -- Hội An - Tham quan
(3, 2), -- Fansipan - Leo núi
(3, 3), -- Fansipan - Chụp ảnh
(3, 4); -- Fansipan - Cắm trại

-- Reviews
INSERT INTO REVIEW (user_id, destination_id, rating, comment) VALUES
(2, 1, 5, 'Cảnh đẹp tuyệt vời, rất đáng để đi!'),
(3, 1, 4, 'Đẹp nhưng hơi đông người'),
(2, 2, 5, 'Phố cổ rất thơ mộng, đồ ăn ngon'),
(3, 3, 4, 'Leo lên đỉnh rất vất vả nhưng xứng đáng');