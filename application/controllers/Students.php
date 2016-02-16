<?php

class Students extends CI_Controller {

  public function index() {
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode(array('controller' => 'students', 'action' => 'index')));

  }

  public function create() {
    # $this->load->model('user_model');
    # var_dump($this->user->create());
    # $user = $this->user->authenticate('rubyonrails3', 'password');
    # $user = $this->user->create();
    $this->form_validation->set_rules(
      'username', 'Username',
      'required|min_length[5]|max_length[12]|is_unique[users.username]',
      array(
        'required'      => 'You have not provided %s.',
        'is_unique'     => 'This %s already exists.'
      )
    );
    $this->form_validation->set_rules('password', 'Password', 'required');
    $this->form_validation->set_rules('passconf', 'Password Confirmation', 'required|matches[password]');
    $this->form_validation->run();
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($this->form_validation->error_array()));
  }

  public function update($id) {
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode(array('controller' => 'students', 'action' => 'update', 'id' => $id)));
  }

  public function show($id) {
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode(array('controller' => 'students', 'action' => 'show', 'id' => $id)));
  }

  public function destroy($id) {
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode(array('controller' => 'students', 'action' => 'destroy', 'id' => $id)));
  }

  public function show_404() {
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode(array('controller' => 'students', 'action' => 'not_found')));
  }
}

?>
