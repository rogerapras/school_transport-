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
        ->set_status_header(401)
        ->set_output(json_encode(array('code' => 401, 'message' => 'Unauthorized: You are not logged in.')));

      $this->output->_display();
      exit;
    }
  }

  public function index($type = 'driver') {
    $this->db->where('type', $type);
    $users = array();
    $result = $this->db->get('users')->result();
    foreach($result as $user) {
      array_push($users, User_model::initialize($user)->asJson());
    }
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode(array('users' => $users)));
  }

  public function me() {
    if($this->currentUser != NULL) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode($this->currentUser->routeAsJson()));
    }
  }


  private function updateTimings($user) {
    $timing_ids = $this->input->input_stream('timing_ids');
    foreach($timing_ids as $timing_id) {
      $timing = $this->timing->find($timing_id);
      if($timing != NULL) {
        if($user->type == 'driver') {
          $timing->assignDriver($user);
        } else {
          $timing->assignStudent($user);
        }
      }
    }
  }

  public function update($user_id = NULL) {
    if($this->currentUser != NULL) {
      if(isset($user_id) && $this->currentUser->isAdmin()) {
        $user = $this->user->find($user_id);
        $user->update();
        $this->updateTimings($user);

        $this->output
          ->set_content_type('application/json')
          ->set_status_header(200)
          ->set_output(json_encode($this->user->asJson()));
      } else {
        $this->currentUser->update();
        $this->updateTimings($this->currentUser);
        $this->output
          ->set_content_type('application/json')
          ->set_status_header(200)
          ->set_output(json_encode($this->currentUser->asJson()));
      }
    }
  }

  public function destroy($user_id) {
    if($this->currentUser->isAdmin()) {
      $user = $this->user->find($user_id);
      if($user == NULL) {
        $this->output
          ->set_status_header(404)
          ->set_output(json_encode(array('code' => 404, 'message' => 'User not found with Given ID')));
        $this->output->_display();
        exit;
      }
      if(!$user->isAdmin()) {
        $user->destroy();
        $this->output
          ->set_content_type('application/json')
          ->set_status_header(204);
      } else {
        $this->output
          ->set_content_type('application/json')
          ->set_status_header(403)
          ->set_output(json_encode(array('code' => 403, 'message' => 'Forbidden: Not allowed to delete User')));
      }
    }
  }
}
