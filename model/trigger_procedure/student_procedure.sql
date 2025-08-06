-- MySQL compatible stored procedures for STUDENT management.
-- The original Oracle PL/SQL package has been converted into individual procedures.

-- A delimiter is used to allow for the semicolon within the procedure body.
DELIMITER $$

-- =================================================================================
-- Procedure to create a new student.
-- Equivalent to Oracle's CREATE_STUDENT_PROC.
-- =================================================================================
CREATE PROCEDURE `CREATE_STUDENT_PROC`(
    IN p_StudentID INT,
    IN p_UserID    INT
)
BEGIN
    -- Declare a handler for duplicate key errors (MySQL error code 1062).
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        -- Check if the StudentID is the duplicate.
        IF (SELECT COUNT(*) FROM `STUDENT` WHERE `StudentID` = p_StudentID) > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = CONCAT('Student with StudentID ''', p_StudentID, ''' already exists.');
        -- Check if the UserID is the duplicate.
        ELSEIF (SELECT COUNT(*) FROM `STUDENT` WHERE `UserID` = p_UserID) > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = CONCAT('User with UserID ''', p_UserID, ''' is already registered as a student.');
        -- Handle other potential unique constraint violations.
        ELSE
            SIGNAL SQLSTATE '23000'
            SET MESSAGE_TEXT = 'Duplicate entry violation.';
        END IF;
    END;

    -- Insert the new student into the STUDENT table.
    INSERT INTO `STUDENT` (`StudentID`, `UserID`)
    VALUES (p_StudentID, p_UserID);
END$$

-- =================================================================================
-- Procedure to delete a student by their ID.
-- Equivalent to Oracle's DELETE_STUDENT_PROC.
-- =================================================================================
CREATE PROCEDURE `DELETE_STUDENT_PROC`(
    IN p_StudentID INT
)
BEGIN
    -- Delete the student from the STUDENT table.
    DELETE FROM `STUDENT`
    WHERE `StudentID` = p_StudentID;

    -- Check if any row was actually deleted.
    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = CONCAT('Student with StudentID ''', p_StudentID, ''' not found for deletion.');
    END IF;
END$$

-- =================================================================================
-- Procedure to update an existing student's UserID.
-- Equivalent to Oracle's UPDATE_STUDENT_PROC.
-- =================================================================================
CREATE PROCEDURE `UPDATE_STUDENT_PROC`(
    IN p_StudentID INT,
    IN p_UserID    INT
)
BEGIN
    -- Declare a handler for duplicate key errors (MySQL error code 1062).
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = CONCAT('Update failed: UserID ''', p_UserID, ''' is already associated with another student.');
    END;

    -- Update the UserID for the given StudentID.
    UPDATE `STUDENT`
    SET `UserID` = p_UserID
    WHERE `StudentID` = p_StudentID;

    -- Check if any row was actually updated.
    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = CONCAT('Student with StudentID ''', p_StudentID, ''' not found for update.');
    END IF;
END$$

-- =================================================================================
-- Procedure to get a student by their ID.
-- Equivalent to Oracle's GET_STUDENT_BY_ID_FUNC.
-- =================================================================================
CREATE PROCEDURE `GET_STUDENT_BY_ID_PROC`(
    IN p_StudentID INT
)
BEGIN
    -- Select the student details and format the created_at timestamp.
    SELECT
        `StudentID`,
        `UserID`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS `created_at_formatted`
    FROM
        `STUDENT`
    WHERE
        `StudentID` = p_StudentID;
END$$

-- =================================================================================
-- Procedure to get a student by their UserID.
-- Equivalent to Oracle's GET_STUDENT_BY_USER_ID_FUNC.
-- =================================================================================
CREATE PROCEDURE `GET_STUDENT_BY_USER_ID_PROC`(
    IN p_UserID INT
)
BEGIN
    -- Select the student details and format the created_at timestamp.
    SELECT
        `StudentID`,
        `UserID`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS `created_at_formatted`
    FROM
        `STUDENT`
    WHERE
        `UserID` = p_UserID;
END$$

-- =================================================================================
-- Procedure to get all students.
-- Equivalent to Oracle's GET_ALL_STUDENTS_FUNC.
-- =================================================================================
CREATE PROCEDURE `GET_ALL_STUDENTS_PROC`()
BEGIN
    -- Select all students and format the created_at timestamp.
    SELECT
        `StudentID`,
        `UserID`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS `created_at_formatted`
    FROM
        `STUDENT`
    ORDER BY `StudentID` ASC;
END$$

-- Reset the delimiter back to the default semicolon.
DELIMITER ;
