create table events_images (
    id int primary key auto_increment,
    event_id int not null,
    image_url varchar(255) not null,
    created_at datetime default current_timestamp,
    updated_at datetime default current_timestamp on update current_timestamp,
    foreign key (event_id) references events(event_id) on delete cascade
);

