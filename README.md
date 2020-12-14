# PWPOP_AJG

Àlex Vicente - Jofre Figueras - Guillermo Serraclara

Passwords are encrypted with md5.

## SQL TABLES

### Table User

```
CREATE TABLE `user` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `is_active` int(3) DEFAULT 1,
 `name` varchar(100) NOT NULL,
 `username` varchar(100) NOT NULL,
 `email` varchar(100) NOT NULL,
 `birthdate` datetime DEFAULT NULL,
 `phone` varchar(10) DEFAULT NULL,
 `password` varchar(100) NOT NULL,
 `profile_image` varchar(255) DEFAULT NULL,
 `created_at` datetime DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

### Table Item

```
CREATE TABLE `item` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `id_user` int(10) unsigned NOT NULL,
 `is_active` int(3) DEFAULT 1,
 `title` varchar(100) NOT NULL,
 `description` varchar(255) NOT NULL,
 `price` varchar(255) NOT NULL,
 `product_image` varchar(255) DEFAULT NULL,
 `category` varchar(255) NOT NULL,
 `created_at` datetime DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `user_item` (`id_user`),
 CONSTRAINT `user_item` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```