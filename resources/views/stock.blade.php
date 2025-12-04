<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Stock en Tiempo Real</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .product-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .product-card.updated {
            animation: highlight 2s ease;
        }

        .stock-control {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }

        .stock-input {
            width: 80px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .update-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        .update-btn:hover {
            background: #0056b3;
        }

        .stock-badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-weight: bold;
        }

        .stock-high {
            background: #d4edda;
            color: #155724;
        }

        .stock-medium {
            background: #fff3cd;
            color: #856404;
        }

        .stock-low {
            background: #f8d7da;
            color: #721c24;
        }

        @keyframes highlight {
            0% {
                background-color: #ffff99;
            }

            100% {
                background-color: white;
            }
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 15px;
            border-radius: 5px;
            display: none;
            z-index: 1000;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📊 Panel de Stock en Tiempo Real</h1>
            <p>Los cambios se actualizan automáticamente para todos los usuarios conectados</p>
        </div>

        <div id="notification" class="notification"></div>

        <div class="products-grid" id="products-grid">
            @foreach($products as $product)
                <div class="product-card" id="product-{{ $product->id }}">
                    <h3>{{ $product->name }}</h3>
                    <p>{{ $product->description }}</p>
                    <div class="product-info">
                        <strong>Precio: ${{ number_format($product->price, 2) }}</strong>
                        <div class="stock-control">
                            <span>Stock:</span>
                            <input type="number" class="stock-input" value="{{ $product->stock }}" min="0"
                                data-product-id="{{ $product->id }}">
                            <button class="update-btn" onclick="updateStock({{ $product->id }})">
                                Actualizar
                            </button>
                        </div>
                        <div style="margin-top: 10px;">
                            <span class="stock-badge" id="stock-badge-{{ $product->id }}"
                                data-stock="{{ $product->stock }}">
                                {{ $product->stock }} unidades
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

<script>
    // Función para suscribirse al canal de stock
    function subscribeToStockChannel() {
        if (window.Echo) {
            console.log('🔔 Suscribiéndose al canal stock-updates...');
            
            window.Echo.channel('stock-updates')
                .listen('.stock.updated', (e) => {
                    console.log('🎉 Evento stock.updated recibido:', e);
                    updateProductCard(e);
                    showNotification(`Stock actualizado: ${e.name} - ${e.stock} unidades`, 'info');
                })
                .error((error) => {
                    console.error('❌ Error suscribiéndose al canal:', error);
                });
                
            console.log('✅ Suscrito correctamente a stock-updates');
        } else {
            console.warn('⚠️ Echo no disponible, reintentando en 2 segundos...');
            setTimeout(subscribeToStockChannel, 2000);
        }
    }

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        console.log('📋 DOM cargado, verificando Echo...');
        console.log('Echo disponible:', typeof window.Echo !== 'undefined');
        console.log('Pusher disponible:', typeof window.Pusher !== 'undefined');
        
        // Verificar conexión WebSocket
        if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
            const pusher = window.Echo.connector.pusher;
            
            // Monitorear estado de conexión
            pusher.connection.bind('connected', () => {
                console.log('✅ WebSocket CONECTADO a Reverb');
                subscribeToStockChannel();
            });
            
            pusher.connection.bind('error', (error) => {
                console.error('❌ Error de conexión WebSocket:', error);
            });
            
            pusher.connection.bind('disconnected', () => {
                console.warn('⚠️ WebSocket desconectado');
            });
            
            // Si ya está conectado, suscribirse inmediatamente
            if (pusher.connection.state === 'connected') {
                console.log('✅ Ya conectado, suscribiendo...');
                subscribeToStockChannel();
            }
        } else {
            console.warn('Echo no inicializado completamente, esperando...');
            setTimeout(() => subscribeToStockChannel(), 1000);
        }
    });

    // Funciones existentes (mantener)
    async function updateStock(productId) {
        const input = document.querySelector(`.stock-input[data-product-id="${productId}"]`);
        const stockValue = parseInt(input.value);

        if (isNaN(stockValue) || stockValue < 0) {
            showNotification('Por favor ingresa un valor válido', 'error');
            return;
        }

        try {
            const response = await fetch(`/products/${productId}/stock`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ stock: stockValue })
            });

            const data = await response.json();

            if (data.success) {
                showNotification('Stock actualizado correctamente', 'success');
                console.log('📤 Evento enviado al servidor');
            } else {
                showNotification('Error al actualizar el stock', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error de conexión al actualizar el stock', 'error');
        }
    }

    function showNotification(message, type) {
        const notification = document.getElementById('notification');
        if (!notification) return;
        
        notification.textContent = message;
        notification.style.display = 'block';
        
        if (type === 'success') {
            notification.style.background = '#28a745';
        } else if (type === 'error') {
            notification.style.background = '#dc3545';
        } else if (type === 'info') {
            notification.style.background = '#17a2b8';
        } else {
            notification.style.background = '#6c757d';
        }

        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }

    function updateProductCard(data) {
        const productCard = document.getElementById(`product-${data.id}`);
        if (productCard) {
            const input = productCard.querySelector('.stock-input');
            const badge = productCard.querySelector('.stock-badge');

            if (input) input.value = data.stock;
            if (badge) {
                badge.textContent = `${data.stock} unidades`;
                badge.setAttribute('data-stock', data.stock);
                badge.className = `stock-badge ${getStockClass(data.stock)}`;
            }

            productCard.classList.add('updated');
            setTimeout(() => {
                productCard.classList.remove('updated');
            }, 2000);
            
            console.log(`🔄 Producto ${data.id} actualizado: ${data.stock} unidades`);
        }
    }

    function getStockClass(stock) {
        if (stock > 20) return 'stock-high';
        if (stock > 5) return 'stock-medium';
        return 'stock-low';
    }
</script>
</body>

</html>