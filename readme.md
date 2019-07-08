### Summary of setup  ###

Use laravel 5.1 LTS verion
*For installation use laravel 5.1 official documentation 
   https://laravel.com/docs/5.1

*Here we are install by Composer


###  Configration   ###
Project are working on virtual host.
Basic URL or project

* http://api.scholarspace.org/v1  (for api)

* http://staging.scholarspace.org  (for frontend)

* http://admin.scholarspace.org   (for admin/backend)

###  Dependencies   ###
* Apache
* PHP 5.5.9
* MySql
* Laravel 5.1(LTS)

###  Database configuration  ###
HOST=127.0.0.1

DATABASE=scholar

USERNAME=root

PASSWORD=hariom123


###  Api End-points ###

---------------------------------------------------------------------------------
###  Unauthenticated Group  ###
---------------------------------------------------------------------------------


###  Register Api  ###


To genrate otp:       http://api.scholarspace.org/v1/user.otp.generate 

Request      |   POST

Parameters   |  mobile


Response   |  success  or error


-----------------------------------------------
-----------------------------------------------

To validate otp:         http://api.scholarspace.org/v1/user.otp.validate


Request         |   POST


Parameters      |   mobile,  otp

Response       | success  or error

---------------------------------------------------
---------------------------------------------------

To register:                http://api.scholarspace.org/v1/auth.user.register

Request         |    POST

Parameters      |    usertype_id,     mobile,      email,     password,       confirm_password

Response       |   success   or error


---------------------------------------------------------------------------------
###  Authenticated Group ###
---------------------------------------------------------------------------------


###  userprofile Api  ###

To show user profile:       http://api.scholarspace.org/v1/auth.user.profile?token=$token

Request        		 |      GET

Response       		 |    	success  or error

success parameters	 |		id,	username, mobile, email, title, first_name, last_name, dob, job_title, department



### user change password Api ###

To change password: 		http://api.scholarspace.org/v1/auth.user.changepassword?token=$token

Request        		 |    POST

Parameters       	 |    old_password, password, confirm_password

Response       		 |   success   or error



### user profile update Api ###

To update profile: 		http://api.scholarspace.org/v1/user.profile.update?token=$token

Request        		 |    POST

Parameters       	 |    title, first_name, middle_name, last_name, dob, job_title, department

Response       		 |   success   or error


### user profile pic upload Api ###

To update profile pic: 		http://api.scholarspace.org/v1/user.profile.pic?token=$token

Request        		 |    POST

Parameters       	 |    pic (file)

Response       		 |   success   or error



### Cart Api ###

To get Cart with item: 		http://api.scholarspace.org/v1/user.cart?token=$token

Request        		 |      GET

Response       		 |    	success  or error

success parameters	 |		cartitems [ id, title, price, expires_time, type, created_at ], total, taxper, tax, finalprice



### To add to cart Api ###

To add item to cart: 		http://api.scholarspace.org/v1/user.profile.pic?token=$token

Request        		 |    POST

Parameters       	 |    type , id ( primary key of item for perticular item )

Response       		 |   success   or error



### To remove item from cart Api ###

To remove item from cart: 		http://api.scholarspace.org/v1/user.cart.removeitem?token=$token

Request        		 |    POST

Parameters       	 |    id ( cartitem id )

Response       		 |   success   or error
"# apilivetutoring_repo1" 
