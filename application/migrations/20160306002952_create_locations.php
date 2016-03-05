<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_locations extends CI_Migration {

  public function up() {
    $fields = array(
      'latitude' => array(
        'type' => 'DECIMAL',
        'constraint' => '10,8',
        'default' => 0.0
      ),
      'longitude' => array(
        'type' => 'DECIMAL',
        'constraint' => '11,8',
        'default' => 0.0
      ),
      'speed' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' => 0
      ),
      'user_id' => array(
        'type' => 'INT',
        'constraint' => 11
      )
    );
    $this->dbforge->add_field('id');
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('user_id', FALSE);
    $this->dbforge->create_table('locations');
  }
}
