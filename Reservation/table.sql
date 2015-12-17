DROP table IF EXISTS res_unit;
CREATE TABLE res_unit(
	res_unit_id int not null auto_increment, 
	res_unit_name varchar(100) not null,
	res_unit_name_plural varchar(100) not null,
	primary key (res_unit_id)  
);

DROP table IF EXISTS res_resource;
CREATE TABLE res_resource(
	res_resource_id int not null auto_increment, 
	res_resource_name varchar(100) not null,
	res_resource_group_id int not null,
  res_resource_capacity int not null,
	res_resource_unit_id int not null,
	primary key (res_resource_id)  
);


DROP table IF EXISTS res_group;
CREATE TABLE res_group(
	res_group_id int not null auto_increment, 
	res_group_right varchar(100) not null,
	res_group_name varchar(100) not null,
	primary key (res_group_id)  
);

#DROP table IF EXISTS res_beneficiary;
#CREATE TABLE res_beneficiary(
#	res_beneficiary_id int not null auto_increment, 
#	res_beneficiary_name varchar(100) not null,
#	primary key (res_beneficiary_id)  
#);

#DROP table IF EXISTS res_membership;
#CREATE TABLE res_membership(
#	res_membership_beneficiary_id int not null, 
#	res_membership_group_id  int not null,
#	primary key (res_membership_group_id, res_membership_beneficiary_id)  
#);

DROP table IF EXISTS res_booking;
CREATE TABLE res_booking(
	res_booking_id int not null auto_increment, 
	res_booking_resource_id int not null,
  res_booking_units int not null,
	res_booking_beneficiary_id int not null,
	res_booking_start datetime not null,
	res_booking_end datetime not null,
	primary key (res_booking_id)  
);

DROP table IF EXISTS res_log;
CREATE TABLE res_log(
	res_log_id int not null auto_increment, 
	res_log_who varchar(200) not null,
	res_log_text varchar(400) not null,
  	res_log_when datetime not null,
	primary key (res_log_id)  
);
