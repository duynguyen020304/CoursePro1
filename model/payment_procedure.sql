CREATE OR REPLACE PACKAGE PAYMENT_PKG AS

    PROCEDURE CREATE_NEW_PAYMENT (
        p_paymentID IN PAYMENT.PaymentID%TYPE,
        p_orderID IN PAYMENT.OrderID%TYPE,
        p_paymentDate IN VARCHAR2,
        p_paymentMethod IN PAYMENT.PaymentMethod%TYPE,
        p_paymentStatus IN PAYMENT.PaymentStatus%TYPE,
        p_amount IN PAYMENT.Amount%TYPE,
        p_success OUT BOOLEAN
    );

    PROCEDURE UPDATE_PAYMENT (
        p_paymentID IN PAYMENT.PaymentID%TYPE,
        p_orderID IN PAYMENT.OrderID%TYPE,
        p_paymentDate IN VARCHAR2,
        p_paymentMethod IN PAYMENT.PaymentMethod%TYPE,
        p_paymentStatus IN PAYMENT.PaymentStatus%TYPE,
        p_amount IN PAYMENT.Amount%TYPE,
        p_success OUT BOOLEAN
    );

    PROCEDURE DELETE_PAYMENT (
        p_paymentID IN PAYMENT.PaymentID%TYPE,
        p_success OUT BOOLEAN
    );

    PROCEDURE GET_PAYMENT_BY_ORDER_ID (
        p_orderID IN PAYMENT.OrderID%TYPE,
        p_found_paymentID OUT PAYMENT.PaymentID%TYPE,
        p_found_orderID OUT PAYMENT.OrderID%TYPE,
        p_paymentDate OUT VARCHAR2,
        p_paymentMethod OUT PAYMENT.PaymentMethod%TYPE,
        p_paymentStatus OUT PAYMENT.PaymentStatus%TYPE,
        p_amount OUT PAYMENT.Amount%TYPE,
        p_created_at OUT VARCHAR2
    );

    PROCEDURE GET_PAYMENT_BY_ID (
        p_paymentID IN PAYMENT.PaymentID%TYPE,
        p_found_paymentID OUT PAYMENT.PaymentID%TYPE,
        p_orderID OUT PAYMENT.OrderID%TYPE,
        p_paymentDate OUT VARCHAR2,
        p_paymentMethod OUT PAYMENT.PaymentMethod%TYPE,
        p_paymentStatus OUT PAYMENT.PaymentStatus%TYPE,
        p_amount OUT PAYMENT.Amount%TYPE,
        p_created_at OUT VARCHAR2
    );

END PAYMENT_PKG;
/

CREATE OR REPLACE PACKAGE BODY PAYMENT_PKG AS

    PROCEDURE CREATE_NEW_PAYMENT (
        p_paymentID IN PAYMENT.PaymentID%TYPE,
        p_orderID IN PAYMENT.OrderID%TYPE,
        p_paymentDate IN VARCHAR2,
        p_paymentMethod IN PAYMENT.PaymentMethod%TYPE,
        p_paymentStatus IN PAYMENT.PaymentStatus%TYPE,
        p_amount IN PAYMENT.Amount%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM PAYMENT WHERE PaymentID = p_paymentID;

        IF v_count = 0 THEN
            INSERT INTO PAYMENT (PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount)
            VALUES (p_paymentID, p_orderID, TO_TIMESTAMP(p_paymentDate, 'YYYY-MM-DD HH24:MI:SS.FF6'), p_paymentMethod, p_paymentStatus, p_amount);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: PaymentID ' || p_paymentID || ' already exists.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_NEW_PAYMENT: ' || SQLERRM);
    END CREATE_NEW_PAYMENT;

    PROCEDURE UPDATE_PAYMENT (
        p_paymentID IN PAYMENT.PaymentID%TYPE,
        p_orderID IN PAYMENT.OrderID%TYPE,
        p_paymentDate IN VARCHAR2,
        p_paymentMethod IN PAYMENT.PaymentMethod%TYPE,
        p_paymentStatus IN PAYMENT.PaymentStatus%TYPE,
        p_amount IN PAYMENT.Amount%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        UPDATE PAYMENT
        SET
            OrderID = p_orderID,
            PaymentDate = TO_TIMESTAMP(p_paymentDate, 'YYYY-MM-DD HH24:MI:SS.FF6'),
            PaymentMethod = p_paymentMethod,
            PaymentStatus = p_paymentStatus,
            Amount = p_amount
        WHERE PaymentID = p_paymentID;

        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No payment updated for PaymentID ' || p_paymentID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_PAYMENT: ' || SQLERRM);
    END UPDATE_PAYMENT;

    PROCEDURE DELETE_PAYMENT (
        p_paymentID IN PAYMENT.PaymentID%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM PAYMENT
        WHERE PaymentID = p_paymentID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No payment deleted for PaymentID ' || p_paymentID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_PAYMENT: ' || SQLERRM);
    END DELETE_PAYMENT;

    PROCEDURE GET_PAYMENT_BY_ORDER_ID (
        p_orderID IN PAYMENT.OrderID%TYPE,
        p_found_paymentID OUT PAYMENT.PaymentID%TYPE,
        p_found_orderID OUT PAYMENT.OrderID%TYPE,
        p_paymentDate OUT VARCHAR2,
        p_paymentMethod OUT PAYMENT.PaymentMethod%TYPE,
        p_paymentStatus OUT PAYMENT.PaymentStatus%TYPE,
        p_amount OUT PAYMENT.Amount%TYPE,
        p_created_at OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT PaymentID, OrderID,
               TO_CHAR(PaymentDate, 'YYYY-MM-DD HH24:MI:SS.FF6'),
               PaymentMethod, PaymentStatus, Amount,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_paymentID, p_found_orderID, p_paymentDate, p_paymentMethod, p_paymentStatus, p_amount, p_created_at
        FROM PAYMENT
        WHERE OrderID = p_orderID;
    END GET_PAYMENT_BY_ORDER_ID;

    PROCEDURE GET_PAYMENT_BY_ID (
        p_paymentID IN PAYMENT.PaymentID%TYPE,
        p_found_paymentID OUT PAYMENT.PaymentID%TYPE,
        p_orderID OUT PAYMENT.OrderID%TYPE,
        p_paymentDate OUT VARCHAR2,
        p_paymentMethod OUT PAYMENT.PaymentMethod%TYPE,
        p_paymentStatus OUT PAYMENT.PaymentStatus%TYPE,
        p_amount OUT PAYMENT.Amount%TYPE,
        p_created_at OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT PaymentID, OrderID,
               TO_CHAR(PaymentDate, 'YYYY-MM-DD HH24:MI:SS.FF6'),
               PaymentMethod, PaymentStatus, Amount,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_paymentID, p_orderID, p_paymentDate, p_paymentMethod, p_paymentStatus, p_amount, p_created_at
        FROM PAYMENT
        WHERE PaymentID = p_paymentID;
    END GET_PAYMENT_BY_ID;

END PAYMENT_PKG;