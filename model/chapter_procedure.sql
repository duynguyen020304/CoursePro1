CREATE OR REPLACE PACKAGE CHAPTER_PKG AS
    PROCEDURE CREATE_NEW_CHAPTER (
        p_chapterID   IN COURSECHAPTER.ChapterID%TYPE,
        p_courseID    IN COURSECHAPTER.CourseID%TYPE,
        p_title       IN COURSECHAPTER.Title%TYPE,
        p_description IN COURSECHAPTER.Description%TYPE,
        p_sortOrder   IN COURSECHAPTER.SortOrder%TYPE,
        p_success     OUT BOOLEAN
    );
    PROCEDURE UPDATE_CHAPTER (
        p_chapterID   IN COURSECHAPTER.ChapterID%TYPE,
        p_courseID    IN COURSECHAPTER.CourseID%TYPE,
        p_title       IN COURSECHAPTER.Title%TYPE,
        p_description IN COURSECHAPTER.Description%TYPE,
        p_sortOrder   IN COURSECHAPTER.SortOrder%TYPE,
        p_success     OUT BOOLEAN
    );
    PROCEDURE DELETE_CHAPTER (
        p_chapterID IN COURSECHAPTER.ChapterID%TYPE,
        p_success   OUT BOOLEAN
    );
    TYPE t_chapter_cursor IS REF CURSOR;
    PROCEDURE GET_ALL_CHAPTERS (
        p_cursor    OUT t_chapter_cursor
    );
    PROCEDURE GET_CHAPTER_BY_ID (
        p_chapterID         IN COURSECHAPTER.ChapterID%TYPE,
        p_found_chapterID   OUT COURSECHAPTER.ChapterID%TYPE,
        p_courseID          OUT COURSECHAPTER.CourseID%TYPE,
        p_title             OUT COURSECHAPTER.Title%TYPE,
        p_description       OUT COURSECHAPTER.Description%TYPE,
        p_sortOrder         OUT COURSECHAPTER.SortOrder%TYPE,
        p_created_at        OUT VARCHAR2
    );
    PROCEDURE GET_CHAPTERS_BY_COURSE_ID (
        p_courseID  IN COURSECHAPTER.CourseID%TYPE,
        p_cursor    OUT t_chapter_cursor
    );
END CHAPTER_PKG;
/

