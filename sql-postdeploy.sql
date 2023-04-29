

CREATE TABLE users (
  id INT NOT NULL IDENTITY(1,1),
  username NVARCHAR(50) NOT NULL,
  password NVARCHAR(255) NOT NULL,
  name NVARCHAR(300) NOT NULL,
  CONSTRAINT PK_users PRIMARY KEY (id),
  CONSTRAINT AK_users_username UNIQUE (username)
);

INSERT INTO users (username, password, name) VALUES
('john', '$2a$12$9kOEWRS56PZPpUWv1hISk.rGDLRBYsSJNw9X8mBD7bz60YBTQ06Se', 'John Doe'),
('jane', '$2a$12$9kOEWRS56PZPpUWv1hISk.rGDLRBYsSJNw9X8mBD7bz60YBTQ06Se', 'Jane Doe'),
('bob', '$2a$12$9kOEWRS56PZPpUWv1hISk.rGDLRBYsSJNw9X8mBD7bz60YBTQ06Se', 'Bob Smith');


CREATE USER [VM-NAME] FROM EXTERNAL PROVIDER;
ALTER ROLE [db_datareader] ADD MEMBER [VM-NAME];