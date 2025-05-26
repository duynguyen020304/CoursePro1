CREATE OR REPLACE PACKAGE LESSON_PKG AS

    PROCEDURE CREATE_NEW_LESSON (
        p_lessonID  IN COURSELESSON.LessonID%TYPE,
        p_courseID  IN COURSELESSON.CourseID%TYPE,
        p_chapterID IN COURSELESSON.ChapterID%TYPE,
        p_title     IN COURSELESSON.Title%TYPE,
        p_content   IN COURSELESSON.Content%TYPE,
        p_sortOrder IN COURSELESSON.SortOrder%TYPE,
        p_success   OUT BOOLEAN
    );

    PROCEDURE UPDATE_LESSON (
        p_lessonID  IN COURSELESSON.LessonID%TYPE,
        p_courseID  IN COURSELESSON.CourseID%TYPE,
        p_chapterID IN COURSELESSON.ChapterID%TYPE,
        p_title     IN COURSELESSON.Title%TYPE,
        p_content   IN COURSELESSON.Content%TYPE,
        p_sortOrder IN COURSELESSON.SortOrder%TYPE,
        p_success   OUT BOOLEAN
    );

    PROCEDURE DELETE_LESSON (
        p_lessonID IN COURSELESSON.LessonID%TYPE,
        p_success  OUT BOOLEAN
    );

    TYPE t_lesson_cursor IS REF CURSOR;

    PROCEDURE GET_LESSON_BY_ID (
        p_lessonID          IN COURSELESSON.LessonID%TYPE,
        p_found_lessonID    OUT COURSELESSON.LessonID%TYPE,
        p_courseID          OUT COURSELESSON.CourseID%TYPE,
        p_chapterID         OUT COURSELESSON.ChapterID%TYPE,
        p_title             OUT COURSELESSON.Title%TYPE,
        p_content           OUT COURSELESSON.Content%TYPE,
        p_sortOrder         OUT COURSELESSON.SortOrder%TYPE,
        p_created_at        OUT VARCHAR2
    );

    PROCEDURE GET_LESSONS_BY_CHAPTER_ID (
        p_chapterID IN COURSELESSON.ChapterID%TYPE,
        p_cursor    OUT t_lesson_cursor
    );

    PROCEDURE GET_LESSONS_BY_COURSE_ID (
        p_courseID  IN COURSELESSON.CourseID%TYPE,
        p_cursor    OUT t_lesson_cursor
    );

END LESSON_PKG;
/

CREATE OR REPLACE PACKAGE BODY LESSON_PKG AS

    PROCEDURE CREATE_NEW_LESSON (
        p_lessonID  IN COURSELESSON.LessonID%TYPE,
        p_courseID  IN COURSELESSON.CourseID%TYPE,
        p_chapterID IN COURSELESSON.ChapterID%TYPE,
        p_title     IN COURSELESSON.Title%TYPE,
        p_content   IN COURSELESSON.Content%TYPE,
        p_sortOrder IN COURSELESSON.SortOrder%TYPE,
        p_success   OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM COURSELESSON WHERE LessonID = p_lessonID;

        IF v_count = 0 THEN
            INSERT INTO COURSELESSON (LessonID, CourseID, ChapterID, Title, Content, SortOrder)
            VALUES (p_lessonID, p_courseID, p_chapterID, p_title, p_content, p_sortOrder);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: LessonID ' || p_lessonID || ' already exists.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_NEW_LESSON: ' || SQLERRM);
    END CREATE_NEW_LESSON;

    PROCEDURE UPDATE_LESSON (
        p_lessonID  IN COURSELESSON.LessonID%TYPE,
        p_courseID  IN COURSELESSON.CourseID%TYPE,
        p_chapterID IN COURSELESSON.ChapterID%TYPE,
        p_title     IN COURSELESSON.Title%TYPE,
        p_content   IN COURSELESSON.Content%TYPE,
        p_sortOrder IN COURSELESSON.SortOrder%TYPE,
        p_success   OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        UPDATE COURSELESSON
        SET
            CourseID  = p_courseID,
            ChapterID = p_chapterID,
            Title     = p_title,
            Content   = p_content,
            SortOrder = p_sortOrder
        WHERE LessonID = p_lessonID;

        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No lesson updated for LessonID ' || p_lessonID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_LESSON: ' || SQLERRM);
    END UPDATE_LESSON;

    PROCEDURE DELETE_LESSON (
        p_lessonID IN COURSELESSON.LessonID%TYPE,
        p_success  OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM COURSELESSON
        WHERE LessonID = p_lessonID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No lesson deleted for LessonID ' || p_lessonID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_LESSON: ' || SQLERRM);
    END DELETE_LESSON;

    PROCEDURE GET_LESSON_BY_ID (
        p_lessonID          IN COURSELESSON.LessonID%TYPE,
        p_found_lessonID    OUT COURSELESSON.LessonID%TYPE,
        p_courseID          OUT COURSELESSON.CourseID%TYPE,
        p_chapterID         OUT COURSELESSON.ChapterID%TYPE,
        p_title             OUT COURSELESSON.Title%TYPE,
        p_content           OUT COURSELESSON.Content%TYPE,
        p_sortOrder         OUT COURSELESSON.SortOrder%TYPE,
        p_created_at        OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT LessonID, CourseID, ChapterID, Title, Content, SortOrder,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_lessonID, p_courseID, p_chapterID, p_title, p_content, p_sortOrder, p_created_at
        FROM COURSELESSON
        WHERE LessonID = p_lessonID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_lessonID := NULL;
            p_courseID := NULL;
            p_chapterID := NULL;
            p_title := NULL;
            p_content := NULL;
            p_sortOrder := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_lessonID := NULL;
            p_courseID := NULL;
            p_chapterID := NULL;
            p_title := NULL;
            p_content := NULL;
            p_sortOrder := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_LESSON_BY_ID: ' || SQLERRM);
    END GET_LESSON_BY_ID;

    PROCEDURE GET_LESSONS_BY_CHAPTER_ID (
        p_chapterID IN COURSELESSON.ChapterID%TYPE,
        p_cursor    OUT t_lesson_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT LessonID, CourseID, ChapterID, Title, Content, SortOrder,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM COURSELESSON
            WHERE ChapterID = p_chapterID
            ORDER BY SortOrder ASC;
    EXCEPTION
        WHEN OTHERS THEN
            IF p_cursor%ISOPEN THEN
                CLOSE p_cursor;
            END IF;
            DBMS_OUTPUT.PUT_LINE('Error in GET_LESSONS_BY_CHAPTER_ID: ' || SQLERRM);
    END GET_LESSONS_BY_CHAPTER_ID;

    PROCEDURE GET_LESSONS_BY_COURSE_ID (
        p_courseID  IN COURSELESSON.CourseID%TYPE,
        p_cursor    OUT t_lesson_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT LessonID, CourseID, ChapterID, Title, Content, SortOrder,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM COURSELESSON
            WHERE CourseID = p_courseID
            ORDER BY ChapterID ASC, SortOrder ASC;
    EXCEPTION
        WHEN OTHERS THEN
            IF p_cursor%ISOPEN THEN
                CLOSE p_cursor;
            END IF;
            DBMS_OUTPUT.PUT_LINE('Error in GET_LESSONS_BY_COURSE_ID: ' || SQLERRM);
    END GET_LESSONS_BY_COURSE_ID;

END LESSON_PKG;