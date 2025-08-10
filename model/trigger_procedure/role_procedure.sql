DELIMITER $$

CREATE PROCEDURE `CREATE_ROLE_PROC`(
    IN p_RoleID   INT,
    IN p_RoleName VARCHAR(255)
)
BEGIN
    DECLARE v_error_message VARCHAR(255);

    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        IF (SELECT COUNT(*) FROM `ROLE` WHERE `RoleID` = p_RoleID) > 0 THEN
            SET v_error_message = CONCAT(
                'Role with RoleID ''',
                p_RoleID,
                ''' already exists.'
            );
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = v_error_message;
        ELSEIF (SELECT COUNT(*) FROM `ROLE` WHERE `RoleName` = p_RoleName) > 0 THEN
            SET v_error_message = CONCAT(
                'Role with RoleName ''',
                p_RoleName,
                ''' already exists.'
            );
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = v_error_message;
        ELSE
            SET v_error_message = 'Duplicate entry violation.';
            SIGNAL SQLSTATE '23000'
                SET MESSAGE_TEXT = v_error_message;
        END IF;
    END;

    INSERT INTO `ROLE` (`RoleID`, `RoleName`)
    VALUES (p_RoleID, p_RoleName);
END$$

CREATE PROCEDURE `DELETE_ROLE_PROC`(
    IN p_RoleID INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);

    DECLARE EXIT HANDLER FOR 1451
    BEGIN
        SET v_error_message = CONCAT(
            'Cannot delete RoleID ''',
            p_RoleID,
            ''' as it is currently in use by users.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END;

    DELETE FROM `ROLE`
    WHERE `RoleID` = p_RoleID;

    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT(
            'Role with RoleID ''',
            p_RoleID,
            ''' not found for deletion.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$

CREATE PROCEDURE `UPDATE_ROLE_PROC`(
    IN p_RoleID   INT,
    IN p_RoleName VARCHAR(255)
)
BEGIN
    DECLARE v_error_message VARCHAR(255);

    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        SET v_error_message = CONCAT(
            'Update failed: RoleName ''',
            p_RoleName,
            ''' already exists for another role.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END;

    UPDATE `ROLE`
    SET `RoleName` = p_RoleName
    WHERE `RoleID` = p_RoleID;

    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT(
            'Role with RoleID ''',
            p_RoleID,
            ''' not found for update.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$

CREATE PROCEDURE `GET_ROLE_BY_ID_PROC`(
    IN p_RoleID INT
)
BEGIN
    SELECT
        `RoleID`,
        `RoleName`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS `created_at_formatted`
    FROM
        `ROLE`
    WHERE
        `RoleID` = p_RoleID;
END$$

CREATE PROCEDURE `GET_ALL_ROLES_PROC`()
BEGIN
    SELECT
        `RoleID`,
        `RoleName`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS `created_at_formatted`
    FROM
        `ROLE`
    ORDER BY `RoleID` ASC;
END$$

DELIMITER ;