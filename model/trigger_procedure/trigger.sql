CREATE OR REPLACE TRIGGER trg_users_audit
    AFTER INSERT OR UPDATE OR DELETE ON Users
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO Users_History (
            UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.UserID, :NEW.FirstName, :NEW.LastName, :NEW.Email, :NEW.Password, :NEW.RoleID, :NEW.ProfileImage, :NEW.created_at,
                     'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO Users_History (
            UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.UserID, :NEW.FirstName, :NEW.LastName, :NEW.Email, :NEW.Password, :NEW.RoleID, :NEW.ProfileImage, :NEW.created_at,
                     'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO Users_History (
            UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :OLD.UserID, :OLD.FirstName, :OLD.LastName, :OLD.Email, :OLD.Password, :OLD.RoleID, :OLD.ProfileImage, :OLD.created_at,
                     'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_course_audit
    AFTER INSERT OR UPDATE OR DELETE ON Course
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO Course_History (
            CourseID, Title, Description, Price, CreatedBy, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.CourseID, :NEW.Title, :NEW.Description, :NEW.Price, :NEW.CreatedBy, :NEW.created_at,
                     'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO Course_History (
            CourseID, Title, Description, Price, CreatedBy, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.CourseID, :NEW.Title, :NEW.Description, :NEW.Price, :NEW.CreatedBy, :NEW.created_at,
                     'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO Course_History (
            CourseID, Title, Description, Price, CreatedBy, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :OLD.CourseID, :OLD.Title, :OLD.Description, :OLD.Price, :OLD.CreatedBy, :OLD.created_at,
                     'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_orders_audit
    AFTER INSERT OR UPDATE OR DELETE ON Orders
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO Orders_History (
            OrderID, UserID, OrderDate, TotalAmount, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.OrderID, :NEW.UserID, :NEW.OrderDate, :NEW.TotalAmount, :NEW.created_at,
                     'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO Orders_History (
            OrderID, UserID, OrderDate, TotalAmount, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.OrderID, :NEW.UserID, :NEW.OrderDate, :NEW.TotalAmount, :NEW.created_at,
                     'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO Orders_History (
            OrderID, UserID, OrderDate, TotalAmount, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :OLD.OrderID, :OLD.UserID, :OLD.OrderDate, :OLD.TotalAmount, :OLD.created_at,
                     'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_role_audit
    AFTER INSERT OR UPDATE OR DELETE ON Role
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO Role_History (
            RoleID, RoleName, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.RoleID, :NEW.RoleName, :NEW.created_at, 'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO Role_History (
            RoleID, RoleName, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.RoleID, :NEW.RoleName, :NEW.created_at, 'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO Role_History (
            RoleID, RoleName, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :OLD.RoleID, :OLD.RoleName, :OLD.created_at, 'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_instructor_audit
    AFTER INSERT OR UPDATE OR DELETE ON Instructor
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO Instructor_History (
            InstructorID, UserID, Biography, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.InstructorID, :NEW.UserID, :NEW.Biography, :NEW.created_at,
                     'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO Instructor_History (
            InstructorID, UserID, Biography, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.InstructorID, :NEW.UserID, :NEW.Biography, :NEW.created_at,
                     'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO Instructor_History (
            InstructorID, UserID, Biography, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :OLD.InstructorID, :OLD.UserID, :OLD.Biography, :OLD.created_at,
                     'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_student_audit
    AFTER INSERT OR UPDATE OR DELETE ON Student
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO Student_History (
            StudentID, UserID, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.StudentID, :NEW.UserID, :NEW.created_at, 'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO Student_History (
            StudentID, UserID, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.StudentID, :NEW.UserID, :NEW.created_at, 'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO Student_History (
            StudentID, UserID, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :OLD.StudentID, :OLD.UserID, :OLD.created_at, 'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_categories_audit
    AFTER INSERT OR UPDATE OR DELETE ON categories
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO categories_History (
            id, name, parent_id, sort_order, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.id, :NEW.name, :NEW.parent_id, :NEW.sort_order, :NEW.created_at,
                     'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO categories_History (
            id, name, parent_id, sort_order, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.id, :NEW.name, :NEW.parent_id, :NEW.sort_order, :NEW.created_at,
                     'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO categories_History (
            id, name, parent_id, sort_order, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :OLD.id, :OLD.name, :OLD.parent_id, :OLD.sort_order, :OLD.created_at,
                     'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_courseinstructor_audit
    AFTER INSERT OR UPDATE OR DELETE ON CourseInstructor
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO CourseInstructor_History (
            CourseID, InstructorID, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.CourseID, :NEW.InstructorID, :NEW.created_at, 'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO CourseInstructor_History (
            CourseID, InstructorID, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.CourseID, :NEW.InstructorID, :NEW.created_at, 'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO CourseInstructor_History (
            CourseID, InstructorID, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :OLD.CourseID, :OLD.InstructorID, :OLD.created_at, 'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_coursecategory_audit
    AFTER INSERT OR UPDATE OR DELETE ON CourseCategory
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO CourseCategory_History (
            CourseID, CategoryID, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.CourseID, :NEW.CategoryID, :NEW.created_at, 'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO CourseCategory_History (
            CourseID, CategoryID, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.CourseID, :NEW.CategoryID, :NEW.created_at, 'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO CourseCategory_History (
            CourseID, CategoryID, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :OLD.CourseID, :OLD.CategoryID, :OLD.created_at, 'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_coursechapter_audit
    AFTER INSERT OR UPDATE OR DELETE ON CourseChapter
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO CourseChapter_History (
            ChapterID, CourseID, Title, Description, SortOrder, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.ChapterID, :NEW.CourseID, :NEW.Title, :NEW.Description, :NEW.SortOrder, :NEW.created_at,
                     'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO CourseChapter_History (
            ChapterID, CourseID, Title, Description, SortOrder, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.ChapterID, :NEW.CourseID, :NEW.Title, :NEW.Description, :NEW.SortOrder, :NEW.created_at,
                     'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO CourseChapter_History (
            ChapterID, CourseID, Title, Description, SortOrder, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :OLD.ChapterID, :OLD.CourseID, :OLD.Title, :OLD.Description, :OLD.SortOrder, :OLD.created_at,
                     'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_courselesson_audit
    AFTER INSERT OR UPDATE OR DELETE ON CourseLesson
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO CourseLesson_History (
            LessonID, CourseID, ChapterID, Title, Content, SortOrder, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.LessonID, :NEW.CourseID, :NEW.ChapterID, :NEW.Title, :NEW.Content, :NEW.SortOrder, :NEW.created_at,
                     'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO CourseLesson_History (
            LessonID, CourseID, ChapterID, Title, Content, SortOrder, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.LessonID, :NEW.CourseID, :NEW.ChapterID, :NEW.Title, :NEW.Content, :NEW.SortOrder, :NEW.created_at,
                     'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO CourseLesson_History (
            LessonID, CourseID, ChapterID, Title, Content, SortOrder, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :OLD.LessonID, :OLD.CourseID, :OLD.ChapterID, :OLD.Title, :OLD.Content, :OLD.SortOrder, :OLD.created_at,
                     'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_coursevideo_audit
    AFTER INSERT OR UPDATE OR DELETE ON CourseVideo
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO CourseVideo_History (
            VideoID, LessonID, Url, Title, Duration, SortOrder, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.VideoID, :NEW.LessonID, :NEW.Url, :NEW.Title, :NEW.Duration, :NEW.SortOrder, :NEW.created_at,
                     'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO CourseVideo_History (
            VideoID, LessonID, Url, Title, Duration, SortOrder, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.VideoID, :NEW.LessonID, :NEW.Url, :NEW.Title, :NEW.Duration, :NEW.SortOrder, :NEW.created_at,
                     'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO CourseVideo_History (
            VideoID, LessonID, Url, Title, Duration, SortOrder, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :OLD.VideoID, :OLD.LessonID, :OLD.Url, :OLD.Title, :OLD.Duration, :OLD.SortOrder, :OLD.created_at,
                     'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_courseresource_audit
    AFTER INSERT OR UPDATE OR DELETE ON CourseResource
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO CourseResource_History (
            ResourceID, LessonID, ResourcePath, Title, SortOrder, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.ResourceID, :NEW.LessonID, :NEW.ResourcePath, :NEW.Title, :NEW.SortOrder, :NEW.created_at,
                     'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO CourseResource_History (
            ResourceID, LessonID, ResourcePath, Title, SortOrder, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.ResourceID, :NEW.LessonID, :NEW.ResourcePath, :NEW.Title, :NEW.SortOrder, :NEW.created_at,
                     'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO CourseResource_History (
            ResourceID, LessonID, ResourcePath, Title, SortOrder, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :OLD.ResourceID, :OLD.LessonID, :OLD.ResourcePath, :OLD.Title, :OLD.SortOrder, :OLD.created_at,
                     'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_courseimage_audit
    AFTER INSERT OR UPDATE OR DELETE ON CourseImage
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO CourseImage_History (
            ImageID, CourseID, ImagePath, Caption, SortOrder, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.ImageID, :NEW.CourseID, :NEW.ImagePath, :NEW.Caption, :NEW.SortOrder, :NEW.created_at,
                     'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO CourseImage_History (
            ImageID, CourseID, ImagePath, Caption, SortOrder, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.ImageID, :NEW.CourseID, :NEW.ImagePath, :NEW.Caption, :NEW.SortOrder, :NEW.created_at,
                     'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO CourseImage_History (
            ImageID, CourseID, ImagePath, Caption, SortOrder, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :OLD.ImageID, :OLD.CourseID, :OLD.ImagePath, :OLD.Caption, :OLD.SortOrder, :OLD.created_at,
                     'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_courseobjective_audit
    AFTER INSERT OR UPDATE OR DELETE ON CourseObjective
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO CourseObjective_History (
            ObjectiveID, CourseID, Objective, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.ObjectiveID, :NEW.CourseID, :NEW.Objective, :NEW.created_at, 'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO CourseObjective_History (
            ObjectiveID, CourseID, Objective, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.ObjectiveID, :NEW.CourseID, :NEW.Objective, :NEW.created_at, 'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO CourseObjective_History (
            ObjectiveID, CourseID, Objective, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :OLD.ObjectiveID, :OLD.CourseID, :OLD.Objective, :OLD.created_at, 'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_courserequirement_audit
    AFTER INSERT OR UPDATE OR DELETE ON CourseRequirement
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO CourseRequirement_History (
            RequirementID, CourseID, Requirement, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.RequirementID, :NEW.CourseID, :NEW.Requirement, :NEW.created_at, 'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO CourseRequirement_History (
            RequirementID, CourseID, Requirement, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.RequirementID, :NEW.CourseID, :NEW.Requirement, :NEW.created_at, 'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO CourseRequirement_History (
            RequirementID, CourseID, Requirement, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :OLD.RequirementID, :OLD.CourseID, :OLD.Requirement, :OLD.created_at, 'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_cart_audit
    AFTER INSERT OR UPDATE OR DELETE ON Cart
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO Cart_History (
            CartID, UserID, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.CartID, :NEW.UserID, :NEW.created_at, 'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO Cart_History (
            CartID, UserID, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.CartID, :NEW.UserID, :NEW.created_at, 'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO Cart_History (
            CartID, UserID, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :OLD.CartID, :OLD.UserID, :OLD.created_at, 'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_cartitem_audit
    AFTER INSERT OR UPDATE OR DELETE ON CartItem
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO CartItem_History (
            CartItemID, CartID, CourseID, Quantity, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.CartItemID, :NEW.CartID, :NEW.CourseID, :NEW.Quantity, :NEW.created_at,
                     'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO CartItem_History (
            CartItemID, CartID, CourseID, Quantity, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.CartItemID, :NEW.CartID, :NEW.CourseID, :NEW.Quantity, :NEW.created_at,
                     'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO CartItem_History (
            CartItemID, CartID, CourseID, Quantity, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :OLD.CartItemID, :OLD.CartID, :OLD.CourseID, :OLD.Quantity, :OLD.created_at,
                     'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_orderdetail_audit
    AFTER INSERT OR UPDATE OR DELETE ON OrderDetail
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO OrderDetail_History (
            OrderID, CourseID, Price, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.OrderID, :NEW.CourseID, :NEW.Price, :NEW.created_at, 'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO OrderDetail_History (
            OrderID, CourseID, Price, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.OrderID, :NEW.CourseID, :NEW.Price, :NEW.created_at, 'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO OrderDetail_History (
            OrderID, CourseID, Price, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :OLD.OrderID, :OLD.CourseID, :OLD.Price, :OLD.created_at, 'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_review_audit
    AFTER INSERT OR UPDATE OR DELETE ON Review
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO Review_History (
            ReviewID, UserID, CourseID, Rating, ReviewText, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.ReviewID, :NEW.UserID, :NEW.CourseID, :NEW.Rating, :NEW.ReviewText, :NEW.created_at,
                     'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO Review_History (
            ReviewID, UserID, CourseID, Rating, ReviewText, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.ReviewID, :NEW.UserID, :NEW.CourseID, :NEW.Rating, :NEW.ReviewText, :NEW.created_at,
                     'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO Review_History (
            ReviewID, UserID, CourseID, Rating, ReviewText, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :OLD.ReviewID, :OLD.UserID, :OLD.CourseID, :OLD.Rating, :OLD.ReviewText, :OLD.created_at,
                     'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_payment_audit
    AFTER INSERT OR UPDATE OR DELETE ON Payment
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO Payment_History (
            PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.PaymentID, :NEW.OrderID, :NEW.PaymentDate, :NEW.PaymentMethod, :NEW.PaymentStatus, :NEW.Amount, :NEW.created_at,
                     'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO Payment_History (
            PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :NEW.PaymentID, :NEW.OrderID, :NEW.PaymentDate, :NEW.PaymentMethod, :NEW.PaymentStatus, :NEW.Amount, :NEW.created_at,
                     'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO Payment_History (
            PaymentID, OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount, created_at_original,
            DML_Type, DML_User
        ) VALUES (
                     :OLD.PaymentID, :OLD.OrderID, :OLD.PaymentDate, :OLD.PaymentMethod, :OLD.PaymentStatus, :OLD.Amount, :OLD.created_at,
                     'DELETE', v_dml_user
                 );
    END IF;
END;
/

CREATE OR REPLACE TRIGGER trg_password_resets_audit
    AFTER INSERT OR UPDATE OR DELETE ON password_resets
    FOR EACH ROW
DECLARE
    v_dml_user VARCHAR2(128 CHAR);
BEGIN
    v_dml_user := SYS_CONTEXT('USERENV', 'SESSION_USER');

    IF INSERTING THEN
        INSERT INTO password_resets_History (
            id, email, token, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.id, :NEW.email, :NEW.token, :NEW.created_at, 'INSERT', v_dml_user
                 );
    ELSIF UPDATING THEN
        INSERT INTO password_resets_History (
            id, email, token, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :NEW.id, :NEW.email, :NEW.token, :NEW.created_at, 'UPDATE', v_dml_user
                 );
    ELSIF DELETING THEN
        INSERT INTO password_resets_History (
            id, email, token, created_at_original, DML_Type, DML_User
        ) VALUES (
                     :OLD.id, :OLD.email, :OLD.token, :OLD.created_at, 'DELETE', v_dml_user
                 );
    END IF;
END;
/
