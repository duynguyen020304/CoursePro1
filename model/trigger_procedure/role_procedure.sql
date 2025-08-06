-- MySQL compatible stored procedures for ROLE management.
-- The original Oracle PL/SQL package has been converted into individual procedures.

-- A delimiter is used to allow for the semicolon within the procedure body.
DELIMITER $$

-- =================================================================================
-- Procedure to create a new role.
-- Equivalent to Oracle's CREATE_ROLE_PROC.
-- =================================================================================
CREATE PROCEDURE `CREATE_ROLE_PROC`(
    IN p_RoleID   INT,
    IN p_RoleName VARCHAR(255)
)
BEGIN
    -- Declare a handler for duplicate key errors (MySQL error code 1062).
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        -- Check if the RoleID is the duplicate.
        IF (SELECT COUNT(*) FROM `ROLE` WHERE `RoleID` = p_RoleID) > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = CONCAT('Role with RoleID ''', p_RoleID, ''' already exists.');
        -- Check if the RoleName is the duplicate.
        ELSEIF (SELECT COUNT(*) FROM `ROLE` WHERE `RoleName` = p_RoleName) > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = CONCAT('Role with RoleName ''', p_RoleName, ''' already exists.');
        -- Handle other potential unique constraint violations.
        ELSE
            SIGNAL SQLSTATE '23000'
            SET MESSAGE_TEXT = 'Duplicate entry violation.';
        END IF;
    END;

    -- Insert the new role into the ROLE table.
    INSERT INTO `ROLE` (`RoleID`, `RoleName`)
    VALUES (p_RoleID, p_RoleName);
END$$

-- =================================================================================
-- Procedure to delete a role by its ID.
-- Equivalent to Oracle's DELETE_ROLE_PROC.
-- =================================================================================
CREATE PROCEDURE `DELETE_ROLE_PROC`(
    IN p_RoleID INT
)
BEGIN
    -- Declare a handler for foreign key constraint violations (MySQL error code 1451).
    DECLARE EXIT HANDLER FOR 1451
    BEGIN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = CONCAT('Cannot delete RoleID ''', p_RoleID, ''' as it is currently in use by users.');
    END;

    -- Delete the role from the ROLE table.
    DELETE FROM `ROLE`
    WHERE `RoleID` = p_RoleID;

    -- Check if any row was actually deleted.
    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = CONCAT('Role with RoleID ''', p_RoleID, ''' not found for deletion.');
    END IF;
END$$

-- =================================================================================
-- Procedure to update an existing role's name.
-- Equivalent to Oracle's UPDATE_ROLE_PROC.
-- =================================================================================
CREATE PROCEDURE `UPDATE_ROLE_PROC`(
    IN p_RoleID   INT,
    IN p_RoleName VARCHAR(255)
)
BEGIN
    -- Declare a handler for duplicate key errors (MySQL error code 1062).
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = CONCAT('Update failed: RoleName ''', p_RoleName, ''' already exists for another role.');
    END;

    -- Update the RoleName for the given RoleID.
    UPDATE `ROLE`
    SET `RoleName` = p_RoleName
    WHERE `RoleID` = p_RoleID;

    -- Check if any row was actually updated.
    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = CONCAT('Role with RoleID ''', p_RoleID, ''' not found for update.');
    END IF;
END$$

-- =================================================================================
-- Procedure to get a role by its ID.
-- Equivalent to Oracle's GET_ROLE_BY_ID_FUNC.
-- In MySQL, procedures can return result sets directly without needing a cursor function.
-- =================================================================================
CREATE PROCEDURE `GET_ROLE_BY_ID_PROC`(
    IN p_RoleID INT
)
BEGIN
    -- Select the role details and format the created_at timestamp.
    -- DATE_FORMAT is the MySQL equivalent of Oracle's TO_CHAR for dates.
    SELECT
        `RoleID`,
        `RoleName`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS `created_at_formatted`
    FROM
        `ROLE`
    WHERE
        `RoleID` = p_RoleID;
END$$

-- =================================================================================
-- Procedure to get all roles.
-- Equivalent to Oracle's GET_ALL_ROLES_FUNC.
-- =================================================================================
CREATE PROCEDURE `GET_ALL_ROLES_PROC`()
BEGIN
    -- Select all roles and format the created_at timestamp.
    SELECT
        `RoleID`,
        `RoleName`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS `created_at_formatted`
    FROM
        `ROLE`
    ORDER BY `RoleID` ASC;
END$$

-- Reset the delimiter back to the default semicolon.
DELIMITER ;
