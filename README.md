# Laptop store project

- `database/`   SQLite DB (`store.sqlite` already built with sample data). Rebuild: `php database/setup.php --reset`
- `storefront/` customer site (PHP-rendered, clean URLs, SEO)
- `admin/`      admin panel (PHP API + JS frontend)

## Run locally
XAMPP/Apache: put the folder in htdocs and open `/laptop-store-project/storefront/` (mod_rewrite on, AllowOverride All).
Or PHP built-in server:  cd storefront && php -S localhost:8000 router.php

The `database/` folder must be writable by PHP (SQLite WAL needs to create extra files).

## Re-brand for another store
Edit the `settings` table: store name, phone, address, colours, fonts, hero text, shipping fee.
Product images: copy files to `storefront/uploads/` and add rows to `product_images` (sort_order 0 = main image).
