CREATE OR REPLACE PACKAGE COURSE_CHAPTER_PKG AS

    PROCEDURE CREATE_CHAPTER_PROC(
        p_ChapterID   IN COURSECHAPTER.ChapterID%TYPE,
        p_CourseID    IN COURSECHAPTER.CourseID%TYPE,
        p_Title       IN COURSECHAPTER.Title%TYPE,
        p_Description IN COURSECHAPTER.Description%TYPE,
        p_SortOrder   IN COURSECHAPTER.SortOrder%TYPE
    );

    PROCEDURE UPDATE_CHAPTER_PROC(
        p_ChapterID   IN COURSECHAPTER.ChapterID%TYPE,
        p_CourseID    IN COURSECHAPTER.CourseID%TYPE,
        p_Title       IN COURSECHAPTER.Title%TYPE,
        p_Description IN COURSECHAPTER.Description%TYPE,
        p_SortOrder   IN COURSECHAPTER.SortOrder%TYPE
    );

    PROCEDURE DELETE_CHAPTER_PROC(
        p_ChapterID IN COURSECHAPTER.ChapterID%TYPE
    );

    FUNCTION GET_ALL_CHAPTERS_FUNC
        RETURN SYS_REFCURSOR;

    FUNCTION GET_CHAPTER_BY_ID_FUNC(
        p_ChapterID IN COURSECHAPTER.ChapterID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_CHAPTERS_BY_COURSE_FUNC(
        p_CourseID IN COURSECHAPTER.CourseID%TYPE
    ) RETURN SYS_REFCURSOR;

END COURSE_CHAPTER_PKG;
/

CREATE OR REPLACE PACKAGE BODY COURSE_CHAPTER_PKG AS

    PROCEDURE CREATE_CHAPTER_PROC(
        p_ChapterID   IN COURSECHAPTER.ChapterID%TYPE,
        p_CourseID    IN COURSECHAPTER.CourseID%TYPE,
        p_Title       IN COURSECHAPTER.Title%TYPE,
        p_Description IN COURSECHAPTER.Description%TYPE,
        p_SortOrder   IN COURSECHAPTER.SortOrder%TYPE
    ) IS
    BEGIN
        INSERT INTO COURSECHAPTER (ChapterID, CourseID, Title, Description, SortOrder)
        VALUES (p_ChapterID, p_CourseID, p_Title, p_Description, p_SortOrder);

    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            RAISE_APPLICATION_ERROR(-20190, 'Chapter with ChapterID ''' || p_ChapterID || ''' already exists.');
        WHEN OTHERS THEN
            RAISE;
    END CREATE_CHAPTER_PROC;

    PROCEDURE UPDATE_CHAPTER_PROC(
        p_ChapterID   IN COURSECHAPTER.ChapterID%TYPE,
        p_CourseID    IN COURSECHAPTER.CourseID%TYPE,
        p_Title       IN COURSECHAPTER.Title%TYPE,
        p_Description IN COURSECHAPTER.Description%TYPE,
        p_SortOrder   IN COURSECHAPTER.SortOrder%TYPE
    ) IS
    BEGIN
        UPDATE COURSECHAPTER
        SET CourseID    = p_CourseID,
            Title       = p_Title,
            Description = p_Description,
            SortOrder   = p_SortOrder
        WHERE ChapterID = p_ChapterID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20191, 'Chapter with ChapterID ''' || p_ChapterID || ''' not found for update.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20191 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UPDATE_CHAPTER_PROC: ' || SQLERRM);
            END IF;
    END UPDATE_CHAPTER_PROC;

    PROCEDURE DELETE_CHAPTER_PROC(
        p_ChapterID IN COURSECHAPTER.ChapterID%TYPE
    ) IS
    BEGIN
        DELETE FROM COURSECHAPTER
        WHERE ChapterID = p_ChapterID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20192, 'Chapter with ChapterID ''' || p_ChapterID || ''' not found for deletion.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20192 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in DELETE_CHAPTER_PROC: ' || SQLERRM);
            END IF;
    END DELETE_CHAPTER_PROC;

    FUNCTION GET_ALL_CHAPTERS_FUNC
        RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                ChapterID,
                CourseID,
                Title,
                Description,
                SortOrder,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS CREATED_AT_FORMATTED
            FROM
                COURSECHAPTER
            ORDER BY SortOrder ASC, Title ASC;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_ALL_CHAPTERS_FUNC;

    FUNCTION GET_CHAPTER_BY_ID_FUNC(
        p_ChapterID IN COURSECHAPTER.ChapterID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                ChapterID,
                CourseID,
                Title,
                Description,
                SortOrder,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS CREATED_AT_FORMATTED
            FROM
                COURSECHAPTER
            WHERE
                ChapterID = p_ChapterID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_CHAPTER_BY_ID_FUNC;

    FUNCTION GET_CHAPTERS_BY_COURSE_FUNC(
        p_CourseID IN COURSECHAPTER.CourseID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                ChapterID,
                CourseID,
                Title,
                Description,
                SortOrder,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS CREATED_AT_FORMATTED
            FROM
                COURSECHAPTER
            WHERE
                CourseID = p_CourseID
            ORDER BY SortOrder ASC;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_CHAPTERS_BY_COURSE_FUNC;

END COURSE_CHAPTER_PKG;
/