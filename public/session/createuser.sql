create database auth;

use auth;

create table authorized_users (
  id int not null primary key auto_increment, 
  name char(30),
  password char(10)
);

insert into authorized_users (name, password) values
  ('testuser', 'password');

grant all privileges
on auth.*
to webauth@localhost
identified by 'webauth';
