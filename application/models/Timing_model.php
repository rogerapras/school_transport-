<?php

class Timing_model extends CI_Model {

  public $id;
  public $route_id;
  public $arrival_time;
  public $departure_time;
  public $status;

  public function __construct() {
    parent::__construct();
  }




	private function setObject( $row) {
		foreach($row as $key => $value) {
			if( property_exists( $this, $key ) ) {
				$this->$key = $value;
			}
		}
	}

  public function notifyStudents() {
    $students = $this->db->select('token')->where('token <>', '')
      ->join('student_timings', 'student_timings.student_id = users.id', 'INNER')
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
    $this->db->update('timings', $this);
    if($new_status != NULL && $new_status != $old_status) {
      $this->notifyStudents();
    }
  }


  public static function initialize($row) {
    $object = new self();
    $object->setObject($row);
    return $object;
  }


  public function asJson() {
    return array(
      'id' => $this->id,
      'route_id' => $this->route_id,
      'arrival_time' => $this->arrival_time,
      'departure_time' => $this->departure_time,
      'status' => $this->status,
      'driver' => $this->getDriver()
    );
  }

  public function getDriver() {
    $row = $this->db->join('driver_timings', 'driver_timings.driver_id = users.id', 'INNER')
      ->where('driver_timings.timing_id', $this->id)->get('users')->row();
    if($row == NULL) {
      return NULL;
    }
    $driver = User_model::initialize($row);
    return $driver->asJson();
  }


  public function assignDriver($driver) {
    $row = $this->db->where('timing_id', $this->id)->get('driver_timings')->row();
    if($row == NULL) {
      $this->db->insert('driver_timings', array('driver_id' => $driver->id, 'timing_id' => $this->id));
      return $this->db->insert_id();
    } else {
      $this->db->set('driver_id', $driver->id);
      $this->db->update('driver_timings', array('timing_id' => $this->id));
      return $this->db->affected_rows();
    }
  }

  public function assignStudent($student) {
    $row = $this->db->where('student_id', $student->id)->get('student_timings')->row();
    if($row == NULL) {
      $this->db->insert('student_timings', array('student_id' => $student->id, 'timing_id' => $this->id));
      return $this->db->insert_id();
    } else {
      $this->db->set('timing_id', $this->id);
      $this->db->where('student_id', $student->id);
      $this->db->update('student_timings');
      return $this->db->affected_rows();
    }
  }

  public function remove() {
    $this->db->delete('driver_timings', array('timing_id' => $this->id));
    $this->db->delete('student_timings', array('timing_id' => $this->id));
    $this->db->delete('timings', array('id' => $this->id));
    return $this->db->affected_rows();
  }


  public function find($timing_id) {
    $row = $this->db->where('id', $timing_id)->get('timings')->row();
    if($row != NULL) {
      $this->setObject($row);
    } else {
      return NULL;
    }
    return $this;
  }

  public static function createFrom( $route_id, $params ) {

    foreach($params as $timing) {
      if(isset($timing->id)) {
        $timing = Timing_model::initialize($timing);
        $timing->update();
      } else {
        $object = new self();
        $object->route_id = $route_id;

        foreach($timing as $key => $value) {
          if( property_exists( $object, $key ) ) {
            $object->$key = $value;
          }
        }
      }

      $object->save();
    }
  }

  public function save() {
      $this->db->insert('timings', $this);
  }

}
