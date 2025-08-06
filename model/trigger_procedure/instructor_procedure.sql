-- MySQL-Compatible Stored Procedures for Instructor Management
--
-- This script converts the Oracle PL/SQL package `INSTRUCTOR_PKG`
-- into standalone MySQL stored procedures.
--
-- Key Differences from Oracle Version:
-- 1. No Packages: MySQL does not have packages, so each procedure is a separate object.
-- 2. CREATE/DROP Syntax: Uses `DROP PROCEDURE IF EXISTS` and `CREATE PROCEDURE`.
-- 3. No %TYPE: Parameter data types are explicitly defined (e.g., INT, TEXT).
-- 4. No SYS_REFCURSOR: Procedures that return data simply execute a SELECT statement.
--    The original functions have been converted to procedures.
-- 5. Error Handling: Uses `DECLARE HANDLER` and `SIGNAL SQLSTATE '45000'` to raise custom errors.
-- 6. Row Count: Uses `ROW_COUNT()` instead of `SQL%ROWCOUNT`.
-- 7. Date Formatting: Uses `DATE_FORMAT()` instead of `TO_CHAR()`.

-- Change the delimiter to allow for semicolons within the procedures.
DELIMITER $$

-- =================================================================
-- Procedure to create a new instructor.
-- =================================================================
DROP PROCEDURE IF EXISTS `CREATE_INSTRUCTOR_PROC`$$
CREATE PROCEDURE `CREATE_INSTRUCTOR_PROC`(
    IN p_InstructorID INT,
    IN p_UserID INT,
    IN p_Biography TEXT
)
BEGIN
    -- Declare an exit handler for duplicate key errors (e.g., PK or UQ violation).
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        SET @message = CONCAT('Instructor with InstructorID ''', p_InstructorID, ''' or UserID ''', p_UserID, ''' already exists.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END;

    -- Insert the new instructor record.
    INSERT INTO INSTRUCTOR (InstructorID, UserID, Biography)
    VALUES (p_InstructorID, p_UserID, p_Biography);
END$$

-- =================================================================
-- Procedure to delete an instructor by their ID.
-- =================================================================
DROP PROCEDURE IF EXISTS `DELETE_INSTRUCTOR_PROC`$$
CREATE PROCEDURE `DELETE_INSTRUCTOR_PROC`(
    IN p_InstructorID INT
)
BEGIN
    DELETE FROM INSTRUCTOR WHERE InstructorID = p_InstructorID;

    IF ROW_COUNT() = 0 THEN
        SET @message = CONCAT('Instructor with InstructorID ''', p_InstructorID, ''' not found for deletion.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END IF;
END$$

-- =================================================================
-- Procedure to update an existing instructor.
-- =================================================================
DROP PROCEDURE IF EXISTS `UPDATE_INSTRUCTOR_PROC`$$
CREATE PROCEDURE `UPDATE_INSTRUCTOR_PROC`(
    IN p_InstructorID INT,
    IN p_UserID INT,
    IN p_Biography TEXT
)
BEGIN
    -- Declare an exit handler for duplicate key errors on the UserID unique constraint.
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        SET @message = CONCAT('Update failed: UserID ''', p_UserID, ''' is already associated with another instructor.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END;

    UPDATE INSTRUCTOR
    SET UserID = p_UserID,
        Biography = p_Biography
    WHERE InstructorID = p_InstructorID;

    IF ROW_COUNT() = 0 THEN
        SET @message = CONCAT('Instructor with InstructorID ''', p_InstructorID, ''' not found for update.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END IF;
END$$

-- =================================================================
-- Procedure to get a single instructor by their InstructorID.
-- =================================================================
DROP PROCEDURE IF EXISTS `GET_INSTRUCTOR_BY_ID_PROC`$$
CREATE PROCEDURE `GET_INSTRUCTOR_BY_ID_PROC`(
    IN p_InstructorID INT
)
BEGIN
    SELECT
        InstructorID,
        UserID,
        Biography,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM INSTRUCTOR
    WHERE InstructorID = p_InstructorID;
END$$

-- =================================================================
-- Procedure to get a single instructor by their UserID.
-- =================================================================
DROP PROCEDURE IF EXISTS `GET_INSTRUCTOR_BY_USER_ID_PROC`$$
CREATE PROCEDURE `GET_INSTRUCTOR_BY_USER_ID_PROC`(
    IN p_UserID INT
)
BEGIN
    SELECT
        InstructorID,
        UserID,
        Biography,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM INSTRUCTOR
    WHERE UserID = p_UserID;
END$$

-- =================================================================
-- Procedure to get all instructors.
-- =================================================================
DROP PROCEDURE IF EXISTS `GET_ALL_INSTRUCTORS_PROC`$$
CREATE PROCEDURE `GET_ALL_INSTRUCTORS_PROC`()
BEGIN
    SELECT
        InstructorID,
        UserID,
        Biography,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM INSTRUCTOR
    ORDER BY InstructorID ASC;
END$$

-- Reset the delimiter back to the default.
DELIMITER ;
