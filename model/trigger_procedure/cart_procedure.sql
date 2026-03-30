CREATE OR REPLACE PACKAGE CART_PKG AS

    PROCEDURE CREATE_CART_PROC(
        p_CartID IN CART.CartID%TYPE,
        p_UserID IN CART.UserID%TYPE
    );

    FUNCTION GET_CART_BY_USER_FUNC(
        p_UserID IN CART.UserID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_CART_BY_ID_FUNC(
        p_CartID IN CART.CartID%TYPE
    ) RETURN SYS_REFCURSOR;

    PROCEDURE DELETE_CART_PROC(
        p_CartID IN CART.CartID%TYPE
    );

END CART_PKG;
/

CREATE OR REPLACE PACKAGE BODY CART_PKG AS

    PROCEDURE CREATE_CART_PROC(
        p_CartID IN CART.CartID%TYPE,
        p_UserID IN CART.UserID%TYPE
    ) IS
    BEGIN
        INSERT INTO CART (CartID, UserID)
        VALUES (p_CartID, p_UserID);
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END CREATE_CART_PROC;

    FUNCTION GET_CART_BY_USER_FUNC(
        p_UserID IN CART.UserID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                CartID,
                UserID,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                CART
            WHERE
                UserID = p_UserID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_CART_BY_USER_FUNC;

    FUNCTION GET_CART_BY_ID_FUNC(
        p_CartID IN CART.CartID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                CartID,
                UserID,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                CART
            WHERE
                CartID = p_CartID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_CART_BY_ID_FUNC;

    PROCEDURE DELETE_CART_PROC(
        p_CartID IN CART.CartID%TYPE
    ) IS
    BEGIN
        DELETE FROM CART
        WHERE CartID = p_CartID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20001, 'Cart with ID ''' || p_CartID || ''' not found, or no rows deleted.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20001 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in DELETE_CART_PROC: ' || SQLERRM);
            END IF;
    END DELETE_CART_PROC;

END CART_PKG;
/

