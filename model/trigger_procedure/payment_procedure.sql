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
    DECLARE v_exists INT DEFAULT 0;

    -- Kiểm tra OrderID đã tồn tại
    SELECT COUNT(*) INTO v_exists
    FROM `ORDERS`
    WHERE `OrderID` = p_OrderID;

    IF v_exists > 0 THEN
        SET v_error_message = CONCAT(
            'Order with OrderID ''',
            p_OrderID,
            ''' already exists.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    INSERT INTO `ORDERS` (
        `OrderID`, `UserID`, `OrderDate`, `TotalAmount`
    )
    VALUES (
        p_OrderID, p_UserID, p_OrderDate, p_TotalAmount
    );
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS GET_ORDER_BY_ID_PROC;
DELIMITER $$
CREATE PROCEDURE GET_ORDER_BY_ID_PROC(
    IN p_OrderID INT
)
BEGIN
    SELECT
        `OrderID`,
        `UserID`,
        `TotalAmount`,
        DATE_FORMAT(`OrderDate`, '%Y-%m-%d %H:%i:%s.%f') AS
            order_date_formatted,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS
            created_at_formatted
    FROM
        `ORDERS`
    WHERE
        `OrderID` = p_OrderID;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS GET_ORDERS_BY_USER_PROC;
DELIMITER $$
CREATE PROCEDURE GET_ORDERS_BY_USER_PROC(
    IN p_UserID INT
)
BEGIN
    SELECT
        `OrderID`,
        `UserID`,
        `TotalAmount`,
        DATE_FORMAT(`OrderDate`, '%Y-%m-%d %H:%i:%s.%f') AS
            order_date_formatted,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS
            created_at_formatted
    FROM
        `ORDERS`
    WHERE
        `UserID` = p_UserID
    ORDER BY `OrderDate` DESC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS UPDATE_ORDER_PROC;
DELIMITER $$
CREATE PROCEDURE UPDATE_ORDER_PROC(
    IN p_OrderID     INT,
    IN p_UserID      INT,
    IN p_OrderDate   DATETIME,
    IN p_TotalAmount DECIMAL(10, 2)
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DECLARE v_exists INT DEFAULT 0;

    -- Kiểm tra tồn tại trước khi cập nhật
    SELECT COUNT(*) INTO v_exists
    FROM `ORDERS`
    WHERE `OrderID` = p_OrderID;

    IF v_exists = 0 THEN
        SET v_error_message = CONCAT(
            'Order with OrderID ''',
            p_OrderID,
            ''' not found for update.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    UPDATE `ORDERS`
    SET `UserID` = p_UserID,
        `OrderDate` = p_OrderDate,
        `TotalAmount` = p_TotalAmount
    WHERE `OrderID` = p_OrderID;

    -- Bảo hiểm nếu có race-condition nhỏ
    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT(
            'Order with OrderID ''',
            p_OrderID,
            ''' not found for update.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS DELETE_ORDER_PROC;
DELIMITER $$
CREATE PROCEDURE DELETE_ORDER_PROC(
    IN p_OrderID INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DECLARE v_exists INT DEFAULT 0;
    DECLARE v_in_use INT DEFAULT 0;

    -- Kiểm tra tồn tại
    SELECT COUNT(*) INTO v_exists
    FROM `ORDERS`
    WHERE `OrderID` = p_OrderID;

    IF v_exists = 0 THEN
        SET v_error_message = CONCAT(
            'Order with OrderID ''',
            p_OrderID,
            ''' not found for deletion.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    -- Kiểm tra bảng PAYMENT có tham chiếu tới Order hay không
    SELECT COUNT(*) INTO v_in_use
    FROM `PAYMENT`
    WHERE `OrderID` = p_OrderID;

    IF v_in_use > 0 THEN
        SET v_error_message = CONCAT(
            'Cannot delete OrderID ''',
            p_OrderID,
            ''' as it is referenced by payments.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    DELETE FROM `ORDERS`
    WHERE `OrderID` = p_OrderID;

    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT(
            'Order with OrderID ''',
            p_OrderID,
            ''' not found for deletion.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS CREATE_PAYMENT_PROC;
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
    DECLARE v_exists INT DEFAULT 0;

    -- Kiểm tra PaymentID đã tồn tại
    SELECT COUNT(*) INTO v_exists
    FROM `PAYMENT`
    WHERE `PaymentID` = p_PaymentID;

    IF v_exists > 0 THEN
        SET v_error_message = CONCAT(
            'Payment with PaymentID ''',
            p_PaymentID,
            ''' already exists.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    -- Kiểm tra Order tồn tại (tham chiếu)
    SELECT COUNT(*) INTO v_exists
    FROM `ORDERS`
    WHERE `OrderID` = p_OrderID;

    IF v_exists = 0 THEN
        SET v_error_message = CONCAT(
            'Order with OrderID ''',
            p_OrderID,
            ''' not found.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    -- Kiểm tra số tiền hợp lệ
    IF p_Amount < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Payment amount cannot be negative.';
    END IF;

    INSERT INTO `PAYMENT` (
        `PaymentID`, `OrderID`, `PaymentDate`, `PaymentMethod`,
        `PaymentStatus`, `Amount`
    )
    VALUES (
        p_PaymentID, p_OrderID, p_PaymentDate, p_PaymentMethod,
        p_PaymentStatus, p_Amount
    );
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS GET_PAYMENT_BY_ORDER_ID_PROC;
DELIMITER $$
CREATE PROCEDURE GET_PAYMENT_BY_ORDER_ID_PROC(
    IN p_OrderID INT
)
BEGIN
    SELECT
        `PaymentID`,
        `OrderID`,
        `PaymentMethod`,
        `PaymentStatus`,
        `Amount`,
        DATE_FORMAT(`PaymentDate`, '%Y-%m-%d %H:%i:%s.%f') AS
            payment_date_formatted,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS
            created_at_formatted
    FROM
        `PAYMENT`
    WHERE
        `OrderID` = p_OrderID;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS GET_PAYMENT_BY_ID_PROC;
DELIMITER $$
CREATE PROCEDURE GET_PAYMENT_BY_ID_PROC(
    IN p_PaymentID INT
)
BEGIN
    SELECT
        `PaymentID`,
        `OrderID`,
        `PaymentMethod`,
        `PaymentStatus`,
        `Amount`,
        DATE_FORMAT(`PaymentDate`, '%Y-%m-%d %H:%i:%s.%f') AS
            payment_date_formatted,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS
            created_at_formatted
    FROM
        `PAYMENT`
    WHERE
        `PaymentID` = p_PaymentID;
END$$
DELIMITER ;

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
    DECLARE v_error_message VARCHAR(255);
    DECLARE v_exists INT DEFAULT 0;

    -- Kiểm tra Payment tồn tại
    SELECT COUNT(*) INTO v_exists
    FROM `PAYMENT`
    WHERE `PaymentID` = p_PaymentID;

    IF v_exists = 0 THEN
        SET v_error_message = CONCAT(
            'Payment with PaymentID ''',
            p_PaymentID,
            ''' not found for update.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    -- Kiểm tra số tiền hợp lệ
    IF p_Amount < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Payment amount cannot be negative for update.';
    END IF;

    UPDATE `PAYMENT`
    SET `OrderID`       = p_OrderID,
        `PaymentDate`   = p_PaymentDate,
        `PaymentMethod` = p_PaymentMethod,
        `PaymentStatus` = p_PaymentStatus,
        `Amount`        = p_Amount
    WHERE `PaymentID` = p_PaymentID;

    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT(
            'Payment with PaymentID ''',
            p_PaymentID,
            ''' not found for update.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS DELETE_PAYMENT_PROC;
DELIMITER $$
CREATE PROCEDURE DELETE_PAYMENT_PROC(
    IN p_PaymentID INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DECLARE v_exists INT DEFAULT 0;

    -- Kiểm tra tồn tại trước khi xóa
    SELECT COUNT(*) INTO v_exists
    FROM `PAYMENT`
    WHERE `PaymentID` = p_PaymentID;

    IF v_exists = 0 THEN
        SET v_error_message = CONCAT(
            'Payment with PaymentID ''',
            p_PaymentID,
            ''' not found for deletion.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    DELETE FROM `PAYMENT`
    WHERE `PaymentID` = p_PaymentID;

    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT(
            'Payment with PaymentID ''',
            p_PaymentID,
            ''' not found for deletion.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$
DELIMITER ;