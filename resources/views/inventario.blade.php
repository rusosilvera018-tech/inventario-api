<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventario - Tiempo real</title>
    @vite(['resources/js/app.js'])
</head>
<body>
    <h1>Inventario (WebSockets / Reverb)</h1>

    <button id="recargar">Recargar tabla</button>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody id="tabla-productos">
        </tbody>
    </table>

    <h2>Logs de eventos broadcast</h2>
    <pre id="logs" style="background:#111;color:#0f0;padding:10px;min-height:120px;max-height:300px;overflow:auto;"></pre>

    <script>
        const tbody = document.getElementById('tabla-productos');
        const logs = document.getElementById('logs');

        function agregarLog(texto) {
            const linea = document.createElement('div');
            linea.textContent = new Date().toLocaleTimeString() + ' - ' + texto;
            logs.appendChild(linea);
            logs.scrollTop = logs.scrollHeight;
        }

        async function recargar() {
            const respuesta = await fetch('/api/productos');
            const productos = await respuesta.json();
            tbody.textContent = '';

            productos.forEach(producto => {
                const fila = document.createElement('tr');

                const celdaId = document.createElement('td');
                celdaId.textContent = producto.id;

                const celdaNombre = document.createElement('td');
                celdaNombre.textContent = producto.nombre;

                const celdaPrecio = document.createElement('td');
                celdaPrecio.textContent = producto.precio;

                const celdaStock = document.createElement('td');
                celdaStock.textContent = producto.stock;

                const celdaAccion = document.createElement('td');
                const boton = document.createElement('button');
                boton.textContent = '+1 stock';
                boton.addEventListener('click', () => sumarStock(producto));
                celdaAccion.appendChild(boton);

                fila.appendChild(celdaId);
                fila.appendChild(celdaNombre);
                fila.appendChild(celdaPrecio);
                fila.appendChild(celdaStock);
                fila.appendChild(celdaAccion);
                tbody.appendChild(fila);
            });
        }

        async function sumarStock(producto) {
            try {
                const respuesta = await fetch('/api/productos/' + producto.id, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ stock: Number(producto.stock) + 1 })
                });

                if (!respuesta.ok) {
                    const error = await respuesta.text();
                    agregarLog('ERROR PATCH producto ' + producto.id + ': ' + error);
                    return;
                }

                agregarLog('PATCH enviado para producto ' + producto.id + ' (stock ' + producto.stock + ' -> ' + (Number(producto.stock) + 1) + ')');
            } catch (e) {
                agregarLog('ERROR de red: ' + e.message);
            }
        }

        document.getElementById('recargar').addEventListener('click', recargar);

        window.addEventListener('inventario-cambio', (evento) => {
            agregarLog(JSON.stringify(evento.detail));
            recargar();
        });

        recargar();
        agregarLog('Pagina lista. Esperando eventos del canal "inventario"...');
    </script>
</body>
</html>
