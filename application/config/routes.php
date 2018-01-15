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
|   example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|   http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|   $route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|   $route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|   $route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples: my-controller/index -> my_controller/index
|       my-controller/my-method -> my_controller/my_method
*/

$route['default_controller'] = 'clients';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['admin']  = "admin/dashboard";
// Misc controller rewrites
$route['admin/access_denied']  = "admin/misc/access_denied";
$route['admin/not_found']  = "admin/misc/not_found";

// Staff rewrites
$route['admin/profile']  = "admin/staff/profile";
$route['admin/profile/(:num)']  = "admin/staff/profile/$1";
$route['admin/tasks/view/(:any)']  = "admin/tasks/index/$1";

// Items search rewrite
$route['admin/items/search'] = 'admin/invoice_items/search';

/* Clients links and routes */
// // In case if client access directly to url without the arguments redirect to clients url
$route['/']  = "clients";
$route['viewinvoice']  = "clients/viewinvoice";
$route['viewinvoice/(:num)/(:any)']  = "clients/viewinvoice/$1/$2";

$route['viewestimate/(:num)/(:any)']  = "clients/viewestimate/$1/$2";
$route['viewestimate']  = "clients/viewestimate";

$route['viewproposal/(:num)/(:any)']  = "clients/viewproposal/$1/$2";

$route['survey/(:num)/(:any)']  = "clients/survey/$1/$2";
$route['knowledge_base']  = "clients/knowledge_base";
$route['knowledge_base/(:any)']  = "clients/knowledge_base/$1";

$route['knowledge-base']  = "clients/knowledge_base";
$route['knowledge-base/(:any)']  = "clients/knowledge_base/$1";

if(file_exists(APPPATH.'config/my_routes.php')){
    include_once(APPPATH.'config/my_routes.php');
}
