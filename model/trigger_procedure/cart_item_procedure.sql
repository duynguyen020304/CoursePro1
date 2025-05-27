CREATE OR REPLACE PACKAGE CART_ITEM_PKG AS

    PROCEDURE CREATE_ITEM_PROC(
        p_CartItemID IN CARTITEM.CartItemID%TYPE,
        p_CartID     IN CARTITEM.CartID%TYPE,
        p_CourseID   IN CARTITEM.CourseID%TYPE,
        p_Quantity   IN CARTITEM.Quantity%TYPE
    );

    FUNCTION GET_ITEMS_BY_CART_FUNC(
        p_CartID IN CARTITEM.CartID%TYPE
    ) RETURN SYS_REFCURSOR;

    PROCEDURE DELETE_ITEM_PROC(
        p_CartItemID IN CARTITEM.CartItemID%TYPE
    );

    FUNCTION GET_ITEM_BY_ID_FUNC(
        p_CartItemID IN CARTITEM.CartItemID%TYPE
    ) RETURN SYS_REFCURSOR;

    PROCEDURE UPDATE_ITEM_QUANTITY_PROC(
        p_CartItemID IN CARTITEM.CartItemID%TYPE,
        p_Quantity   IN CARTITEM.Quantity%TYPE
    );

    PROCEDURE CLEAR_CART_PROC(
        p_CartID IN CARTITEM.CartID%TYPE
    );

END CART_ITEM_PKG;
/

CREATE OR REPLACE PACKAGE BODY CART_ITEM_PKG AS

    PROCEDURE CREATE_ITEM_PROC(
        p_CartItemID IN CARTITEM.CartItemID%TYPE,
        p_CartID     IN CARTITEM.CartID%TYPE,
        p_CourseID   IN CARTITEM.CourseID%TYPE,
        p_Quantity   IN CARTITEM.Quantity%TYPE
    ) IS
    BEGIN
        IF p_Quantity <= 0 THEN
             RAISE_APPLICATION_ERROR(-20010, 'Quantity must be greater than 0.');
        END IF;

        INSERT INTO CARTITEM (CartItemID, CartID, CourseID, Quantity)
        VALUES (p_CartItemID, p_CartID, p_CourseID, p_Quantity);
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END CREATE_ITEM_PROC;

    FUNCTION GET_ITEMS_BY_CART_FUNC(
        p_CartID IN CARTITEM.CartID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                CartItemID,
                CartID,
                CourseID,
                Quantity,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                CARTITEM
            WHERE
                CartID = p_CartID
            ORDER BY CourseID ASC;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_ITEMS_BY_CART_FUNC;

    PROCEDURE DELETE_ITEM_PROC(
        p_CartItemID IN CARTITEM.CartItemID%TYPE
    ) IS
    BEGIN
        DELETE FROM CARTITEM
        WHERE CartItemID = p_CartItemID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20011, 'CartItem with ID ''' || p_CartItemID || ''' not found, or no rows deleted.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20011 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in DELETE_ITEM_PROC: ' || SQLERRM);
            END IF;
    END DELETE_ITEM_PROC;

    FUNCTION GET_ITEM_BY_ID_FUNC(
        p_CartItemID IN CARTITEM.CartItemID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                CartItemID,
                CartID,
                CourseID,
                Quantity,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                CARTITEM
            WHERE
                CartItemID = p_CartItemID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_ITEM_BY_ID_FUNC;

    PROCEDURE UPDATE_ITEM_QUANTITY_PROC(
        p_CartItemID IN CARTITEM.CartItemID%TYPE,
        p_Quantity   IN CARTITEM.Quantity%TYPE
    ) IS
    BEGIN
        IF p_Quantity <= 0 THEN
            DELETE_ITEM_PROC(p_CartItemID => p_CartItemID);
        ELSE
            UPDATE CARTITEM
            SET Quantity = p_Quantity
            WHERE CartItemID = p_CartItemID;

            IF SQL%ROWCOUNT = 0 THEN
                RAISE_APPLICATION_ERROR(-20012, 'CartItem with ID ''' || p_CartItemID || ''' not found for update.');
            END IF;
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20011 OR SQLCODE = -20012 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UPDATE_ITEM_QUANTITY_PROC: ' || SQLERRM);
            END IF;
    END UPDATE_ITEM_QUANTITY_PROC;

    PROCEDURE CLEAR_CART_PROC(
        p_CartID IN CARTITEM.CartID%TYPE
    ) IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM CARTITEM
        WHERE CartID = p_CartID;
        RAISE_APPLICATION_ERROR(-20013, SQL%ROWCOUNT || ' items cleared from cart ' || p_CartID);
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END CLEAR_CART_PROC;

END CART_ITEM_PKG;
/

SHOW ERRORS PACKAGE CART_ITEM_PKG;
SHOW ERRORS PACKAGE BODY CART_ITEM_PKG;