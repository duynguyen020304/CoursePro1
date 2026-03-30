CREATE OR REPLACE PACKAGE PAYMENT_PKG AS

    PROCEDURE CREATE_PAYMENT_PROC(
        p_PaymentID     IN PAYMENT.PaymentID%TYPE,
        p_OrderID       IN PAYMENT.OrderID%TYPE,
        p_PaymentDate   IN PAYMENT.PaymentDate%TYPE,
        p_PaymentMethod IN PAYMENT.PaymentMethod%TYPE,
        p_PaymentStatus IN PAYMENT.PaymentStatus%TYPE,
        p_Amount        IN PAYMENT.Amount%TYPE
    );

    FUNCTION GET_PAYMENT_BY_ORDER_ID_FUNC(
        p_OrderID IN PAYMENT.OrderID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_PAYMENT_BY_ID_FUNC(
        p_PaymentID IN PAYMENT.PaymentID%TYPE
    ) RETURN SYS_REFCURSOR;

    PROCEDURE UPDATE_PAYMENT_PROC(
        p_PaymentID     IN PAYMENT.PaymentID%TYPE,
        p_OrderID       IN PAYMENT.OrderID%TYPE,
        p_PaymentDate   IN PAYMENT.PaymentDate%TYPE,
        p_PaymentMethod IN PAYMENT.PaymentMethod%TYPE,
        p_PaymentStatus IN PAYMENT.PaymentStatus%TYPE,
        p_Amount        IN PAYMENT.Amount%TYPE
    );

    PROCEDURE DELETE_PAYMENT_PROC(
        p_PaymentID IN PAYMENT.PaymentID%TYPE
    );

END PAYMENT_PKG;
/

CREATE OR REPLACE PACKAGE BODY PAYMENT_PKG AS

    PROCEDURE CREATE_PAYMENT_PROC(
        p_PaymentID     IN PAYMENT.PaymentID%TYPE,
        p_OrderID       IN PAYMENT.OrderID%TYPE,
        p_PaymentDate   IN PAYMENT.PaymentDate%TYPE,
        p_PaymentMethod IN PAYMENT.PaymentMethod%TYPE,
        p_PaymentStatus IN PAYMENT.PaymentStatus%TYPE,
        p_Amount        IN PAYMENT.Amount%TYPE
    ) IS
    BEGIN
        IF p_Amount < 0 THEN
            RAISE_APPLICATION_ERROR(-20133, 'Payment amount cannot be negative.');
        END IF;

        INSERT INTO PAYMENT (PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount)
        VALUES (p_PaymentID, p_OrderID, p_PaymentDate, p_PaymentMethod, p_PaymentStatus, p_Amount);
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            RAISE_APPLICATION_ERROR(-20130, 'Payment with PaymentID ''' || p_PaymentID || ''' already exists.');
        WHEN OTHERS THEN
            RAISE;
    END CREATE_PAYMENT_PROC;

    FUNCTION GET_PAYMENT_BY_ORDER_ID_FUNC(
        p_OrderID IN PAYMENT.OrderID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                PaymentID,
                OrderID,
                PaymentMethod,
                PaymentStatus,
                Amount,
                TO_CHAR(PaymentDate, 'YYYY-MM-DD HH24:MI:SS.FF6') AS payment_date_formatted,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                PAYMENT
            WHERE
                OrderID = p_OrderID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_PAYMENT_BY_ORDER_ID_FUNC;

    FUNCTION GET_PAYMENT_BY_ID_FUNC(
        p_PaymentID IN PAYMENT.PaymentID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                PaymentID,
                OrderID,
                PaymentMethod,
                PaymentStatus,
                Amount,
                TO_CHAR(PaymentDate, 'YYYY-MM-DD HH24:MI:SS.FF6') AS payment_date_formatted,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                PAYMENT
            WHERE
                PaymentID = p_PaymentID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_PAYMENT_BY_ID_FUNC;

    PROCEDURE UPDATE_PAYMENT_PROC(
        p_PaymentID     IN PAYMENT.PaymentID%TYPE,
        p_OrderID       IN PAYMENT.OrderID%TYPE,
        p_PaymentDate   IN PAYMENT.PaymentDate%TYPE,
        p_PaymentMethod IN PAYMENT.PaymentMethod%TYPE,
        p_PaymentStatus IN PAYMENT.PaymentStatus%TYPE,
        p_Amount        IN PAYMENT.Amount%TYPE
    ) IS
    BEGIN
        IF p_Amount < 0 THEN
            RAISE_APPLICATION_ERROR(-20133, 'Payment amount cannot be negative for update.');
        END IF;

        UPDATE PAYMENT
        SET OrderID       = p_OrderID,
            PaymentDate   = p_PaymentDate,
            PaymentMethod = p_PaymentMethod,
            PaymentStatus = p_PaymentStatus,
            Amount        = p_Amount
        WHERE PaymentID = p_PaymentID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20131, 'Payment with PaymentID ''' || p_PaymentID || ''' not found for update.');
        END IF;
    END UPDATE_PAYMENT_PROC;

    PROCEDURE DELETE_PAYMENT_PROC(
        p_PaymentID IN PAYMENT.PaymentID%TYPE
    ) IS
    BEGIN
        DELETE FROM PAYMENT
        WHERE PaymentID = p_PaymentID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20132, 'Payment with PaymentID ''' || p_PaymentID || ''' not found for deletion.');
        END IF;
    END DELETE_PAYMENT_PROC;

END PAYMENT_PKG;
/
