<?php
require_once __DIR__ . '/db.php';

$db = getDB();

$db->exec("CREATE TABLE IF NOT EXISTS users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    username      TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    name          TEXT NOT NULL,
    role          TEXT DEFAULT 'staff',
    last_access   TEXT,
    created_at    TEXT DEFAULT (datetime('now'))
)");

$db->exec("CREATE TABLE IF NOT EXISTS hotels (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT    NOT NULL,
    description TEXT,
    location    TEXT,
    stars       INTEGER DEFAULT 3,
    base_price  REAL    DEFAULT 0.0,
    verified    INTEGER DEFAULT 0,
    created_at  TEXT    DEFAULT (datetime('now'))
)");

$db->exec("CREATE TABLE IF NOT EXISTS hotel_images (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    hotel_id   INTEGER NOT NULL,
    url        TEXT    NOT NULL,
    is_main    INTEGER DEFAULT 0,
    sort_order INTEGER DEFAULT 0,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS amenities (
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    name     TEXT NOT NULL,
    icon     TEXT,
    category TEXT DEFAULT 'general'
)");

$db->exec("CREATE TABLE IF NOT EXISTS hotel_amenities (
    hotel_id   INTEGER NOT NULL,
    amenity_id INTEGER NOT NULL,
    PRIMARY KEY (hotel_id, amenity_id),
    FOREIGN KEY (hotel_id)   REFERENCES hotels(id),
    FOREIGN KEY (amenity_id) REFERENCES amenities(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS room_types (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    hotel_id    INTEGER NOT NULL,
    name        TEXT    NOT NULL,
    description TEXT,
    price       REAL    NOT NULL,
    capacity    INTEGER DEFAULT 2,
    image_url   TEXT,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS rooms (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    hotel_id     INTEGER NOT NULL,
    room_type_id INTEGER NOT NULL,
    number       TEXT    NOT NULL,
    floor        INTEGER,
    status       TEXT    DEFAULT 'available',
    FOREIGN KEY (hotel_id)     REFERENCES hotels(id),
    FOREIGN KEY (room_type_id) REFERENCES room_types(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS guests (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name  TEXT NOT NULL,
    last_name   TEXT NOT NULL,
    email       TEXT NOT NULL UNIQUE,
    phone       TEXT,
    nationality TEXT,
    id_document TEXT,
    created_at  TEXT DEFAULT (datetime('now'))
)");

$db->exec("CREATE TABLE IF NOT EXISTS reservations (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    guest_id    INTEGER NOT NULL,
    room_id     INTEGER NOT NULL,
    check_in    TEXT    NOT NULL,
    check_out   TEXT    NOT NULL,
    status      TEXT    DEFAULT 'pending',
    total_price REAL    DEFAULT 0.0,
    notes       TEXT,
    created_at  TEXT    DEFAULT (datetime('now')),
    updated_at  TEXT    DEFAULT (datetime('now')),
    FOREIGN KEY (guest_id) REFERENCES guests(id),
    FOREIGN KEY (room_id)  REFERENCES rooms(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS reviews (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    hotel_id       INTEGER NOT NULL,
    guest_id       INTEGER,
    reservation_id INTEGER,
    rating         INTEGER NOT NULL,
    comment        TEXT,
    reviewer_city  TEXT,
    created_at     TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (hotel_id)       REFERENCES hotels(id),
    FOREIGN KEY (guest_id)       REFERENCES guests(id),
    FOREIGN KEY (reservation_id) REFERENCES reservations(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS hotel_rules (
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    hotel_id INTEGER NOT NULL,
    rule     TEXT    NOT NULL,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    email      TEXT    NOT NULL UNIQUE,
    subscribed INTEGER DEFAULT 1,
    created_at TEXT    DEFAULT (datetime('now'))
)");

$db->exec("CREATE TABLE IF NOT EXISTS access_log (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id     INTEGER NOT NULL,
    username    TEXT    NOT NULL,
    accessed_at TEXT    DEFAULT (datetime('now')),
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

echo "Base de dados criada com sucesso em: " . DB_PATH . "\n";
unset($db);