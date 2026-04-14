-- =============================================================
--  Online Food Ordering — Database Setup
--  Run this once in phpMyAdmin or MySQL CLI:
--    mysql -u root -p < database/setup.sql
-- =============================================================

CREATE DATABASE IF NOT EXISTS food_ordering
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE food_ordering;

-- -------------------------------------------------------
-- USERS
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fullname      VARCHAR(120)  NOT NULL,
    email         VARCHAR(191)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- RESTAURANTS
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS restaurants (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug        VARCHAR(80)  NOT NULL UNIQUE,   -- e.g. "royal-bites"
    name        VARCHAR(120) NOT NULL,
    description VARCHAR(255),
    image       VARCHAR(255),
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- MENU ITEMS
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS menu_items (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT UNSIGNED NOT NULL,
    category      VARCHAR(60)  NOT NULL,          -- "Popular", "Starters", etc.
    name          VARCHAR(120) NOT NULL,
    description   VARCHAR(255),
    price         DECIMAL(8,2) NOT NULL,
    image         VARCHAR(255),
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- ORDERS
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    total           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    delivery_name   VARCHAR(120)  NOT NULL,
    delivery_email  VARCHAR(191)  NOT NULL,
    delivery_address TEXT         NOT NULL,
    delivery_phone  VARCHAR(30)   NOT NULL,
    status          ENUM('pending','confirmed','delivered','cancelled')
                    NOT NULL DEFAULT 'pending',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- ORDER ITEMS
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id     INT UNSIGNED NOT NULL,
    menu_item_id INT UNSIGNED NOT NULL,
    name         VARCHAR(120)   NOT NULL,   -- snapshot at time of order
    price        DECIMAL(8,2)   NOT NULL,
    quantity     TINYINT UNSIGNED NOT NULL DEFAULT 1,
    FOREIGN KEY (order_id)     REFERENCES orders(id)     ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- CART  (server-side cart, linked to user session)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS cart (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,
    menu_item_id INT UNSIGNED NOT NULL,
    quantity     TINYINT UNSIGNED NOT NULL DEFAULT 1,
    added_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart_item (user_id, menu_item_id),
    FOREIGN KEY (user_id)      REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- SEED DATA — Restaurants
-- -------------------------------------------------------
INSERT IGNORE INTO restaurants (slug, name, description, image) VALUES
('royal-bites', 'Royal Bites',  'African • Rice • Grills',        'images/restaurants/royal-bites.jpg'),
('urban-pizza', 'Urban Pizza',  'Pizza • Wings • Drinks',          'images/restaurants/urban-pizza.jpg'),
('green-bowl',  'Green Bowl',   'Vegan • Salads • Smoothies',      'images/restaurants/green-bowl.jpg'),
('cafe-brew',   'Cafe Brew',    'Coffee • Pastries • Desserts',    'images/restaurants/cafe-brew.jpg');

-- -------------------------------------------------------
-- SEED DATA — Menu Items
-- -------------------------------------------------------
-- Royal Bites (id=1)
INSERT IGNORE INTO menu_items (restaurant_id, category, name, description, price, image) VALUES
(1,'Popular',  'Classic Burger',       'Juicy beef patty, cheese, lettuce, tomato, and house sauce.', 9.99,  'images/menu/burger.jpg'),
(1,'Starters', 'Crispy Fries',         'Golden fries with a pinch of salt and seasoning.',            4.50,  'images/menu/fries.jpg'),
(1,'Main',     'Jollof Rice Combo',    'Jollof rice, grilled chicken, and plantain.',                13.99, 'images/menu/rice.jpg'),
(1,'Beverages','Chilled Cola',         'Refreshing chilled cola served cold.',                         2.50,  'images/menu/cola.jpg');

-- Urban Pizza (id=2)
INSERT IGNORE INTO menu_items (restaurant_id, category, name, description, price, image) VALUES
(2,'Popular',  'Pepperoni Pizza',      'Cheesy pizza with pepperoni and crispy crust.',               14.50, 'images/menu/pizza.jpg'),
(2,'Starters', 'Spicy Chicken Wings',  'Hot wings with dipping sauce on the side.',                   8.99,  'images/menu/wings.jpg'),
(2,'Beverages','Fresh Juice',          'Fruit juice blend — fresh and tasty.',                         3.75,  'images/menu/juice.jpg');

-- Green Bowl (id=3)
INSERT IGNORE INTO menu_items (restaurant_id, category, name, description, price, image) VALUES
(3,'Vegan',    'Vegan Power Bowl',     'Quinoa, veggies, chickpeas, and dressing.',                  10.99, 'images/menu/veganbowl.jpg'),
(3,'Vegan',    'Veggie Wrap',          'Loaded wrap with veggies and vegan sauce.',                   8.50,  'images/menu/vegwrap.jpg'),
(3,'Vegan',    'Vegan Curry',          'Spiced curry with vegetables and rice.',                     12.25, 'images/menu/vegancurry.jpg'),
(3,'Popular',  'Fresh Garden Salad',   'Crisp greens, cucumber, tomatoes, and light vinaigrette.',    7.25,  'images/menu/salad.jpg');

-- Cafe Brew (id=4)
INSERT IGNORE INTO menu_items (restaurant_id, category, name, description, price, image) VALUES
(4,'Coffee',   'Latte',                'Creamy espresso latte with milk.',                            4.25,  'images/menu/latte.jpg'),
(4,'Coffee',   'Cappuccino',           'Espresso with foamy milk and rich taste.',                    4.50,  'images/menu/cappuccino.jpg'),
(4,'Coffee',   'Espresso',             'Strong espresso shot for quick energy.',                      2.99,  'images/menu/espresso.jpg'),
(4,'Desserts', 'Chocolate Cake',       'Soft chocolate cake slice with rich frosting.',               6.50,  'images/menu/cake.jpg'),
(4,'Desserts', 'Glazed Donut',         'Soft donut with a smooth sweet glaze.',                       2.75,  'images/menu/donut.jpg');
