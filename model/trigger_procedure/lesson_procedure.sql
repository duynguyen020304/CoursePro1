-- MySQL Stored Procedures for Course Lessons
-- This script converts the Oracle PL/SQL COURSE_LESSON_PKG
-- into individual, MySQL-compatible stored procedures.

-- Delimiter is changed to $$ to allow for semicolons within the procedure bodies.
DELIMITER $$

-- -----------------------------------------------------------------------------
-- Procedure to create a new lesson.
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS CREATE_LESSON_PROC$$

CREATE PROCEDURE CREATE_LESSON_PROC(
    IN p_LessonID VARCHAR(255),
    IN p_CourseID VARCHAR(255),
    IN p_ChapterID VARCHAR(255),
    IN p_Title VARCHAR(255),
    IN p_Content TEXT,
    IN p_SortOrder INT
)
BEGIN
    -- Declare a handler for duplicate key errors (MySQL error 1062).
    -- This replaces Oracle's DUP_VAL_ON_INDEX exception.
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        SET @message = CONCAT('Lesson with LessonID ''', p_LessonID, ''' already exists.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END;

    -- Insert the new lesson record.
    INSERT INTO COURSELESSON (LessonID, CourseID, ChapterID, Title, Content, SortOrder)
    VALUES (p_LessonID, p_CourseID, p_ChapterID, p_Title, p_Content, p_SortOrder);
END$$

-- -----------------------------------------------------------------------------
-- Procedure to delete a lesson by its ID.
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS DELETE_LESSON_PROC$$

CREATE PROCEDURE DELETE_LESSON_PROC(
    IN p_LessonID VARCHAR(255)
)
BEGIN
    -- Delete the specified lesson.
    DELETE FROM COURSELESSON
    WHERE LessonID = p_LessonID;

    -- Check if a row was actually deleted. ROW_COUNT() is the MySQL
    -- equivalent of Oracle's SQL%ROWCOUNT.
    IF ROW_COUNT() = 0 THEN
        SET @message = CONCAT('Lesson with LessonID ''', p_LessonID, ''' not found for deletion.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END IF;
END$$

-- -----------------------------------------------------------------------------
-- Procedure to update an existing lesson.
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS UPDATE_LESSON_PROC$$

CREATE PROCEDURE UPDATE_LESSON_PROC(
    IN p_LessonID VARCHAR(255),
    IN p_CourseID VARCHAR(255),
    IN p_ChapterID VARCHAR(255),
    IN p_Title VARCHAR(255),
    IN p_Content TEXT,
    IN p_SortOrder INT
)
BEGIN
    -- Update the specified lesson record.
    UPDATE COURSELESSON
    SET CourseID  = p_CourseID,
        ChapterID = p_ChapterID,
        Title     = p_Title,
        Content   = p_Content,
        SortOrder = p_SortOrder
    WHERE LessonID = p_LessonID;

    -- Check if a row was actually updated.
    IF ROW_COUNT() = 0 THEN
        SET @message = CONCAT('Lesson with LessonID ''', p_LessonID, ''' not found for update.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END IF;
END$$

-- -----------------------------------------------------------------------------
-- Procedure to get a single lesson by its ID.
-- Note: In MySQL, procedures can return result sets directly,
-- so we convert the Oracle function with SYS_REFCURSOR to a simple SELECT.
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS GET_LESSON_BY_ID_PROC$$

CREATE PROCEDURE GET_LESSON_BY_ID_PROC(
    IN p_LessonID VARCHAR(255)
)
BEGIN
    -- Select the lesson and format the timestamp.
    -- DATE_FORMAT is the MySQL equivalent of Oracle's TO_CHAR for dates.
    SELECT
        LessonID,
        CourseID,
        ChapterID,
        Title,
        Content,
        SortOrder,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSELESSON
    WHERE
        LessonID = p_LessonID;
END$$

-- -----------------------------------------------------------------------------
-- Procedure to get all lessons for a specific chapter, sorted by SortOrder.
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS GET_LESSONS_BY_CHAPTER_PROC$$

CREATE PROCEDURE GET_LESSONS_BY_CHAPTER_PROC(
    IN p_ChapterID VARCHAR(255)
)
BEGIN
    -- Select the lessons and format the timestamp.
    SELECT
        LessonID,
        CourseID,
        ChapterID,
        Title,
        Content,
        SortOrder,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSELESSON
    WHERE
        ChapterID = p_ChapterID
    ORDER BY SortOrder ASC;
END$$

-- -----------------------------------------------------------------------------
-- Procedure to get all lessons for a specific course, sorted by chapter and then lesson order.
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS GET_LESSONS_BY_COURSE_PROC$$

CREATE PROCEDURE GET_LESSONS_BY_COURSE_PROC(
    IN p_CourseID VARCHAR(255)
)
BEGIN
    -- Select the lessons and format the timestamp.
    SELECT
        LessonID,
        CourseID,
        ChapterID,
        Title,
        Content,
        SortOrder,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSELESSON
    WHERE
        CourseID = p_CourseID
    ORDER BY ChapterID ASC, SortOrder ASC;
END$$

-- Reset the delimiter back to the default semicolon.
DELIMITER ;
