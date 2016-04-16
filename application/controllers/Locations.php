<?php

class Locations extends CI_Controller {

  public $currentUser = NULL;

  public function __construct() {
    parent::__construct();
  }

  private function authenticate() {
    $token = $this->input->get_request_header('X-Api-Key', TRUE);
    $this->currentUser = $this->device->find_user($token);
    if($this->currentUser == NULL) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(401)
        ->set_output(json_encode(array('code' => 401, 'message' => 'Unauthorized: You are not logged in.')));

      $this->output->_display();
      exit;
    }
  }

  public function set() {
    $this->authenticate();

    $location = $this->db->where('user_id', $this->currentUser->id)->get('locations')->row();

    if( $location != NULL ) {
      $this->db->set($this->input->post());
      $this->db->update('locations');
    } else {
      $this->db->set('user_id', $this->currentUser->id);
      $this->db->set($this->input->post());
      $this->db->insert('locations');
    }

    $location = $this->db->where('user_id', $this->currentUser->id)->get('locations')->row();

    $this->output
      ->set_status_header(200)
      ->set_content_type('appliation/json')
      ->set_output(json_encode($location));
  }

  public function get() {
    $this->authenticate();

    $user_id = $this->input->get('user_id');
    if($user_id == NULL) {
      $user_id = $this->currentUser->id;
    }
    $timing_id = $this->input->get('timing_id');
    if(isset($timing_id)) {
      $row = $this->db->where('timing_id', $timing_id)->get('driver_timings')->row();
      if($row != NULL) {
        $user_id = $row->driver_id;
      }
    }

    $location = $this->db->where('user_id', $user_id)->get('locations')->row();
    if($location == NULL) {
      $this->output
        ->set_status_header(404)
        ->set_content_type('appliation/json')
        ->set_output(json_encode(array('code' => 404, 'message' => 'Location not found with given user_id')));
    } else {
      $this->output
        ->set_status_header(200)
        ->set_content_type('appliation/json')
        ->set_output(json_encode($location));
    }
  }

}
