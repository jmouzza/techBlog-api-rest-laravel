CREATE DATABASE IF NOT EXISTS api_rest_laravel;
use api_rest_laravel;

CREATE TABLE IF NOT EXISTS users(
id              int(20) auto_increment not null,
name            varchar(200) not null,
surname         varchar(200) not null,
email           varchar(255) not null,
password        varchar(255) not null,
role            varchar(100),
description     text,
image           varchar(255),
created_at      datetime,
updated_at      datetime,
remember_token  varchar(255),
CONSTRAINT pk_users PRIMARY KEY(id),
CONSTRAINT uq_email UNIQUE(email)
)DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB; 

CREATE TABLE IF NOT EXISTS categories(
id              int(20) auto_increment not null,
name            varchar(200) not null,
created_at      datetime,
updated_at      datetime,
CONSTRAINT pk_categories PRIMARY KEY(id) 
)DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS posts(
id              int(255) auto_increment not null, 
user_id         int(20) not null,
category_id     int(20) not null,
title           varchar(255),
content         text,
image           varchar(255),
created_at      datetime,
updated_at      datetime,
CONSTRAINT pk_posts PRIMARY KEY(id),
CONSTRAINT fk_posts_users FOREIGN KEY(user_id) REFERENCES users(id),
CONSTRAINT fk_posts_categories FOREIGN KEY(category_id) REFERENCES categories(id)
)DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
