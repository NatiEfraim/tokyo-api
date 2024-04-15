# TODO List for Tokyo Project

## Features

-   [ ] Implement inventories table 
-   [ ] Add CRUD functionality for posts/gets/put/delete - for inventories,departments,users,distributions table.
-   [ ] UserController - user can search user by personal_number or by name.
-   [ ] ExportController - user can export records of (users,distributions,inventories) table on email or download as xlsx file.
-   [ ] DistributionController user can search records based status fileds,departments (associated department_id),item_type (assoscited inventory_id),persnol_numer (associated created_by)       
-   [ ] ExportController - user can export records of 
(users,distributions - [user can filter based on updated_at,created_at,personal_number,name,status,sku],inventories [user can filter based on sku]) 
table on email or download as xlsx file.





## Remaining Tasks

-   [ ] Need too add fileds by department or by persnol_numer or by data or by status fileds on the exportSitstributions & sendDistributionsByEmail.
-   [ ] Need to added by SPATIE permissions & roles for users.
-   [ ] Added user the user send me personal_number and rule -> by outsource api I get all the fileds of the user like name employee_type and so on.

## Bugs



## Improvements



## ERD table

-   [] depatments table
-   has one to many realtion with disrubutions table - each record of department table may have one or many distributions records
-   [] employee_types table
-   has for now 4 rows code_emp_type - 1 -> 'keva', 2 ->'miluim', 3 -> 'sadr', 4 --> 'civilian_employee'
-   has one to many relation with the users table - each record of employee_types table may have one or many users records
-   [] users table
-   has one to many relation with the distributions table - each record of users table may have one or many distributions records
-   has one to many relation with employee_types table - belongTo by the fileds emp_type_id
-   [] inventories table
-   has one to many relation with the distributions table - each record of inventories table may have one or many distributions records
-   [] distributions table
-   has one to many relation with the depratments table - belongTo by the fileds department_id
-   has one to many relation with the users table - belongTo by the fileds created_by
-   has one to many relation with the inventories table - belongTo by the fileds inventory_id



## Ideas

## Documentation
