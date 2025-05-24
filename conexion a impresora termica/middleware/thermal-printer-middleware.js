// thermal-printer-middleware.js

/**
 * MIDDLEWARE PARA IMPRESORAS TÉRMICAS
 * 
 * Este middleware permite conectar aplicaciones con impresoras térmicas mediante WiFi.
 * Recibe datos JSON y los transforma en tickets impresos con formato personalizable.
 */

// Importación de dependencias
const express = require('express');               // Framework para crear API REST
const bodyParser = require('body-parser');        // Middleware para parsear el cuerpo de las peticiones
const escpos = require('escpos');                 // Biblioteca para control de impresoras térmicas
const cors = require('cors');                     // Middleware para habilitar CORS

// Registrar adaptador de red para impresoras térmicas WiFi
escpos.Network = require('escpos-network');

// Configuración básica de Express
const app = express();
app.use(cors());                                  // Habilitar CORS para permitir peticiones de otros dominios
const PORT = process.env.PORT || 3000;            // Puerto configurable mediante variable de entorno

// Configuración para parsear JSON en las peticiones
app.use(bodyParser.json());

/**
 * FUNCIÓN PARA CREAR PLANTILLA DE TICKET
 * 
 * Esta función procesa los datos recibidos y estructura el contenido del ticket
 * en tres secciones: encabezado, cuerpo y pie.
 * 
 * @param {Object} datos - Objeto JSON con la información para el ticket
 * @returns {Object} Objeto con las tres secciones del ticket estructuradas
 */
const crearPlantillaTicket = (datos) => {
    try {
        // Extraer datos relevantes del JSON con valores por defecto
        const { 
            nombreNegocio = 'Mi Negocio', 
            direccion = 'Dirección del Negocio',
            telefono = 'Tel: 123456789',
            rfc = 'RFC: XXX0000000XX',
            numeroTicket = '00001',
            fecha = new Date().toLocaleDateString(),
            hora = new Date().toLocaleTimeString(),
            cajero = 'Cajero: Admin',
            productos = [],
            subtotal = 0,
            impuestos = 0,
            total = 0,
            metodoPago = 'Efectivo',
            mensajePie = '¡Gracias por su compra!' 
        } = datos;

        // Calcular totales si no vienen en el JSON
        let calculoSubtotal = 0;
        if (productos.length > 0 && subtotal === 0) {
            calculoSubtotal = productos.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
        }

        const subtotalFinal = subtotal || calculoSubtotal;
        const impuestosFinal = impuestos || (subtotalFinal * 0.16); // 16% por defecto
        const totalFinal = total || (subtotalFinal + impuestosFinal);

        // Crear el contenido del ticket
        let ticketContent = {
            encabezado: [
                nombreNegocio,
                direccion,
                telefono,
                rfc,
                `Ticket #: ${numeroTicket}`,
                `Fecha: ${fecha}`,
                `Hora: ${hora}`,
                cajero,
                '-'.repeat(48)
            ],
            cuerpo: productos.map(item => ({
                nombre: item.nombre,
                cantidad: item.cantidad,
                precioUnitario: item.precio,
                importe: item.precio * item.cantidad
            })),
            pie: [
                '-'.repeat(48),
                `SUBTOTAL: ${subtotalFinal.toFixed(2)}`,
                `IMPUESTOS: ${impuestosFinal.toFixed(2)}`,
                `TOTAL: ${totalFinal.toFixed(2)}`,
                `Método de pago: ${metodoPago}`,
                '-'.repeat(48),
                mensajePie
            ]
        };

        return ticketContent;
    } catch (error) {
        console.error('Error al crear plantilla:', error);
        throw new Error('Error al procesar los datos para la plantilla del ticket');
    }
};

/**
 * ENDPOINT PARA IMPRIMIR TICKET
 * 
 * Recibe los datos del ticket en formato JSON y los envía a la impresora.
 */
app.post('/imprimir', async (req, res) => {
    try {
        const datos = req.body;
        
        // Validar que haya datos
        if (!datos) {
            return res.status(400).json({ error: 'No se recibieron datos para el ticket' });
        }

        // Crear plantilla de ticket
        const plantillaTicket = crearPlantillaTicket(datos);
        
        // Configuración de la impresora
        const printerConfig = {
            address: datos.impresora?.ip || '192.168.1.200', // IP por defecto
            port: datos.impresora?.puerto || 9100            // Puerto por defecto
        };

        // Imprimir el ticket
        await imprimirTicket(printerConfig, plantillaTicket);
        
        res.status(200).json({ 
            success: true, 
            message: 'Ticket enviado a la impresora' 
        });
    } catch (error) {
        console.error('Error al procesar la solicitud:', error);
        res.status(500).json({ 
            success: false, 
            error: error.message || 'Error al procesar la solicitud' 
        });
    }
});

