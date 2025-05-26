
CREATE OR REPLACE PACKAGE COURSE_PKG AS
    PROCEDURE CREATE_NEW_COURSE (
        p_courseID    IN COURSE.CourseID%TYPE,
        p_title       IN COURSE.Title%TYPE,
        p_description IN COURSE.Description%TYPE,
        p_price       IN COURSE.Price%TYPE,
        p_createdBy   IN COURSE.CreatedBy%TYPE,
        p_success     OUT BOOLEAN
    );
    PROCEDURE DELETE_COURSE (
        p_courseID  IN COURSE.CourseID%TYPE,
        p_success   OUT BOOLEAN
    );
    PROCEDURE UPDATE_COURSE (
        p_courseID    IN COURSE.CourseID%TYPE,
        p_title       IN COURSE.Title%TYPE,
        p_description IN COURSE.Description%TYPE,
        p_price       IN COURSE.Price%TYPE,
        p_createdBy   IN COURSE.CreatedBy%TYPE,
        p_success     OUT BOOLEAN
    );
    TYPE t_course_cursor IS REF CURSOR;
    PROCEDURE GET_COURSE_BY_ID (
        p_courseID          IN COURSE.CourseID%TYPE,
        p_found_courseID    OUT COURSE.CourseID%TYPE,
        p_title             OUT COURSE.Title%TYPE,
        p_description       OUT COURSE.Description%TYPE,
        p_price             OUT COURSE.Price%TYPE,
        p_createdBy         OUT COURSE.CreatedBy%TYPE,
        p_created_at        OUT VARCHAR2
    );
    PROCEDURE GET_ALL_COURSES (
        p_cursor    OUT t_course_cursor
    );
END COURSE_PKG;
/

