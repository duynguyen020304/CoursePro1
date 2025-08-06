-- MySQL-Compatible Stored Procedures for Course-Instructor Management
--
-- This script converts the Oracle PL/SQL package `COURSE_INSTRUCTOR_PKG`
-- into standalone MySQL stored procedures.
--
-- Key Differences from Oracle Version:
-- 1. No Packages: MySQL does not have packages, so each procedure is a separate object.
-- 2. CREATE/DROP Syntax: Uses `DROP PROCEDURE IF EXISTS` and `CREATE PROCEDURE`.
-- 3. No %TYPE: Parameter data types are explicitly defined (e.g., INT).
-- 4. No SYS_REFCURSOR: Procedures that return data simply execute a SELECT statement.
--    The original functions have been converted to procedures for this reason.
-- 5. Error Handling: Uses `DECLARE HANDLER` and `SIGNAL SQLSTATE '45000'` to raise custom errors.
-- 6. Row Count: Uses `ROW_COUNT()` instead of `SQL%ROWCOUNT`.
-- 7. Date Formatting: Uses `DATE_FORMAT()` instead of `TO_CHAR()`.

-- Change the delimiter to allow for semicolons within the procedures.
DELIMITER $$

-- =================================================================
-- Procedure to add a new course-instructor assignment.
-- =================================================================
DROP PROCEDURE IF EXISTS `ADD_COURSE_INSTRUCTOR_PROC`$$
CREATE PROCEDURE `ADD_COURSE_INSTRUCTOR_PROC`(
    IN p_CourseID INT,
    IN p_InstructorID INT
)
BEGIN
    -- Declare an exit handler for duplicate key errors (e.g., primary key violation).
    -- MySQL error code 1062 is for duplicate entry.
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        SET @message = CONCAT('Assignment for CourseID ''', p_CourseID, ''' and InstructorID ''', p_InstructorID, ''' already exists.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END;

    -- Insert the new assignment.
    -- Foreign key constraints will automatically handle non-existent CourseID or InstructorID.
    INSERT INTO COURSEINSTRUCTOR (CourseID, InstructorID)
    VALUES (p_CourseID, p_InstructorID);
END$$

-- =================================================================
-- Procedure to update an existing course-instructor assignment.
-- =================================================================
DROP PROCEDURE IF EXISTS `UPDATE_COURSE_INSTRUCTOR_PROC`$$
CREATE PROCEDURE `UPDATE_COURSE_INSTRUCTOR_PROC`(
    IN p_OldCourseID INT,
    IN p_OldInstructorID INT,
    IN p_NewCourseID INT,
    IN p_NewInstructorID INT
)
BEGIN
    DECLARE v_row_exists INT DEFAULT 0;

    -- Declare an exit handler for duplicate key errors on update.
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        SET @message = CONCAT('Cannot update: New assignment for CourseID ''', p_NewCourseID, ''' and InstructorID ''', p_NewInstructorID, ''' would create a duplicate.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END;

    -- Check if the original assignment exists before attempting to update.
    SELECT COUNT(*)
    INTO v_row_exists
    FROM COURSEINSTRUCTOR
    WHERE CourseID = p_OldCourseID AND InstructorID = p_OldInstructorID;

    IF v_row_exists = 0 THEN
        SET @message = CONCAT('Original assignment for CourseID ''', p_OldCourseID, ''' and InstructorID ''', p_OldInstructorID, ''' not found for update.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END IF;

    -- Perform the update.
    UPDATE COURSEINSTRUCTOR
    SET CourseID = p_NewCourseID,
        InstructorID = p_NewInstructorID
    WHERE CourseID = p_OldCourseID AND InstructorID = p_OldInstructorID;

    -- Check if any row was actually updated.
    IF ROW_COUNT() = 0 THEN
        SET @message = CONCAT('Assignment for CourseID ''', p_OldCourseID, ''' and InstructorID ''', p_OldInstructorID, ''' not found for update, or no changes made.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END IF;
END$$

-- =================================================================
-- Procedure to delete (unlink) a course-instructor assignment.
-- =================================================================
DROP PROCEDURE IF EXISTS `UNLINK_COURSE_INSTRUCTOR_PROC`$$
CREATE PROCEDURE `UNLINK_COURSE_INSTRUCTOR_PROC`(
    IN p_CourseID INT,
    IN p_InstructorID INT
)
BEGIN
    -- Perform the deletion.
    DELETE FROM COURSEINSTRUCTOR
    WHERE CourseID = p_CourseID AND InstructorID = p_InstructorID;

    -- Check if a row was deleted. If not, the record was not found.
    IF ROW_COUNT() = 0 THEN
        SET @message = CONCAT('Assignment for CourseID ''', p_CourseID, ''' and InstructorID ''', p_InstructorID, ''' not found for deletion.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END IF;
END$$

-- =================================================================
-- Procedure to get a specific assignment.
-- This was a FUNCTION in Oracle, but must be a PROCEDURE in MySQL
-- to return a result set.
-- =================================================================
DROP PROCEDURE IF EXISTS `GET_ASSIGNMENT_PROC`$$
CREATE PROCEDURE `GET_ASSIGNMENT_PROC`(
    IN p_CourseID INT,
    IN p_InstructorID INT
)
BEGIN
    -- Select the data to return it as a result set.
    SELECT
        CourseID,
        InstructorID,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSEINSTRUCTOR
    WHERE
        CourseID = p_CourseID AND InstructorID = p_InstructorID;
END$$

-- =================================================================
-- Procedure to get all instructors for a given course.
-- This was a FUNCTION in Oracle, but must be a PROCEDURE in MySQL
-- to return a result set.
-- =================================================================
DROP PROCEDURE IF EXISTS `GET_INSTR_BY_COURSE_PROC`$$
CREATE PROCEDURE `GET_INSTR_BY_COURSE_PROC`(
    IN p_CourseID INT
)
BEGIN
    -- Select the data to return it as a result set.
    SELECT
        ci.CourseID,
        ci.InstructorID,
        DATE_FORMAT(ci.created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSEINSTRUCTOR ci
    WHERE
        ci.CourseID = p_CourseID
    ORDER BY
        ci.InstructorID ASC;
END$$

-- Reset the delimiter back to the default.
DELIMITER ;
