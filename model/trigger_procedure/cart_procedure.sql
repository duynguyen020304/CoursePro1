DROP PROCEDURE IF EXISTS CREATE_CART_PROC;
DELIMITER $$
CREATE PROCEDURE CREATE_CART_PROC(
    IN p_CartID VARCHAR(255),
    IN p_UserID VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    INSERT INTO CART (CartID, UserID)
    VALUES (p_CartID, p_UserID);
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS GET_CART_BY_USER_PROC;
DELIMITER $$
CREATE PROCEDURE GET_CART_BY_USER_PROC(
    IN p_UserID VARCHAR(255)
)
BEGIN
    SELECT
        CartID,
        UserID,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        CART
    WHERE
        UserID = p_UserID;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS GET_CART_BY_ID_PROC;
DELIMITER $$
CREATE PROCEDURE GET_CART_BY_ID_PROC(
    IN p_CartID VARCHAR(255)
)
BEGIN
    SELECT
        CartID,
        UserID,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        CART
    WHERE
        CartID = p_CartID;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS DELETE_CART_PROC;
DELIMITER $$
CREATE PROCEDURE DELETE_CART_PROC(
    IN p_CartID VARCHAR(255)
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DELETE FROM CART
    WHERE CartID = p_CartID;
    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT('Cart with ID ''', p_CartID, ''' not found, or no rows deleted.');
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$
DELIMITER ;