<?php

defined('BASEPATH') OR exit('No direct sscript access allowed');

class Migration_Creates_Devices extends CI_Migration {

  public function up() {
    $fields = array(
      'token' => array(
        'type' => 'VARCHAR',
        'constraint' => 255
      ),
      'device_id' => array(
        'type' => 'VARCHAR',
        'constraint' => 255,
        'null' => TRUE
      ),
      'user_id' => array(
        'type' => 'INT',
        'unsigned' => TRUE
      )
    );
    $this->dbforge->add_field('id');
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('user_id');
    $this->dbforge->add_key(array('device_id', 'token'));
    if($this->dbforge->create_table('devices')) {
      echo 'Devices Database Created!';
    }

  }

  public function down() {
    if( $this->dbforge->drop_table('devices') ) {
      echo 'Devices Databases Dropped!';
    }
  }
}
