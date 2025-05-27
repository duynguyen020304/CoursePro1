
CREATE OR REPLACE PACKAGE CATEGORY_PKG AS
    PROCEDURE CREATE_NEW_CATEGORY (
        p_name        IN CATEGORIES.Name%TYPE,
        p_parent_id   IN CATEGORIES.Parent_ID%TYPE,
        p_sort_order  IN CATEGORIES.Sort_Order%TYPE,
        p_new_id      OUT CATEGORIES.ID%TYPE,
        p_success     OUT BOOLEAN
    );
    PROCEDURE DELETE_CATEGORY (
        p_id        IN CATEGORIES.ID%TYPE,
        p_success   OUT BOOLEAN
    );
    PROCEDURE UPDATE_CATEGORY (
        p_id          IN CATEGORIES.ID%TYPE,
        p_name        IN CATEGORIES.Name%TYPE,
        p_parent_id   IN CATEGORIES.Parent_ID%TYPE,
        p_sort_order  IN CATEGORIES.Sort_Order%TYPE,
        p_success     OUT BOOLEAN
    );
    PROCEDURE GET_CATEGORY_BY_ID (
        p_id                IN CATEGORIES.ID%TYPE,
        p_found_id          OUT CATEGORIES.ID%TYPE,
        p_name              OUT CATEGORIES.Name%TYPE,
        p_parent_id         OUT CATEGORIES.Parent_ID%TYPE,
        p_sort_order        OUT CATEGORIES.Sort_Order%TYPE,
        p_created_at        OUT VARCHAR2
    );
    TYPE t_category_cursor IS REF CURSOR;
    PROCEDURE GET_ALL_CATEGORIES (
        p_cursor    OUT t_category_cursor
    );
END CATEGORY_PKG;
/

