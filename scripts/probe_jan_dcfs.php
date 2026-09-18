<?php
declare(strict_types=1);
$root = dirname(__DIR__);
require_once $root . '/website/inc/bootstrap.php';

$files = [
    'ProcessPower.dcf' => 'C:/Users/Administrator/Downloads/ProcessPower.dcf',
    'Piping.dcf' => 'C:/Users/Administrator/Downloads/Piping.dcf',
    'Ortho.dcf' => 'C:/Users/Administrator/Downloads/Ortho.dcf',
    'Iso.dcf' => 'C:/Users/Administrator/Downloads/Iso.dcf',
];

foreach ($files as $label => $path) {
    echo "======== {$label} ========\n";
    echo "size=" . filesize($path) . "\n";
    try {
        ErcDcf::validate($path);
        echo "validate: OK\n";
    } catch (Throwable $e) {
        echo "validate: FAIL — {$e->getMessage()}\n";
    }

    try {
        $pdo = ErcDcf::connect($path);
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
        echo "tables=" . count($tables) . "\n";
        $key = ['PnPProject','PnPDrawings','EngineeringItems','Equipment','HandValves','Instrumentation','PipeLines','PipeLineGroup','Pipes','PipingSpecialtyItems','OrthoDrawings','IsometricDrawings'];
        foreach ($key as $t) {
            $exists = in_array($t, $tables, true);
            $count = $exists ? (int)$pdo->query('SELECT COUNT(*) FROM "'.$t.'"')->fetchColumn() : -1;
            echo "  {$t}: " . ($exists ? "yes count={$count}" : "no") . "\n";
        }

        // Drawing-like tables
        foreach ($tables as $t) {
            if (stripos($t, 'draw') !== false || stripos($t, 'ortho') !== false || stripos($t, 'iso') !== false) {
                $c = (int)$pdo->query('SELECT COUNT(*) FROM "'.str_replace('"','""',$t).'"')->fetchColumn();
                echo "  [draw-ish] {$t} count={$c}\n";
            }
        }

        if (ErcDcf::tableExists($pdo, 'PnPDrawings')) {
            $cols = $pdo->query('PRAGMA table_info(PnPDrawings)')->fetchAll(PDO::FETCH_ASSOC);
            $colNames = array_column($cols, 'name');
            echo "  PnPDrawings cols sample: " . implode(', ', array_slice($colNames, 0, 20)) . "\n";
            try {
                $rows = ErcQueries::run($pdo, 'drawings');
                echo "  drawings() rows=" . count($rows) . "\n";
                if ($rows) {
                    $r0 = $rows[0];
                    echo "  first keys: " . implode(', ', array_slice(array_keys($r0), 0, 12)) . "\n";
                }
            } catch (Throwable $e) {
                echo "  drawings() FAIL — {$e->getMessage()}\n";
            }
        } else {
            echo "  drawings(): N/A (no PnPDrawings)\n";
        }

        try {
            $proj = ErcDcf::loadProject($path)['project'];
            echo "  project name=" . ($proj['name'] ?: '(empty)') . " drawings_count=" . ($proj['counts']['drawings'] ?? '?') . "\n";
        } catch (Throwable $e) {
            echo "  loadProject FAIL — {$e->getMessage()}\n";
        }
    } catch (Throwable $e) {
        echo "connect FAIL — {$e->getMessage()}\n";
    }
    echo "\n";
}
