create table if not exists partners
(
    id           int auto_increment
    primary key,
    partner_name varchar(255) not null
    )
    collate = utf8mb4_unicode_ci;

create table if not exists partner_sales
(
    id             int auto_increment
    primary key,
    partner_id_id  int          not null,
    item_date_time datetime     not null,
    client_name    varchar(255) not null,
    product_name   varchar(255) not null,
    quantity       int          not null,
    piece_price    double       not null,
    delivery_type  varchar(255) not null,
    delivery_city  varchar(255) not null,
    delivery_price double       not null,
    total_price    double       not null,
    constraint FK_78BDDC216C783232
    foreign key (partner_id_id) references partners (id)
    )
    collate = utf8mb4_unicode_ci;

create index IDX_78BDDC216C783232
    on partner_sales (partner_id_id);
