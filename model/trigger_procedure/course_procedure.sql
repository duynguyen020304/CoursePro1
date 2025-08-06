-- MySQL-Compatible Stored Procedures for Course Management
--
-- This script converts the Oracle PL/SQL package `COURSE_PKG`
-- into standalone MySQL stored procedures.
--
-- Key Differences from Oracle Version:
-- 1. No Packages: MySQL does not have packages, so each procedure is a separate object.
-- 2. CREATE/DROP Syntax: Uses `DROP PROCEDURE IF EXISTS` and `CREATE PROCEDURE`.
-- 3. No %TYPE: Parameter data types are explicitly defined (e.g., INT, VARCHAR, TEXT, DECIMAL).
-- 4. No SYS_REFCURSOR: Procedures that return data simply execute a SELECT statement.
-- 5. Error Handling: Uses `DECLARE HANDLER` and `SIGNAL SQLSTATE '45000'` to raise custom errors.
-- 6. Row Count: Uses `ROW_COUNT()` instead of `SQL%ROWCOUNT`.
-- 7. Date Formatting: Uses `DATE_FORMAT()` instead of `TO_CHAR()`.
-- 8. Pagination: Uses `LIMIT ... OFFSET ...` instead of Oracle's `OFFSET ... FETCH ...` syntax.

-- Change the delimiter to allow for semicolons within the procedures.
DELIMITER $$

