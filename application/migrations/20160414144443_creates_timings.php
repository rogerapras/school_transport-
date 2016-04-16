<?php

class Migration_Creates_timings extends CI_Migration {

  public function up() {
    $fields = array(
      'route_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => TRUE
      ),
      'arrival_time' => array(
        'type' => 'TIMESTAMP'
      ),
      'departure_time' => array(
        'type'=> 'TIMESTAMP'
      ),
      'status' => array('type' => 'INT', 'constraint' => 2, 'default' => 0)
    );

    $this->dbforge->add_field('id');
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('route_id', FALSE);
    $this->dbforge->create_table('timings');
  }


  public function down() {
    $this->dbforge->drop_table('timings');
  }
}
