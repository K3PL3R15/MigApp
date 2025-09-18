<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #{{ str_pad($sale->id_sale, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .invoice-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .invoice-details h3 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        
        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }
        
        .detail-value {
            color: #495057;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .products-table th {
            background-color: #495057;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .products-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .products-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .products-table tbody tr:hover {
            background-color: #e9ecef;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-section {
            background-color: #28a745;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .total-section h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .total-amount {
            font-size: 32px;
            font-weight: 700;
        }
        
        .footer {
            background-color: #495057;
            color: white;
            padding: 25px 30px;
            text-align: center;
        }
        
        .footer p {
            margin-bottom: 5px;
            opacity: 0.9;
        }
        
        .branch-info {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .branch-info h4 {
            color: #495057;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 0;
            }
            
            .header, .content, .footer {
                padding: 20px;
            }
            
            .invoice-info {
                flex-direction: column;
            }
            
            .products-table {
                font-size: 14px;
            }
            
            .products-table th,
            .products-table td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-receipt"></i> MigApp</h1>
            <p>{{ $sale->branch->name }} - Factura Digital</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <!-- Invoice Details -->
            <div class="invoice-details">
                <h3>Detalles de la Factura</h3>
                <div class="detail-row">
                    <span class="detail-label">Número de Factura:</span>
                    <span class="detail-value">#{{ str_pad($sale->id_sale, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Fecha de Emisión:</span>
                    <span class="detail-value">{{ $sale->date->format('d/m/Y H:i') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Vendedor:</span>
                    <span class="detail-value">{{ $sale->user->name }} ({{ ucfirst($sale->user->role) }})</span>
                </div>
                @if($sale->justify)
                <div class="detail-row">
                    <span class="detail-label">Observaciones:</span>
                    <span class="detail-value">{{ $sale->justify }}</span>
                </div>
                @endif
            </div>
            
            <!-- Branch Info -->
            <div class="branch-info">
                <h4>Información de la Sucursal</h4>
                <p><strong>{{ $sale->branch->name }}</strong></p>
                @if($sale->branch->address)
                    <p>{{ $sale->branch->address }}</p>
                @endif
                @if($sale->branch->phone)
                    <p>Teléfono: {{ $sale->branch->phone }}</p>
                @endif
            </div>
            
            <!-- Products Table -->
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-right">Precio Unit.</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->products as $product)
                        <tr>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                @if($product->description)
                                    <br><small style="color: #6c757d;">{{ Str::limit($product->description, 50) }}</small>
                                @endif
                            </td>
                            <td class="text-center">{{ $product->pivot->quantity }}</td>
                            <td class="text-right">${{ number_format($product->pivot->unit_price, 0) }}</td>
                            <td class="text-right"><strong>${{ number_format($product->pivot->subtotal, 0) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Total Section -->
            <div class="total-section">
                <h3>Total a Pagar</h3>
                <div class="total-amount">${{ number_format($sale->total, 0) }}</div>
                <p style="margin-top: 10px; opacity: 0.9;">
                    {{ $sale->products->count() }} {{ $sale->products->count() === 1 ? 'producto' : 'productos' }} - 
                    {{ $sale->products->sum('pivot.quantity') }} unidades
                </p>
            </div>
            
            <!-- Thank You Message -->
            <div style="text-align: center; padding: 20px; background-color: #f8f9fa; border-radius: 8px;">
                <h4 style="color: #495057; margin-bottom: 10px;">¡Gracias por tu compra!</h4>
                <p style="color: #6c757d;">Esperamos verte pronto en {{ $sale->branch->name }}</p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>MigApp - Sistema de Gestión de Panaderías</strong></p>
            <p>Este es un email generado automáticamente, por favor no responder.</p>
            <p style="font-size: 12px; margin-top: 15px;">
                Factura enviada el {{ now()->format('d/m/Y H:i') }}
            </p>
        </div>
    </div>
</body>
</html>
