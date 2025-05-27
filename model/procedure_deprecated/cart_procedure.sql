CREATE OR REPLACE PACKAGE CART_PKG AS

    TYPE t_cart_item_cursor IS REF CURSOR;

    PROCEDURE CREATE_NEW_CART (
        p_cartID IN CART.CartID%TYPE,
        p_userID IN CART.UserID%TYPE,
        p_success OUT BOOLEAN
    );

    PROCEDURE GET_CART_BY_USER (
        p_userID IN CART.UserID%TYPE,
        p_cartID OUT CART.CartID%TYPE,
        p_found_userID OUT CART.UserID%TYPE,
        p_created_at OUT VARCHAR2
    );

    PROCEDURE GET_CART_BY_ID (
        p_cartID IN CART.CartID%TYPE,
        p_found_cartID OUT CART.CartID%TYPE,
        p_userID OUT CART.UserID%TYPE,
        p_created_at OUT VARCHAR2
    );

    PROCEDURE DELETE_EXISTING_CART (
        p_cartID IN CART.CartID%TYPE,
        p_success OUT BOOLEAN
    );

END CART_PKG;
/

CREATE OR REPLACE PACKAGE BODY CART_PKG AS

    PROCEDURE CREATE_NEW_CART (
        p_cartID IN CART.CartID%TYPE,
        p_userID IN CART.UserID%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM CART WHERE CartID = p_cartID;
        IF v_count = 0 THEN
            INSERT INTO CART (CartID, UserID)
            VALUES (p_cartID, p_userID);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error: CartID ' || p_cartID || ' already exists.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_NEW_CART: ' || SQLERRM);
    END CREATE_NEW_CART;

    PROCEDURE GET_CART_BY_USER (
        p_userID IN CART.UserID%TYPE,
        p_cartID OUT CART.CartID%TYPE,
        p_found_userID OUT CART.UserID%TYPE,
        p_created_at OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT CartID, UserID, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_cartID, p_found_userID, p_created_at
        FROM CART
        WHERE UserID = p_userID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_cartID := NULL;
            p_found_userID := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_cartID := NULL;
            p_found_userID := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_CART_BY_USER: ' || SQLERRM);
    END GET_CART_BY_USER;

    PROCEDURE GET_CART_BY_ID (
        p_cartID IN CART.CartID%TYPE,
        p_found_cartID OUT CART.CartID%TYPE,
        p_userID OUT CART.UserID%TYPE,
        p_created_at OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT CartID, UserID, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_cartID, p_userID, p_created_at
        FROM CART
        WHERE CartID = p_cartID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_cartID := NULL;
            p_userID := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_cartID := NULL;
            p_userID := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_CART_BY_ID: ' || SQLERRM);
    END GET_CART_BY_ID;

    PROCEDURE DELETE_EXISTING_CART (
        p_cartID IN CART.CartID%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM CART
        WHERE CartID = p_cartID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No cart deleted or multiple deleted for CartID ' || p_cartID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_EXISTING_CART: ' || SQLERRM);
    END DELETE_EXISTING_CART;

END CART_PKG;