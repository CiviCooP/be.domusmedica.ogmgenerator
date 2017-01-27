CREATE TABLE IF NOT EXISTS `domus_inv_fin_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `financial_type_id` int(11) DEFAULT NULL,
  `is_domus_invoice` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
