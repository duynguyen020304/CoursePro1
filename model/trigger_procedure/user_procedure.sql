DELIMITER $$

CREATE PROCEDURE `CREATE_USER_PROC`(
    IN p_UserID       INT,
    IN p_FirstName    VARCHAR(255),
    IN p_LastName     VARCHAR(255),
    IN p_Email        VARCHAR(255),
    IN p_Password     VARCHAR(255),
    IN p_RoleID       INT,
    IN p_ProfileImage VARCHAR(255)
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DECLARE v_exists INT DEFAULT 0;

    -- Kiểm tra UserID đã tồn tại
    SELECT COUNT(*) INTO v_exists
    FROM `USERS`
    WHERE `UserID` = p_UserID;

    IF v_exists > 0 THEN
        SET v_error_message = CONCAT(
            'User with UserID ''',
            p_UserID,
            ''' already exists.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    -- Kiểm tra Email đã tồn tại
    SELECT COUNT(*) INTO v_exists
    FROM `USERS`
    WHERE `Email` = p_Email;

    IF v_exists > 0 THEN
        SET v_error_message = CONCAT(
            'User with Email ''',
            p_Email,
            ''' already exists.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    INSERT INTO `USERS` (
        `UserID`,
        `FirstName`,
        `LastName`,
        `Email`,
        `Password`,
        `RoleID`,
        `ProfileImage`
    )
    VALUES (
        p_UserID,
        p_FirstName,
        p_LastName,
        p_Email,
        p_Password,
        p_RoleID,
        p_ProfileImage
    );
END$$

CREATE PROCEDURE `GET_USER_FOR_AUTH_PROC`(
    IN p_Email VARCHAR(255)
)
BEGIN
    SELECT
        `UserID`,
        `FirstName`,
        `LastName`,
        `Email`,
        `Password`,
        `RoleID`,
        `ProfileImage`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS
            `created_at_formatted`
    FROM
        `USERS`
    WHERE
        `Email` = p_Email;
END$$

CREATE PROCEDURE `DELETE_USER_PROC`(
    IN p_UserID INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DECLARE v_exists INT DEFAULT 0;
    DECLARE v_in_use INT DEFAULT 0;

    -- Kiểm tra tồn tại user
    SELECT COUNT(*) INTO v_exists
    FROM `USERS`
    WHERE `UserID` = p_UserID;

    IF v_exists = 0 THEN
        SET v_error_message = CONCAT(
            'User with UserID ''',
            p_UserID,
            ''' not found for deletion.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    -- Kiểm tra các bảng có thể tham chiếu tới USERS (ví dụ ORDERS)
    SELECT COUNT(*) INTO v_in_use
    FROM `ORDERS`
    WHERE `UserID` = p_UserID;

    IF v_in_use > 0 THEN
        SET v_error_message = CONCAT(
            'Cannot delete UserID ''',
            p_UserID,
            ''' as it is referenced by other records.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    -- Kiểm tra bảng STUDENT (nếu có tham chiếu)
    SELECT COUNT(*) INTO v_in_use
    FROM `STUDENT`
    WHERE `UserID` = p_UserID;

    IF v_in_use > 0 THEN
        SET v_error_message = CONCAT(
            'Cannot delete UserID ''',
            p_UserID,
            ''' as it is referenced by other records.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    DELETE FROM `USERS`
    WHERE `UserID` = p_UserID;

    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT(
            'User with UserID ''',
            p_UserID,
            ''' not found for deletion.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$

CREATE PROCEDURE `UPDATE_USER_PROC`(
    IN p_UserID        INT,
    IN p_FirstName     VARCHAR(255),
    IN p_LastName      VARCHAR(255),
    IN p_Password      VARCHAR(255),
    IN p_RoleID        INT,
    IN p_ProfileImage  VARCHAR(255)
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DECLARE v_exists INT DEFAULT 0;

    -- Kiểm tra tồn tại user trước khi cập nhật
    SELECT COUNT(*) INTO v_exists
    FROM `USERS`
    WHERE `UserID` = p_UserID;

    IF v_exists = 0 THEN
        SET v_error_message = CONCAT(
            'User with UserID ''',
            p_UserID,
            ''' not found for update.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    UPDATE `USERS`
    SET
        `FirstName`    = IFNULL(p_FirstName, `FirstName`),
        `LastName`     = IFNULL(p_LastName, `LastName`),
        `Password`     = IFNULL(p_Password, `Password`),
        `RoleID`       = IFNULL(p_RoleID, `RoleID`),
        `ProfileImage` = IFNULL(p_ProfileImage, `ProfileImage`)
    WHERE
        `UserID` = p_UserID;

    IF ROW_COUNT() = 0 THEN
        -- Bảo hiểm chống race condition: nếu bị xóa giữa chừng
        SET v_error_message = CONCAT(
            'User with UserID ''',
            p_UserID,
            ''' not found for update.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$

CREATE PROCEDURE `GET_USER_BY_ID_PROC`(
    IN p_UserID INT
)
BEGIN
    SELECT
        `UserID`,
        `FirstName`,
        `LastName`,
        `Email`,
        `Password`,
        `RoleID`,
        `ProfileImage`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS
            `created_at_formatted`
    FROM
        `USERS`
    WHERE
        `UserID` = p_UserID;
END$$

CREATE PROCEDURE `GET_USER_BY_EMAIL_PROC`(
    IN p_Email VARCHAR(255)
)
BEGIN
    SELECT
        `UserID`,
        `FirstName`,
        `LastName`,
        `Email`,
        `Password`,
        `RoleID`,
        `ProfileImage`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS
            `created_at_formatted`
    FROM
        `USERS`
    WHERE
        `Email` = p_Email;
END$$

CREATE PROCEDURE `GET_ALL_USERS_PROC`()
BEGIN
    SELECT
        `UserID`,
        `FirstName`,
        `LastName`,
        `Email`,
        `RoleID`,
        `ProfileImage`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS
            `created_at_formatted`
    FROM
        `USERS`
    ORDER BY `UserID` ASC;
END$$

CREATE FUNCTION `EMAIL_EXISTS_FUNC`(
    p_Email         VARCHAR(255),
    p_ExcludeUserID INT
)
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE v_count INT;

    SELECT COUNT(*) INTO v_count
    FROM `USERS`
    WHERE `Email` = p_Email
      AND (p_ExcludeUserID IS NULL OR `UserID` != p_ExcludeUserID);

    IF v_count > 0 THEN
        RETURN 1;
    ELSE
        RETURN 0;
    END IF;
END$$

DELIMITER ;