CREATE OR REPLACE PACKAGE CART_ITEM_PKG AS

    TYPE t_cart_item_cursor IS REF CURSOR;

    PROCEDURE CREATE_CART_ITEM (
        p_cartItemID IN CARTITEM.CartItemID%TYPE,
        p_cartID     IN CARTITEM.CartID%TYPE,
        p_courseID   IN CARTITEM.CourseID%TYPE,
        p_quantity   IN CARTITEM.Quantity%TYPE,
        p_success    OUT BOOLEAN
    );

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

END CART_ITEM_PKG;
/

CREATE OR REPLACE PACKAGE BODY CART_ITEM_PKG AS

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

END CART_ITEM_PKG;