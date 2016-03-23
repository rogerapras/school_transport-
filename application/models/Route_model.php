<?php

class Route_model extends CI_Model {


  public $id;
  public $name;
  public $bus_number;
  public $arrival_time;
  public $departure_time;
  public $start_latitude;
  public $end_latitude;
  public $start_longitude;
  public $end_longitude;
  public $total_stops;
  private $driver;
  public $status;

  public function __construct() {
    parent::__construct();
  }

  public function create() {
    $this->setFromParams();
    $this->db->insert('routes', $this);
    $this->id = $this->db->insert_id();
    return $this;
  }

  public function asJson() {
    $response = array(
      'id' => $this->id,
      'name' => $this->name,
      'bus_number' => $this->bus_number,
      'arrival_time' => $this->arrival_time,
      'departure_time' => $this->departure_time,
      'start_latitude' => $this->start_latitude,
      'end_latitude' => $this->end_latitude,
      'start_longitude' => $this->start_longitude,
      'end_longitude' => $this->end_longitude,
      'total_stops' => $this->total_stops,
      'status' => $this->status,
      'driver' => NULL
    );
    $driver = $this->driver();
    if(isset($driver) && isset($driver->id)) {
      $response['driver'] = $driver->asJson();
    }
    return $response;
  }


  public function assignDriver($driver) {
    $driver_route = $this->db->where('route_id', $this->id)->get('driver_routes')->row();
    if($driver_route == NULL) {
      $this->db->insert('driver_routes', array('user_id' => $driver->id, 'route_id' => $this->id));
      return $this->db->insert_id();
    } else {
      $this->db->where('id', $driver_route->id);
      $this->db->update('driver_routes', array('user_id' => $driver->id));
      return $this->db->affected_rows();
    }
  }

  public function unassignDriver() {
    $driver_route = $this->db->where('route_id', $this->id)->delete('driver_routes');
    return $this->db->affected_rows();
  }

  public function assignStudent($student) {
    $route_student = $this->db->where('student_id', $student->id)->get('route_students')->row();
    if($route_student == NULL) {
      $this->db->insert('route_students', array('student_id' => $student->id, 'route_id' => $this->id));
      return $this->db->insert_id();
    } else {
      $this->db->where('id', $route_student->id);
      $this->db->update('route_students', array('route_id' => $this->id));
      return $this->db->affected_rows();
    }
  }

  public function students() {
    $students = $this->db->join('route_students', 'route_students.student_id = users.id', 'inner')
      ->where('route_students.route_id', $this->id)
      ->get('users')->result();
    $users = array();
    foreach($students as $student) {
      array_push($users, User_model::initialize($student));
    }
    return $users;
  }


  public function destroy() {
    $this->db->delete('driver_routes', array('route_id', $this->id));
    $this->db->delete('routes', array('id' => $this->id));
    return $this->db->affected_rows();
  }

  public function notifyStudents() {
    $students = $this->db->select('token')->where('token <>', '')
      ->join('route_students', 'route_students.student_id = users.id', 'inner')
      ->get('users')->result();
    $registrationIds = array();
    foreach($students as $student) {
      array_push($registrationIds, $student->token);
    }

    if(count($registrationIds) > 0) {
      $data = array('route_id' => $this->id, 'activity' => 'bus_status', 'value' => $this->status);
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


  public function find($route_id) {
    $route = $this->db->where('id', $route_id)->get('routes')->row();
    if($route != NULL) {
      $this->setObject( $route );
    } else {
      return NULL;
    }
    return $this;
  }

  public static function initialize($route_row) {
    $route = new self();
    $route->setObject($route_row);
    return $route;
  }

  public function driver() {
    $this->db->from('users');
    $this->db->join('driver_routes', 'driver_routes.user_id = users.id', 'inner');
    $this->db->where('driver_routes.route_id = ' . $this->id);
    $user = $this->db->get()->row();
    if($user != null) {
      $this->driver = User_model::initialize($user);
    }
    return $this->driver;
  }

  private function setObject( $route) {
    foreach($route as $key => $value) {
      if( property_exists( $this, $key ) ) {
        $this->$key = $value;
      }
    }
  }

  private function setFromParams() {
    $parameters = $this->input->input_stream();
    $ary = [];
    foreach($parameters as $key => $value) {
      if( property_exists( $this, $key ) ) {
        $ary[$key] = $key;
        $this->$key = $value;
      }
    }
  }

  public function update() {
    $new_status = $this->input->input_stream('status');
    $old_status = $this->status;
    $this->setFromParams();
    $this->db->where('id', $this->id);
    $this->db->update('routes', $this);
    if($new_status != NULL && $new_status != $old_status) {
      $this->notifyStudents();
    }
  }
}
