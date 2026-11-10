-- Run ONCE on an existing store.sqlite (keeps products, images, orders).
-- Adds warranty + rating columns, adds 6 premium laptops, sets warranty/rating on every product.
ALTER TABLE products ADD COLUMN warranty_months INTEGER NOT NULL DEFAULT 12;
ALTER TABLE products ADD COLUMN rating REAL;
ALTER TABLE products ADD COLUMN rating_count INTEGER NOT NULL DEFAULT 0;

-- Premium laptops (price/stock are sample data: edit to match your real stock)
INSERT OR IGNORE INTO products (category_id,name,slug,brand,description,specs,price,old_price,stock,warranty_months,rating,rating_count,is_featured) VALUES
 ((SELECT id FROM categories WHERE slug='laptops'),'Apple MacBook Pro 14 M3 Pro','apple-macbook-pro-14-m3-pro','Apple','Pro-level performance in a 14-inch Liquid Retina XDR display. Built for editing, coding and heavy multitasking.','{"Processor": "Apple M3 Pro 11-core", "RAM": "18GB Unified", "Storage": "512GB SSD", "Display": "14.2\" Liquid Retina XDR", "Graphics": "14-core GPU", "OS": "macOS", "Weight": "1.61 kg", "Battery": "70Wh"}',199900,214900,3,12,4.9,186,1),
 ((SELECT id FROM categories WHERE slug='laptops'),'Dell XPS 14 9440','dell-xps-14-9440','Dell','Edge-to-edge 14.5-inch OLED touch display in a CNC aluminium body with Intel Core Ultra power.','{"Processor": "Intel Core Ultra 7 155H", "RAM": "16GB LPDDR5x", "Storage": "1TB NVMe SSD", "Display": "14.5\" 3.2K OLED Touch", "Graphics": "Intel Arc", "OS": "Windows 11 Home", "Weight": "1.68 kg", "Battery": "69.5Wh"}',174990,189990,4,12,4.7,92,1),
 ((SELECT id FROM categories WHERE slug='laptops'),'Lenovo ThinkPad X1 Carbon Gen 12','lenovo-thinkpad-x1-carbon-gen-12','Lenovo','Ultralight business flagship with a carbon-fibre chassis, legendary keyboard and all-day battery.','{"Processor": "Intel Core Ultra 7 155U", "RAM": "32GB LPDDR5x", "Storage": "1TB NVMe SSD", "Display": "14\" 2.8K OLED", "Graphics": "Intel Graphics", "OS": "Windows 11 Pro", "Weight": "1.09 kg", "Battery": "57Wh"}',189990,204990,3,36,4.8,74,1),
 ((SELECT id FROM categories WHERE slug='laptops'),'ASUS ROG Zephyrus G14','asus-rog-zephyrus-g14','ASUS','Compact 14-inch gaming powerhouse with a 120Hz OLED display and RTX graphics.','{"Processor": "AMD Ryzen 9 8945HS", "RAM": "16GB LPDDR5X", "Storage": "1TB NVMe SSD", "Display": "14\" 3K OLED 120Hz", "Graphics": "NVIDIA RTX 4060 8GB", "OS": "Windows 11 Home", "Weight": "1.5 kg", "Battery": "73Wh"}',159990,174990,5,12,4.7,128,1),
 ((SELECT id FROM categories WHERE slug='laptops'),'HP Spectre x360 14','hp-spectre-x360-14','HP','Gem-cut 2-in-1 with a 3K OLED touch display, pen support and a premium aluminium build.','{"Processor": "Intel Core Ultra 7 155H", "RAM": "16GB LPDDR5x", "Storage": "1TB NVMe SSD", "Display": "14\" 2.8K OLED Touch", "Graphics": "Intel Arc", "OS": "Windows 11 Home", "Weight": "1.4 kg", "Battery": "68Wh"}',154990,169990,4,12,4.6,63,1),
 ((SELECT id FROM categories WHERE slug='laptops'),'ASUS Zenbook 14 OLED','asus-zenbook-14-oled','ASUS','Thin and light 14-inch laptop with a vivid 120Hz OLED display and a premium finish.','{"Processor": "Intel Core Ultra 7 155H", "RAM": "16GB LPDDR5X", "Storage": "1TB NVMe SSD", "Display": "14\" 3K OLED 120Hz", "Graphics": "Intel Arc", "OS": "Windows 11 Home", "Weight": "1.2 kg", "Battery": "75Wh"}',89990,99990,9,12,4.5,141,1);

-- Warranty (months) and rating per product. Ratings here are SAMPLE values: replace with real customer ratings.
UPDATE products SET warranty_months=12, rating=4.4, rating_count=312 WHERE slug='dell-inspiron-15-3520';
UPDATE products SET warranty_months=12, rating=4.5, rating_count=204 WHERE slug='hp-pavilion-14-ec1005';
UPDATE products SET warranty_months=12, rating=4.2, rating_count=418 WHERE slug='lenovo-ideapad-slim-3';
UPDATE products SET warranty_months=12, rating=4.6, rating_count=267 WHERE slug='asus-tuf-gaming-f15';
UPDATE products SET warranty_months=12, rating=4.9, rating_count=521 WHERE slug='apple-macbook-air-m2';
UPDATE products SET warranty_months=12, rating=4.3, rating_count=189 WHERE slug='acer-aspire-5';
UPDATE products SET warranty_months=12, rating=4.5, rating_count=980 WHERE slug='logitech-m331-silent-mouse';
UPDATE products SET warranty_months=6, rating=4.1, rating_count=356 WHERE slug='redgear-a15-gaming-mouse';
UPDATE products SET warranty_months=12, rating=4.6, rating_count=742 WHERE slug='logitech-k380-keyboard';
UPDATE products SET warranty_months=6, rating=4.4, rating_count=233 WHERE slug='wildcraft-laptop-backpack';
UPDATE products SET warranty_months=0, rating=4.0, rating_count=121 WHERE slug='neoprene-sleeve-14';
UPDATE products SET warranty_months=18, rating=4.7, rating_count=389 WHERE slug='anker-65w-gan-charger';
UPDATE products SET warranty_months=6, rating=4.2, rating_count=167 WHERE slug='usbc-cable-2m-100w';
UPDATE products SET warranty_months=36, rating=4.8, rating_count=611 WHERE slug='samsung-t7-ssd-1tb';
UPDATE products SET warranty_months=60, rating=4.5, rating_count=874 WHERE slug='sandisk-ultra-128gb-pendrive';
UPDATE products SET warranty_months=24, rating=4.6, rating_count=1203 WHERE slug='logitech-g102-gaming-mouse';
UPDATE products SET warranty_months=12, rating=4.1, rating_count=298 WHERE slug='redgear-cosmo-71-headset';
UPDATE products SET warranty_months=12, rating=4.3, rating_count=2210 WHERE slug='boat-rockerz-450';
UPDATE products SET warranty_months=12, rating=4.6, rating_count=1450 WHERE slug='jbl-go-3-speaker';
UPDATE products SET warranty_months=24, rating=4.4, rating_count=812 WHERE slug='logitech-c270-webcam';
UPDATE products SET warranty_months=6, rating=4.3, rating_count=276 WHERE slug='aluminium-laptop-stand';
UPDATE products SET warranty_months=6, rating=4.0, rating_count=144 WHERE slug='laptop-cooling-pad-5-fans';
UPDATE products SET warranty_months=0, rating=4.2, rating_count=330 WHERE slug='large-mouse-pad-80x30';
