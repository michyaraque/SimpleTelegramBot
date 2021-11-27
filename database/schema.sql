CREATE TABLE IF NOT EXISTS users (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id BIGINT SIGNED NOT NULL,
  username varchar(30) NOT NULL,
  real_name varchar(60) NOT NULL,
  register_date int(15) NOT NULL,
  rol_id int(2) NOT NULL DEFAULT 0,
  term_conditions varchar(10) NOT NULL,
  language varchar(4) NOT NULL DEFAULT 'es',
  banned int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY(id),
  UNIQUE KEY(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS conversation(
  user_id BIGINT SIGNED NOT NULL,
  step varchar(50) NOT NULL,
  temp_data text NOT NULL,
  FOREIGN KEY(user_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS roles (
  id int(2) NOT NULL,
  name varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO roles VALUES (0, 'None'),(1, 'Editor'),(2, 'Moderator'),(999, 'Admin');

DELIMITER //
CREATE TRIGGER oncreate_user AFTER INSERT ON users
FOR EACH ROW BEGIN
  INSERT INTO conversation (user_id) VALUES (new.user_id);
END
//

DELIMITER //
CREATE TRIGGER ondelete_user BEFORE DELETE ON users
FOR EACH ROW BEGIN
	DELETE FROM conversation WHERE user_id = OLD.user_id;
END
//