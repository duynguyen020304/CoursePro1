DELIMITER $$

CREATE PROCEDURE `CREATE_VIDEO_PROC`(
    IN p_VideoID   INT,
    IN p_LessonID  INT,
    IN p_Url       VARCHAR(255),
    IN p_Title     VARCHAR(255),
    IN p_Duration  INT,
    IN p_SortOrder INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DECLARE v_exists INT DEFAULT 0;

    -- Kiểm tra VideoID đã tồn tại
    SELECT COUNT(*) INTO v_exists
    FROM `CourseVideo`
    WHERE `VideoID` = p_VideoID;

    IF v_exists > 0 THEN
        SET v_error_message = CONCAT(
            'Video with VideoID ''',
            p_VideoID,
            ''' already exists.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    INSERT INTO `CourseVideo` (
        `VideoID`, `LessonID`, `Url`, `Title`, `Duration`, `SortOrder`
    )
    VALUES (
        p_VideoID, p_LessonID, p_Url, p_Title, p_Duration, p_SortOrder
    );
END$$

CREATE PROCEDURE `DELETE_VIDEO_PROC`(
    IN p_VideoID INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DECLARE v_exists INT DEFAULT 0;

    -- Kiểm tra tồn tại trước khi xóa
    SELECT COUNT(*) INTO v_exists
    FROM `CourseVideo`
    WHERE `VideoID` = p_VideoID;

    IF v_exists = 0 THEN
        SET v_error_message = CONCAT(
            'Video with VideoID ''',
            p_VideoID,
            ''' not found for deletion.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    DELETE FROM `CourseVideo`
    WHERE `VideoID` = p_VideoID;

    -- Bảo hiểm chống race-condition nhỏ
    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT(
            'Video with VideoID ''',
            p_VideoID,
            ''' not found for deletion.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$

CREATE PROCEDURE `UPDATE_VIDEO_PROC`(
    IN p_VideoID   INT,
    IN p_LessonID  INT,
    IN p_Url       VARCHAR(255),
    IN p_Title     VARCHAR(255),
    IN p_Duration  INT,
    IN p_SortOrder INT
)
BEGIN
    DECLARE v_error_message VARCHAR(255);
    DECLARE v_exists INT DEFAULT 0;

    -- Kiểm tra tồn tại trước khi cập nhật
    SELECT COUNT(*) INTO v_exists
    FROM `CourseVideo`
    WHERE `VideoID` = p_VideoID;

    IF v_exists = 0 THEN
        SET v_error_message = CONCAT(
            'Video with VideoID ''',
            p_VideoID,
            ''' not found for update.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;

    UPDATE `CourseVideo`
    SET
        `LessonID`  = p_LessonID,
        `Url`       = p_Url,
        `Title`     = p_Title,
        `Duration`  = p_Duration,
        `SortOrder` = p_SortOrder
    WHERE
        `VideoID` = p_VideoID;

    -- Bảo hiểm nếu có race-condition
    IF ROW_COUNT() = 0 THEN
        SET v_error_message = CONCAT(
            'Video with VideoID ''',
            p_VideoID,
            ''' not found for update.'
        );
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_error_message;
    END IF;
END$$

CREATE PROCEDURE `GET_VIDEO_BY_ID_PROC`(
    IN p_VideoID INT
)
BEGIN
    SELECT
        `VideoID`,
        `LessonID`,
        `Url`,
        `Title`,
        `SortOrder`,
        `Duration`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS
            `created_at_formatted`
    FROM
        `CourseVideo`
    WHERE
        `VideoID` = p_VideoID;
END$$

CREATE PROCEDURE `GET_VIDEOS_BY_LESSON_PROC`(
    IN p_LessonID INT
)
BEGIN
    SELECT
        `VideoID`,
        `LessonID`,
        `Url`,
        `Title`,
        `SortOrder`,
        `Duration`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS
            `created_at_formatted`
    FROM
        `CourseVideo`
    WHERE
        `LessonID` = p_LessonID
    ORDER BY `SortOrder` ASC;
END$$

DELIMITER ;