/**
 * FUNCIÓN PARA IMPRIMIR EL TICKET
 * 
 * Esta función maneja la conexión con la impresora y el formateo del ticket.
 * Aquí se pueden personalizar aspectos visuales del ticket.
 * 
 * @param {Object} printerConfig - Configuración de conexión a la impresora
 * @param {Object} plantillaTicket - Objeto con el contenido estructurado del ticket
 * @returns {Promise} Promesa que se resuelve cuando el ticket se imprime
 */
async function imprimirTicket(printerConfig, plantillaTicket) {
    return new Promise((resolve, reject) => {
        try {
            // Crear conexión con la impresora
            const device = new escpos.Network(printerConfig.address, printerConfig.port);
            
            // Crear impresora
            const printer = new escpos.Printer(device);
            
            // Abrir conexión
            device.open(function(err) {
                if (err) {
                    return reject(new Error(`Error al conectar con la impresora: ${err.message}`));
                }
                
                // ENCABEZADO: Configuración y formato
                printer.font('a')                // Tipo de fuente (a = normal, b = comprimida)
                       .align('ct')              // Alineación (ct = centro, lt = izquierda, rt = derecha)
                       .style('b');              // Estilo (b = negrita)
                
                // Imprimir cada línea del encabezado
                plantillaTicket.encabezado.forEach(linea => {
                    printer.text(linea);         // Cada text() genera una nueva línea
                });
                
                // Resetear estilo para el cuerpo
                printer.align('lt')              // Alinear a la izquierda para la lista de productos
                       .style('normal');         // Quitar negrita
                
                // CUERPO: Tabla de productos (CORREGIDO)
                // Encabezados de la tabla con ancho porcentual para cada columna
                printer.tableCustom([
                    { text: 'PRODUCTO', width: 0.45, align: 'LEFT' },   // Nombre del producto (45% del ancho)
                    { text: 'CANT', width: 0.15, align: 'RIGHT' },      // Cantidad (15% del ancho)
                    { text: 'PRECIO', width: 0.2, align: 'RIGHT' },     // Precio unitario (20% del ancho)
                    { text: 'TOTAL', width: 0.2, align: 'RIGHT' }       // Importe total (20% del ancho)
                ]);
                
                // Imprimir cada producto con la misma estructura de columnas
                plantillaTicket.cuerpo.forEach(producto => {
                    printer.tableCustom([
                        { text: producto.nombre, width: 0.45, align: 'LEFT' },
                        { text: producto.cantidad.toString(), width: 0.15, align: 'RIGHT' },
                        { text: producto.precioUnitario.toFixed(2), width: 0.2, align: 'RIGHT' },
                        { text: producto.importe.toFixed(2), width: 0.2, align: 'RIGHT' }
                    ]);
                });
                
                // PIE: Totales y mensajes finales
                printer.align('rt');             // Alinear a la derecha para los totales
                
                // Imprimir cada línea del pie
                plantillaTicket.pie.forEach((linea, index) => {
                    if (index === 2) {           // Para la línea del TOTAL
                        printer.style('b')       // Negrita
                               .size(1, 1);      // Tamaño aumentado (1,1 = normal*2)
                    }
                    
                    printer.text(linea);
                    
                    if (index === 2) {           // Después del TOTAL, volver a normal
                        printer.style('normal')
                               .size(0, 0);      // Tamaño normal (0,0)
                    }
                });
                
                // Espacio final
                printer.align('ct')
                       .text('');
                
                // Cortar papel y cerrar conexión
                printer.cut()                   // Corta el papel
                       .close();                // Cierra la conexión
                
                resolve();
            });
        } catch (error) {
            reject(new Error(`Error al imprimir ticket: ${error.message}`));
        }
    });
}

/**
 * ENDPOINT PARA VERIFICAR ESTADO
 * 
 * Permite verificar si el servidor está en funcionamiento.
 */
app.get('/estado', (req, res) => {
    res.status(200).json({
        status: 'online',
        message: 'Middleware de impresora térmica funcionando correctamente'
    });
});

// Iniciar servidor
app.listen(PORT, () => {
    console.log(`Middleware de impresora térmica ejecutándose en el puerto ${PORT}`);
});

module.exports = app;