-- MySQL version of the COURSE_IMAGE_PKG
-- Since MySQL does not have packages, each procedure and function is created as a separate routine.

-- Set a custom delimiter to allow for semicolons within the procedure bodies.
DELIMITER //

-- --------------------------------------------------------------------------------
-- Procedure to create a new course image record.
-- --------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS CREATE_IMAGE_PROC;
//
CREATE PROCEDURE CREATE_IMAGE_PROC(
    IN p_ImageID    INT,
    IN p_CourseID   INT,
    IN p_ImagePath  VARCHAR(255),
    IN p_Caption    TEXT,
    IN p_SortOrder  INT
)
BEGIN
    -- The DECLARE HANDLER will catch any SQL exceptions (like PK/FK violations)
    -- and re-throw them, which mimics the original Oracle behavior.
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        -- Resignal the error to the calling application.
        RESIGNAL;
    END;

    -- Insert the new record into the COURSEIMAGE table.
    INSERT INTO COURSEIMAGE (ImageID, CourseID, ImagePath, Caption, SortOrder)
    VALUES (p_ImageID, p_CourseID, p_ImagePath, p_Caption, p_SortOrder);
END;
//

-- --------------------------------------------------------------------------------
-- Procedure to update an existing course image record.
-- --------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS UPDATE_IMAGE_PROC;
//
CREATE PROCEDURE UPDATE_IMAGE_PROC(
    IN p_ImageID    INT,
    IN p_CourseID   INT,
    IN p_ImagePath  VARCHAR(255),
    IN p_Caption    TEXT,
    IN p_SortOrder  INT
)
BEGIN
    -- Update the specified record.
    UPDATE COURSEIMAGE
    SET CourseID   = p_CourseID,
        ImagePath  = p_ImagePath,
        Caption    = p_Caption,
        SortOrder  = p_SortOrder
    WHERE ImageID = p_ImageID;

    -- Check if any row was actually updated. If not, the ImageID was not found.
    -- ROW_COUNT() in MySQL is similar to SQL%ROWCOUNT in Oracle.
    IF ROW_COUNT() = 0 THEN
        -- Signal a "not found" error, which is the MySQL equivalent of RAISE_APPLICATION_ERROR.
        -- SQLSTATE '45000' is a generic state for user-defined exceptions.
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'CourseImage with ImageID not found for update.';
    END IF;
END;
//

-- --------------------------------------------------------------------------------
-- Procedure to delete (unlink) a course image record.
-- --------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS UNLINK_IMAGE_COURSE_PROC;
//
CREATE PROCEDURE UNLINK_IMAGE_COURSE_PROC(
    IN p_ImageID  INT,
    IN p_CourseID INT
)
BEGIN
    -- Delete the record that matches both ImageID and CourseID.
    DELETE FROM COURSEIMAGE
    WHERE ImageID = p_ImageID AND CourseID = p_CourseID;

    -- Check if a row was deleted. If not, the record was not found.
    IF ROW_COUNT() = 0 THEN
        -- Signal a "not found" error.
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'CourseImage with specified ImageID and CourseID not found for deletion.';
    END IF;
END;
//

-- --------------------------------------------------------------------------------
-- Procedure to get a single image by its ID.
-- In MySQL, procedures can return result sets directly, so a function is not needed.
-- --------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS GET_IMAGE_BY_IMAGE_ID;
//
CREATE PROCEDURE GET_IMAGE_BY_IMAGE_ID(
    IN p_ImageID INT
)
BEGIN
    -- Select the record and return it as a result set.
    -- DATE_FORMAT is the MySQL equivalent of Oracle's TO_CHAR for dates.
    SELECT
        ImageID,
        CourseID,
        ImagePath,
        Caption,
        SortOrder,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSEIMAGE
    WHERE
        ImageID = p_ImageID;
END;
//

-- --------------------------------------------------------------------------------
-- Procedure to get all images for a specific course, ordered by SortOrder.
-- --------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS GET_IMAGES_BY_COURSE_ID;
//
CREATE PROCEDURE GET_IMAGES_BY_COURSE_ID(
    IN p_CourseID INT
)
BEGIN
    -- Select all matching records and return them as a result set.
    SELECT
        ImageID,
        CourseID,
        ImagePath,
        Caption,
        SortOrder,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSEIMAGE
    WHERE
        CourseID = p_CourseID
    ORDER BY SortOrder ASC;
END;
//

-- Reset the delimiter back to the default semicolon.
DELIMITER ;
