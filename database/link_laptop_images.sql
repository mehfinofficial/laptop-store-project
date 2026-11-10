-- Links the laptop photos you pasted into storefront/uploads/ to their products.
-- Names have no extension on purpose: the store finds the file whatever its extension, case or spacing.
-- Safe to run again (skips products that already have that image).
INSERT INTO product_images (product_id, filename, sort_order)
SELECT p.id, m.filename, 0
FROM (
  SELECT 'apple-macbook-pro-14-m3-pro' AS slug, 'AppleMacBookPro14' AS filename UNION ALL
  SELECT 'asus-tuf-gaming-f15',               'ASUSTUFF15' UNION ALL
  SELECT 'dell-xps-14-9440',                  'DellXPS14' UNION ALL
  SELECT 'asus-zenbook-14-oled',              'ASUSZenbook14' UNION ALL
  SELECT 'apple-macbook-air-m2',              'Apple MacBook Air' UNION ALL
  SELECT 'hp-spectre-x360-14',                'LAPTOPHPSPECTRE x360' UNION ALL
  SELECT 'asus-rog-zephyrus-g14',             'ASUSROGZephyrusG14' UNION ALL
  SELECT 'lenovo-thinkpad-x1-carbon-gen-12',  'LenovoThinkPadX1Carbon'
) m
JOIN products p ON p.slug = m.slug
WHERE NOT EXISTS (SELECT 1 FROM product_images i WHERE i.product_id = p.id AND i.filename = m.filename);
