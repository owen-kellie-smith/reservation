INSERT INTO 
res_unit( res_unit_name, res_unit_name_plural ) 
VALUES 
( 'core', 'cores' );

INSERT INTO
res_group( res_group_id, res_group_right, res_group_name )
values
( 1, 'blade-prophet-dev','Prophet-dev'),
( 2, 'blade-ALS','ALS'),
( 3, 'blade-production','Production')
;

INSERT INTO 
res_resource( 
res_resource_group_id, res_resource_name,  res_resource_capacity, res_resource_unit_id 
) 
values 
( 1,'bladeA', 32,1),
( 1,'bladeB', 32,1),
( 1,'bladeC', 32,1),
( 2,'bladeD', 32,1),
( 2,'bladeE', 32,1),
( 2,'bladeF', 32,1),
( 3,'bladeG', 32,1)
;

