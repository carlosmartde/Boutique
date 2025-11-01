<!-- Modal de Facturación -->
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceModalLabel">Generar Factura</h5>
                <button type="button" class="btn-close" id="closeModalBtn"></button>
            </div>
        <div class="modal-body">                <form id="invoiceForm">
                    <input type="hidden" id="sale_id" name="sale_id">
                    <input type="hidden" id="sale_total" name="sale_total">
            <input type="hidden" id="is_cf" name="is_cf" value="0">
                          <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label for="customer_name" class="form-label mb-0">Nombre del Cliente *</label>
                        <button type="button" id="cfButton" class="btn btn-secondary btn-sm" style="display: none;">
                            <i class="bi bi-person-fill"></i> Consumidor Final
                        </button>
                    </div>
                    <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                </div>

                <div class="mb-3">
                    <label for="customer_nit" class="form-label">NIT</label>
                    <input type="text" class="form-control" id="customer_nit" name="customer_nit">

                    <div class="mb-3">
                        <label for="customer_address" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="customer_address" name="customer_address">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_phone" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="customer_phone" name="customer_phone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Método de Pago *</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="cash">Efectivo</option>
                            <option value="card">Tarjeta</option>
                            <option value="transfer">Transferencia</option>
                        </select>
                    </div>
                </form>
            </div>            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelInvoiceBtn">Cancelar</button>
                <button type="button" class="btn btn-info" id="saveAndPrintInvoice">
                    <i class="bi bi-printer me-2"></i>Guardar e Imprimir
                </button>
                <button type="button" class="btn btn-primary" id="saveInvoice">
                    <i class="bi bi-save me-2"></i>Solo Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cfButton = document.getElementById('cfButton');
    const form = document.getElementById('invoiceForm');
    const requiredFields = ['customer_name', 'customer_nit', 'customer_address'];
    let saleTotal = 0;

    // Function to set required fields
    function setFieldsRequired(required) {
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                if (required) {
                    field.setAttribute('required', '');
                    field.classList.add('required-field');
                } else {
                    field.removeAttribute('required');
                    field.classList.remove('required-field');
                }
            }
        });
    }    // Function to fill C/F data
    function fillAsCF() {
        if (saleTotal >= 2500) {
            Swal.fire({
                title: 'No permitido',
                text: 'Para ventas de Q2,500 o más, debe ingresar los datos completos del cliente.',
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#3a86ff'
            });
            return;
        }
        document.getElementById('customer_name').value = 'Consumidor Final';
        document.getElementById('customer_nit').value = 'C/F';
        document.getElementById('customer_address').value = 'Ciudad';
        document.getElementById('customer_phone').value = 'N/A';
        document.getElementById('customer_email').value = 'N/A';
        document.getElementById('payment_method').value = 'cash';
        document.getElementById('is_cf').value = '1';
        setFieldsRequired(false);
    }// When opening the modal
    document.getElementById('invoiceModal').addEventListener('show.bs.modal', function(event) {
        form.reset();
        document.getElementById('is_cf').value = '0';
        
        // Get sale total from hidden input
        saleTotal = parseFloat(document.getElementById('sale_total').value || 0);
        
        // Always show the C/F button and set field requirements based on total
        cfButton.style.display = 'block';
        setFieldsRequired(saleTotal >= 2500);
    });

    // C/F button click handler
    cfButton.addEventListener('click', fillAsCF);

    // Manejador para el botón de cerrar y cancelar
    function handleCloseAttempt() {
        Swal.fire({
            title: '¿Está seguro?',
            text: 'Si cancela la facturación, se anulará la venta. ¿Desea continuar?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, anular venta',
            cancelButtonText: 'No, continuar'
        }).then((result) => {
            if (result.isConfirmed) {
                const saleId = document.getElementById('sale_id').value;
                
                // Mostrar indicador de carga
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Anulando la venta',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Hacer la petición para anular la venta
                fetch(`/sales/${saleId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Venta Anulada',
                            text: 'La venta ha sido anulada correctamente',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Cerrar el modal y refrescar la página actual
                            const modal = bootstrap.Modal.getInstance(document.getElementById('invoiceModal'));
                            modal.hide();
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Error al anular la venta');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error',
                        text: error.message || 'Error al anular la venta',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            }
        });
    }

    // Event listeners para los botones de cerrar y cancelar
    document.getElementById('closeModalBtn').addEventListener('click', handleCloseAttempt);
    document.getElementById('cancelInvoiceBtn').addEventListener('click', handleCloseAttempt);

    // Form validation before submission
    form.addEventListener('submit', function(event) {
        if (saleTotal >= 2500) {
            const emptyFields = requiredFields.filter(fieldId => {
                const field = document.getElementById(fieldId);
                return field && !field.value.trim();
            });

            if (emptyFields.length > 0) {
                event.preventDefault();
                Swal.fire({
                    title: 'Datos requeridos',
                    text: 'Para ventas de Q2,500 o más, todos los datos del cliente son obligatorios.',
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#3a86ff'
                });
            }
        }
    });
});</script>
