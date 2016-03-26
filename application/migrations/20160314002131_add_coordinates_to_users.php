<?php

class Migration_Add_coordinates_to_users extends CI_Migration {

  public function up() {
    $fields = array(
      'latitude' => array('type' => 'DECIMAL', 'constraint' => '11,8', 'default' => 0.0),
      'longitude' => array('type' => 'DECIMAL', 'constraint' => '11,8', 'default' => 0.0)
    );

    $this->dbforge->add_column('users', $fields);
  }

  public function down() {
    $this->dbforge->drop_column('users', 'longitude');
    $this->dbforge->drop_column('users', 'latitude');
  }
}
