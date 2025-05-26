CREATE OR REPLACE PACKAGE COURSE_IMAGE_PKG AS

    PROCEDURE CREATE_NEW_IMAGE (
        p_imageID   IN COURSEIMAGE.ImageID%TYPE,
        p_courseID  IN COURSEIMAGE.CourseID%TYPE,
        p_imagePath IN COURSEIMAGE.ImagePath%TYPE,
        p_caption   IN COURSEIMAGE.Caption%TYPE,
        p_sortOrder IN COURSEIMAGE.SortOrder%TYPE,
        p_success   OUT BOOLEAN
    );

    PROCEDURE UPDATE_IMAGE (
        p_imageID   IN COURSEIMAGE.ImageID%TYPE,
        p_courseID  IN COURSEIMAGE.CourseID%TYPE,
        p_imagePath IN COURSEIMAGE.ImagePath%TYPE,
        p_caption   IN COURSEIMAGE.Caption%TYPE,
        p_sortOrder IN COURSEIMAGE.SortOrder%TYPE,
        p_success   OUT BOOLEAN
    );

    PROCEDURE UNLINK_IMAGE_COURSE (
        p_imageID   IN COURSEIMAGE.ImageID%TYPE,
        p_courseID  IN COURSEIMAGE.CourseID%TYPE,
        p_success   OUT BOOLEAN
    );

    TYPE t_course_image_cursor IS REF CURSOR;

    PROCEDURE GET_IMAGE_BY_IMAGE_ID (
        p_imageID           IN COURSEIMAGE.ImageID%TYPE,
        p_found_imageID     OUT COURSEIMAGE.ImageID%TYPE,
        p_courseID          OUT COURSEIMAGE.CourseID%TYPE,
        p_imagePath         OUT COURSEIMAGE.ImagePath%TYPE,
        p_caption           OUT COURSEIMAGE.Caption%TYPE,
        p_sortOrder         OUT COURSEIMAGE.SortOrder%TYPE,
        p_created_at        OUT VARCHAR2
    );

    PROCEDURE GET_IMAGES_BY_COURSE_ID (
        p_courseID  IN COURSEIMAGE.CourseID%TYPE,
        p_cursor    OUT t_course_image_cursor
    );

END COURSE_IMAGE_PKG;
/

CREATE OR REPLACE PACKAGE BODY COURSE_IMAGE_PKG AS

    PROCEDURE CREATE_NEW_IMAGE (
        p_imageID   IN COURSEIMAGE.ImageID%TYPE,
        p_courseID  IN COURSEIMAGE.CourseID%TYPE,
        p_imagePath IN COURSEIMAGE.ImagePath%TYPE,
        p_caption   IN COURSEIMAGE.Caption%TYPE,
        p_sortOrder IN COURSEIMAGE.SortOrder%TYPE,
        p_success   OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM COURSEIMAGE WHERE ImageID = p_imageID;

        IF v_count = 0 THEN
            INSERT INTO COURSEIMAGE (ImageID, CourseID, ImagePath, Caption, SortOrder)
            VALUES (p_imageID, p_courseID, p_imagePath, p_caption, p_sortOrder);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: ImageID ' || p_imageID || ' already exists.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_NEW_IMAGE: ' || SQLERRM);
    END CREATE_NEW_IMAGE;

    PROCEDURE UPDATE_IMAGE (
        p_imageID   IN COURSEIMAGE.ImageID%TYPE,
        p_courseID  IN COURSEIMAGE.CourseID%TYPE,
        p_imagePath IN COURSEIMAGE.ImagePath%TYPE,
        p_caption   IN COURSEIMAGE.Caption%TYPE,
        p_sortOrder IN COURSEIMAGE.SortOrder%TYPE,
        p_success   OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        UPDATE COURSEIMAGE
        SET
            CourseID  = p_courseID,
            ImagePath = p_imagePath,
            Caption   = p_caption,
            SortOrder = p_sortOrder
        WHERE ImageID = p_imageID;

        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No image updated for ImageID ' || p_imageID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_IMAGE: ' || SQLERRM);
    END UPDATE_IMAGE;

    PROCEDURE UNLINK_IMAGE_COURSE (
        p_imageID   IN COURSEIMAGE.ImageID%TYPE,
        p_courseID  IN COURSEIMAGE.CourseID%TYPE,
        p_success   OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM COURSEIMAGE
        WHERE ImageID = p_imageID AND CourseID = p_courseID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No image deleted for ImageID ' || p_imageID || ' and CourseID ' || p_courseID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UNLINK_IMAGE_COURSE: ' || SQLERRM);
    END UNLINK_IMAGE_COURSE;

    PROCEDURE GET_IMAGE_BY_IMAGE_ID (
        p_imageID           IN COURSEIMAGE.ImageID%TYPE,
        p_found_imageID     OUT COURSEIMAGE.ImageID%TYPE,
        p_courseID          OUT COURSEIMAGE.CourseID%TYPE,
        p_imagePath         OUT COURSEIMAGE.ImagePath%TYPE,
        p_caption           OUT COURSEIMAGE.Caption%TYPE,
        p_sortOrder         OUT COURSEIMAGE.SortOrder%TYPE,
        p_created_at        OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT ImageID, CourseID, ImagePath, Caption, SortOrder,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_imageID, p_courseID, p_imagePath, p_caption, p_sortOrder, p_created_at
        FROM COURSEIMAGE
        WHERE ImageID = p_imageID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_imageID := NULL;
            p_courseID := NULL;
            p_imagePath := NULL;
            p_caption := NULL;
            p_sortOrder := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_imageID := NULL;
            p_courseID := NULL;
            p_imagePath := NULL;
            p_caption := NULL;
            p_sortOrder := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_IMAGE_BY_IMAGE_ID: ' || SQLERRM);
    END GET_IMAGE_BY_IMAGE_ID;

    PROCEDURE GET_IMAGES_BY_COURSE_ID (
        p_courseID  IN COURSEIMAGE.CourseID%TYPE,
        p_cursor    OUT t_course_image_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT ImageID, CourseID, ImagePath, Caption, SortOrder,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM COURSEIMAGE
            WHERE CourseID = p_courseID
            ORDER BY SortOrder ASC;
    EXCEPTION
        WHEN OTHERS THEN
            IF p_cursor%ISOPEN THEN
                CLOSE p_cursor;
            END IF;
            DBMS_OUTPUT.PUT_LINE('Error in GET_IMAGES_BY_COURSE_ID: ' || SQLERRM);
    END GET_IMAGES_BY_COURSE_ID;

END COURSE_IMAGE_PKG;
/