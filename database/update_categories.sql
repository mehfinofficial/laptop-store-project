-- Run once on your existing store.sqlite. Keeps all your products, images and orders.
-- (If you run it a second time, delete the first ALTER TABLE line, it can only run once.)

-- 1. Order of categories on the homepage
ALTER TABLE categories ADD COLUMN sort_order INTEGER NOT NULL DEFAULT 0;

-- 2. Bags & Sleeves becomes Accessories, Chargers gets a clearer name
UPDATE categories SET name='Accessories',      slug='accessories' WHERE slug='bags';
UPDATE categories SET name='Chargers & Power'                     WHERE slug='chargers';

-- 3. New categories
INSERT OR IGNORE INTO categories (name, slug) VALUES ('Gaming','gaming'), ('Audio','audio');

-- 4. Display order: Laptops, Gaming, Mice & Keyboards, Audio, Storage, Chargers & Power, Accessories
UPDATE categories SET sort_order = CASE slug
    WHEN 'laptops'     THEN 1
    WHEN 'gaming'      THEN 2
    WHEN 'peripherals' THEN 3
    WHEN 'audio'       THEN 4
    WHEN 'storage'     THEN 5
    WHEN 'chargers'    THEN 6
    WHEN 'accessories' THEN 7
    ELSE 99 END;

-- 5. The gaming mouse belongs in Gaming
UPDATE products SET category_id=(SELECT id FROM categories WHERE slug='gaming') WHERE slug='redgear-a15-gaming-mouse';

-- 6. Dummy products so the new categories are not empty (delete them whenever you like)
INSERT OR IGNORE INTO products (category_id,name,slug,brand,description,specs,price,old_price,stock,is_featured) VALUES
 ((SELECT id FROM categories WHERE slug='gaming'),'Logitech G102 Lightsync Gaming Mouse','logitech-g102-gaming-mouse','Logitech','Wired gaming mouse with RGB lighting and an accurate 8000 DPI sensor.','{"DPI": "8000", "Buttons": "6", "Connection": "Wired USB", "Lighting": "RGB"}',899,1495,40,1),
 ((SELECT id FROM categories WHERE slug='gaming'),'Redgear Cosmo 7.1 Gaming Headset','redgear-cosmo-71-headset','Redgear','USB gaming headset with virtual 7.1 surround sound and a built-in mic.','{"Sound": "Virtual 7.1", "Connection": "USB", "Driver": "50mm", "Microphone": "Yes"}',1299,2999,25,0),
 ((SELECT id FROM categories WHERE slug='audio'),'boAt Rockerz 450 Bluetooth Headphones','boat-rockerz-450','boAt','Wireless on-ear headphones with deep bass and long battery life.','{"Playback": "Up to 15 hours", "Connection": "Bluetooth 5.0", "Driver": "40mm", "Microphone": "Yes"}',1499,3990,50,1),
 ((SELECT id FROM categories WHERE slug='audio'),'JBL Go 3 Portable Speaker','jbl-go-3-speaker','JBL','Pocket-size waterproof Bluetooth speaker with punchy sound.','{"Playtime": "5 hours", "Water Resistance": "IP67", "Connection": "Bluetooth"}',2999,3999,30,0),
 ((SELECT id FROM categories WHERE slug='audio'),'Logitech C270 HD Webcam','logitech-c270-webcam','Logitech','Simple plug-and-play HD webcam for classes and video calls.','{"Resolution": "720p", "Microphone": "Built-in", "Connection": "USB-A"}',1799,2995,35,0),
 ((SELECT id FROM categories WHERE slug='accessories'),'Aluminium Laptop Stand Adjustable','aluminium-laptop-stand','Generic','Foldable aluminium stand that lifts your laptop to eye level and keeps it cool.','{"Fits": "10 to 17 inch laptops", "Material": "Aluminium", "Foldable": "Yes"}',1299,1999,45,1),
 ((SELECT id FROM categories WHERE slug='accessories'),'Laptop Cooling Pad with 5 Fans','laptop-cooling-pad-5-fans','Generic','Five quiet fans keep your laptop cool during long work or gaming sessions.','{"Fits": "Up to 17 inch laptops", "Fans": "5", "Power": "USB"}',1499,2499,20,0),
 ((SELECT id FROM categories WHERE slug='accessories'),'Large Mouse Pad 80x30 cm','large-mouse-pad-80x30','Generic','Extra-large smooth mouse pad with an anti-slip rubber base.','{"Size": "800 x 300 mm", "Base": "Anti-slip rubber", "Surface": "Smooth cloth"}',399,699,90,0);
