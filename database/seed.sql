-- SETTINGS: change these to re-brand the project for another store
INSERT INTO settings (setting_key,setting_value,group_name,label,input_type) VALUES
 ('store_name','TechHub','store','Store name','text'),
 ('tagline','Laptops and accessories, priced clearly','store','Tagline','text'),
 ('logo','','store','Logo file (in uploads)','image'),
 ('phone','+91 98765 43210','store','Phone','text'),
 ('whatsapp','919876543210','store','WhatsApp number (digits only)','text'),
 ('email','hello@techhub.example','store','Email','text'),
 ('address','Shop 12, Main Market','store','Address','textarea'),
 ('city','Jammu','store','City','text'),
 ('opening_hours','Mon - Sat, 10am - 8pm','store','Opening hours','text'),
 ('currency_symbol','₹','store','Currency symbol','text'),
 ('footer_text','Thanks for shopping with us.','store','Footer text','text'),

 ('color_primary','#6D3CD9','theme','Primary colour','color'),
 ('color_primary_dark','#4F2AB0','theme','Primary dark','color'),
 ('color_accent','#FFC93C','theme','Accent colour','color'),
 ('color_bg','#F6F5FB','theme','Page background','color'),
 ('color_surface','#FFFFFF','theme','Card background','color'),
 ('color_ink','#14122B','theme','Text colour','color'),
 ('color_muted','#6B6880','theme','Muted text','color'),
 ('color_line','#E6E3F1','theme','Border colour','color'),
 ('font_heading','Plus Jakarta Sans','theme','Heading font (Google Fonts name)','text'),
 ('font_body','Plus Jakarta Sans','theme','Body font (Google Fonts name)','text'),
 ('radius','12','theme','Card corner radius (px)','number'),

 ('hero_title','Work-ready laptops, clear prices','hero','Hero headline','text'),
 ('hero_subtitle','Compare real specs and order with cash on delivery.','hero','Hero sub text','textarea'),
 ('hero_cta','Shop now','hero','Hero button text','text'),
 ('hero_image','Laptop','hero','Hero image name (in assets/image, no extension needed)','text'),
 ('color_sale','#E5383B','theme','Sale / discount colour','color'),
 ('hero_search_placeholder','Search laptops, mouse, chargers','hero','Search box hint','text'),

 ('feature1_title','Cash on delivery','features','Highlight 1 title','text'),
 ('feature1_text','Pay when your order arrives.','features','Highlight 1 text','text'),
 ('feature2_title','Brand warranty','features','Highlight 2 title','text'),
 ('feature2_text','Every laptop comes with the maker warranty.','features','Highlight 2 text','text'),
 ('feature3_title','Confirmed by phone','features','Highlight 3 title','text'),
 ('feature3_text','We call to confirm before we ship.','features','Highlight 3 text','text'),

 ('shipping_fee','99','checkout','Shipping fee','number'),
 ('free_shipping_above','10000','checkout','Free shipping above (0 = never)','number'),
 ('order_prefix','TH','checkout','Order number prefix','text'),
 ('items_per_page','12','checkout','Products per page','number'),
 ('max_qty_per_item','10','checkout','Max quantity per item','number'),

 ('meta_description','Buy laptops, mouse, keyboards, bags and chargers online. Real specs, clear prices, cash on delivery.','seo','Default meta description','textarea'),
 ('facebook','','social','Facebook URL','text'),
 ('instagram','','social','Instagram URL','text');

INSERT INTO categories (name, slug, sort_order) VALUES
 ('Laptops','laptops',1),('Mice & Keyboards','peripherals',3),('Accessories','accessories',7),
 ('Chargers & Power','chargers',6),('Storage','storage',5),('Gaming','gaming',2),('Audio','audio',4);

