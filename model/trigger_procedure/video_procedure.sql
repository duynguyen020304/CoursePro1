CREATE OR REPLACE PACKAGE COURSE_VIDEO_PKG AS

    PROCEDURE CREATE_VIDEO_PROC(
        p_VideoID   IN COURSEVIDEO.VideoID%TYPE,
        p_LessonID  IN COURSEVIDEO.LessonID%TYPE,
        p_Url       IN COURSEVIDEO.Url%TYPE,
        p_Title     IN COURSEVIDEO.Title%TYPE,
        p_Duration  IN COURSEVIDEO.Duration%TYPE,
        p_SortOrder IN COURSEVIDEO.SortOrder%TYPE
    );

    PROCEDURE DELETE_VIDEO_PROC(
        p_VideoID IN COURSEVIDEO.VideoID%TYPE
    );

    PROCEDURE UPDATE_VIDEO_PROC(
        p_VideoID   IN COURSEVIDEO.VideoID%TYPE,
        p_LessonID  IN COURSEVIDEO.LessonID%TYPE,
        p_Url       IN COURSEVIDEO.Url%TYPE,
        p_Title     IN COURSEVIDEO.Title%TYPE,
        p_Duration  IN COURSEVIDEO.Duration%TYPE,
        p_SortOrder IN COURSEVIDEO.SortOrder%TYPE
    );

    FUNCTION GET_VIDEO_BY_ID_FUNC(
        p_VideoID IN COURSEVIDEO.VideoID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_VIDEOS_BY_LESSON_FUNC(
        p_LessonID IN COURSEVIDEO.LessonID%TYPE
    ) RETURN SYS_REFCURSOR;

END COURSE_VIDEO_PKG;
/

CREATE OR REPLACE PACKAGE BODY COURSE_VIDEO_PKG AS

    PROCEDURE CREATE_VIDEO_PROC(
        p_VideoID   IN COURSEVIDEO.VideoID%TYPE,
        p_LessonID  IN COURSEVIDEO.LessonID%TYPE,
        p_Url       IN COURSEVIDEO.Url%TYPE,
        p_Title     IN COURSEVIDEO.Title%TYPE,
        p_Duration  IN COURSEVIDEO.Duration%TYPE,
        p_SortOrder IN COURSEVIDEO.SortOrder%TYPE
    ) IS
    BEGIN
        INSERT INTO CourseVideo (VideoID, LessonID, Url, Title, Duration, SortOrder)
        VALUES (p_VideoID, p_LessonID, p_Url, p_Title, p_Duration, p_SortOrder);
        RAISE_APPLICATION_ERROR(-20180, 'Video with VideoID ''' || p_VideoID || ''' already exists.');
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            RAISE_APPLICATION_ERROR(-20180, 'Video with VideoID ''' || p_VideoID || ''' already exists.');
        WHEN OTHERS THEN
            RAISE;
    END CREATE_VIDEO_PROC;

    PROCEDURE DELETE_VIDEO_PROC(
        p_VideoID IN COURSEVIDEO.VideoID%TYPE
    ) IS
    BEGIN
        DELETE FROM CourseVideo
        WHERE VideoID = p_VideoID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20182, 'Video with VideoID ''' || p_VideoID || ''' not found for deletion.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20182 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in DELETE_VIDEO_PROC: ' || SQLERRM);
            END IF;
    END DELETE_VIDEO_PROC;

    PROCEDURE UPDATE_VIDEO_PROC(
        p_VideoID   IN COURSEVIDEO.VideoID%TYPE,
        p_LessonID  IN COURSEVIDEO.LessonID%TYPE,
        p_Url       IN COURSEVIDEO.Url%TYPE,
        p_Title     IN COURSEVIDEO.Title%TYPE,
        p_Duration  IN COURSEVIDEO.Duration%TYPE,
        p_SortOrder IN COURSEVIDEO.SortOrder%TYPE
    ) IS
    BEGIN
        UPDATE CourseVideo
        SET LessonID  = p_LessonID,
            Url       = p_Url,
            Title     = p_Title,
            Duration  = p_Duration,
            SortOrder = p_SortOrder
        WHERE VideoID = p_VideoID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20181, 'Video with VideoID ''' || p_VideoID || ''' not found for update.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20181 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UPDATE_VIDEO_PROC: ' || SQLERRM);
            END IF;
    END UPDATE_VIDEO_PROC;

    FUNCTION GET_VIDEO_BY_ID_FUNC(
        p_VideoID IN COURSEVIDEO.VideoID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                VideoID,
                LessonID,
                Url,
                Title,
                SortOrder,
                Duration,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                CourseVideo
            WHERE
                VideoID = p_VideoID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_VIDEO_BY_ID_FUNC;

    FUNCTION GET_VIDEOS_BY_LESSON_FUNC(
        p_LessonID IN COURSEVIDEO.LessonID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                VideoID,
                LessonID,
                Url,
                Title,
                SortOrder,
                Duration,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                CourseVideo
            WHERE
                LessonID = p_LessonID
            ORDER BY SortOrder ASC;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_VIDEOS_BY_LESSON_FUNC;

END COURSE_VIDEO_PKG;
/

