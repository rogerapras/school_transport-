<?php

class Device_model extends CI_Model {

  public $id;
  public $device_id;
  public $token;
  public $user_id;


  public function __construct() {
    parent::__construct();
  }

  public function create($user) {
    $this->user_id = $user->id;
    $this->token = $this->generateToken();
    $this->device_id = $this->input->post('device_id');
    $this->db->insert('devices', $this);
    $this->id = $this->db->insert_id();
    return $this;
  }

  public function find_user( $token ) {
    $device = $this->db->where('token', $token)->get('devices')->row();
    if($device != NULL) {
      return $this->user->find($device->user_id);
    } else {
      return NULL;
    }
  }

  public function asJson() {
    $user = $this->db->where('id', $this->user_id)->get('users')->row();
    $route = NULL;
    if(isset($user) && $user->type == 'driver') {
      $route = $this->db->select('routes.*')
        ->join('driver_routes', 'driver_routes.route_id = routes.id', 'left')
        ->where('driver_routes.user_id', $user->id)->get('routes')->row();
    } else if( isset($user) && $user->type == 'student') {
      $route = $this->db->select('routes.*')
        ->join('route_students', 'route_students.route_id = routes.id', 'left')
        ->where('route_students.student_id', $user->id)->get('routes')->row();
    }
    return array(
      'id' => $this->id,
      'token' => $this->token,
      'device_id' => $this->device_id,
      'user' => $user,
      'route' => $route
    );
  }


  private function generateToken() {
    $length = 40;
    $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    return $randomString;
  }

}
