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
    return array(
      'id' => $this->id,
      'token' => $this->token,
      'device_id' => $this->device_id,
      'user' => array(
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'username' => $user->username,
        'type' => $user->type
      )
    );
  }


  private function generateToken() {
    $length = 40;
    $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    return $randomString;
  }

}
