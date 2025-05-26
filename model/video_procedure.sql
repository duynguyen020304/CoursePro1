-- Tạo bảng CourseVideo nếu chưa tồn tại
-- Bảng này chứa thông tin về các video bài học.
-- Giả định bảng COURSELESSON đã tồn tại để thiết lập khóa ngoại.
CREATE TABLE CourseVideo (
                             VideoID     VARCHAR2(50) PRIMARY KEY,
                             LessonID    VARCHAR2(50) NOT NULL,
                             Url         VARCHAR2(1000) NOT NULL, -- URL của video
                             Title       VARCHAR2(500) NOT NULL,
                             Duration    NUMBER(10) DEFAULT 0 NOT NULL, -- Thời lượng video tính bằng giây
                             SortOrder   NUMBER(10) DEFAULT 0 NOT NULL,
                             created_at  TIMESTAMP(6) DEFAULT SYSTIMESTAMP,
                             CONSTRAINT fk_coursevideo_lesson FOREIGN KEY (LessonID) REFERENCES COURSELESSON(LessonID) ON DELETE CASCADE
);

-- Tạo PACKAGE SPECIFICATION cho VIDEO
CREATE OR REPLACE PACKAGE VIDEO_PKG AS

    -- Procedure để tạo một video mới
    PROCEDURE CREATE_NEW_VIDEO (
        p_videoID   IN CourseVideo.VideoID%TYPE,
        p_lessonID  IN CourseVideo.LessonID%TYPE,
        p_url       IN CourseVideo.Url%TYPE,
        p_title     IN CourseVideo.Title%TYPE,
        p_duration  IN CourseVideo.Duration%TYPE,
        p_sortOrder IN CourseVideo.SortOrder%TYPE,
        p_success   OUT BOOLEAN
    );

    -- Procedure để cập nhật thông tin video
    PROCEDURE UPDATE_VIDEO (
        p_videoID   IN CourseVideo.VideoID%TYPE,
        p_lessonID  IN CourseVideo.LessonID%TYPE,
        p_url       IN CourseVideo.Url%TYPE,
        p_title     IN CourseVideo.Title%TYPE,
        p_duration  IN CourseVideo.Duration%TYPE,
        p_sortOrder IN CourseVideo.SortOrder%TYPE,
        p_success   OUT BOOLEAN
    );

    -- Procedure để xóa một video
    PROCEDURE DELETE_VIDEO (
        p_videoID IN CourseVideo.VideoID%TYPE,
        p_success OUT BOOLEAN
    );

    -- Type cho REF CURSOR để trả về nhiều bản ghi
    TYPE t_video_cursor IS REF CURSOR;

    -- Procedure để lấy thông tin một video theo VideoID
    PROCEDURE GET_VIDEO_BY_ID (
        p_videoID           IN CourseVideo.VideoID%TYPE,
        p_found_videoID     OUT CourseVideo.VideoID%TYPE,
        p_lessonID          OUT CourseVideo.LessonID%TYPE,
        p_url               OUT CourseVideo.Url%TYPE,
        p_title             OUT CourseVideo.Title%TYPE,
        p_duration          OUT CourseVideo.Duration%TYPE,
        p_sortOrder         OUT CourseVideo.SortOrder%TYPE,
        p_created_at        OUT VARCHAR2 -- Định dạng chuỗi 'YYYY-MM-DD HH24:MI:SS.FF6'
    );

    -- Procedure để lấy tất cả các video của một bài học cụ thể
    PROCEDURE GET_VIDEOS_BY_LESSON_ID (
        p_lessonID  IN CourseVideo.LessonID%TYPE,
        p_cursor    OUT t_video_cursor
    );

END VIDEO_PKG;
/

