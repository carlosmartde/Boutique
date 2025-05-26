@extends('layouts.app')

@section('title', 'Nueva Venta')

@section('styles')
<style>
        .sale-card {
            border-radius: 12px;
            overflow: hidden;
        }

        .sale-header {
            background: linear-gradient(135deg, var(--dark-primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem;
            border-radius: 10px 10px 0 0;
        }

        .input-scan-wrapper {
            position: relative;
            margin-bottom: 2rem;
        }

        .input-scan-wrapper::before {
            content: '\F4E6';
            /* Bootstrap icon for scan */
            font-family: "bootstrap-icons";
            position: absolute;
            left: 15px;
            top: 10px;
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        #product_code {
            padding-left: 45px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            height: 50px;
            transition: all 0.3s ease;
        }

        #product_code:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(58, 134, 255, 0.25);
        }

        #sales-table {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
        }

        #sales-table thead th {
            background-color: rgba(58, 134, 255, 0.1);
            color: var(--dark-color);
            font-weight: 600;
            border: none;
            padding: 12px 15px;
        }

        #sales-table tbody td {
            vertical-align: middle;
            padding: 12px 15px;
            border-color: #f0f2f5;
        }

        .quantity {
            border-radius: 6px;
            border: 1px solid #e0e0e0;
        }

        .btn-action {
            border-radius: 50px;
            padding: 0.4rem 1rem;
            transition: all 0.3s;
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        .remove-product {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .remove-product:hover {
            background-color: #e0005c;
            border-color: #e0005c;
        }

        #complete-sale {
            padding: 0.7rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(58, 134, 255, 0.3);
            transition: all 0.3s;
            background: linear-gradient(135deg, var(--dark-primary-color), var(--secondary-color));
            border: none;
            color: black;
        }

        #complete-sale:hover {
            transform: translateY(-3px);
            background: var(--hover-color-button);
            box-shadow: 0 6px 15px rgba(58, 134, 255, 0.4);
            color: black;
        }

        /* Animación para productos recién agregados */
        @keyframes highlightRow {
            0% {
                background-color: rgba(58, 134, 255, 0.2);
            }

            100% {
                background-color: transparent;
            }
        }

        .highlight-row {
            animation: highlightRow 1.5s ease;
        }

        /* Total section styling */
        tfoot tr td {
            background-color: #f8f9fa;
            font-size: 1.1rem;
        }

        #total {
            color: var(--primary-color);
            font-size: 1.2rem;
        }


        .custom-select-wrapper {
            position: relative;
            width: 100%;
        }
    </style>
@endsection

@section('content')
<div class="card sale-card">
    <div class="sale-header">
        <h4 class="mb-0"><i class="bi bi-cart-plus me-2"></i>Registrar Nueva Venta</h4>
    </div>
    <div class="card-body p-4">
        <div class="input-scan-wrapper">
            <label for="product_code" class="form-label fw-bold mb-2">Escanear o ingresar código de producto</label>
            <input type="text" class="form-control form-control-lg" id="product_code" placeholder="Escanee o ingrese el código y presione Enter" autofocus>
        </div>
        
        <div class="table-responsive mb-4">
            <table class="table" id="sales-table">
                <thead>
                    <tr>
                        <th width="15%"><i class="bi bi-upc me-2"></i>Código</th>
                        <th width="25%"><i class="bi bi-box me-2"></i>Producto</th>
                        <th width="15%"><i class="bi bi-tag me-2"></i>Precio</th>
                        <th width="15%"><i class="bi bi-123 me-2"></i>Cantidad</th>
                        <th width="15%"><i class="bi bi-calculator me-2"></i>Subtotal</th>
                        <th width="15%"><i class="bi bi-gear me-2"></i>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Los productos se agregarán aquí dinámicamente -->
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end fw-bold">Total a pagar:</td>
                        <td class="fw-bold" id="total">Q0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver al Dashboard
            </a>
            <button type="button" class="btn btn-primary" id="complete-sale">
                <i class="bi bi-check-circle me-2"></i>Finalizar Venta
            </button>
        </div>
    </div>
</div>

<template id="product-row-template">
    <tr>
        <td class="product-code"></td>
        <td class="product-name fw-medium"></td>
        <td class="product-price"></td>
        <td>
            <input type="number" class="form-control quantity" min="1" value="1">
        </td>
        <td class="subtotal fw-bold"></td>
        <td>
            <button type="button" class="btn btn-danger btn-sm btn-action remove-product">
                <i class="bi bi-trash me-1"></i>Eliminar
            </button>
        </td>
    </tr>
</template>
@endsection

