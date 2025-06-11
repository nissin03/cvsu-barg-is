@forelse ($facilities as $facility)
    <tr>
        <td>{{ $facility->id }}</td>
        <td class="pname">
            <div class="image">
                @if ($facility->image && File::exists(storage_path('app/public/' . $facility->image)))
                    <img src="{{ asset('storage/' . $facility->image) }}" alt="{{ $facility->name }}" class="image">
                @else
                    <img src="{{ asset('images/upload/upload-1.png') }}" alt="No Image" class="image">
                @endif
            </div>
            <div class="name">
                <a href="#" class="body-title-2 facility-name"
                    data-id="{{ $facility->id }}">{{ $facility->name }}</a>
                <div class="tooltip" id="tooltip-{{ $facility->id }}">
                    <strong>Rules:</strong>
                    <span class="badge {{ $facility->rules_and_regulations ? 'badge-success' : 'badge-danger' }}">
                        {{ $facility->rules_and_regulations ? 'Available' : 'N/A' }}
                    </span>
                    <br>
                    <strong>Requirements:</strong>
                    <span class="badge {{ $facility->requirements ? 'badge-success' : 'badge-danger' }}">
                        {{ $facility->requirements ? 'Available' : 'N/A' }}
                    </span>
                </div>
                <span class="badge {{ ucfirst($facility->status) === '1' ? 'badge-success' : 'badge-danger' }}">
                    {{ ucfirst($facility->status) }}
                </span>
            </div>
        </td>

        <td>{{ $facility->facility_type ? $facility->facility_type : 'N/A' }}</td>
        <td>{{ $facility->description ? $facility->description : 'N/A' }}</td>

        <td>
            <div class="list-icon-function">
                <a href="{{ route('admin.facilities.edit', ['id' => $facility->id]) }}">
                    <div class="item edit">
                        <i class="icon-edit-3"></i>
                    </div>
                </a>

                <form action="{{ route('admin.facilities.archive', ['id' => $facility->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="item text-warning archive">
                        <i class="icon-archive"></i>
                    </div>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center">No facilities found.</td>
    </tr>
@endforelse
