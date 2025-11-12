<?php
class Factura {
    private $db;
    public function __construct(PDO $db){ $this->db = $db; }

    public function generar(array $pago){
        $numero = 'FAC-'.date('Ymd').'-'.str_pad($pago['id'],6,'0',STR_PAD_LEFT);
        $datos = json_encode([
            'reserva_id' => $pago['reserva_id'],
            'usuario_id' => $pago['usuario_id'],
            'metodo_id'  => $pago['metodo_id'],
            'monto'      => $pago['monto'],
            'estado'     => $pago['estado']
        ]);
        $st = $this->db->prepare(
            "INSERT INTO facturas (pago_id, numero, total, moneda, datos_json)
             VALUES (?,?,?,?,?)"
        );
        if($st->execute([$pago['id'],$numero,$pago['monto'],$pago['moneda'] ?? 'UYU',$datos])){
            return $this->obtenerPorNumero($numero);
        }
        return false;
    }

    public function obtenerPorPago(int $pagoId){
        $st = $this->db->prepare("SELECT * FROM facturas WHERE pago_id=? LIMIT 1");
        $st->execute([$pagoId]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorNumero(string $numero){
        $st = $this->db->prepare("SELECT * FROM facturas WHERE numero=? LIMIT 1");
        $st->execute([$numero]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    // NUEVO: Generar PDF con DomPDF
    public function generarPDF(array $factura, array $pago, array $usuario){
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $dompdf = new \Dompdf\Dompdf();
        
        // Template HTML de la factura
        $html = $this->plantillaHTML($factura, $pago, $usuario);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Guardar en carpeta facturas/
        $nombreArchivo = 'factura_' . $factura['numero'] . '.pdf';
        $rutaCarpeta = __DIR__ . '/../facturas/';
        if(!is_dir($rutaCarpeta)) mkdir($rutaCarpeta, 0755, true);
        
        $rutaCompleta = $rutaCarpeta . $nombreArchivo;
        file_put_contents($rutaCompleta, $dompdf->output());
        
        // Actualizar BD con ruta del PDF
        $st = $this->db->prepare("UPDATE facturas SET pdf_path=? WHERE id=?");
        $st->execute([$nombreArchivo, $factura['id']]);
        
        return $nombreArchivo;
    }

    private function plantillaHTML($factura, $pago, $usuario){
        $fecha = date('d/m/Y H:i', strtotime($factura['creado_en'] ?? 'now'));
        $datos = json_decode($factura['datos_json'], true);
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura {$factura['numero']}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #333; }
        .header { text-align: center; margin-bottom: 40px; border-bottom: 3px solid #1976d2; padding-bottom: 20px; }
        .header h1 { color: #1976d2; margin: 0; }
        .info { display: table; width: 100%; margin-bottom: 30px; }
        .info-col { display: table-cell; width: 50%; vertical-align: top; }
        .info-col h3 { color: #1976d2; border-bottom: 2px solid #e3f2fd; padding-bottom: 8px; }
        .info-row { margin: 8px 0; }
        .label { font-weight: bold; display: inline-block; width: 140px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #1976d2; color: white; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        .total { text-align: right; margin-top: 30px; font-size: 1.3em; font-weight: bold; color: #1976d2; }
        .footer { margin-top: 50px; text-align: center; color: #777; font-size: 0.9em; border-top: 1px solid #ddd; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚡ FACTURA ELECTRÓNICA</h1>
        <p style="margin: 5px 0; font-size: 1.1em;">Nº {$factura['numero']}</p>
    </div>

    <div class="info">
        <div class="info-col">
            <h3>Emisor</h3>
            <div class="info-row"><span class="label">Empresa:</span> GestiónEV S.A.</div>
            <div class="info-row"><span class="label">RUT:</span> 21 234 567 890 012</div>
            <div class="info-row"><span class="label">Dirección:</span> Av. Italia 2025, Montevideo</div>
            <div class="info-row"><span class="label">Teléfono:</span> +598 2XXX XXXX</div>
        </div>
        <div class="info-col">
            <h3>Cliente</h3>
            <div class="info-row"><span class="label">Usuario:</span> {$usuario['usuario']}</div>
            <div class="info-row"><span class="label">Email:</span> {$usuario['correo']}</div>
            <div class="info-row"><span class="label">Fecha:</span> {$fecha}</div>
            <div class="info-row"><span class="label">Pago ID:</span> #{$pago['id']}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th style="width:120px; text-align:right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Reserva de Estación de Carga</strong><br>
                    <small>ID Reserva: #{$datos['reserva_id']}</small>
                </td>
                <td style="text-align:right;">{$factura['moneda']} {$factura['total']}</td>
            </tr>
        </tbody>
    </table>

    <div class="total">
        TOTAL: {$factura['moneda']} {$factura['total']}
    </div>

    <div class="footer">
        <p>Gracias por confiar en GestiónEV - Tu red de carga inteligente</p>
        <p style="font-size:0.85em;">Este documento es una representación impresa de una factura electrónica válida.</p>
    </div>
</body>
</html>
HTML;
    }
}
?>