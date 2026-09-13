<?php

declare(strict_types=1);

class SulzbannHeizungVisualisierung extends IPSModule
{
    /*
     * ============================================================
     * CREATE
     * ============================================================
     */

    public function Create(): void
    {
        parent::Create();


        /*
         * --------------------------------------------------------
         * DATENPUNKTE
         * --------------------------------------------------------
         *
         * Defaultwerte entsprechen deinem aktuellen System.
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
         * B1 noch nicht eindeutig zugeordnet.
         */
        $this->RegisterPropertyInteger(
            'FBHFlowID',
            0
        );


        $this->RegisterPropertyInteger(
            'WPStatusID',
            16864
        );


        /*
         * Flexible Verbraucher aus kleiner Visu.
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
         * Mehrere unmittelbar folgende Telegramme
         * werden zu einem Render-Vorgang zusammengefasst.
         */

        $this->RegisterPropertyInteger(
            'RenderDelay',
            800
        );


        /*
         * ========================================================
         * EINE HTMLBOX
         * ========================================================
         *
         * Genau wie bei der funktionierenden Wettervisu.
         */

        $this->RegisterVariableString(
            'HTML',
            'Heizung Visualisierung',
            '~HTMLBox',
            10
        );


        /*
         * Timer standardmässig aus.
         */

        $this->RegisterTimer(
            'RenderTimer',
            0,
            'SBHV_Update($_IPS["TARGET"]);'
        );
    }


    /*
     * ============================================================
     * APPLY CHANGES
     * ============================================================
     */

    public function ApplyChanges(): void
    {
        parent::ApplyChanges();


        /*
         * Alle verwendeten Variablen beobachten.
         */

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


        /*
         * Timer sicher deaktivieren.
         */

        $this->SetTimerInterval(
            'RenderTimer',
            0
        );


        /*
         * Einmal sofort aufbauen.
         */

        $this->Update();
    }


    /*
     * ============================================================
     * ÖFFENTLICHES UPDATE
     * ============================================================
     */

    public function Update(): void
    {
        /*
         * Sammel-Timer ausschalten.
         */

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


        /*
         * Nur schreiben, wenn sich wirklich etwas geändert hat.
         *
         * Dadurch keine dauernden unnötigen HTMLBox-Reloads.
         */

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


    /*
     * ============================================================
     * MESSAGE SINK
     * ============================================================
     */

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


        /*
         * Mehrere Telegramme bündeln.
         */

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
     * BEOBACHTETE VARIABLEN
     * ============================================================
     */

    private function GetObservedVariableIDs(): array
    {
        return [

            $this->ReadPropertyInteger(
                'OutsideTempID'
            ),

            $this->ReadPropertyInteger(
                'WPFlowID'
            ),

            $this->ReadPropertyInteger(
                'WPReturnID'
            ),

            $this->ReadPropertyInteger(
                'BoilerTopID'
            ),

            $this->ReadPropertyInteger(
                'BoilerBottomID'
            ),

            $this->ReadPropertyInteger(
                'BufferTopID'
            ),

            $this->ReadPropertyInteger(
                'BufferMiddleID'
            ),

            $this->ReadPropertyInteger(
                'BufferBottomID'
            ),

            $this->ReadPropertyInteger(
                'FBHFlowID'
            ),

            $this->ReadPropertyInteger(
                'WPStatusID'
            ),

            $this->ReadPropertyInteger(
                'BoilerHeaterID'
            ),

            $this->ReadPropertyInteger(
                'BufferHeaterID'
            )

        ];
    }


    /*
     * ============================================================
     * WERT SICHER LESEN
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

            return GetValue(
                $variableID
            );

        } catch (Throwable $e) {

            return $default;
        }
    }


    /*
     * ============================================================
     * FLOAT
     * ============================================================
     */

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


        return (float) $value;
    }


    /*
     * ============================================================
     * BOOL
     * ============================================================
     */

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
     * TEMPERATUR
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


    /*
     * ============================================================
     * HTML ESCAPE
     * ============================================================
     */

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


    /*
     * ============================================================
     * TEMPLATE LADEN
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
     * VISUALISIERUNG AUFBAUEN
     * ============================================================
     */

    private function BuildVisualization(): string
    {
        $template =
            $this->LoadTemplate();


        /*
         * --------------------------------------------------------
         * WERTE
         * --------------------------------------------------------
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
         * WP Status.
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


        $wpStateDetail =
            $wpActive
                ?
                'Verdichter Ein'
                :
                'Verdichter Aus';


        $wpDotColor =
            $wpActive
                ?
                '#24d68a'
                :
                '#78929f';


        /*
         * Heizstäbe.
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
         * --------------------------------------------------------
         * TEMPLATE-PLATZHALTER
         * --------------------------------------------------------
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
                $wpStateCompact,

            '{{WP_STATE_DETAIL}}' =>
                $wpStateDetail,

            '{{WP_DOT_COLOR}}' =>
                $wpDotColor,

            '{{BOILER_HEATER}}' =>
                $boilerHeaterText,

            '{{BUFFER_HEATER}}' =>
                $bufferHeaterText

        ];


        return strtr(
            $template,
            $replace
        );
    }
}
