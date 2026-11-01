CREATE TABLE `players` (
  `player_id` integer PRIMARY KEY AUTO_INCREMENT,
  `player_name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL
);

CREATE TABLE `game_saves` (
  `save_id` integer PRIMARY KEY AUTO_INCREMENT,
  `player_id` integer NOT NULL,
  `save_name` varchar(100) NOT NULL,
  `current_day` integer NOT NULL DEFAULT 1,
  `current_time` integer NOT NULL DEFAULT 360,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
);

CREATE TABLE `player_state` (
  `save_id` integer PRIMARY KEY,
  `position_x` real NOT NULL DEFAULT 0,
  `position_y` real NOT NULL DEFAULT 0,
  `money` integer NOT NULL DEFAULT 0,
  `selected_tool_item_id` integer
);

CREATE TABLE `items` (
  `item_id` integer PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `item_type` varchar(30) NOT NULL,
  `max_stack` integer NOT NULL DEFAULT 1,
  `sell_price` integer NOT NULL DEFAULT 0
);

CREATE TABLE `inventory_slots` (
  `save_id` integer NOT NULL,
  `slot_index` integer NOT NULL,
  `item_id` integer NOT NULL,
  `quantity` integer NOT NULL,
  PRIMARY KEY (`save_id`, `slot_index`)
);

CREATE TABLE `farm_tiles` (
  `tile_id` integer PRIMARY KEY AUTO_INCREMENT,
  `save_id` integer NOT NULL,
  `tile_x` integer NOT NULL,
  `tile_y` integer NOT NULL,
  `soil_state` varchar(30) NOT NULL,
  `watered` boolean NOT NULL DEFAULT false
);

CREATE TABLE `crop_types` (
  `crop_type_id` integer PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `seed_item_id` integer NOT NULL,
  `harvest_item_id` integer NOT NULL,
  `growth_days` integer NOT NULL
);

CREATE TABLE `planted_crops` (
  `crop_id` integer PRIMARY KEY AUTO_INCREMENT,
  `tile_id` integer UNIQUE NOT NULL,
  `crop_type_id` integer NOT NULL,
  `days_grown` integer NOT NULL DEFAULT 0,
  `growth_stage` integer NOT NULL DEFAULT 0
);

CREATE UNIQUE INDEX `farm_tiles_index_0` ON `farm_tiles` (`save_id`, `tile_x`, `tile_y`);

ALTER TABLE `game_saves` ADD FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`);

ALTER TABLE `player_state` ADD FOREIGN KEY (`save_id`) REFERENCES `game_saves` (`save_id`);

ALTER TABLE `player_state` ADD FOREIGN KEY (`selected_tool_item_id`) REFERENCES `items` (`item_id`);

ALTER TABLE `inventory_slots` ADD FOREIGN KEY (`save_id`) REFERENCES `game_saves` (`save_id`);

ALTER TABLE `inventory_slots` ADD FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

ALTER TABLE `farm_tiles` ADD FOREIGN KEY (`save_id`) REFERENCES `game_saves` (`save_id`);

ALTER TABLE `crop_types` ADD FOREIGN KEY (`seed_item_id`) REFERENCES `items` (`item_id`);

ALTER TABLE `crop_types` ADD FOREIGN KEY (`harvest_item_id`) REFERENCES `items` (`item_id`);

ALTER TABLE `planted_crops` ADD FOREIGN KEY (`tile_id`) REFERENCES `farm_tiles` (`tile_id`);

ALTER TABLE `planted_crops` ADD FOREIGN KEY (`crop_type_id`) REFERENCES `crop_types` (`crop_type_id`);
