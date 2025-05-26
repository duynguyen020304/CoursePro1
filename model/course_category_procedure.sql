
CREATE OR REPLACE PACKAGE COURSE_CATEGORY_PKG AS

    PROCEDURE LINK_COURSE_CATEGORY (
        p_courseID   IN COURSECATEGORY.CourseID%TYPE,
        p_categoryID IN COURSECATEGORY.CategoryID%TYPE,
        p_success    OUT BOOLEAN
    );

    PROCEDURE UNLINK_COURSE_CATEGORY (
        p_courseID   IN COURSECATEGORY.CourseID%TYPE,
        p_categoryID IN COURSECATEGORY.CategoryID%TYPE,
        p_success    OUT BOOLEAN
    );

    TYPE t_coursecategory_cursor IS REF CURSOR;

    PROCEDURE GET_CATEGORIES_BY_COURSE (
        p_courseID IN COURSECATEGORY.CourseID%TYPE,
        p_cursor   OUT t_coursecategory_cursor
    );

    PROCEDURE GET_COURSES_BY_CATEGORY (
        p_categoryID IN COURSECATEGORY.CategoryID%TYPE,
        p_cursor     OUT t_coursecategory_cursor
    );

    PROCEDURE LINK_EXISTS (
        p_courseID   IN COURSECATEGORY.CourseID%TYPE,
        p_categoryID IN COURSECATEGORY.CategoryID%TYPE,
        p_exists     OUT BOOLEAN
    );

END COURSE_CATEGORY_PKG;
/

CREATE OR REPLACE PACKAGE BODY COURSE_CATEGORY_PKG AS

    PROCEDURE LINK_COURSE_CATEGORY (
        p_courseID   IN COURSECATEGORY.CourseID%TYPE,
        p_categoryID IN COURSECATEGORY.CategoryID%TYPE,
        p_success    OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM COURSECATEGORY
        WHERE CourseID = p_courseID AND CategoryID = p_categoryID;

        IF v_count = 0 THEN
            INSERT INTO COURSECATEGORY (CourseID, CategoryID)
            VALUES (p_courseID, p_categoryID);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: Link already exists for CourseID ' || p_courseID || ' and CategoryID ' || p_categoryID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in LINK_COURSE_CATEGORY: ' || SQLERRM);
    END LINK_COURSE_CATEGORY;

    PROCEDURE UNLINK_COURSE_CATEGORY (
        p_courseID   IN COURSECATEGORY.CourseID%TYPE,
        p_categoryID IN COURSECATEGORY.CategoryID%TYPE,
        p_success    OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM COURSECATEGORY
        WHERE CourseID = p_courseID AND CategoryID = p_categoryID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No link deleted for CourseID ' || p_courseID || ' and CategoryID ' || p_categoryID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UNLINK_COURSE_CATEGORY: ' || SQLERRM);
    END UNLINK_COURSE_CATEGORY;

    PROCEDURE GET_CATEGORIES_BY_COURSE (
        p_courseID IN COURSECATEGORY.CourseID%TYPE,
        p_cursor   OUT t_coursecategory_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT CourseID, CategoryID, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM COURSECATEGORY
            WHERE CourseID = p_courseID
            ORDER BY CategoryID ASC;
    EXCEPTION
        WHEN OTHERS THEN
            IF p_cursor%ISOPEN THEN
                CLOSE p_cursor;
            END IF;
            DBMS_OUTPUT.PUT_LINE('Error in GET_CATEGORIES_BY_COURSE: ' || SQLERRM);
    END GET_CATEGORIES_BY_COURSE;

    PROCEDURE GET_COURSES_BY_CATEGORY (
        p_categoryID IN COURSECATEGORY.CategoryID%TYPE,
        p_cursor     OUT t_coursecategory_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT CourseID, CategoryID, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM COURSECATEGORY
            WHERE CategoryID = p_categoryID
            ORDER BY CourseID ASC;
    EXCEPTION
        WHEN OTHERS THEN
            IF p_cursor%ISOPEN THEN
                CLOSE p_cursor;
            END IF;
            DBMS_OUTPUT.PUT_LINE('Error in GET_COURSES_BY_CATEGORY: ' || SQLERRM);
    END GET_COURSES_BY_CATEGORY;

    PROCEDURE LINK_EXISTS (
        p_courseID   IN COURSECATEGORY.CourseID%TYPE,
        p_categoryID IN COURSECATEGORY.CategoryID%TYPE,
        p_exists     OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM COURSECATEGORY
        WHERE CourseID = p_courseID AND CategoryID = p_categoryID;

        IF v_count > 0 THEN
            p_exists := TRUE;
        ELSE
            p_exists := FALSE;
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            p_exists := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in LINK_EXISTS: ' || SQLERRM);
    END LINK_EXISTS;

END COURSE_CATEGORY_PKG;
/