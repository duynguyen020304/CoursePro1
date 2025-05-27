CREATE OR REPLACE PACKAGE CART_PKG AS

    PROCEDURE CREATE_NEW_CART (
        p_cartID    IN CART.CartID%TYPE,
        p_userID    IN CART.UserID%TYPE,
        p_success   OUT BOOLEAN
    );

    PROCEDURE GET_CART_BY_USER (
        p_userID        IN CART.UserID%TYPE,
        p_cartID        OUT CART.CartID%TYPE,
        p_found_userID  OUT CART.UserID%TYPE,
        p_created_at    OUT VARCHAR2
    );

    PROCEDURE GET_CART_BY_ID (
        p_cartID        IN CART.CartID%TYPE,
        p_found_cartID  OUT CART.CartID%TYPE,
        p_userID        OUT CART.UserID%TYPE,
        p_created_at    OUT VARCHAR2
    );

    PROCEDURE DELETE_EXISTING_CART (
        p_cartID    IN CART.CartID%TYPE,
        p_success   OUT BOOLEAN
    );

    PROCEDURE CREATE_CART_ITEM (
        p_cartItemID IN CARTITEM.CartItemID%TYPE,
        p_cartID     IN CARTITEM.CartID%TYPE,
        p_courseID   IN CARTITEM.CourseID%TYPE,
        p_quantity   IN CARTITEM.Quantity%TYPE,
        p_success    OUT BOOLEAN
    );

    TYPE t_cart_item_cursor IS REF CURSOR;
    PROCEDURE GET_CART_ITEMS_BY_CART (
        p_cartID    IN CARTITEM.CartID%TYPE,
        p_cursor    OUT t_cart_item_cursor
    );

    PROCEDURE DELETE_CART_ITEM (
        p_cartItemID IN CARTITEM.CartItemID%TYPE,
        p_success    OUT BOOLEAN
    );

    PROCEDURE GET_CART_ITEM_BY_ID (
        p_cartItemID        IN CARTITEM.CartItemID%TYPE,
        p_found_cartItemID  OUT CARTITEM.CartItemID%TYPE,
        p_cartID            OUT CARTITEM.CartID%TYPE,
        p_courseID          OUT CARTITEM.CourseID%TYPE,
        p_quantity          OUT CARTITEM.Quantity%TYPE,
        p_created_at        OUT VARCHAR2
    );

    PROCEDURE UPDATE_ITEM_QUANTITY (
        p_cartItemID IN CARTITEM.CartItemID%TYPE,
        p_quantity   IN CARTITEM.Quantity%TYPE,
        p_success    OUT BOOLEAN
    );

    PROCEDURE CLEAR_CART_ITEMS (
        p_cartID    IN CARTITEM.CartID%TYPE,
        p_success   OUT BOOLEAN
    );

END CART_PKG;
/

