PRAGMA foreign_keys = ON;

-- Store identity, theme and options (one row per setting). Edited from admin later.
CREATE TABLE settings (
    setting_key   TEXT PRIMARY KEY,
    setting_value TEXT,
    group_name    TEXT NOT NULL DEFAULT 'general',   -- store, theme, hero, features, checkout, social, seo
    label         TEXT,                               -- friendly name for the admin form
    input_type    TEXT NOT NULL DEFAULT 'text'        -- text, textarea, color, number, image
);

CREATE TABLE categories (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    slug       TEXT NOT NULL UNIQUE,
    sort_order INTEGER NOT NULL DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    name        TEXT NOT NULL,
    slug        TEXT NOT NULL UNIQUE,
    brand       TEXT,
    description TEXT,
    specs       TEXT,                        -- JSON string
    price       NUMERIC NOT NULL,
    old_price   NUMERIC,
    stock       INTEGER NOT NULL DEFAULT 0,
    warranty_months INTEGER NOT NULL DEFAULT 12,   -- 0 = no warranty shown on the card
    rating      REAL,                                 -- 0 to 5, NULL = no rating shown
    rating_count INTEGER NOT NULL DEFAULT 0,
    is_featured INTEGER NOT NULL DEFAULT 0,
    is_active   INTEGER NOT NULL DEFAULT 1,
    created_at  TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- One product -> many images. Filename only (file lives in storefront/uploads/)
CREATE TABLE product_images (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    filename   TEXT NOT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0,   -- 0 = main image
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE orders (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    order_number   TEXT NOT NULL UNIQUE,
    customer_name  TEXT NOT NULL,
    customer_email TEXT,
    customer_phone TEXT NOT NULL,
    address        TEXT NOT NULL,
    city           TEXT NOT NULL,
    pincode        TEXT NOT NULL,
    shipping       NUMERIC NOT NULL DEFAULT 0,
    total          NUMERIC NOT NULL,
    payment_method TEXT NOT NULL DEFAULT 'cod',
    status         TEXT NOT NULL DEFAULT 'pending',  -- pending, confirmed, shipped, delivered, cancelled
    created_at     TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_items (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id     INTEGER NOT NULL,
    product_id   INTEGER NOT NULL,
    product_name TEXT NOT NULL,
    price        NUMERIC NOT NULL,
    quantity     INTEGER NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE admins (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    username      TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at    TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE banners (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    title      TEXT NOT NULL UNIQUE,
    subtitle   TEXT,
    cta_text   TEXT NOT NULL DEFAULT 'Shop now',
    cta_link   TEXT NOT NULL DEFAULT 'products',   -- path inside the store, e.g. products, laptops, products?deals=1
    sort_order INTEGER NOT NULL DEFAULT 0,
    is_active  INTEGER NOT NULL DEFAULT 1
);

CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_images_product    ON product_images(product_id, sort_order);
CREATE INDEX idx_orders_status     ON orders(status);
