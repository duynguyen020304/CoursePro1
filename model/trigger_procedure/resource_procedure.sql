-- MySQL-Compatible Stored Procedures for Course Resources
--
-- This script converts the Oracle COURSE_RESOURCE_PKG package into
-- individual stored procedures compatible with MySQL.
--
-- Key Differences from Oracle Version:
-- 1. No Packages: MySQL does not have packages, so each procedure is created as a standalone object.
-- 2. No SYS_REFCURSOR: Functions returning cursors are converted to procedures that directly
--    return a result set via a SELECT statement. The client application will fetch this result set.
-- 3. Datatypes: Oracle's %TYPE is replaced with explicit MySQL datatypes (e.g., VARCHAR, INT).
-- 4. Error Handling: DUP_VAL_ON_INDEX is handled by `DECLARE EXIT HANDLER FOR 1062`.
-- 5. Custom Errors: RAISE_APPLICATION_ERROR is replaced with `SIGNAL SQLSTATE '45000'`.
-- 6. Row Count: SQL%ROWCOUNT is replaced with `ROW_COUNT()`.
-- 7. Date Formatting: TO_CHAR is replaced with `DATE_FORMAT()`.
-- 8. DELIMITER: The DELIMITER is changed to $$ to allow semicolons within the procedure body.

-- -----------------------------------------------------------------------------
-- Procedure to create a new course resource.
-- -----------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS CREATE_RESOURCE_PROC;
DELIMITER $$
CREATE PROCEDURE CREATE_RESOURCE_PROC(
    IN p_ResourceID   VARCHAR(255),
    IN p_LessonID     VARCHAR(255),
    IN p_ResourcePath TEXT,
    IN p_Title        VARCHAR(255),
    IN p_SortOrder    INT
)
BEGIN
    -- Declare a handler for duplicate key errors (MySQL error code 1062)
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = CONCAT('Resource with ResourceID ''', p_ResourceID, ''' already exists.');
    END;

    -- Insert the new record into the COURSERESOURCE table.
    INSERT INTO COURSERESOURCE (ResourceID, LessonID, ResourcePath, Title, SortOrder)
    VALUES (p_ResourceID, p_LessonID, p_ResourcePath, p_Title, p_SortOrder);
END$$
DELIMITER ;

-- -----------------------------------------------------------------------------
-- Procedure to retrieve a single resource by its ID.
-- (Formerly GET_RESOURCE_BY_ID_FUNC)
-- -----------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS GET_RESOURCE_BY_ID_PROC;
DELIMITER $$
CREATE PROCEDURE GET_RESOURCE_BY_ID_PROC(
    IN p_ResourceID VARCHAR(255)
)
BEGIN
    -- Select the resource and format the creation date.
    SELECT
        ResourceID,
        LessonID,
        ResourcePath,
        Title,
        SortOrder,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSERESOURCE
    WHERE
        ResourceID = p_ResourceID;
END$$
DELIMITER ;


-- -----------------------------------------------------------------------------
-- Procedure to retrieve all resources for a specific lesson.
-- (Formerly GET_RESOURCES_BY_LESSON_FUNC)
-- -----------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS GET_RESOURCES_BY_LESSON_PROC;
DELIMITER $$
CREATE PROCEDURE GET_RESOURCES_BY_LESSON_PROC(
    IN p_LessonID VARCHAR(255)
)
BEGIN
    -- Select resources for the given lesson, ordered by SortOrder.
    SELECT
        ResourceID,
        LessonID,
        ResourcePath,
        Title,
        SortOrder,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSERESOURCE
    WHERE
        LessonID = p_LessonID
    ORDER BY SortOrder ASC;
END$$
DELIMITER ;


-- -----------------------------------------------------------------------------
-- Procedure to retrieve all resources from the table.
-- (Formerly GET_ALL_RESOURCES_FUNC)
-- -----------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS GET_ALL_RESOURCES_PROC;
DELIMITER $$
CREATE PROCEDURE GET_ALL_RESOURCES_PROC()
BEGIN
    -- Select all resources, ordered by SortOrder.
    SELECT
        ResourceID,
        LessonID,
        ResourcePath,
        Title,
        SortOrder,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSERESOURCE
    ORDER BY SortOrder ASC;
END$$
DELIMITER ;


-- -----------------------------------------------------------------------------
-- Procedure to update an existing course resource.
-- -----------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS UPDATE_RESOURCE_PROC;
DELIMITER $$
CREATE PROCEDURE UPDATE_RESOURCE_PROC(
    IN p_ResourceID   VARCHAR(255),
    IN p_LessonID     VARCHAR(255),
    IN p_ResourcePath TEXT,
    IN p_Title        VARCHAR(255),
    IN p_SortOrder    INT
)
BEGIN
    -- Update the specified resource.
    UPDATE COURSERESOURCE
    SET LessonID     = p_LessonID,
        ResourcePath = p_ResourcePath,
        Title        = p_Title,
        SortOrder    = p_SortOrder
    WHERE ResourceID = p_ResourceID;

    -- Check if any row was actually updated. If not, the resource was not found.
    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = CONCAT('Resource with ResourceID ''', p_ResourceID, ''' not found for update.');
    END IF;
END$$
DELIMITER ;


-- -----------------------------------------------------------------------------
-- Procedure to delete a course resource by its ID.
-- -----------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS DELETE_RESOURCE_PROC;
DELIMITER $$
CREATE PROCEDURE DELETE_RESOURCE_PROC(
    IN p_ResourceID VARCHAR(255)
)
BEGIN
    -- Delete the specified resource.
    DELETE FROM COURSERESOURCE
    WHERE ResourceID = p_ResourceID;

    -- Check if a row was deleted. If not, the resource was not found.
    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = CONCAT('Resource with ResourceID ''', p_ResourceID, ''' not found for deletion.');
    END IF;
END$$
DELIMITER ;
