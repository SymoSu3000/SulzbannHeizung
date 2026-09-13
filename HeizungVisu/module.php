<?php

declare(strict_types=1);

class SulzbannHeizungVisualisierung extends IPSModule
{
    /*
     * ============================================================
     * FBH-KREISE
     * ============================================================
     *
     * Physikalisch geprüft:
     *
     * Ventilstatus:
     *   false / 0 = geschlossen
     *   true  / 1 = offen
     *
     * Stellwert:
     *   0...100 %
     *
     * Der Stellwert ist der MDT-Reglerausgang / PWM-Bedarf.
     * Er ist NICHT gleichbedeutend mit der momentanen
     * mechanischen Ventilstellung.
     * ============================================================
     */

    private const FBH_CIRCUITS = [

        /*
         * ========================================================
         * ERDGESCHOSS
         * ========================================================
         */

        'EG' => [

            [
                'name'   => 'Haupteingang',
                'state'  => 18344,
                'demand' => 39391
            ],

            [
                'name'   => 'Küche',
                'state'  => 50892,
                'demand' => 22281
            ],

            [
                'name'   => 'WC',
                'state'  => 37309,
                'demand' => 49588
            ],

            [
                'name'   => 'Wohnen / Essen',
                'state'  => 57721,
                'demand' => 51260
            ]

        ],


        /*
         * ========================================================
         * OBERGESCHOSS
         * ========================================================
         */

        'OG' => [

            [
                'name'   => 'Bad',
                'state'  => 52749,
                'demand' => 26389
            ],

            [
                'name'   => 'Büro',
                'state'  => 46861,
                'demand' => 43578
            ],

            [
                'name'   => 'Dusche',
                'state'  => 49967,
                'demand' => 36459
            ],

            [
                'name'   => 'Eltern',
                'state'  => 15602,
                'demand' => 26819
            ],

            [
                'name'   => 'Jan',
                'state'  => 27850,
                'demand' => 39276
            ],

            [
                'name'   => 'Lea',
                'state'  => 10375,
                'demand' => 16937
            ]

        ],


        /*
         * ========================================================
         * EINLIEGERWOHNUNG
         * ========================================================
         */

        'ELW' => [

            [
                'name'   => 'Bad',
                'state'  => 33924,
                'demand' => 19263
            ],

            [
                'name'   => 'Küche Essen',
                'state'  => 19131,
                'demand' => 17140
            ],

            [
                'name'   => 'Wohnen',
                'state'  => 58629,
                'demand' => 14321
            ],

            [
                'name'   => 'Zimmer',
                'state'  => 12822,
                'demand' => 20806
            ]

        ]

    ];


