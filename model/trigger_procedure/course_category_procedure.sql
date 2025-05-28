CREATE OR REPLACE PACKAGE COURSE_CATEGORY_PKG AS

    PROCEDURE LINK_COURSE_CATEGORY_PROC(
        p_CourseID   IN COURSECATEGORY.CourseID%TYPE,
        p_CategoryID IN COURSECATEGORY.CategoryID%TYPE
    );

    PROCEDURE UNLINK_COURSE_CATEGORY_PROC(
        p_CourseID   IN COURSECATEGORY.CourseID%TYPE,
        p_CategoryID IN COURSECATEGORY.CategoryID%TYPE
    );

    FUNCTION GET_CATEGORIES_BY_COURSE_FUNC(
        p_CourseID IN COURSECATEGORY.CourseID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_COURSES_BY_CATEGORY_FUNC(
        p_CategoryID IN COURSECATEGORY.CategoryID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION LINK_EXISTS_FUNC(
        p_CourseID   IN COURSECATEGORY.CourseID%TYPE,
        p_CategoryID IN COURSECATEGORY.CategoryID%TYPE
    ) RETURN NUMBER;

END COURSE_CATEGORY_PKG;
/

CREATE OR REPLACE PACKAGE BODY COURSE_CATEGORY_PKG AS

    PROCEDURE LINK_COURSE_CATEGORY_PROC(
        p_CourseID   IN COURSECATEGORY.CourseID%TYPE,
        p_CategoryID IN COURSECATEGORY.CategoryID%TYPE
    ) IS
    BEGIN
        INSERT INTO COURSECATEGORY (CourseID, CategoryID)
        VALUES (p_CourseID, p_CategoryID);
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            RAISE_APPLICATION_ERROR(-20040, 'Link between CourseID ''' || p_CourseID || ''' and CategoryID ' || p_CategoryID || ' already exists.');
        WHEN OTHERS THEN
            RAISE;
    END LINK_COURSE_CATEGORY_PROC;

    PROCEDURE UNLINK_COURSE_CATEGORY_PROC(
        p_CourseID   IN COURSECATEGORY.CourseID%TYPE,
        p_CategoryID IN COURSECATEGORY.CategoryID%TYPE
    ) IS
    BEGIN
        DELETE FROM COURSECATEGORY
        WHERE CourseID = p_CourseID AND CategoryID = p_CategoryID;
        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20041, 'Link between CourseID ''' || p_CourseID || ''' and CategoryID ' || p_CategoryID || ' not found, or no rows deleted.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20041 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UNLINK_COURSE_CATEGORY_PROC: ' || SQLERRM);
            END IF;
    END UNLINK_COURSE_CATEGORY_PROC;

    FUNCTION GET_CATEGORIES_BY_COURSE_FUNC(
        p_CourseID IN COURSECATEGORY.CourseID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                cc.CourseID,
                cc.CategoryID,
                TO_CHAR(cc.created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                COURSECATEGORY cc
            WHERE
                cc.CourseID = p_CourseID
            ORDER BY cc.CategoryID ASC;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_CATEGORIES_BY_COURSE_FUNC;

    FUNCTION GET_COURSES_BY_CATEGORY_FUNC(
        p_CategoryID IN COURSECATEGORY.CategoryID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                cc.CourseID,
                cc.CategoryID,
                TO_CHAR(cc.created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                COURSECATEGORY cc
            WHERE
                cc.CategoryID = p_CategoryID
            ORDER BY cc.CourseID ASC;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_COURSES_BY_CATEGORY_FUNC;

    FUNCTION LINK_EXISTS_FUNC(
        p_CourseID   IN COURSECATEGORY.CourseID%TYPE,
        p_CategoryID IN COURSECATEGORY.CategoryID%TYPE
    ) RETURN NUMBER IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM COURSECATEGORY
        WHERE CourseID = p_CourseID AND CategoryID = p_CategoryID;
        IF v_count > 0 THEN
            RETURN 1;
        ELSE
            RETURN 0;
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END LINK_EXISTS_FUNC;

END COURSE_CATEGORY_PKG;
/
