-- MySQL compatible stored procedures for COURSEVIDEO management.
-- The original Oracle PL/SQL package has been converted into individual procedures.

-- A delimiter is used to allow for the semicolon within the procedure body.
DELIMITER $$

-- =================================================================================
-- Procedure to create a new course video.
-- Equivalent to Oracle's CREATE_VIDEO_PROC.
-- =================================================================================
CREATE PROCEDURE `CREATE_VIDEO_PROC`(
    IN p_VideoID   INT,
    IN p_LessonID  INT,
    IN p_Url       VARCHAR(255),
    IN p_Title     VARCHAR(255),
    IN p_Duration  INT,
    IN p_SortOrder INT
)
BEGIN
    -- Declare a handler for duplicate key errors (MySQL error code 1062).
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = CONCAT('Video with VideoID ''', p_VideoID, ''' already exists.');
    END;

    -- Insert the new video into the CourseVideo table.
    INSERT INTO `CourseVideo` (`VideoID`, `LessonID`, `Url`, `Title`, `Duration`, `SortOrder`)
    VALUES (p_VideoID, p_LessonID, p_Url, p_Title, p_Duration, p_SortOrder);
END$$

-- =================================================================================
-- Procedure to delete a video by its ID.
-- Equivalent to Oracle's DELETE_VIDEO_PROC.
-- =================================================================================
CREATE PROCEDURE `DELETE_VIDEO_PROC`(
    IN p_VideoID INT
)
BEGIN
    -- Delete the video from the CourseVideo table.
    DELETE FROM `CourseVideo`
    WHERE `VideoID` = p_VideoID;

    -- Check if any row was actually deleted.
    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = CONCAT('Video with VideoID ''', p_VideoID, ''' not found for deletion.');
    END IF;
END$$

-- =================================================================================
-- Procedure to update an existing video's details.
-- Equivalent to Oracle's UPDATE_VIDEO_PROC.
-- =================================================================================
CREATE PROCEDURE `UPDATE_VIDEO_PROC`(
    IN p_VideoID   INT,
    IN p_LessonID  INT,
    IN p_Url       VARCHAR(255),
    IN p_Title     VARCHAR(255),
    IN p_Duration  INT,
    IN p_SortOrder INT
)
BEGIN
    -- Update the video details for the given VideoID.
    UPDATE `CourseVideo`
    SET
        `LessonID`  = p_LessonID,
        `Url`       = p_Url,
        `Title`     = p_Title,
        `Duration`  = p_Duration,
        `SortOrder` = p_SortOrder
    WHERE
        `VideoID` = p_VideoID;

    -- Check if any row was actually updated.
    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = CONCAT('Video with VideoID ''', p_VideoID, ''' not found for update.');
    END IF;
END$$

-- =================================================================================
-- Procedure to get a video by its ID.
-- Equivalent to Oracle's GET_VIDEO_BY_ID_FUNC.
-- =================================================================================
CREATE PROCEDURE `GET_VIDEO_BY_ID_PROC`(
    IN p_VideoID INT
)
BEGIN
    -- Select the video details and format the created_at timestamp.
    SELECT
        `VideoID`,
        `LessonID`,
        `Url`,
        `Title`,
        `SortOrder`,
        `Duration`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS `created_at_formatted`
    FROM
        `CourseVideo`
    WHERE
        `VideoID` = p_VideoID;
END$$

-- =================================================================================
-- Procedure to get all videos for a specific lesson.
-- Equivalent to Oracle's GET_VIDEOS_BY_LESSON_FUNC.
-- =================================================================================
CREATE PROCEDURE `GET_VIDEOS_BY_LESSON_PROC`(
    IN p_LessonID INT
)
BEGIN
    -- Select all videos for a given lesson, ordered by SortOrder.
    SELECT
        `VideoID`,
        `LessonID`,
        `Url`,
        `Title`,
        `SortOrder`,
        `Duration`,
        DATE_FORMAT(`created_at`, '%Y-%m-%d %H:%i:%s.%f') AS `created_at_formatted`
    FROM
        `CourseVideo`
    WHERE
        `LessonID` = p_LessonID
    ORDER BY `SortOrder` ASC;
END$$

-- Reset the delimiter back to the default semicolon.
DELIMITER ;
