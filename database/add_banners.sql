-- Run once on an existing store.sqlite (safe to run again, it will not duplicate).
CREATE TABLE IF NOT EXISTS banners (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    title      TEXT NOT NULL UNIQUE,
    subtitle   TEXT,
    cta_text   TEXT NOT NULL DEFAULT 'Shop now',
    cta_link   TEXT NOT NULL DEFAULT 'products',   -- path inside the store, e.g. products, laptops, products?deals=1
    sort_order INTEGER NOT NULL DEFAULT 0,
    is_active  INTEGER NOT NULL DEFAULT 1,
    image      TEXT,                               -- optional file name in storefront/assets/image (e.g. Laptop2); empty = main hero image
    theme      TEXT,                               -- optional colour theme for the whole top block (green); empty = store colours
    img_scale  INTEGER                             -- optional image zoom in % (100 = normal, 60 smaller, 130 bigger)
);

INSERT OR IGNORE INTO banners (title, subtitle, cta_text, cta_link, sort_order, image, theme, img_scale) VALUES
 ('HP EliteBook', 'Business-class laptops starting at ₹23,999', 'Shop now', 'laptops', 0, 'Laptop2', NULL, 105),
 ('Premium Antivirus, now ₹300', 'Advanced protection for your laptop. Flash offer.', 'Get protected', 'products?q=antivirus', 0, 'KasperskyAntivirus', 'green', NULL),
 ('Deals you''ll love', 'Save big on top laptops and accessories this week.', 'See deals', 'products?deals=1', 1, NULL, NULL, NULL),
 ('Built for work and study', 'Reliable laptops with the specs that actually matter.', 'Browse laptops', 'laptops', 2, NULL, NULL, NULL),
 ('Pay when it arrives', 'Cash on delivery on every order. No card needed.', 'Shop now', 'products', 3, NULL, NULL, NULL);
