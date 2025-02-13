<?php

namespace Iyzico\IyzipayWoocommerce\Admin;

class SettingsPage
{
    public function getHtmlContent()
    {
        $html = '<style scoped>
                    @media (max-width:768px){.iyziBrand{position:fixed;bottom:0;top:auto!important;right:0!important}}
                </style>
                <div class="iyziBrandWrap">
                    <div class="iyziBrand" style="clear:both;position:absolute;right: 50px;top:440px;display: flex;flex-direction: column;justify-content: center;">
                        <img src=' . PLUGIN_URL . '/assets/images/iyzico_logo.png style="width: 250px;margin-left: auto;">
                        <p style="text-align:center;"><strong>Version: </strong>' . IYZICO_PLUGIN_VERSION . '</p>
                    </div>
                </div>';
        echo $html;
    }
}