<?php

namespace Iyzico\IyzipayWoocommerce\Checkout;

use Iyzipay\Model\CheckoutFormInitialize;

class CheckoutView
{
    private const LOADING_ID = 'loadingBar';
    private const INFO_BOX_ID = 'infoBox';
    private const CHECKOUT_FORM_ID = 'iyzipay-checkout-form';
    
    private CheckoutSettings $checkoutSettings;
    
    public function __construct()
    {
        $this->checkoutSettings = new CheckoutSettings();
    }
    
    /**
     * @return void
     */
    public function renderCheckoutForm(CheckoutFormInitialize $checkoutFormInitialize): void
    {
        if ($checkoutFormInitialize->getStatus() === 'success') {
            // İlk önce info box'ı render et
            $this->renderInfoBox();
            
            // Sonra form container'ı render et
            $className = $this->checkoutSettings->findByKey('form_class') ?? 'popup';
            printf(
                '<div id="%s" class="%s" style="display:none">%s</div>',
                esc_attr(self::CHECKOUT_FORM_ID),
                esc_attr($className),
                $checkoutFormInitialize->getCheckoutFormContent()
            );
            
            // En son UI kontrolü için JavaScript'i render et
            $this->renderUiControlScript();
        } else {
            echo esc_html($checkoutFormInitialize->getErrorMessage());
        }
    }

    /**
     * @return void
     */
    public function renderLoadingHtml(): void
    {
        printf(
            '<div id="%s">
                <div class="loading"></div>
                <div class="brand">
                    <p>iyzico</p>
                </div>
            </div>',
            esc_attr(self::LOADING_ID)
        );
    }
    
    /**
     * @return void
     */
    private function renderInfoBox(): void
    {
        $paymentValue = $this->checkoutSettings->findByKey('payment_checkout_value');
        printf(
            '<p id="%s" style="display:none">%s</p>',
            esc_attr(self::INFO_BOX_ID),
            esc_html($paymentValue)
        );
    }
    
    /**
     * @return void
     */
    private function renderUiControlScript(): void
    {
        ?>
        <script type="text/javascript">
            var checkIyziInit = function() {
                if (typeof iyziInit !== 'undefined') {
                    document.getElementById('<?php echo esc_js(self::LOADING_ID); ?>').style.display = 'none';
                    document.getElementById('<?php echo esc_js(self::INFO_BOX_ID); ?>').style.display = 'block';
                    document.getElementById('<?php echo esc_js(self::CHECKOUT_FORM_ID); ?>').style.display = 'block';
                    return;
                }
                // Henüz yüklenmediyse tekrar dene
                setTimeout(checkIyziInit, 100);
            };

            // Sayfa yüklendiğinde kontrol etmeye başla
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', checkIyziInit);
            } else {
                checkIyziInit();
            }
        </script>
        <?php
    }
}