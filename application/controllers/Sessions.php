<?php

class Sessions extends CI_Controller {

  public $currentUser = NULL;

  public function __construct() {
    parent::__construct();
    $token = $this->input->get_request_header('X-Api-Key', TRUE);
    $this->currentUser = $this->device->find_user($token);
    $this->output
      ->set_content_type('application/json');
  }

  public function login() {
    $username = $this->input->post('username');
    $password = $this->input->post('password');
    $user = $this->user->authenticate($username, $password);
    if($user != NULL) {
      $this->output
        ->set_status_header(200)
        ->set_output(json_encode($this->createSession($user)->asJson()));

    } else {
      $this->output
        ->set_status_header(404)
        ->set_output(json_encode(array('code' => 404, 'message' => 'Unauthorized: username/password does not match')));
    }
  }

  public function sign_up() {
    if($this->currentUser == NULL || !$this->currentUser->isAdmin()) {
      $this->output
        ->set_status_header(401)
        ->set_output(json_encode(array('code' => 401, 'message' => 'Unauthorized: Please login to add drivers/students')));
      return;
    }
    $this->form_validation->set_rules(
      'username', 'Username',
      'required|min_length[5]|max_length[12]|is_unique[users.username]',
      array(
        'required'      => 'You have not provided %s.',
        'is_unique'     => 'This %s already exists.'
      )
    );
    $this->form_validation->set_rules('password', 'Password', 'required');
    $this->form_validation->set_rules('first_name', 'First name', 'required');
    $this->form_validation->set_rules('last_name', 'Last name', 'required');
    $this->form_validation->set_rules('passconf', 'Password Confirmation', 'required|matches[password]');
    $this->form_validation->set_rules('type', 'Type', 'required|in_list[student,driver]');

    if( $this->form_validation->run() == FALSE ) {
      $this->output
        ->set_status_header(422)
        ->set_output(json_encode(array('code' => 422, 'message' => $this->form_validation->error_array())));
    } else  {
      $user = $this->user->create();
      if($user != NULL) {
        $this->output
          ->set_status_header(201)
          ->set_output(json_encode($this->createSession($user)->asJson()));
      } else {
        $this->output
          ->set_status_header(422)
          ->set_output(json_encode(array('code' => 422, 'message' => 'Something went wrong.')));
      }
    }

  }

  private function createSession($currentUser) {
    return $this->device->create($currentUser);
  }

}

?>
