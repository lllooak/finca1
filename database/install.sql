-- =====================================================================
-- xbt.co.il - Database Schema (install.sql)
-- MySQL 8.0+ / utf8mb4
-- Built for 100,000+ dynamic pages and 1M+ monthly visits.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET time_zone = '+00:00';

CREATE DATABASE IF NOT EXISTS `xbt_finance`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `xbt_finance`;

-- ---------------------------------------------------------------------
-- Reference tables (countries, exchanges, sectors, industries, themes)
-- ---------------------------------------------------------------------

CREATE TABLE `countries` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(120) NOT NULL,
  `name_he`       VARCHAR(120) DEFAULT NULL,
  `slug`          VARCHAR(140) NOT NULL,
  `iso2`          CHAR(2) DEFAULT NULL,
  `iso3`          CHAR(3) DEFAULT NULL,
  `region`        VARCHAR(80) DEFAULT NULL,
  `currency_code` CHAR(3) DEFAULT NULL,
  `flag_emoji`    VARCHAR(16) DEFAULT NULL,
  `description`   MEDIUMTEXT,
  `meta_title`    VARCHAR(255) DEFAULT NULL,
  `meta_desc`     VARCHAR(320) DEFAULT NULL,
  `status`        TINYINT NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_country_slug` (`slug`),
  KEY `idx_country_region` (`region`),
  KEY `idx_country_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `exchanges` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(160) NOT NULL,
  `name_he`       VARCHAR(160) DEFAULT NULL,
  `slug`          VARCHAR(180) NOT NULL,
  `code`          VARCHAR(20) DEFAULT NULL,
  `mic`           VARCHAR(10) DEFAULT NULL,
  `country_id`    INT UNSIGNED DEFAULT NULL,
  `timezone`      VARCHAR(64) DEFAULT NULL,
  `currency_code` CHAR(3) DEFAULT NULL,
  `description`   MEDIUMTEXT,
  `meta_title`    VARCHAR(255) DEFAULT NULL,
  `meta_desc`     VARCHAR(320) DEFAULT NULL,
  `status`        TINYINT NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_exchange_slug` (`slug`),
  KEY `idx_exchange_code` (`code`),
  KEY `idx_exchange_country` (`country_id`),
  CONSTRAINT `fk_exchange_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sectors` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(120) NOT NULL,
  `name_he`     VARCHAR(120) DEFAULT NULL,
  `slug`        VARCHAR(140) NOT NULL,
  `description` MEDIUMTEXT,
  `overview`    MEDIUMTEXT,
  `icon`        VARCHAR(60) DEFAULT NULL,
  `meta_title`  VARCHAR(255) DEFAULT NULL,
  `meta_desc`   VARCHAR(320) DEFAULT NULL,
  `status`      TINYINT NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_sector_slug` (`slug`),
  KEY `idx_sector_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `industries` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sector_id`   INT UNSIGNED DEFAULT NULL,
  `name`        VARCHAR(160) NOT NULL,
  `name_he`     VARCHAR(160) DEFAULT NULL,
  `slug`        VARCHAR(180) NOT NULL,
  `description` MEDIUMTEXT,
  `overview`    MEDIUMTEXT,
  `meta_title`  VARCHAR(255) DEFAULT NULL,
  `meta_desc`   VARCHAR(320) DEFAULT NULL,
  `status`      TINYINT NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_industry_slug` (`slug`),
  KEY `idx_industry_sector` (`sector_id`),
  CONSTRAINT `fk_industry_sector` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `themes` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(160) NOT NULL,
  `name_he`     VARCHAR(160) DEFAULT NULL,
  `slug`        VARCHAR(180) NOT NULL,
  `category`    VARCHAR(80) DEFAULT NULL,
  `description` MEDIUMTEXT,
  `overview`    MEDIUMTEXT,
  `icon`        VARCHAR(60) DEFAULT NULL,
  `is_featured` TINYINT NOT NULL DEFAULT 0,
  `meta_title`  VARCHAR(255) DEFAULT NULL,
  `meta_desc`   VARCHAR(320) DEFAULT NULL,
  `status`      TINYINT NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_theme_slug` (`slug`),
  KEY `idx_theme_featured` (`is_featured`),
  KEY `idx_theme_status` (`status`),
  FULLTEXT KEY `ft_theme` (`name`, `name_he`, `description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Stocks
-- ---------------------------------------------------------------------

CREATE TABLE `stocks` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticker`          VARCHAR(20) NOT NULL,
  `company_name`    VARCHAR(255) NOT NULL,
  `company_name_he` VARCHAR(255) DEFAULT NULL,
  `slug`            VARCHAR(255) NOT NULL,
  `exchange_id`     INT UNSIGNED DEFAULT NULL,
  `country_id`      INT UNSIGNED DEFAULT NULL,
  `sector_id`       INT UNSIGNED DEFAULT NULL,
  `industry_id`     INT UNSIGNED DEFAULT NULL,
  `currency_code`   CHAR(3) NOT NULL DEFAULT 'USD',
  `logo_url`        VARCHAR(255) DEFAULT NULL,
  `website`         VARCHAR(255) DEFAULT NULL,
  `ceo`             VARCHAR(160) DEFAULT NULL,
  `founded_year`    SMALLINT DEFAULT NULL,
  `headquarters`    VARCHAR(200) DEFAULT NULL,
  `employees`       INT DEFAULT NULL,

  `price`           DECIMAL(20,4) DEFAULT NULL,
  `prev_close`      DECIMAL(20,4) DEFAULT NULL,
  `day_change`      DECIMAL(20,4) DEFAULT NULL,
  `day_change_pct`  DECIMAL(10,4) DEFAULT NULL,
  `day_high`        DECIMAL(20,4) DEFAULT NULL,
  `day_low`         DECIMAL(20,4) DEFAULT NULL,
  `volume`          BIGINT DEFAULT NULL,
  `avg_volume`      BIGINT DEFAULT NULL,
  `market_cap`      BIGINT DEFAULT NULL,
  `enterprise_value` BIGINT DEFAULT NULL,
  `shares_outstanding` BIGINT DEFAULT NULL,

  `pe_ratio`        DECIMAL(12,2) DEFAULT NULL,
  `forward_pe`      DECIMAL(12,2) DEFAULT NULL,
  `peg_ratio`       DECIMAL(12,2) DEFAULT NULL,
  `ps_ratio`        DECIMAL(12,2) DEFAULT NULL,
  `pb_ratio`        DECIMAL(12,2) DEFAULT NULL,
  `eps`             DECIMAL(12,4) DEFAULT NULL,
  `eps_forward`     DECIMAL(12,4) DEFAULT NULL,
  `revenue`         BIGINT DEFAULT NULL,
  `revenue_growth`  DECIMAL(10,4) DEFAULT NULL,
  `net_income`      BIGINT DEFAULT NULL,
  `gross_margin`    DECIMAL(10,4) DEFAULT NULL,
  `operating_margin` DECIMAL(10,4) DEFAULT NULL,
  `profit_margin`   DECIMAL(10,4) DEFAULT NULL,
  `roe`             DECIMAL(10,4) DEFAULT NULL,
  `roa`             DECIMAL(10,4) DEFAULT NULL,
  `roic`            DECIMAL(10,4) DEFAULT NULL,
  `free_cash_flow`  BIGINT DEFAULT NULL,
  `total_debt`      BIGINT DEFAULT NULL,
  `total_cash`      BIGINT DEFAULT NULL,
  `debt_to_equity`  DECIMAL(12,4) DEFAULT NULL,

  `dividend`        DECIMAL(12,4) DEFAULT NULL,
  `dividend_yield`  DECIMAL(10,4) DEFAULT NULL,
  `payout_ratio`    DECIMAL(10,4) DEFAULT NULL,
  `ex_dividend_date` DATE DEFAULT NULL,

  `beta`            DECIMAL(10,4) DEFAULT NULL,
  `week52_high`     DECIMAL(20,4) DEFAULT NULL,
  `week52_low`      DECIMAL(20,4) DEFAULT NULL,
  `ytd_return`      DECIMAL(10,4) DEFAULT NULL,
  `return_1y`       DECIMAL(10,4) DEFAULT NULL,
  `return_3y`       DECIMAL(10,4) DEFAULT NULL,
  `return_5y`       DECIMAL(10,4) DEFAULT NULL,

  `description`     MEDIUMTEXT,
  `description_he`  MEDIUMTEXT,
  `business_model`  MEDIUMTEXT,
  `products_services` MEDIUMTEXT,
  `revenue_sources` MEDIUMTEXT,
  `competitive_advantages` MEDIUMTEXT,
  `growth_drivers`  MEDIUMTEXT,
  `risks`           MEDIUMTEXT,

  `is_active`       TINYINT NOT NULL DEFAULT 1,
  `is_featured`     TINYINT NOT NULL DEFAULT 0,
  `meta_title`      VARCHAR(255) DEFAULT NULL,
  `meta_desc`       VARCHAR(320) DEFAULT NULL,
  `status`          TINYINT NOT NULL DEFAULT 1,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_stock_slug` (`slug`),
  KEY `idx_stock_ticker` (`ticker`),
  KEY `idx_stock_exchange` (`exchange_id`),
  KEY `idx_stock_country` (`country_id`),
  KEY `idx_stock_sector` (`sector_id`),
  KEY `idx_stock_industry` (`industry_id`),
  KEY `idx_stock_marketcap` (`market_cap`),
  KEY `idx_stock_change` (`day_change_pct`),
  KEY `idx_stock_volume` (`volume`),
  KEY `idx_stock_divyield` (`dividend_yield`),
  KEY `idx_stock_pe` (`pe_ratio`),
  KEY `idx_stock_featured` (`is_featured`),
  KEY `idx_stock_status_cap` (`status`, `market_cap`),
  FULLTEXT KEY `ft_stock` (`ticker`, `company_name`, `company_name_he`, `description_he`),
  CONSTRAINT `fk_stock_exchange` FOREIGN KEY (`exchange_id`) REFERENCES `exchanges` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_stock_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_stock_sector` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_stock_industry` FOREIGN KEY (`industry_id`) REFERENCES `industries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stock_prices` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `stock_id`   INT UNSIGNED NOT NULL,
  `price_date` DATE NOT NULL,
  `open`       DECIMAL(20,4) DEFAULT NULL,
  `high`       DECIMAL(20,4) DEFAULT NULL,
  `low`        DECIMAL(20,4) DEFAULT NULL,
  `close`      DECIMAL(20,4) NOT NULL,
  `adj_close`  DECIMAL(20,4) DEFAULT NULL,
  `volume`     BIGINT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_stock_date` (`stock_id`, `price_date`),
  KEY `idx_price_date` (`price_date`),
  CONSTRAINT `fk_price_stock` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stock_financials` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `stock_id`      INT UNSIGNED NOT NULL,
  `fiscal_year`   SMALLINT NOT NULL,
  `fiscal_period` VARCHAR(10) NOT NULL DEFAULT 'FY',
  `revenue`       BIGINT DEFAULT NULL,
  `gross_profit`  BIGINT DEFAULT NULL,
  `operating_income` BIGINT DEFAULT NULL,
  `net_income`    BIGINT DEFAULT NULL,
  `eps`           DECIMAL(12,4) DEFAULT NULL,
  `ebitda`        BIGINT DEFAULT NULL,
  `total_assets`  BIGINT DEFAULT NULL,
  `total_liabilities` BIGINT DEFAULT NULL,
  `total_equity`  BIGINT DEFAULT NULL,
  `free_cash_flow` BIGINT DEFAULT NULL,
  `operating_cash_flow` BIGINT DEFAULT NULL,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_fin` (`stock_id`, `fiscal_year`, `fiscal_period`),
  CONSTRAINT `fk_fin_stock` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stock_dividends` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `stock_id`   INT UNSIGNED NOT NULL,
  `ex_date`    DATE NOT NULL,
  `pay_date`   DATE DEFAULT NULL,
  `amount`     DECIMAL(12,4) NOT NULL,
  `frequency`  VARCHAR(20) DEFAULT 'quarterly',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_div_stock` (`stock_id`),
  KEY `idx_div_exdate` (`ex_date`),
  CONSTRAINT `fk_div_stock` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stock_splits` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `stock_id`     INT UNSIGNED NOT NULL,
  `split_date`   DATE NOT NULL,
  `split_factor` DECIMAL(16,6) NOT NULL,
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_split` (`stock_id`, `split_date`),
  KEY `idx_split_stock` (`stock_id`),
  CONSTRAINT `fk_split_stock` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- ETFs
-- ---------------------------------------------------------------------

CREATE TABLE `etfs` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticker`         VARCHAR(20) NOT NULL,
  `name`           VARCHAR(255) NOT NULL,
  `name_he`        VARCHAR(255) DEFAULT NULL,
  `slug`           VARCHAR(255) NOT NULL,
  `issuer`         VARCHAR(160) DEFAULT NULL,
  `exchange_id`    INT UNSIGNED DEFAULT NULL,
  `country_id`     INT UNSIGNED DEFAULT NULL,
  `asset_class`    VARCHAR(60) DEFAULT NULL,
  `category`       VARCHAR(120) DEFAULT NULL,
  `benchmark`      VARCHAR(200) DEFAULT NULL,
  `currency_code`  CHAR(3) NOT NULL DEFAULT 'USD',
  `aum`            BIGINT DEFAULT NULL,
  `expense_ratio`  DECIMAL(8,4) DEFAULT NULL,
  `price`          DECIMAL(20,4) DEFAULT NULL,
  `day_change_pct` DECIMAL(10,4) DEFAULT NULL,
  `nav`            DECIMAL(20,4) DEFAULT NULL,
  `dividend_yield` DECIMAL(10,4) DEFAULT NULL,
  `inception_date` DATE DEFAULT NULL,
  `holdings_count` INT DEFAULT NULL,
  `ytd_return`     DECIMAL(10,4) DEFAULT NULL,
  `return_1y`      DECIMAL(10,4) DEFAULT NULL,
  `return_3y`      DECIMAL(10,4) DEFAULT NULL,
  `return_5y`      DECIMAL(10,4) DEFAULT NULL,
  `beta`           DECIMAL(10,4) DEFAULT NULL,
  `description`    MEDIUMTEXT,
  `description_he` MEDIUMTEXT,
  `strategy`       MEDIUMTEXT,
  `is_featured`    TINYINT NOT NULL DEFAULT 0,
  `meta_title`     VARCHAR(255) DEFAULT NULL,
  `meta_desc`      VARCHAR(320) DEFAULT NULL,
  `status`         TINYINT NOT NULL DEFAULT 1,
  `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_etf_slug` (`slug`),
  KEY `idx_etf_ticker` (`ticker`),
  KEY `idx_etf_aum` (`aum`),
  KEY `idx_etf_expense` (`expense_ratio`),
  KEY `idx_etf_class` (`asset_class`),
  KEY `idx_etf_featured` (`is_featured`),
  FULLTEXT KEY `ft_etf` (`ticker`, `name`, `name_he`, `description_he`),
  CONSTRAINT `fk_etf_exchange` FOREIGN KEY (`exchange_id`) REFERENCES `exchanges` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_etf_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `etf_holdings` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `etf_id`      INT UNSIGNED NOT NULL,
  `stock_id`    INT UNSIGNED DEFAULT NULL,
  `holding_name` VARCHAR(255) NOT NULL,
  `holding_ticker` VARCHAR(20) DEFAULT NULL,
  `weight`      DECIMAL(10,4) DEFAULT NULL,
  `shares`      BIGINT DEFAULT NULL,
  `sector`      VARCHAR(120) DEFAULT NULL,
  `country`     VARCHAR(120) DEFAULT NULL,
  `position`    INT DEFAULT NULL,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_holding_etf` (`etf_id`),
  KEY `idx_holding_stock` (`stock_id`),
  KEY `idx_holding_weight` (`weight`),
  CONSTRAINT `fk_holding_etf` FOREIGN KEY (`etf_id`) REFERENCES `etfs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_holding_stock` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Bonds
-- ---------------------------------------------------------------------

CREATE TABLE `bonds` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(255) NOT NULL,
  `name_he`       VARCHAR(255) DEFAULT NULL,
  `slug`          VARCHAR(255) NOT NULL,
  `issuer`        VARCHAR(200) DEFAULT NULL,
  `bond_type`     VARCHAR(80) DEFAULT NULL,
  `country_id`    INT UNSIGNED DEFAULT NULL,
  `currency_code` CHAR(3) NOT NULL DEFAULT 'USD',
  `coupon`        DECIMAL(10,4) DEFAULT NULL,
  `yield`         DECIMAL(10,4) DEFAULT NULL,
  `ytm`           DECIMAL(10,4) DEFAULT NULL,
  `duration`      DECIMAL(10,4) DEFAULT NULL,
  `maturity_date` DATE DEFAULT NULL,
  `maturity_years` DECIMAL(8,2) DEFAULT NULL,
  `credit_rating` VARCHAR(10) DEFAULT NULL,
  `risk_level`    VARCHAR(40) DEFAULT NULL,
  `face_value`    DECIMAL(20,4) DEFAULT NULL,
  `price`         DECIMAL(20,4) DEFAULT NULL,
  `description`   MEDIUMTEXT,
  `description_he` MEDIUMTEXT,
  `meta_title`    VARCHAR(255) DEFAULT NULL,
  `meta_desc`     VARCHAR(320) DEFAULT NULL,
  `status`        TINYINT NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_bond_slug` (`slug`),
  KEY `idx_bond_type` (`bond_type`),
  KEY `idx_bond_yield` (`yield`),
  KEY `idx_bond_rating` (`credit_rating`),
  KEY `idx_bond_country` (`country_id`),
  FULLTEXT KEY `ft_bond` (`name`, `name_he`, `issuer`),
  CONSTRAINT `fk_bond_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Indices
-- ---------------------------------------------------------------------

CREATE TABLE `indices` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(255) NOT NULL,
  `name_he`         VARCHAR(255) DEFAULT NULL,
  `slug`            VARCHAR(255) NOT NULL,
  `symbol`          VARCHAR(30) DEFAULT NULL,
  `country_id`      INT UNSIGNED DEFAULT NULL,
  `exchange_id`     INT UNSIGNED DEFAULT NULL,
  `weighting_method` VARCHAR(80) DEFAULT NULL,
  `constituents_count` INT DEFAULT NULL,
  `value`           DECIMAL(20,4) DEFAULT NULL,
  `day_change_pct`  DECIMAL(10,4) DEFAULT NULL,
  `ytd_return`      DECIMAL(10,4) DEFAULT NULL,
  `return_1y`       DECIMAL(10,4) DEFAULT NULL,
  `return_5y`       DECIMAL(10,4) DEFAULT NULL,
  `description`     MEDIUMTEXT,
  `description_he`  MEDIUMTEXT,
  `methodology`     MEDIUMTEXT,
  `is_featured`     TINYINT NOT NULL DEFAULT 0,
  `meta_title`      VARCHAR(255) DEFAULT NULL,
  `meta_desc`       VARCHAR(320) DEFAULT NULL,
  `status`          TINYINT NOT NULL DEFAULT 1,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_index_slug` (`slug`),
  KEY `idx_index_symbol` (`symbol`),
  KEY `idx_index_country` (`country_id`),
  KEY `idx_index_featured` (`is_featured`),
  FULLTEXT KEY `ft_index` (`name`, `name_he`, `symbol`),
  CONSTRAINT `fk_index_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_index_exchange` FOREIGN KEY (`exchange_id`) REFERENCES `exchanges` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `index_constituents` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `index_id`   INT UNSIGNED NOT NULL,
  `stock_id`   INT UNSIGNED DEFAULT NULL,
  `name`       VARCHAR(255) NOT NULL,
  `ticker`     VARCHAR(20) DEFAULT NULL,
  `weight`     DECIMAL(10,4) DEFAULT NULL,
  `position`   INT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_const_index` (`index_id`),
  KEY `idx_const_stock` (`stock_id`),
  CONSTRAINT `fk_const_index` FOREIGN KEY (`index_id`) REFERENCES `indices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_const_stock` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Commodities / Currencies / Cryptos / REITs
-- ---------------------------------------------------------------------

CREATE TABLE `commodities` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`           VARCHAR(160) NOT NULL,
  `name_he`        VARCHAR(160) DEFAULT NULL,
  `slug`           VARCHAR(180) NOT NULL,
  `symbol`         VARCHAR(30) DEFAULT NULL,
  `category`       VARCHAR(80) DEFAULT NULL,
  `unit`           VARCHAR(40) DEFAULT NULL,
  `currency_code`  CHAR(3) NOT NULL DEFAULT 'USD',
  `price`          DECIMAL(20,4) DEFAULT NULL,
  `day_change_pct` DECIMAL(10,4) DEFAULT NULL,
  `ytd_return`     DECIMAL(10,4) DEFAULT NULL,
  `description`    MEDIUMTEXT,
  `description_he` MEDIUMTEXT,
  `meta_title`     VARCHAR(255) DEFAULT NULL,
  `meta_desc`      VARCHAR(320) DEFAULT NULL,
  `status`         TINYINT NOT NULL DEFAULT 1,
  `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_commodity_slug` (`slug`),
  KEY `idx_commodity_category` (`category`),
  FULLTEXT KEY `ft_commodity` (`name`, `name_he`, `symbol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `currencies` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`           VARCHAR(160) NOT NULL,
  `name_he`        VARCHAR(160) DEFAULT NULL,
  `slug`           VARCHAR(180) NOT NULL,
  `code`           VARCHAR(12) DEFAULT NULL,
  `pair`           VARCHAR(20) DEFAULT NULL,
  `symbol`         VARCHAR(12) DEFAULT NULL,
  `rate`           DECIMAL(20,6) DEFAULT NULL,
  `day_change_pct` DECIMAL(10,4) DEFAULT NULL,
  `description`    MEDIUMTEXT,
  `description_he` MEDIUMTEXT,
  `meta_title`     VARCHAR(255) DEFAULT NULL,
  `meta_desc`      VARCHAR(320) DEFAULT NULL,
  `status`         TINYINT NOT NULL DEFAULT 1,
  `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_currency_slug` (`slug`),
  KEY `idx_currency_code` (`code`),
  FULLTEXT KEY `ft_currency` (`name`, `name_he`, `code`, `pair`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cryptos` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`           VARCHAR(160) NOT NULL,
  `name_he`        VARCHAR(160) DEFAULT NULL,
  `slug`           VARCHAR(180) NOT NULL,
  `symbol`         VARCHAR(30) NOT NULL,
  `category`       VARCHAR(80) DEFAULT NULL,
  `price`          DECIMAL(28,8) DEFAULT NULL,
  `day_change_pct` DECIMAL(10,4) DEFAULT NULL,
  `market_cap`     BIGINT DEFAULT NULL,
  `volume_24h`     BIGINT DEFAULT NULL,
  `circulating_supply` DECIMAL(30,4) DEFAULT NULL,
  `max_supply`     DECIMAL(30,4) DEFAULT NULL,
  `ath`            DECIMAL(28,8) DEFAULT NULL,
  `rank`           INT DEFAULT NULL,
  `description`    MEDIUMTEXT,
  `description_he` MEDIUMTEXT,
  `meta_title`     VARCHAR(255) DEFAULT NULL,
  `meta_desc`      VARCHAR(320) DEFAULT NULL,
  `status`         TINYINT NOT NULL DEFAULT 1,
  `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_crypto_slug` (`slug`),
  KEY `idx_crypto_symbol` (`symbol`),
  KEY `idx_crypto_rank` (`rank`),
  KEY `idx_crypto_mcap` (`market_cap`),
  FULLTEXT KEY `ft_crypto` (`name`, `name_he`, `symbol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reits` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticker`         VARCHAR(20) NOT NULL,
  `name`           VARCHAR(255) NOT NULL,
  `name_he`        VARCHAR(255) DEFAULT NULL,
  `slug`           VARCHAR(255) NOT NULL,
  `property_type`  VARCHAR(120) DEFAULT NULL,
  `exchange_id`    INT UNSIGNED DEFAULT NULL,
  `country_id`     INT UNSIGNED DEFAULT NULL,
  `currency_code`  CHAR(3) NOT NULL DEFAULT 'USD',
  `price`          DECIMAL(20,4) DEFAULT NULL,
  `day_change_pct` DECIMAL(10,4) DEFAULT NULL,
  `market_cap`     BIGINT DEFAULT NULL,
  `dividend_yield` DECIMAL(10,4) DEFAULT NULL,
  `ffo`            BIGINT DEFAULT NULL,
  `affo`           BIGINT DEFAULT NULL,
  `occupancy_rate` DECIMAL(8,4) DEFAULT NULL,
  `properties_count` INT DEFAULT NULL,
  `description`    MEDIUMTEXT,
  `description_he` MEDIUMTEXT,
  `is_featured`    TINYINT NOT NULL DEFAULT 0,
  `meta_title`     VARCHAR(255) DEFAULT NULL,
  `meta_desc`      VARCHAR(320) DEFAULT NULL,
  `status`         TINYINT NOT NULL DEFAULT 1,
  `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_reit_slug` (`slug`),
  KEY `idx_reit_ticker` (`ticker`),
  KEY `idx_reit_type` (`property_type`),
  KEY `idx_reit_yield` (`dividend_yield`),
  KEY `idx_reit_featured` (`is_featured`),
  FULLTEXT KEY `ft_reit` (`ticker`, `name`, `name_he`),
  CONSTRAINT `fk_reit_exchange` FOREIGN KEY (`exchange_id`) REFERENCES `exchanges` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reit_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Derivatives: options & futures
-- ---------------------------------------------------------------------

CREATE TABLE `options_contracts` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `underlying_ticker` VARCHAR(20) NOT NULL,
  `stock_id`      INT UNSIGNED DEFAULT NULL,
  `slug`          VARCHAR(255) NOT NULL,
  `option_type`   ENUM('call','put') NOT NULL,
  `strike`        DECIMAL(20,4) NOT NULL,
  `expiration`    DATE NOT NULL,
  `last_price`    DECIMAL(20,4) DEFAULT NULL,
  `bid`           DECIMAL(20,4) DEFAULT NULL,
  `ask`           DECIMAL(20,4) DEFAULT NULL,
  `volume`        BIGINT DEFAULT NULL,
  `open_interest` BIGINT DEFAULT NULL,
  `implied_volatility` DECIMAL(10,4) DEFAULT NULL,
  `delta`         DECIMAL(10,4) DEFAULT NULL,
  `gamma`         DECIMAL(10,4) DEFAULT NULL,
  `theta`         DECIMAL(10,4) DEFAULT NULL,
  `vega`          DECIMAL(10,4) DEFAULT NULL,
  `status`        TINYINT NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_option_slug` (`slug`),
  KEY `idx_option_underlying` (`underlying_ticker`),
  KEY `idx_option_exp` (`expiration`),
  KEY `idx_option_stock` (`stock_id`),
  CONSTRAINT `fk_option_stock` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `futures_contracts` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(200) NOT NULL,
  `name_he`       VARCHAR(200) DEFAULT NULL,
  `slug`          VARCHAR(255) NOT NULL,
  `symbol`        VARCHAR(30) NOT NULL,
  `category`      VARCHAR(80) DEFAULT NULL,
  `underlying`    VARCHAR(160) DEFAULT NULL,
  `exchange_id`   INT UNSIGNED DEFAULT NULL,
  `currency_code` CHAR(3) NOT NULL DEFAULT 'USD',
  `contract_month` VARCHAR(20) DEFAULT NULL,
  `expiration`    DATE DEFAULT NULL,
  `price`         DECIMAL(20,4) DEFAULT NULL,
  `day_change_pct` DECIMAL(10,4) DEFAULT NULL,
  `contract_size` VARCHAR(80) DEFAULT NULL,
  `open_interest` BIGINT DEFAULT NULL,
  `volume`        BIGINT DEFAULT NULL,
  `description`   MEDIUMTEXT,
  `description_he` MEDIUMTEXT,
  `status`        TINYINT NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_future_slug` (`slug`),
  KEY `idx_future_symbol` (`symbol`),
  KEY `idx_future_category` (`category`),
  CONSTRAINT `fk_future_exchange` FOREIGN KEY (`exchange_id`) REFERENCES `exchanges` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Content: guides, glossary, comparisons, news, faqs
-- ---------------------------------------------------------------------

CREATE TABLE `guide_categories` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(160) NOT NULL,
  `name_he`    VARCHAR(160) DEFAULT NULL,
  `slug`       VARCHAR(180) NOT NULL,
  `description` TEXT,
  `status`     TINYINT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_gcat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `guides` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id`  INT UNSIGNED DEFAULT NULL,
  `title`        VARCHAR(255) NOT NULL,
  `slug`         VARCHAR(255) NOT NULL,
  `summary`      TEXT,
  `content`      LONGTEXT,
  `reading_time` INT DEFAULT NULL,
  `level`        VARCHAR(40) DEFAULT NULL,
  `is_featured`  TINYINT NOT NULL DEFAULT 0,
  `views`        INT UNSIGNED NOT NULL DEFAULT 0,
  `meta_title`   VARCHAR(255) DEFAULT NULL,
  `meta_desc`    VARCHAR(320) DEFAULT NULL,
  `status`       TINYINT NOT NULL DEFAULT 1,
  `published_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_guide_slug` (`slug`),
  KEY `idx_guide_cat` (`category_id`),
  KEY `idx_guide_featured` (`is_featured`),
  KEY `idx_guide_status` (`status`),
  FULLTEXT KEY `ft_guide` (`title`, `summary`, `content`),
  CONSTRAINT `fk_guide_cat` FOREIGN KEY (`category_id`) REFERENCES `guide_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `glossary_terms` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `term`            VARCHAR(200) NOT NULL,
  `term_he`         VARCHAR(200) DEFAULT NULL,
  `slug`            VARCHAR(220) NOT NULL,
  `letter`          CHAR(1) DEFAULT NULL,
  `category`        VARCHAR(80) DEFAULT NULL,
  `definition`      TEXT,
  `explanation`     LONGTEXT,
  `example`         TEXT,
  `formula`         VARCHAR(500) DEFAULT NULL,
  `meta_title`      VARCHAR(255) DEFAULT NULL,
  `meta_desc`       VARCHAR(320) DEFAULT NULL,
  `views`           INT UNSIGNED NOT NULL DEFAULT 0,
  `status`          TINYINT NOT NULL DEFAULT 1,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_term_slug` (`slug`),
  KEY `idx_term_letter` (`letter`),
  KEY `idx_term_category` (`category`),
  FULLTEXT KEY `ft_term` (`term`, `term_he`, `definition`, `explanation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comparisons` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`        VARCHAR(255) NOT NULL,
  `slug`         VARCHAR(255) NOT NULL,
  `asset_type`   VARCHAR(40) NOT NULL DEFAULT 'stock',
  `entity_a_type` VARCHAR(40) DEFAULT 'stock',
  `entity_a_id`  INT UNSIGNED DEFAULT NULL,
  `entity_a_label` VARCHAR(120) DEFAULT NULL,
  `entity_b_type` VARCHAR(40) DEFAULT 'stock',
  `entity_b_id`  INT UNSIGNED DEFAULT NULL,
  `entity_b_label` VARCHAR(120) DEFAULT NULL,
  `summary`      TEXT,
  `content`      LONGTEXT,
  `pros_a`       TEXT,
  `cons_a`       TEXT,
  `pros_b`       TEXT,
  `cons_b`       TEXT,
  `verdict`      TEXT,
  `is_featured`  TINYINT NOT NULL DEFAULT 0,
  `views`        INT UNSIGNED NOT NULL DEFAULT 0,
  `meta_title`   VARCHAR(255) DEFAULT NULL,
  `meta_desc`    VARCHAR(320) DEFAULT NULL,
  `status`       TINYINT NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_comparison_slug` (`slug`),
  KEY `idx_comparison_type` (`asset_type`),
  KEY `idx_comparison_featured` (`is_featured`),
  FULLTEXT KEY `ft_comparison` (`title`, `summary`, `content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `news_categories` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(120) NOT NULL,
  `name_he`    VARCHAR(120) DEFAULT NULL,
  `slug`       VARCHAR(140) NOT NULL,
  `status`     TINYINT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_ncat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `news` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id`  INT UNSIGNED DEFAULT NULL,
  `title`        VARCHAR(300) NOT NULL,
  `slug`         VARCHAR(300) NOT NULL,
  `summary`      TEXT,
  `content`      LONGTEXT,
  `source`       VARCHAR(160) DEFAULT NULL,
  `author`       VARCHAR(160) DEFAULT NULL,
  `image_url`    VARCHAR(255) DEFAULT NULL,
  `tags`         VARCHAR(500) DEFAULT NULL,
  `related_assets` VARCHAR(500) DEFAULT NULL,
  `is_featured`  TINYINT NOT NULL DEFAULT 0,
  `views`        INT UNSIGNED NOT NULL DEFAULT 0,
  `meta_title`   VARCHAR(255) DEFAULT NULL,
  `meta_desc`    VARCHAR(320) DEFAULT NULL,
  `status`       TINYINT NOT NULL DEFAULT 1,
  `published_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_news_slug` (`slug`),
  KEY `idx_news_cat` (`category_id`),
  KEY `idx_news_published` (`published_at`),
  KEY `idx_news_featured` (`is_featured`),
  FULLTEXT KEY `ft_news` (`title`, `summary`, `content`, `tags`),
  CONSTRAINT `fk_news_cat` FOREIGN KEY (`category_id`) REFERENCES `news_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Polymorphic FAQ table attached to any entity
CREATE TABLE `faqs` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_type` VARCHAR(40) NOT NULL,
  `entity_id`   INT UNSIGNED DEFAULT NULL,
  `question`    VARCHAR(500) NOT NULL,
  `answer`      TEXT NOT NULL,
  `position`    INT NOT NULL DEFAULT 0,
  `status`      TINYINT NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_faq_entity` (`entity_type`, `entity_id`),
  FULLTEXT KEY `ft_faq` (`question`, `answer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Relationship tables (asset <-> theme/sector tagging)
-- ---------------------------------------------------------------------

CREATE TABLE `stock_themes` (
  `stock_id` INT UNSIGNED NOT NULL,
  `theme_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`stock_id`, `theme_id`),
  KEY `idx_st_theme` (`theme_id`),
  CONSTRAINT `fk_st_stock` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_st_theme` FOREIGN KEY (`theme_id`) REFERENCES `themes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `etf_themes` (
  `etf_id`   INT UNSIGNED NOT NULL,
  `theme_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`etf_id`, `theme_id`),
  KEY `idx_et_theme` (`theme_id`),
  CONSTRAINT `fk_et_etf` FOREIGN KEY (`etf_id`) REFERENCES `etfs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_et_theme` FOREIGN KEY (`theme_id`) REFERENCES `themes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- System: api sources & settings
-- ---------------------------------------------------------------------

CREATE TABLE `api_sources` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(120) NOT NULL,
  `slug`         VARCHAR(140) NOT NULL,
  `base_url`     VARCHAR(255) DEFAULT NULL,
  `api_key`      VARCHAR(255) DEFAULT NULL,
  `priority`     INT NOT NULL DEFAULT 100,
  `rate_limit`   INT DEFAULT NULL,
  `supports`     VARCHAR(255) DEFAULT NULL,
  `is_enabled`   TINYINT NOT NULL DEFAULT 0,
  `last_synced_at` TIMESTAMP NULL DEFAULT NULL,
  `status`       TINYINT NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_api_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `site_settings` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(120) NOT NULL,
  `setting_value` TEXT,
  `autoload`   TINYINT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- End of schema. Load seed_*.sql files next, or run database/seed.php
-- =====================================================================
