CREATE OR REPLACE PACKAGE ORDER_PKG AS

    PROCEDURE CREATE_NEW_ORDER (
        p_orderID     IN ORDERS.OrderID%TYPE,
        p_userID      IN ORDERS.UserID%TYPE,
        p_orderDate   IN VARCHAR2,
        p_totalAmount IN ORDERS.TotalAmount%TYPE,
        p_success     OUT BOOLEAN
    );

    PROCEDURE UPDATE_ORDER (
        p_orderID     IN ORDERS.OrderID%TYPE,
        p_userID      IN ORDERS.UserID%TYPE,
        p_orderDate   IN VARCHAR2,
        p_totalAmount IN ORDERS.TotalAmount%TYPE,
        p_success     OUT BOOLEAN
    );

    PROCEDURE DELETE_ORDER (
        p_orderID IN ORDERS.OrderID%TYPE,
        p_success OUT BOOLEAN
    );

    TYPE t_order_cursor IS REF CURSOR;

    PROCEDURE GET_ORDER_BY_ID (
        p_orderID           IN ORDERS.OrderID%TYPE,
        p_found_orderID     OUT ORDERS.OrderID%TYPE,
        p_userID            OUT ORDERS.UserID%TYPE,
        p_orderDate         OUT VARCHAR2,
        p_totalAmount       OUT ORDERS.TotalAmount%TYPE,
        p_created_at        OUT VARCHAR2
    );

    PROCEDURE GET_ORDERS_BY_USER_ID (
        p_userID  IN ORDERS.UserID%TYPE,
        p_cursor  OUT t_order_cursor
    );

END ORDER_PKG;
/

CREATE OR REPLACE PACKAGE BODY ORDER_PKG AS

    PROCEDURE CREATE_NEW_ORDER (
        p_orderID IN ORDERS.OrderID%TYPE,
        p_userID IN ORDERS.UserID%TYPE,
        p_orderDate IN VARCHAR2,
        p_totalAmount IN ORDERS.TotalAmount%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM ORDERS WHERE OrderID = p_orderID;

        IF v_count = 0 THEN
            INSERT INTO ORDERS (OrderID, UserID, OrderDate, TotalAmount)
            VALUES (p_orderID, p_userID, TO_TIMESTAMP(p_orderDate, 'YYYY-MM-DD HH24:MI:SS.FF6'), p_totalAmount);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: OrderID ' || p_orderID || ' already exists.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_NEW_ORDER: ' || SQLERRM);
    END CREATE_NEW_ORDER;

    PROCEDURE UPDATE_ORDER (
        p_orderID IN ORDERS.OrderID%TYPE,
        p_userID IN ORDERS.UserID%TYPE,
        p_orderDate IN VARCHAR2,
        p_totalAmount IN ORDERS.TotalAmount%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        UPDATE ORDERS
        SET
            UserID = p_userID,
            OrderDate = TO_TIMESTAMP(p_orderDate, 'YYYY-MM-DD HH24:MI:SS.FF6'),
            TotalAmount = p_totalAmount
        WHERE OrderID = p_orderID;

        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No order updated for OrderID ' || p_orderID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_ORDER: ' || SQLERRM);
    END UPDATE_ORDER;

    PROCEDURE DELETE_ORDER (
        p_orderID IN ORDERS.OrderID%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM ORDERS
        WHERE OrderID = p_orderID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No order deleted for OrderID ' || p_orderID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_ORDER: ' || SQLERRM);
    END DELETE_ORDER;

    PROCEDURE GET_ORDER_BY_ID (
        p_orderID IN ORDERS.OrderID%TYPE,
        p_found_orderID OUT ORDERS.OrderID%TYPE,
        p_userID OUT ORDERS.UserID%TYPE,
        p_orderDate OUT VARCHAR2,
        p_totalAmount OUT ORDERS.TotalAmount%TYPE,
        p_created_at OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT OrderID, UserID,
               TO_CHAR(OrderDate, 'YYYY-MM-DD HH24:MI:SS.FF6'),
               TotalAmount,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_orderID, p_userID, p_orderDate, p_totalAmount, p_created_at
        FROM ORDERS
        WHERE OrderID = p_orderID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_orderID := NULL;
            p_userID := NULL;
            p_orderDate := NULL;
            p_totalAmount := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_orderID := NULL;
            p_userID := NULL;
            p_orderDate := NULL;
            p_totalAmount := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_ORDER_BY_ID: ' || SQLERRM);
    END GET_ORDER_BY_ID;

    PROCEDURE GET_ORDERS_BY_USER_ID (
        p_userID IN ORDERS.UserID%TYPE,
        p_cursor OUT t_order_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT OrderID, UserID,
                   TO_CHAR(OrderDate, 'YYYY-MM-DD HH24:MI:SS.FF6') AS order_date_formatted,
                   TotalAmount,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM ORDERS
            WHERE UserID = p_userID
            ORDER BY OrderDate DESC;
    END GET_ORDERS_BY_USER_ID;

END ORDER_PKG;