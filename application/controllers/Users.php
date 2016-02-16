<?php

class Users extends CI_Controller {

  public $currentUser = NULL;

  public function __construct() {
    parent::__construct();
    $token = $this->input->get_request_header('X-Api-Key', TRUE);
    $this->currentUser = $this->device->find_user($token);
    if($this->currentUser == NULL) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(array('code' => 401, 'message' => 'Unauthorized: You are not logged in.')));
    }
  }

  public function index($type = 'driver') {
    $this->db->where('type', $type);
    $result = $this->db->select("id, first_name, last_name, username, type")->get('users')->result();
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode(array('users' => $result)));
  }

  public function me() {
    if($this->currentUser != NULL) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode($this->currentUser->asJson()));
    }
  }

  public function update($user_id = NULL) {
    if($this->currentUser != NULL) {
      if(isset($user_id) && $this->currentUser->isAdmin()) {
        $user = $this->user->find($user_id);
        $user->update();
        $this->output
          ->set_content_type('application/json')
          ->set_status_header(200)
          ->set_output(json_encode($this->user->asJson()));
      } else {
        $this->currentUser->update();
        $this->output
          ->set_content_type('application/json')
          ->set_status_header(200)
          ->set_output(json_encode($this->currentUser->asJson()));
      }
    }
  }
}