CREATE OR REPLACE PACKAGE BODY CART_PKG AS

    PROCEDURE CREATE_NEW_CART (
        p_cartID    IN CART.CartID%TYPE,
        p_userID    IN CART.UserID%TYPE,
        p_success   OUT BOOLEAN
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
        p_userID        IN CART.UserID%TYPE,
        p_cartID        OUT CART.CartID%TYPE,
        p_found_userID  OUT CART.UserID%TYPE,
        p_created_at    OUT VARCHAR2
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
        p_cartID        IN CART.CartID%TYPE,
        p_found_cartID  OUT CART.CartID%TYPE,
        p_userID        OUT CART.UserID%TYPE,
        p_created_at    OUT VARCHAR2
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
        p_cartID    IN CART.CartID%TYPE,
        p_success   OUT BOOLEAN
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
            DBMS_OUTPUT.PUT_LINE('Warning: No cart deleted or more than one cart deleted for CartID ' || p_cartID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_EXISTING_CART: ' || SQLERRM);
    END DELETE_EXISTING_CART;

    PROCEDURE CREATE_CART_ITEM (
        p_cartItemID IN CARTITEM.CartItemID%TYPE,
        p_cartID     IN CARTITEM.CartID%TYPE,
        p_courseID   IN CARTITEM.CourseID%TYPE,
        p_quantity   IN CARTITEM.Quantity%TYPE,
        p_success    OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM CARTITEM WHERE CartItemID = p_cartItemID;

        IF v_count = 0 THEN
            INSERT INTO CARTITEM (CartItemID, CartID, CourseID, Quantity)
            VALUES (p_cartItemID, p_cartID, p_courseID, p_quantity);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error: CartItemID ' || p_cartItemID || ' already exists.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_CART_ITEM: ' || SQLERRM);
    END CREATE_CART_ITEM;

    PROCEDURE GET_CART_ITEMS_BY_CART (
        p_cartID    IN CARTITEM.CartID%TYPE,
        p_cursor    OUT t_cart_item_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT CartItemID, CartID, CourseID, Quantity,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM CARTITEM
            WHERE CartID = p_cartID
            ORDER BY CourseID ASC;
    EXCEPTION
        WHEN OTHERS THEN
            IF p_cursor%ISOPEN THEN
                CLOSE p_cursor;
            END IF;
            DBMS_OUTPUT.PUT_LINE('Error in GET_CART_ITEMS_BY_CART: ' || SQLERRM);
    END GET_CART_ITEMS_BY_CART;

    PROCEDURE DELETE_CART_ITEM (
        p_cartItemID IN CARTITEM.CartItemID%TYPE,
        p_success    OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM CARTITEM
        WHERE CartItemID = p_cartItemID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No cart item deleted for CartItemID ' || p_cartItemID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_CART_ITEM: ' || SQLERRM);
    END DELETE_CART_ITEM;

    PROCEDURE GET_CART_ITEM_BY_ID (
        p_cartItemID        IN CARTITEM.CartItemID%TYPE,
        p_found_cartItemID  OUT CARTITEM.CartItemID%TYPE,
        p_cartID            OUT CARTITEM.CartID%TYPE,
        p_courseID          OUT CARTITEM.CourseID%TYPE,
        p_quantity          OUT CARTITEM.Quantity%TYPE,
        p_created_at        OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT CartItemID, CartID, CourseID, Quantity,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_cartItemID, p_cartID, p_courseID, p_quantity, p_created_at
        FROM CARTITEM
        WHERE CartItemID = p_cartItemID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_cartItemID := NULL;
            p_cartID := NULL;
            p_courseID := NULL;
            p_quantity := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_cartItemID := NULL;
            p_cartID := NULL;
            p_courseID := NULL;
            p_quantity := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_CART_ITEM_BY_ID: ' || SQLERRM);
    END GET_CART_ITEM_BY_ID;

    PROCEDURE UPDATE_ITEM_QUANTITY (
        p_cartItemID IN CARTITEM.CartItemID%TYPE,
        p_quantity   IN CARTITEM.Quantity%TYPE,
        p_success    OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        IF p_quantity <= 0 THEN
            DELETE_CART_ITEM(p_cartItemID, p_success);
        ELSE
            UPDATE CARTITEM
            SET Quantity = p_quantity
            WHERE CartItemID = p_cartItemID;

            v_rows_updated := SQL%ROWCOUNT;
            IF v_rows_updated = 1 THEN
                COMMIT;
                p_success := TRUE;
            ELSE
                ROLLBACK;
                p_success := FALSE;
                DBMS_OUTPUT.PUT_LINE('Warning: No cart item updated for CartItemID ' || p_cartItemID);
            END IF;
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_ITEM_QUANTITY: ' || SQLERRM);
    END UPDATE_ITEM_QUANTITY;

    PROCEDURE CLEAR_CART_ITEMS (
        p_cartID    IN CARTITEM.CartID%TYPE,
        p_success   OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM CARTITEM
        WHERE CartID = p_cartID;

        v_rows_deleted := SQL%ROWCOUNT;
        COMMIT;
        p_success := TRUE;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CLEAR_CART_ITEMS: ' || SQLERRM);
    END CLEAR_CART_ITEMS;

END CART_PKG;
/



DECLARE
    v_success BOOLEAN;
BEGIN
    CART_PKG.CREATE_NEW_CART('CART001', 'USER001', v_success);
    IF v_success THEN
        DBMS_OUTPUT.PUT_LINE('Cart created successfully.');
    ELSE
        DBMS_OUTPUT.PUT_LINE('Failed to create cart.');
    END IF;

    CART_PKG.CREATE_NEW_CART('CART002', 'USER002', v_success);
    IF v_success THEN
        DBMS_OUTPUT.PUT_LINE('Cart created successfully.');
    ELSE
        DBMS_OUTPUT.PUT_LINE('Failed to create cart.');
    END IF;
END;
/

DECLARE
    v_cartID        CART.CartID%TYPE;
    v_userID        CART.UserID%TYPE;
    v_created_at    VARCHAR2(50);
BEGIN
    CART_PKG.GET_CART_BY_USER('USER001', v_cartID, v_userID, v_created_at);
    IF v_cartID IS NOT NULL THEN
        DBMS_OUTPUT.PUT_LINE('Cart found for USER001:');
        DBMS_OUTPUT.PUT_LINE('  CartID: ' || v_cartID);
        DBMS_OUTPUT.PUT_LINE('  UserID: ' || v_userID);
        DBMS_OUTPUT.PUT_LINE('  Created At: ' || v_created_at);
    ELSE
        DBMS_OUTPUT.PUT_LINE('No cart found for USER001.');
    END IF;

    CART_PKG.GET_CART_BY_USER('NONEXISTENT_USER', v_cartID, v_userID, v_created_at);
    IF v_cartID IS NOT NULL THEN
        DBMS_OUTPUT.PUT_LINE('Cart found for NONEXISTENT_USER:');
    ELSE
        DBMS_OUTPUT.PUT_LINE('No cart found for NONEXISTENT_USER (expected).');
    END IF;
END;
/

DECLARE
    v_found_cartID  CART.CartID%TYPE;
    v_userID        CART.UserID%TYPE;
    v_created_at    VARCHAR2(50);
BEGIN
    CART_PKG.GET_CART_BY_ID('CART002', v_found_cartID, v_userID, v_created_at);
    IF v_found_cartID IS NOT NULL THEN
        DBMS_OUTPUT.PUT_LINE('Cart found for CART002:');
        DBMS_OUTPUT.PUT_LINE('  CartID: ' || v_found_cartID);
        DBMS_OUTPUT.PUT_LINE('  UserID: ' || v_userID);
        DBMS_OUTPUT.PUT_LINE('  Created At: ' || v_created_at);
    ELSE
        DBMS_OUTPUT.PUT_LINE('No cart found for CART002.');
    END IF;

    CART_PKG.GET_CART_BY_ID('NONEXISTENT_CART', v_found_cartID, v_userID, v_created_at);
    IF v_found_cartID IS NOT NULL THEN
        DBMS_OUTPUT.PUT_LINE('Cart found for NONEXISTENT_CART:');
    ELSE
        DBMS_OUTPUT.PUT_LINE('No cart found for NONEXISTENT_CART (expected).');
    END IF;
END;
/

DECLARE
    v_success BOOLEAN;
BEGIN
    CART_PKG.DELETE_EXISTING_CART('CART001', v_success);
    IF v_success THEN
        DBMS_OUTPUT.PUT_LINE('Cart CART001 deleted successfully.');
    ELSE
        DBMS_OUTPUT.PUT_LINE('Failed to delete cart CART001.');
    END IF;

    CART_PKG.DELETE_EXISTING_CART('NONEXISTENT_CART', v_success);
    IF v_success THEN
        DBMS_OUTPUT.PUT_LINE('Cart NONEXISTENT_CART deleted successfully.');
    ELSE
        DBMS_OUTPUT.PUT_LINE('Failed to delete cart NONEXISTENT_CART (expected).');
    END IF;
END;
/

DECLARE
    v_success BOOLEAN;
BEGIN
    CART_PKG.CREATE_CART_ITEM('ITEM001', 'CART001', 'COURSE_A', 2, v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Item ITEM001 added to CART001.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to add ITEM001.'); END IF;

    CART_PKG.CREATE_CART_ITEM('ITEM002', 'CART001', 'COURSE_B', 1, v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Item ITEM002 added to CART001.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to add ITEM002.'); END IF;

    CART_PKG.CREATE_CART_ITEM('ITEM003', 'CART002', 'COURSE_C', 3, v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Item ITEM003 added to CART002.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to add ITEM003.'); END IF;
END;
/

DECLARE
    v_cursor CART_PKG.t_cart_item_cursor;
    v_cartItemID CARTITEM.CartItemID%TYPE;
    v_cartID     CARTITEM.CartID%TYPE;
    v_courseID   CARTITEM.CourseID%TYPE;
    v_quantity   CARTITEM.Quantity%TYPE;
    v_created_at VARCHAR2(50);
BEGIN
    DBMS_OUTPUT.PUT_LINE('Items in CART001:');
    CART_PKG.GET_CART_ITEMS_BY_CART('CART001', v_cursor);
    LOOP
        FETCH v_cursor INTO v_cartItemID, v_cartID, v_courseID, v_quantity, v_created_at;
        EXIT WHEN v_cursor%NOTFOUND;
        DBMS_OUTPUT.PUT_LINE('  CartItemID: ' || v_cartItemID || ', CourseID: ' || v_courseID || ', Quantity: ' || v_quantity);
    END LOOP;
    CLOSE v_cursor;

    DBMS_OUTPUT.PUT_LINE('Items in CART002:');
    CART_PKG.GET_CART_ITEMS_BY_CART('CART002', v_cursor);
    LOOP
        FETCH v_cursor INTO v_cartItemID, v_cartID, v_courseID, v_quantity, v_created_at;
        EXIT WHEN v_cursor%NOTFOUND;
        DBMS_OUTPUT.PUT_LINE('  CartItemID: ' || v_cartItemID || ', CourseID: ' || v_courseID || ', Quantity: ' || v_quantity);
    END LOOP;
    CLOSE v_cursor;
END;
/

