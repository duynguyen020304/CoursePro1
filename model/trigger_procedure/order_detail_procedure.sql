-- SQL for MySQL Database
-- This script provides stored procedures for managing order details,
-- converted from an Oracle PL/SQL package.

-- Drop procedures if they already exist to allow for recreation.
DROP PROCEDURE IF EXISTS ADD_DETAIL_PROC;
DROP PROCEDURE IF EXISTS GET_DETAILS_BY_ORDER_PROC;
DROP PROCEDURE IF EXISTS UPDATE_DETAIL_PROC;
DROP PROCEDURE IF EXISTS GET_DETAIL_PROC;
DROP PROCEDURE IF EXISTS DELETE_DETAIL_PROC;

DELIMITER $$

-- Procedure to add a new order detail record.
-- It prevents adding details with negative prices and handles duplicate entries.
CREATE PROCEDURE ADD_DETAIL_PROC(
    IN p_OrderID  INT,
    IN p_CourseID INT,
    IN p_Price    DECIMAL(10, 2)
)
BEGIN
    -- Declare a handler for duplicate key errors (MySQL error code 1062).
    -- This is equivalent to Oracle's DUP_VAL_ON_INDEX exception.
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        SET @message = CONCAT('OrderDetail for OrderID ''', p_OrderID, ''' and CourseID ''', p_CourseID, ''' already exists.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END;

    -- Validate that the price is not negative.
    IF p_Price < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Price cannot be negative.';
    END IF;

    -- Insert the new order detail into the table.
    INSERT INTO ORDERDETAIL (OrderID, CourseID, Price)
    VALUES (p_OrderID, p_CourseID, p_Price);
END$$

-- Procedure to retrieve all details for a specific order.
-- In MySQL, procedures can return result sets directly by using a SELECT statement.
CREATE PROCEDURE GET_DETAILS_BY_ORDER_PROC(
    IN p_OrderID INT
)
BEGIN
    -- Select the records, formatting the timestamp for consistency.
    -- DATE_FORMAT is the MySQL equivalent of Oracle's TO_CHAR for dates.
    SELECT
        OrderID,
        CourseID,
        Price,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        ORDERDETAIL
    WHERE
        OrderID = p_OrderID
    ORDER BY CourseID ASC;
END$$

-- Procedure to update the price of an existing order detail.
-- It checks if the record exists before updating.
CREATE PROCEDURE UPDATE_DETAIL_PROC(
    IN p_OrderID  INT,
    IN p_CourseID INT,
    IN p_Price    DECIMAL(10, 2)
)
BEGIN
    -- Validate that the new price is not negative.
    IF p_Price < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Price cannot be negative for update.';
    END IF;

    -- Update the specified order detail.
    UPDATE ORDERDETAIL
    SET Price = p_Price
    WHERE OrderID = p_OrderID AND CourseID = p_CourseID;

    -- Check if any row was actually updated.
    -- ROW_COUNT() is the MySQL equivalent of Oracle's SQL%ROWCOUNT.
    IF ROW_COUNT() = 0 THEN
        SET @message = CONCAT('OrderDetail for OrderID ''', p_OrderID, ''' and CourseID ''', p_CourseID, ''' not found for update.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END IF;
END$$

-- Procedure to retrieve a single, specific order detail.
CREATE PROCEDURE GET_DETAIL_PROC(
    IN p_OrderID  INT,
    IN p_CourseID INT
)
BEGIN
    -- Select the specific record.
    SELECT
        OrderID,
        CourseID,
        Price,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS created_at_formatted
    FROM
        ORDERDETAIL
    WHERE
        OrderID = p_OrderID AND CourseID = p_CourseID;
END$$

-- Procedure to delete a specific order detail.
-- It checks if the record exists before attempting deletion.
CREATE PROCEDURE DELETE_DETAIL_PROC(
    IN p_OrderID  INT,
    IN p_CourseID INT
)
BEGIN
    -- Delete the specified record.
    DELETE FROM ORDERDETAIL
    WHERE OrderID = p_OrderID AND CourseID = p_CourseID;

    -- Check if a row was actually deleted.
    IF ROW_COUNT() = 0 THEN
        SET @message = CONCAT('OrderDetail for OrderID ''', p_OrderID, ''' and CourseID ''', p_CourseID, ''' not found for deletion.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END IF;
END$$

DELIMITER ;