CREATE OR REPLACE PACKAGE BODY CHAPTER_PKG AS
    PROCEDURE CREATE_NEW_CHAPTER (
        p_chapterID   IN COURSECHAPTER.ChapterID%TYPE,
        p_courseID    IN COURSECHAPTER.CourseID%TYPE,
        p_title       IN COURSECHAPTER.Title%TYPE,
        p_description IN COURSECHAPTER.Description%TYPE,
        p_sortOrder   IN COURSECHAPTER.SortOrder%TYPE,
        p_success     OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM COURSECHAPTER WHERE ChapterID = p_chapterID;
        IF v_count = 0 THEN
            INSERT INTO COURSECHAPTER (ChapterID, CourseID, Title, Description, SortOrder)
            VALUES (p_chapterID, p_courseID, p_title, p_description, p_sortOrder);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error: ChapterID ' || p_chapterID || ' already exists.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_NEW_CHAPTER: ' || SQLERRM);
    END CREATE_NEW_CHAPTER;

    PROCEDURE UPDATE_CHAPTER (
        p_chapterID   IN COURSECHAPTER.ChapterID%TYPE,
        p_courseID    IN COURSECHAPTER.CourseID%TYPE,
        p_title       IN COURSECHAPTER.Title%TYPE,
        p_description IN COURSECHAPTER.Description%TYPE,
        p_sortOrder   IN COURSECHAPTER.SortOrder%TYPE,
        p_success     OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        UPDATE COURSECHAPTER
        SET
            CourseID    = p_courseID,
            Title       = p_title,
            Description = p_description,
            SortOrder   = p_sortOrder
        WHERE ChapterID = p_chapterID;
        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No chapter updated for ChapterID ' || p_chapterID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_CHAPTER: ' || SQLERRM);
    END UPDATE_CHAPTER;

    PROCEDURE DELETE_CHAPTER (
        p_chapterID IN COURSECHAPTER.ChapterID%TYPE,
        p_success   OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM COURSECHAPTER WHERE ChapterID = p_chapterID;
        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No chapter deleted for ChapterID ' || p_chapterID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_CHAPTER: ' || SQLERRM);
    END DELETE_CHAPTER;

    PROCEDURE GET_ALL_CHAPTERS (
        p_cursor    OUT t_chapter_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT ChapterID, CourseID, Title, Description, SortOrder,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM COURSECHAPTER
            ORDER BY SortOrder ASC, Title ASC;
    EXCEPTION
        WHEN OTHERS THEN
            IF p_cursor%ISOPEN THEN
                CLOSE p_cursor;
            END IF;
            DBMS_OUTPUT.PUT_LINE('Error in GET_ALL_CHAPTERS: ' || SQLERRM);
    END GET_ALL_CHAPTERS;

    PROCEDURE GET_CHAPTER_BY_ID (
        p_chapterID         IN COURSECHAPTER.ChapterID%TYPE,
        p_found_chapterID   OUT COURSECHAPTER.ChapterID%TYPE,
        p_courseID          OUT COURSECHAPTER.CourseID%TYPE,
        p_title             OUT COURSECHAPTER.Title%TYPE,
        p_description       OUT COURSECHAPTER.Description%TYPE,
        p_sortOrder         OUT COURSECHAPTER.SortOrder%TYPE,
        p_created_at        OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT ChapterID, CourseID, Title, Description, SortOrder,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_chapterID, p_courseID, p_title, p_description, p_sortOrder, p_created_at
        FROM COURSECHAPTER
        WHERE ChapterID = p_chapterID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_chapterID := NULL;
            p_courseID := NULL;
            p_title := NULL;
            p_description := NULL;
            p_sortOrder := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_chapterID := NULL;
            p_courseID := NULL;
            p_title := NULL;
            p_description := NULL;
            p_sortOrder := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_CHAPTER_BY_ID: ' || SQLERRM);
    END GET_CHAPTER_BY_ID;

    PROCEDURE GET_CHAPTERS_BY_COURSE_ID (
        p_courseID  IN COURSECHAPTER.CourseID%TYPE,
        p_cursor    OUT t_chapter_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT ChapterID, CourseID, Title, Description, SortOrder,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM COURSECHAPTER
            WHERE CourseID = p_courseID
            ORDER BY SortOrder ASC;
    EXCEPTION
        WHEN OTHERS THEN
            IF p_cursor%ISOPEN THEN
                CLOSE p_cursor;
            END IF;
            DBMS_OUTPUT.PUT_LINE('Error in GET_CHAPTERS_BY_COURSE_ID: ' || SQLERRM);
    END GET_CHAPTERS_BY_COURSE_ID;

END CHAPTER_PKG;
/

SET SERVEROUTPUT ON;

-- Các ví dụ sử dụng các procedure
DECLARE
    v_success BOOLEAN;
BEGIN
    CHAPTER_PKG.CREATE_NEW_CHAPTER('CHAP001', 'COURSE001', 'Introduction to SQL', 'This chapter covers the basics of SQL.', 1, v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Chapter CHAP001 created successfully.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to create chapter CHAP001.'); END IF;

    CHAPTER_PKG.CREATE_NEW_CHAPTER('CHAP002', 'COURSE001', 'Advanced SQL', 'Deep dive into complex SQL queries.', 2, v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Chapter CHAP002 created successfully.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to create chapter CHAP002.'); END IF;

    CHAPTER_PKG.CREATE_NEW_CHAPTER('CHAP003', 'COURSE002', 'PHP Fundamentals', 'Learn the basics of PHP programming.', 1, v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Chapter CHAP003 created successfully.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to create chapter CHAP003.'); END IF;
END;
/

-- Các ví dụ lấy thông tin chapter theo ID
DECLARE
    v_chapterID         COURSECHAPTER.ChapterID%TYPE;
    v_courseID          COURSECHAPTER.CourseID%TYPE;
    v_title             COURSECHAPTER.Title%TYPE;
    v_description       COURSECHAPTER.Description%TYPE;
    v_sortOrder         COURSECHAPTER.SortOrder%TYPE;
    v_created_at        VARCHAR2(50);
BEGIN
    CHAPTER_PKG.GET_CHAPTER_BY_ID('CHAP001', v_chapterID, v_courseID, v_title, v_description, v_sortOrder, v_created_at);
    IF v_chapterID IS NOT NULL THEN
        DBMS_OUTPUT.PUT_LINE('Chapter found for CHAP001:');
        DBMS_OUTPUT.PUT_LINE('  ID: ' || v_chapterID || ', CourseID: ' || v_courseID || ', Title: ' || v_title || ', Description: ' || v_description || ', SortOrder: ' || v_sortOrder);
    ELSE
        DBMS_OUTPUT.PUT_LINE('No chapter found for CHAP001.');
    END IF;

    CHAPTER_PKG.GET_CHAPTER_BY_ID('NONEXISTENT_CHAP', v_chapterID, v_courseID, v_title, v_description, v_sortOrder, v_created_at);
    IF v_chapterID IS NOT NULL THEN
        DBMS_OUTPUT.PUT_LINE('Chapter found for NONEXISTENT_CHAP:');
    ELSE
        DBMS_OUTPUT.PUT_LINE('No chapter found for NONEXISTENT_CHAP (expected).');
    END IF;
END;
/

-- Các ví dụ cập nhật chapter
DECLARE
    v_success BOOLEAN;
BEGIN
    CHAPTER_PKG.UPDATE_CHAPTER('CHAP001', 'COURSE001', 'Introduction to SQL (Updated)', 'This chapter now includes advanced topics.', 10, v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Chapter CHAP001 updated successfully.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to update chapter CHAP001.'); END IF;
END;
/

-- Các ví dụ lấy tất cả các chapter
DECLARE
    v_cursor        CHAPTER_PKG.t_chapter_cursor;
    v_chapterID     COURSECHAPTER.ChapterID%TYPE;
    v_courseID      COURSECHAPTER.CourseID%TYPE;
    v_title         COURSECHAPTER.Title%TYPE;
    v_description   COURSECHAPTER.Description%TYPE;
    v_sortOrder     COURSECHAPTER.SortOrder%TYPE;
    v_created_at    VARCHAR2(50);
BEGIN
    DBMS_OUTPUT.PUT_LINE('All Chapters:');
    CHAPTER_PKG.GET_ALL_CHAPTERS(v_cursor);
    LOOP
        FETCH v_cursor INTO v_chapterID, v_courseID, v_title, v_description, v_sortOrder, v_created_at;
        EXIT WHEN v_cursor%NOTFOUND;
        DBMS_OUTPUT.PUT_LINE('  ID: ' || v_chapterID || ', CourseID: ' || v_courseID || ', Title: ' || v_title || ', SortOrder: ' || v_sortOrder);
    END LOOP;
    CLOSE v_cursor;
END;
/

-- Các ví dụ lấy chapter theo CourseID
DECLARE
    v_cursor        CHAPTER_PKG.t_chapter_cursor;
    v_chapterID     COURSECHAPTER.ChapterID%TYPE;
    v_courseID      COURSECHAPTER.CourseID%TYPE;
    v_title         COURSECHAPTER.Title%TYPE;
    v_description   COURSECHAPTER.Description%TYPE;
    v_sortOrder     COURSECHAPTER.SortOrder%TYPE;
    v_created_at    VARCHAR2(50);
BEGIN
    DBMS_OUTPUT.PUT_LINE('Chapters for COURSE001:');
    CHAPTER_PKG.GET_CHAPTERS_BY_COURSE_ID('COURSE001', v_cursor);
    LOOP
        FETCH v_cursor INTO v_chapterID, v_courseID, v_title, v_description, v_sortOrder, v_created_at;
        EXIT WHEN v_cursor%NOTFOUND;
        DBMS_OUTPUT.PUT_LINE('  ID: ' || v_chapterID || ', Title: ' || v_title || ', SortOrder: ' || v_sortOrder);
    END LOOP;
    CLOSE v_cursor;
END;
/

-- Các ví dụ xóa chapter
DECLARE
    v_success BOOLEAN;
BEGIN
    CHAPTER_PKG.DELETE_CHAPTER('CHAP003', v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Chapter CHAP003 deleted successfully.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to delete chapter CHAP003.'); END IF;

    CHAPTER_PKG.DELETE_CHAPTER('NONEXISTENT_CHAP', v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Chapter NONEXISTENT_CHAP deleted successfully.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to delete chapter NONEXISTENT_CHAP (expected).'); END IF;
END;
/

-- Kiểm tra lại dữ liệu
SELECT * FROM COURSECHAPTER;