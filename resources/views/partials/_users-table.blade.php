@forelse ($users as $user)
    <tr class="user-row cursor-pointer" data-href="{{ route('admin.users.edit', $user->id) }}">
        <td class="text-center">
            <div class="name-cell">
                <div class="name-text" title="{{ $user->name }}">
                    {{ Str::limit($user->name, 25) }}
                </div>
            </div>
        </td>
        <td class="text-center">
            <div class="text-truncate" style="max-width: 200px;" title="{{ $user->email }}">
                {{ $user->email }}
            </div>
        </td>
        <td class="text-center">
            {{ $user->phone_number ?? 'Not Provided' }}
        </td>
        <td class="text-center">
            <span class="badge bg-info text-white">
                {{ $user->year_level ?? 'N/A' }}
            </span>
        </td>
        <td class="text-center">
            <span class="badge bg-primary text-white">
                {{ $user->college->code ?? 'N/A' }}
            </span>
        </td>
        <td class="text-center">
            <div class="text-truncate" style="max-width: 120px;" title="{{ $user->course->code ?? 'N/A' }}">
                <span class="badge bg-success text-white">
                    {{ $user->course->code ?? 'N/A' }}
                </span>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="table-empty">
            <div class="text-center py-4">
                <i class="icon-users" style="font-size: 3rem; color: #dee2e6; margin-bottom: 1rem;"></i>
                <h5 class="text-muted">No users found</h5>
                <p class="text-muted mb-0">Try adjusting your search criteria or filters</p>
            </div>
        </td>
    </tr>
@endforelse
