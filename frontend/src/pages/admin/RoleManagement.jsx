import { useState, useEffect } from 'react';
import { roleApi, permissionApi } from '../../services/api';

export default function RoleManagement() {
  const [roles, setRoles] = useState([]);
  const [permissions, setPermissions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [editingRole, setEditingRole] = useState(null);
  const [formData, setFormData] = useState({ role_name: '' });
  const [selectedPermissions, setSelectedPermissions] = useState([]);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      const [rolesRes, permissionsRes] = await Promise.all([
        roleApi.list(),
        permissionApi.list(),
      ]);
      setRoles(rolesRes.data.data || []);
      setPermissions(permissionsRes.data.data || []);
    } catch (err) {
      setError('Failed to load data');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const openCreateModal = () => {
    setEditingRole(null);
    setFormData({ role_name: '' });
    setSelectedPermissions([]);
    setShowModal(true);
  };

  const openEditModal = async (role) => {
    setEditingRole(role);
    setFormData({ role_name: role.role_name });
    try {
      const res = await roleApi.getPermissions(role.role_id);
      const permIds = (res.data.data || []).map(p => p.permission_id);
      setSelectedPermissions(permIds);
    } catch (err) {
      setSelectedPermissions(role.permissions?.map(p => p.permission_id) || []);
    }
    setShowModal(true);
  };

  const closeModal = () => {
    setShowModal(false);
    setEditingRole(null);
    setFormData({ role_name: '' });
    setSelectedPermissions([]);
    setError('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setError('');

    try {
      if (editingRole) {
        await roleApi.update(editingRole.role_id, formData);
        await roleApi.syncPermissions(editingRole.role_id, selectedPermissions);
      } else {
        const res = await roleApi.create(formData);
        if (selectedPermissions.length > 0) {
          await roleApi.assignPermissions(res.data.data.role_id, selectedPermissions);
        }
      }
      await fetchData();
      closeModal();
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to save role');
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (role) => {
    if (!confirm(`Are you sure you want to delete the role "${role.role_name}"?`)) {
      return;
    }

    try {
      await roleApi.delete(role.role_id);
      await fetchData();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete role');
    }
  };

  const togglePermission = (permissionId) => {
    setSelectedPermissions(prev =>
      prev.includes(permissionId)
        ? prev.filter(id => id !== permissionId)
        : [...prev, permissionId]
    );
  };

  const groupedPermissions = permissions.reduce((acc, perm) => {
    const group = perm.name.split('.')[0];
    if (!acc[group]) acc[group] = [];
    acc[group].push(perm);
    return acc;
  }, {});

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  return (
    <div className="px-4 sm:px-6 lg:px-8">
      <div className="sm:flex sm:items-center">
        <div className="sm:flex-auto">
          <h1 className="text-2xl font-semibold text-gray-900">Role Management</h1>
          <p className="mt-2 text-sm text-gray-700">
            Manage roles and their permissions for the application.
          </p>
        </div>
        <div className="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
          <button
            onClick={openCreateModal}
            className="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
          >
            Create Role
          </button>
        </div>
      </div>

      {error && (
        <div className="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
          {error}
        </div>
      )}

      <div className="mt-8 flex flex-col">
        <div className="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div className="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div className="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
              <table className="min-w-full divide-y divide-gray-300">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Role ID</th>
                    <th className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Role Name</th>
                    <th className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Permissions</th>
                    <th className="relative py-3.5 pl-3 pr-4 sm:pr-6">
                      <span className="sr-only">Actions</span>
                    </th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200 bg-white">
                  {roles.map((role) => (
                    <tr key={role.role_id}>
                      <td className="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{role.role_id}</td>
                      <td className="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">{role.role_name}</td>
                      <td className="px-3 py-4 text-sm text-gray-500">
                        <div className="flex flex-wrap gap-1">
                          {(role.permissions || []).slice(0, 3).map((p) => (
                            <span key={p.permission_id} className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                              {p.name}
                            </span>
                          ))}
                          {(role.permissions || []).length > 3 && (
                            <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                              +{(role.permissions || []).length - 3} more
                            </span>
                          )}
                        </div>
                      </td>
                      <td className="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                        <button
                          onClick={() => openEditModal(role)}
                          className="text-indigo-600 hover:text-indigo-900 mr-4"
                        >
                          Edit
                        </button>
                        {!['admin', 'student', 'instructor'].includes(role.role_id) && (
                          <button
                            onClick={() => handleDelete(role)}
                            className="text-red-600 hover:text-red-900"
                          >
                            Delete
                          </button>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      {/* Modal */}
      {showModal && (
        <div className="fixed z-10 inset-0 overflow-y-auto">
          <div className="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div className="fixed inset-0 transition-opacity" onClick={closeModal}>
              <div className="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span className="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div className="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
              <form onSubmit={handleSubmit}>
                <div>
                  <h3 className="text-lg font-medium text-gray-900">
                    {editingRole ? 'Edit Role' : 'Create Role'}
                  </h3>
                  <div className="mt-4">
                    <label className="block text-sm font-medium text-gray-700">Role Name</label>
                    <input
                      type="text"
                      value={formData.role_name}
                      onChange={(e) => setFormData({ ...formData, role_name: e.target.value })}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2"
                      required
                    />
                  </div>
                  <div className="mt-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                    <div className="max-h-60 overflow-y-auto border rounded-md p-3">
                      {Object.entries(groupedPermissions).map(([group, perms]) => (
                        <div key={group} className="mb-3">
                          <h4 className="text-xs font-semibold text-gray-500 uppercase mb-1">{group}</h4>
                          <div className="space-y-1">
                            {perms.map((perm) => (
                              <label key={perm.permission_id} className="flex items-center">
                                <input
                                  type="checkbox"
                                  checked={selectedPermissions.includes(perm.permission_id)}
                                  onChange={() => togglePermission(perm.permission_id)}
                                  className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                                />
                                <span className="ml-2 text-sm text-gray-700">{perm.display_name}</span>
                              </label>
                            ))}
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
                {error && (
                  <p className="mt-2 text-sm text-red-600">{error}</p>
                )}
                <div className="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                  <button
                    type="submit"
                    disabled={saving}
                    className="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm disabled:opacity-50"
                  >
                    {saving ? 'Saving...' : 'Save'}
                  </button>
                  <button
                    type="button"
                    onClick={closeModal}
                    className="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                  >
                    Cancel
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}