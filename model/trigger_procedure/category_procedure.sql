CREATE OR REPLACE PACKAGE CATEGORY_PKG AS

    PROCEDURE CREATE_CATEGORY_PROC(
        p_Name       IN CATEGORIES.name%TYPE,
        p_Parent_ID  IN CATEGORIES.parent_id%TYPE,
        p_Sort_Order IN CATEGORIES.sort_order%TYPE
    );

    PROCEDURE DELETE_CATEGORY_PROC(
        p_ID IN CATEGORIES.id%TYPE
    );

    PROCEDURE UPDATE_CATEGORY_PROC(
        p_ID         IN CATEGORIES.id%TYPE,
        p_Name       IN CATEGORIES.name%TYPE,
        p_Parent_ID  IN CATEGORIES.parent_id%TYPE,
        p_Sort_Order IN CATEGORIES.sort_order%TYPE
    );

    FUNCTION GET_CATEGORY_BY_ID_FUNC(
        p_ID IN CATEGORIES.id%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_ALL_CATEGORIES_FUNC
    RETURN SYS_REFCURSOR;

END CATEGORY_PKG;
/

CREATE OR REPLACE PACKAGE BODY CATEGORY_PKG AS

    PROCEDURE CREATE_CATEGORY_PROC(
        p_Name       IN CATEGORIES.name%TYPE,
        p_Parent_ID  IN CATEGORIES.parent_id%TYPE,
        p_Sort_Order IN CATEGORIES.sort_order%TYPE
    ) IS
    BEGIN
        INSERT INTO CATEGORIES (name, parent_id, sort_order)
        VALUES (p_Name, p_Parent_ID, p_Sort_Order);
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END CREATE_CATEGORY_PROC;

    PROCEDURE DELETE_CATEGORY_PROC(
        p_ID IN CATEGORIES.id%TYPE
    ) IS
    BEGIN
        DELETE FROM CATEGORIES
        WHERE id = p_ID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20021, 'Category with ID ' || p_ID || ' not found, or no rows deleted.');
        END IF;
        -- On delete cascade for parent categories and linked tables handled separately
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20021 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in DELETE_CATEGORY_PROC: ' || SQLERRM);
            END IF;
    END DELETE_CATEGORY_PROC;

    PROCEDURE UPDATE_CATEGORY_PROC(
        p_ID         IN CATEGORIES.id%TYPE,
        p_Name       IN CATEGORIES.name%TYPE,
        p_Parent_ID  IN CATEGORIES.parent_id%TYPE,
        p_Sort_Order IN CATEGORIES.sort_order%TYPE
    ) IS
    BEGIN
        UPDATE CATEGORIES
        SET name = p_Name,
            parent_id = p_Parent_ID,
            sort_order = p_Sort_Order
        WHERE id = p_ID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20022, 'Category with ID ' || p_ID || ' not found for update.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20022 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UPDATE_CATEGORY_PROC: ' || SQLERRM);
            END IF;
    END UPDATE_CATEGORY_PROC;

    FUNCTION GET_CATEGORY_BY_ID_FUNC(
        p_ID IN CATEGORIES.id%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                id,
                name,
                parent_id,
                sort_order,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                CATEGORIES
            WHERE
                id = p_ID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_CATEGORY_BY_ID_FUNC;

    FUNCTION GET_ALL_CATEGORIES_FUNC
    RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                id,
                name,
                parent_id,
                sort_order,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                CATEGORIES
            ORDER BY sort_order ASC, name ASC;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_ALL_CATEGORIES_FUNC;

END CATEGORY_PKG;
/

