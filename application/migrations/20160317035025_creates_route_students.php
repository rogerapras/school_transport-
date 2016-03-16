<?php

class Migration_Creates_route_students extends CI_Migration {

  public function up() {
    $fields = array(
      'route_id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE),
      'student_id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE)
    );

    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('route_id', FALSE);
    $this->dbforge->add_key('student_id', FALSE);
    $this->dbforge->create_table('route_students');
  }

  public function down() {
    $this->dbforge->drop_table('route_students');
  }
}
