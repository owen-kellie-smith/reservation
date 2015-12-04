INSERT INTO 
res_unit( res_unit_name ) 
VALUES 
( 'core' );

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
('Marco'),
('Mei'),
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
(1, 11, 1, now(), now()+1000),
(1, 11, 1, now(), now()+10000),
(1, 11, 3, now(), now()+100000),
(1, 11, 3, now(), now()+1000000),
(1, 11, 3, now(), now()+10000000)
;

INSERT INTO 
 res_log(	res_log_text, res_log_when)
VALUES
('System booked Owen into sdorps111 from now-ish to a bit later', now() );

