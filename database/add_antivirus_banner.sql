-- Run ONCE on your existing database (adds banner colour theme + the Antivirus slide).
-- Needs the `image` column from add_elitebook_banner.sql first.

ALTER TABLE banners ADD COLUMN theme TEXT;

INSERT OR IGNORE INTO banners (title, subtitle, cta_text, cta_link, sort_order, image, theme)
VALUES ('Premium Antivirus, now ₹300', 'Advanced protection for your laptop. Flash offer.', 'Get protected', 'products?q=antivirus', 0, 'KasperskyAntivirus', 'green');
