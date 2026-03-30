CREATE OR REPLACE PACKAGE COURSE_IMAGE_PKG AS

    PROCEDURE CREATE_IMAGE_PROC(
        p_ImageID    IN COURSEIMAGE.ImageID%TYPE,
        p_CourseID   IN COURSEIMAGE.CourseID%TYPE,
        p_ImagePath  IN COURSEIMAGE.ImagePath%TYPE,
        p_Caption    IN COURSEIMAGE.Caption%TYPE,
        p_SortOrder  IN COURSEIMAGE.SortOrder%TYPE
    );

    PROCEDURE UPDATE_IMAGE_PROC(
        p_ImageID    IN COURSEIMAGE.ImageID%TYPE,
        p_CourseID   IN COURSEIMAGE.CourseID%TYPE,
        p_ImagePath  IN COURSEIMAGE.ImagePath%TYPE,
        p_Caption    IN COURSEIMAGE.Caption%TYPE,
        p_SortOrder  IN COURSEIMAGE.SortOrder%TYPE
    );

    PROCEDURE UNLINK_IMAGE_COURSE_PROC(
        p_ImageID  IN COURSEIMAGE.ImageID%TYPE,
        p_CourseID IN COURSEIMAGE.CourseID%TYPE
    );

    FUNCTION GET_IMAGE_BY_IMAGE_ID_FUNC(
        p_ImageID IN COURSEIMAGE.ImageID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_IMAGES_BY_COURSE_ID_FUNC(
        p_CourseID IN COURSEIMAGE.CourseID%TYPE
    ) RETURN SYS_REFCURSOR;

END COURSE_IMAGE_PKG;
/

CREATE OR REPLACE PACKAGE BODY COURSE_IMAGE_PKG AS

    PROCEDURE CREATE_IMAGE_PROC(
        p_ImageID    IN COURSEIMAGE.ImageID%TYPE,
        p_CourseID   IN COURSEIMAGE.CourseID%TYPE,
        p_ImagePath  IN COURSEIMAGE.ImagePath%TYPE,
        p_Caption    IN COURSEIMAGE.Caption%TYPE,
        p_SortOrder  IN COURSEIMAGE.SortOrder%TYPE
    ) IS
    BEGIN
        INSERT INTO COURSEIMAGE (ImageID, CourseID, ImagePath, Caption, SortOrder)
        VALUES (p_ImageID, p_CourseID, p_ImagePath, p_Caption, p_SortOrder);
        -- PHP BLL checks for affectedRows === 1. SQL%ROWCOUNT will be 1 on success.
        -- Oracle will handle PK violation (DUP_VAL_ON_INDEX for ImageID).
        -- Oracle will handle FK violation (if CourseID does not exist in COURSE table).
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END CREATE_IMAGE_PROC;

    PROCEDURE UPDATE_IMAGE_PROC(
        p_ImageID    IN COURSEIMAGE.ImageID%TYPE,
        p_CourseID   IN COURSEIMAGE.CourseID%TYPE,
        p_ImagePath  IN COURSEIMAGE.ImagePath%TYPE,
        p_Caption    IN COURSEIMAGE.Caption%TYPE,
        p_SortOrder  IN COURSEIMAGE.SortOrder%TYPE
    ) IS
    BEGIN
        UPDATE COURSEIMAGE
        SET CourseID   = p_CourseID,
            ImagePath  = p_ImagePath,
            Caption    = p_Caption,
            SortOrder  = p_SortOrder
        WHERE ImageID = p_ImageID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20051, 'CourseImage with ImageID ''' || p_ImageID || ''' not found for update.');
        END IF;
        -- PHP BLL checks ($stid !== false), implying success if no Oracle error.
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20051 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UPDATE_IMAGE_PROC: ' || SQLERRM);
            END IF;
    END UPDATE_IMAGE_PROC;

    PROCEDURE UNLINK_IMAGE_COURSE_PROC(
        p_ImageID  IN COURSEIMAGE.ImageID%TYPE,
        p_CourseID IN COURSEIMAGE.CourseID%TYPE
    ) IS
    BEGIN
        DELETE FROM COURSEIMAGE
        WHERE ImageID = p_ImageID AND CourseID = p_CourseID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20052, 'CourseImage with ImageID ''' || p_ImageID || ''' and CourseID ''' || p_CourseID || ''' not found for deletion.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20052 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UNLINK_IMAGE_COURSE_PROC: ' || SQLERRM);
            END IF;
    END UNLINK_IMAGE_COURSE_PROC;

    FUNCTION GET_IMAGE_BY_IMAGE_ID_FUNC(
        p_ImageID IN COURSEIMAGE.ImageID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                ImageID,
                CourseID,
                ImagePath,
                Caption,
                SortOrder,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                COURSEIMAGE
            WHERE
                ImageID = p_ImageID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_IMAGE_BY_IMAGE_ID_FUNC;

    FUNCTION GET_IMAGES_BY_COURSE_ID_FUNC(
        p_CourseID IN COURSEIMAGE.CourseID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                ImageID,
                CourseID,
                ImagePath,
                Caption,
                SortOrder,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                COURSEIMAGE
            WHERE
                CourseID = p_CourseID
            ORDER BY SortOrder ASC; -- As per BLL
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_IMAGES_BY_COURSE_ID_FUNC;

END COURSE_IMAGE_PKG;
/