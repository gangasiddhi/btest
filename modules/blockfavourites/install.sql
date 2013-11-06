CREATE TABLE IF NOT EXISTS `PREFIX_favlist` (
  `id_favlist` int(10) unsigned NOT NULL auto_increment,
  `id_customer` int(10) unsigned NOT NULL,
  `token` varchar(64) character set utf8 NOT NULL,
  `name` varchar(64) character set utf8 NOT NULL,
  `counter` int(10) unsigned NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY  (`id_favlist`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `PREFIX_favlist_product` (
  `id_favlist_product` int(10) NOT NULL auto_increment,
  `id_favlist` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `id_product_attribute` int(10) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `priority` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_favlist_product`)
) ENGINE=ENGINE_TYPE  DEFAULT CHARSET=utf8;
