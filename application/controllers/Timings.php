<?php

class Timings extends CI_Controller {

  public function authenticate() {
    $token = $this->input->get_request_header('X-Api-Key', TRUE);
    $this->currentUser = $this->device->find_user($token);
    $this->output
      ->set_content_type('application/json');
    if($this->currentUser == NULL) {
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

  public function index() {
    $this->authenticate();

    $result = $this->db->where('route_id', $this->input->get('route_id'))->get('timings')->result();
    $timings = array();
    foreach($result as $row) {
      $timings[] = Timing_model::initialize($row)->asJson();
    }

    $this->output
      ->set_status_header(200)
      ->set_output(json_encode($timings));
  }


  public function show($timing_id) {
    $this->authenticate();

    $row = $this->db->where('id', $timing_id)->get('timings')->row();
    if($row == NULL) {
      $this->output
        ->set_status_header(404)
        ->set_output(json_encode(array('code' => 404, 'message' => 'Timing not found with given ID')));
      $this->output->_display();
      exit;
    }
    $timing = Timing_model::initialize($row);

    $result = $this->db
      ->join('student_timings', 'student_timings.student_id = users.id', 'INNER')
      ->where('student_timings.timing_id', $timing->id)->get('users')->result();

    $students = array();
    foreach($result as $row) {
      $students[] = User_model::initialize($row)->asJsonStudent();
    }


    $output = $timing->asJson();
    $output['students'] = $students;

    $this->output
      ->set_status_header(200)
      ->set_output(json_encode($output));
  }


  public function update($timing_id) {
    $this->authenticate();
    $timing = $this->timing->find($timing_id);
    $timing->update();

    $this->output
      ->set_status_header(200)
      ->set_output(json_encode($timing->asJson()));
  }

}
