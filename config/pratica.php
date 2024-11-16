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
        /**
         * Formato base del numero pratica
         * -----------------------------------
         * Opzioni disponibili:
         * - 'standard': Formato base (es: 2024/0001)
         * - 'tipo': Include il tipo di pratica (es: CIV/2024/0001)
         * - 'team': Include il team (es: TEAM1/2024/0001)
         * - 'mensile': Include il mese (es: 2024/01/0001)
         * - 'progressivo': Solo numero progressivo (es: 0001)
         * - 'custom': Utilizza un pattern personalizzato
         *
         * @var string
         */
        'formato' => env('PRATICA_NUMERO_FORMATO', 'standard'),

        /**
         * Pattern personalizzato per il formato 'custom'
         * -----------------------------------
         * Variabili disponibili:
         * - {anno}: Anno corrente (formato configurabile)
         * - {mese}: Mese corrente (formato configurabile)
         * - {tipo}: Tipo pratica (prefisso configurabile)
         * - {team}: ID del team
         * - {numero}: Numero progressivo
         *
         * Esempi:
         * - '{tipo}/{anno}/{numero}' → CIV/2024/0001
         * - 'P{anno}-{mese}-{numero}' → P2024-01-0001
         * - '{tipo}{anno}{numero}' → CIV20240001
         * - 'STUDIO-{tipo}-{anno}{numero}' → STUDIO-CIV-20240001
         * - 'T{team}/{tipo}/{anno}.{numero}' → T1/CIV/2024.0001
         *
         * @var string
         */
        'pattern_custom' => env('PRATICA_PATTERN_CUSTOM', '{tipo}/{anno}/{numero}'),

        /**
         * Separatore tra le parti del numero
         * -----------------------------------
         * Esempi:
         * - '/': CIV/2024/0001
         * - '-': CIV-2024-0001
         * - '.': CIV.2024.0001
         *
         * @var string
         */
        'separatore' => env('PRATICA_NUMERO_SEPARATORE', '/'),

        /**
         * Numero di cifre per il contatore progressivo
         * -----------------------------------
         * Esempi:
         * - 4: 0001
         * - 5: 00001
         * - 6: 000001
         *
         * @var int
         */
        'lunghezza_numero' => env('PRATICA_NUMERO_LUNGHEZZA', 4),

        /**
         * Punto di partenza per la numerazione progressiva
         * -----------------------------------
         * @var int
         */
        'numero_partenza' => env('PRATICA_NUMERO_PARTENZA', 1),

        /**
         * Prefissi configurabili per tipo pratica
         * =======================================
         * Questa sezione permette di configurare come i diversi tipi di pratica
         * vengono rappresentati nel numero pratica attraverso prefissi personalizzabili.
         */
        'prefissi_tipo' => [
            /**
             * Mappatura dei prefissi
             * -----------------------------------
             * Definisce la corrispondenza tra i tipi di pratica e i loro prefissi.
             * I tipi devono corrispondere esattamente ai valori disponibili nel campo
             * 'tipologia' del modello Pratica.
             *
             * Esempi di utilizzo:
             * 1. Base:
             *    'Civile' => 'CIV' genera: CIV/2024/0001
             *
             * 2. Multi-livello:
             *    'Civile Ordinario' => 'CIV-ORD' genera: CIV-ORD/2024/0001
             *    'Civile Lavoro' => 'CIV-LAV' genera: CIV-LAV/2024/0001
             *
             * 3. Numerato:
             *    'Civile' => 'C1' genera: C1/2024/0001
             *    'Penale' => 'P1' genera: P1/2024/0001
             *
             * 4. Descrittivo:
             *    'Civile' => 'CIVILE' genera: CIVILE/2024/0001
             *    'Penale' => 'PENALE' genera: PENALE/2024/0001
             */
            'prefissi' => [
                // Pratiche Civili
                'Civile' => 'CIV',
                'Civile Ordinario' => 'CIV-ORD',
                'Civile Lavoro' => 'CIV-LAV',
                'Civile Famiglia' => 'CIV-FAM',
                'Civile Esecuzioni' => 'CIV-ESE',
                'Civile Fallimentare' => 'CIV-FALL',

                // Pratiche Penali
                'Penale' => 'PEN',
                'Penale Ordinario' => 'PEN-ORD',
                'Penale Tributario' => 'PEN-TRI',
                'Penale Minorile' => 'PEN-MIN',

                // Pratiche Amministrative
                'Amministrativo' => 'AMM',
                'Amministrativo TAR' => 'AMM-TAR',
                'Amministrativo Consiglio di Stato' => 'AMM-CDS',

                // Pratiche Tributarie
                'Tributario' => 'TRI',
                'Tributario Commissione Provinciale' => 'TRI-CTP',
                'Tributario Commissione Regionale' => 'TRI-CTR',

                // Volontaria Giurisdizione
                'Volontaria' => 'VOL',

                // Mediazioni e ADR
                'Mediazione' => 'MED',
                'Arbitrato' => 'ARB',
                'Negoziazione Assistita' => 'NEG',

                // Consulenze
                'Consulenza' => 'CONS',
                'Parere' => 'PAR',
            ],

            /**
             * Prefisso di default
             * -----------------------------------
             * Viene utilizzato quando:
             * 1. Il tipo pratica non corrisponde a nessun prefisso configurato
             * 2. Il tipo pratica è vuoto o null
             * 3. Si vuole un fallback sicuro
             *
             * Il valore può essere configurato tramite variabile d'ambiente:
             * PRATICA_PREFISSO_DEFAULT=PRAT
             *
             * Esempi di utilizzo del prefisso default:
             * 1. Pratica senza tipo specifico: GEN/2024/0001
             * 2. Pratica con tipo non mappato: GEN/2024/0002
             *
             * @var string
             */
            'default' => env('PRATICA_PREFISSO_DEFAULT', 'GEN'),

            /**
             * Esempio di utilizzo completo:
             * =============================
             *
             * 1. Pratica Civile Ordinaria
             * Tipo: 'Civile Ordinario'
             * Risultato: CIV-ORD/2024/0001
             *
             * 2. Pratica Penale Minorile
             * Tipo: 'Penale Minorile'
             * Risultato: PEN-MIN/2024/0001
             *
             * 3. Pratica Amministrativa TAR
             * Tipo: 'Amministrativo TAR'
             * Risultato: AMM-TAR/2024/0001
             *
             * 4. Pratica di tipo non mappato
             * Tipo: 'Altro'
             * Risultato: GEN/2024/0001
             *
             * Personalizzazione tramite .env:
             * ------------------------------
             * # Cambia il prefisso default
             * PRATICA_PREFISSO_DEFAULT=STD
             *
             * # Usa il prefisso nel pattern custom
             * PRATICA_NUMERO_FORMATO=custom
             * PRATICA_PATTERN_CUSTOM={tipo}-{anno}/{numero}
             *
             * Risultati con pattern custom:
             * CIV-ORD-2024/0001
             * PEN-MIN-2024/0001
             * AMM-TAR-2024/0001
             */
        ],

        'prefisso_standard' => env('PRATICA_PREFISSO_STANDARD', 'STD'),


        /**
         * Configurazione del reset contatore
         * -----------------------------------
         * Frequenza:
         * - 'mai': Il contatore non si resetta mai
         * - 'annuale': Reset all'inizio di ogni anno
         * - 'mensile': Reset all'inizio di ogni mese
         *
         * Esempi con reset mensile:
         * Gennaio:   CIV/2024/01/0001
         * Febbraio:  CIV/2024/02/0001
         * Marzo:     CIV/2024/03/0001
         */
        'reset_contatore' => [
            'frequenza' => env('PRATICA_RESET_CONTATORE', 'mai'),
            'riparti_da' => env('PRATICA_RESET_NUMERO', 1),
        ],

        /**
         * Configurazione dei componenti del numero
         * -----------------------------------
         * Personalizza il formato di ogni componente del numero pratica
         */
        'componenti' => [
            'anno' => [
                'includi' => env('PRATICA_INCLUDI_ANNO', true),
                'formato' => env('PRATICA_FORMATO_ANNO', 'Y'), // 'Y' = 2024, 'y' = 24
            ],
            'mese' => [
                'includi' => env('PRATICA_INCLUDI_MESE', false),
                'formato' => env('PRATICA_FORMATO_MESE', 'm'), // 'm' = 01, 'n' = 1
            ],
            'progressivo' => [
                'tipo' => env('PRATICA_TIPO_PROGRESSIVO', 'annuale'), // 'globale', 'annuale', 'mensile'
                'lunghezza' => env('PRATICA_LUNGHEZZA_PROGRESSIVO', 4),
            ],
        ],
    ],

    /**
     * Esempi di Configurazione
     * =============================
     *
     * 1. Formato Standard con Anno
     * -----------------------------------
     * PRATICA_NUMERO_FORMATO=standard
     * PRATICA_NUMERO_SEPARATORE=/
     * Risultato: 2024/0001
     *
     * 2. Formato Tipologia con Anno e Team
     * -----------------------------------
     * PRATICA_NUMERO_FORMATO=custom
     * PRATICA_PATTERN_CUSTOM={tipo}/{anno}/T{team}/{numero}
     * Risultato: CIV/2024/T1/0001
     *
     * 3. Formato Mensile con Reset
     * -----------------------------------
     * PRATICA_NUMERO_FORMATO=mensile
     * PRATICA_RESET_CONTATORE=mensile
     * PRATICA_COMPONENTI_MESE_INCLUDI=true
     * Risultato: 2024/01/0001
     *
     * 4. Formato Studio Legale Personalizzato
     * -----------------------------------
     * PRATICA_NUMERO_FORMATO=custom
     * PRATICA_PATTERN_CUSTOM=STUDIO-{tipo}-{anno}{numero}
     * PRATICA_NUMERO_SEPARATORE=-
     * Risultato: STUDIO-CIV-20240001
     *
     * 5. Formato con Anno Abbreviato
     * -----------------------------------
     * PRATICA_NUMERO_FORMATO=custom
     * PRATICA_PATTERN_CUSTOM={tipo}/{anno}/{numero}
     * PRATICA_COMPONENTI_ANNO_FORMATO=y
     * Risultato: CIV/24/0001
     *
     * 6. Formato con Team e Sottodivisione
     * -----------------------------------
     * PRATICA_NUMERO_FORMATO=custom
     * PRATICA_PATTERN_CUSTOM=T{team}/{tipo}/{anno}.{numero}
     * Risultato: T1/CIV/2024.0001
     *
     * 7. Formato Progressivo Globale
     * -----------------------------------
     * PRATICA_NUMERO_FORMATO=custom
     * PRATICA_PATTERN_CUSTOM={tipo}{numero}
     * PRATICA_RESET_CONTATORE=mai
     * PRATICA_LUNGHEZZA_NUMERO=5
     * Risultato: CIV00001
     */
];