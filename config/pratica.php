<?php

/**
 * Configurazione Sistema di Numerazione Pratiche
 *
 * Questo file contiene tutte le configurazioni necessarie per gestire il sistema
 * di numerazione delle pratiche legali. La struttura è progettata per essere
 * flessibile e personalizzabile attraverso variabili d'ambiente o direttamente
 * nel file di configurazione.
 */

return [
    'numero_pratica' => [
        'formato' => 'custom',  // Usiamo il formato custom per massima flessibilità
        'pattern_custom' => '{tipo}-{team}-{anno}-{numero}',
        'separatore' => '-',
        'lunghezza_numero' => 3,
        'numero_partenza' => 1,
        'prefissi_tipo' => [
            'prefissi' => [
                'default' => 'STD',
                // altri prefissi specifici se necessari
            ],
            'default' => 'STD'
        ],
        'componenti' => [
            'anno' => [
                'includi' => true,
                'formato' => 'Y'
            ],
            'mese' => [
                'includi' => false,
                'formato' => 'm'
            ],
            'progressivo' => [
                'tipo' => 'annuale',  // si resetta ogni anno
                'lunghezza' => 3
            ]
        ],
        'reset_contatore' => [
            'frequenza' => 'annuale'
        ]
    ]
];