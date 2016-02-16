<?php
  class Pages extends CI_Controller {
    public function view($page = 'home') {
      $this->load->database();
      $query = $this->db->query('SELECT id, first_name, last_name FROM users');
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(201)
        ->set_output(json_encode($query->result_array()));
    }

    public function index() {
      print_r($this->router->routes);
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(array('action' => 'index')));
    }

    public function show($num) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(array('action' => 'show', 'id' => $num)));
    }
  }
?>
