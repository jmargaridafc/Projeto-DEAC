<?php
define('DB_PATH', __DIR__ . '/../data/hotel.db');

function getDB(): SQLite3 {
    static $db = null;

    if ($db === null) {
        $dir = dirname(DB_PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $db = new SQLite3(DB_PATH);
        $db->exec('PRAGMA foreign_keys = ON;');
    }

    return $db;
}
