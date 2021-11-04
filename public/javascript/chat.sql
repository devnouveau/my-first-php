CREATE DATABASE chat;
USE chat;
CREATE TABLE chatlog (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  message TEXT,
  sent_by VARCHAR(50),
  date_created INT(11)
);

grant select, insert, update, delete
on chat.*
to chat_user@localhost identified by 'chat_password';