-- MySQL-Compatible Stored Procedures for Course-Objective Management
--
-- This script converts the Oracle PL/SQL package `COURSE_OBJECTIVE_PKG`
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
-- Procedure to create a new course objective.
-- =================================================================
DROP PROCEDURE IF EXISTS `CREATE_OBJECTIVE_PROC`$$
CREATE PROCEDURE `CREATE_OBJECTIVE_PROC`(
    IN p_ObjectiveID INT,
    IN p_CourseID INT,
    IN p_Objective TEXT
)
BEGIN
    -- Declare an exit handler for duplicate key errors.
    -- MySQL error code 1062 is for duplicate entry.
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        SET @message = CONCAT('Objective with ObjectiveID ''', p_ObjectiveID, ''' and CourseID ''', p_CourseID, ''' already exists.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END;

    -- Insert the new course objective.
    -- A foreign key constraint on CourseID should handle cases where the course does not exist.
    INSERT INTO COURSEOBJECTIVE (ObjectiveID, CourseID, Objective)
    VALUES (p_ObjectiveID, p_CourseID, p_Objective);
END$$

-- =================================================================
-- Procedure to update an existing course objective.
-- =================================================================
DROP PROCEDURE IF EXISTS `UPDATE_OBJECTIVE_PROC`$$
CREATE PROCEDURE `UPDATE_OBJECTIVE_PROC`(
    IN p_ObjectiveID INT,
    IN p_CourseID INT,
    IN p_Objective TEXT
)
BEGIN
    -- Perform the update.
    UPDATE COURSEOBJECTIVE
    SET Objective = p_Objective
    WHERE ObjectiveID = p_ObjectiveID AND CourseID = p_CourseID;

    -- Check if a row was actually updated.
    IF ROW_COUNT() = 0 THEN
        SET @message = CONCAT('CourseObjective with ObjectiveID ''', p_ObjectiveID, ''' and CourseID ''', p_CourseID, ''' not found for update.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END IF;
END$$

-- =================================================================
-- Procedure to delete a course objective.
-- Note: This deletes all objectives with the given ObjectiveID,
-- consistent with the original Oracle procedure's logic.
-- =================================================================
DROP PROCEDURE IF EXISTS `DELETE_OBJECTIVE_PROC`$$
CREATE PROCEDURE `DELETE_OBJECTIVE_PROC`(
    IN p_ObjectiveID INT
)
BEGIN
    -- Perform the deletion.
    DELETE FROM COURSEOBJECTIVE
    WHERE ObjectiveID = p_ObjectiveID;

    -- Check if any rows were deleted.
    IF ROW_COUNT() = 0 THEN
        SET @message = CONCAT('No CourseObjective found with ObjectiveID ''', p_ObjectiveID, ''' for deletion, or no rows deleted.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END IF;
END$$

-- =================================================================
-- Procedure to get a specific objective by its ID.
-- This was a FUNCTION in Oracle, converted to a PROCEDURE for MySQL.
-- =================================================================
DROP PROCEDURE IF EXISTS `GET_OBJ_BY_OBJ_ID_PROC`$$
CREATE PROCEDURE `GET_OBJ_BY_OBJ_ID_PROC`(
    IN p_ObjectiveID INT
)
BEGIN
    -- Select the data to return it as a result set.
    SELECT
        ObjectiveID,
        CourseID,
        Objective,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSEOBJECTIVE
    WHERE
        ObjectiveID = p_ObjectiveID;
END$$

-- =================================================================
-- Procedure to get all objectives for a given course.
-- This was a FUNCTION in Oracle, converted to a PROCEDURE for MySQL.
-- =================================================================
DROP PROCEDURE IF EXISTS `GET_OBJS_BY_COURSE_ID_PROC`$$
CREATE PROCEDURE `GET_OBJS_BY_COURSE_ID_PROC`(
    IN p_CourseID INT
)
BEGIN
    -- Select the data to return it as a result set.
    SELECT
        ObjectiveID,
        CourseID,
        Objective,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSEOBJECTIVE
    WHERE
        CourseID = p_CourseID
    ORDER BY ObjectiveID ASC;
END$$

-- Reset the delimiter back to the default.
DELIMITER ;
