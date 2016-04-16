<?php

class Migration_Creates_student_timings extends CI_Migration {
  public function up() {
    $fields = array(
      'student_id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE),
      'timing_id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE)
    );

    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('student_id', FALSE);
    $this->dbforge->add_key('timing_id', FALSE);
    $this->dbforge->create_table('student_timings');
  }

  public function down() {
    $this->dbforge->drop_table('student_timings');
  }
}
