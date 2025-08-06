-- MySQL-Compatible Stored Procedures for Course-Requirement Management
--
-- This script converts the Oracle PL/SQL package `COURSE_REQUIREMENT_PKG`
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
-- Procedure to create a new course requirement.
-- =================================================================
DROP PROCEDURE IF EXISTS `CREATE_REQUIREMENT_PROC`$$
CREATE PROCEDURE `CREATE_REQUIREMENT_PROC`(
    IN p_RequirementID INT,
    IN p_CourseID INT,
    IN p_Requirement TEXT
)
BEGIN
    -- Declare an exit handler for duplicate key errors.
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        SET @message = CONCAT('Requirement with RequirementID ''', p_RequirementID, ''' and CourseID ''', p_CourseID, ''' already exists.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END;

    -- Insert the new course requirement.
    -- A foreign key constraint on CourseID should handle cases where the course does not exist.
    INSERT INTO COURSEREQUIREMENT (RequirementID, CourseID, Requirement)
    VALUES (p_RequirementID, p_CourseID, p_Requirement);
END$$

-- =================================================================
-- Procedure to update an existing course requirement.
-- =================================================================
DROP PROCEDURE IF EXISTS `UPDATE_REQUIREMENT_PROC`$$
CREATE PROCEDURE `UPDATE_REQUIREMENT_PROC`(
    IN p_RequirementID INT,
    IN p_CourseID INT,
    IN p_Requirement TEXT
)
BEGIN
    -- Perform the update.
    UPDATE COURSEREQUIREMENT
    SET Requirement = p_Requirement
    WHERE RequirementID = p_RequirementID AND CourseID = p_CourseID;

    -- Check if a row was actually updated.
    IF ROW_COUNT() = 0 THEN
        SET @message = CONCAT('CourseRequirement with RequirementID ''', p_RequirementID, ''' and CourseID ''', p_CourseID, ''' not found for update.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END IF;
END$$

-- =================================================================
-- Procedure to delete a course requirement.
-- Note: This deletes all requirements with the given RequirementID,
-- consistent with the original Oracle procedure's logic.
-- =================================================================
DROP PROCEDURE IF EXISTS `DELETE_REQUIREMENT_PROC`$$
CREATE PROCEDURE `DELETE_REQUIREMENT_PROC`(
    IN p_RequirementID INT
)
BEGIN
    -- Perform the deletion.
    DELETE FROM COURSEREQUIREMENT
    WHERE RequirementID = p_RequirementID;

    -- Check if any rows were deleted.
    IF ROW_COUNT() = 0 THEN
        SET @message = CONCAT('No CourseRequirement found with RequirementID ''', p_RequirementID, ''' for deletion, or no rows deleted.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END IF;
END$$

-- =================================================================
-- Procedure to get a specific requirement by its ID.
-- This was a FUNCTION in Oracle, converted to a PROCEDURE for MySQL.
-- =================================================================
DROP PROCEDURE IF EXISTS `GET_REQ_BY_REQ_ID_PROC`$$
CREATE PROCEDURE `GET_REQ_BY_REQ_ID_PROC`(
    IN p_RequirementID INT
)
BEGIN
    -- Select the data to return it as a result set.
    SELECT
        RequirementID,
        CourseID,
        Requirement,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSEREQUIREMENT
    WHERE
        RequirementID = p_RequirementID;
END$$

-- =================================================================
-- Procedure to get all requirements for a given course.
-- This was a FUNCTION in Oracle, converted to a PROCEDURE for MySQL.
-- =================================================================
DROP PROCEDURE IF EXISTS `GET_REQS_BY_COURSE_ID_PROC`$$
CREATE PROCEDURE `GET_REQS_BY_COURSE_ID_PROC`(
    IN p_CourseID INT
)
BEGIN
    -- Select the data to return it as a result set.
    SELECT
        RequirementID,
        CourseID,
        Requirement,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSEREQUIREMENT
    WHERE
        CourseID = p_CourseID
    ORDER BY RequirementID ASC;
END$$

-- Reset the delimiter back to the default.
DELIMITER ;