INSERT INTO products (category_id,name,slug,brand,description,specs,price,old_price,stock,is_featured) VALUES
(1,'Dell Inspiron 15 3520','dell-inspiron-15-3520','Dell','Reliable everyday laptop for work and study with a full HD display.','{"Processor":"Intel Core i5-1235U","RAM":"8GB DDR4","Storage":"512GB NVMe SSD","Display":"15.6\" FHD 120Hz","Graphics":"Intel Iris Xe","OS":"Windows 11 Home","Weight":"1.65 kg","Battery":"41Wh"}',52990,58990,12,1),
(1,'HP Pavilion 14 ec1005','hp-pavilion-14-ec1005','HP','Slim and light 14-inch laptop with a metal body and fast Ryzen performance.','{"Processor":"AMD Ryzen 5 5625U","RAM":"16GB DDR4","Storage":"512GB NVMe SSD","Display":"14\" FHD IPS","Graphics":"AMD Radeon","OS":"Windows 11 Home","Weight":"1.41 kg","Battery":"43Wh"}',56490,62999,8,1),
(1,'Lenovo IdeaPad Slim 3','lenovo-ideapad-slim-3','Lenovo','Budget-friendly laptop with solid battery life for students.','{"Processor":"Intel Core i3-1215U","RAM":"8GB DDR4","Storage":"256GB SSD","Display":"15.6\" FHD","Graphics":"Intel UHD","OS":"Windows 11 Home","Weight":"1.63 kg","Battery":"47Wh"}',37990,42990,20,0),
(1,'ASUS TUF Gaming F15','asus-tuf-gaming-f15','ASUS','Gaming laptop built tough, with a high refresh display and RTX graphics.','{"Processor":"Intel Core i5-11400H","RAM":"16GB DDR4","Storage":"512GB NVMe SSD","Display":"15.6\" FHD 144Hz","Graphics":"NVIDIA RTX 3050 4GB","OS":"Windows 11 Home","Weight":"2.3 kg","Battery":"48Wh"}',68990,79990,5,1),
(1,'Apple MacBook Air M2','apple-macbook-air-m2','Apple','Thin, fanless and powerful. All-day battery life with the M2 chip.','{"Processor":"Apple M2 8-core","RAM":"8GB Unified","Storage":"256GB SSD","Display":"13.6\" Liquid Retina","Graphics":"8-core GPU","OS":"macOS","Weight":"1.24 kg","Battery":"52.6Wh"}',99900,114900,4,1),
(1,'Acer Aspire 5','acer-aspire-5','Acer','Balanced performance laptop with a slim bezel display.','{"Processor":"Intel Core i5-1235U","RAM":"16GB DDR4","Storage":"512GB SSD","Display":"15.6\" FHD IPS","Graphics":"Intel Iris Xe","OS":"Windows 11 Home","Weight":"1.77 kg","Battery":"50Wh"}',49990,55990,10,0),
(2,'Logitech M331 Silent Wireless Mouse','logitech-m331-silent-mouse','Logitech','90% quieter clicks with a comfortable right-hand shape.','{"Connectivity":"2.4GHz Wireless","DPI":"1000","Battery":"24 months","Buttons":"3"}',999,1295,50,0),
(2,'Redgear A-15 Wired Gaming Mouse','redgear-a15-gaming-mouse','Redgear','RGB gaming mouse with adjustable DPI up to 6400.','{"Connectivity":"USB Wired","DPI":"6400","Lighting":"RGB","Buttons":"6"}',599,999,40,0),
(2,'Logitech K380 Bluetooth Keyboard','logitech-k380-keyboard','Logitech','Compact multi-device keyboard that pairs with up to 3 devices.','{"Connectivity":"Bluetooth","Layout":"Compact","Battery":"2 years","Devices":"Up to 3"}',2995,3995,25,1),
(3,'Wildcraft 15.6" Laptop Backpack','wildcraft-laptop-backpack','Wildcraft','Water-resistant backpack with padded laptop compartment.','{"Fits":"Up to 15.6\" laptops","Material":"Polyester","Capacity":"25L","Water Resistant":"Yes"}',1499,2199,30,0),
(3,'Neoprene Laptop Sleeve 14"','neoprene-sleeve-14','Generic','Slim protective sleeve with soft inner lining.','{"Fits":"13 to 14\" laptops","Material":"Neoprene","Closure":"Zipper"}',499,799,60,0),
(4,'Anker 65W USB-C GaN Charger','anker-65w-gan-charger','Anker','Compact fast charger for laptops, tablets and phones.','{"Output":"65W","Ports":"2 x USB-C, 1 x USB-A","Technology":"GaN","Cable Included":"No"}',3499,4499,22,1),
(4,'USB-C to USB-C Cable 2m 100W','usbc-cable-2m-100w','Generic','Braided cable supporting up to 100W power delivery.','{"Length":"2 m","Power":"100W PD","Data Speed":"480 Mbps","Material":"Braided Nylon"}',699,999,80,0),
(5,'Samsung T7 Portable SSD 1TB','samsung-t7-ssd-1tb','Samsung','Pocket-size external SSD with fast USB 3.2 speeds.','{"Capacity":"1TB","Interface":"USB 3.2 Gen 2","Read Speed":"1050 MB/s","Write Speed":"1000 MB/s"}',8999,11999,15,1),
(5,'SanDisk Ultra 128GB Pen Drive','sandisk-ultra-128gb-pendrive','SanDisk','USB 3.0 flash drive for quick file transfers.','{"Capacity":"128GB","Interface":"USB 3.0","Read Speed":"130 MB/s"}',899,1299,100,0);

