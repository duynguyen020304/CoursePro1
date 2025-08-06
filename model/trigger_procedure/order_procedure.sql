DELIMITER $$

CREATE PROCEDURE CREATE_ORDER_PROC(
    IN p_OrderID     INT,
    IN p_UserID      INT,
    IN p_OrderDate   DATETIME,
    IN p_TotalAmount DECIMAL(10, 2)
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DECLARE EXIT HANDLER FOR 1062
    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT('Order with OrderID ''', p_OrderID, ''' already exists.');
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
    INSERT INTO ORDERS (OrderID, UserID, OrderDate, TotalAmount)
    VALUES (p_OrderID, p_UserID, p_OrderDate, p_TotalAmount);
END$$

CREATE PROCEDURE GET_ORDER_BY_ID_PROC(
    IN p_OrderID INT
)
BEGIN
    SELECT
        OrderID,
        UserID,
        TotalAmount,
        DATE_FORMAT(OrderDate, '%Y-%m-%d %H:%i:%s.%f') AS order_date_formatted,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        ORDERS
    WHERE
        OrderID = p_OrderID;
END$$

CREATE PROCEDURE GET_ORDERS_BY_USER_PROC(
    IN p_UserID INT
)
BEGIN
    SELECT
        OrderID,
        UserID,
        TotalAmount,
        DATE_FORMAT(OrderDate, '%Y-%m-%d %H:%i:%s.%f') AS order_date_formatted,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        ORDERS
    WHERE
        UserID = p_UserID
    ORDER BY OrderDate DESC;
END$$

CREATE PROCEDURE UPDATE_ORDER_PROC(
    IN p_OrderID     INT,
    IN p_UserID      INT,
    IN p_OrderDate   DATETIME,
    IN p_TotalAmount DECIMAL(10, 2)
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    UPDATE ORDERS
    SET UserID = p_UserID,
        OrderDate = p_OrderDate,
        TotalAmount = p_TotalAmount
    WHERE OrderID = p_OrderID;

    IF ROW_COUNT() = 0 THEN
        SET MESSAGE_TEXT = CONCAT('Order with OrderID ''', p_OrderID, ''' not found for update.');
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$

CREATE PROCEDURE DELETE_ORDER_PROC(
    IN p_OrderID INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DELETE FROM ORDERS
    WHERE OrderID = p_OrderID;
    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT('Order with OrderID ''', p_OrderID, ''' not found for deletion.');
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$

DELIMITER ;