CREATE OR REPLACE PACKAGE BODY CATEGORY_PKG AS
    PROCEDURE CREATE_NEW_CATEGORY (
        p_name        IN CATEGORIES.Name%TYPE,
        p_parent_id   IN CATEGORIES.Parent_ID%TYPE,
        p_sort_order  IN CATEGORIES.Sort_Order%TYPE,
        p_new_id      OUT CATEGORIES.ID%TYPE,
        p_success     OUT BOOLEAN
    )
        IS
    BEGIN
        INSERT INTO CATEGORIES (Name, Parent_ID, Sort_Order)
        VALUES (p_name, p_parent_id, p_sort_order)
        RETURNING ID INTO p_new_id;
        COMMIT;
        p_success := TRUE;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_new_id := NULL;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_NEW_CATEGORY: ' || SQLERRM);
    END CREATE_NEW_CATEGORY;

    PROCEDURE DELETE_CATEGORY (
        p_id        IN CATEGORIES.ID%TYPE,
        p_success   OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM CATEGORIES WHERE ID = p_id;
        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No category deleted for ID ' || p_id);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_CATEGORY: ' || SQLERRM);
    END DELETE_CATEGORY;

    PROCEDURE UPDATE_CATEGORY (
        p_id          IN CATEGORIES.ID%TYPE,
        p_name        IN CATEGORIES.Name%TYPE,
        p_parent_id   IN CATEGORIES.Parent_ID%TYPE,
        p_sort_order  IN CATEGORIES.Sort_Order%TYPE,
        p_success     OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        UPDATE CATEGORIES
        SET
            Name = p_name,
            Parent_ID = p_parent_id,
            Sort_Order = p_sort_order
        WHERE ID = p_id;
        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No category updated for ID ' || p_id);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_CATEGORY: ' || SQLERRM);
    END UPDATE_CATEGORY;

    PROCEDURE GET_CATEGORY_BY_ID (
        p_id                IN CATEGORIES.ID%TYPE,
        p_found_id          OUT CATEGORIES.ID%TYPE,
        p_name              OUT CATEGORIES.Name%TYPE,
        p_parent_id         OUT CATEGORIES.Parent_ID%TYPE,
        p_sort_order        OUT CATEGORIES.Sort_Order%TYPE,
        p_created_at        OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT ID, Name, Parent_ID, Sort_Order,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_id, p_name, p_parent_id, p_sort_order, p_created_at
        FROM CATEGORIES
        WHERE ID = p_id;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_id := NULL;
            p_name := NULL;
            p_parent_id := NULL;
            p_sort_order := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_id := NULL;
            p_name := NULL;
            p_parent_id := NULL;
            p_sort_order := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_CATEGORY_BY_ID: ' || SQLERRM);
    END GET_CATEGORY_BY_ID;

    PROCEDURE GET_ALL_CATEGORIES (
        p_cursor    OUT t_category_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT ID, Name, Parent_ID, Sort_Order,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM CATEGORIES
            ORDER BY Sort_Order ASC, Name ASC;
    EXCEPTION
        WHEN OTHERS THEN
            IF p_cursor%ISOPEN THEN
                CLOSE p_cursor;
            END IF;
            DBMS_OUTPUT.PUT_LINE('Error in GET_ALL_CATEGORIES: ' || SQLERRM);
    END GET_ALL_CATEGORIES;
END CATEGORY_PKG;
/

SET SERVEROUTPUT ON;

-- Các ví dụ sử dụng các procedure
DECLARE
    v_success BOOLEAN;
BEGIN
    CATEGORY_PKG.CREATE_NEW_CATEGORY('Electronics', NULL, 10, v_new_id, v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Category created: ID=' || v_new_id || ', Name=Electronics'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to create category.'); END IF;

    CATEGORY_PKG.CREATE_NEW_CATEGORY('Laptops', v_new_id, 20, v_new_id, v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Category created: ID=' || v_new_id || ', Name=Laptops'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to create category.'); END IF;

    CATEGORY_PKG.CREATE_NEW_CATEGORY('Smartphones', 1, 30, v_new_id, v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Category created: ID=' || v_new_id || ', Name=Smartphones'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to create category.'); END IF;

    CATEGORY_PKG.CREATE_NEW_CATEGORY('Books', NULL, 5, v_new_id, v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Category created: ID=' || v_new_id || ', Name=Books'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to create category.'); END IF;
END;
/

DECLARE
    v_id            CATEGORIES.ID%TYPE;
    v_name          CATEGORIES.Name%TYPE;
    v_parent_id     CATEGORIES.Parent_ID%TYPE;
    v_sort_order    CATEGORIES.Sort_Order%TYPE;
    v_created_at    VARCHAR2(50);
BEGIN
    CATEGORY_PKG.GET_CATEGORY_BY_ID(1, v_id, v_name, v_parent_id, v_sort_order, v_created_at);
    IF v_id IS NOT NULL THEN
        DBMS_OUTPUT.PUT_LINE('Category found for ID 1:');
        DBMS_OUTPUT.PUT_LINE('  ID: ' || v_id || ', Name: ' || v_name || ', Parent_ID: ' || NVL(TO_CHAR(v_parent_id), 'NULL') || ', Sort_Order: ' || v_sort_order || ', Created At: ' || v_created_at);
    ELSE
        DBMS_OUTPUT.PUT_LINE('No category found for ID 1.');
    END IF;

    CATEGORY_PKG.GET_CATEGORY_BY_ID(999, v_id, v_name, v_parent_id, v_sort_order, v_created_at);
    IF v_id IS NOT NULL THEN
        DBMS_OUTPUT.PUT_LINE('Category found for ID 999:');
    ELSE
        DBMS_OUTPUT.PUT_LINE('No category found for ID 999 (expected).');
    END IF;
END;
/

DECLARE
    v_success BOOLEAN;
BEGIN
    CATEGORY_PKG.UPDATE_CATEGORY(1, 'Electronics & Gadgets', NULL, 15, v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Category ID 1 updated successfully.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to update category ID 1.'); END IF;
END;
/

DECLARE
    v_cursor        CATEGORY_PKG.t_category_cursor;
    v_id            CATEGORIES.ID%TYPE;
    v_name          CATEGORIES.Name%TYPE;
    v_parent_id     CATEGORIES.Parent_ID%TYPE;
    v_sort_order    CATEGORIES.Sort_Order%TYPE;
    v_created_at    VARCHAR2(50);
BEGIN
    DBMS_OUTPUT.PUT_LINE('All Categories:');
    CATEGORY_PKG.GET_ALL_CATEGORIES(v_cursor);
    LOOP
        FETCH v_cursor INTO v_id, v_name, v_parent_id, v_sort_order, v_created_at;
        EXIT WHEN v_cursor%NOTFOUND;
        DBMS_OUTPUT.PUT_LINE('  ID: ' || v_id || ', Name: ' || v_name || ', Parent_ID: ' || NVL(TO_CHAR(v_parent_id), 'NULL') || ', Sort_Order: ' || v_sort_order);
    END LOOP;
    CLOSE v_cursor;
END;
/

-- Kiểm tra lại dữ liệu sau tất cả các thao tác
SELECT * FROM CATEGORIES;