    public function Create(): void
    {
        parent::Create();


        /*
         * ============================================================
         * GRUNDWERTE
         * ============================================================
         */

        $this->RegisterPropertyInteger(
            'OutsideTempID',
            50237
        );

        $this->RegisterPropertyInteger(
            'WPFlowID',
            27923
        );

        $this->RegisterPropertyInteger(
            'WPReturnID',
            45419
        );

        $this->RegisterPropertyInteger(
            'BoilerTopID',
            39112
        );

        $this->RegisterPropertyInteger(
            'BoilerBottomID',
            40096
        );

        $this->RegisterPropertyInteger(
            'BufferTopID',
            27553
        );

        $this->RegisterPropertyInteger(
            'BufferMiddleID',
            52270
        );

        $this->RegisterPropertyInteger(
            'BufferBottomID',
            18594
        );


        /*
         * ============================================================
         * HYDRAULIK
         * ============================================================
         */

        $this->RegisterPropertyInteger(
            'FBHFlowID',
            58972
        );

        $this->RegisterPropertyInteger(
            'Q2ID',
            56962
        );


        /*
         * ============================================================
         * DIREKTE OZW-BETRIEBSMELDUNGEN
         * ============================================================
         */

        $this->RegisterPropertyInteger(
            'WPHeatingID',
            44357
        );

        $this->RegisterPropertyInteger(
            'WPCoolingID',
            17689
        );

        $this->RegisterPropertyInteger(
            'WPDHWID',
            21617
        );


        /*
         * ============================================================
         * WP DIAGNOSE
         * ============================================================
         */

        $this->RegisterPropertyInteger(
            'WPCompressor1ID',
            29853
        );

        $this->RegisterPropertyInteger(
            'WPCompressor2ID',
            56852
        );

        $this->RegisterPropertyInteger(
            'WPModulationID',
            20837
        );

        $this->RegisterPropertyInteger(
            'WPFlowRateID',
            16705
        );

        $this->RegisterPropertyInteger(
            'WPElectricalPowerID',
            50450
        );

        $this->RegisterPropertyInteger(
            'WPHeatOutputID',
            32403
        );

        $this->RegisterPropertyInteger(
            'WPCOPID',
            37700
        );

        $this->RegisterPropertyInteger(
            'CondenserPumpID',
            50837
        );

        $this->RegisterPropertyInteger(
            'CondenserPumpSpeedID',
            32715
        );

        $this->RegisterPropertyInteger(
            'SourcePumpID',
            11178
        );

        $this->RegisterPropertyInteger(
            'SourcePumpSpeedID',
            17723
        );

        $this->RegisterPropertyInteger(
            'SourceFlowID',
            41957
        );


        /*
         * ============================================================
         * KOMPAKTVISU
         * ============================================================
         */

        $this->RegisterPropertyInteger(
            'WPStatusID',
            16864
        );


        /*
         * ============================================================
         * ELEKTROHEIZEINSÄTZE
         * ============================================================
         */

        $this->RegisterPropertyInteger(
            'BoilerHeaterID',
            10737
        );

        $this->RegisterPropertyInteger(
            'BufferHeaterID',
            53546
        );


        /*
         * ============================================================
         * UPDATE
         * ============================================================
         */

        $this->RegisterPropertyInteger(
            'RenderDelay',
            800
        );


        /*
         * ============================================================
         * HTMLBOX
         * ============================================================
         */

        $this->RegisterVariableString(
            'HTML',
            'Heizung Visualisierung',
            '~HTMLBox',
            10
        );


        $this->RegisterTimer(
            'RenderTimer',
            0,
            'SBHV_Update($_IPS["TARGET"]);'
        );
    }


    public function ApplyChanges(): void
    {
        parent::ApplyChanges();


        foreach (
            $this->GetObservedVariableIDs()
            as $variableID
        ) {

            if (
                $variableID > 0
                &&
                IPS_VariableExists(
                    $variableID
                )
            ) {

                $this->RegisterMessage(
                    $variableID,
                    VM_UPDATE
                );
            }
        }


        $this->SetTimerInterval(
            'RenderTimer',
            0
        );


        $this->Update();
    }


    public function Update(): void
    {
        $this->SetTimerInterval(
            'RenderTimer',
            0
        );


        $html =
            $this->BuildVisualization();


        $variableID =
            $this->GetIDForIdent(
                'HTML'
            );


        $current =
            GetValueString(
                $variableID
            );


        if ($current !== $html) {

            SetValueString(
                $variableID,
                $html
            );
        }


        $this->SetStatus(
            102
        );
    }


    public function MessageSink(
        $TimeStamp,
        $SenderID,
        $Message,
        $Data
    ): void {

        parent::MessageSink(
            $TimeStamp,
            $SenderID,
            $Message,
            $Data
        );


        if ($Message !== VM_UPDATE) {

            return;
        }


        $delay =
            max(
                250,
                $this->ReadPropertyInteger(
                    'RenderDelay'
                )
            );


        $this->SetTimerInterval(
            'RenderTimer',
            $delay
        );
    }


    /*
     * ============================================================
     * ID MIT FALLBACK
     * ============================================================
     */

    private function PropertyID(
        string $name,
        int $fallback = 0
    ): int {

        $id =
            $this->ReadPropertyInteger(
                $name
            );


        if ($id <= 0) {

            return $fallback;
        }


        return $id;
    }


    /*
     * ============================================================
     * BEOBACHTETE VARIABLEN
     * ============================================================
     */

