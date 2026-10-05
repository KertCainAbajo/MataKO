<x-admin.layout :title="'Edit '.$user->name">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="admin-card max-w-2xl space-y-4">
        @csrf @method('PUT')
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="admin-label">Full name</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" class="admin-input">
            </div>
            <div class="sm:col-span-2">
                <label for="email" class="admin-label">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="admin-input">
            </div>
            <div>
                <label for="phone" class="admin-label">Phone</label>
                <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="20" class="admin-input">
            </div>
            <div>
                <label for="age" class="admin-label">Age</label>
                <input id="age" name="age" type="number" min="18" max="120" value="{{ old('age', $user->age) }}" class="admin-input">
            </div>
            <div>
                <label for="role" class="admin-label">User type</label>
                <select id="role" name="role" class="admin-input">
                    <option value="student" @selected(old('role', $user->role) === 'student')>Student</option>
                    <option value="professional" @selected(old('role', $user->role) === 'professional')>Professional</option>
                </select>
                <p class="mt-1 text-xs text-slate-500">Decides which questionnaire and tips the user sees.</p>
            </div>
        </div>
        <label class="flex items-start gap-2 rounded-lg bg-slate-50 p-3 text-sm">
            <input type="hidden" name="is_admin" value="0">
            <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin', $user->is_admin)) class="mt-0.5 rounded border-slate-300 text-brand">
            <span><span class="font-medium">Admin access</span><br><span class="text-slate-500">Can sign in to this dashboard and change users, questions and content.</span></span>
        </label>
        <div class="flex gap-2">
            <button class="admin-btn-primary">Save changes</button>
            <a href="{{ route('admin.users.show', $user) }}" class="admin-btn-secondary">Cancel</a>
        </div>
    </form>
</x-admin.layout>
