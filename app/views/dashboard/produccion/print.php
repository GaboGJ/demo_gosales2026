<?php
if (!isset($produccionesLista) || empty($produccionesLista)) {
    $single = $produccion ?? [
        'id_produccion' => 6,
        'codigo'        => '00006',
        'fecha'         => '21/11/2026',
        'turno'         => 'Mañana',
        'sucursal'      => 'Tatu Central',
        'responsable'   => 'Carlos Mendoza',
        'estado'        => 'En proceso',
        'items' => [
            ['producto' => 'Masa Familiar',    'medida' => 'u',  'entrada' => 26,    'producido' => 30,   'existencia' => 22],
            ['producto' => 'Masa Mediana',     'medida' => 'u',  'entrada' => 44,    'producido' => 40,   'existencia' => null],
            ['producto' => 'Queso Muzzarella', 'medida' => 'g',  'entrada' => 33012, 'producido' => 0,    'existencia' => null],
            ['producto' => 'Queso Cheddar',    'medida' => 'g',  'entrada' => 5132,  'producido' => 0,    'existencia' => null],
            ['producto' => 'Calabresa',        'medida' => 'g',  'entrada' => 2594,  'producido' => 0,    'existencia' => null],
            ['producto' => 'Peperoni',         'medida' => 'g',  'entrada' => 2524,  'producido' => 0,    'existencia' => null],
            ['producto' => 'Tocino',           'medida' => 'g',  'entrada' => 750,   'producido' => 1000, 'existencia' => null],
            ['producto' => 'Jamón',            'medida' => 'g',  'entrada' => 2798,  'producido' => 0,    'existencia' => null],
            ['producto' => 'Harina',           'medida' => 'kg', 'entrada' => 0,     'producido' => 21,   'existencia' => null],
            ['producto' => 'Carne',            'medida' => 'g',  'entrada' => 6355,  'producido' => 0,    'existencia' => null],
            ['producto' => 'Pollo',            'medida' => 'g',  'entrada' => 2000,  'producido' => 0,    'existencia' => null],
        ],
    ];
    $produccionesLista = [$single];
}

