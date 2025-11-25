<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEnrollmentTriggers extends Migration
{
    public function up()
    {
        // Trigger 1: Increment on new enrollment
        $this->db->query("
            CREATE TRIGGER increment_enrollment_count
            AFTER INSERT ON enrollments
            FOR EACH ROW
            BEGIN
                IF NEW.enrollment_status = 'enrolled' THEN
                    UPDATE course_offerings 
                    SET current_enrollment = current_enrollment + 1
                    WHERE id = NEW.course_offering_id;
                END IF;
            END
        ");

        // Trigger 2: Handle status changes (enrolled -> dropped/withdrawn)
        $this->db->query("
            CREATE TRIGGER update_enrollment_count
            AFTER UPDATE ON enrollments
            FOR EACH ROW
            BEGIN
                -- Student just became enrolled (was pending, now enrolled)
                IF OLD.enrollment_status != 'enrolled' AND NEW.enrollment_status = 'enrolled' THEN
                    UPDATE course_offerings 
                    SET current_enrollment = current_enrollment + 1
                    WHERE id = NEW.course_offering_id;
                END IF;
                
                -- Student dropped/withdrew (was enrolled, now not)
                IF OLD.enrollment_status = 'enrolled' AND NEW.enrollment_status != 'enrolled' THEN
                    UPDATE course_offerings 
                    SET current_enrollment = current_enrollment - 1
                    WHERE id = NEW.course_offering_id;
                END IF;
            END
        ");

        // Trigger 3: Decrement on enrollment deletion
        $this->db->query("
            CREATE TRIGGER decrement_enrollment_count
            AFTER DELETE ON enrollments
            FOR EACH ROW
            BEGIN
                IF OLD.enrollment_status = 'enrolled' THEN
                    UPDATE course_offerings 
                    SET current_enrollment = current_enrollment - 1
                    WHERE id = OLD.course_offering_id;
                END IF;
            END
        ");
    }

    public function down()
    {
        // Drop triggers in reverse order
        $this->db->query("DROP TRIGGER IF EXISTS decrement_enrollment_count");
        $this->db->query("DROP TRIGGER IF EXISTS update_enrollment_count");
        $this->db->query("DROP TRIGGER IF EXISTS increment_enrollment_count");
    }
}
