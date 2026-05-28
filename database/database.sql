PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS review;
DROP TABLE IF EXISTS trainer_review;
DROP TABLE IF EXISTS equipment_rentals;
DROP TABLE IF EXISTS waitlist;
DROP TABLE IF EXISTS enrollment;
DROP TABLE IF EXISTS class_session;
DROP TABLE IF EXISTS class;
DROP TABLE IF EXISTS trainer;
DROP TABLE IF EXISTS equipment;
DROP TABLE IF EXISTS user;

CREATE TABLE user (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  username TEXT NOT NULL UNIQUE,
  email TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL CHECK (role IN ('member', 'trainer', 'admin')),
  photo TEXT,
  is_active INTEGER NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE trainer (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL UNIQUE,
  bio TEXT,
  specializations TEXT,
  certifications TEXT,
  rating REAL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);

CREATE TABLE equipment (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  category TEXT NOT NULL,
  status TEXT NOT NULL CHECK (status IN ('available', 'in_use', 'maintenance')),
  location TEXT,
  total_quantity INTEGER NOT NULL DEFAULT 1 CHECK (total_quantity >= 0),
  maintenance_quantity INTEGER NOT NULL DEFAULT 0 CHECK (maintenance_quantity >= 0),
  rental_available INTEGER NOT NULL DEFAULT 0 CHECK (rental_available IN (0, 1))
);

CREATE TABLE equipment_rentals (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  equipment_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL,
  start_time DATETIME NOT NULL,
  end_time DATETIME NOT NULL,
  status TEXT NOT NULL CHECK (status IN ('active', 'cancelled', 'completed')),
  FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);

CREATE UNIQUE INDEX one_active_equipment_rental_per_user_equipment_day
ON equipment_rentals(user_id, equipment_id, DATE(start_time))
WHERE status = 'active';

CREATE TABLE trainer_review (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  trainer_id INTEGER NOT NULL,
  rating INTEGER NOT NULL CHECK (rating BETWEEN 0 AND 5),
  comment TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
  FOREIGN KEY (trainer_id) REFERENCES trainer(id) ON DELETE CASCADE,
  UNIQUE(user_id, trainer_id)
);

CREATE TABLE class (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  trainer_id INTEGER,
  name TEXT NOT NULL,
  description TEXT,
  type TEXT NOT NULL,
  intensity TEXT CHECK (intensity IN ('Baixa', 'Média', 'Alta')),
  duration INTEGER NOT NULL,
  capacity INTEGER NOT NULL,
  image TEXT,
  FOREIGN KEY (trainer_id) REFERENCES trainer(id) ON DELETE SET NULL
);

CREATE TABLE class_session (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  class_id INTEGER NOT NULL,
  session_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  room TEXT,
  FOREIGN KEY (class_id) REFERENCES class(id) ON DELETE CASCADE
);

CREATE TABLE enrollment (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  session_id INTEGER NOT NULL,
  status TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'cancelled')),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
  FOREIGN KEY (session_id) REFERENCES class_session(id) ON DELETE CASCADE,
  UNIQUE(user_id, session_id)
);


CREATE TABLE waitlist (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  session_id INTEGER NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
  FOREIGN KEY (session_id) REFERENCES class_session(id) ON DELETE CASCADE,
  UNIQUE(user_id, session_id)
);

CREATE TABLE review (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  class_id INTEGER NOT NULL,
  rating INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES class(id) ON DELETE CASCADE,
  UNIQUE(user_id, class_id)
);