    private function GetObservedVariableIDs(): array
    {
        $ids = [

            $this->PropertyID(
                'OutsideTempID',
                50237
            ),

            $this->PropertyID(
                'WPFlowID',
                27923
            ),

            $this->PropertyID(
                'WPReturnID',
                45419
            ),

            $this->PropertyID(
                'BoilerTopID',
                39112
            ),

            $this->PropertyID(
                'BoilerBottomID',
                40096
            ),

            $this->PropertyID(
                'BufferTopID',
                27553
            ),

            $this->PropertyID(
                'BufferMiddleID',
                52270
            ),

            $this->PropertyID(
                'BufferBottomID',
                18594
            ),

            $this->PropertyID(
                'FBHFlowID',
                58972
            ),

            $this->PropertyID(
                'Q2ID',
                56962
            ),

            $this->PropertyID(
                'WPHeatingID',
                44357
            ),

            $this->PropertyID(
                'WPCoolingID',
                17689
            ),

            $this->PropertyID(
                'WPDHWID',
                21617
            ),

            $this->PropertyID(
                'WPCompressor1ID',
                29853
            ),

            $this->PropertyID(
                'WPCompressor2ID',
                56852
            ),

            $this->PropertyID(
                'WPModulationID',
                20837
            ),

            $this->PropertyID(
                'WPFlowRateID',
                16705
            ),

            $this->PropertyID(
                'WPElectricalPowerID',
                50450
            ),

            $this->PropertyID(
                'WPHeatOutputID',
                32403
            ),

            $this->PropertyID(
                'WPCOPID',
                37700
            ),

            $this->PropertyID(
                'CondenserPumpID',
                50837
            ),

            $this->PropertyID(
                'CondenserPumpSpeedID',
                32715
            ),

            $this->PropertyID(
                'SourcePumpID',
                11178
            ),

            $this->PropertyID(
                'SourcePumpSpeedID',
                17723
            ),

            $this->PropertyID(
                'SourceFlowID',
                41957
            ),

            $this->PropertyID(
                'WPStatusID',
                16864
            ),

            $this->PropertyID(
                'BoilerHeaterID',
                10737
            ),

            $this->PropertyID(
                'BufferHeaterID',
                53546
            )

        ];


        /*
         * Alle FBH Status- und Stellwertvariablen mitbeobachten.
         */

        foreach (
            self::FBH_CIRCUITS
            as $circuits
        ) {

            foreach (
                $circuits
                as $circuit
            ) {

                $ids[] =
                    (int) $circuit['state'];

                $ids[] =
                    (int) $circuit['demand'];
            }
        }


        return
            array_values(
                array_unique(
                    $ids
                )
            );
    }


    /*
     * ============================================================
     * WERTE LESEN
     * ============================================================
     */

    private function ReadValueSafe(
        int $variableID,
        mixed $default = null
    ): mixed {

        if (
            $variableID <= 0
            ||
            !IPS_VariableExists(
                $variableID
            )
        ) {

            return $default;
        }


        try {

            return
                GetValue(
                    $variableID
                );

        } catch (Throwable $e) {

            return $default;
        }
    }


    private function ReadFloat(
        int $variableID
    ): ?float {

        $value =
            $this->ReadValueSafe(
                $variableID,
                null
            );


        if (
            $value === null
            ||
            !is_numeric(
                $value
            )
        ) {

            return null;
        }


        return
            (float) $value;
    }


    private function ReadInteger(
        int $variableID
    ): ?int {

        $value =
            $this->ReadValueSafe(
                $variableID,
                null
            );


        if (
            $value === null
            ||
            !is_numeric(
                $value
            )
        ) {

            return null;
        }


        return
            (int) round(
                (float) $value
            );
    }


    private function ReadBool(
        int $variableID
    ): bool {

        $value =
            $this->ReadValueSafe(
                $variableID,
                false
            );


        if (is_bool($value)) {

            return $value;
        }


        if (is_numeric($value)) {

            return
                ((float) $value)
                !==
                0.0;
        }


        return false;
    }


    /*
     * ============================================================
     * FBH AUSWERTEN
     * ============================================================
     */

