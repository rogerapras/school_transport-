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
  private $timings;

  public function __construct() {
    parent::__construct();
  }

  public function create() {
    $this->setFromParams();
    $this->db->insert('routes', $this);
    $this->id = $this->db->insert_id();
    return $this;
  }

  public function getTimings($user = NULL) {
    if(isset($user)) {
    $result = $this->db->where('route_id', $this->id)->select('timings.*')
      ->join('student_timings', 'student_timings.timing_id = timings.id', 'INNER')
      ->where('student_timings.student_id', $user->id)->get('timings')->result();
    } else {
      $result = $this->db->where('route_id', $this->id)->get('timings')->result();
    }
    $timings = array();
    foreach($result as $row) {
      $timing = Timing_model::initialize($row);
      $timings[] = $timing->asJson();
    }
    return $timings;
  }

  public function asJson($user = NULL) {
    $response = array(
      'id' => $this->id,
      'name' => $this->name,
      'bus_number' => $this->bus_number,
      'start_latitude' => $this->start_latitude,
      'end_latitude' => $this->end_latitude,
      'start_longitude' => $this->start_longitude,
      'end_longitude' => $this->end_longitude,
      'total_stops' => $this->total_stops,
      'timings' => $this->getTimings($user)
    );
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
    $result = $this->db->where('route_id', $this->id)->get('timings')->result();

    foreach($result as $row) {
      $timing = Timing_model::initialize($row);
      $timing->remove();
    }
    $this->db->delete('routes', array('id' => $this->id));
    return $this->db->affected_rows();
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
    $this->db->from('users')->select('users.*');
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
