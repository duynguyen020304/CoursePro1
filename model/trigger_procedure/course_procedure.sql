-- Package Specification
CREATE OR REPLACE PACKAGE COURSE_PKG AS

    PROCEDURE CREATE_COURSE_PROC(
        p_CourseID     IN COURSE.CourseID%TYPE,
        p_Title        IN COURSE.Title%TYPE,
        p_Description  IN COURSE.Description%TYPE,
        p_Price        IN COURSE.Price%TYPE,
        p_Difficulty   IN COURSE.Difficulty%TYPE,
        p_Language     IN COURSE.Language%TYPE,
        p_CreatedBy    IN COURSE.CreatedBy%TYPE
    );

    PROCEDURE DELETE_COURSE_PROC(
        p_CourseID IN COURSE.CourseID%TYPE
    );

    PROCEDURE UPDATE_COURSE_PROC(
        p_CourseID_where IN COURSE.CourseID%TYPE,
        p_Title        IN COURSE.Title%TYPE,
        p_Description  IN COURSE.Description%TYPE,
        p_Price        IN COURSE.Price%TYPE,
        p_Difficulty   IN COURSE.Difficulty%TYPE,
        p_Language     IN COURSE.Language%TYPE
    );

    FUNCTION GET_COURSE_BY_ID_FUNC(
        p_CourseID_param IN COURSE.CourseID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_ALL_COURSES_FUNC
        RETURN SYS_REFCURSOR;

    FUNCTION GET_COURSES_BY_TITLE_FUNC(
        p_Title_param IN COURSE.Title%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_COURSES_BY_DIFFICULTY_LANG_FUNC(
        p_difficulty IN COURSE.Difficulty%TYPE,
        p_language   IN COURSE.Language%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_COURSES_BY_LANGUAGE_FUNC(
        p_language IN COURSE.Language%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_COURSES_BY_DIFFICULTY_FUNC(
        p_difficulty IN COURSE.Difficulty%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_COURSES_PAGINATED_FUNC(
        p_page_number       IN NUMBER,
        p_page_size         IN NUMBER,
        p_filter_difficulty IN COURSE.Difficulty%TYPE,
        p_filter_language   IN COURSE.Language%TYPE
    ) RETURN SYS_REFCURSOR;

END COURSE_PKG;
/

-- Package Body
CREATE OR REPLACE PACKAGE BODY COURSE_PKG AS

    PROCEDURE CREATE_COURSE_PROC(
        p_CourseID     IN COURSE.CourseID%TYPE,
        p_Title        IN COURSE.Title%TYPE,
        p_Description  IN COURSE.Description%TYPE,
        p_Price        IN COURSE.Price%TYPE,
        p_Difficulty   IN COURSE.Difficulty%TYPE,
        p_Language     IN COURSE.Language%TYPE,
        p_CreatedBy    IN COURSE.CreatedBy%TYPE
    ) IS
BEGIN
INSERT INTO COURSE (
    CourseID,
    Title,
    Description,
    Price,
    Difficulty,
    Language,
    CreatedBy
) VALUES (
             p_CourseID,
             p_Title,
             p_Description,
             p_Price,
             p_Difficulty,
             p_Language,
             p_CreatedBy
         );
EXCEPTION
        WHEN OTHERS THEN
            RAISE;
END CREATE_COURSE_PROC;

    PROCEDURE DELETE_COURSE_PROC(
        p_CourseID IN COURSE.CourseID%TYPE
    ) IS
BEGIN
DELETE FROM COURSE
WHERE CourseID = p_CourseID;

IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20031, 'Course with ID ''' || p_CourseID || ''' not found, or no rows deleted.');
END IF;
EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20031 THEN
                RAISE;
ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in DELETE_COURSE_PROC: ' || SQLERRM);
END IF;
END DELETE_COURSE_PROC;

    PROCEDURE UPDATE_COURSE_PROC(
        p_CourseID_where IN COURSE.CourseID%TYPE,
        p_Title        IN COURSE.Title%TYPE,
        p_Description  IN COURSE.Description%TYPE,
        p_Price        IN COURSE.Price%TYPE,
        p_Difficulty   IN COURSE.Difficulty%TYPE,
        p_Language     IN COURSE.Language%TYPE
    ) IS
BEGIN
UPDATE COURSE
SET Title       = p_Title,
    Description = p_Description,
    Price       = p_Price,
    Difficulty  = p_Difficulty,
    Language    = p_Language
WHERE CourseID = p_CourseID_where;

IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20032, 'Course with ID ''' || p_CourseID_where || ''' not found for update, or no data changed.');
END IF;
EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20032 THEN
                RAISE;
ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UPDATE_COURSE_PROC: ' || SQLERRM);
END IF;
END UPDATE_COURSE_PROC;

    FUNCTION GET_COURSE_BY_ID_FUNC(
        p_CourseID_param IN COURSE.CourseID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
BEGIN
OPEN v_cursor FOR
SELECT
    CourseID,
    Title,
    Description,
    Price,
    Difficulty,
    Language,
    CreatedBy,
    TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS CREATED_AT_FORMATTED
FROM
    COURSE
WHERE
    CourseID = p_CourseID_param;
RETURN v_cursor;
EXCEPTION
        WHEN OTHERS THEN
            RAISE;
END GET_COURSE_BY_ID_FUNC;

    FUNCTION GET_ALL_COURSES_FUNC
        RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
BEGIN
OPEN v_cursor FOR
SELECT
    CourseID,
    Title,
    Description,
    Price,
    Difficulty,
    Language,
    CreatedBy,
    TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS CREATED_AT_FORMATTED
FROM
    COURSE
ORDER BY Title ASC;
RETURN v_cursor;
EXCEPTION
        WHEN OTHERS THEN
            RAISE;
END GET_ALL_COURSES_FUNC;

    FUNCTION GET_COURSES_BY_TITLE_FUNC(
        p_Title_param IN COURSE.Title%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
BEGIN
OPEN v_cursor FOR
SELECT
    CourseID,
    Title,
    Description,
    Price,
    Difficulty,
    Language,
    CreatedBy,
    TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS CREATED_AT_FORMATTED
FROM
    COURSE
WHERE
    UPPER(Title) LIKE '%' || UPPER(p_Title_param) || '%'
ORDER BY Title ASC;
RETURN v_cursor;
EXCEPTION
        WHEN OTHERS THEN
            RAISE;
END GET_COURSES_BY_TITLE_FUNC;

    FUNCTION GET_COURSES_BY_DIFFICULTY_LANG_FUNC(
        p_difficulty IN COURSE.Difficulty%TYPE,
        p_language   IN COURSE.Language%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
BEGIN
OPEN v_cursor FOR
SELECT
    CourseID,
    Title,
    Description,
    Price,
    Difficulty,
    Language,
    CreatedBy,
    TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS CREATED_AT_FORMATTED
FROM
    COURSE
WHERE
    Difficulty = p_difficulty
  AND Language = p_language
ORDER BY Title ASC;
RETURN v_cursor;
EXCEPTION
        WHEN OTHERS THEN
            RAISE;
END GET_COURSES_BY_DIFFICULTY_LANG_FUNC;

    FUNCTION GET_COURSES_BY_LANGUAGE_FUNC(
        p_language IN COURSE.Language%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
BEGIN
OPEN v_cursor FOR
SELECT
    CourseID,
    Title,
    Description,
    Price,
    Difficulty,
    Language,
    CreatedBy,
    TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS CREATED_AT_FORMATTED
FROM
    COURSE
WHERE
    Language = p_language
ORDER BY Title ASC;
RETURN v_cursor;
EXCEPTION
        WHEN OTHERS THEN
            RAISE;
END GET_COURSES_BY_LANGUAGE_FUNC;

    FUNCTION GET_COURSES_BY_DIFFICULTY_FUNC(
        p_difficulty IN COURSE.Difficulty%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
BEGIN
OPEN v_cursor FOR
SELECT
    CourseID,
    Title,
    Description,
    Price,
    Difficulty,
    Language,
    CreatedBy,
    TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS CREATED_AT_FORMATTED
FROM
    COURSE
WHERE
    Difficulty = p_difficulty
ORDER BY Title ASC;
RETURN v_cursor;
EXCEPTION
        WHEN OTHERS THEN
            RAISE;
END GET_COURSES_BY_DIFFICULTY_FUNC;

    FUNCTION GET_COURSES_PAGINATED_FUNC(
        p_page_number       IN NUMBER,
        p_page_size         IN NUMBER,
        p_filter_difficulty IN COURSE.Difficulty%TYPE,
        p_filter_language   IN COURSE.Language%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
        v_offset NUMBER;
BEGIN
        v_offset := (p_page_number - 1) * p_page_size;

OPEN v_cursor FOR
SELECT
    CourseID,
    Title,
    Description,
    Price,
    Difficulty,
    Language,
    CreatedBy,
    TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS CREATED_AT_FORMATTED
FROM
    COURSE
WHERE
    (p_filter_difficulty IS NULL OR Difficulty = p_filter_difficulty)
  AND (p_filter_language IS NULL OR Language = p_filter_language)
ORDER BY
    Title ASC
OFFSET v_offset ROWS
    FETCH NEXT p_page_size ROWS ONLY;
RETURN v_cursor;
EXCEPTION
        WHEN OTHERS THEN
            RAISE;
END GET_COURSES_PAGINATED_FUNC;

END COURSE_PKG;
/