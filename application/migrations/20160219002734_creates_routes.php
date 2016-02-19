<?php

class Migration_Creates_routes extends CI_Migration {

  public function up() {
    $fields = array(
      'name' => array(
        'type' => 'VARCHAR',
        'constraint' => 100,
        'default' => ''
      ),
      'bus_number' => array(
        'type' => 'VARCHAR',
        'constraint' => 100,
        'default' => ''
      ),
      'arrival_time' => array(
        'type' => 'TIMESTAMP'
      ),
      'departure_time' => array(
        'type'=> 'TIMESTAMP'
      ),
      'start_latitude' => array(
        'type' => 'DECIMAL',
        'constraint' => '10,8',
        'default' => 0.0
      ),
      'end_latitude' => array(
        'type' => 'DECIMAL',
        'constraint' => '11,8',
        'default' => 0.0
      ),
      'start_longitude' => array(
        'type' => 'DECIMAL',
        'constraint' => '10,8',
        'default' => 0.0
      ),
      'end_longitude' => array(
        'type' => 'DECIMAL',
        'constraint' => '11,8',
        'default' => 0.0
      ),
      'total_stops' => array(
        'type' => 'INT',
        'constraint' => 5,
        'unsigned' => TRUE,
        'default' => 0
      )
    );
    $this->dbforge->add_field('id');
    $this->dbforge->add_field($fields);
    $this->dbforge->create_table('routes');
  }

  public function down() {
    $this->dbforge->drop_table('routes');
  }

}
