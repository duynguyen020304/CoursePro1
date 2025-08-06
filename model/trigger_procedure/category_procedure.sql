DROP PROCEDURE IF EXISTS CREATE_CATEGORY_PROC;
DELIMITER $$
CREATE PROCEDURE CREATE_CATEGORY_PROC(
    IN p_Name       VARCHAR(255),
    IN p_Parent_ID  INT,
    IN p_Sort_Order INT
)
BEGIN
    INSERT INTO CATEGORIES (name, parent_id, sort_order)
    VALUES (p_Name, p_Parent_ID, p_Sort_Order);
END$$
DELIMITER ;
DROP PROCEDURE IF EXISTS DELETE_CATEGORY_PROC;
DELIMITER $$
CREATE PROCEDURE DELETE_CATEGORY_PROC(
    IN p_ID INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DELETE FROM CATEGORIES
    WHERE id = p_ID;
    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT('Category with ID ', p_ID, ' not found, or no rows deleted.');
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$
DELIMITER ;
DROP PROCEDURE IF EXISTS UPDATE_CATEGORY_PROC;
DELIMITER $$
CREATE PROCEDURE UPDATE_CATEGORY_PROC(
    IN p_ID         INT,
    IN p_Name       VARCHAR(255),
    IN p_Parent_ID  INT,
    IN p_Sort_Order INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    UPDATE CATEGORIES
    SET name = p_Name,
        parent_id = p_Parent_ID,
        sort_order = p_Sort_Order
    WHERE id = p_ID;
    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT('Category with ID ', p_ID, ' not found for update.');
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$
DELIMITER ;
DROP PROCEDURE IF EXISTS GET_CATEGORY_BY_ID_PROC;
DELIMITER $$
CREATE PROCEDURE GET_CATEGORY_BY_ID_PROC(
    IN p_ID INT
)
BEGIN
    SELECT
        id,
        name,
        parent_id,
        sort_order,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        CATEGORIES
    WHERE
        id = p_ID;
END$$
DELIMITER ;
DROP PROCEDURE IF EXISTS GET_ALL_CATEGORIES_PROC;
DELIMITER $$
CREATE PROCEDURE GET_ALL_CATEGORIES_PROC()
BEGIN
    SELECT
        id,
        name,
        parent_id,
        sort_order,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        CATEGORIES
    ORDER BY sort_order ASC, name ASC;
END$$
DELIMITER ;