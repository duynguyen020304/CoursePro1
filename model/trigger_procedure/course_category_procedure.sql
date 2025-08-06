
DROP PROCEDURE IF EXISTS LINK_COURSE_CATEGORY_PROC;
DELIMITER //

CREATE PROCEDURE LINK_COURSE_CATEGORY_PROC(
    IN p_CourseID   VARCHAR(255),
    IN p_CategoryID INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT('Link between CourseID ''', p_CourseID, ''' and CategoryID ', p_CategoryID, ' already exists.');
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
    INSERT INTO COURSECATEGORY (CourseID, CategoryID)
    VALUES (p_CourseID, p_CategoryID);
END //

DROP PROCEDURE IF EXISTS UNLINK_COURSE_CATEGORY_PROC;
DELIMITER //

CREATE PROCEDURE UNLINK_COURSE_CATEGORY_PROC(
    IN p_CourseID   VARCHAR(255),
    IN p_CategoryID INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DELETE FROM COURSECATEGORY
    WHERE CourseID = p_CourseID AND CategoryID = p_CategoryID;

    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT('Link between CourseID ''', p_CourseID, ''' and CategoryID ', p_CategoryID, ' not found, or no rows deleted.');
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;

    END IF;
END //

DROP PROCEDURE IF EXISTS GET_CATEGORIES_BY_COURSE_PROC;
DELIMITER //

CREATE PROCEDURE GET_CATEGORIES_BY_COURSE_PROC(
    IN p_CourseID VARCHAR(255)
)
BEGIN
    SELECT
        cc.CourseID,
        cc.CategoryID,
        DATE_FORMAT(cc.created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSECATEGORY cc
    WHERE
        cc.CourseID = p_CourseID
    ORDER BY cc.CategoryID ASC;
END //

DROP PROCEDURE IF EXISTS GET_COURSES_BY_CATEGORY_PROC;
DELIMITER //

CREATE PROCEDURE GET_COURSES_BY_CATEGORY_PROC(
    IN p_CategoryID INT
)
BEGIN
    SELECT
        cc.CourseID,
        cc.CategoryID,
        DATE_FORMAT(cc.created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        COURSECATEGORY cc
    WHERE
        cc.CategoryID = p_CategoryID
    ORDER BY cc.CourseID ASC;
END //

DROP FUNCTION IF EXISTS LINK_EXISTS_FUNC;
DELIMITER //

CREATE FUNCTION LINK_EXISTS_FUNC(
    p_CourseID   VARCHAR(255),
    p_CategoryID INT
)
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_count INT;

    SELECT COUNT(*)
    INTO v_count
    FROM COURSECATEGORY
    WHERE CourseID = p_CourseID AND CategoryID = p_CategoryID;

    IF v_count > 0 THEN
        RETURN 1;
    ELSE
        RETURN 0;
    END IF;
END //

DELIMITER ;