-- Laptop finder: use cases + which laptop fits which use case (a laptop can have many).
-- Safe to run more than once.
CREATE TABLE IF NOT EXISTS use_cases (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    slug       TEXT NOT NULL UNIQUE,
    sort_order INTEGER NOT NULL DEFAULT 0,
    is_active  INTEGER NOT NULL DEFAULT 1
);
CREATE TABLE IF NOT EXISTS product_use_cases (
    product_id  INTEGER NOT NULL,
    use_case_id INTEGER NOT NULL,
    PRIMARY KEY (product_id, use_case_id),
    FOREIGN KEY (product_id)  REFERENCES products(id)  ON DELETE CASCADE,
    FOREIGN KEY (use_case_id) REFERENCES use_cases(id) ON DELETE CASCADE
);
INSERT OR IGNORE INTO use_cases (name, slug, sort_order) VALUES
    ('Office & business', 'office',    1),
    ('Student',           'student',   2),
    ('Everyday use',      'everyday',  3),
    ('Coding & dev',      'coding',    4),
    ('Design & editing',  'design',    5),
    ('Gaming',            'gaming',    6);

-- Starter mapping (change freely; admin form will edit these later)
INSERT OR IGNORE INTO product_use_cases (product_id, use_case_id)
SELECT p.id, u.id FROM products p JOIN use_cases u ON (
    (p.slug IN (SELECT slug FROM products WHERE name LIKE 'Dell Inspiron 15%')        AND u.slug IN ('office','student','everyday')) OR
    (p.name LIKE 'HP Pavilion 14%'          AND u.slug IN ('student','everyday','office','coding')) OR
    (p.name LIKE 'Lenovo IdeaPad Slim 3%'   AND u.slug IN ('student','everyday','office')) OR
    (p.name LIKE 'ASUS TUF Gaming F15%'     AND u.slug IN ('gaming','coding')) OR
    (p.name LIKE 'Apple MacBook Air M2%'    AND u.slug IN ('student','everyday','office','coding','design')) OR
    (p.name LIKE 'Acer Aspire 5%'           AND u.slug IN ('student','everyday','office','coding')) OR
    (p.name LIKE 'Apple MacBook Pro 14%'    AND u.slug IN ('design','coding','office')) OR
    (p.name LIKE 'Dell XPS 14%'             AND u.slug IN ('design','office','coding')) OR
    (p.name LIKE 'Lenovo ThinkPad X1%'      AND u.slug IN ('office','coding')) OR
    (p.name LIKE 'ASUS ROG Zephyrus%'       AND u.slug IN ('gaming','design','coding')) OR
    (p.name LIKE 'HP Spectre x360%'         AND u.slug IN ('office','design','everyday')) OR
    (p.name LIKE 'ASUS Zenbook 14%'         AND u.slug IN ('student','office','design','everyday','coding'))
);
