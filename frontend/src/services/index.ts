// Services barrel export
// Central export point for all API modules

// API client (Axios instance)
export { default as api, authApi, userApi, studentApi, instructorApi, courseApi, lessonApi, chapterApi, categoryApi, instructorPublicApi, cartApi, orderApi, reviewApi, roleApi, permissionApi, adminUserApi } from './api';

// Individual validated API modules
export { categoryApi } from './categoryApi';
export { instructorPublicApi } from './instructorPublicApi';
export { lessonApi } from './lessonApi';
export { chapterApi } from './chapterApi';
export { reviewApi } from './reviewApi';
export { roleApi } from './roleApi';
export { permissionApi } from './permissionApi';
export { adminUserApi } from './adminUserApi';
