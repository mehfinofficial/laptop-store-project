-- Run once on an existing store.sqlite (keeps your products, images and orders).
UPDATE settings SET setting_value='#6D3CD9' WHERE setting_key='color_primary';
UPDATE settings SET setting_value='#4F2AB0' WHERE setting_key='color_primary_dark';
UPDATE settings SET setting_value='#F6F5FB' WHERE setting_key='color_bg';
UPDATE settings SET setting_value='#14122B' WHERE setting_key='color_ink';
UPDATE settings SET setting_value='#6B6880' WHERE setting_key='color_muted';
UPDATE settings SET setting_value='#E6E3F1' WHERE setting_key='color_line';
UPDATE settings SET setting_value='Plus Jakarta Sans' WHERE setting_key IN ('font_heading','font_body');
UPDATE settings SET setting_value='Work-ready laptops, clear prices' WHERE setting_key='hero_title';
UPDATE settings SET setting_value='Compare real specs and order with cash on delivery.' WHERE setting_key='hero_subtitle';

INSERT OR IGNORE INTO settings (setting_key,setting_value,group_name,label,input_type) VALUES
 ('color_sale','#E5383B','theme','Sale / discount colour','color'),
 ('hero_cta','Shop now','hero','Hero button text','text'),
 ('hero_image','Laptop','hero','Hero image name (in assets/image, no extension needed)','text');
