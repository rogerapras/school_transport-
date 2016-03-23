<?php

class Routes extends CI_Controller {

  public function authenticate() {
    $token = $this->input->get_request_header('X-Api-Key', TRUE);
    $this->currentUser = $this->device->find_user($token);
    $this->output
      ->set_content_type('application/json');
    if($this->currentUser == NULL || $this->currentUser->isStudent()) {
      $this->output
        ->set_status_header(401)
        ->set_output(json_encode(array('code' => 401, 'message' => 'Unauthorized: You are not logged in.')));

      $this->output->_display();
      exit;

    }
  }

  public function authenticateAdmin() {
    $token = $this->input->get_request_header('X-Api-Key', TRUE);
    $this->currentUser = $this->device->find_user($token);
    $this->output
      ->set_content_type('application/json');
    if($this->currentUser == NULL || !$this->currentUser->isAdmin()) {
      $this->output
        ->set_status_header(401)
        ->set_output(json_encode(array('code' => 401, 'message' => 'Unauthorized: You are not logged in.')));

      $this->output->_display();
      exit;

    }
  }

  public function __construct() {
    parent::__construct();
  }

  public function update($route_id) {
    $this->authenticate();
    $route = $this->route->find($route_id);
    $route->update();
    $this->output
      ->set_status_header(200)
      ->set_output(json_encode($route->asJson()));
  }

  public function index() {
    $this->authenticateAdmin();

    $all = $this->input->get('unassigned');
    $routes = array();
    if(!isset($all)) {
      $query = $this->db->get('routes');
    } else {
      $this->db->select('routes.*');
      $query = $this->db->join('driver_routes', 'driver_routes.route_id = routes.id', 'LEFT OUTER')
        ->where('driver_routes.id', NULL)->get('routes');
    }
    foreach($query->result() as $route_row) {
      $route = Route_model::initialize($route_row);
      array_push($routes, $route->asJson());
    }

    $this->output
      ->set_status_header(200)
      ->set_output(json_encode($routes));
  }

  public function show($route_id) {
    $this->authenticateAdmin();

    $route = $this->route->find($route_id);
    $this->output
      ->set_status_header(200)
      ->set_output(json_encode($route->asJson()));

  }

  public function assignStudent($route_id, $student_id) {
    $this->authenticateAdmin();

    $route = $this->route->find($route_id);
    $student = $this->user->find($student_id);

    $route->assignStudent($student);

    $this->output
      ->set_status_header(200)
      ->set_output(json_encode($route->students()));
  }

  public function students($route_id) {
    $this->authenticateAdmin();

    $route = $this->route->find($route_id);
    $this->output
      ->set_status_header(200)
      ->set_output(json_encode($route->students()));
  }

  public function assign($route_id, $driver_id) {
    $this->authenticateAdmin();

    $route = $this->route->find($route_id);
    $driver = $this->user->find($driver_id);
    if($driver->hasRoute()) {
      $this->output
        ->set_status_header(422)
        ->set_output(json_encode(array('code' => 422, 'message' => 'Driver already assigned to an other route')));
    } else {
      $route->assignDriver($driver);
      $this->output
        ->set_status_header(201)
        ->set_output(json_encode($route->asJson()));
    }
  }

  public function unassign($route_id) {
    $this->authenticateAdmin();

    $route = $this->route->find($route_id);
    $route->unassignDriver();
    $this->output
      ->set_status_header(204);
  }

  public function destroy($route_id) {
    $this->authenticateAdmin();

    $route = $this->route->find($route_id);
    if($route == NULL) {
      $this->output
        ->set_status_header(422)
        ->set_output(json_encode(array('code' => 422, 'message' => 'Route not found with given id')));
    } else {
      $route->destroy();
      $this->output
        ->set_status_header(204);
    }
  }


  public function create() {
    $this->authenticateAdmin();

    $this->form_validation->set_rules('name', 'Name', 'required');
    $this->form_validation->set_rules('bus_number', 'Bus number', 'required');
    $this->form_validation->set_rules('arrival_time', 'Arrival', 'required');
    $this->form_validation->set_rules('departure_time', 'Departure', 'required');
    $this->form_validation->set_rules('start_latitude', 'Start latitude', 'required|numeric');
    $this->form_validation->set_rules('end_latitude', 'End Latitude', 'required|numeric');
    $this->form_validation->set_rules('start_longitude', 'Start longitude', 'required|numeric');
    $this->form_validation->set_rules('end_longitude', 'End longitude', 'required|numeric');

    if ($this->form_validation->run() == FALSE) {
      $this->output
        ->set_status_header(422)
        ->set_output(json_encode(array('code' => 422, 'message' => $this->form_validation->error_array())))
        ->_display();
      exit;

    }

    
    $route = $this->route->create();
    $this->output
      ->set_status_header(201)
      ->set_output(json_encode($route->asJson()));
  }
}

?>
