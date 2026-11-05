CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    display_name TEXT NOT NULL,
    password_hash TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS rooms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
);
CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_id INTEGER NOT NULL REFERENCES rooms(id),
    user_id INTEGER NOT NULL REFERENCES users(id),
    content TEXT NOT NULL CHECK (length(content) BETWEEN 1 AND 500),
    created_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS messages_room_id ON messages(room_id, id);
CREATE TABLE IF NOT EXISTS sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER REFERENCES users(id),
    token_hash TEXT NOT NULL UNIQUE,
    csrf_token TEXT NOT NULL,
    expires_at INTEGER NOT NULL,
    revoked_at INTEGER
);
CREATE INDEX IF NOT EXISTS sessions_expiry ON sessions(expires_at);

CREATE TABLE IF NOT EXISTS friendships (
    user_low INTEGER NOT NULL REFERENCES users(id),
    user_high INTEGER NOT NULL REFERENCES users(id),
    sender_id INTEGER NOT NULL REFERENCES users(id),
    status TEXT NOT NULL CHECK(status IN ('pending', 'accepted')),
    PRIMARY KEY(user_low, user_high),
    CHECK(user_low < user_high),
    CHECK(sender_id IN (user_low, user_high))
);
CREATE TABLE IF NOT EXISTS private_groups (
    room_id INTEGER PRIMARY KEY REFERENCES rooms(id),
    owner_id INTEGER NOT NULL REFERENCES users(id)
);
CREATE TABLE IF NOT EXISTS group_members (
    room_id INTEGER NOT NULL REFERENCES private_groups(room_id),
    user_id INTEGER NOT NULL REFERENCES users(id),
    PRIMARY KEY(room_id, user_id)
);
CREATE INDEX IF NOT EXISTS group_members_user ON group_members(user_id, room_id);
CREATE TABLE IF NOT EXISTS user_locations (
    user_id INTEGER PRIMARY KEY REFERENCES users(id),
    latitude REAL NOT NULL CHECK(latitude BETWEEN -90 AND 90),
    longitude REAL NOT NULL CHECK(longitude BETWEEN -180 AND 180),
    updated_at INTEGER NOT NULL
);