CREATE OR REPLACE PACKAGE BODY COURSE_PKG AS
    PROCEDURE CREATE_NEW_COURSE (
        p_courseID    IN COURSE.CourseID%TYPE,
        p_title       IN COURSE.Title%TYPE,
        p_description IN COURSE.Description%TYPE,
        p_price       IN COURSE.Price%TYPE,
        p_createdBy   IN COURSE.CreatedBy%TYPE,
        p_success     OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM COURSE WHERE CourseID = p_courseID;
        IF v_count = 0 THEN
            INSERT INTO COURSE (CourseID, Title, Description, Price, CreatedBy)
            VALUES (p_courseID, p_title, p_description, p_price, p_createdBy);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error: CourseID ' || p_courseID || ' already exists.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_NEW_COURSE: ' || SQLERRM);
    END CREATE_NEW_COURSE;

    PROCEDURE DELETE_COURSE (
        p_courseID  IN COURSE.CourseID%TYPE,
        p_success   OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM COURSE WHERE CourseID = p_courseID;
        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No course deleted for CourseID ' || p_courseID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_COURSE: ' || SQLERRM);
    END DELETE_COURSE;

    PROCEDURE UPDATE_COURSE (
        p_courseID    IN COURSE.CourseID%TYPE,
        p_title       IN COURSE.Title%TYPE,
        p_description IN COURSE.Description%TYPE,
        p_price       IN COURSE.Price%TYPE,
        p_createdBy   IN COURSE.CreatedBy%TYPE,
        p_success     OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        UPDATE COURSE
        SET
            Title       = p_title,
            Description = p_description,
            Price       = p_price,
            CreatedBy   = p_createdBy
        WHERE CourseID = p_courseID;
        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No course updated for CourseID ' || p_courseID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_COURSE: ' || SQLERRM);
    END UPDATE_COURSE;

    PROCEDURE GET_COURSE_BY_ID (
        p_courseID          IN COURSE.CourseID%TYPE,
        p_found_courseID    OUT COURSE.CourseID%TYPE,
        p_title             OUT COURSE.Title%TYPE,
        p_description       OUT COURSE.Description%TYPE,
        p_price             OUT COURSE.Price%TYPE,
        p_createdBy         OUT COURSE.CreatedBy%TYPE,
        p_created_at        OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT CourseID, Title, Description, Price, CreatedBy,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_courseID, p_title, p_description, p_price, p_createdBy, p_created_at
        FROM COURSE
        WHERE CourseID = p_courseID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_courseID := NULL;
            p_title := NULL;
            p_description := NULL;
            p_price := NULL;
            p_createdBy := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_courseID := NULL;
            p_title := NULL;
            p_description := NULL;
            p_price := NULL;
            p_createdBy := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_COURSE_BY_ID: ' || SQLERRM);
    END GET_COURSE_BY_ID;

    PROCEDURE GET_ALL_COURSES (
        p_cursor    OUT t_course_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT CourseID, Title, Description, Price, CreatedBy,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM COURSE
            ORDER BY Title ASC;
    EXCEPTION
        WHEN OTHERS THEN
            IF p_cursor%ISOPEN THEN
                CLOSE p_cursor;
            END IF;
            DBMS_OUTPUT.PUT_LINE('Error in GET_ALL_COURSES: ' || SQLERRM);
    END GET_ALL_COURSES;

END COURSE_PKG;
/

SET SERVEROUTPUT ON;

-- Các ví dụ sử dụng các procedure
DECLARE
    v_success BOOLEAN;
BEGIN
    COURSE_PKG.CREATE_NEW_COURSE('COURSE001', 'Database Fundamentals', 'An introductory course to relational databases.', 99.99, 'AdminUser', v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Course COURSE001 created successfully.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to create course COURSE001.'); END IF;

    COURSE_PKG.CREATE_NEW_COURSE('COURSE002', 'Web Development with PHP', 'Learn to build dynamic web applications using PHP.', 149.99, 'DevUser', v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Course COURSE002 created successfully.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to create course COURSE002.'); END IF;

    COURSE_PKG.CREATE_NEW_COURSE('COURSE003', 'Advanced Algorithms', 'Explore complex algorithms and data structures.', 199.99, 'AlgoExpert', v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Course COURSE003 created successfully.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to create course COURSE003.'); END IF;
END;
/

-- Các ví dụ lấy thông tin khóa học theo ID
DECLARE
    v_courseID          COURSE.CourseID%TYPE;
    v_title             COURSE.Title%TYPE;
    v_description       COURSE.Description%TYPE;
    v_price             COURSE.Price%TYPE;
    v_createdBy         COURSE.CreatedBy%TYPE;
    v_created_at        VARCHAR2(50);
BEGIN
    COURSE_PKG.GET_COURSE_BY_ID('COURSE001', v_courseID, v_title, v_description, v_price, v_createdBy, v_created_at);
    IF v_courseID IS NOT NULL THEN
        DBMS_OUTPUT.PUT_LINE('Course found for COURSE001:');
        DBMS_OUTPUT.PUT_LINE('  ID: ' || v_courseID || ', Title: ' || v_title || ', Price: ' || v_price || ', CreatedBy: ' || v_createdBy);
    ELSE
        DBMS_OUTPUT.PUT_LINE('No course found for COURSE001.');
    END IF;

    COURSE_PKG.GET_COURSE_BY_ID('NONEXISTENT_COURSE', v_courseID, v_title, v_description, v_price, v_createdBy, v_created_at);
    IF v_courseID IS NOT NULL THEN
        DBMS_OUTPUT.PUT_LINE('Course found for NONEXISTENT_COURSE:');
    ELSE
        DBMS_OUTPUT.PUT_LINE('No course found for NONEXISTENT_COURSE (expected).');
    END IF;
END;
/

-- Các ví dụ cập nhật khóa học
DECLARE
    v_success BOOLEAN;
BEGIN
    COURSE_PKG.UPDATE_COURSE('COURSE001', 'Database Fundamentals (Revised)', 'An updated introductory course to relational databases with new content.', 109.99, 'AdminUser', v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Course COURSE001 updated successfully.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to update course COURSE001.'); END IF;
END;
/

-- Các ví dụ lấy tất cả các khóa học
DECLARE
    v_cursor        COURSE_PKG.t_course_cursor;
    v_courseID      COURSE.CourseID%TYPE;
    v_title         COURSE.Title%TYPE;
    v_description   COURSE.Description%TYPE;
    v_price         COURSE.Price%TYPE;
    v_createdBy     COURSE.CreatedBy%TYPE;
    v_created_at    VARCHAR2(50);
BEGIN
    DBMS_OUTPUT.PUT_LINE('All Courses:');
    COURSE_PKG.GET_ALL_COURSES(v_cursor);
    LOOP
        FETCH v_cursor INTO v_courseID, v_title, v_description, v_price, v_createdBy, v_created_at;
        EXIT WHEN v_cursor%NOTFOUND;
        DBMS_OUTPUT.PUT_LINE('  ID: ' || v_courseID || ', Title: ' || v_title || ', Price: ' || v_price || ', CreatedBy: ' || v_createdBy);
    END LOOP;
    CLOSE v_cursor;
END;
/