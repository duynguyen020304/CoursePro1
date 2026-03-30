CREATE OR REPLACE PACKAGE COURSE_LESSON_PKG AS

    PROCEDURE CREATE_LESSON_PROC(
        p_LessonID  IN COURSELESSON.LessonID%TYPE,
        p_CourseID  IN COURSELESSON.CourseID%TYPE,
        p_ChapterID IN COURSELESSON.ChapterID%TYPE,
        p_Title     IN COURSELESSON.Title%TYPE,
        p_Content   IN COURSELESSON.Content%TYPE,
        p_SortOrder IN COURSELESSON.SortOrder%TYPE
    );

    PROCEDURE DELETE_LESSON_PROC(
        p_LessonID IN COURSELESSON.LessonID%TYPE
    );

    PROCEDURE UPDATE_LESSON_PROC(
        p_LessonID  IN COURSELESSON.LessonID%TYPE,
        p_CourseID  IN COURSELESSON.CourseID%TYPE,
        p_ChapterID IN COURSELESSON.ChapterID%TYPE,
        p_Title     IN COURSELESSON.Title%TYPE,
        p_Content   IN COURSELESSON.Content%TYPE,
        p_SortOrder IN COURSELESSON.SortOrder%TYPE
    );

    FUNCTION GET_LESSON_BY_ID_FUNC(
        p_LessonID IN COURSELESSON.LessonID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_LESSONS_BY_CHAPTER_FUNC(
        p_ChapterID IN COURSELESSON.ChapterID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_LESSONS_BY_COURSE_FUNC(
        p_CourseID IN COURSELESSON.CourseID%TYPE
    ) RETURN SYS_REFCURSOR;

END COURSE_LESSON_PKG;
/

CREATE OR REPLACE PACKAGE BODY COURSE_LESSON_PKG AS

    PROCEDURE CREATE_LESSON_PROC(
        p_LessonID  IN COURSELESSON.LessonID%TYPE,
        p_CourseID  IN COURSELESSON.CourseID%TYPE,
        p_ChapterID IN COURSELESSON.ChapterID%TYPE,
        p_Title     IN COURSELESSON.Title%TYPE,
        p_Content   IN COURSELESSON.Content%TYPE,
        p_SortOrder IN COURSELESSON.SortOrder%TYPE
    ) IS
    BEGIN
        INSERT INTO COURSELESSON (LessonID, CourseID, ChapterID, Title, Content, SortOrder)
        VALUES (p_LessonID, p_CourseID, p_ChapterID, p_Title, p_Content, p_SortOrder);
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            RAISE_APPLICATION_ERROR(-20100, 'Lesson with LessonID ''' || p_LessonID || ''' already exists.');
        WHEN OTHERS THEN
            RAISE;
    END CREATE_LESSON_PROC;

    PROCEDURE DELETE_LESSON_PROC(
        p_LessonID IN COURSELESSON.LessonID%TYPE
    ) IS
    BEGIN
        DELETE FROM COURSELESSON
        WHERE LessonID = p_LessonID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20102, 'Lesson with LessonID ''' || p_LessonID || ''' not found for deletion.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20102 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in DELETE_LESSON_PROC: ' || SQLERRM);
            END IF;
    END DELETE_LESSON_PROC;

    PROCEDURE UPDATE_LESSON_PROC(
        p_LessonID  IN COURSELESSON.LessonID%TYPE,
        p_CourseID  IN COURSELESSON.CourseID%TYPE,
        p_ChapterID IN COURSELESSON.ChapterID%TYPE,
        p_Title     IN COURSELESSON.Title%TYPE,
        p_Content   IN COURSELESSON.Content%TYPE,
        p_SortOrder IN COURSELESSON.SortOrder%TYPE
    ) IS
    BEGIN
        UPDATE COURSELESSON
        SET CourseID  = p_CourseID,
            ChapterID = p_ChapterID,
            Title     = p_Title,
            Content   = p_Content,
            SortOrder = p_SortOrder
        WHERE LessonID = p_LessonID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20101, 'Lesson with LessonID ''' || p_LessonID || ''' not found for update.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20101 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UPDATE_LESSON_PROC: ' || SQLERRM);
            END IF;
    END UPDATE_LESSON_PROC;

    FUNCTION GET_LESSON_BY_ID_FUNC(
        p_LessonID IN COURSELESSON.LessonID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                LessonID,
                CourseID,
                ChapterID,
                Title,
                Content,
                SortOrder,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                COURSELESSON
            WHERE
                LessonID = p_LessonID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_LESSON_BY_ID_FUNC;

    FUNCTION GET_LESSONS_BY_CHAPTER_FUNC(
        p_ChapterID IN COURSELESSON.ChapterID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                LessonID,
                CourseID,
                ChapterID,
                Title,
                Content,
                SortOrder,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                COURSELESSON
            WHERE
                ChapterID = p_ChapterID
            ORDER BY SortOrder ASC;
        RETURN v_cursor;
    END GET_LESSONS_BY_CHAPTER_FUNC;

    FUNCTION GET_LESSONS_BY_COURSE_FUNC(
        p_CourseID IN COURSELESSON.CourseID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                LessonID,
                CourseID,
                ChapterID,
                Title,
                Content,
                SortOrder,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                COURSELESSON
            WHERE
                CourseID = p_CourseID
            ORDER BY ChapterID ASC, SortOrder ASC;
        RETURN v_cursor;
    END GET_LESSONS_BY_COURSE_FUNC;

END COURSE_LESSON_PKG;
/
