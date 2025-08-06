DROP PROCEDURE IF EXISTS CREATE_ITEM_PROC;
DELIMITER $$
CREATE PROCEDURE CREATE_ITEM_PROC(
    IN p_CartItemID VARCHAR(255),
    IN p_CartID     VARCHAR(255),
    IN p_CourseID   VARCHAR(255),
    IN p_Quantity   INT
)
BEGIN
    IF p_Quantity <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Quantity must be greater than 0.';
    END IF;
    INSERT INTO CARTITEM (CartItemID, CartID, CourseID, Quantity)
    VALUES (p_CartItemID, p_CartID, p_CourseID, p_Quantity);
END$$
DELIMITER ;
DROP PROCEDURE IF EXISTS GET_ITEMS_BY_CART_PROC;
DELIMITER $$
CREATE PROCEDURE GET_ITEMS_BY_CART_PROC(
    IN p_CartID VARCHAR(255)
)
BEGIN
    SELECT
        CartItemID,
        CartID,
        CourseID,
        Quantity,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        CARTITEM
    WHERE
        CartID = p_CartID
    ORDER BY CourseID ASC;
END$$
DELIMITER ;
DROP PROCEDURE IF EXISTS DELETE_ITEM_PROC;
DELIMITER $$
CREATE PROCEDURE DELETE_ITEM_PROC(
    IN p_CartItemID VARCHAR(255)
)
BEGIN
    DELETE FROM CARTITEM
    WHERE CartItemID = p_CartItemID;
    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'CartItem not found, or no rows were deleted.';
    END IF;
END$$
DELIMITER ;
DROP PROCEDURE IF EXISTS GET_ITEM_BY_ID_PROC;
DELIMITER $$
CREATE PROCEDURE GET_ITEM_BY_ID_PROC(
    IN p_CartItemID VARCHAR(255)
)
BEGIN
    SELECT
        CartItemID,
        CartID,
        CourseID,
        Quantity,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        CARTITEM
    WHERE
        CartItemID = p_CartItemID;
END$$
DELIMITER ;
DROP PROCEDURE IF EXISTS UPDATE_ITEM_QUANTITY_PROC;
DELIMITER $$
CREATE PROCEDURE UPDATE_ITEM_QUANTITY_PROC(
    IN p_CartItemID VARCHAR(255),
    IN p_Quantity   INT
)
BEGIN
    IF p_Quantity <= 0 THEN
        CALL DELETE_ITEM_PROC(p_CartItemID);
    ELSE
        UPDATE CARTITEM
        SET Quantity = p_Quantity
        WHERE CartItemID = p_CartItemID;
        IF ROW_COUNT() = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'CartItem not found for update.';
        END IF;
    END IF;
END$$
DELIMITER ;
DROP PROCEDURE IF EXISTS CLEAR_CART_PROC;
DELIMITER $$
CREATE PROCEDURE CLEAR_CART_PROC(
    IN p_CartID VARCHAR(255)
)
BEGIN
    DELETE FROM CARTITEM
    WHERE CartID = p_CartID;
END$$
DELIMITER ;