CREATE DATABASE IF NOT EXISTS fitness_tracker
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE fitness_tracker;

CREATE TABLE users (
    id            INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)     NOT NULL UNIQUE,
    email         VARCHAR(100)    NOT NULL UNIQUE,
    password_hash VARCHAR(255)    NOT NULL,
    weight_kg     DECIMAL(5,2)    DEFAULT NULL,
    height_cm     DECIMAL(5,2)    DEFAULT NULL,
    created_at    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE workouts (
    id               INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED   NOT NULL,
    title            VARCHAR(100)   NOT NULL,
    date             DATE           NOT NULL,
    duration_minutes INT UNSIGNED   DEFAULT NULL,
    notes            TEXT           DEFAULT NULL,
    created_at       TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE exercises (
    id          INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED   NOT NULL,
    name        VARCHAR(100)   NOT NULL,
    category    ENUM('Kraft', 'Cardio', 'Stretching') NOT NULL,
    description TEXT           DEFAULT NULL,
    UNIQUE KEY unique_user_exercise_name (user_id, name),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE workout_exercises (
    id               INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    workout_id       INT UNSIGNED   NOT NULL,
    exercise_id      INT UNSIGNED   NOT NULL,
    sets             INT UNSIGNED   DEFAULT NULL,
    reps             INT UNSIGNED   DEFAULT NULL,
    weight_kg        DECIMAL(5,2)   DEFAULT NULL,
    duration_seconds INT UNSIGNED   DEFAULT NULL,
    FOREIGN KEY (workout_id)  REFERENCES workouts(id)  ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE
);

CREATE TABLE goals (
    id            INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED   NOT NULL,
    description   VARCHAR(255)   NOT NULL,
    target_value  DECIMAL(8,2)   DEFAULT NULL,
    current_value DECIMAL(8,2)   DEFAULT 0,
    unit          VARCHAR(30)    DEFAULT NULL,
    deadline      DATE           DEFAULT NULL,
    status        ENUM('aktiv', 'erreicht', 'abgebrochen') DEFAULT 'aktiv',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
