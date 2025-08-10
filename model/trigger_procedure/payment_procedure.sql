DROP PROCEDURE IF EXISTS CREATE_ORDER_PROC;
DELIMITER $$
CREATE PROCEDURE CREATE_ORDER_PROC(
    IN p_OrderID     INT,
    IN p_UserID      INT,
    IN p_OrderDate   DATETIME,
    IN p_TotalAmount DECIMAL(10, 2)
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT('Order with OrderID ''', p_OrderID, ''' already exists.');
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message
    END IF;

    INSERT INTO ORDERS (OrderID, UserID, OrderDate, TotalAmount)
    VALUES (p_OrderID, p_UserID, p_OrderDate, p_TotalAmount);
END$$

DROP PROCEDURE IF EXISTS CREATE_ORDER_PROC;
DELIMITER $$
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

DROP PROCEDURE IF EXISTS CREATE_ORDER_PROC;
DELIMITER $$
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

DROP PROCEDURE IF EXISTS CREATE_ORDER_PROC;
DELIMITER $$
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
        SET v_error_message = CONCAT('Order with OrderID ''', p_OrderID, ''' not found for update.');
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message
    END IF;
END$$

DROP PROCEDURE IF EXISTS CREATE_ORDER_PROC;
DELIMITER $$
CREATE PROCEDURE DELETE_ORDER_PROC(
    IN p_OrderID INT
)
BEGIN
    DELETE FROM ORDERS
    WHERE OrderID = p_OrderID;

    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = CONCAT('Order with OrderID ''', p_OrderID, ''' not found for deletion.');
    END IF;
END$$

DROP PROCEDURE IF EXISTS CREATE_ORDER_PROC;
DELIMITER $$
CREATE PROCEDURE CREATE_PAYMENT_PROC(
    IN p_PaymentID     INT,
    IN p_OrderID       INT,
    IN p_PaymentDate   DATETIME,
    IN p_PaymentMethod VARCHAR(50),
    IN p_PaymentStatus VARCHAR(50),
    IN p_Amount        DECIMAL(10, 2)
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    BEGIN
        IF ROW_COUNT() == 0 then
            SET MESSAGE_TEXT = CONCAT('Payment with PaymentID ''', p_PaymentID, ''' already exists.');
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = v_error_message
    END;

    IF p_Amount < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Payment amount cannot be negative.';
    END IF;

    INSERT INTO PAYMENT (PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount)
    VALUES (p_PaymentID, p_OrderID, p_PaymentDate, p_PaymentMethod, p_PaymentStatus, p_Amount);
END$$

DROP PROCEDURE IF EXISTS GET_PAYMENT_BY_ORDER_ID_PROC;
DELIMITER $$
CREATE PROCEDURE GET_PAYMENT_BY_ORDER_ID_PROC(
    IN p_OrderID INT
)
BEGIN
    SELECT
        PaymentID,
        OrderID,
        PaymentMethod,
        PaymentStatus,
        Amount,
        DATE_FORMAT(PaymentDate, '%Y-%m-%d %H:%i:%s.%f') AS payment_date_formatted,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        PAYMENT
    WHERE
        OrderID = p_OrderID;
END$$

DROP PROCEDURE IF EXISTS GET_PAYMENT_BY_ID_PROC;
DELIMITER $$
CREATE PROCEDURE GET_PAYMENT_BY_ID_PROC(
    IN p_PaymentID INT
)
BEGIN
    SELECT
        PaymentID,
        OrderID,
        PaymentMethod,
        PaymentStatus,
        Amount,
        DATE_FORMAT(PaymentDate, '%Y-%m-%d %H:%i:%s.%f') AS payment_date_formatted,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        PAYMENT
    WHERE
        PaymentID = p_PaymentID;
END$$

DROP PROCEDURE IF EXISTS UPDATE_PAYMENT_PROC;
DELIMITER $$
CREATE PROCEDURE UPDATE_PAYMENT_PROC(
    IN p_PaymentID     INT,
    IN p_OrderID       INT,
    IN p_PaymentDate   DATETIME,
    IN p_PaymentMethod VARCHAR(50),
    IN p_PaymentStatus VARCHAR(50),
    IN p_Amount        DECIMAL(10, 2)
)
BEGIN
    IF p_Amount < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Payment amount cannot be negative for update.';
    END IF;

    UPDATE PAYMENT
    SET OrderID       = p_OrderID,
        PaymentDate   = p_PaymentDate,
        PaymentMethod = p_PaymentMethod,
        PaymentStatus = p_PaymentStatus,
        Amount        = p_Amount
    WHERE PaymentID = p_PaymentID;

    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = CONCAT('Payment with PaymentID ''', p_PaymentID, ''' not found for update.');
    END IF;
END$$

DROP PROCEDURE IF EXISTS DELETE_PAYMENT_PROC;
DELIMITER $$
CREATE PROCEDURE DELETE_PAYMENT_PROC(
    IN p_PaymentID INT
)
BEGIN
    DELETE FROM PAYMENT
    WHERE PaymentID = p_PaymentID;

    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = CONCAT('Payment with PaymentID ''', p_PaymentID, ''' not found for deletion.');
    END IF;
END$$

DELIMITER ;