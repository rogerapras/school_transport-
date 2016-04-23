<?php

class User_model extends CI_Model {

  public $id;
  public $first_name;
  public $last_name;
  public $username;
  public $salt;
  public $password;
  public $encrypted_password;
  public $type;
  public $phone;
  public $allowed;
  public $attending;
  public $token;
  public $roll_number;
  public $latitude;
  public $longitude;


  public function __construct() {
    parent::__construct();
  }

  public static function initialize($user_row) {
    $user = new self();
    $user->setObject($user_row);
    return $user;
  }


  public function setObject( $user_row) {

    foreach($user_row as $key => $value) {
      if( property_exists( $this, $key ) ) {
        $this->$key = $value;
      }
    }
  }


  public function hasRoute() {
    return !!$this->db->where('user_id', $this->id)->get('driver_routes')->row();
  }


  public function create() {
    $this->id = $this->db->insert_id();
    $parameters = $this->input->post();
    foreach($parameters as $key => $value) {
      if( property_exists( $this, $key ) ) {
        $this->$key = $value;
      }
    }
    $this->encrypted_password = $this->generateHashedPassword($this->input->post('password'), TRUE);
    $this->db->insert('users', $this);
    $this->id = $this->db->insert_id();
    return isset($this->id) ? $this : NULL;
  }

  public function update() {
    $parameters = $this->input->input_stream();
    foreach($parameters as $key => $value) {
      if( property_exists( $this, $key ) ) {
        $this->$key = $value;
      }
    }
    if($this->input->input_stream('password') != NULL) {
      $this->encrypted_password = $this->generateHashedPassword($this->input->input_stream('password'), TRUE);
    }
    $this->db->where('id', $this->id);
    $this->db->update('users', $this);
    $this->notifyDriver();
    $this->notifyStudent();
    return $this->db->affected_rows();
  }

  public function destroy() {
    $this->db->where('id', $this->id);
    $this->db->delete('users');
    $affected_rows = $this->db->affected_rows();
    $this->db->where('driver_id', $this->id)->delete('driver_timings');
    $this->db->where('student_id', $this->id)->delete('student_timings');
    $this->db->where('user_id', $this->id)->delete('locations');
    return $affected_rows;
  }

  public function find($id) {
    $user = $this->db->where('id', $id)->get('users')->row();
    if( $user != NULL ) { 
      $this->setObject($user);
    } else {
      return NULL;
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
    $this->setObject( $user );
  }


  public function driverRoutes() {
    $result = $this->db->select('routes.*')
      ->join('timings', 'timings.route_id = routes.id', 'INNER')
      ->join('driver_timings', 'driver_timings.timing_id = timings.id', 'INNER')
      ->where('driver_timings.driver_id', $this->id)->get('routes')->result();
    $routes = array();
    foreach($result as $row) {
      $routes[] = Route_model::initialize($row)->asJson();
    }
    return $routes;
  }

  public function studentRoute() {
    $row = $this->db->select('routes.*')
      ->join('timings', 'timings.route_id = routes.id', 'INNER')
      ->join('student_timings', 'student_timings.timing_id = timings.id', 'INNER')
      ->where('student_timings.student_id', $this->id)->get('routes')->row();
    if(isset($row)) {
      return [Route_model::initialize($row)->asJson($this)];
    } else {
      return array();
    }
  }


  public function routeAsJson() {
    $json = $this->asJson();
    if($this->type == 'driver') {
      $json['routes'] = $this->driverRoutes();
    } elseif ($this->type == 'student') {
      $json['routes'] = $this->studentRoute();
    }
    return $json;
  }

  public function asJson() {


    $json = array(
      'id' => $this->id,
      'first_name' => $this->first_name,
      'last_name' => $this->last_name,
      'username' => $this->username,
      'type' => $this->type,
      'phone' => $this->phone,
      'token' => $this->token,
      'password' => $this->password,
      'latitude' => $this->latitude,
      'longitude' => $this->longitude
    );

    if( $this->type == 'student' ) {
      $json['attending'] = $this->attending;
      $json['allowed'] = $this->allowed;
      $json['roll_number'] = $this->roll_number;
    }
    return $json;
  }

  public function asJsonStudent() {


    $json = array(
      'id' => $this->id,
      'first_name' => $this->first_name,
      'last_name' => $this->last_name,
      'username' => $this->username,
      'type' => $this->type,
      'phone' => $this->phone,
      'token' => $this->token,
      'password' => $this->password,
      'latitude' => $this->latitude,
      'longitude' => $this->longitude
    );

    if( $this->type == 'student' ) {
      $json['attending'] = $this->attending;
      $json['allowed'] = $this->allowed;
      $json['roll_number'] = $this->roll_number;
    }
    return $json;
  }


  public function notifyDriver() {
    if(!isset($this->token)) {
      return;
    }

    $attending = $this->input->input_stream('attending');
    if( $attending != NULL && ($attending == 0 || $attending == 1) ) {
      $student_timing = $this->db->where('student_id', $this->id)->get('student_timings')->row();
      if($student_timing != NULL) {
        $driver_timing = $this->db->where('timing_id', $student_timing->timing_id)->get('driver_timings')->row();
        if($driver_timing != NULL) {
          $data = array('user_id' => $this->id, 'activity' => 'student_attending', 'value' => $attending);
          $registrationIds = array();
          $registrationIds[] = $this->token;
          $this->pushNotification($registrationIds, $data);
        }
      }
    }
  }

  public function notifyStudent() {
    $allowed = $this->input->input_stream('allowed');
    if( $allowed != NULL && ( $allowed == 0 || $allowed == 1 ) ) {
      $data = array('user_id' => $this->id, 'activity' => 'student_allowed', 'value' => $allowed);
      $registrationIds = array();
      $registrationIds[] = $this->token;
      $this->pushNotification($registrationIds, $data);
    }
  }

  public function pushNotification($registrationIds, $data) {
    $apiKey = 'AIzaSyAbC0MHyaA4Rwmn9qsluSghc7kignq86fQ';
    $url = 'https://android.googleapis.com/gcm/send';

    $fields = array(
      'registration_ids' => $registrationIds,
      'data' => $data
    );

    $headers = array(
      'Authorization: key=' . $apiKey,
      'Content-Type: application/json'
    );

    $ch = curl_init();

    curl_setopt( $ch, CURLOPT_URL, $url);
    curl_setopt( $ch, CURLOPT_POST, true);
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    $result = curl_exec($ch);

    curl_close($ch);
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

  public function updateToken() {
    $token = $this->input->post('token');
    if($token != NULL) {
      $this->db->where('id', $this->id);
      $this->db->update('users', array('token' => $token));
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
