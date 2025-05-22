@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="m-0">Gestión de Usuarios</h2>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ ucfirst($user->rol) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $user->status ? 'success' : 'danger' }}">
                                                    {{ $user->status ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $authUser = Auth::user();
                                                    $targetRol = $user->rol;
                                                    $canToggle = false;

                                                    if ($authUser->id !== $user->id) {
                                                        if ($authUser->rol === 'admin' && in_array($targetRol, ['vendedor', 'gerente'])) {
                                                            $canToggle = true;
                                                        } elseif ($authUser->rol === 'gerente' && $targetRol === 'vendedor') {
                                                            $canToggle = true;
                                                        }
                                                    }
                                                @endphp

                                                @if ($canToggle)
                                                    <button onclick="toggleUserStatus({{ $user->id }}, '{{ $user->name }}')"
                                                        class="btn btn-{{ $user->status ? 'danger' : 'success' }} btn-sm">
                                                        {{ $user->status ? 'Desactivar' : 'Activar' }}
                                                    </button>
                                                @else
                                                    <button class="btn btn-secondary btn-sm" disabled>
                                                        Acción no permitida
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function toggleUserStatus(userId, userName) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(`/users/${userId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'PATCH'
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar la UI
                        const row = document.querySelector(`tr:has(button[onclick*="${userId}"])`);
                        const statusBadge = row.querySelector('.badge');
                        const actionButton = row.querySelector('button[onclick]');

                        if (data.newStatus) {
                            statusBadge.className = 'badge bg-success';
                            statusBadge.textContent = 'Activo';
                            actionButton.className = 'btn btn-danger btn-sm';
                            actionButton.textContent = 'Desactivar';
                        } else {
                            statusBadge.className = 'badge bg-danger';
                            statusBadge.textContent = 'Inactivo';
                            actionButton.className = 'btn btn-success btn-sm';
                            actionButton.textContent = 'Activar';
                        }

                        // Mostrar mensaje de éxito
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ha ocurrido un error al procesar la solicitud'
                    });
                });
        }
    </script>
@endsection