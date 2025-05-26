CREATE OR REPLACE PACKAGE RESOURCE_PKG AS

    PROCEDURE CREATE_NEW_RESOURCE (
        p_resourceID IN COURSERESOURCE.ResourceID%TYPE,
        p_lessonID IN COURSERESOURCE.LessonID%TYPE,
        p_resourcePath IN COURSERESOURCE.ResourcePath%TYPE,
        p_title IN COURSERESOURCE.Title%TYPE,
        p_sortOrder IN COURSERESOURCE.SortOrder%TYPE,
        p_success OUT BOOLEAN
    );

    PROCEDURE UPDATE_RESOURCE (
        p_resourceID IN COURSERESOURCE.ResourceID%TYPE,
        p_lessonID IN COURSERESOURCE.LessonID%TYPE,
        p_resourcePath IN COURSERESOURCE.ResourcePath%TYPE,
        p_title IN COURSERESOURCE.Title%TYPE,
        p_sortOrder IN COURSERESOURCE.SortOrder%TYPE,
        p_success OUT BOOLEAN
    );

    PROCEDURE DELETE_RESOURCE (
        p_resourceID IN COURSERESOURCE.ResourceID%TYPE,
        p_success OUT BOOLEAN
    );

    TYPE t_resource_cursor IS REF CURSOR;

    PROCEDURE GET_RESOURCE_BY_ID (
        p_resourceID IN COURSERESOURCE.ResourceID%TYPE,
        p_found_resourceID OUT COURSERESOURCE.ResourceID%TYPE,
        p_lessonID OUT COURSERESOURCE.LessonID%TYPE,
        p_resourcePath OUT COURSERESOURCE.ResourcePath%TYPE,
        p_title OUT COURSERESOURCE.Title%TYPE,
        p_sortOrder OUT COURSERESOURCE.SortOrder%TYPE,
        p_created_at OUT VARCHAR2
    );

    PROCEDURE GET_RESOURCES_BY_LESSON_ID (
        p_lessonID IN COURSERESOURCE.LessonID%TYPE,
        p_cursor OUT t_resource_cursor
    );

    PROCEDURE GET_ALL_RESOURCES (
        p_cursor OUT t_resource_cursor
    );

END RESOURCE_PKG;
/

CREATE OR REPLACE PACKAGE BODY RESOURCE_PKG AS

    PROCEDURE CREATE_NEW_RESOURCE (
        p_resourceID IN COURSERESOURCE.ResourceID%TYPE,
        p_lessonID IN COURSERESOURCE.LessonID%TYPE,
        p_resourcePath IN COURSERESOURCE.ResourcePath%TYPE,
        p_title IN COURSERESOURCE.Title%TYPE,
        p_sortOrder IN COURSERESOURCE.SortOrder%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM COURSERESOURCE WHERE ResourceID = p_resourceID;

        IF v_count = 0 THEN
            INSERT INTO COURSERESOURCE (ResourceID, LessonID, ResourcePath, Title, SortOrder)
            VALUES (p_resourceID, p_lessonID, p_resourcePath, p_title, p_sortOrder);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: ResourceID ' || p_resourceID || ' already exists.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_NEW_RESOURCE: ' || SQLERRM);
    END CREATE_NEW_RESOURCE;

    PROCEDURE UPDATE_RESOURCE (
        p_resourceID IN COURSERESOURCE.ResourceID%TYPE,
        p_lessonID IN COURSERESOURCE.LessonID%TYPE,
        p_resourcePath IN COURSERESOURCE.ResourcePath%TYPE,
        p_title IN COURSERESOURCE.Title%TYPE,
        p_sortOrder IN COURSERESOURCE.SortOrder%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        UPDATE COURSERESOURCE
        SET
            LessonID = p_lessonID,
            ResourcePath = p_resourcePath,
            Title = p_title,
            SortOrder = p_sortOrder
        WHERE ResourceID = p_resourceID;

        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No resource updated for ResourceID ' || p_resourceID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_RESOURCE: ' || SQLERRM);
    END UPDATE_RESOURCE;

    PROCEDURE DELETE_RESOURCE (
        p_resourceID IN COURSERESOURCE.ResourceID%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM COURSERESOURCE
        WHERE ResourceID = p_resourceID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No resource deleted for ResourceID ' || p_resourceID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_RESOURCE: ' || SQLERRM);
    END DELETE_RESOURCE;

    PROCEDURE GET_RESOURCE_BY_ID (
        p_resourceID IN COURSERESOURCE.ResourceID%TYPE,
        p_found_resourceID OUT COURSERESOURCE.ResourceID%TYPE,
        p_lessonID OUT COURSERESOURCE.LessonID%TYPE,
        p_resourcePath OUT COURSERESOURCE.ResourcePath%TYPE,
        p_title OUT COURSERESOURCE.Title%TYPE,
        p_sortOrder OUT COURSERESOURCE.SortOrder%TYPE,
        p_created_at OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT ResourceID, LessonID, ResourcePath, Title, SortOrder,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_resourceID, p_lessonID, p_resourcePath, p_title, p_sortOrder, p_created_at
        FROM COURSERESOURCE
        WHERE ResourceID = p_resourceID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_resourceID := NULL;
            p_lessonID := NULL;
            p_resourcePath := NULL;
            p_title := NULL;
            p_sortOrder := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_resourceID := NULL;
            p_lessonID := NULL;
            p_resourcePath := NULL;
            p_title := NULL;
            p_sortOrder := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_RESOURCE_BY_ID: ' || SQLERRM);
    END GET_RESOURCE_BY_ID;

    PROCEDURE GET_RESOURCES_BY_LESSON_ID (
        p_lessonID IN COURSERESOURCE.LessonID%TYPE,
        p_cursor OUT t_resource_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT ResourceID, LessonID, ResourcePath, Title, SortOrder,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM COURSERESOURCE
            WHERE LessonID = p_lessonID
            ORDER BY SortOrder ASC;
    END GET_RESOURCES_BY_LESSON_ID;

    PROCEDURE GET_ALL_RESOURCES (
        p_cursor OUT t_resource_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT ResourceID, LessonID, ResourcePath, Title, SortOrder,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM COURSERESOURCE
            ORDER BY SortOrder ASC;
    END GET_ALL_RESOURCES;

END RESOURCE_PKG;