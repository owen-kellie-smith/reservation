DROP table IF EXISTS /*_*/res_unit;
CREATE TABLE /*_*/res_unit(
	res_unit_id int not null auto_increment, 
	res_unit_name varchar(100) not null,
	res_unit_name_plural varchar(100) not null,
	primary key (res_unit_id)  
);

DROP table IF EXISTS /*_*/res_resource;
CREATE TABLE /*_*/res_resource(
	res_resource_id int not null auto_increment, 
	res_resource_name varchar(100) not null,
	res_resource_group_id int not null,
  res_resource_capacity int not null,
	res_resource_unit_id int not null,
	primary key (res_resource_id)  
);


DROP table IF EXISTS /*_*/res_group;
CREATE TABLE /*_*/res_group(
	res_group_id int not null auto_increment, 
	res_group_right varchar(100) not null,
	res_group_name varchar(100) not null,
	primary key (res_group_id)  
);

DROP table IF EXISTS /*_*/res_message;
CREATE TABLE /*_*/res_message(
	res_message_id int not null auto_increment, 
	res_message_user_id int not null, 
	res_message_text varchar(400) not null,
	res_message_type varchar(50) not null,
	res_message_time datetime not null,
	primary key (res_message_id)  
);

DROP table IF EXISTS /*_*/res_booking;
CREATE TABLE /*_*/res_booking(
	res_booking_id int not null auto_increment, 
	res_booking_resource_id int not null,
  res_booking_units int not null,
	res_booking_beneficiary_id int not null,
	res_booking_start datetime not null,
	res_booking_end datetime not null,
	primary key (res_booking_id)  
);

DROP table IF EXISTS /*_*/res_log;
CREATE TABLE /*_*/res_log(
	res_log_id int not null auto_increment, 
	res_log_who varchar(200) not null,
	res_log_text varchar(400) not null,
  	res_log_when datetime not null,
	primary key (res_log_id)  
);
