const path = require('path');
const grpc = require('@grpc/grpc-js');
const protoLoader = require('@grpc/proto-loader');

const packageDefinition = protoLoader.loadSync(path.join(__dirname, 'producto.proto'), {
    keepCase: false,
    longs: String,
    enums: String,
    defaults: true,
    oneofs: true,
});

const inventario = grpc.loadPackageDefinition(packageDefinition).inventario;

const API_BASE = 'http://127.0.0.1:8000/api/interno/productos';
const KEY = process.env.GRPC_INTERNAL_KEY;

async function obtenerProducto(call, callback) {
    const id = call.request.id;

    try {
        const respuesta = await fetch(`${API_BASE}/${id}`, {
            headers: {
                'X-Internal-Key': KEY || '',
                'Accept': 'application/json',
            },
        });

        if (respuesta.status === 404) {
            return callback({
                code: grpc.status.NOT_FOUND,
                message: `Producto con id ${id} no encontrado`,
            });
        }

        if (!respuesta.ok) {
            return callback({
                code: grpc.status.INTERNAL,
                message: `La API respondio con codigo HTTP ${respuesta.status}`,
            });
        }

        const producto = await respuesta.json();

        return callback(null, {
            id: producto.id,
            nombre: producto.nombre,
            precio: Number(producto.precio),
            stock: producto.stock,
        });
    } catch (error) {
        return callback({
            code: grpc.status.UNAVAILABLE,
            message: `No se pudo conectar con la API: ${error.message}`,
        });
    }
}

function main() {
    const server = new grpc.Server();

    server.addService(inventario.ProductoService.service, {
        ObtenerProducto: obtenerProducto,
    });

    const direccion = '127.0.0.1:50051';

    server.bindAsync(direccion, grpc.ServerCredentials.createInsecure(), (error, puerto) => {
        if (error) {
            console.error('Error al iniciar el servidor gRPC:', error);
            return;
        }
        console.log(`Servidor gRPC escuchando en ${direccion} (puerto ${puerto})`);
        console.log(`Clave interna cargada: ${KEY ? 'SI (' + KEY + ')' : 'NO (GRPC_INTERNAL_KEY vacia)'}`);
    });
}

main();
