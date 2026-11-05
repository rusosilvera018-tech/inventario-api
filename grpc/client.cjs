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

const client = new inventario.ProductoService(
    '127.0.0.1:50051',
    grpc.credentials.createInsecure()
);

client.ObtenerProducto({ id: 1 }, (error, respuesta) => {
    if (error) {
        console.error('ERROR gRPC:');
        console.error('  code   :', error.code, '-', grpc.status[error.code]);
        console.error('  message:', error.message);
    } else {
        console.log('Respuesta del servidor gRPC:');
        console.log(respuesta);
    }

    client.close();
});
