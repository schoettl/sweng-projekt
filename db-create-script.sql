SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

DROP SCHEMA IF EXISTS `sweng_projekt` ;
CREATE SCHEMA IF NOT EXISTS `sweng_projekt` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `sweng_projekt` ;

-- -----------------------------------------------------
-- Table `sweng_projekt`.`lock`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sweng_projekt`.`lock` ;

CREATE  TABLE IF NOT EXISTS `sweng_projekt`.`lock` (
  `LockId` INT NOT NULL AUTO_INCREMENT ,
  `Location` VARCHAR(45) NOT NULL ,
  `last_change` TIMESTAMP NOT NULL COMMENT 'Wann hat sich in der DB beim Schloss das letzte Mal was geändert?' ,
  `last_sync` TIMESTAMP NULL COMMENT 'Wann wurde für das Schloss der Stand der DB das letzte mal aufs Programmiergerät übertragen?' ,
  PRIMARY KEY (`LockId`) ,
  UNIQUE INDEX `Location_UNIQUE` (`Location` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sweng_projekt`.`key`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sweng_projekt`.`key` ;

CREATE  TABLE IF NOT EXISTS `sweng_projekt`.`key` (
  `KeyId` INT NOT NULL AUTO_INCREMENT ,
  `Aktiv` TINYINT(1) NOT NULL DEFAULT FALSE ,
  PRIMARY KEY (`KeyId`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sweng_projekt`.`blacklist`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sweng_projekt`.`blacklist` ;

CREATE  TABLE IF NOT EXISTS `sweng_projekt`.`blacklist` (
  `LockId` INT NOT NULL ,
  `KeyId` INT NOT NULL ,
  PRIMARY KEY (`LockId`, `KeyId`) ,
  INDEX `fk_blacklist_lock1_idx` (`LockId` ASC) ,
  INDEX `fk_blacklist_key1_idx` (`KeyId` ASC) ,
  CONSTRAINT `fk_blacklist_lock1`
    FOREIGN KEY (`LockId` )
    REFERENCES `sweng_projekt`.`lock` (`LockId` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_blacklist_key1`
    FOREIGN KEY (`KeyId` )
    REFERENCES `sweng_projekt`.`key` (`KeyId` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sweng_projekt`.`whitelist`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sweng_projekt`.`whitelist` ;

CREATE  TABLE IF NOT EXISTS `sweng_projekt`.`whitelist` (
  `LockId` INT NOT NULL ,
  `KeyId` INT NOT NULL ,
  PRIMARY KEY (`LockId`, `KeyId`) ,
  INDEX `fk_whitelist_lock1_idx` (`LockId` ASC) ,
  INDEX `fk_whitelist_key1_idx` (`KeyId` ASC) ,
  CONSTRAINT `fk_whitelist_lock1`
    FOREIGN KEY (`LockId` )
    REFERENCES `sweng_projekt`.`lock` (`LockId` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_whitelist_key1`
    FOREIGN KEY (`KeyId` )
    REFERENCES `sweng_projekt`.`key` (`KeyId` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sweng_projekt`.`access`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sweng_projekt`.`access` ;

CREATE  TABLE IF NOT EXISTS `sweng_projekt`.`access` (
  `AccessId` INT NOT NULL COMMENT 'AccessId aus AccessSystem (aka Buchungssystem)!' ,
  `LockId` INT NOT NULL ,
  `KeyId` INT NOT NULL COMMENT 'Wenn key Aktiv: Zugang wird auf key gespeichert!\\nWenn key nicht Aktiv: Zugang wird in lock gespeichert!' ,
  `Begin` DATE NULL ,
  `End` DATE NULL ,
  PRIMARY KEY (`AccessId`) ,
  INDEX `fk_access_lock2_idx` (`LockId` ASC) ,
  INDEX `fk_access_key1_idx` (`KeyId` ASC) ,
  CONSTRAINT `fk_access_lock2`
    FOREIGN KEY (`LockId` )
    REFERENCES `sweng_projekt`.`lock` (`LockId` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_access_key1`
    FOREIGN KEY (`KeyId` )
    REFERENCES `sweng_projekt`.`key` (`KeyId` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- procedure touch_lock
-- -----------------------------------------------------

USE `sweng_projekt`;
DROP procedure IF EXISTS `sweng_projekt`.`touch_lock`;

DELIMITER $$
USE `sweng_projekt`$$
CREATE PROCEDURE `sweng_projekt`.`touch_lock` (LockId INT)
BEGIN
  -- Wie touch bei Unix: Setzt last_change timestamp auf NOW() für LockId
  UPDATE `lock` SET last_change = NOW() WHERE `lock`.LockId = LockId;
END

$$

DELIMITER ;
USE `sweng_projekt`;

DELIMITER $$

USE `sweng_projekt`$$
DROP TRIGGER IF EXISTS `sweng_projekt`.`blacklist_del` $$
USE `sweng_projekt`$$


CREATE TRIGGER blacklist_del AFTER DELETE ON blacklist
FOR EACH ROW CALL touch_lock(OLD.LockId)

$$


USE `sweng_projekt`$$
DROP TRIGGER IF EXISTS `sweng_projekt`.`blacklist_ins` $$
USE `sweng_projekt`$$


CREATE TRIGGER blacklist_ins AFTER INSERT ON blacklist
FOR EACH ROW CALL touch_lock(NEW.LockId)

$$


USE `sweng_projekt`$$
DROP TRIGGER IF EXISTS `sweng_projekt`.`blacklist_upd` $$
USE `sweng_projekt`$$


CREATE TRIGGER blacklist_upd AFTER UPDATE ON blacklist
FOR EACH ROW BEGIN
  IF (OLD.LockId != NEW.LockId) THEN
    CALL touch_lock(OLD.LockId);
  END IF;
  CALL touch_lock(NEW.LockId);
END

$$


DELIMITER ;

DELIMITER $$

USE `sweng_projekt`$$
DROP TRIGGER IF EXISTS `sweng_projekt`.`whitelist_del` $$
USE `sweng_projekt`$$


CREATE TRIGGER whitelist_del AFTER DELETE ON whitelist
FOR EACH ROW CALL touch_lock(OLD.LockId)

$$


USE `sweng_projekt`$$
DROP TRIGGER IF EXISTS `sweng_projekt`.`whitelist_ins` $$
USE `sweng_projekt`$$


CREATE TRIGGER whitelist_ins AFTER INSERT ON whitelist
FOR EACH ROW CALL touch_lock(NEW.LockId)

$$


USE `sweng_projekt`$$
DROP TRIGGER IF EXISTS `sweng_projekt`.`whitelist_upd` $$
USE `sweng_projekt`$$


CREATE TRIGGER whitelist_upd AFTER UPDATE ON whitelist
FOR EACH ROW BEGIN
  IF (OLD.LockId != NEW.LockId) THEN
    CALL touch_lock(OLD.LockId);
  END IF;
  CALL touch_lock(NEW.LockId);
END

$$


DELIMITER ;

DELIMITER $$

USE `sweng_projekt`$$
DROP TRIGGER IF EXISTS `sweng_projekt`.`access_upd` $$
USE `sweng_projekt`$$


CREATE TRIGGER access_upd AFTER UPDATE ON access
FOR EACH ROW BEGIN
  -- Handelt es sich um Passiven Schlüssel?  
  SELECT Aktiv INTO @oaktiv FROM `key` WHERE KeyId = OLD.KeyId;
  SELECT Aktiv INTO @naktiv FROM `key` WHERE KeyId = NEW.KeyId;
  IF OLD.LockId != NEW.LockId AND NOT @oaktiv THEN
    CALL touch_lock(OLD.LockId);
  END IF;
  IF NOT @naktiv THEN
    CALL touch_lock(NEW.LockId);
  END IF;
END

$$


USE `sweng_projekt`$$
DROP TRIGGER IF EXISTS `sweng_projekt`.`access_ins` $$
USE `sweng_projekt`$$


CREATE TRIGGER access_ins AFTER INSERT ON access
FOR EACH ROW BEGIN
  -- Handelt es sich um Passiven Schlüssel?
  SELECT Aktiv INTO @aktiv FROM `key` WHERE KeyId = NEW.KeyId;  
  IF NOT @aktiv THEN
    CALL touch_lock(NEW.LockId);
  END IF;
END

$$


USE `sweng_projekt`$$
DROP TRIGGER IF EXISTS `sweng_projekt`.`access_del` $$
USE `sweng_projekt`$$


CREATE TRIGGER access_del AFTER DELETE ON access
FOR EACH ROW BEGIN
  -- Handelt es sich um Passiven Schlüssel?
  SELECT Aktiv INTO @aktiv FROM `key` WHERE KeyId = OLD.KeyId;  
  IF NOT @aktiv THEN
    CALL touch_lock(OLD.LockId);
  END IF;
END

$$


DELIMITER ;

SET SQL_MODE = '';
GRANT USAGE ON *.* TO sweng_projekt;
 DROP USER sweng_projekt;
SET SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';
CREATE USER `sweng_projekt` IDENTIFIED BY 'sweng_projekt';

grant INSERT on TABLE `sweng_projekt`.`blacklist` to sweng_projekt;
grant SELECT on TABLE `sweng_projekt`.`blacklist` to sweng_projekt;
grant UPDATE on TABLE `sweng_projekt`.`blacklist` to sweng_projekt;
grant DELETE on TABLE `sweng_projekt`.`blacklist` to sweng_projekt;
grant INSERT on TABLE `sweng_projekt`.`access` to sweng_projekt;
grant SELECT on TABLE `sweng_projekt`.`access` to sweng_projekt;
grant UPDATE on TABLE `sweng_projekt`.`access` to sweng_projekt;
grant DELETE on TABLE `sweng_projekt`.`access` to sweng_projekt;
grant INSERT on TABLE `sweng_projekt`.`key` to sweng_projekt;
grant SELECT on TABLE `sweng_projekt`.`key` to sweng_projekt;
grant UPDATE on TABLE `sweng_projekt`.`key` to sweng_projekt;
grant DELETE on TABLE `sweng_projekt`.`key` to sweng_projekt;
grant INSERT on TABLE `sweng_projekt`.`lock` to sweng_projekt;
grant SELECT on TABLE `sweng_projekt`.`lock` to sweng_projekt;
grant UPDATE on TABLE `sweng_projekt`.`lock` to sweng_projekt;
grant DELETE on TABLE `sweng_projekt`.`lock` to sweng_projekt;
grant INSERT on TABLE `sweng_projekt`.`whitelist` to sweng_projekt;
grant SELECT on TABLE `sweng_projekt`.`whitelist` to sweng_projekt;
grant UPDATE on TABLE `sweng_projekt`.`whitelist` to sweng_projekt;
grant DELETE on TABLE `sweng_projekt`.`whitelist` to sweng_projekt;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
