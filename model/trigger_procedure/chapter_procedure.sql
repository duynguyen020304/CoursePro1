
DROP PROCEDURE IF EXISTS CREATE_CHAPTER_PROC;
DELIMITER $$

CREATE PROCEDURE CREATE_CHAPTER_PROC(
    IN p_ChapterID   VARCHAR(255),
    IN p_CourseID    VARCHAR(255),
    IN p_Title       VARCHAR(255),
    IN p_Description TEXT,
    IN p_SortOrder   INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);

    INSERT IGNORE INTO COURSECHAPTER (ChapterID, CourseID, Title, Description, SortOrder)
    VALUES (p_ChapterID, p_CourseID, p_Title, p_Description, p_SortOrder);

    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT('Chapter with ChapterID ''', p_ChapterID, ''' already exists.');
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$

DROP PROCEDURE IF EXISTS UPDATE_CHAPTER_PROC;
DELIMITER $$

CREATE PROCEDURE UPDATE_CHAPTER_PROC(
    IN p_ChapterID   VARCHAR(255),
    IN p_CourseID    VARCHAR(255),
    IN p_Title       VARCHAR(255),
    IN p_Description TEXT,
    IN p_SortOrder   INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);

    UPDATE COURSECHAPTER
    SET CourseID    = p_CourseID,
        Title       = p_Title,
        Description = p_Description,
        SortOrder   = p_SortOrder
    WHERE ChapterID = p_ChapterID;

    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT('Chapter with ChapterID ''', p_ChapterID, ''' not found for update.');
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$

DROP PROCEDURE IF EXISTS DELETE_CHAPTER_PROC;
DELIMITER $$

CREATE PROCEDURE DELETE_CHAPTER_PROC(
    IN p_ChapterID VARCHAR(255)
)
BEGIN
    DECLARE v_error_message VARCHAR(255);

    DELETE FROM COURSECHAPTER
    WHERE ChapterID = p_ChapterID;

    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT('Chapter with ChapterID ''', p_ChapterID, ''' not found for deletion.');
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$

DROP PROCEDURE IF EXISTS GET_ALL_CHAPTERS_PROC;
DELIMITER $$

CREATE PROCEDURE GET_ALL_CHAPTERS_PROC()
BEGIN
    SELECT
        ChapterID,
        CourseID,
        Title,
        Description,
        SortOrder,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSECHAPTER
    ORDER BY SortOrder ASC, Title ASC;
END$$

DROP PROCEDURE IF EXISTS GET_CHAPTER_BY_ID_PROC;
DELIMITER $$

CREATE PROCEDURE GET_CHAPTER_BY_ID_PROC(
    IN p_ChapterID VARCHAR(255)
)
BEGIN
    SELECT
        ChapterID,
        CourseID,
        Title,
        Description,
        SortOrder,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSECHAPTER
    WHERE
        ChapterID = p_ChapterID;
END$$

DROP PROCEDURE IF EXISTS GET_CHAPTERS_BY_COURSE_PROC;
DELIMITER $$

CREATE PROCEDURE GET_CHAPTERS_BY_COURSE_PROC(
    IN p_CourseID VARCHAR(255)
)
BEGIN
    SELECT
        ChapterID,
        CourseID,
        Title,
        Description,
        SortOrder,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSECHAPTER
    WHERE
        CourseID = p_CourseID
    ORDER BY SortOrder ASC;
END$$

DELIMITER ;