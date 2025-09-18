<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sale;
use App\Mail\InvoiceMail;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email {sale_id?} {recipient?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Obtener ID de venta o usar el primero disponible
            $saleId = $this->argument('sale_id') ?? Sale::first()?->id_sale;
            
            if (!$saleId) {
                $this->error('No hay ventas disponibles para probar.');
                return 1;
            }
            
            // Cargar la venta con sus relaciones
            $sale = Sale::with(['products', 'branch', 'user'])->find($saleId);
            
            if (!$sale) {
                $this->error("Venta con ID {$saleId} no encontrada.");
                return 1;
            }
            
            $this->info("Probando envío de factura para venta #{$sale->id_sale}...");
            
            // Generar número de factura
            $invoiceNumber = 'MIG-' . str_pad($sale->id_sale, 6, '0', STR_PAD_LEFT);
            
            // Email de prueba (se puede pasar como segundo argumento)
            $testEmail = $this->argument('recipient') ?? 'test@example.com';
            
            $this->info('Configuración de email:');
            $this->line('  - Driver: ' . config('mail.default'));
            $this->line('  - Host: ' . config('mail.mailers.smtp.host'));
            $this->line('  - Port: ' . config('mail.mailers.smtp.port'));
            $this->line('  - From: ' . config('mail.from.address'));
            
            // Enviar email
            $this->info("Enviando email a {$testEmail}...");
            Mail::to($testEmail)->send(new InvoiceMail($sale, $invoiceNumber));
            
            $this->info("\u2713 Email de factura enviado exitosamente a {$testEmail}");
            $this->line("   Venta: #{$sale->id_sale}");
            $this->line("   Total: $" . number_format($sale->total, 2));
            $this->line("   Sucursal: {$sale->branch->name}");
            
            if (config('mail.default') === 'log') {
                $this->warn('NOTA: El email se guardó en storage/logs/laravel.log (modo log)');
            } else {
                $this->info('NOTA: Email enviado vía SMTP');
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error enviando email: {$e->getMessage()}");
            $this->line("Stack trace: {$e->getTraceAsString()}");
            return 1;
        }
    }
}
