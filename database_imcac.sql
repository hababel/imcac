
-- IMCAC Database Schema and Seed Data

DROP DATABASE IF EXISTS database_imcac;
CREATE DATABASE database_imcac CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE database_imcac;

-- Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('ADMIN','ENCARGADO','INTEGRANTE') NOT NULL,
    team_creation_limit INT NOT NULL DEFAULT 1,
    status ENUM('ACTIVE','DISABLED') NOT NULL DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Teams
CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    plan_id INT NOT NULL,
    manager_user_id INT NOT NULL,
    size_limit INT NOT NULL,
    current_stage ENUM('DIAGNOSTIC','TRAINING','DOCUMENTATION','FOLLOWUP','CLOSURE') DEFAULT 'DIAGNOSTIC',
    status ENUM('REGISTERED','PAID') DEFAULT 'REGISTERED',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Plans
CREATE TABLE plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    min_size INT,
    max_size INT,
    price_usd DECIMAL(10,2)
);

-- Payments
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT,
    amount DECIMAL(10,2),
    status ENUM('PENDING','PAID','FAILED') DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Participants
CREATE TABLE participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT,
    name VARCHAR(100),
    email VARCHAR(150),
    role VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Invitations
CREATE TABLE invitations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT,
    participant_id INT,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME,
    consumed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Forms (IMCAC Questionnaire)
CREATE TABLE forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    email_template_html TEXT NULL,
    calculation_formula TEXT NULL,
    status ENUM('DRAFT','PUBLISHED','DISABLED') NOT NULL DEFAULT 'DRAFT'
);

CREATE TABLE form_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_id INT,
    name VARCHAR(100),
    description TEXT,
    weight DECIMAL(5,2)
);

CREATE TABLE form_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT,
    question TEXT
);

CREATE TABLE form_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT,
    label VARCHAR(200),
    value INT,
    justification TEXT NULL
);

-- Submissions
CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participant_id INT,
    form_id INT,
    global_field_set_id INT, -- Vincula la respuesta a una versión de campos globales
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT,
    question_id INT,
    answer_value INT
);

-- IMCAC Scores
CREATE TABLE imcac_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT,
    score_total DECIMAL(6,2),
    scores_by_group JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Audit Logs
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    actor_user_id INT,
    action VARCHAR(100),
    entity VARCHAR(100),
    entity_id INT,
    meta JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Access Codes (2FA)
CREATE TABLE access_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    consumed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Version sets for Global Form Fields
CREATE TABLE global_field_sets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Global Form Fields (Header for all surveys)
CREATE TABLE global_form_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(255) NOT NULL,
    placeholder VARCHAR(255) NULL,
    field_type ENUM('text', 'textarea', 'select', 'radio', 'range') NOT NULL DEFAULT 'text',
    is_required BOOLEAN NOT NULL DEFAULT FALSE,
    options JSON NULL,
    field_set_id INT NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Answers for Global Form Fields
CREATE TABLE global_field_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    global_form_field_id INT NOT NULL,
    answer_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed default settings for survey header
INSERT INTO settings (setting_key, setting_value) VALUES
('active_global_field_set_id', '1');

-- Seed the first version of global fields
INSERT INTO global_field_sets (id, name) VALUES (1, 'Versión Inicial');
-- Seed some example global fields
INSERT INTO global_form_fields (label, placeholder, is_required, sort_order, field_set_id) VALUES
('Nombre Completo', 'Tu nombre y apellido', TRUE, 1, 1),
('Correo Electrónico', 'Tu dirección de correo', TRUE, 2, 1);

-- Seed Plans
INSERT INTO plans (name, min_size, max_size, price_usd) VALUES
 ('Plan 1 (1-5)',1,5,500.00),
 ('Plan 2 (6-20)',6,20,950.00);

-- Seed Users (admin and encargados)
INSERT INTO users (name,email,password_hash,role) VALUES
 ('Admin Root','admin@example.com','hash_admin','ADMIN'),
 ('Carlos Encargado','carlos@example.com','hash_carlos','ENCARGADO'),
 ('Maria Encargada','maria@example.com','hash_maria','ENCARGADO'),
 ('Jose Encargado','jose@example.com','hash_jose','ENCARGADO'),
 ('Ana Integrante','ana@example.com','hash_ana','INTEGRANTE'),
 ('Luis Integrante','luis@example.com','hash_luis','INTEGRANTE');

-- Seed Teams
INSERT INTO teams (name,plan_id,manager_user_id,size_limit,status) VALUES
 ('Equipo Alfa',1,2,5,'PAID'),
 ('Equipo Beta',2,3,15,'PAID'),
 ('Equipo Gamma',1,4,5,'PAID'),
 ('Equipo Delta',2,3,10,'PAID'),
 ('Equipo Epsilon',1,2,5,'REGISTERED');

-- Seed Payments
INSERT INTO payments (team_id,amount,status) VALUES
 (1,500,'PAID'),
 (2,950,'PAID'),
 (3,500,'PAID'),
 (4,950,'PAID'),
 (5,500,'PENDING');

-- Example Participants
INSERT INTO participants (team_id,name,email,role) VALUES
 (1,'Ana Integrante','ana@example.com','INTEGRANTE'),
 (1,'Luis Integrante','luis@example.com','INTEGRANTE'),
 (2,'Pedro Invitado','pedro@example.com','INTEGRANTE'),
 (2,'Sofia Invitada','sofia@example.com','INTEGRANTE'),
 (3,'Juan Integrante','juan@example.com','INTEGRANTE');
