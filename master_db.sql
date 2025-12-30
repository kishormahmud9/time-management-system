CREATE TABLE company (
    `id` int NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(55),
    `created_datetime` datetime DEFAULT NULL,
  	`modified_datetime` datetime DEFAULT NULL,
    `address` VARCHAR(225),
    `url_slug` VARCHAR(255),
    `timesheet_submit_email` VARCHAR(255) DEFAULT NULL,
    `db_name` VARCHAR(255),
    `image_path` VARCHAR(255) DEFAULT NULL,
    `file_folder` VARCHAR(255) DEFAULT NULL,
    `uuid` varchar(255) DEFAULT NULL,
    `details` VARCHAR(255),
    `active` bit(1) DEFAULT FALSE,
    `varify` bit(1) DEFAULT FALSE,
    PRIMARY KEY (`id`));
    
 CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `active` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `uuid` varchar(255) DEFAULT NULL,
  `work_email` varchar(255) NOT NULL,
  `private_sign` text DEFAULT NULL,
  `client_active` int(11) DEFAULT NULL,
  `file_folder` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY (`company_id`),
  FOREIGN KEY (`company_id`) REFERENCES `company` (`id`)
);

INSERT INTO `user`
(`user_id`, `active`, `email`, `company_id`,`first_name`,`last_name`,`password`,`phone`,`role`,`uuid`,
`work_email`,
`client_active`, `file_folder`)
VALUES
(0,
true,
"managetsadm@gmail.com",
null,
"Super",
"admin",
"$2a$10$PHAmRrabGhkqWWVeqOX./es0dcbY16hmKpLbfi8tfzeSsviXTUK7a",
"98654321",
"ROLE_SUPER_ADMIN",
"052a14c2-e5d6-43d5-a58d-62ff68085353",
"managetsadm@gmail.com",
null, null);
 
CREATE TABLE `user_company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `login_count` integer,
  PRIMARY KEY (`id`),
  KEY (`company_id`),
  KEY (`user_id`),
  FOREIGN KEY (`company_id`) REFERENCES `company` (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
);

CREATE TABLE `permission_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `user_can_login` bit(1) DEFAULT false,
  `commission` bit(1) DEFAULT false,
  `schedular_can_set` bit(1) DEFAULT false,
  `template_can_set` bit(1) DEFAULT false,
  `qb_integration` bit(1) DEFAULT false,
  `user_limit` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY (`company_id`),
  FOREIGN KEY (`company_id`) REFERENCES `company` (`id`)
);

CREATE TABLE `otp_token` (
     `id` bigint NOT NULL AUTO_INCREMENT,
     `createdAt` datetime(6) NOT NULL,
     `expiresAt` datetime(6) NOT NULL,
     `otp` varchar(6) NOT NULL,
     `resendAvailableAt` datetime(6) NOT NULL,
     `used` bit(1) NOT NULL,
     `user_id` int NOT NULL,
     PRIMARY KEY (`id`),
     KEY `fk_user_id` (`user_id`),
     CONSTRAINT `fk_constraint_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
);
