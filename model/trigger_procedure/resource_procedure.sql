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
    DECLARE v_error_message VARCHAR(255);

    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        SET v_error_message = CONCAT(
            'Resource with ResourceID ''',
            p_ResourceID,
            ''' already exists.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END;

    INSERT INTO COURSERESOURCE (
        ResourceID, LessonID, ResourcePath, Title, SortOrder
    )
    VALUES (
        p_ResourceID, p_LessonID, p_ResourcePath, p_Title, p_SortOrder
    );
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS GET_RESOURCE_BY_ID_PROC;
DELIMITER $$
CREATE PROCEDURE GET_RESOURCE_BY_ID_PROC(
    IN p_ResourceID VARCHAR(255)
)
BEGIN
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

DROP PROCEDURE IF EXISTS GET_RESOURCES_BY_LESSON_PROC;
DELIMITER $$
CREATE PROCEDURE GET_RESOURCES_BY_LESSON_PROC(
    IN p_LessonID VARCHAR(255)
)
BEGIN
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

DROP PROCEDURE IF EXISTS GET_ALL_RESOURCES_PROC;
DELIMITER $$
CREATE PROCEDURE GET_ALL_RESOURCES_PROC()
BEGIN
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
    DECLARE v_error_message VARCHAR(255);

    UPDATE COURSERESOURCE
    SET LessonID     = p_LessonID,
        ResourcePath = p_ResourcePath,
        Title        = p_Title,
        SortOrder    = p_SortOrder
    WHERE ResourceID = p_ResourceID;

    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT(
            'Resource with ResourceID ''',
            p_ResourceID,
            ''' not found for update.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS DELETE_RESOURCE_PROC;
DELIMITER $$
CREATE PROCEDURE DELETE_RESOURCE_PROC(
    IN p_ResourceID VARCHAR(255)
)
BEGIN
    DECLARE v_error_message VARCHAR(255);

    DELETE FROM COURSERESOURCE
    WHERE ResourceID = p_ResourceID;

    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT(
            'Resource with ResourceID ''',
            p_ResourceID,
            ''' not found for deletion.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$
DELIMITER ;