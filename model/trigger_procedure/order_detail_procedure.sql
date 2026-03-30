-- SQL for Oracle Database 19c
-- Package for ORDER_DETAIL Business Logic Layer

CREATE OR REPLACE PACKAGE ORDER_DETAIL_PKG AS

    PROCEDURE ADD_DETAIL_PROC(
        p_OrderID  IN ORDERDETAIL.OrderID%TYPE,
        p_CourseID IN ORDERDETAIL.CourseID%TYPE,
        p_Price    IN ORDERDETAIL.Price%TYPE
    );

    FUNCTION GET_DETAILS_BY_ORDER_FUNC(
        p_OrderID IN ORDERDETAIL.OrderID%TYPE
    ) RETURN SYS_REFCURSOR;

    PROCEDURE UPDATE_DETAIL_PROC(
        p_OrderID  IN ORDERDETAIL.OrderID%TYPE,
        p_CourseID IN ORDERDETAIL.CourseID%TYPE,
        p_Price    IN ORDERDETAIL.Price%TYPE
    );

    FUNCTION GET_DETAIL_FUNC(
        p_OrderID  IN ORDERDETAIL.OrderID%TYPE,
        p_CourseID IN ORDERDETAIL.CourseID%TYPE
    ) RETURN SYS_REFCURSOR;

    PROCEDURE DELETE_DETAIL_PROC(
        p_OrderID  IN ORDERDETAIL.OrderID%TYPE,
        p_CourseID IN ORDERDETAIL.CourseID%TYPE
    );

END ORDER_DETAIL_PKG;
/

CREATE OR REPLACE PACKAGE BODY ORDER_DETAIL_PKG AS

    PROCEDURE ADD_DETAIL_PROC(
        p_OrderID  IN ORDERDETAIL.OrderID%TYPE,
        p_CourseID IN ORDERDETAIL.CourseID%TYPE,
        p_Price    IN ORDERDETAIL.Price%TYPE
    ) IS
    BEGIN
        IF p_Price < 0 THEN
            RAISE_APPLICATION_ERROR(-20123, 'Price cannot be negative.');
        END IF;

        INSERT INTO ORDERDETAIL (OrderID, CourseID, Price)
        VALUES (p_OrderID, p_CourseID, p_Price);

    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            RAISE_APPLICATION_ERROR(-20120, 'OrderDetail for OrderID ''' || p_OrderID || ''' and CourseID ''' || p_CourseID || ''' already exists.');
        WHEN OTHERS THEN
            RAISE;
    END ADD_DETAIL_PROC;

    FUNCTION GET_DETAILS_BY_ORDER_FUNC(
        p_OrderID IN ORDERDETAIL.OrderID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                OrderID,
                CourseID,
                Price,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                ORDERDETAIL
            WHERE
                OrderID = p_OrderID
            ORDER BY CourseID ASC;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_DETAILS_BY_ORDER_FUNC;

    PROCEDURE UPDATE_DETAIL_PROC(
        p_OrderID  IN ORDERDETAIL.OrderID%TYPE,
        p_CourseID IN ORDERDETAIL.CourseID%TYPE,
        p_Price    IN ORDERDETAIL.Price%TYPE
    ) IS
    BEGIN
        IF p_Price < 0 THEN
            RAISE_APPLICATION_ERROR(-20123, 'Price cannot be negative for update.');
        END IF;

        UPDATE ORDERDETAIL
        SET Price = p_Price
        WHERE OrderID = p_OrderID AND CourseID = p_CourseID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20121, 'OrderDetail for OrderID ''' || p_OrderID || ''' and CourseID ''' || p_CourseID || ''' not found for update.');
        END IF;
        -- PHP BLL checks ($stid !== false). SQL%ROWCOUNT check here ensures row was found.
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20121 OR SQLCODE = -20123 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UPDATE_DETAIL_PROC: ' || SQLERRM);
            END IF;
    END UPDATE_DETAIL_PROC;

    FUNCTION GET_DETAIL_FUNC(
        p_OrderID  IN ORDERDETAIL.OrderID%TYPE,
        p_CourseID IN ORDERDETAIL.CourseID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                OrderID,
                CourseID,
                Price,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                ORDERDETAIL
            WHERE
                OrderID = p_OrderID AND CourseID = p_CourseID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_DETAIL_FUNC;

    PROCEDURE DELETE_DETAIL_PROC(
        p_OrderID  IN ORDERDETAIL.OrderID%TYPE,
        p_CourseID IN ORDERDETAIL.CourseID%TYPE
    ) IS
    BEGIN
        DELETE FROM ORDERDETAIL
        WHERE OrderID = p_OrderID AND CourseID = p_CourseID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20122, 'OrderDetail for OrderID ''' || p_OrderID || ''' and CourseID ''' || p_CourseID || ''' not found for deletion.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20122 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in DELETE_DETAIL_PROC: ' || SQLERRM);
            END IF;
    END DELETE_DETAIL_PROC;

END ORDER_DETAIL_PKG;
/