    private function EvaluateCircuits(
        array $circuits
    ): array {

        $total =
            0;

        $open =
            0;

        $demandSum =
            0.0;

        $demandCount =
            0;


        foreach (
            $circuits
            as $circuit
        ) {

            $stateID =
                (int) $circuit['state'];

            $demandID =
                (int) $circuit['demand'];


            if (
                $stateID > 0
                &&
                IPS_VariableExists(
                    $stateID
                )
            ) {

                $total++;


                /*
                 * Physikalisch geprüft:
                 *
                 * true / 1 = offen
                 */
                if (
                    $this->ReadBool(
                        $stateID
                    )
                ) {

                    $open++;
                }
            }


            $demand =
                $this->ReadFloat(
                    $demandID
                );


            if ($demand !== null) {

                /*
                 * KNX Scaling bei unseren MDT-Werten:
                 * bereits als 0...100 angezeigt / geliefert.
                 */

                $demand =
                    max(
                        0.0,
                        min(
                            100.0,
                            $demand
                        )
                    );


                $demandSum +=
                    $demand;

                $demandCount++;
            }
        }


        $averageDemand =
            $demandCount > 0
                ?
                $demandSum
                /
                $demandCount
                :
                0.0;


        return [

            'open' =>
                $open,

            'total' =>
                $total,

            'demand' =>
                $averageDemand,

            'demandSum' =>
                $demandSum,

            'demandCount' =>
                $demandCount

        ];
    }


    private function EvaluateAllCircuits(): array
    {
        $result = [];

        $totalOpen =
            0;

        $totalCircuits =
            0;

        $totalDemandSum =
            0.0;

        $totalDemandCount =
            0;


        foreach (
            self::FBH_CIRCUITS
            as $group =>
            $circuits
        ) {

            $evaluation =
                $this->EvaluateCircuits(
                    $circuits
                );


            $result[
                $group
            ] =
                $evaluation;


            $totalOpen +=
                $evaluation['open'];

            $totalCircuits +=
                $evaluation['total'];

            $totalDemandSum +=
                $evaluation['demandSum'];

            $totalDemandCount +=
                $evaluation['demandCount'];
        }


        $result['TOTAL'] = [

            'open' =>
                $totalOpen,

            'total' =>
                $totalCircuits,

            'demand' =>
                $totalDemandCount > 0
                    ?
                    $totalDemandSum
                    /
                    $totalDemandCount
                    :
                    0.0

        ];


        return $result;
    }


    /*
     * ============================================================
     * FORMATIERUNG
     * ============================================================
     */

    private function FormatTemperature(
        ?float $value
    ): string {

        if ($value === null) {

            return '—';
        }


        return
            number_format(
                $value,
                1,
                '.',
                ''
            )
            .
            ' °C';
    }


    private function FormatPower(
        ?float $value
    ): string {

        if ($value === null) {

            return '—';
        }


        return
            number_format(
                $value,
                2,
                '.',
                ''
            )
            .
            ' kW';
    }


    private function FormatFlow(
        ?float $value
    ): string {

        if ($value === null) {

            return '—';
        }


        return
            number_format(
                $value,
                1,
                '.',
                ''
            )
            .
            ' l/min';
    }


    private function FormatPercent(
        ?float $value
    ): string {

        if ($value === null) {

            return '—';
        }


        return
            number_format(
                $value,
                0,
                '.',
                ''
            )
            .
            ' %';
    }


    private function FormatCOP(
        ?float $value
    ): string {

        if ($value === null) {

            return '—';
        }


        return
            number_format(
                $value,
                2,
                '.',
                ''
            );
    }


    private function FormatValveCount(
        array $evaluation
    ): string {

        return
            $evaluation['open']
            .
            ' / '
            .
            $evaluation['total']
            .
            ' offen';
    }


    private function H(
        string $value
    ): string {

        return
            htmlspecialchars(
                $value,
                ENT_QUOTES |
                ENT_SUBSTITUTE,
                'UTF-8'
            );
    }


    /*
     * ============================================================
     * TEMPLATE
     * ============================================================
     */

