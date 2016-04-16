<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = 'students/show_404';
$route['translate_uri_dashes'] = FALSE;

$route['students']['POST'] = 'students/create'; # POST /students
$route['students/(:num)']['GET'] = 'students/show/$1'; # GET /students/:id 
$route['students/(:num)']['PUT'] = 'students/update/$1'; # PUT /students/:id
$route['students/(:num)']['DELETE'] = 'students/destroy/$1'; # DELETE /students/:id

$route['login']['POST'] = 'sessions/login';
$route['register']['POST'] = 'sessions/sign_up';
$route['drivers']['POST'] = 'sessions/sign_up';
$route['students']['POST'] = 'sessions/sign_up';

$route['user']['GET'] = 'users/me';
$route['user']['PUT'] = 'users/update';
$route['users/(:num)']['PUT'] = 'users/update/$1';
$route['users/(:num)']['DELETE'] = 'users/destroy/$1';
$route['users']['GET'] = 'users/index';
$route['users/(driver)']['GET'] = 'users/index/$1';
$route['users/(student)']['GET'] = 'users/index/$1';

# $route['routes']['GET'] = 'routes';
$route['routes']['POST'] = 'routes/create';
$route['routes/(:num)/assign/(:num)']['POST'] = 'routes/assign/$1/$2';
$route['routes/(:num)/assign_student/(:num)']['POST'] = 'routes/assignStudent/$1/$2';
$route['routes/(:num)/unassign']['DELETE'] = 'routes/unassign/$1';
$route['routes/(:num)']['PUT'] = 'routes/update/$1';
$route['routes/(:num)']['DELETE'] = 'routes/destroy/$1';
$route['routes/(:num)/students']['GET'] = 'routes/students/$1';
$route['routes/(:num)']['GET'] = 'routes/show/$1';

$route['timings']['POST'] = 'timings/create';
$route['timings/(:num)/assign/(:num)']['POST'] = 'timings/assign/$1/$2';
$route['timings/(:num)/assign_student/(:num)']['POST'] = 'timings/assignStudent/$1/$2';
$route['timings/(:num)/unassign']['DELETE'] = 'timings/unassign/$1';
$route['timings/(:num)']['PUT'] = 'timings/update/$1';
$route['timings/(:num)']['DELETE'] = 'timings/destroy/$1';
$route['timings/(:num)/students']['GET'] = 'timings/students/$1';
$route['timings/(:num)']['GET'] = 'timings/show/$1';

$routes['locations/set']['POST'] = 'locations/set';
$routes['locations/get']['GET'] = 'locations/get';
