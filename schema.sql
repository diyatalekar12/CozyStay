CREATE TABLE hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    location VARCHAR(100),
    starting_price DOUBLE,
    description TEXT,
    image_path VARCHAR(255)
);

-- Create Rooms Table
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT,
    type VARCHAR(50),
    price DOUBLE,
    availability INT,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
);

-- Create Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(15),
    username VARCHAR(100),
    password VARCHAR(100)
);

-- Create Bookings Table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    users_id INT,
    hotel_id INT,
    room_id INT,
    checkin DATE,
    checkout DATE,
    status VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (users_id) REFERENCES users(id),
    FOREIGN KEY (hotel_id) REFERENCES hotels(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id)
);

-- Create Payments Table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    amount DOUBLE,
    payment_date DATE,
    payment_status VARCHAR(20),
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);


DELIMITER $$

CREATE TRIGGER update_room_availability AFTER INSERT ON bookings
FOR EACH ROW
BEGIN
    UPDATE rooms
    SET availability = availability - 1
    WHERE id = NEW.room_id;
END $$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER revert_room_availability AFTER DELETE ON bookings
FOR EACH ROW
BEGIN
    UPDATE rooms
    SET availability = availability + 1
    WHERE id = OLD.room_id;
END $$

DELIMITER ;
