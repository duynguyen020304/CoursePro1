CREATE OR REPLACE PACKAGE COURSE_RESOURCE_PKG AS

    PROCEDURE CREATE_RESOURCE_PROC(
        p_ResourceID   IN COURSERESOURCE.ResourceID%TYPE,
        p_LessonID     IN COURSERESOURCE.LessonID%TYPE,
        p_ResourcePath IN COURSERESOURCE.ResourcePath%TYPE,
        p_Title        IN COURSERESOURCE.Title%TYPE,
        p_SortOrder    IN COURSERESOURCE.SortOrder%TYPE
    );

    FUNCTION GET_RESOURCE_BY_ID_FUNC(
        p_ResourceID IN COURSERESOURCE.ResourceID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_RESOURCES_BY_LESSON_FUNC(
        p_LessonID IN COURSERESOURCE.LessonID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_ALL_RESOURCES_FUNC
        RETURN SYS_REFCURSOR;

    PROCEDURE UPDATE_RESOURCE_PROC(
        p_ResourceID   IN COURSERESOURCE.ResourceID%TYPE,
        p_LessonID     IN COURSERESOURCE.LessonID%TYPE,
        p_ResourcePath IN COURSERESOURCE.ResourcePath%TYPE,
        p_Title        IN COURSERESOURCE.Title%TYPE,
        p_SortOrder    IN COURSERESOURCE.SortOrder%TYPE
    );

    PROCEDURE DELETE_RESOURCE_PROC(
        p_ResourceID IN COURSERESOURCE.ResourceID%TYPE
    );

END COURSE_RESOURCE_PKG;
/

CREATE OR REPLACE PACKAGE BODY COURSE_RESOURCE_PKG AS

    PROCEDURE CREATE_RESOURCE_PROC(
        p_ResourceID   IN COURSERESOURCE.ResourceID%TYPE,
        p_LessonID     IN COURSERESOURCE.LessonID%TYPE,
        p_ResourcePath IN COURSERESOURCE.ResourcePath%TYPE,
        p_Title        IN COURSERESOURCE.Title%TYPE,
        p_SortOrder    IN COURSERESOURCE.SortOrder%TYPE
    ) IS
    BEGIN
        INSERT INTO COURSERESOURCE (ResourceID, LessonID, ResourcePath, Title, SortOrder)
        VALUES (p_ResourceID, p_LessonID, p_ResourcePath, p_Title, p_SortOrder);
        -- PHP BLL checks for affectedRows === 1. SQL%ROWCOUNT will be 1 on success.
        -- Oracle handles PK violation (ResourceID).
        -- Oracle handles FK violation (LessonID).
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            RAISE_APPLICATION_ERROR(-20140, 'Resource with ResourceID ''' || p_ResourceID || ''' already exists.');
        WHEN OTHERS THEN
            RAISE;
    END CREATE_RESOURCE_PROC;

    FUNCTION GET_RESOURCE_BY_ID_FUNC(
        p_ResourceID IN COURSERESOURCE.ResourceID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                ResourceID,
                LessonID,
                ResourcePath,
                Title,
                SortOrder,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                COURSERESOURCE
            WHERE
                ResourceID = p_ResourceID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_RESOURCE_BY_ID_FUNC;

    FUNCTION GET_RESOURCES_BY_LESSON_FUNC(
        p_LessonID IN COURSERESOURCE.LessonID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                ResourceID,
                LessonID,
                ResourcePath,
                Title,
                SortOrder,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                COURSERESOURCE
            WHERE
                LessonID = p_LessonID
            ORDER BY SortOrder ASC;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_RESOURCES_BY_LESSON_FUNC;

    FUNCTION GET_ALL_RESOURCES_FUNC
        RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                ResourceID,
                LessonID,
                ResourcePath,
                Title,
                SortOrder,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                COURSERESOURCE
            ORDER BY SortOrder ASC;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_ALL_RESOURCES_FUNC;

    PROCEDURE UPDATE_RESOURCE_PROC(
        p_ResourceID   IN COURSERESOURCE.ResourceID%TYPE,
        p_LessonID     IN COURSERESOURCE.LessonID%TYPE,
        p_ResourcePath IN COURSERESOURCE.ResourcePath%TYPE,
        p_Title        IN COURSERESOURCE.Title%TYPE,
        p_SortOrder    IN COURSERESOURCE.SortOrder%TYPE
    ) IS
    BEGIN
        UPDATE COURSERESOURCE
        SET LessonID     = p_LessonID,
            ResourcePath = p_ResourcePath,
            Title        = p_Title,
            SortOrder    = p_SortOrder
        WHERE ResourceID = p_ResourceID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20141, 'Resource with ResourceID ''' || p_ResourceID || ''' not found for update.');
        END IF;
        -- PHP BLL checks ($stid !== false). SQL%ROWCOUNT check here ensures row was found.
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20141 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UPDATE_RESOURCE_PROC: ' || SQLERRM);
            END IF;
    END UPDATE_RESOURCE_PROC;

    PROCEDURE DELETE_RESOURCE_PROC(
        p_ResourceID IN COURSERESOURCE.ResourceID%TYPE
    ) IS
    BEGIN
        DELETE FROM COURSERESOURCE
        WHERE ResourceID = p_ResourceID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20142, 'Resource with ResourceID ''' || p_ResourceID || ''' not found for deletion.');
        END IF;
    END DELETE_RESOURCE_PROC;

END COURSE_RESOURCE_PKG;
/
