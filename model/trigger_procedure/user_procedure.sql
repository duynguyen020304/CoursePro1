-- MySQL compatible stored procedures and functions for USERS management.
-- The original Oracle PL/SQL package has been converted into individual procedures and functions.

-- A delimiter is used to allow for the semicolon within the procedure body.
DELIMITER $$

-- =================================================================================
-- Procedure to create a new user.
-- Equivalent to Oracle's CREATE_USER_PROC.
-- =================================================================================
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
    -- Declare a handler for duplicate key errors (MySQL error code 1062).
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        -- Check if the UserID is the duplicate.
        IF (SELECT COUNT(*) FROM `USERS` WHERE `UserID` = p_UserID) > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = CONCAT('User with UserID ''', p_UserID, ''' already exists.');
        -- Check if the Email is the duplicate.
        ELSEIF (SELECT COUNT(*) FROM `USERS` WHERE `Email` = p_Email) > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = CONCAT('User with Email ''', p_Email, ''' already exists.');
        -- Handle other potential unique constraint violations.
        ELSE
            SIGNAL SQLSTATE '23000'
            SET MESSAGE_TEXT = 'Duplicate entry violation.';
        END IF;
    END;

    -- Insert the new user into the USERS table.
    INSERT INTO `USERS` (`UserID`, `FirstName`, `LastName`, `Email`, `Password`, `RoleID`, `ProfileImage`)
    VALUES (p_UserID, p_FirstName, p_LastName, p_Email, p_Password, p_RoleID, p_ProfileImage);
END$$

-- =================================================================================
-- Procedure to get user details for authentication by email.
-- Equivalent to Oracle's GET_USER_FOR_AUTH_FUNC.
-- =================================================================================
CREATE PROCEDURE `GET_USER_FOR_AUTH_PROC`(
    IN p_Email VARCHAR(255)
)
BEGIN
    -- Select user details for a given email.
    SELECT
        `UserID`,
        `FirstName`,
        `LastName`,
        `Email`,
        `Password`,
        `RoleID`,
        `ProfileImage`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS `created_at_formatted`
    FROM
        `USERS`
    WHERE
        `Email` = p_Email;
END$$

-- =================================================================================
-- Procedure to delete a user by their ID.
-- Equivalent to Oracle's DELETE_USER_PROC.
-- =================================================================================
CREATE PROCEDURE `DELETE_USER_PROC`(
    IN p_UserID INT
)
BEGIN
    -- Declare a handler for foreign key constraint violations (MySQL error code 1451).
    DECLARE EXIT HANDLER FOR 1451
    BEGIN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = CONCAT('Cannot delete UserID ''', p_UserID, ''' as it is referenced by other records.');
    END;

    -- Delete the user from the USERS table.
    DELETE FROM `USERS`
    WHERE `UserID` = p_UserID;

    -- Check if any row was actually deleted.
    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = CONCAT('User with UserID ''', p_UserID, ''' not found for deletion.');
    END IF;
END$$

-- =================================================================================
-- Procedure to update an existing user's details.
-- Equivalent to Oracle's UPDATE_USER_PROC.
-- =================================================================================
CREATE PROCEDURE `UPDATE_USER_PROC`(
    IN p_UserID        INT,
    IN p_FirstName     VARCHAR(255),
    IN p_LastName      VARCHAR(255),
    IN p_Password      VARCHAR(255),
    IN p_RoleID        INT,
    IN p_ProfileImage  VARCHAR(255)
)
BEGIN
    -- First, check if the user exists.
    IF (SELECT COUNT(*) FROM `USERS` WHERE `UserID` = p_UserID) = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = CONCAT('User with UserID ''', p_UserID, ''' not found for update.');
    ELSE
        -- Update the user's details. IFNULL is used to keep the existing value if a parameter is NULL.
        UPDATE `USERS`
        SET
            `FirstName`    = IFNULL(p_FirstName, `FirstName`),
            `LastName`     = IFNULL(p_LastName, `LastName`),
            `Password`     = IFNULL(p_Password, `Password`),
            `RoleID`       = IFNULL(p_RoleID, `RoleID`),
            `ProfileImage` = IFNULL(p_ProfileImage, `ProfileImage`)
        WHERE
            `UserID` = p_UserID;
    END IF;
END$$

-- =================================================================================
-- Procedure to get a user by their ID.
-- Equivalent to Oracle's GET_USER_BY_ID_FUNC.
-- =================================================================================
CREATE PROCEDURE `GET_USER_BY_ID_PROC`(
    IN p_UserID INT
)
BEGIN
    -- Select user details for a given UserID.
    SELECT
        `UserID`,
        `FirstName`,
        `LastName`,
        `Email`,
        `Password`,
        `RoleID`,
        `ProfileImage`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS `created_at_formatted`
    FROM
        `USERS`
    WHERE
        `UserID` = p_UserID;
END$$

-- =================================================================================
-- Procedure to get a user by their email.
-- Equivalent to Oracle's GET_USER_BY_EMAIL_FUNC.
-- =================================================================================
CREATE PROCEDURE `GET_USER_BY_EMAIL_PROC`(
    IN p_Email VARCHAR(255)
)
BEGIN
    -- Select user details for a given email.
    SELECT
        `UserID`,
        `FirstName`,
        `LastName`,
        `Email`,
        `Password`,
        `RoleID`,
        `ProfileImage`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS `created_at_formatted`
    FROM
        `USERS`
    WHERE
        `Email` = p_Email;
END$$

-- =================================================================================
-- Procedure to get all users.
-- Equivalent to Oracle's GET_ALL_USERS_FUNC.
-- =================================================================================
CREATE PROCEDURE `GET_ALL_USERS_PROC`()
BEGIN
    -- Select all users, excluding the password for general listings.
    SELECT
        `UserID`,
        `FirstName`,
        `LastName`,
        `Email`,
        `RoleID`,
        `ProfileImage`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS `created_at_formatted`
    FROM
        `USERS`
    ORDER BY `UserID` ASC;
END$$

-- =================================================================================
-- Function to check if an email exists.
-- Equivalent to Oracle's EMAIL_EXISTS_FUNC.
-- =================================================================================
CREATE FUNCTION `EMAIL_EXISTS_FUNC`(
    p_Email         VARCHAR(255),
    p_ExcludeUserID INT
)
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE v_count INT;

    -- Count occurrences of the email, optionally excluding a specific UserID.
    SELECT COUNT(*)
    INTO v_count
    FROM `USERS`
    WHERE `Email` = p_Email
      AND (`p_ExcludeUserID` IS NULL OR `UserID` != p_ExcludeUserID);

    -- Return 1 if the email exists, otherwise return 0.
    IF v_count > 0 THEN
        RETURN 1;
    ELSE
        RETURN 0;
    END IF;
END$$

-- Reset the delimiter back to the default semicolon.
DELIMITER ;