@section('scripts')
<script>
        function sendSaleToMiddleware(saleData) {
            const payload = {
                productos: saleData.products.map(product => ({
                    nombre: product.name,
                    cantidad: product.quantity,
                    precio: product.price
                })),
                impresora: {
                    ip: "192.168.1.200",
                    puerto: 9100
                }
            };
            console.log("Datos a enviar al middleware:", payload);



            fetch('http://localhost:3000/imprimir', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Respuesta del middleware:', data);
                })
                .catch(error => {
                    console.error('Error al enviar los datos al middleware:', error);
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const productCodeInput = document.getElementById('product_code');
            const salesTable = document.getElementById('sales-table').getElementsByTagName('tbody')[0];
            const completeSaleBtn = document.getElementById('complete-sale');
            const totalElement = document.getElementById('total');
            let products = [];

            // Escanear código de producto
            productCodeInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const code = this.value.trim();
                    console.log("Código ingresado:", code);
                    if (code) {
                        fetchProductDetails(code);
                        this.value = ''; // Limpia el input
                    }
                }
            });

            // Obtener detalles del producto
            function fetchProductDetails(code) {
                fetch(`/product/code/${code}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log("Respuesta del backend:", data);
                        if (data.error) {
                            Swal.fire({
                                title: 'Error',
                                text: data.error,
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3a86ff'
                            });
                            return;
                        }
                        if (data) {
                            addProductToTable(data);
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'Producto no encontrado',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3a86ff'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Error al buscar el producto. Intente nuevamente.',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3a86ff'
                        });
                    });
            }

            // Agregar producto a la tabla
            function addProductToTable(product) {
                // Convertir sale_price a número
                const salePrice = Number(product.sale_price);

                if (isNaN(salePrice)) {
                    console.error("Error: El precio del producto no es un número válido", product.sale_price);
                    Swal.fire({
                        title: 'Error',
                        text: 'El precio del producto no es válido.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3a86ff'
                    });
                    return;
                }

                // Verificar si el producto ya está en la tabla
                const existingProduct = products.find(p => p.id === product.id);
                if (existingProduct) {
                    // Incrementar cantidad
                    const row = document.querySelector(`tr[data-product-id="${product.id}"]`);
                    const quantityInput = row.querySelector('.quantity');
                    quantityInput.value = parseInt(quantityInput.value) + 1;
                    // Resaltar la fila cuando se incrementa
                    row.classList.add('highlight-row');
                    // Eliminar la clase después de la animación
                    setTimeout(() => {
                        row.classList.remove('highlight-row');
                    }, 1500);
                    updateSubtotal(row);
                } else {
                    // Agregar nuevo producto
                    const template = document.getElementById('product-row-template');
                    const clone = document.importNode(template.content, true);
                    const row = clone.querySelector('tr');

                    row.setAttribute('data-product-id', product.id);
                    row.querySelector('.product-code').textContent = product.code;
                    row.querySelector('.product-name').textContent = product.name;
                    row.querySelector('.product-price').textContent = `Q${salePrice.toFixed(2)}`;

                    const quantityInput = row.querySelector('.quantity');
                    quantityInput.addEventListener('change', function () {
                        updateSubtotal(row);
                    });

                    row.querySelector('.subtotal').textContent = `Q${salePrice.toFixed(2)}`;

                    const removeButton = row.querySelector('.remove-product');
                    removeButton.addEventListener('click', function () {
                        // Efecto de desvanecimiento antes de eliminar
                        row.style.transition = 'opacity 0.3s ease';
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                            products = products.filter(p => p.id !== product.id);
                            updateTotal();
                        }, 300);
                    });

                    row.classList.add('highlight-row');
                    salesTable.appendChild(row);

                    // Agregar a la lista de productos
                    products.push({
                        id: product.id,
                        code: product.code,
                        name: product.name,
                        price: salePrice,
                        quantity: 1
                    });
                }

                updateTotal();
            }

            // Actualizar subtotal de un producto
            function updateSubtotal(row) {
                const productId = parseInt(row.getAttribute('data-product-id'));
                const product = products.find(p => p.id === productId);
                const quantityInput = row.querySelector('.quantity');
                const quantity = parseInt(quantityInput.value);

                if (quantity < 1) {
                    quantityInput.value = 1;
                    return updateSubtotal(row);
                }

                const subtotal = product.price * quantity;
                row.querySelector('.subtotal').textContent = `Q${subtotal.toFixed(2)}`;

                // Actualizar cantidad en el array de productos
                product.quantity = quantity;

                updateTotal();
            }

            // Actualizar total
            function updateTotal() {
                const total = products.reduce((sum, product) => {
                    return sum + (product.price * product.quantity);
                }, 0);

                // Animación del cambio de total
                const oldTotal = parseFloat(totalElement.textContent.replace('Q', ''));
                if (oldTotal !== total) {
                    totalElement.style.transition = 'color 0.3s ease';
                    totalElement.style.color = '#ff006e';
                    setTimeout(() => {
                        totalElement.style.color = '#3a86ff';
                    }, 500);
                }

                totalElement.textContent = `Q${total.toFixed(2)}`;
            }

            // Finalizar venta
            completeSaleBtn.addEventListener('click', function () {
                if (products.length === 0) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Debe agregar al menos un producto a la venta',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3a86ff'
                    });
                    return;
                }

                Swal.fire({
                    title: '¿Está seguro?',
                    text: 'Esta acción finalizará la venta actual',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3a86ff',
                    cancelButtonColor: '#ff006e',
                    confirmButtonText: 'Sí, finalizar venta',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const total = products.reduce((sum, product) => {
                            return sum + (product.price * product.quantity);
                        }, 0);

                        const saleData = {
                            products: products.map(product => ({
                                name: product.name,
                                id: product.id,
                                quantity: product.quantity,
                                price: product.price,
                                subtotal: product.price * product.quantity
                            })),
                            total: total
                        };

                        // Mostrar indicador de carga
                        Swal.fire({
                            title: 'Procesando...',
                            text: 'Registrando la venta',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        fetch('/sales', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(saleData)
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Llamada al middleware para imprimir
                                    sendSaleToMiddleware(saleData);

                                    Swal.fire({
                                        title: '¡Venta exitosa!',
                                        text: 'La venta ha sido registrada correctamente',
                                        icon: 'success',
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: '#3a86ff',
                                        timer: 2000,
                                        timerProgressBar: true,
                                    }).then(() => {
                                        // Limpiar tabla
                                        salesTable.innerHTML = '';
                                        products = [];
                                        updateTotal();
                                        productCodeInput.focus();

                                        // Recargar la página después de finalizar la venta
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Error al registrar la venta: ' + data.message,
                                        icon: 'error',
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: '#3a86ff'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Error al registrar la venta. Intente nuevamente.',
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    confirmButtonColor: '#3a86ff'
                                });
                            });
                    }
                });
            });
        });
    </script>
@endsection