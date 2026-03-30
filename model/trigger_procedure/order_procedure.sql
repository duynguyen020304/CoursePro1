CREATE OR REPLACE PACKAGE ORDER_PKG AS

    PROCEDURE CREATE_ORDER_PROC(
        p_OrderID     IN ORDERS.OrderID%TYPE,
        p_UserID      IN ORDERS.UserID%TYPE,
        p_OrderDate   IN ORDERS.OrderDate%TYPE,
        p_TotalAmount IN ORDERS.TotalAmount%TYPE
    );

    FUNCTION GET_ORDER_BY_ID_FUNC(
        p_OrderID IN ORDERS.OrderID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_ORDERS_BY_USER_FUNC(
        p_UserID IN ORDERS.UserID%TYPE
    ) RETURN SYS_REFCURSOR;

    PROCEDURE UPDATE_ORDER_PROC(
        p_OrderID     IN ORDERS.OrderID%TYPE,
        p_UserID      IN ORDERS.UserID%TYPE,
        p_OrderDate   IN ORDERS.OrderDate%TYPE,
        p_TotalAmount IN ORDERS.TotalAmount%TYPE
    );

    PROCEDURE DELETE_ORDER_PROC(
        p_OrderID IN ORDERS.OrderID%TYPE
    );

END ORDER_PKG;
/

CREATE OR REPLACE PACKAGE BODY ORDER_PKG AS

    PROCEDURE CREATE_ORDER_PROC(
        p_OrderID     IN ORDERS.OrderID%TYPE,
        p_UserID      IN ORDERS.UserID%TYPE,
        p_OrderDate   IN ORDERS.OrderDate%TYPE,
        p_TotalAmount IN ORDERS.TotalAmount%TYPE
    ) IS
    BEGIN
        INSERT INTO ORDERS (OrderID, UserID, OrderDate, TotalAmount)
        VALUES (p_OrderID, p_UserID, p_OrderDate, p_TotalAmount);
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            RAISE_APPLICATION_ERROR(-20110, 'Order with OrderID ''' || p_OrderID || ''' already exists.');
        WHEN OTHERS THEN
            RAISE;
    END CREATE_ORDER_PROC;

    FUNCTION GET_ORDER_BY_ID_FUNC(
        p_OrderID IN ORDERS.OrderID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                OrderID,
                UserID,
                TotalAmount,
                TO_CHAR(OrderDate, 'YYYY-MM-DD HH24:MI:SS.FF6') AS order_date_formatted,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                ORDERS
            WHERE
                OrderID = p_OrderID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_ORDER_BY_ID_FUNC;

    FUNCTION GET_ORDERS_BY_USER_FUNC(
        p_UserID IN ORDERS.UserID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                OrderID,
                UserID,
                TotalAmount,
                TO_CHAR(OrderDate, 'YYYY-MM-DD HH24:MI:SS.FF6') AS order_date_formatted,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                ORDERS
            WHERE
                UserID = p_UserID
            ORDER BY OrderDate DESC;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_ORDERS_BY_USER_FUNC;

    PROCEDURE UPDATE_ORDER_PROC(
        p_OrderID     IN ORDERS.OrderID%TYPE,
        p_UserID      IN ORDERS.UserID%TYPE,
        p_OrderDate   IN ORDERS.OrderDate%TYPE,
        p_TotalAmount IN ORDERS.TotalAmount%TYPE
    ) IS
    BEGIN
        UPDATE ORDERS
        SET UserID = p_UserID,
            OrderDate = p_OrderDate,
            TotalAmount = p_TotalAmount
        WHERE OrderID = p_OrderID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20111, 'Order with OrderID ''' || p_OrderID || ''' not found for update.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20111 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UPDATE_ORDER_PROC: ' || SQLERRM);
            END IF;
    END UPDATE_ORDER_PROC;

    PROCEDURE DELETE_ORDER_PROC(
        p_OrderID IN ORDERS.OrderID%TYPE
    ) IS
    BEGIN
        DELETE FROM ORDERS
        WHERE OrderID = p_OrderID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20112, 'Order with OrderID ''' || p_OrderID || ''' not found for deletion.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20112 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in DELETE_ORDER_PROC: ' || SQLERRM);
            END IF;
    END DELETE_ORDER_PROC;

END ORDER_PKG;
/