-- Tạo PACKAGE BODY cho VIDEO
CREATE OR REPLACE PACKAGE BODY VIDEO_PKG AS

    -- Implementation for CREATE_NEW_VIDEO
    PROCEDURE CREATE_NEW_VIDEO (
        p_videoID   IN CourseVideo.VideoID%TYPE,
        p_lessonID  IN CourseVideo.LessonID%TYPE,
        p_url       IN CourseVideo.Url%TYPE,
        p_title     IN CourseVideo.Title%TYPE,
        p_duration  IN CourseVideo.Duration%TYPE,
        p_sortOrder IN CourseVideo.SortOrder%TYPE,
        p_success   OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        -- Kiểm tra xem VideoID đã tồn tại chưa để tránh lỗi PRIMARY KEY
        SELECT COUNT(*) INTO v_count FROM CourseVideo WHERE VideoID = p_videoID;

        IF v_count = 0 THEN
            INSERT INTO CourseVideo (VideoID, LessonID, Url, Title, Duration, SortOrder)
            VALUES (p_videoID, p_lessonID, p_url, p_title, p_duration, p_sortOrder);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: VideoID ' || p_videoID || ' already exists.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_NEW_VIDEO: ' || SQLERRM);
    END CREATE_NEW_VIDEO;

    -- Implementation for UPDATE_VIDEO
    PROCEDURE UPDATE_VIDEO (
        p_videoID   IN CourseVideo.VideoID%TYPE,
        p_lessonID  IN CourseVideo.LessonID%TYPE,
        p_url       IN CourseVideo.Url%TYPE,
        p_title     IN CourseVideo.Title%TYPE,
        p_duration  IN CourseVideo.Duration%TYPE,
        p_sortOrder IN CourseVideo.SortOrder%TYPE,
        p_success   OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        UPDATE CourseVideo
        SET
            LessonID  = p_lessonID,
            Url       = p_url,
            Title     = p_title,
            Duration  = p_duration,
            SortOrder = p_sortOrder
        WHERE VideoID = p_videoID;

        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No video updated for VideoID ' || p_videoID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_VIDEO: ' || SQLERRM);
    END UPDATE_VIDEO;

    -- Implementation for DELETE_VIDEO
    PROCEDURE DELETE_VIDEO (
        p_videoID IN CourseVideo.VideoID%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM CourseVideo
        WHERE VideoID = p_videoID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No video deleted for VideoID ' || p_videoID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_VIDEO: ' || SQLERRM);
    END DELETE_VIDEO;

    -- Implementation for GET_VIDEO_BY_ID
    PROCEDURE GET_VIDEO_BY_ID (
        p_videoID           IN CourseVideo.VideoID%TYPE,
        p_found_videoID     OUT CourseVideo.VideoID%TYPE,
        p_lessonID          OUT CourseVideo.LessonID%TYPE,
        p_url               OUT CourseVideo.Url%TYPE,
        p_title             OUT CourseVideo.Title%TYPE,
        p_duration          OUT CourseVideo.Duration%TYPE,
        p_sortOrder         OUT CourseVideo.SortOrder%TYPE,
        p_created_at        OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT VideoID, LessonID, Url, Title, Duration, SortOrder,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_videoID, p_lessonID, p_url, p_title, p_duration, p_sortOrder, p_created_at
        FROM CourseVideo
        WHERE VideoID = p_videoID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_videoID := NULL;
            p_lessonID := NULL;
            p_url := NULL;
            p_title := NULL;
            p_duration := NULL;
            p_sortOrder := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_videoID := NULL;
            p_lessonID := NULL;
            p_url := NULL;
            p_title := NULL;
            p_duration := NULL;
            p_sortOrder := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_VIDEO_BY_ID: ' || SQLERRM);
    END GET_VIDEO_BY_ID;

    -- Implementation for GET_VIDEOS_BY_LESSON_ID
    PROCEDURE GET_VIDEOS_BY_LESSON_ID (
        p_lessonID  IN CourseVideo.LessonID%TYPE,
        p_cursor    OUT t_video_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT VideoID, LessonID, Url, Title, Duration, SortOrder,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM CourseVideo
            WHERE LessonID = p_lessonID
            ORDER BY SortOrder ASC;
    EXCEPTION
        WHEN OTHERS THEN
            IF p_cursor%ISOPEN THEN
                CLOSE p_cursor;
            END IF;
            DBMS_OUTPUT.PUT_LINE('Error in GET_VIDEOS_BY_LESSON_ID: ' || SQLERRM);
    END GET_VIDEOS_BY_LESSON_ID;

END VIDEO_PKG;
/

-- Ví dụ cách sử dụng các procedure (trong SQL*Plus hoặc SQL Developer)
-- Bật output để xem kết quả từ DBMS_OUTPUT.PUT_LINE
SET SERVEROUTPUT ON;

-- *************************************************************************
-- LƯU Ý QUAN TRỌNG:
-- Để các ví dụ này chạy được, bạn cần đảm bảo bảng COURSELESSON đã tồn tại
-- và có dữ liệu LessonID tương ứng.
-- Ví dụ:
-- INSERT INTO COURSE (CourseID, Title, Description, Price, CreatedBy) VALUES ('COURSE_V', 'Course with Videos', 'Desc', 120.0, 'Admin');
-- INSERT INTO COURSECHAPTER (ChapterID, CourseID, Title, Description, SortOrder) VALUES ('CHAP_V_INTRO', 'COURSE_V', 'Video Intro', 'Intro to videos.', 1);
-- INSERT INTO COURSELESSON (LessonID, CourseID, ChapterID, Title, Content, SortOrder) VALUES ('LESSON_V_01', 'COURSE_V', 'CHAP_V_INTRO', 'First Video Lesson', 'Video content.', 1);
-- *************************************************************************

-- Ví dụ 1: Tạo một video mới
DECLARE
    v_success BOOLEAN;
BEGIN
    -- Giả sử 'LESSON_V_01' tồn tại
    VIDEO_PKG.CREATE_NEW_VIDEO('VID001', 'LESSON_V_01', 'https://example.com/video1.mp4', 'Introduction to Topic', 300, 1, v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Video VID001 created successfully.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to create video VID001.'); END IF;

    VIDEO_PKG.CREATE_NEW_VIDEO('VID002', 'LESSON_V_01', 'https://example.com/video2.mp4', 'Advanced Concepts', 600, 2, v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Video VID002 created successfully.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to create video VID002.'); END IF;

    VIDEO_PKG.CREATE_NEW_VIDEO('VID003', 'LESSON_V_02', 'https://example.com/video3.mp4', 'Review Session', 450, 1, v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Video VID003 created successfully.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to create video VID003.'); END IF;
END;
/

-- Ví dụ 2: Lấy thông tin video theo VideoID
DECLARE
    v_videoID       CourseVideo.VideoID%TYPE;
    v_lessonID      CourseVideo.LessonID%TYPE;
    v_url           CourseVideo.Url%TYPE;
    v_title         CourseVideo.Title%TYPE;
    v_duration      CourseVideo.Duration%TYPE;
    v_sortOrder     CourseVideo.SortOrder%TYPE;
    v_created_at    VARCHAR2(50);
BEGIN
    VIDEO_PKG.GET_VIDEO_BY_ID('VID001', v_videoID, v_lessonID, v_url, v_title, v_duration, v_sortOrder, v_created_at);
    IF v_videoID IS NOT NULL THEN
        DBMS_OUTPUT.PUT_LINE('Video found for VID001:');
        DBMS_OUTPUT.PUT_LINE('  ID: ' || v_videoID || ', Title: ' || v_title || ', Duration: ' || v_duration || 's');
    ELSE
        DBMS_OUTPUT.PUT_LINE('No video found for VID001.');
    END IF;

    VIDEO_PKG.GET_VIDEO_BY_ID('NONEXISTENT_VID', v_videoID, v_lessonID, v_url, v_title, v_duration, v_sortOrder, v_created_at);
    IF v_videoID IS NOT NULL THEN
        DBMS_OUTPUT.PUT_LINE('Video found for NONEXISTENT_VID:');
    ELSE
        DBMS_OUTPUT.PUT_LINE('No video found for NONEXISTENT_VID (expected).');
    END IF;
END;
/

-- Ví dụ 3: Cập nhật video
DECLARE
    v_success BOOLEAN;
BEGIN
    VIDEO_PKG.UPDATE_VIDEO('VID001', 'LESSON_V_01', 'https://example.com/video1_revised.mp4', 'Introduction to Topic (Revised)', 320, 10, v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Video VID001 updated successfully.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to update video VID001.'); END IF;
END;
/

-- Ví dụ 4: Lấy các video theo LessonID
DECLARE
    v_cursor        VIDEO_PKG.t_video_cursor;
    v_videoID       CourseVideo.VideoID%TYPE;
    v_lessonID      CourseVideo.LessonID%TYPE;
    v_url           CourseVideo.Url%TYPE;
    v_title         CourseVideo.Title%TYPE;
    v_duration      CourseVideo.Duration%TYPE;
    v_sortOrder     CourseVideo.SortOrder%TYPE;
    v_created_at    VARCHAR2(50);
BEGIN
    DBMS_OUTPUT.PUT_LINE('Videos for LESSON_V_01:');
    VIDEO_PKG.GET_VIDEOS_BY_LESSON_ID('LESSON_V_01', v_cursor);
    LOOP
        FETCH v_cursor INTO v_videoID, v_lessonID, v_url, v_title, v_duration, v_sortOrder, v_created_at;
        EXIT WHEN v_cursor%NOTFOUND;
        DBMS_OUTPUT.PUT_LINE('  ID: ' || v_videoID || ', Title: ' || v_title || ', SortOrder: ' || v_sortOrder);
    END LOOP;
    CLOSE v_cursor;
END;
/

-- Ví dụ 5: Xóa một video
DECLARE
    v_success BOOLEAN;
BEGIN
    VIDEO_PKG.DELETE_VIDEO('VID003', v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Video VID003 deleted successfully.'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to delete video VID003.'); END IF;

    VIDEO_PKG.DELETE_VIDEO('NONEXISTENT_VID', v_success);
    IF v_success THEN DBMS_OUTPUT.PUT_LINE('Video NONEXISTENT_VID deleted successfully (expected).'); ELSE DBMS_OUTPUT.PUT_LINE('Failed to delete video NONEXISTENT_VID (expected).'); END IF;
END;
/

-- Kiểm tra lại dữ liệu sau tất cả các thao tác
SELECT * FROM CourseVideo;
