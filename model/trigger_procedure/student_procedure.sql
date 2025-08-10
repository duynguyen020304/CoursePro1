DELIMITER $$

CREATE PROCEDURE `CREATE_STUDENT_PROC`(
    IN p_StudentID INT,
    IN p_UserID    INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DECLARE v_exists INT DEFAULT 0;

    /* Kiểm tra StudentID đã tồn tại */
    SELECT COUNT(*) INTO v_exists
    FROM `STUDENT`
    WHERE `StudentID` = p_StudentID;

    IF v_exists > 0 THEN
        SET v_error_message = CONCAT(
            'Student with StudentID ''',
            p_StudentID,
            ''' already exists.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    /* Kiểm tra UserID đã được đăng ký làm student */
    SELECT COUNT(*) INTO v_exists
    FROM `STUDENT`
    WHERE `UserID` = p_UserID;

    IF v_exists > 0 THEN
        SET v_error_message = CONCAT(
            'User with UserID ''',
            p_UserID,
            ''' is already registered as a student.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    INSERT INTO `STUDENT` (`StudentID`, `UserID`)
    VALUES (p_StudentID, p_UserID);
END$$

CREATE PROCEDURE `DELETE_STUDENT_PROC`(
    IN p_StudentID INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DECLARE v_exists INT DEFAULT 0;

    /* Kiểm tra tồn tại trước khi xóa */
    SELECT COUNT(*) INTO v_exists
    FROM `STUDENT`
    WHERE `StudentID` = p_StudentID;

    IF v_exists = 0 THEN
        SET v_error_message = CONCAT(
            'Student with StudentID ''',
            p_StudentID,
            ''' not found for deletion.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    DELETE FROM `STUDENT`
    WHERE `StudentID` = p_StudentID;
END$$

CREATE PROCEDURE `UPDATE_STUDENT_PROC`(
    IN p_StudentID INT,
    IN p_UserID    INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DECLARE v_exists INT DEFAULT 0;

    /* Kiểm tra UserID đã được liên kết với student khác chưa */
    SELECT COUNT(*) INTO v_exists
    FROM `STUDENT`
    WHERE `UserID` = p_UserID
      AND `StudentID` <> p_StudentID;

    IF v_exists > 0 THEN
        SET v_error_message = CONCAT(
            'Update failed: UserID ''',
            p_UserID,
            ''' is already associated with another student.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    UPDATE `STUDENT`
    SET `UserID` = p_UserID
    WHERE `StudentID` = p_StudentID;

    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT(
            'Student with StudentID ''',
            p_StudentID,
            ''' not found for update.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$

CREATE PROCEDURE `GET_STUDENT_BY_ID_PROC`(
    IN p_StudentID INT
)
BEGIN
    SELECT
        `StudentID`,
        `UserID`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS `created_at_formatted`
    FROM
        `STUDENT`
    WHERE
        `StudentID` = p_StudentID;
END$$

CREATE PROCEDURE `GET_STUDENT_BY_USER_ID_PROC`(
    IN p_UserID INT
)
BEGIN
    SELECT
        `StudentID`,
        `UserID`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS `created_at_formatted`
    FROM
        `STUDENT`
    WHERE
        `UserID` = p_UserID;
END$$

CREATE PROCEDURE `GET_ALL_STUDENTS_PROC`()
BEGIN
    SELECT
        `StudentID`,
        `UserID`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS `created_at_formatted`
    FROM
        `STUDENT`
    ORDER BY `StudentID` ASC;
END$$

DELIMITER ;