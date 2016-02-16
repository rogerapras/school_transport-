<?php

class User_model extends CI_Model {

  public $first_name;
  public $last_name;
  public $username;
  public $salt;
  public $encrypted_password;
  public $type;


  public function __construct() {
    parent::__construct();
  }

  public function create() {
    $this->id = $this->db->insert_id();
    $this->username = $this->input->post('username');
    $this->first_name = $this->input->post('first_name');
    $this->last_name = $this->input->post('last_name');
    $this->type = $this->input->post('type');
    $this->encrypted_password = $this->generateHashedPassword($this->input->post('password'), TRUE);
    $this->db->insert('users', $this);
    $this->id = $this->db->insert_id();
    return isset($this->id) ? $this : NULL;
  }

  public function update() {
    $this->first_name = $this->input->input_stream('first_name');
    $this->last_name = $this->input->input_stream('last_name');
    if($this->input->input_stream('password') != NULL) {
      $this->encrypted_password = $this->generateHashedPassword($this->input->input_stream('password'), TRUE);
    }
    $this->db->where('id', $this->id);
    $this->db->update('users', $this);
  }

  public function find($id) {
    $user = $this->db->where('id', $id)->get('users')->row();
    if( $user != NULL ) { 
      $this->set($user);
    }
    return $this;
  }

  public function find_by($field, $value) {
    return $this->db->where($field, $value)->get('users')->row();
  }

  public function isAdmin() {
    return $this->type == 'admin';
  }

  public function isDriver() {
    return $this->type == 'driver';
  }

  public function isStudent() {
    return $this->type == 'student';
  }

  private function set($user) {
    $this->id = $user->id;
    $this->first_name = $user->first_name;
    $this->last_name = $user->last_name;
    $this->username = $user->username;
    $this->salt = $user->salt;
    $this->encrypted_password = $user->encrypted_password;
    $this->type = $user->type;
  }

  public function asJson() {
    return array(
      'id' => $this->id,
      'first_name' => $this->first_name,
      'last_name' => $this->last_name,
      'username' => $this->username,
      'type' => $this->type
    );
  }


 public function generateHashedPassword($password, $isNew) {
   $options = [
     'cost' => 11,
     'salt' => $this->getSalt($isNew),
   ];
   return password_hash($password, PASSWORD_BCRYPT, $options);
 }

  private function getSalt($isNew) {
    if($isNew == FALSE) {
      return $this->salt;
    } else {
      $length = 22;
      $this->salt = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
      return $this->salt;
    }
  }


  public function authenticate($username, $password) {
    $user = $this->db->where('username', $username)->get('users')->row();
    if( $user != NULL ) {
      $this->set($user);
      if($this->generateHashedPassword($password, FALSE) == $user->encrypted_password) {
        return $this;
      } else {
        return NULL;
      }
    }
  }

}
