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
  public $driver;

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


  public function destroy() {
    $this->db->delete('driver_routes', array('route_id', $this->id));
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

  public function driver() {
    $this->db->from('users');
    $this->db->join('driver_routes', 'driver_routes.user_id = users.id', 'left');
    $this->db->where('driver_routes.route_id = ' . $this->id);
    $this->driver = User_model::initialize($this->db->get()->row());
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
    foreach($parameters as $key => $value) {
      if( property_exists( $this, $key ) ) {
        $this->$key = $value;
      }
    }
  }

  public function update() {
    $this->setFromParams();
    $this->db->where('id', $this->id);
    $this->db->update('routes', $this);
  }
}
