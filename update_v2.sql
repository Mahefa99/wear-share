-- À importer UNE SEULE FOIS si ta base "rewear" existe déjà (phpMyAdmin > rewear > Importer).
-- Ajoute la table du "mot de passe oublié". Rien d'autre ne change dans la base.
USE rewear;
CREATE TABLE IF NOT EXISTS password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
