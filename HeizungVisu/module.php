<?php

declare(strict_types=1);

class SulzbannHeizungVisualisierung extends IPSModule
{
    public function Create(): void
    {
        parent::Create();

        /*
         * ============================================================
         * BESTEHENDE VISU-WERTE
         * ============================================================
         */

        $this->RegisterPropertyInteger('OutsideTempID', 50237);

        $this->RegisterPropertyInteger('WPFlowID', 27923);
        $this->RegisterPropertyInteger('WPReturnID', 45419);

        $this->RegisterPropertyInteger('BoilerTopID', 39112);
        $this->RegisterPropertyInteger('BoilerBottomID', 40096);

        $this->RegisterPropertyInteger('BufferTopID', 27553);
        $this->RegisterPropertyInteger('BufferMiddleID', 52270);
        $this->RegisterPropertyInteger('BufferBottomID', 18594);

        $this->RegisterPropertyInteger('WPStatusID', 16864);

        $this->RegisterPropertyInteger('BoilerHeaterID', 10737);
        $this->RegisterPropertyInteger('BufferHeaterID', 53546);


        /*
         * ============================================================
         * HYDRAULIK / GROSSVISU
         * ============================================================
         */

        /*
         * B1:
         * Vorlauffühler FBH
         */
        $this->RegisterPropertyInteger(
            'FBHFlowID',
            58972
        );

        /*
         * Q2:
         * Heizkreis-/Mischerkreispumpe
         */
        $this->RegisterPropertyInteger(
            'Q2ID',
            56962
        );


        /*
         * ============================================================
         * DIREKTE WP-BETRIEBSMELDUNGEN AUS OZW
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
         * WP DIAGNOSEWERTE AUS OZW
         * ============================================================
         */

        $this->RegisterPropertyInteger(
            'WPCompressor1ID',
            29853
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


        foreach ($this->GetObservedVariableIDs() as $variableID) {

            if (
                $variableID > 0
                &&
                IPS_VariableExists($variableID)
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


    private function GetObservedVariableIDs(): array
    {
        return [

            $this->ReadPropertyInteger('OutsideTempID'),

            $this->ReadPropertyInteger('WPFlowID'),
            $this->ReadPropertyInteger('WPReturnID'),

            $this->ReadPropertyInteger('BoilerTopID'),
            $this->ReadPropertyInteger('BoilerBottomID'),

            $this->ReadPropertyInteger('BufferTopID'),
            $this->ReadPropertyInteger('BufferMiddleID'),
            $this->ReadPropertyInteger('BufferBottomID'),

            $this->ReadPropertyInteger('FBHFlowID'),
            $this->ReadPropertyInteger('Q2ID'),

            $this->ReadPropertyInteger('WPStatusID'),

            $this->ReadPropertyInteger('BoilerHeaterID'),
            $this->ReadPropertyInteger('BufferHeaterID'),

            $this->ReadPropertyInteger('WPHeatingID'),
            $this->ReadPropertyInteger('WPCoolingID'),
            $this->ReadPropertyInteger('WPDHWID'),

            $this->ReadPropertyInteger('WPCompressor1ID'),
            $this->ReadPropertyInteger('WPModulationID'),
            $this->ReadPropertyInteger('WPFlowRateID'),

            $this->ReadPropertyInteger('WPElectricalPowerID'),
            $this->ReadPropertyInteger('WPHeatOutputID'),
            $this->ReadPropertyInteger('WPCOPID'),

            $this->ReadPropertyInteger('CondenserPumpID'),
            $this->ReadPropertyInteger('CondenserPumpSpeedID'),

            $this->ReadPropertyInteger('SourcePumpID'),
            $this->ReadPropertyInteger('SourcePumpSpeedID'),

            $this->ReadPropertyInteger('SourceFlowID')

        ];
    }


    private function ReadValueSafe(
        int $variableID,
        mixed $default = null
    ): mixed {

        if (
            $variableID <= 0
            ||
            !IPS_VariableExists($variableID)
        ) {

            return $default;
        }


        try {

            return GetValue(
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
            !is_numeric($value)
        ) {

            return null;
        }


        return (float) $value;
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
            !is_numeric($value)
        ) {

            return null;
        }


        return (int) $value;
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
        ?int $value
    ): string {

        if ($value === null) {

            return '—';
        }


        return
            $value
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


    private function H(
        string $value
    ): string {

        return htmlspecialchars(
            $value,
            ENT_QUOTES |
            ENT_SUBSTITUTE,
            'UTF-8'
        );
    }


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


    private function DetermineWPMode(
        bool $heating,
        bool $cooling,
        bool $dhw
    ): array {

        /*
         * Direkte OZW-Betriebsmeldungen der Wärmepumpe.
         *
         * Priorität Warmwasser > Kühlen > Heizen.
         */

        if ($dhw) {

            return [
                'text'  => 'Warmwasser',
                'class' => 'mode-dhw',
                'color' => '#ffc928'
            ];
        }


        if ($cooling) {

            return [
                'text'  => 'Kühlen',
                'class' => 'mode-cooling',
                'color' => '#35a9ff'
            ];
        }


        if ($heating) {

            return [
                'text'  => 'Heizen',
                'class' => 'mode-heating',
                'color' => '#ff5a55'
            ];
        }


        return [
            'text'  => 'Standby',
            'class' => 'mode-standby',
            'color' => '#78909c'
        ];
    }


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
                    $this->ReadPropertyInteger(
                        'OutsideTempID'
                    )
                )
            );


        $wpFlow =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->ReadPropertyInteger(
                        'WPFlowID'
                    )
                )
            );


        $wpReturn =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->ReadPropertyInteger(
                        'WPReturnID'
                    )
                )
            );


        $boilerTop =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->ReadPropertyInteger(
                        'BoilerTopID'
                    )
                )
            );


        $boilerBottom =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->ReadPropertyInteger(
                        'BoilerBottomID'
                    )
                )
            );


        $bufferTop =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->ReadPropertyInteger(
                        'BufferTopID'
                    )
                )
            );


        $bufferMiddle =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->ReadPropertyInteger(
                        'BufferMiddleID'
                    )
                )
            );


        $bufferBottom =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->ReadPropertyInteger(
                        'BufferBottomID'
                    )
                )
            );


        $fbhFlow =
            $this->FormatTemperature(
                $this->ReadFloat(
                    $this->ReadPropertyInteger(
                        'FBHFlowID'
                    )
                )
            );


        /*
         * ========================================================
         * KOMPAKTVISU WP STATUS
         * ========================================================
         */

        $wpActive =
            $this->ReadBool(
                $this->ReadPropertyInteger(
                    'WPStatusID'
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


        $wpDotColor =
            $wpActive
                ?
                '#25db90'
                :
                '#78909c';


        /*
         * ========================================================
         * DIREKTER WP MODUS AUS OZW
         * ========================================================
         */

        $heating =
            $this->ReadBool(
                $this->ReadPropertyInteger(
                    'WPHeatingID'
                )
            );


        $cooling =
            $this->ReadBool(
                $this->ReadPropertyInteger(
                    'WPCoolingID'
                )
            );


        $dhw =
            $this->ReadBool(
                $this->ReadPropertyInteger(
                    'WPDHWID'
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
                $this->ReadPropertyInteger(
                    'Q2ID'
                )
            );


        $q2Text =
            $q2Active
                ?
                'Ein'
                :
                'Aus';


        $q2Class =
            $q2Active
                ?
                'active'
                :
                '';


        $q2Color =
            $q2Active
                ?
                '#25db90'
                :
                '#78909c';


        /*
         * ========================================================
         * DIAGNOSE
         * ========================================================
         */

        $compressor =
            $this->ReadBool(
                $this->ReadPropertyInteger(
                    'WPCompressor1ID'
                )
            );


        $compressorText =
            $compressor
                ?
                'Ein'
                :
                'Aus';


        $modulation =
            $this->FormatPercent(
                $this->ReadInteger(
                    $this->ReadPropertyInteger(
                        'WPModulationID'
                    )
                )
            );


        $flowRate =
            $this->FormatFlow(
                $this->ReadFloat(
                    $this->ReadPropertyInteger(
                        'WPFlowRateID'
                    )
                )
            );


        $electricalPower =
            $this->FormatPower(
                $this->ReadFloat(
                    $this->ReadPropertyInteger(
                        'WPElectricalPowerID'
                    )
                )
            );


        $heatOutput =
            $this->FormatPower(
                $this->ReadFloat(
                    $this->ReadPropertyInteger(
                        'WPHeatOutputID'
                    )
                )
            );


        $cop =
            $this->FormatCOP(
                $this->ReadFloat(
                    $this->ReadPropertyInteger(
                        'WPCOPID'
                    )
                )
            );


        $condenserPump =
            $this->ReadBool(
                $this->ReadPropertyInteger(
                    'CondenserPumpID'
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
                $this->ReadInteger(
                    $this->ReadPropertyInteger(
                        'CondenserPumpSpeedID'
                    )
                )
            );


        $sourcePump =
            $this->ReadBool(
                $this->ReadPropertyInteger(
                    'SourcePumpID'
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
                $this->ReadInteger(
                    $this->ReadPropertyInteger(
                        'SourcePumpSpeedID'
                    )
                )
            );


        $sourceFlow =
            $this->FormatFlow(
                $this->ReadFloat(
                    $this->ReadPropertyInteger(
                        'SourceFlowID'
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
                $this->ReadPropertyInteger(
                    'BoilerHeaterID'
                )
            );


        $bufferHeater =
            $this->ReadBool(
                $this->ReadPropertyInteger(
                    'BufferHeaterID'
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


        /*
         * ========================================================
         * PLATZHALTER
         * ========================================================
         */

        $replace = [

            '{{OUTSIDE}}' =>
                $this->H($outside),

            '{{WP_FLOW}}' =>
                $this->H($wpFlow),

            '{{WP_RETURN}}' =>
                $this->H($wpReturn),

            '{{BOILER_TOP}}' =>
                $this->H($boilerTop),

            '{{BOILER_BOTTOM}}' =>
                $this->H($boilerBottom),

            '{{BUFFER_TOP}}' =>
                $this->H($bufferTop),

            '{{BUFFER_MIDDLE}}' =>
                $this->H($bufferMiddle),

            '{{BUFFER_BOTTOM}}' =>
                $this->H($bufferBottom),

            '{{FBH_FLOW}}' =>
                $this->H($fbhFlow),

            '{{WP_STATE_COMPACT}}' =>
                $this->H($wpStateCompact),

            '{{WP_STATE_GRAPHIC}}' =>
                $this->H($wpStateGraphic),

            '{{WP_ACTIVE_CLASS}}' =>
                $wpActiveClass,

            '{{WP_DOT_COLOR}}' =>
                $wpDotColor,

            '{{WP_MODE}}' =>
                $this->H(
                    $wpMode['text']
                ),

            '{{WP_MODE_CLASS}}' =>
                $wpMode['class'],

            '{{WP_MODE_COLOR}}' =>
                $wpMode['color'],

            '{{Q2_STATE}}' =>
                $this->H($q2Text),

            '{{Q2_CLASS}}' =>
                $q2Class,

            '{{Q2_COLOR}}' =>
                $q2Color,

            '{{COMPRESSOR_STATE}}' =>
                $this->H($compressorText),

            '{{WP_MODULATION}}' =>
                $this->H($modulation),

            '{{WP_FLOW_RATE}}' =>
                $this->H($flowRate),

            '{{WP_ELECTRICAL_POWER}}' =>
                $this->H($electricalPower),

            '{{WP_HEAT_OUTPUT}}' =>
                $this->H($heatOutput),

            '{{WP_COP}}' =>
                $this->H($cop),

            '{{CONDENSER_PUMP}}' =>
                $this->H($condenserPumpText),

            '{{CONDENSER_PUMP_SPEED}}' =>
                $this->H($condenserPumpSpeed),

            '{{SOURCE_PUMP}}' =>
                $this->H($sourcePumpText),

            '{{SOURCE_PUMP_SPEED}}' =>
                $this->H($sourcePumpSpeed),

            '{{SOURCE_FLOW}}' =>
                $this->H($sourceFlow),

            '{{BOILER_HEATER_CLASS}}' =>
                $boilerHeaterClass,

            '{{BUFFER_HEATER_CLASS}}' =>
                $bufferHeaterClass

        ];


        return
            strtr(
                $template,
                $replace
            );
    }
}
