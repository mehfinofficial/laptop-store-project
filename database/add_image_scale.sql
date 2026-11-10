-- Run ONCE: adds a per-banner image zoom (in %, 100 = normal, 60 = smaller, 130 = bigger).
ALTER TABLE banners ADD COLUMN img_scale INTEGER;

UPDATE banners SET img_scale = 105 WHERE image = 'Laptop2';

-- tune any banner later, e.g. make the antivirus box bigger:
-- UPDATE banners SET img_scale = 120 WHERE theme = 'green';
