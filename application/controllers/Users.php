<?php

class Users extends CI_Controller {

  public function me() {
    $token = $this->input->get_request_header('X-Api-Key', TRUE);
    $user = $this->device->find_user($token);
    if($user == NULL) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(array('code' => 401, 'message' => 'Unauthorized: You are not logged in.')));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode($user->asJson()));
    }
  }
}
