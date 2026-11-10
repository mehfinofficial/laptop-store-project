<?php
// Run from terminal:  php setup.php      (add --reset to rebuild from scratch)
$dbFile = __DIR__ . '/store.sqlite';

if (in_array('--reset', $argv ?? []) && file_exists($dbFile)) {
    unlink($dbFile);
    @unlink($dbFile . '-wal');
    @unlink($dbFile . '-shm');
}
if (file_exists($dbFile)) {
    exit("store.sqlite already exists. Use: php setup.php --reset\n");
}

$pdo = new PDO('sqlite:' . $dbFile, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec(file_get_contents(__DIR__ . '/schema.sqlite.sql'));
$pdo->exec(file_get_contents(__DIR__ . '/seed.sql'));

echo "Done. Settings: " . $pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn()
   . ", Products: " . $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() . "\n";
