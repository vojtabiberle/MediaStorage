CREATE TABLE `media_storage`
(
    `id` varchar(23) PRIMARY KEY,
    `name` varchar(255),
    `full_path` text NOT NULL,
    `size` int,
    `content_type` varchar(100),
    `is_image` tinyint
);

CREATE TABLE `media_usage`
(
    `id` varchar(23) PRIMARY KEY,
    `media_id` varchar(23),
    `namespace` varchar(255),
    `primary` tinyint DEFAULT 0 NULL,
    FOREIGN KEY (`media_id`) REFERENCES `media_storage` (`id`)  ON DELETE CASCADE ON UPDATE RESTRICT
);