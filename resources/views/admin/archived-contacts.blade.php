@extends('layouts.admin')

@section('content')
    <div class="main-content-inner">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <h3>Archived Messages</h3>

            <ul class="breadcrumbs d-flex flex-wrap gap-2">
                <li>
                    <a href="{{ route('admin.index') }}">
                        <div class="text-muted">Dashboard</div>
                    </a>
                </li>
                <li><i class="icon-chevron-right"></i></li>
                <li>
                    <div class="text-muted">Contacts</div>
                </li>
            </ul>
        </div>
        @if (Session::has('status'))
            <div class="alert alert-success">{{ Session::get('status') }}</div>
        @endif

        <div class="row g-4">
            @forelse ($contacts as $contact)
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5>{{ $contact->name }}</h5>
                            <p>{{ Str::limit($contact->message, 100) }}</p>
                            <p><small>Archived on {{ $contact->deleted_at->format('M d, Y') }}</small></p>
                        </div>
                        <div class="card-footer">
                            <form action="{{ route('admin.contact.restore', $contact->id) }}" method="POST">
                                @csrf
                                <button class="btn btn-success btn-sm">Restore</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p>No archived contacts found.</p>
            @endforelse
        </div>

        {{ $contacts->links('pagination::bootstrap-5') }}
    </div>
@endsection