$tipo = strtolower($tipo ?? ($_GET['tipo'] ?? 'completo'));
if (!in_array($tipo, ['completo', 'resumen', 'vacio'], true)) {
    $tipo = 'completo';
}
$esVacio = ($tipo === 'vacio');
$esResumen = ($tipo === 'resumen' || $tipo === 'vacio');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Planillas de Producción</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        :root {
            --primary: #e91e63;
            --primary-dark: #c2185b;
            --primary-light: #fce4ec;
            --text-dark: #1a1a1a;
            --text-muted: #5f6672;
            --border: #dcdfe4;
        }
        * {
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            background: #ffffff;
            color: var(--text-dark);
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .contenedor-vertical {
            width: 100%;
            max-width: 820px;
            margin: 10px auto;
            padding: 0 8px;
        }

        .hoja {
            display: block;
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            background: #ffffff;
            margin-bottom: 14px;
        }
        .hoja:last-child {
            margin-bottom: 0;
        }

        .hoja-header {
            background: linear-gradient(195deg, #ec407a, var(--primary-dark));
            color: #ffffff;
            padding: 9px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .hoja-header .brand {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }
        .hoja-header .brand .material-symbols-rounded {
            font-size: 20px;
            flex-shrink: 0;
        }
        .hoja-header .brand-name {
            font-weight: 700;
            font-size: 13.5px;
            line-height: 1.15;
            text-transform: uppercase;
            word-break: break-word;
        }
        .hoja-header .brand-tag {
            font-size: 9.5px;
            opacity: .92;
        }
        .hoja-header .doc-box {
            text-align: right;
            flex-shrink: 0;
        }
        .hoja-header .doc-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: .05em;
            opacity: .88;
        }
        .hoja-header .doc-num {
            font-size: 14.5px;
            font-weight: 800;
        }

        .meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 5px 12px;
            align-items: center;
            justify-content: space-between;
            padding: 7px 14px;
            background: var(--primary-light);
            border-bottom: 1px solid var(--border);
            font-size: 10.5px;
        }
        .meta-row span {
            color: var(--text-muted);
        }
        .meta-row strong {
            color: var(--text-dark);
        }

        .seccion {
            padding: 8px 14px;
        }

        .subtitulo-bloque {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--primary-dark);
            background: var(--primary-light);
            border-left: 3px solid var(--primary);
            padding: 4px 8px;
            margin: 8px 0 5px;
        }
        .subtitulo-bloque:first-child {
            margin-top: 0;
        }

        table.tabla-show {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }
        table.tabla-show thead {
            display: table-header-group;
        }
        table.tabla-show tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        table.tabla-show th {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--text-muted);
            padding: 5px 3px;
            border-bottom: 1.5px solid var(--border);
            font-weight: 700;
        }
        table.tabla-show td {
            padding: 4.5px 3px;
            font-size: 10.5px;
            border-bottom: 1px solid #eef0f2;
            vertical-align: middle;
            word-break: break-word;
            min-height: 18px;
        }
        table.tabla-show th.text-left,
        table.tabla-show td.text-left {
            text-align: left;
        }
        table.tabla-show th.text-center,
        table.tabla-show td.text-center {
            text-align: center;
        }

        .prod-nombre {
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-dark);
        }
        .prod-medida {
            font-size: 9px;
            color: var(--text-muted);
            margin-left: 2px;
        }
        .val-bold {
            font-weight: 700;
            color: var(--text-dark);
        }
        .val-salida {
            font-weight: 800;
            color: var(--primary);
        }
        .pendiente {
            color: var(--text-muted);
            font-size: 9px;
            font-weight: 600;
        }

        .nota-formula {
            margin-top: 6px;
            font-size: 9.5px;
            color: var(--text-muted);
        }

        .firma-box {
            margin: 22px auto 4px;
            width: 180px;
            max-width: 100%;
            border-top: 1px solid var(--text-dark);
            text-align: center;
            padding-top: 3px;
            font-size: 9px;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .hoja-footer {
            text-align: center;
            padding: 5px 8px;
            font-size: 8.5px;
            color: var(--text-muted);
            border-top: 1px solid var(--border);
            background: #fafafa;
        }

        @media print {
            html, body {
                width: 100%;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                font-size: 9.5px;
            }
            .contenedor-vertical {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            /* En hojas estándar: evita cortar cada planilla/tabla a la mitad, apiladas una debajo de otra */
            .hoja {
                width: 100% !important;
                max-width: 100% !important;
                border: 1px solid #c5cad2;
                border-radius: 0;
                margin: 0 0 4mm 0 !important;
                break-inside: avoid;
                page-break-inside: avoid;
            }
            .hoja:last-child {
                margin-bottom: 0 !important;
            }
            .seccion,
            table.tabla-show {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            .hoja-header,
            .meta-row,
            .subtitulo-bloque {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .hoja-header {
                padding: 6px 8px;
            }
            .hoja-header .brand .material-symbols-rounded {
                font-size: 16px;
            }
            .hoja-header .brand-name {
                font-size: 10.5px;
            }
            .hoja-header .doc-num {
                font-size: 11.5px;
            }
            .meta-row {
                padding: 4px 8px;
                gap: 3px 8px;
                font-size: 8.5px;
            }
            .seccion {
                padding: 5px 8px;
            }
            .subtitulo-bloque {
                font-size: 8px;
                padding: 3px 6px;
                margin: 6px 0 4px;
            }
            table.tabla-show th {
                font-size: 7.5px;
                padding: 3px 2px;
            }
            table.tabla-show td {
                font-size: 8px;
                padding: 4px 2px;
            }
            .prod-medida, .pendiente, .nota-formula {
                font-size: 7.5px;
            }
            .firma-box {
                margin: 14px auto 2px;
                width: 120px;
                font-size: 7.5px;
            }
            .hoja-footer {
                padding: 3px 6px;
                font-size: 7px;
            }
            @page {
                margin: 4mm;
            }
        }

        /* En impresoras térmicas (58mm / 80mm): permite flujo continuo sin bloquear cortes de cinta */
        @media print and (max-width: 90mm) {
            .hoja,
            .seccion,
            table.tabla-show {
                break-inside: auto;
                page-break-inside: auto;
            }
        }
    </style>
</head>
<body>

<div class="contenedor-vertical" id="areaImpresion">
    <?php foreach ($produccionesLista as $p):
        $codigo = htmlspecialchars($p['codigo'] ?? str_pad((int)($p['id_produccion'] ?? 0), 5, '0', STR_PAD_LEFT));
        $items  = $p['items'] ?? [];
    ?>
    <div class="hoja">
        <div class="hoja-header">
            <div class="brand">
                <span class="material-symbols-rounded">skillet</span>
                <div>
                    <div class="brand-name"><?= htmlspecialchars($p['sucursal'] ?? 'Tatu Central') ?></div>
                    <div class="brand-tag">
                        Planilla de Producción y Existencia
                        <?php if ($tipo === 'resumen'): ?> &middot; Resumen<?php elseif ($tipo === 'vacio'): ?> &middot; Vacío<?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="doc-box">
                <div class="doc-label">Planilla Nº</div>
                <div class="doc-num">#<?= $codigo ?></div>
            </div>
        </div>

        <div class="meta-row">
            <span><strong>Fecha:</strong> <?= htmlspecialchars($p['fecha'] ?? '') ?></span>
            <span><strong>Turno:</strong> <?= htmlspecialchars($p['turno'] ?? '') ?></span>
            <span><strong>Encargado:</strong> <?= htmlspecialchars($p['responsable'] ?? '') ?></span>
            <span><strong>Estado:</strong> <?= htmlspecialchars($p['estado'] ?? '') ?></span>
        </div>

        <div class="seccion">
            <?php if (!$esResumen): ?>
                <table class="tabla-show">
                    <thead>
                        <tr>
                            <th class="text-left">Producto</th>
                            <th class="text-center">Entrada</th>
                            <th class="text-center">Producción</th>
                            <th class="text-center">Existencia</th>
                            <th class="text-center">Salida / Consumo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it):
                            $ent = (float)($it['entrada'] ?? 0);
                            $pro = (float)($it['producido'] ?? 0);
                            $exi = $it['existencia'] ?? null;
                            $contado = $exi !== null && $exi !== '';
                            $sal = $contado ? ($ent + $pro - (float)$exi) : null;
                        ?>
                            <tr>
                                <td class="text-left">
                                    <span class="prod-nombre"><?= htmlspecialchars($it['producto'] ?? '') ?></span>
                                    <span class="prod-medida">(<?= htmlspecialchars($it['medida'] ?? '') ?>)</span>
                                </td>
                                <td class="text-center"><?= number_format($ent, 2, ',', '.') ?></td>
                                <td class="text-center"><?= number_format($pro, 2, ',', '.') ?></td>
                                <td class="text-center val-bold">
                                    <?= $contado ? number_format((float)$exi, 2, ',', '.') : '—' ?>
                                </td>
                                <td class="text-center">
                                    <?= $contado
                                        ? '<span class="val-salida">' . number_format($sal, 2, ',', '.') . '</span>'
                                        : '<span class="pendiente">Pendiente</span>' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="nota-formula">Salida / consumo = entrada + producción − existencia.</div>
            <?php else: ?>
                <div class="subtitulo-bloque">Datos de Producción</div>
                <table class="tabla-show">
                    <thead>
                        <tr>
                            <th class="text-left">Producto</th>
                            <th class="text-center">Producción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it):
                            $pro = (float)($it['producido'] ?? 0);
                        ?>
                            <tr>
                                <td class="text-left">
                                    <span class="prod-nombre"><?= htmlspecialchars($it['producto'] ?? '') ?></span>
                                    <span class="prod-medida">(<?= htmlspecialchars($it['medida'] ?? '') ?>)</span>
                                </td>
                                <td class="text-center val-bold"><?= $esVacio ? '' : number_format($pro, 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="subtitulo-bloque">Existencia</div>
                <table class="tabla-show">
                    <thead>
                        <tr>
                            <th class="text-left">Producto</th>
                            <th class="text-center">Existencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it):
                            $exi = $it['existencia'] ?? null;
                            $contado = $exi !== null && $exi !== '';
                        ?>
                            <tr>
                                <td class="text-left">
                                    <span class="prod-nombre"><?= htmlspecialchars($it['producto'] ?? '') ?></span>
                                    <span class="prod-medida">(<?= htmlspecialchars($it['medida'] ?? '') ?>)</span>
                                </td>
                                <td class="text-center val-bold">
                                    <?= (!$esVacio && $contado) ? number_format((float)$exi, 2, ',', '.') : '' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="firma-box">Firma del encargado</div>
        </div>

        <div class="hoja-footer">
            <?= htmlspecialchars($p['sucursal'] ?? 'Tatu Central') ?> &middot; Turno <?= htmlspecialchars($p['turno'] ?? '') ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

</body>
</html>