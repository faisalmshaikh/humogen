## -- phpMyAdmin SQL Dump

-- version 5.2.3
-- [https://www.phpmyadmin.net/](https://www.phpmyadmin.net/)

-- Host: localhost:3306
-- Generation Time: Aug 20, 2026 at 09:35 PM
-- Server version: 10.11.18-MariaDB
-- PHP Version: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

## --

-- Database: `khandesh21at_humogen`

---



## --

-- Table structure for table `bachelors2`

CREATE TABLE `bachelors2` (
`RIN` varchar(20) DEFAULT NULL,
`NAME` varchar(50) DEFAULT NULL,
`SURNAME` varchar(50) DEFAULT NULL,
`FATHER` varchar(50) DEFAULT NULL,
`MOTHER` varchar(50) DEFAULT NULL,
`GENDER` varchar(1) DEFAULT NULL,
`AGE` double DEFAULT NULL,
`PLACE` varchar(75) DEFAULT NULL,
`PHONE` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `Education`

CREATE TABLE `Education` (
`event_tree_id` smallint(5) DEFAULT NULL,
`event_connect_id` varchar(20) DEFAULT NULL,
`education` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `fundtracker`

CREATE TABLE `fundtracker` (
`id` int(4) NOT NULL,
`description` varchar(50) DEFAULT NULL,
`raised_amt` float DEFAULT NULL,
`goal_amt` float DEFAULT NULL,
`sponsor_count` int(6) DEFAULT NULL,
`end_date` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `humo_addresses`

CREATE TABLE `humo_addresses` (
`address_id` int(10) UNSIGNED NOT NULL,
`address_tree_id` smallint(5) DEFAULT NULL,
`address_gedcomnr` varchar(25) DEFAULT NULL,
`address_shared` varchar(1) DEFAULT '',
`address_order` mediumint(6) DEFAULT NULL,
`address_connect_kind` varchar(25) DEFAULT NULL,
`address_connect_sub_kind` varchar(30) DEFAULT NULL,
`address_connect_id` varchar(25) DEFAULT NULL,
`address_address` text DEFAULT NULL,
`address_zip` varchar(20) DEFAULT NULL,
`address_place` varchar(120) DEFAULT NULL,
`address_phone` varchar(20) DEFAULT NULL,
`address_email` varchar(35) DEFAULT NULL,
`address_date` varchar(35) DEFAULT NULL,
`address_text` text DEFAULT NULL,
`address_quality` varchar(1) DEFAULT '',
`address_new_user_id` smallint(6) DEFAULT NULL,
`address_new_datetime` datetime NOT NULL DEFAULT current_timestamp(),
`address_changed_user_id` smallint(6) DEFAULT NULL,
`address_changed_datetime` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_cms_menu`

CREATE TABLE `humo_cms_menu` (
`menu_id` int(10) NOT NULL,
`menu_parent_id` int(10) NOT NULL DEFAULT 0,
`menu_order` int(5) NOT NULL DEFAULT 0,
`menu_name` varchar(25) DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_cms_pages`

CREATE TABLE `humo_cms_pages` (
`page_id` int(10) NOT NULL,
`page_status` varchar(1) DEFAULT '',
`page_menu_id` int(10) NOT NULL DEFAULT 0,
`page_order` int(10) NOT NULL DEFAULT 0,
`page_counter` int(10) NOT NULL DEFAULT 0,
`page_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
`page_edit_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
`page_title` varchar(50) DEFAULT '',
`page_text` longtext DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_connections`

CREATE TABLE `humo_connections` (
`connect_id` int(10) UNSIGNED NOT NULL,
`connect_tree_id` smallint(5) DEFAULT NULL,
`connect_order` mediumint(6) DEFAULT NULL,
`connect_kind` varchar(25) DEFAULT NULL,
`connect_sub_kind` varchar(30) DEFAULT NULL,
`connect_connect_id` varchar(25) DEFAULT NULL,
`connect_date` varchar(35) DEFAULT NULL,
`connect_place` varchar(120) DEFAULT NULL,
`connect_time` varchar(25) DEFAULT NULL,
`connect_page` text DEFAULT NULL,
`connect_role` varchar(75) DEFAULT NULL,
`connect_text` text DEFAULT NULL,
`connect_source_id` varchar(25) DEFAULT NULL,
`connect_item_id` varchar(25) DEFAULT NULL,
`connect_status` varchar(10) DEFAULT NULL,
`connect_quality` varchar(1) DEFAULT '',
`connect_new_user_id` smallint(6) DEFAULT NULL,
`connect_new_datetime` datetime NOT NULL DEFAULT current_timestamp(),
`connect_changed_user_id` smallint(6) DEFAULT NULL,
`connect_changed_datetime` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_events`

CREATE TABLE `humo_events` (
`event_id` int(10) UNSIGNED NOT NULL,
`event_tree_id` smallint(5) DEFAULT NULL,
`event_gedcomnr` varchar(25) DEFAULT NULL,
`event_order` mediumint(6) DEFAULT NULL,
`person_id` int(10) UNSIGNED DEFAULT NULL,
`relation_id` int(10) UNSIGNED DEFAULT NULL,
`event_connect_kind` varchar(25) DEFAULT NULL,
`event_connect_id` varchar(25) DEFAULT NULL,
`event_connect_kind2` varchar(25) DEFAULT NULL,
`event_connect_id2` varchar(25) DEFAULT NULL,
`event_pers_age` varchar(15) DEFAULT NULL,
`event_kind` varchar(20) DEFAULT NULL,
`event_event` text DEFAULT NULL,
`event_event_extra` text DEFAULT NULL,
`authority` text DEFAULT NULL,
`stillborn` varchar(1) DEFAULT 'n',
`cause` text DEFAULT NULL,
`cremation` varchar(1) DEFAULT NULL,
`event_end_date` varchar(35) DEFAULT NULL,
`event_gedcom` varchar(25) DEFAULT NULL,
`event_date` varchar(40) DEFAULT NULL,
`date_year` int(11) DEFAULT NULL,
`date_month` tinyint(4) DEFAULT NULL,
`date_day` tinyint(4) DEFAULT NULL,
`place_id` int(10) UNSIGNED DEFAULT NULL,
`event_date_hebnight` varchar(10) DEFAULT NULL,
`event_text` text DEFAULT NULL,
`event_time` text DEFAULT NULL,
`event_quality` varchar(1) DEFAULT '',
`event_new_user_id` smallint(6) DEFAULT NULL,
`event_new_datetime` datetime NOT NULL DEFAULT current_timestamp(),
`event_changed_user_id` smallint(6) DEFAULT NULL,
`event_changed_datetime` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_events_temp`

CREATE TABLE `humo_events_temp` (
`event_id` mediumint(6) UNSIGNED NOT NULL,
`event_tree_id` smallint(5) DEFAULT NULL,
`event_gedcomnr` varchar(20) DEFAULT NULL,
`event_order` mediumint(6) DEFAULT NULL,
`event_connect_kind` varchar(25) DEFAULT NULL,
`event_connect_id` varchar(20) DEFAULT NULL,
`event_pers_age` varchar(15) DEFAULT NULL,
`event_kind` varchar(20) DEFAULT NULL,
`event_event` text DEFAULT NULL,
`event_event_extra` text DEFAULT NULL,
`event_gedcom` varchar(10) DEFAULT NULL,
`event_date` varchar(35) DEFAULT NULL,
`event_place` varchar(75) DEFAULT NULL,
`event_text` text DEFAULT NULL,
`event_quality` varchar(1) DEFAULT '',
`event_new_user` varchar(200) DEFAULT NULL,
`event_new_date` varchar(35) DEFAULT NULL,
`event_new_time` varchar(25) DEFAULT NULL,
`event_changed_user` varchar(200) DEFAULT NULL,
`event_changed_date` varchar(35) DEFAULT NULL,
`event_changed_time` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_families`

CREATE TABLE `humo_families` (
`fam_id` int(10) UNSIGNED NOT NULL,
`fam_tree_id` mediumint(7) DEFAULT NULL,
`fam_gedcomnumber` varchar(25) DEFAULT NULL,
`fam_kind` varchar(50) DEFAULT NULL,
`fam_religion` varchar(50) DEFAULT NULL,
`fam_text` text DEFAULT NULL,
`fam_alive` int(1) DEFAULT NULL,
`fam_cal_date` varchar(35) DEFAULT NULL,
`fam_quality` varchar(1) DEFAULT '',
`fam_counter` mediumint(7) DEFAULT NULL,
`fam_new_user_id` smallint(6) DEFAULT NULL,
`fam_new_datetime` datetime NOT NULL DEFAULT current_timestamp(),
`fam_changed_user_id` smallint(6) DEFAULT NULL,
`fam_changed_datetime` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_families_temp`

CREATE TABLE `humo_families_temp` (
`fam_id` mediumint(7) UNSIGNED NOT NULL,
`fam_tree_id` mediumint(7) DEFAULT NULL,
`fam_gedcomnumber` varchar(20) DEFAULT NULL,
`fam_man` varchar(20) DEFAULT NULL,
`fam_man_age` varchar(15) DEFAULT NULL,
`fam_woman` varchar(20) DEFAULT NULL,
`fam_woman_age` varchar(15) DEFAULT NULL,
`fam_children` text DEFAULT NULL,
`fam_kind` varchar(50) DEFAULT NULL,
`fam_relation_date` varchar(35) DEFAULT NULL,
`fam_relation_place` varchar(75) DEFAULT NULL,
`fam_relation_text` text DEFAULT NULL,
`fam_relation_end_date` varchar(35) DEFAULT NULL,
`fam_marr_notice_date` varchar(35) DEFAULT NULL,
`fam_marr_notice_place` varchar(75) DEFAULT NULL,
`fam_marr_notice_text` text DEFAULT NULL,
`fam_marr_date` varchar(35) DEFAULT NULL,
`fam_marr_place` varchar(75) DEFAULT NULL,
`fam_marr_text` text DEFAULT NULL,
`fam_marr_authority` text DEFAULT NULL,
`fam_marr_church_notice_date` varchar(35) DEFAULT NULL,
`fam_marr_church_notice_place` varchar(75) DEFAULT NULL,
`fam_marr_church_notice_text` text DEFAULT NULL,
`fam_marr_church_date` varchar(35) DEFAULT NULL,
`fam_marr_church_place` varchar(75) DEFAULT NULL,
`fam_marr_church_text` text DEFAULT NULL,
`fam_religion` varchar(50) DEFAULT NULL,
`fam_div_date` varchar(35) DEFAULT NULL,
`fam_div_place` varchar(75) DEFAULT NULL,
`fam_div_text` text DEFAULT NULL,
`fam_div_authority` text DEFAULT NULL,
`fam_text` text DEFAULT NULL,
`fam_alive` int(1) DEFAULT NULL,
`fam_cal_date` varchar(35) DEFAULT NULL,
`fam_quality` varchar(1) DEFAULT '',
`fam_counter` mediumint(7) DEFAULT NULL,
`fam_new_user` varchar(200) DEFAULT NULL,
`fam_new_date` varchar(35) DEFAULT NULL,
`fam_new_time` varchar(25) DEFAULT NULL,
`fam_changed_user` varchar(200) DEFAULT NULL,
`fam_changed_date` varchar(35) DEFAULT NULL,
`fam_changed_time` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_groups`

CREATE TABLE `humo_groups` (
`group_id` smallint(5) UNSIGNED NOT NULL,
`group_name` varchar(25) DEFAULT NULL,
`group_privacy` varchar(1) DEFAULT NULL,
`group_menu_places` varchar(1) DEFAULT NULL,
`group_admin` varchar(1) DEFAULT NULL,
`group_statistics` varchar(1) NOT NULL DEFAULT 'j',
`group_menu_persons` varchar(1) NOT NULL DEFAULT 'j',
`group_menu_names` varchar(1) NOT NULL DEFAULT 'j',
`group_menu_login` varchar(1) NOT NULL DEFAULT 'j',
`group_menu_cms` varchar(1) NOT NULL DEFAULT 'y',
`group_menu_chat` varchar(1) NOT NULL DEFAULT 'n',
`group_menu_change_password` varchar(1) NOT NULL DEFAULT 'y',
`group_birthday_rss` varchar(1) NOT NULL DEFAULT 'j',
`group_birthday_list` varchar(1) NOT NULL DEFAULT 'j',
`group_latestchanges` varchar(1) NOT NULL DEFAULT 'j',
`group_contact` varchar(1) NOT NULL DEFAULT 'j',
`group_googlemaps` varchar(1) NOT NULL DEFAULT 'j',
`group_relcalc` varchar(1) NOT NULL DEFAULT 'j',
`group_showstatistics` varchar(1) NOT NULL DEFAULT 'j',
`group_sources` varchar(1) DEFAULT NULL,
`group_show_restricted_source` varchar(1) NOT NULL DEFAULT 'y',
`group_source_presentation` varchar(20) DEFAULT NULL,
`group_text_presentation` varchar(20) NOT NULL DEFAULT 'show',
`group_pictures` varchar(1) DEFAULT NULL,
`group_photobook` varchar(1) NOT NULL DEFAULT 'n',
`group_gedcomnr` varchar(1) DEFAULT NULL,
`group_living_place` varchar(1) DEFAULT NULL,
`group_places` varchar(1) DEFAULT NULL,
`group_religion` varchar(1) DEFAULT NULL,
`group_place_date` varchar(1) DEFAULT NULL,
`group_kindindex` varchar(1) DEFAULT NULL,
`group_event` varchar(1) DEFAULT NULL,
`group_addresses` varchar(1) DEFAULT NULL,
`group_own_code` varchar(1) DEFAULT NULL,
`group_citation_generation` varchar(1) NOT NULL DEFAULT 'n',
`group_user_notes` varchar(1) NOT NULL DEFAULT 'n',
`group_user_notes_notes` varchar(1) NOT NULL DEFAULT 'n',
`group_user_notes_show` varchar(1) NOT NULL DEFAULT 'n',
`group_family_presentation` varchar(10) NOT NULL DEFAULT 'compact',
`group_maps_presentation` varchar(10) NOT NULL DEFAULT 'hide',
`group_show_age_living_person` varchar(1) NOT NULL DEFAULT 'y',
`group_pdf_button` varchar(1) DEFAULT NULL,
`group_rtf_button` varchar(1) NOT NULL DEFAULT 'n',
`group_work_text` varchar(1) DEFAULT NULL,
`group_texts` varchar(1) DEFAULT NULL,
`group_text_pers` varchar(1) DEFAULT NULL,
`group_texts_pers` varchar(1) DEFAULT NULL,
`group_texts_fam` varchar(1) DEFAULT NULL,
`group_alive` varchar(1) DEFAULT NULL,
`group_alive_date_act` varchar(1) DEFAULT NULL,
`group_alive_date` varchar(4) DEFAULT NULL,
`group_death_date_act` varchar(1) DEFAULT NULL,
`group_death_date` varchar(4) DEFAULT NULL,
`group_birth_date` varchar(4) DEFAULT NULL,
`group_filter_date` varchar(1) NOT NULL DEFAULT 'n',
`group_filter_death` varchar(1) DEFAULT NULL,
`group_filter_total` varchar(1) DEFAULT NULL,
`group_filter_name` varchar(1) DEFAULT NULL,
`group_filter_fam` varchar(1) DEFAULT NULL,
`group_filter_pers_show_act` varchar(1) DEFAULT NULL,
`group_filter_pers_show` varchar(50) DEFAULT NULL,
`group_filter_pers_hide_act` varchar(1) DEFAULT NULL,
`group_filter_pers_hide` varchar(50) DEFAULT NULL,
`group_filter_gender` varchar(1) DEFAULT NULL,
`group_pers_hide_totally_act` varchar(1) NOT NULL DEFAULT 'n',
`group_pers_hide_totally` varchar(50) NOT NULL DEFAULT 'X',
`group_gen_protection` varchar(1) NOT NULL DEFAULT 'n',
`group_hide_trees` varchar(200) NOT NULL DEFAULT '',
`group_edit_trees` varchar(200) NOT NULL DEFAULT '',
`group_hide_photocat` varchar(200) NOT NULL DEFAULT '',
`group_upd_family` varchar(1) DEFAULT 'y' COMMENT 'Can update family data',
`group_upd_anc` varchar(1) DEFAULT '1' COMMENT 'Can update how many levels of ancestors data',
`group_upd_desc` varchar(1) DEFAULT '1' COMMENT 'Can update how many levels of descendants data',
`group_upd_rel` varchar(1) DEFAULT 'y' COMMENT 'Can update relatives data',
`group_upd_relofrel` varchar(1) DEFAULT 'n' COMMENT 'Can update relative of a relatives data'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_location`

CREATE TABLE `humo_location` (
`location_id` int(10) UNSIGNED NOT NULL,
`location_location` varchar(120) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
`location_lat` float(10,6) DEFAULT NULL,
`location_lng` float(10,6) DEFAULT NULL,
`location_status` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `humo_persons`

CREATE TABLE `humo_persons` (
`pers_id` int(10) UNSIGNED NOT NULL,
`pers_gedcomnumber` varchar(25) DEFAULT NULL,
`pers_tree_id` mediumint(7) DEFAULT NULL,
`pers_tree_prefix` varchar(10) DEFAULT NULL,
`pers_indexnr` varchar(25) DEFAULT NULL,
`pers_firstname` varchar(50) DEFAULT NULL,
`pers_prefix` varchar(20) DEFAULT NULL,
`pers_lastname` varchar(50) DEFAULT NULL,
`pers_patronym` varchar(50) DEFAULT NULL,
`pers_name_text` text DEFAULT NULL,
`pers_sexe` varchar(1) DEFAULT NULL,
`pers_own_code` varchar(100) DEFAULT NULL,
`pers_religion` varchar(50) DEFAULT NULL,
`pers_text` text DEFAULT NULL,
`pers_alive` varchar(20) DEFAULT NULL,
`pers_cal_date` varchar(35) DEFAULT NULL,
`pers_quality` varchar(1) DEFAULT '',
`pers_new_user_id` smallint(6) DEFAULT NULL,
`pers_new_datetime` datetime NOT NULL DEFAULT current_timestamp(),
`pers_changed_user_id` smallint(6) DEFAULT NULL,
`pers_changed_datetime` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_persons_temp`

CREATE TABLE `humo_persons_temp` (
`pers_id` mediumint(7) UNSIGNED NOT NULL,
`pers_gedcomnumber` varchar(20) DEFAULT NULL,
`pers_tree_id` mediumint(7) DEFAULT NULL,
`pers_tree_prefix` varchar(10) DEFAULT NULL,
`pers_famc` varchar(50) DEFAULT NULL,
`pers_fams` varchar(150) DEFAULT NULL,
`pers_indexnr` varchar(20) DEFAULT NULL,
`pers_firstname` varchar(50) DEFAULT NULL,
`pers_callname` varchar(20) DEFAULT NULL,
`pers_prefix` varchar(20) DEFAULT NULL,
`pers_lastname` varchar(50) DEFAULT NULL,
`pers_patronym` varchar(50) DEFAULT NULL,
`pers_name_text` text DEFAULT NULL,
`pers_sexe` varchar(1) DEFAULT NULL,
`pers_own_code` varchar(100) DEFAULT NULL,
`pers_birth_place` varchar(75) DEFAULT NULL,
`pers_birth_date` varchar(35) DEFAULT NULL,
`pers_birth_time` varchar(25) DEFAULT NULL,
`pers_birth_text` text DEFAULT NULL,
`pers_stillborn` varchar(1) DEFAULT 'n',
`pers_bapt_place` varchar(75) DEFAULT NULL,
`pers_bapt_date` varchar(35) DEFAULT NULL,
`pers_bapt_text` text DEFAULT NULL,
`pers_religion` varchar(50) DEFAULT NULL,
`pers_death_place` varchar(75) DEFAULT NULL,
`pers_death_date` varchar(35) DEFAULT NULL,
`pers_death_time` varchar(25) DEFAULT NULL,
`pers_death_text` text DEFAULT NULL,
`pers_death_cause` varchar(255) DEFAULT NULL,
`pers_death_age` varchar(15) DEFAULT NULL,
`pers_buried_place` varchar(75) DEFAULT NULL,
`pers_buried_date` varchar(35) DEFAULT NULL,
`pers_buried_text` text DEFAULT NULL,
`pers_cremation` varchar(1) DEFAULT NULL,
`pers_place_index` text DEFAULT NULL,
`pers_text` text DEFAULT NULL,
`pers_alive` varchar(20) DEFAULT NULL,
`pers_cal_date` varchar(35) DEFAULT NULL,
`pers_quality` varchar(1) DEFAULT '',
`pers_new_user` varchar(200) DEFAULT NULL,
`pers_new_date` varchar(35) DEFAULT NULL,
`pers_new_time` varchar(25) DEFAULT NULL,
`pers_changed_user` varchar(200) DEFAULT NULL,
`pers_changed_date` varchar(35) DEFAULT NULL,
`pers_changed_time` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_photocat`

CREATE TABLE `humo_photocat` (
`photocat_id` int(11) NOT NULL,
`photocat_order` mediumint(6) DEFAULT NULL,
`photocat_prefix` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
`photocat_language` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
`photocat_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `humo_pw_retrieval`

CREATE TABLE `humo_pw_retrieval` (
`retrieval_userid` varchar(20) NOT NULL,
`retrieval_pkey` varchar(32) NOT NULL,
`retrieval_time` varchar(10) NOT NULL,
`retrieval_status` varchar(7) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_relations_persons`

CREATE TABLE `humo_relations_persons` (
`id` int(10) UNSIGNED NOT NULL,
`relation_id` int(10) UNSIGNED NOT NULL,
`relation_gedcomnumber` varchar(30) DEFAULT NULL,
`person_id` int(10) UNSIGNED NOT NULL,
`person_gedcomnumber` varchar(30) DEFAULT NULL,
`person_age` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
`tree_id` smallint(5) NOT NULL,
`relation_type` varchar(20) DEFAULT NULL,
`relation_order` tinyint(3) UNSIGNED DEFAULT NULL,
`partner_order` tinyint(3) UNSIGNED DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `humo_repositories`

CREATE TABLE `humo_repositories` (
`repo_id` int(10) UNSIGNED NOT NULL,
`repo_tree_id` smallint(5) DEFAULT NULL,
`repo_gedcomnr` varchar(25) DEFAULT NULL,
`repo_name` text DEFAULT NULL,
`repo_address` text DEFAULT NULL,
`repo_zip` varchar(20) DEFAULT NULL,
`repo_place` varchar(120) DEFAULT NULL,
`repo_phone` varchar(25) DEFAULT NULL,
`repo_date` varchar(35) DEFAULT NULL,
`repo_text` text DEFAULT NULL,
`repo_mail` varchar(100) DEFAULT NULL,
`repo_url` varchar(150) DEFAULT NULL,
`repo_quality` varchar(1) DEFAULT '',
`repo_new_user_id` smallint(6) DEFAULT NULL,
`repo_new_datetime` datetime NOT NULL DEFAULT current_timestamp(),
`repo_changed_user_id` smallint(6) DEFAULT NULL,
`repo_changed_datetime` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_settings`

CREATE TABLE `humo_settings` (
`setting_id` int(10) UNSIGNED NOT NULL,
`setting_variable` varchar(50) DEFAULT NULL,
`setting_value` text DEFAULT NULL,
`setting_order` smallint(5) DEFAULT NULL,
`setting_tree_id` smallint(5) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_sources`

CREATE TABLE `humo_sources` (
`source_id` int(10) UNSIGNED NOT NULL,
`source_tree_id` smallint(5) DEFAULT NULL,
`source_status` varchar(10) DEFAULT NULL,
`source_gedcomnr` varchar(25) DEFAULT NULL,
`source_order` mediumint(6) DEFAULT NULL,
`source_title` text DEFAULT NULL,
`source_abbr` varchar(50) DEFAULT NULL,
`source_date` varchar(35) DEFAULT NULL,
`source_publ` varchar(150) DEFAULT NULL,
`source_place` varchar(120) DEFAULT NULL,
`source_refn` varchar(50) DEFAULT NULL,
`source_auth` varchar(50) DEFAULT NULL,
`source_subj` varchar(248) DEFAULT NULL,
`source_item` varchar(30) DEFAULT NULL,
`source_kind` varchar(50) DEFAULT NULL,
`source_text` text DEFAULT NULL,
`source_repo_name` varchar(50) DEFAULT NULL,
`source_repo_caln` varchar(50) DEFAULT NULL,
`source_repo_page` varchar(50) DEFAULT NULL,
`source_repo_gedcomnr` varchar(25) DEFAULT NULL,
`source_quality` varchar(1) DEFAULT '',
`source_new_user_id` smallint(6) DEFAULT NULL,
`source_new_datetime` datetime NOT NULL DEFAULT current_timestamp(),
`source_changed_user_id` smallint(6) DEFAULT NULL,
`source_changed_datetime` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_stat_country`

CREATE TABLE `humo_stat_country` (
`stat_country_id` int(10) NOT NULL,
`stat_country_ip_address` varchar(40) DEFAULT NULL,
`stat_country_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `humo_stat_date`

CREATE TABLE `humo_stat_date` (
`stat_id` int(10) NOT NULL,
`stat_easy_id` varchar(100) DEFAULT NULL,
`stat_ip_address` varchar(40) DEFAULT NULL,
`stat_user_agent` varchar(255) DEFAULT NULL,
`stat_tree_id` varchar(5) DEFAULT NULL,
`stat_gedcom_fam` varchar(25) DEFAULT NULL,
`stat_gedcom_man` varchar(25) DEFAULT NULL,
`stat_gedcom_woman` varchar(25) DEFAULT NULL,
`stat_date_stat` datetime NOT NULL,
`stat_date_linux` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_texts`

CREATE TABLE `humo_texts` (
`text_id` int(10) UNSIGNED NOT NULL,
`text_tree_id` smallint(5) DEFAULT NULL,
`text_gedcomnr` varchar(25) DEFAULT NULL,
`text_text` text DEFAULT NULL,
`text_quality` varchar(1) DEFAULT '',
`text_new_user_id` smallint(6) DEFAULT NULL,
`text_new_datetime` datetime NOT NULL DEFAULT current_timestamp(),
`text_changed_user_id` smallint(6) DEFAULT NULL,
`text_changed_datetime` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_trees`

CREATE TABLE `humo_trees` (
`tree_id` smallint(5) UNSIGNED NOT NULL,
`tree_order` smallint(5) DEFAULT NULL,
`tree_prefix` varchar(10) DEFAULT NULL,
`tree_date` varchar(20) DEFAULT NULL,
`tree_persons` varchar(10) DEFAULT NULL,
`tree_families` varchar(10) DEFAULT NULL,
`tree_email` varchar(100) DEFAULT NULL,
`tree_owner` varchar(100) DEFAULT NULL,
`tree_pict_path` varchar(100) DEFAULT NULL,
`tree_privacy` varchar(100) DEFAULT NULL,
`tree_gedcom` varchar(100) DEFAULT NULL,
`tree_gedcom_program` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_tree_texts`

CREATE TABLE `humo_tree_texts` (
`treetext_id` smallint(5) UNSIGNED NOT NULL,
`treetext_tree_id` smallint(5) DEFAULT NULL,
`treetext_language` varchar(100) DEFAULT NULL,
`treetext_name` varchar(100) DEFAULT NULL,
`treetext_mainmenu_text` text DEFAULT NULL,
`treetext_mainmenu_source` text DEFAULT NULL,
`treetext_family_top` text DEFAULT NULL,
`treetext_family_footer` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_unprocessed_tags`

CREATE TABLE `humo_unprocessed_tags` (
`tag_id` int(10) UNSIGNED NOT NULL,
`tag_pers_id` int(10) UNSIGNED DEFAULT NULL,
`tag_rel_id` int(10) UNSIGNED DEFAULT NULL,
`tag_event_id` int(10) UNSIGNED DEFAULT NULL,
`tag_source_id` int(10) UNSIGNED DEFAULT NULL,
`tag_connect_id` int(10) UNSIGNED DEFAULT NULL,
`tag_repo_id` int(10) UNSIGNED DEFAULT NULL,
`tag_place_id` int(10) UNSIGNED DEFAULT NULL,
`tag_address_id` int(10) UNSIGNED DEFAULT NULL,
`tag_text_id` int(10) UNSIGNED DEFAULT NULL,
`tag_tree_id` smallint(5) DEFAULT NULL,
`tag_tag` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_users`

CREATE TABLE `humo_users` (
`user_id` smallint(5) UNSIGNED NOT NULL,
`user_name` varchar(25) DEFAULT NULL,
`user_gedcomnbr` varchar(7) DEFAULT NULL,
`user_mail` varchar(100) DEFAULT NULL,
`user_trees` text DEFAULT NULL,
`user_remark` text DEFAULT NULL,
`user_password` varchar(50) DEFAULT NULL,
`user_password_salted` varchar(255) DEFAULT NULL,
`user_2fa_enabled` varchar(1) DEFAULT '',
`user_2fa_auth_secret` varchar(50) DEFAULT '',
`user_status` varchar(1) DEFAULT NULL,
`user_group_id` smallint(5) DEFAULT NULL,
`user_hide_trees` varchar(200) NOT NULL DEFAULT '',
`user_edit_trees` varchar(200) NOT NULL DEFAULT '',
`user_ip_address` varchar(45) DEFAULT '',
`user_register_date` varchar(20) DEFAULT NULL,
`user_last_visit` varchar(25) DEFAULT NULL,
`oauth_provider` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
`first_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
`last_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
`gender` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
`locale` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
`picture` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
`link` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
`tg_userid` varchar(12) DEFAULT NULL COMMENT 'Telegram User ID',
`created` datetime DEFAULT NULL,
`modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_user_log`

CREATE TABLE `humo_user_log` (
`log_id` mediumint(6) UNSIGNED NOT NULL,
`log_username` varchar(25) DEFAULT NULL,
`log_date` varchar(20) DEFAULT NULL,
`log_ip_address` varchar(45) DEFAULT NULL,
`log_user_admin` varchar(5) DEFAULT '',
`log_status` varchar(10) DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `humo_user_notes`

CREATE TABLE `humo_user_notes` (
`note_id` smallint(5) UNSIGNED NOT NULL,
`note_order` smallint(5) DEFAULT NULL,
`note_new_datetime` datetime NOT NULL DEFAULT current_timestamp(),
`note_new_user_id` smallint(5) DEFAULT NULL,
`note_changed_datetime` datetime DEFAULT NULL ON UPDATE current_timestamp(),
`note_changed_user_id` smallint(5) DEFAULT NULL,
`note_guest_name` varchar(25) DEFAULT NULL,
`note_guest_mail` varchar(25) DEFAULT NULL,
`note_note` text DEFAULT NULL,
`note_status` varchar(15) DEFAULT NULL,
`note_priority` varchar(15) DEFAULT NULL,
`note_tree_id` mediumint(7) DEFAULT NULL,
`note_kind` varchar(10) DEFAULT NULL,
`note_connect_kind` varchar(20) DEFAULT NULL,
`note_connect_id` varchar(25) DEFAULT NULL,
`note_names` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `Individuals`

CREATE TABLE `Individuals` (
`pers_id` mediumint(7) UNSIGNED DEFAULT NULL,
`pers_gedcomnbr` varchar(20) DEFAULT NULL,
`persFName` varchar(50) DEFAULT NULL,
`persLName` varchar(50) DEFAULT NULL,
`persFather` varchar(50) DEFAULT NULL,
`persMother` varchar(50) DEFAULT NULL,
`gender` varchar(1) DEFAULT NULL,
`address_id` mediumint(6) UNSIGNED DEFAULT NULL,
`phone` varchar(20) DEFAULT NULL,
`email` varchar(35) DEFAULT NULL,
`city` varchar(75) DEFAULT NULL,
`address` text DEFAULT NULL,
`occupation` text DEFAULT NULL,
`education` mediumtext DEFAULT NULL,
`dob` varchar(35) DEFAULT NULL,
`age` double DEFAULT NULL,
`dod` varchar(35) DEFAULT NULL,
`isAlive` varchar(1) DEFAULT NULL,
`isMarried` varchar(1) DEFAULT NULL,
`persSpouse` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `markers`

CREATE TABLE `markers` (
`id` int(11) NOT NULL,
`name` varchar(60) NOT NULL,
`address` varchar(80) NOT NULL,
`lat` float(10,6) NOT NULL,
`lng` float(10,6) NOT NULL,
`type` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `missingphone`

CREATE TABLE `missingphone` (
`pers_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `spouse`

CREATE TABLE `spouse` (
`pers_gedcomnumber` varchar(20) DEFAULT NULL,
`pers_indexnr` varchar(20) DEFAULT NULL,
`sp_gedcomnumber` varchar(20) DEFAULT NULL,
`spouse` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `users`

CREATE TABLE `users` (
`id` int(11) NOT NULL,
`username` varchar(50) NOT NULL,
`password` varchar(255) NOT NULL,
`created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_address`

CREATE TABLE `v_address` (
`pers_id` mediumint(7) UNSIGNED DEFAULT NULL,
`address_id` mediumint(6) UNSIGNED DEFAULT NULL,
`phone` varchar(20) DEFAULT NULL,
`email` varchar(35) DEFAULT NULL,
`city` varchar(75) DEFAULT NULL,
`address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_brother`

CREATE TABLE `v_brother` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_bro_daughter`

CREATE TABLE `v_bro_daughter` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_bro_son`

CREATE TABLE `v_bro_son` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_bro_sp`

CREATE TABLE `v_bro_sp` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_dad_bro_sp`

CREATE TABLE `v_dad_bro_sp` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_dad_sis_sp`

CREATE TABLE `v_dad_sis_sp` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_daughter`

CREATE TABLE `v_daughter` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_daughter_sp`

CREATE TABLE `v_daughter_sp` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_father`

CREATE TABLE `v_father` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_father_brother`

CREATE TABLE `v_father_brother` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_father_sister`

CREATE TABLE `v_father_sister` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_getphones_city`

CREATE TABLE `v_getphones_city` (
`self_rin` varchar(20) DEFAULT NULL,
`self_fname` varchar(50) DEFAULT NULL,
`self_father` varchar(50) DEFAULT NULL,
`self_phone` varchar(20) DEFAULT NULL,
`self_city` varchar(75) DEFAULT NULL,
`rel_rin` varchar(20) DEFAULT NULL,
`Relation` varchar(14) DEFAULT NULL,
`rel_fname` varchar(50) DEFAULT NULL,
`rel_phone` varchar(20) DEFAULT NULL,
`rel_city` varchar(75) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_get_dob`

CREATE TABLE `v_get_dob` (
`self_rin` varchar(20) DEFAULT NULL,
`self_fname` varchar(50) DEFAULT NULL,
`self_father` varchar(50) DEFAULT NULL,
`self_phone` varchar(20) DEFAULT NULL,
`self_city` varchar(75) DEFAULT NULL,
`rel_rin` varchar(20) DEFAULT NULL,
`Relation` varchar(14) DEFAULT NULL,
`rel_fname` varchar(50) DEFAULT NULL,
`rel_phone` varchar(20) DEFAULT NULL,
`rel_city` varchar(75) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_mat_gfather`

CREATE TABLE `v_mat_gfather` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_mat_gmother`

CREATE TABLE `v_mat_gmother` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_mom_bro_sp`

CREATE TABLE `v_mom_bro_sp` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_mom_sis_sp`

CREATE TABLE `v_mom_sis_sp` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_mother`

CREATE TABLE `v_mother` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_mother_brother`

CREATE TABLE `v_mother_brother` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_mother_sister`

CREATE TABLE `v_mother_sister` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_pat_gfather`

CREATE TABLE `v_pat_gfather` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_pat_gmother`

CREATE TABLE `v_pat_gmother` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_relatives`

CREATE TABLE `v_relatives` (
`pers_id` mediumint(7) UNSIGNED DEFAULT NULL,
`rel_id` mediumint(7) UNSIGNED DEFAULT NULL,
`persFName` varchar(50) DEFAULT NULL,
`age` double DEFAULT NULL,
`phone` varchar(20) DEFAULT NULL,
`city` varchar(75) DEFAULT NULL,
`isMarried` varchar(1) DEFAULT NULL,
`isAlive` varchar(1) DEFAULT NULL,
`gender` varchar(1) DEFAULT NULL,
`relation` varchar(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_relatives_info`

CREATE TABLE `v_relatives_info` (
`self_rin` varchar(20) DEFAULT NULL,
`rel_rin` varchar(20) DEFAULT NULL,
`Relation` varchar(14) DEFAULT NULL,
`Name` varchar(50) DEFAULT NULL,
`Father` varchar(50) DEFAULT NULL,
`Date of Birth` varchar(35) DEFAULT NULL,
`Phone` varchar(20) DEFAULT NULL,
`City` varchar(75) DEFAULT NULL,
`Spouse` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_sister`

CREATE TABLE `v_sister` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_sis_daughter`

CREATE TABLE `v_sis_daughter` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_sis_son`

CREATE TABLE `v_sis_son` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_sis_sp`

CREATE TABLE `v_sis_sp` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_son`

CREATE TABLE `v_son` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_son_sp`

CREATE TABLE `v_son_sp` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `v_spouse`

CREATE TABLE `v_spouse` (
`self_id` mediumint(7) UNSIGNED DEFAULT NULL,
`relative_id` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `xx_pers_details`

CREATE TABLE `xx_pers_details` (
`pd_gedcomnumber` varchar(20) NOT NULL,
`pd_father` varchar(50) DEFAULT NULL,
`pd_mother` varchar(50) DEFAULT NULL,
`pd_spouse` varchar(50) DEFAULT NULL,
`pd_married` varchar(1) DEFAULT NULL,
`pd_occupation` varchar(50) DEFAULT NULL,
`pd_education` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

---



## --

-- Table structure for table `x_datasurvey`

CREATE TABLE `x_datasurvey` (
`ds_id` mediumint(7) NOT NULL,
`ds_status` varchar(1) NOT NULL COMMENT 'A-ctive, I-nactive',
`ds_pers_gedcomnbr` varchar(10) DEFAULT NULL,
`ds_firstname` varchar(50) DEFAULT NULL,
`ds_lastname` varchar(50) DEFAULT NULL,
`ds_father` varchar(30) DEFAULT NULL,
`ds_mother` varchar(30) DEFAULT NULL,
`ds_sexe` varchar(1) DEFAULT NULL,
`ds_yob` smallint(5) UNSIGNED DEFAULT NULL,
`ds_phone` varchar(15) DEFAULT NULL,
`ds_email` varchar(30) DEFAULT NULL,
`ds_social` varchar(30) DEFAULT NULL,
`ds_town` varchar(20) DEFAULT NULL,
`ds_city` varchar(20) DEFAULT NULL,
`ds_state` varchar(20) DEFAULT NULL,
`ds_country` varchar(20) DEFAULT NULL,
`ds_maritalstatus` varchar(1) DEFAULT NULL,
`ds_iseligiblebachelor` char(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL COMMENT 'Is Eligible Bachelor?',
`ds_skintone` varchar(20) DEFAULT NULL,
`ds_height` tinyint(2) DEFAULT NULL,
`ds_bodytype` varchar(20) DEFAULT NULL,
`ds_disability` varchar(20) DEFAULT NULL,
`ds_smoke` varchar(1) DEFAULT NULL,
`ds_brothers` tinyint(2) UNSIGNED DEFAULT NULL,
`ds_sisters` tinyint(2) UNSIGNED DEFAULT NULL,
`ds_children` tinyint(2) UNSIGNED DEFAULT NULL,
`ds_nanihal` varchar(20) DEFAULT NULL,
`ds_mothertongue` varchar(20) DEFAULT NULL,
`ds_edu_level` varchar(20) DEFAULT NULL,
`ds_edu_field` varchar(20) DEFAULT NULL,
`ds_works_with` varchar(20) DEFAULT NULL,
`ds_works_as` varchar(20) DEFAULT NULL,
`ds_annual_income` mediumint(8) UNSIGNED DEFAULT NULL,
`ds_maslak` varchar(30) DEFAULT NULL,
`ds_ownwords` varchar(200) DEFAULT NULL,
`ds_sp_height` tinyint(2) DEFAULT NULL,
`ds_sp_educ` int(20) DEFAULT NULL,
`ds_sp_occu` int(20) DEFAULT NULL,
`ds_sp_maslak` int(30) DEFAULT NULL,
`ds_sp_ownwords` int(200) DEFAULT NULL,
`ds_new_time` datetime DEFAULT current_timestamp(),
`ds_new_user` varchar(20) DEFAULT NULL,
`ds_changed_time` datetime DEFAULT current_timestamp(),
`ds_changed_user` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `x_field_survey_data`

CREATE TABLE `x_field_survey_data` (
`survey_no` int(3) NOT NULL,
`pers_gedcomnbr` varchar(20) NOT NULL,
`text1` varchar(20) NOT NULL,
`text2` varchar(20) NOT NULL,
`text3` varchar(20) NOT NULL,
`text4` varchar(20) NOT NULL,
`text5` varchar(20) NOT NULL,
`number1` int(7) NOT NULL,
`number2` int(7) NOT NULL,
`number3` int(7) NOT NULL,
`number4` int(7) NOT NULL,
`number5` int(7) NOT NULL,
`date1` date NOT NULL,
`date2` date NOT NULL,
`date3` date NOT NULL,
`date4` date NOT NULL,
`date5` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `x_field_survey_dtl`

CREATE TABLE `x_field_survey_dtl` (
`survey_no` int(3) NOT NULL,
`colname` text NOT NULL,
`label` text NOT NULL,
`required` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

---



## --

-- Table structure for table `x_field_survey_hdr`

CREATE TABLE `x_field_survey_hdr` (
`survey_no` int(3) NOT NULL,
`survey_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

## --

-- Indexes for dumped tables

## --

-- Indexes for table `humo_addresses`

ALTER TABLE `humo_addresses`
ADD PRIMARY KEY (`address_id`),
ADD KEY `address_gedcomnr` (`address_gedcomnr`);

## --

-- Indexes for table `humo_connections`

ALTER TABLE `humo_connections`
ADD PRIMARY KEY (`connect_id`);

## --

-- Indexes for table `humo_events`

ALTER TABLE `humo_events`
ADD PRIMARY KEY (`event_id`),
ADD KEY `idx_event_sync` (`event_tree_id`,`event_connect_id`),
ADD KEY `person_id` (`person_id`),
ADD KEY `relation_id` (`relation_id`),
ADD KEY `place_id` (`place_id`),
ADD KEY `event_connect_id2` (`event_connect_id2`);

## --

-- Indexes for table `humo_families`

ALTER TABLE `humo_families`
ADD PRIMARY KEY (`fam_id`);

## --

-- Indexes for table `humo_location`

ALTER TABLE `humo_location`
ADD PRIMARY KEY (`location_id`);

## --

-- Indexes for table `humo_persons`

ALTER TABLE `humo_persons`
ADD PRIMARY KEY (`pers_id`),
ADD KEY `idx_pers_sync` (`pers_tree_id`,`pers_gedcomnumber`);

## --

-- Indexes for table `humo_relations_persons`

ALTER TABLE `humo_relations_persons`
ADD PRIMARY KEY (`id`),
ADD KEY `idx_relation_id` (`relation_id`),
ADD KEY `idx_relation_gedcomnumber` (`relation_gedcomnumber`),
ADD KEY `idx_person_id` (`person_id`),
ADD KEY `idx_person_gedcomnumber` (`person_gedcomnumber`),
ADD KEY `idx_relation_type` (`relation_type`);

## --

-- Indexes for table `humo_repositories`

ALTER TABLE `humo_repositories`
ADD PRIMARY KEY (`repo_id`);

## --

-- Indexes for table `humo_settings`

ALTER TABLE `humo_settings`
ADD PRIMARY KEY (`setting_id`);

## --

-- Indexes for table `humo_sources`

ALTER TABLE `humo_sources`
ADD PRIMARY KEY (`source_id`);

## --

-- Indexes for table `humo_stat_country`

ALTER TABLE `humo_stat_country`
ADD PRIMARY KEY (`stat_country_id`);

## --

-- Indexes for table `humo_stat_date`

ALTER TABLE `humo_stat_date`
ADD PRIMARY KEY (`stat_id`);

## --

-- Indexes for table `humo_texts`

ALTER TABLE `humo_texts`
ADD PRIMARY KEY (`text_id`),
ADD KEY `text_gedcomnr` (`text_gedcomnr`);

## --

-- Indexes for table `humo_unprocessed_tags`

ALTER TABLE `humo_unprocessed_tags`
ADD PRIMARY KEY (`tag_id`);

## --

-- Indexes for table `humo_users`

ALTER TABLE `humo_users`
ADD PRIMARY KEY (`user_id`);

## --

-- Indexes for table `humo_user_log`

ALTER TABLE `humo_user_log`
ADD PRIMARY KEY (`log_id`);

## --

-- Indexes for table `humo_user_notes`

ALTER TABLE `humo_user_notes`
ADD PRIMARY KEY (`note_id`);

## --

-- AUTO_INCREMENT for dumped tables

## --

-- AUTO_INCREMENT for table `humo_addresses`

ALTER TABLE `humo_addresses`
MODIFY `address_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

## --

-- AUTO_INCREMENT for table `humo_connections`

ALTER TABLE `humo_connections`
MODIFY `connect_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

## --

-- AUTO_INCREMENT for table `humo_events`

ALTER TABLE `humo_events`
MODIFY `event_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

## --

-- AUTO_INCREMENT for table `humo_families`

ALTER TABLE `humo_families`
MODIFY `fam_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

## --

-- AUTO_INCREMENT for table `humo_location`

ALTER TABLE `humo_location`
MODIFY `location_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

## --

-- AUTO_INCREMENT for table `humo_persons`

ALTER TABLE `humo_persons`
MODIFY `pers_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

## --

-- AUTO_INCREMENT for table `humo_relations_persons`

ALTER TABLE `humo_relations_persons`
MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

## --

-- AUTO_INCREMENT for table `humo_repositories`

ALTER TABLE `humo_repositories`
MODIFY `repo_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

## --

-- AUTO_INCREMENT for table `humo_settings`

ALTER TABLE `humo_settings`
MODIFY `setting_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

## --

-- AUTO_INCREMENT for table `humo_sources`

ALTER TABLE `humo_sources`
MODIFY `source_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

## --

-- AUTO_INCREMENT for table `humo_stat_country`

ALTER TABLE `humo_stat_country`
MODIFY `stat_country_id` int(10) NOT NULL AUTO_INCREMENT;

## --

-- AUTO_INCREMENT for table `humo_stat_date`

ALTER TABLE `humo_stat_date`
MODIFY `stat_id` int(10) NOT NULL AUTO_INCREMENT;

## --

-- AUTO_INCREMENT for table `humo_texts`

ALTER TABLE `humo_texts`
MODIFY `text_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

## --

-- AUTO_INCREMENT for table `humo_unprocessed_tags`

ALTER TABLE `humo_unprocessed_tags`
MODIFY `tag_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

## --

-- AUTO_INCREMENT for table `humo_users`

ALTER TABLE `humo_users`
MODIFY `user_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

## --

-- AUTO_INCREMENT for table `humo_user_log`

ALTER TABLE `humo_user_log`
MODIFY `log_id` mediumint(6) UNSIGNED NOT NULL AUTO_INCREMENT;

## --

-- AUTO_INCREMENT for table `humo_user_notes`

ALTER TABLE `humo_user_notes`
MODIFY `note_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

## --

-- Constraints for dumped tables

## --

-- Constraints for table `humo_events`

ALTER TABLE `humo_events`
ADD CONSTRAINT `fk_event_family` FOREIGN KEY (`relation_id`) REFERENCES `humo_families` (`fam_id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `fk_event_person` FOREIGN KEY (`person_id`) REFERENCES `humo_persons` (`pers_id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `fk_event_place` FOREIGN KEY (`place_id`) REFERENCES `humo_location` (`location_id`) ON DELETE SET NULL ON UPDATE CASCADE;

## --

-- Constraints for table `humo_relations_persons`

ALTER TABLE `humo_relations_persons`
ADD CONSTRAINT `fk_relation_person` FOREIGN KEY (`person_id`) REFERENCES `humo_persons` (`pers_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `fk_relation_person_family` FOREIGN KEY (`relation_id`) REFERENCES `humo_families` (`fam_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;