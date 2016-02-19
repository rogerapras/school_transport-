<?php

class Migration_Creates_driver_routes extends CI_Migration {
  public function up() {
    $fields = array(
      'user_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => TRUE
      ),
      'route_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => TRUE
      )
    );

    $this->dbforge->add_field('id');
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('user_id');
    $this->dbforge->add_key('route_id');
    $this->dbforge->create_table('driver_routes');
  }

  public function down() {
    $this->dbforge->drop_table('driver_routes');
  }
}
