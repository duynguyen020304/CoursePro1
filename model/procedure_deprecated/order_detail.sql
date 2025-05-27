CREATE OR REPLACE PACKAGE ORDER_DETAIL_PKG AS

    PROCEDURE ADD_ORDER_DETAIL (
        p_orderID   IN ORDERDETAIL.OrderID%TYPE,
        p_courseID  IN ORDERDETAIL.CourseID%TYPE,
        p_price     IN ORDERDETAIL.Price%TYPE,
        p_success   OUT BOOLEAN
    );

    PROCEDURE UPDATE_ORDER_DETAIL (
        p_orderID   IN ORDERDETAIL.OrderID%TYPE,
        p_courseID  IN ORDERDETAIL.CourseID%TYPE,
        p_price     IN ORDERDETAIL.Price%TYPE,
        p_success   OUT BOOLEAN
    );

    PROCEDURE DELETE_ORDER_DETAIL (
        p_orderID   IN ORDERDETAIL.OrderID%TYPE,
        p_courseID  IN ORDERDETAIL.CourseID%TYPE,
        p_success   OUT BOOLEAN
    );

    TYPE t_order_detail_cursor IS REF CURSOR;

    PROCEDURE GET_ORDER_DETAIL (
        p_orderID           IN ORDERDETAIL.OrderID%TYPE,
        p_courseID          IN ORDERDETAIL.CourseID%TYPE,
        p_found_orderID     OUT ORDERDETAIL.OrderID%TYPE,
        p_found_courseID    OUT ORDERDETAIL.CourseID%TYPE,
        p_price             OUT ORDERDETAIL.Price%TYPE,
        p_created_at        OUT VARCHAR2
    );

    PROCEDURE GET_DETAILS_BY_ORDER_ID (
        p_orderID IN ORDERDETAIL.OrderID%TYPE,
        p_cursor  OUT t_order_detail_cursor
    );

END ORDER_DETAIL_PKG;
/

CREATE OR REPLACE PACKAGE BODY ORDER_DETAIL_PKG AS

    PROCEDURE ADD_ORDER_DETAIL (
        p_orderID IN ORDERDETAIL.OrderID%TYPE,
        p_courseID IN ORDERDETAIL.CourseID%TYPE,
        p_price IN ORDERDETAIL.Price%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count
        FROM ORDERDETAIL
        WHERE OrderID = p_orderID AND CourseID = p_courseID;

        IF v_count = 0 THEN
            INSERT INTO ORDERDETAIL (OrderID, CourseID, Price)
            VALUES (p_orderID, p_courseID, p_price);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: Order detail already exists for OrderID ' || p_orderID || ' and CourseID ' || p_courseID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in ADD_ORDER_DETAIL: ' || SQLERRM);
    END ADD_ORDER_DETAIL;

    PROCEDURE UPDATE_ORDER_DETAIL (
        p_orderID IN ORDERDETAIL.OrderID%TYPE,
        p_courseID IN ORDERDETAIL.CourseID%TYPE,
        p_price IN ORDERDETAIL.Price%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        IF p_price < 0 THEN
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error: Price cannot be negative for OrderID ' || p_orderID || ' and CourseID ' || p_courseID);
            RETURN;
        END IF;

        UPDATE ORDERDETAIL
        SET
            Price = p_price
        WHERE OrderID = p_orderID AND CourseID = p_courseID;

        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No order detail updated for OrderID ' || p_orderID || ' and CourseID ' || p_courseID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_ORDER_DETAIL: ' || SQLERRM);
    END UPDATE_ORDER_DETAIL;

    PROCEDURE DELETE_ORDER_DETAIL (
        p_orderID IN ORDERDETAIL.OrderID%TYPE,
        p_courseID IN ORDERDETAIL.CourseID%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM ORDERDETAIL
        WHERE OrderID = p_orderID AND CourseID = p_courseID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No order detail deleted for OrderID ' || p_orderID || ' and CourseID ' || p_courseID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_ORDER_DETAIL: ' || SQLERRM);
    END DELETE_ORDER_DETAIL;

    PROCEDURE GET_ORDER_DETAIL (
        p_orderID IN ORDERDETAIL.OrderID%TYPE,
        p_courseID IN ORDERDETAIL.CourseID%TYPE,
        p_found_orderID OUT ORDERDETAIL.OrderID%TYPE,
        p_found_courseID OUT ORDERDETAIL.CourseID%TYPE,
        p_price OUT ORDERDETAIL.Price%TYPE,
        p_created_at OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT OrderID, CourseID, Price,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_orderID, p_found_courseID, p_price, p_created_at
        FROM ORDERDETAIL
        WHERE OrderID = p_orderID AND CourseID = p_courseID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_orderID := NULL;
            p_found_courseID := NULL;
            p_price := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_orderID := NULL;
            p_found_courseID := NULL;
            p_price := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_ORDER_DETAIL: ' || SQLERRM);
    END GET_ORDER_DETAIL;

    PROCEDURE GET_DETAILS_BY_ORDER_ID (
        p_orderID IN ORDERDETAIL.OrderID%TYPE,
        p_cursor OUT t_order_detail_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT OrderID, CourseID, Price,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM ORDERDETAIL
            WHERE OrderID = p_orderID
            ORDER BY CourseID ASC;
    END GET_DETAILS_BY_ORDER_ID;

END ORDER_DETAIL_PKG;