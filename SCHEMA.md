Database Schema - Eschool Management System
This document outlines the database schema for the Eschool Management System, designed to support multi-school management, role-based access, attendance tracking, and financial (kas) management. The schema is optimized for a Laravel application using Eloquent ORM.
Table Definitions
1. schools

Description: Stores details of each school.
Columns:
id (PK, integer, auto-increment)
name (varchar, required)
address (text, nullable)
phone (varchar, required)
email (varchar, unique, required)
created_at (datetime, required)
updated_at (datetime, required)



2. profiles

Description: Stores basic individual data (teachers, students).
Columns:
id (PK, integer, auto-increment)
name (varchar, required)
date_of_birth (date, required)
gender (enum: 'M', 'F', required)
address (text, nullable)
status (enum: 'active', 'inactive', default: 'active')
created_at (datetime, required)
updated_at (datetime, required)



3. users

Description: Stores authentication data linked to profiles.
Columns:
id (PK, integer, auto-increment)
profile_id (FK to profiles.id, unique, required)
email (varchar, unique, required)
password (varchar, required)
verified_at (datetime, nullable)
created_at (datetime, required)
updated_at (datetime, required)


Foreign Key Constraints:
profile_id references profiles(id) on delete cascade



4. teachers

Description: Stores data for licensed teachers (including staff and coordinators).
Columns:
id (PK, integer, auto-increment)
profile_id (FK to profiles.id, unique, required)
license_number (varchar, unique, required)
school_id (FK to schools.id, required)
created_at (datetime, required)
updated_at (datetime, required)


Foreign Key Constraints:
profile_id references profiles(id) on delete cascade
school_id references schools(id) on delete cascade



5. students

Description: Stores data for students (members and treasurers).
Columns:
id (PK, integer, auto-increment)
profile_id (FK to profiles.id, unique, required)
school_id (FK to schools.id, required)
student_id (varchar, unique, nullable)
grade_level (varchar, nullable)
created_at (datetime, required)
updated_at (datetime, required)


Foreign Key Constraints:
profile_id references profiles(id) on delete cascade
school_id references schools(id) on delete cascade



6. eschools

Description: Stores extracurricular activity details per school.
Columns:
id (PK, integer, auto-increment)
school_id (FK to schools.id, required)
name (varchar, required)
schedule_days (varchar, required)
description (text, nullable)
is_active (boolean, default: true)
monthly_fee_amount (decimal(8,2), default: 0.00)
created_at (datetime, required)
updated_at (datetime, required)


Foreign Key Constraints:
school_id references schools(id) on delete cascade



7. user_eschool_roles

Description: Links users to eschools with specific roles.
Columns:
id (PK, integer, auto-increment)
user_id (FK to users.id, required)
eschool_id (FK to eschools.id, nullable) // Nullable for supervisor role
role (enum: 'supervisor', 'coordinator', 'treasurer', 'member', required)
created_at (datetime, required)
updated_at (datetime, required)


Foreign Key Constraints:
user_id references users(id) on delete cascade
eschool_id references eschools(id) on delete cascade


Notes: Unique constraint recommended for (user_id, role, eschool_id) for coordinator/treasurer; max 2 supervisors per school enforced via application logic.

8. attendance_record

Description: Tracks attendance for eschool members.
Columns:
id (PK, integer, auto-increment)
user_eschool_role_id (FK to user_eschool_roles.id, required)
status (enum: 'present', 'absent', 'late', required)
notes (text, nullable)
created_at (datetime, required)
updated_at (datetime, required)


Foreign Key Constraints:
user_eschool_role_id references user_eschool_roles(id) on delete cascade



9. kas_record

Description: Manages financial records for eschools.
Columns:
id (PK, integer, auto-increment)
eschool_id (FK to eschools.id, required)
description (text, required)
category (varchar, required)
amount (decimal(8,2), required)
date (date, required)
recorder_id (FK to user_eschool_roles.id, required)
created_at (datetime, required)
updated_at (datetime, required)


Foreign Key Constraints:
eschool_id references eschools(id) on delete cascade
recorder_id references user_eschool_roles(id) on delete cascade


Notes: Recorder must have role 'treasurer'.

10. kas_payment

Description: Tracks individual payments linked to kas records.
Columns:
id (PK, integer, auto-increment)
kas_record_id (FK to kas_record.id, required)
member_id (FK to user_eschool_roles.id, required)
amount (decimal(8,2), required)
month (varchar, required)
year (integer, required)
is_paid (boolean, default: false)
paid_date (date, nullable)
created_at (datetime, required)
updated_at (datetime, required)


Foreign Key Constraints:
kas_record_id references kas_record(id) on delete cascade
member_id references user_eschool_roles(id) on delete cascade


Notes: Member must have role 'member' or 'treasurer'.

Relationships

schools → teachers, students, eschools (via school_id).
profiles → users, teachers, students (via profile_id).
users → user_eschool_roles (via user_id).
eschools → user_eschool_roles, attendance_record, kas_record (via eschool_id).
user_eschool_roles → attendance_record, kas_record, kas_payment (via id).

Business Rules Enforcement

Maximum 2 supervisor roles per school_id (enforced via application logic).
Unique coordinator and treasurer per eschool_id (enforced via unique constraint or application logic).
Members can have multiple eschool_id entries with role member.
Only teachers can be assigned supervisor or coordinator roles; only students can be treasurer or member (enforced via application logic).

Notes

All tables use created_at and updated_at for timestamp tracking.
Foreign key constraints use on delete cascade to maintain data integrity.
Additional indexes or triggers can be added based on performance needs.
