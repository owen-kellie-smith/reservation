INSERT INTO 
res_unit( res_unit_name, res_unit_name_plural ) 
VALUES 
( 'core', 'cores' );

INSERT INTO 
res_group( res_group_name ) 
VALUES 
('all'),
('model-dev-CPM'),
('model-prod'),
('model-dev-ALS') ;

INSERT INTO 
res_resource( 
res_resource_group_id, res_resource_name,  res_resource_capacity, res_resource_unit_id 
) 
values 
( 1,'sdorps111', 32,1),
( 1,'sdorps143', 32,1),
( 1,'sdorps144', 32,1)
;

INSERT INTO 
res_beneficiary(	res_beneficiary_name ) 
VALUES 
('Owen'),
('Mei'),
('Marco'),
('Will S' )
;

INSERT INTO 
res_membership(	res_membership_beneficiary_id, 	res_membership_group_id)
VALUES 
(1,1),
(2,1),
(3,1),
(4,1),
(1,2),
(3,2) 
;

INSERT INTO 
res_booking(
	res_booking_resource_id, 
  res_booking_units, 
	res_booking_beneficiary_id,
	res_booking_start, 
	res_booking_end
)
VALUES
(2, 12, 1, now()-10000, now()+1000),
(2, 13, 1, now(), now()+1000),
(1, 14, 1, now(), now()+10000),
(2, 15, 3, now(), now()+100000),
(1, 16, 3, now(), now()+1000000),
(1, 17, 3, now(), now()+10000000),
(1, 18, 3, now()+1000000, now()+20000000)
;

INSERT INTO 
 res_log(	res_log_text, res_log_when)
VALUES
('System booked Owen into sdorps111 from now-ish to a bit later', now() );

