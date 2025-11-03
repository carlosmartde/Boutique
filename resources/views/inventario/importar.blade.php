@extends('layouts.app')

@section('title', 'Importar Productos desde Excel')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-excel me-2"></i>Importar Productos desde Excel
                    </h5>
                    <a href="{{ route('inventario.mostrar-formulario') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Área de mensajes -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div> 
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Formulario de upload -->
                    <form action="{{ route(name: 'inventario.importar') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        
                        <!-- Área de drag and drop -->
                        <div class="drop-zone" id="dropZone">
                            <div class="drop-zone-content">
                                <i class="bi bi-cloud-arrow-up display-1 text-muted mb-3"></i>
                                <h4 class="text-muted mb-3">Arrastra tu archivo Excel aquí</h4>
                                <p class="text-muted mb-3">o</p>
                                <button type="button" class="btn btn-primary" id="selectFileBtn">
                                    <i class="bi bi-file-plus me-2"></i>Seleccionar archivo
                                </button>
                                <input type="file" name="excel_file" id="excelFile" accept=".xlsx,.xls" style="display: none;" required>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Solo archivos Excel (.xlsx, .xls) - Tamaño máximo: 10MB
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Información del archivo seleccionado -->
                        <div class="file-info mt-3" id="fileInfo" style="display: none;">
                            <div class="alert alert-info">
                                <i class="bi bi-file-earmark-excel me-2"></i>
                                <strong>Archivo seleccionado:</strong> <span id="fileName"></span>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="removeFile">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Botón de envío -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                                <i class="bi bi-upload me-2"></i>Importar Productos
                            </button>
                        </div>
                    </form>

                    <!-- Loading spinner -->
                    <div class="text-center mt-4" id="loadingSpinner" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Procesando...</span>
                        </div>
                        <p class="mt-2 text-muted">Procesando archivo Excel...</p>
                    </div>

                    <!-- Instrucciones -->
                    <div class="mt-4">
                        <h6><i class="bi bi-info-circle me-2"></i>Instrucciones:</h6>
                        <ul class="text-muted">
                            <li>El archivo debe contener las columnas: <strong>nombre, codigo, precio_compra, precio_venta, cantidad</strong></li>
                            <li>La primera fila debe contener los encabezados</li>
                            <li>Los precios deben ser números válidos</li>
                            <li>La cantidad debe ser un número entero</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.drop-zone {
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 60px 20px;
    text-align: center;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
}

.drop-zone:hover {
    border-color: #007bff;
    background-color: #e3f2fd;
}

.drop-zone.drag-over {
    border-color: #28a745;
    background-color: #d4edda;
    transform: scale(1.02);
}

.drop-zone.drag-invalid {
    border-color: #dc3545;
    background-color: #f8d7da;
}

.drop-zone-content {
    pointer-events: none;
}

#selectFileBtn {
    pointer-events: auto;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('excelFile');
    const selectFileBtn = document.getElementById('selectFileBtn');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const removeFileBtn = document.getElementById('removeFile');
    const submitBtn = document.getElementById('submitBtn');
    const uploadForm = document.getElementById('uploadForm');
    const loadingSpinner = document.getElementById('loadingSpinner');

    // Eventos de drag and drop
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('drag-over', 'drag-invalid');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('drag-over', 'drag-invalid');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            if (validateFile(file)) {
                fileInput.files = files;
                showFileInfo(file);
            } else {
                dropZone.classList.add('drag-invalid');
                setTimeout(() => {
                    dropZone.classList.remove('drag-invalid');
                }, 1000);
            }
        }
    });

    // Evento click en el área de drop
    dropZone.addEventListener('click', function(e) {
        if (e.target !== selectFileBtn) {
            fileInput.click();
        }
    });

    // Evento click en el botón de seleccionar archivo
    selectFileBtn.addEventListener('click', function() {
        fileInput.click();
    });

    // Evento change del input file
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file && validateFile(file)) {
            showFileInfo(file);
        }
    });

    // Evento para remover archivo
    removeFileBtn.addEventListener('click', function() {
        fileInput.value = '';
        fileInfo.style.display = 'none';
        submitBtn.disabled = true;
    });

    // Evento submit del formulario
    uploadForm.addEventListener('submit', function() {
        submitBtn.disabled = true;
        loadingSpinner.style.display = 'block';
        dropZone.style.display = 'none';
        fileInfo.style.display = 'none';
    });

    // Función para validar archivo
    function validateFile(file) {
        const allowedTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
            'application/vnd.ms-excel' // .xls
        ];
        
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        if (!allowedTypes.includes(file.type)) {
            alert('Por favor selecciona un archivo Excel válido (.xlsx o .xls)');
            return false;
        }
        
        if (file.size > maxSize) {
            alert('El archivo es demasiado grande. Tamaño máximo: 10MB');
            return false;
        }
        
        return true;
    }

    // Función para mostrar información del archivo
    function showFileInfo(file) {
        fileName.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
        fileInfo.style.display = 'block';
        submitBtn.disabled = false;
    }

    // Función para formatear el tamaño del archivo
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
</script>
@endsection