-- =================================================================
-- Procedure to create a new course.
-- =================================================================
DROP PROCEDURE IF EXISTS `CREATE_COURSE_PROC`$$
CREATE PROCEDURE `CREATE_COURSE_PROC`(
    IN p_CourseID INT,
    IN p_Title VARCHAR(255),
    IN p_Description TEXT,
    IN p_Price DECIMAL(10, 2),
    IN p_Difficulty VARCHAR(50),
    IN p_Language VARCHAR(50),
    IN p_CreatedBy INT
)
BEGIN
    -- Declare an exit handler for duplicate primary key errors.
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        SET @message = CONCAT('Course with CourseID ''', p_CourseID, ''' already exists.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END;

    -- Insert the new course record.
    INSERT INTO COURSE (CourseID, Title, Description, Price, Difficulty, Language, CreatedBy)
    VALUES (p_CourseID, p_Title, p_Description, p_Price, p_Difficulty, p_Language, p_CreatedBy);
END$$

-- =================================================================
-- Procedure to delete a course by its ID.
-- =================================================================
DROP PROCEDURE IF EXISTS `DELETE_COURSE_PROC`$$
CREATE PROCEDURE `DELETE_COURSE_PROC`(
    IN p_CourseID INT
)
BEGIN
    DELETE FROM COURSE WHERE CourseID = p_CourseID;

    IF ROW_COUNT() = 0 THEN
        SET @message = CONCAT('Course with ID ''', p_CourseID, ''' not found, or no rows deleted.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END IF;
END$$

-- =================================================================
-- Procedure to update an existing course.
-- =================================================================
DROP PROCEDURE IF EXISTS `UPDATE_COURSE_PROC`$$
CREATE PROCEDURE `UPDATE_COURSE_PROC`(
    IN p_CourseID_where INT,
    IN p_Title VARCHAR(255),
    IN p_Description TEXT,
    IN p_Price DECIMAL(10, 2),
    IN p_Difficulty VARCHAR(50),
    IN p_Language VARCHAR(50)
)
BEGIN
    UPDATE COURSE
    SET Title = p_Title,
        Description = p_Description,
        Price = p_Price,
        Difficulty = p_Difficulty,
        Language = p_Language
    WHERE CourseID = p_CourseID_where;

    IF ROW_COUNT() = 0 THEN
        SET @message = CONCAT('Course with ID ''', p_CourseID_where, ''' not found for update, or no data changed.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = @message;
    END IF;
END$$

-- =================================================================
-- Procedure to get a single course by its ID.
-- =================================================================
DROP PROCEDURE IF EXISTS `GET_COURSE_BY_ID_PROC`$$
CREATE PROCEDURE `GET_COURSE_BY_ID_PROC`(
    IN p_CourseID_param INT
)
BEGIN
    SELECT
        CourseID, Title, Description, Price, Difficulty, Language, CreatedBy,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS CREATED_AT_FORMATTED
    FROM COURSE
    WHERE CourseID = p_CourseID_param;
END$$

-- =================================================================
-- Procedure to get all courses.
-- =================================================================
DROP PROCEDURE IF EXISTS `GET_ALL_COURSES_PROC`$$
CREATE PROCEDURE `GET_ALL_COURSES_PROC`()
BEGIN
    SELECT
        CourseID, Title, Description, Price, Difficulty, Language, CreatedBy,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS CREATED_AT_FORMATTED
    FROM COURSE
    ORDER BY Title ASC;
END$$

-- =================================================================
-- Procedure to get courses by title, with optional filters.
-- =================================================================
DROP PROCEDURE IF EXISTS `GET_COURSES_BY_TITLE_PROC`$$
CREATE PROCEDURE `GET_COURSES_BY_TITLE_PROC`(
    IN p_Title_param VARCHAR(255),
    IN p_difficulty VARCHAR(50),
    IN p_language VARCHAR(50)
)
BEGIN
    SELECT
        CourseID, Title, Description, Price, Difficulty, Language, CreatedBy,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS CREATED_AT_FORMATTED
    FROM COURSE
    WHERE
        -- Condition for title search (always active)
        UPPER(Title) LIKE CONCAT('%', UPPER(p_Title_param), '%')
        -- Optional condition for difficulty
        AND (p_difficulty IS NULL OR Difficulty = p_difficulty)
        -- Optional condition for language
        AND (p_language IS NULL OR Language = p_language)
    ORDER BY Title ASC;
END$$

-- =================================================================
-- Procedure to get courses by both difficulty and language.
-- =================================================================
DROP PROCEDURE IF EXISTS `GET_COURSES_BY_DIFFICULTY_LANG_PROC`$$
CREATE PROCEDURE `GET_COURSES_BY_DIFFICULTY_LANG_PROC`(
    IN p_difficulty VARCHAR(50),
    IN p_language VARCHAR(50)
)
BEGIN
    SELECT
        CourseID, Title, Description, Price, Difficulty, Language, CreatedBy,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS CREATED_AT_FORMATTED
    FROM COURSE
    WHERE
        Difficulty = p_difficulty AND Language = p_language
    ORDER BY Title ASC;
END$$

-- =================================================================
-- Procedure to get courses by language only.
-- =================================================================
DROP PROCEDURE IF EXISTS `GET_COURSES_BY_LANGUAGE_PROC`$$
CREATE PROCEDURE `GET_COURSES_BY_LANGUAGE_PROC`(
    IN p_language VARCHAR(50)
)
BEGIN
    SELECT
        CourseID, Title, Description, Price, Difficulty, Language, CreatedBy,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS CREATED_AT_FORMATTED
    FROM COURSE
    WHERE Language = p_language
    ORDER BY Title ASC;
END$$

-- =================================================================
-- Procedure to get courses by difficulty only.
-- =================================================================
DROP PROCEDURE IF EXISTS `GET_COURSES_BY_DIFFICULTY_PROC`$$
CREATE PROCEDURE `GET_COURSES_BY_DIFFICULTY_PROC`(
    IN p_difficulty VARCHAR(50)
)
BEGIN
    SELECT
        CourseID, Title, Description, Price, Difficulty, Language, CreatedBy,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS CREATED_AT_FORMATTED
    FROM COURSE
    WHERE Difficulty = p_difficulty
    ORDER BY Title ASC;
END$$

-- =================================================================
-- Procedure to get courses with pagination and optional filters.
-- =================================================================
DROP PROCEDURE IF EXISTS `GET_COURSES_PAGINATED_PROC`$$
CREATE PROCEDURE `GET_COURSES_PAGINATED_PROC`(
    IN p_page_number INT,
    IN p_page_size INT,
    IN p_filter_difficulty VARCHAR(50),
    IN p_filter_language VARCHAR(50)
)
BEGIN
    DECLARE v_offset INT;
    SET v_offset = (p_page_number - 1) * p_page_size;

    SELECT
        CourseID, Title, Description, Price, Difficulty, Language, CreatedBy,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s.%f') AS CREATED_AT_FORMATTED
    FROM COURSE
    WHERE
        (p_filter_difficulty IS NULL OR Difficulty = p_filter_difficulty)
        AND (p_filter_language IS NULL OR Language = p_filter_language)
    ORDER BY Title ASC
    LIMIT p_page_size OFFSET v_offset;
END$$

-- Reset the delimiter back to the default.
DELIMITER ;
