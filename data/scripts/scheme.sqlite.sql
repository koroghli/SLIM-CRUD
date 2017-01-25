/*////////////////////////////////creation de la base de donnee///////////////////////////////////*/
DROP TABLE IF EXISTS `wines`;

CREATE TABLE `wines` (
  `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `name` VARCHAR(45) DEFAULT NULL,
  `year` VARCHAR(45) DEFAULT NULL,
  `grapes` VARCHAR(45) DEFAULT NULL,
  `country` VARCHAR(45) DEFAULT NULL,
  `region` VARCHAR(45) DEFAULT NULL,
  `description` TEXT,
  `picture` VARCHAR(256) DEFAULT NULL
);

DROP INDEX IF EXISTS "pk_wines_id";
CREATE INDEX "pk_wine_id" ON "wines" ("id");