INSERT OR IGNORE INTO banners (title, subtitle, cta_text, cta_link, sort_order) VALUES
 ('Deals you''ll love', 'Save big on top laptops and accessories this week.', 'See deals', 'products?deals=1', 1),
 ('Built for work and study', 'Reliable laptops with the specs that actually matter.', 'Browse laptops', 'laptops', 2),
 ('Pay when it arrives', 'Cash on delivery on every order. No card needed.', 'Shop now', 'products', 3);

-- Gaming mouse lives in Gaming
UPDATE products SET category_id=(SELECT id FROM categories WHERE slug='gaming') WHERE slug='redgear-a15-gaming-mouse';

-- Dummy products for the new categories
INSERT OR IGNORE INTO products (category_id,name,slug,brand,description,specs,price,old_price,stock,is_featured) VALUES
 ((SELECT id FROM categories WHERE slug='gaming'),'Logitech G102 Lightsync Gaming Mouse','logitech-g102-gaming-mouse','Logitech','Wired gaming mouse with RGB lighting and an accurate 8000 DPI sensor.','{"DPI": "8000", "Buttons": "6", "Connection": "Wired USB", "Lighting": "RGB"}',899,1495,40,1),
 ((SELECT id FROM categories WHERE slug='gaming'),'Redgear Cosmo 7.1 Gaming Headset','redgear-cosmo-71-headset','Redgear','USB gaming headset with virtual 7.1 surround sound and a built-in mic.','{"Sound": "Virtual 7.1", "Connection": "USB", "Driver": "50mm", "Microphone": "Yes"}',1299,2999,25,0),
 ((SELECT id FROM categories WHERE slug='audio'),'boAt Rockerz 450 Bluetooth Headphones','boat-rockerz-450','boAt','Wireless on-ear headphones with deep bass and long battery life.','{"Playback": "Up to 15 hours", "Connection": "Bluetooth 5.0", "Driver": "40mm", "Microphone": "Yes"}',1499,3990,50,1),
 ((SELECT id FROM categories WHERE slug='audio'),'JBL Go 3 Portable Speaker','jbl-go-3-speaker','JBL','Pocket-size waterproof Bluetooth speaker with punchy sound.','{"Playtime": "5 hours", "Water Resistance": "IP67", "Connection": "Bluetooth"}',2999,3999,30,0),
 ((SELECT id FROM categories WHERE slug='audio'),'Logitech C270 HD Webcam','logitech-c270-webcam','Logitech','Simple plug-and-play HD webcam for classes and video calls.','{"Resolution": "720p", "Microphone": "Built-in", "Connection": "USB-A"}',1799,2995,35,0),
 ((SELECT id FROM categories WHERE slug='accessories'),'Aluminium Laptop Stand Adjustable','aluminium-laptop-stand','Generic','Foldable aluminium stand that lifts your laptop to eye level and keeps it cool.','{"Fits": "10 to 17 inch laptops", "Material": "Aluminium", "Foldable": "Yes"}',1299,1999,45,1),
 ((SELECT id FROM categories WHERE slug='accessories'),'Laptop Cooling Pad with 5 Fans','laptop-cooling-pad-5-fans','Generic','Five quiet fans keep your laptop cool during long work or gaming sessions.','{"Fits": "Up to 17 inch laptops", "Fans": "5", "Power": "USB"}',1499,2499,20,0),
 ((SELECT id FROM categories WHERE slug='accessories'),'Large Mouse Pad 80x30 cm','large-mouse-pad-80x30','Generic','Extra-large smooth mouse pad with an anti-slip rubber base.','{"Size": "800 x 300 mm", "Base": "Anti-slip rubber", "Surface": "Smooth cloth"}',399,699,90,0);

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
