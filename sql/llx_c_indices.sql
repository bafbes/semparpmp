CREATE TABLE llx_c_indices (
    rowid    integer AUTO_INCREMENT PRIMARY KEY,
    label    varchar(255) NOT NULL,
    content     VARCHAR (255) not NULL,
    fk_pays int(11) NOT NULL ,
    active   tinyint DEFAULT 1 NOT NULL,
    entity   integer  DEFAULT 1 NOT NULL,
    mois    integer not null CHECK(0<mois<=12) ,
    annee   integer not null CHECK(1900<annee<2100),
    unique key (label,mois,annee)
)ENGINE=innodb;