    private function LoadTemplate(): string
    {
        $file =
            __DIR__
            .
            '/module.html';


        if (!is_file($file)) {

            return
                '<div style="padding:20px;color:red;">'
                .
                'module.html fehlt'
                .
                '</div>';
        }


        $html =
            file_get_contents(
                $file
            );


        if ($html === false) {

            return
                '<div style="padding:20px;color:red;">'
                .
                'module.html konnte nicht geladen werden'
                .
                '</div>';
        }


        return $html;
    }


    /*
     * ============================================================
     * WP BETRIEB
     * ============================================================
     */

    private function DetermineWPMode(
        bool $heating,
        bool $cooling,
        bool $dhw
    ): array {

        if ($dhw) {

            return [
                'text'  => 'Warmwasser',
                'color' => '#d69b00'
            ];
        }


        if ($cooling) {

            return [
                'text'  => 'Kühlen',
                'color' => '#1685d1'
            ];
        }


        if ($heating) {

            return [
                'text'  => 'Heizen',
                'color' => '#e13946'
            ];
        }


        return [
            'text'  => 'Standby',
            'color' => '#78909c'
        ];
    }


    /*
     * ============================================================
     * BUILD
     * ============================================================
     */

    private function BuildVisualization(): string
    {
        $template =
            $this->LoadTemplate();


        /*
         * ========================================================
         * TEMPERATUREN
         * ========================================================
         */

        $outside =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->PropertyID(
                        'OutsideTempID',
                        50237
                    )
                )
            );


        $wpFlow =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->PropertyID(
                        'WPFlowID',
                        27923
                    )
                )
            );


        $wpReturn =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->PropertyID(
                        'WPReturnID',
                        45419
                    )
                )
            );


        $boilerTop =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->PropertyID(
                        'BoilerTopID',
                        39112
                    )
                )
            );


        $boilerBottom =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->PropertyID(
                        'BoilerBottomID',
                        40096
                    )
                )
            );


        $bufferTop =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->PropertyID(
                        'BufferTopID',
                        27553
                    )
                )
            );


        $bufferMiddle =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->PropertyID(
                        'BufferMiddleID',
                        52270
                    )
                )
            );


        $bufferBottom =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->PropertyID(
                        'BufferBottomID',
                        18594
                    )
                )
            );


        $fbhFlow =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->PropertyID(
                        'FBHFlowID',
                        58972
                    )
                )
            );


        /*
         * ========================================================
         * KOMPAKTSTATUS
         * ========================================================
         */

        $wpActive =
            $this->ReadBool(
                $this->PropertyID(
                    'WPStatusID',
                    16864
                )
            );


        $wpStateCompact =
            $wpActive
                ?
                'Ein'
                :
                'Aus';


        $wpStateGraphic =
            $wpActive
                ?
                'Verdichter Ein'
                :
                'Verdichter Aus';


        $wpActiveClass =
            $wpActive
                ?
                'active'
                :
                '';


        /*
         * ========================================================
         * DIREKTER WP-BETRIEB AUS OZW
         * ========================================================
         */

        $heating =
            $this->ReadBool(
                $this->PropertyID(
                    'WPHeatingID',
                    44357
                )
            );


        $cooling =
            $this->ReadBool(
                $this->PropertyID(
                    'WPCoolingID',
                    17689
                )
            );


        $dhw =
            $this->ReadBool(
                $this->PropertyID(
                    'WPDHWID',
                    21617
                )
            );


        $wpMode =
            $this->DetermineWPMode(
                $heating,
                $cooling,
                $dhw
            );


        /*
         * ========================================================
         * Q2
         * ========================================================
         */

        $q2Active =
            $this->ReadBool(
                $this->PropertyID(
                    'Q2ID',
                    56962
                )
            );


        $q2Text =
            $q2Active
                ?
                'Ein'
                :
                'Aus';


        $q2Color =
            $q2Active
                ?
                '#25b879'
                :
                '#78909c';


        /*
         * ========================================================
         * VERDICHTER
         * ========================================================
         */

        $compressor1 =
            $this->ReadBool(
                $this->PropertyID(
                    'WPCompressor1ID',
                    29853
                )
            );


        $compressor2 =
            $this->ReadBool(
                $this->PropertyID(
                    'WPCompressor2ID',
                    56852
                )
            );


        $compressorActive =
            $compressor1
            ||
            $compressor2;


        $compressorText =
            $compressorActive
                ?
                'Ein'
                :
                'Aus';


        $compressorColor =
            $compressorActive
                ?
                '#25b879'
                :
                '#78909c';


        /*
         * ========================================================
         * WP-DIAGNOSE
         * ========================================================
         */

        $modulation =
            $this->FormatPercent(
                $this->ReadFloat(
                    $this->PropertyID(
                        'WPModulationID',
                        20837
                    )
                )
            );


        $flowRate =
            $this->FormatFlow(
                $this->ReadFloat(
                    $this->PropertyID(
                        'WPFlowRateID',
                        16705
                    )
                )
            );


        $electricalPower =
            $this->FormatPower(
                $this->ReadFloat(
                    $this->PropertyID(
                        'WPElectricalPowerID',
                        50450
                    )
                )
            );


        $heatOutput =
            $this->FormatPower(
                $this->ReadFloat(
                    $this->PropertyID(
                        'WPHeatOutputID',
                        32403
                    )
                )
            );


        $cop =
            $this->FormatCOP(
                $this->ReadFloat(
                    $this->PropertyID(
                        'WPCOPID',
                        37700
                    )
                )
            );


        /*
         * ========================================================
         * INTERNE WP-PUMPEN
         * ========================================================
         */

        $condenserPump =
            $this->ReadBool(
                $this->PropertyID(
                    'CondenserPumpID',
                    50837
                )
            );


        $condenserPumpText =
            $condenserPump
                ?
                'Ein'
                :
                'Aus';


        $condenserPumpSpeed =
            $this->FormatPercent(
                $this->ReadFloat(
                    $this->PropertyID(
                        'CondenserPumpSpeedID',
                        32715
                    )
                )
            );


        $sourcePump =
            $this->ReadBool(
                $this->PropertyID(
                    'SourcePumpID',
                    11178
                )
            );


        $sourcePumpText =
            $sourcePump
                ?
                'Ein'
                :
                'Aus';


        $sourcePumpSpeed =
            $this->FormatPercent(
                $this->ReadFloat(
                    $this->PropertyID(
                        'SourcePumpSpeedID',
                        17723
                    )
                )
            );


        $sourceFlow =
            $this->FormatFlow(
                $this->ReadFloat(
                    $this->PropertyID(
                        'SourceFlowID',
                        41957
                    )
                )
            );


        /*
         * ========================================================
         * HEIZSTÄBE
         * ========================================================
         */

        $boilerHeater =
            $this->ReadBool(
                $this->PropertyID(
                    'BoilerHeaterID',
                    10737
                )
            );


        $bufferHeater =
            $this->ReadBool(
                $this->PropertyID(
                    'BufferHeaterID',
                    53546
                )
            );


        $boilerHeaterClass =
            $boilerHeater
                ?
                'active'
                :
                '';


        $bufferHeaterClass =
            $bufferHeater
                ?
                'active'
                :
                '';


        $boilerHeaterText =
            $boilerHeater
                ?
                'Ein'
                :
                'Aus';


        $bufferHeaterText =
            $bufferHeater
                ?
                'Ein'
                :
                'Aus';


        /*
         * ========================================================
         * FBH-VENTILE
         * ========================================================
         */

        $fbh =
            $this->EvaluateAllCircuits();


        $egValves =
            $this->FormatValveCount(
                $fbh['EG']
            );


        $ogValves =
            $this->FormatValveCount(
                $fbh['OG']
            );


        $elwValves =
            $this->FormatValveCount(
                $fbh['ELW']
            );


        $totalValves =
            $this->FormatValveCount(
                $fbh['TOTAL']
            );


        $egDemand =
            $this->FormatPercent(
                $fbh['EG']['demand']
            );


        $ogDemand =
            $this->FormatPercent(
                $fbh['OG']['demand']
            );


        $elwDemand =
            $this->FormatPercent(
                $fbh['ELW']['demand']
            );


        $totalDemand =
            $this->FormatPercent(
                $fbh['TOTAL']['demand']
            );


        /*
         * ========================================================
         * PLACEHOLDER
         * ========================================================
         */

        $replace = [

            '{{OUTSIDE}}' =>
                $this->H(
                    $outside
                ),

            '{{WP_FLOW}}' =>
                $this->H(
                    $wpFlow
                ),

            '{{WP_RETURN}}' =>
                $this->H(
                    $wpReturn
                ),

            '{{BOILER_TOP}}' =>
                $this->H(
                    $boilerTop
                ),

            '{{BOILER_BOTTOM}}' =>
                $this->H(
                    $boilerBottom
                ),

            '{{BUFFER_TOP}}' =>
                $this->H(
                    $bufferTop
                ),

            '{{BUFFER_MIDDLE}}' =>
                $this->H(
                    $bufferMiddle
                ),

            '{{BUFFER_BOTTOM}}' =>
                $this->H(
                    $bufferBottom
                ),

            '{{FBH_FLOW}}' =>
                $this->H(
                    $fbhFlow
                ),

            '{{WP_STATE_COMPACT}}' =>
                $this->H(
                    $wpStateCompact
                ),

            '{{WP_STATE_GRAPHIC}}' =>
                $this->H(
                    $wpStateGraphic
                ),

            '{{WP_ACTIVE_CLASS}}' =>
                $wpActiveClass,

            '{{WP_MODE}}' =>
                $this->H(
                    $wpMode['text']
                ),

            '{{WP_MODE_COLOR}}' =>
                $wpMode['color'],

            '{{Q2_STATE}}' =>
                $this->H(
                    $q2Text
                ),

            '{{Q2_COLOR}}' =>
                $q2Color,

            '{{COMPRESSOR_STATE}}' =>
                $this->H(
                    $compressorText
                ),

            '{{COMPRESSOR_COLOR}}' =>
                $compressorColor,

            '{{WP_MODULATION}}' =>
                $this->H(
                    $modulation
                ),

            '{{WP_FLOW_RATE}}' =>
                $this->H(
                    $flowRate
                ),

            '{{WP_ELECTRICAL_POWER}}' =>
                $this->H(
                    $electricalPower
                ),

            '{{WP_HEAT_OUTPUT}}' =>
                $this->H(
                    $heatOutput
                ),

            '{{WP_COP}}' =>
                $this->H(
                    $cop
                ),

            '{{CONDENSER_PUMP}}' =>
                $this->H(
                    $condenserPumpText
                ),

            '{{CONDENSER_PUMP_SPEED}}' =>
                $this->H(
                    $condenserPumpSpeed
                ),

            '{{SOURCE_PUMP}}' =>
                $this->H(
                    $sourcePumpText
                ),

            '{{SOURCE_PUMP_SPEED}}' =>
                $this->H(
                    $sourcePumpSpeed
                ),

            '{{SOURCE_FLOW}}' =>
                $this->H(
                    $sourceFlow
                ),

            '{{BOILER_HEATER_CLASS}}' =>
                $boilerHeaterClass,

            '{{BUFFER_HEATER_CLASS}}' =>
                $bufferHeaterClass,

            '{{BOILER_HEATER_STATE}}' =>
                $this->H(
                    $boilerHeaterText
                ),

            '{{BUFFER_HEATER_STATE}}' =>
                $this->H(
                    $bufferHeaterText
                ),

            '{{EG_VALVES}}' =>
                $this->H(
                    $egValves
                ),

            '{{OG_VALVES}}' =>
                $this->H(
                    $ogValves
                ),

            '{{ELW_VALVES}}' =>
                $this->H(
                    $elwValves
                ),

            '{{TOTAL_VALVES}}' =>
                $this->H(
                    $totalValves
                ),

            '{{EG_DEMAND}}' =>
                $this->H(
                    $egDemand
                ),

            '{{OG_DEMAND}}' =>
                $this->H(
                    $ogDemand
                ),

            '{{ELW_DEMAND}}' =>
                $this->H(
                    $elwDemand
                ),

            '{{TOTAL_DEMAND}}' =>
                $this->H(
                    $totalDemand
                )

        ];


        return
            strtr(
                $template,
                $replace
            );
    }
}
