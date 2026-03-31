import { Outlet } from 'react-router-dom';

export default function AdminLayout() {
  return (
    <div className="min-h-screen flex bg-gray-100">
      {/* Admin Sidebar */}
      <aside className="w-64 bg-gray-900 text-white fixed h-full overflow-y-auto">
        <div className="p-4">
          <h1 className="text-xl font-bold">Admin Panel</h1>
        </div>
        <nav className="mt-4">
          <a href="/admin/dashboard" className="block px-4 py-2 hover:bg-gray-800">
            Dashboard
          </a>
          <a href="/admin/courses" className="block px-4 py-2 hover:bg-gray-800">
            Courses
          </a>
          <a href="/admin/users" className="block px-4 py-2 hover:bg-gray-800">
            Users
          </a>
          <a href="/admin/instructors" className="block px-4 py-2 hover:bg-gray-800">
            Instructors
          </a>
          <a href="/admin/orders" className="block px-4 py-2 hover:bg-gray-800">
            Orders
          </a>
          <a href="/admin/reviews" className="block px-4 py-2 hover:bg-gray-800">
            Reviews
          </a>
        </nav>
      </aside>

      {/* Main content */}
      <div className="ml-64 flex-1 flex flex-col">
        <header className="bg-white shadow">
          <div className="px-4 py-4">
            <h2 className="text-lg font-semibold text-gray-800">Administration</h2>
          </div>
        </header>
        <main className="flex-1 p-6 overflow-y-auto">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
