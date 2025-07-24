<?php

namespace Iyzico\IyzipayWoocommerce\Common\Helpers;

use Iyzico\IyzipayWoocommerce\Checkout\CheckoutSettings;

class GoogleProductsXml
{
    private $xmlContent;
    private $siteUrl;
    private $products;
    private $remotePostUrl;
    private $logger;
    protected $checkoutSettings;

    public function __construct()
    {
        $this->siteUrl = get_site_url();
        $this->remotePostUrl = 'https://xml.iyzitest.com/save';
        $this->logger = new \Iyzico\IyzipayWoocommerce\Common\Helpers\Logger();
        $this->checkoutSettings = new CheckoutSettings();
        
        // API ortamını kontrol et
        $this->checkApiEnvironment();
    }

    /**
     * Check API environment and disable remote sending for sandbox
     */
    private function checkApiEnvironment()
    {
        $api_type = $this->checkoutSettings->findByKey('api_type');
        
        if ($api_type === 'https://sandbox-api.iyzipay.com') {
            $this->remotePostUrl = '';
            $this->logger->info('Iyzico Google XML: Sandbox environment detected, remote sending disabled');
        } else {
            $this->logger->info('Iyzico Google XML: Live environment detected, remote sending enabled');
        }
    }

    /**
     * Generate Google Products XML from WooCommerce products
     *
     * @return string XML content
     */
    public function generateXml()
    {
        $this->logger->info('Iyzico Google XML: Starting XML generation process');
        
        $this->products = $this->getWooCommerceProducts();
        $this->logger->info('Iyzico Google XML: Found ' . count($this->products) . ' products to process');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . "\n";
        $xml .= '    <channel>' . "\n";
        $xml .= '        <title>' . esc_html(get_bloginfo('name')) . ' - Google Products</title>' . "\n";
        $xml .= '        <link>' . esc_url($this->siteUrl) . '</link>' . "\n";
        $xml .= '        <description>Google Products XML Feed for ' . esc_html(get_bloginfo('name')) . '</description>' . "\n";
        $xml .= '        <lastBuildDate>' . date('r') . '</lastBuildDate>' . "\n";
        
        foreach ($this->products as $product) {
            $xml .= $this->generateProductXml($product);
        }
        
        $xml .= '    </channel>' . "\n";
        $xml .= '</rss>';
        
        $this->xmlContent = $xml;
        
        $this->saveXmlFile();
        
        update_option('iyzico_google_products_xml_last_update', current_time('timestamp'));
        
        $next_send_time = get_option('iyzico_google_products_next_send_time', 0);
        
        // Eğer next_send_time 0 ise (hiç ayarlanmamışsa), ilk gönderim için hazırla
        if ($next_send_time == 0) {
            $this->logger->info('Iyzico Google XML: Next send time not set, preparing for first send');
        } else {
            $this->logger->info('Iyzico Google XML: Next send time: ' . date('d.m.Y - H:i:s', (int)$next_send_time) . ' (Current time: ' . date('d.m.Y - H:i:s') . ')');
        }
        
        if (!empty($this->remotePostUrl) && ($next_send_time == 0 || time() >= (int)$next_send_time)) {
            $this->logger->info('Iyzico Google XML: Attempting to send XML to remote server');
            $this->sendToRemoteServer();
        } else if ($next_send_time > 0 && time() < (int)$next_send_time) {
            $this->logger->info('Iyzico Google XML: Remote server send is delayed until ' . date('d.m.Y - H:i:s', (int)$next_send_time));
        } else if (empty($this->remotePostUrl)) {
            $this->logger->warning('Iyzico Google XML: Remote post URL is empty, skipping send');
        }
        
        return $xml;
    }

    /**
     * Generate XML for a single product
     *
     * @param WC_Product $product
     * @return string
     */
    private function generateProductXml($product)
    {
        if (!$product || !is_object($product)) {
            $this->logger->error('Iyzico Google XML: Product is null or not an object.');
            return '';
        }
        
        $xml = '        <item>' . "\n";
        $xml .= '            <g:id>' . esc_html($product->get_id()) . '</g:id>' . "\n";
        $xml .= '            <g:title>' . $this->cleanXmlContent($product->get_name()) . '</g:title>' . "\n";
        $xml .= '            <g:description>' . $this->cleanXmlContent(wp_strip_all_tags($product->get_description())) . '</g:description>' . "\n";
        $xml .= '            <g:link>' . esc_url($product->get_permalink()) . '</g:link>' . "\n";
        
        // Image link
        $image_id = $product->get_image_id();
        if ($image_id) {
            $image_url = wp_get_attachment_url($image_id);
            $xml .= '            <g:image_link>' . esc_url($image_url) . '</g:image_link>' . "\n";
        }
        
        // Price
        $price = $product->get_price();
        if ($price) {
            $currency = get_woocommerce_currency();
            $xml .= '            <g:price>' . esc_html($price . ' ' . $currency) . '</g:price>' . "\n";
        }
        
        // Sale price
        if ($product->is_on_sale()) {
            $sale_price = $product->get_sale_price();
            if ($sale_price) {
                $currency = get_woocommerce_currency();
                $xml .= '            <g:sale_price>' . esc_html($sale_price . ' ' . $currency) . '</g:sale_price>' . "\n";
            }
        }
        
        // Availability
        $availability = $product->is_in_stock() ? 'in_stock' : 'out_of_stock';
        $xml .= '            <g:availability>' . esc_html($availability) . '</g:availability>' . "\n";
        
        // Condition
        $xml .= '            <g:condition>new</g:condition>' . "\n";
        
        // Brand
        $brand = $this->getProductBrand($product);
        if ($brand) {
            $xml .= '            <g:brand>' . $this->cleanXmlContent($brand) . '</g:brand>' . "\n";
        }
        
        // GTIN
        $gtin = $this->getProductGtin($product);
        if ($gtin) {
            $xml .= '            <g:gtin>' . $this->cleanXmlContent($gtin) . '</g:gtin>' . "\n";
        }
        
        // MPN
        $mpn = $this->getProductMpn($product);
        if ($mpn) {
            $xml .= '            <g:mpn>' . $this->cleanXmlContent($mpn) . '</g:mpn>' . "\n";
        }
        
        // Google Product Category
        $google_category = $this->getProductCategory($product);
        if ($google_category) {
            $xml .= '            <g:google_product_category>' . $this->cleanXmlContent($google_category) . '</g:google_product_category>' . "\n";
        }
        
        // Additional image links
        $gallery_ids = $product->get_gallery_image_ids();
        foreach ($gallery_ids as $gallery_id) {
            $gallery_url = wp_get_attachment_url($gallery_id);
            if ($gallery_url) {
                $xml .= '            <g:additional_image_link>' . esc_url($gallery_url) . '</g:additional_image_link>' . "\n";
            }
        }
        
        $xml .= '        </item>' . "\n";
        
        return $xml;
    }

    /**
     * Get WooCommerce products
     *
     * @return array
     */
    private function getWooCommerceProducts()
    {
        $products = array();
        
        // First try with WP_Query to get all published products
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        );
        
        $query = new \WP_Query($args);
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product = wc_get_product(get_the_ID());
                
                if ($product && $product->is_visible()) {
                    $products[] = $product;
                }
            }
        }
        
        wp_reset_postdata();
        
        // If no products found with WP_Query, try wc_get_products as fallback
        if (empty($products)) {
            
            $wc_args = array(
                'status' => 'publish',
                'limit' => -1,
                'type' => array('simple', 'variable', 'grouped', 'external')
            );
            
            $wc_products = wc_get_products($wc_args);
            
            foreach ($wc_products as $product) {
                if ($product && $product->is_visible()) {
                    $products[] = $product;
                }
            }
        }
        
        return (array) $products;
    }

    /**
     * Get product brand
     *
     * @param WC_Product $product
     * @return string|null
     */
    private function getProductBrand($product)
    {
        try {
            // Try to get brand from product attributes
            $attributes = $product->get_attributes();
            
            if (isset($attributes['brand']) && is_object($attributes['brand'])) {
                $options = $attributes['brand']->get_options();
                if (!empty($options) && is_array($options)) {
                    return is_string($options[0]) ? $options[0] : null;
                }
            }
            
            // Try to get brand from custom field
            $brand = get_post_meta($product->get_id(), '_brand', true);
            if ($brand) {
                return is_string($brand) ? $brand : null;
            }
            
            // Try to get brand from taxonomy
            $brand_terms = get_the_terms($product->get_id(), 'product_brand');
            if ($brand_terms && !is_wp_error($brand_terms) && is_array($brand_terms)) {
                return is_string($brand_terms[0]->name) ? $brand_terms[0]->name : null;
            }
        } catch (Exception $e) {
            $this->logger->error('Iyzico Google XML: Error getting brand for product ' . $product->get_id() . ': ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Get product GTIN
     *
     * @param WC_Product $product
     * @return string|null
     */
    private function getProductGtin($product)
    {
        try {
            // Try to get GTIN from SKU if it's a valid GTIN
            $sku = $product->get_sku();
            if ($sku && $this->isValidGtin($sku)) {
                return is_string($sku) ? $sku : null;
            }
            
            // Try to get GTIN from custom field
            $gtin = get_post_meta($product->get_id(), '_gtin', true);
            if ($gtin) {
                return is_string($gtin) ? $gtin : null;
            }
        } catch (Exception $e) {
            $this->logger->error('Iyzico Google XML: Error getting GTIN for product ' . $product->get_id() . ': ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Get product MPN
     *
     * @param WC_Product $product
     * @return string|null
     */
    private function getProductMpn($product)
    {
        try {
            // Try to get MPN from custom field
            $mpn = get_post_meta($product->get_id(), '_mpn', true);
            if ($mpn) {
                return is_string($mpn) ? $mpn : null;
            }
            
            // Use SKU as MPN if no dedicated MPN field
            $sku = $product->get_sku();
            if ($sku) {
                return is_string($sku) ? $sku : null;
            }
        } catch (Exception $e) {
            $this->logger->error('Iyzico Google XML: Error getting MPN for product ' . $product->get_id() . ': ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Get Google Product Category
     *
     * @param WC_Product $product
     * @return string|null
     */
    private function getProductCategory($product)
    {
        try {
            // Try to get Google category from custom field
            $google_category = get_post_meta($product->get_id(), '_google_product_category', true);
            if ($google_category) {
                return is_string($google_category) ? $google_category : null;
            }
            
            // Map WooCommerce categories to Google categories (basic mapping)
            $categories = get_the_terms($product->get_id(), 'product_cat');
            if ($categories && !is_wp_error($categories) && is_array($categories)) {
                // Basic category mapping - you can expand this
                $category_map = array(
                    'electronics' => 'Electronics',
                    'clothing' => 'Apparel & Accessories',
                    'books' => 'Media > Books',
                    'toys' => 'Toys & Games',
                    'home' => 'Home & Garden',
                    'sports' => 'Sporting Goods'
                );
                
                foreach ($categories as $category) {
                    $slug = $category->slug;
                    if (isset($category_map[$slug])) {
                        return is_string($category_map[$slug]) ? $category_map[$slug] : null;
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->error('Iyzico Google XML: Error getting Google category for product ' . $product->get_id() . ': ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Clean content for XML compatibility
     *
     * @param string $content
     * @return string
     */
    private function cleanXmlContent($content)
    {
        if (empty($content)) {
            return '';
        }
        
        // HTML entities'leri decode et
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // HTML taglarını temizle
        $content = wp_strip_all_tags($content);
        
        // XML'de sorun çıkarabilecek karakterleri escape et
        $content = str_replace(
            ['&', '<', '>', '"', "'"],
            ['&amp;', '&lt;', '&gt;', '&quot;', '&apos;'],
            $content
        );
        
        // Non-breaking space ve diğer özel karakterleri temizle
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);
        $content = str_replace(['&nbsp;', '&NBSP;'], ' ', $content);
        
        // Çoklu boşlukları tek boşluğa çevir
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Başındaki ve sonundaki boşlukları temizle
        $content = trim($content);
        
        return $content;
    }

    /**
     * Check if a string is a valid GTIN
     *
     * @param string $gtin
     * @return bool
     */
    private function isValidGtin($gtin)
    {
        // Basic GTIN validation (8, 12, 13, 14 digits)
        return preg_match('/^\d{8}$|^\d{12}$|^\d{13}$|^\d{14}$/', $gtin);
    }

    /**
     * Save XML content to file
     */
    private function saveXmlFile()
    {
        $upload_dir = wp_upload_dir();
        $xml_dir = $upload_dir['basedir'] . '/iyzico-google-products/';
        
        // Create directory if it doesn't exist
        if (!file_exists($xml_dir)) {
            wp_mkdir_p($xml_dir);
        }
        
        $xml_file = $xml_dir . 'google-products.xml';
        $result = file_put_contents($xml_file, $this->xmlContent);
        if ($result === false) {
            $this->logger->error('Iyzico Google XML: Failed to write XML file to ' . $xml_file);
        }
        
        // Update option with file URL
        $xml_url = $upload_dir['baseurl'] . '/iyzico-google-products/google-products.xml';
        update_option('iyzico_google_products_xml_url', $xml_url);
    }

    /**
     * Send XML URL to remote server
     */
    private function sendToRemoteServer()
    {
        $this->logger->info('Iyzico Google XML: Starting remote server send process');
        
        if (empty($this->remotePostUrl)) {
            $this->logger->error('Iyzico Google XML: Remote post URL is empty, cannot send');
            return;
        }
        
        $xml_url = get_option('iyzico_google_products_xml_url', '');
        if (empty($xml_url)) {
            $this->logger->error('Iyzico Google XML: XML URL is empty, cannot send');
            return;
        }
        
        $this->logger->info('Iyzico Google XML: Remote URL: ' . $this->remotePostUrl);
        $this->logger->info('Iyzico Google XML: XML URL: ' . $xml_url);

        // --- YENİ: Retry ve zaman kontrolü ---
        $retry_data = get_option('iyzico_google_products_retry_data', array());
        $retry_count = isset($retry_data['count']) ? (int)$retry_data['count'] : 0;
        $last_try_time = isset($retry_data['last_try']) ? (int)$retry_data['last_try'] : 0;
        $max_retries = 3;
        $retry_interval = 15 * 60; // 15 dakika

        // İlk kurulum kontrolü - eğer hiç gönderim yapılmamışsa retry kontrollerini atla
        $last_sent = get_option('iyzico_google_products_last_sent', null);
        $is_first_setup = empty($last_sent);
        
        $this->logger->info('Iyzico Google XML: Retry count: ' . $retry_count . ', Max retries: ' . $max_retries);
        $this->logger->info('Iyzico Google XML: Is first setup: ' . ($is_first_setup ? 'Yes' : 'No'));

        // Eğer 3 deneme yapıldıysa ve hala başarılı değilse, tekrar deneme yapma (ilk kurulum değilse)
        if (!$is_first_setup && $retry_count >= $max_retries && (time() - $last_try_time) < $retry_interval) {
            $this->logger->error('Iyzico Google XML: Max retry reached, will not try again until next XML generation.');
            return;
        }
        // Eğer son denemeden sonra 15 dakika geçmediyse tekrar deneme (ilk kurulum değilse)
        if (!$is_first_setup && $retry_count > 0 && (time() - $last_try_time) < $retry_interval) {
            $this->logger->info('Iyzico Google XML: Waiting for retry interval. Next try at ' . date('d.m.Y - H:i:s', $last_try_time + $retry_interval));
            return;
        }

        $previous_update_timestamp = get_option('iyzico_google_products_last_sent', null);
        $previous_update = null;
        if (!empty($previous_update_timestamp) && is_numeric($previous_update_timestamp)) {
            $previous_update = (int)$previous_update_timestamp;
        }

        $last_update = time();
        $xml_last_update_timestamp = get_option('iyzico_google_products_xml_last_update', null);
        $xml_last_update = null;
        if (!empty($xml_last_update_timestamp) && is_numeric($xml_last_update_timestamp)) {
            $xml_last_update = (int)$xml_last_update_timestamp;
        }

        $data = array(
            'site_url' => !empty($this->siteUrl) ? $this->siteUrl : '',
            'xml_url' => $xml_url,
            'last_update' => $last_update,
            'previous_update' => $previous_update,
            'xml_last_update' => $xml_last_update
        );
        $this->logger->info('Iyzico Google XML: Sending data to remote server: ' . json_encode($data));
        
        $args = array(
            'body' => json_encode($data),
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json'
            )
        );
        
        $this->logger->info('Iyzico Google XML: Making HTTP request to: ' . $this->remotePostUrl);
        $response = wp_remote_post($this->remotePostUrl, $args);
        
        if (!is_wp_error($response)) {
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $this->logger->info('Iyzico Google XML: HTTP response code: ' . $code);
            $this->logger->info('Iyzico Google XML: HTTP response body: ' . $body);
            
            if ($code !== 200) {
                $this->logger->error('Iyzico Google XML: Remote server returned HTTP ' . $code . ' - Body: ' . $body);
                // Başarısız ise retry sayaçlarını güncelle
                $retry_count++;
                update_option('iyzico_google_products_retry_data', array(
                    'count' => $retry_count,
                    'last_try' => time()
                ));
                $this->logger->info('Iyzico Google XML: Updated retry count to: ' . $retry_count);
            } else {
                $this->logger->info('Iyzico Google XML: Remote server request successful');
                update_option('iyzico_google_products_last_sent', current_time('timestamp'));
                // Başarılı ise retry sayaçlarını sıfırla
                delete_option('iyzico_google_products_retry_data');
                // --- YENİ: Sonraki gönderim için 7-12 gün arası random bekleme ayarla ---
                $days = 7 + rand(0, 5); // 7-12 gün
                $next_send_time = time() + ($days * 24 * 60 * 60);
                update_option('iyzico_google_products_next_send_time', $next_send_time);
                $this->logger->info('Iyzico Google XML: Next send scheduled for ' . $days . ' days from now (' . date('d.m.Y - H:i:s', $next_send_time) . ')');
            }
        } else {
            $this->logger->error('Iyzico Google XML: wp_remote_post error: ' . $response->get_error_message());
            // Hata ise retry sayaçlarını güncelle
            $retry_count++;
            update_option('iyzico_google_products_retry_data', array(
                'count' => $retry_count,
                'last_try' => time()
            ));
            $this->logger->info('Iyzico Google XML: Updated retry count to: ' . $retry_count);
        }
    }

    /**
     * Get XML file URL
     *
     * @return string
     */
    public function getXmlUrl()
    {
        return get_option('iyzico_google_products_xml_url', '');
    }

    /**
     * Schedule XML generation
     */
    public function scheduleXmlGeneration()
    {
        if (!wp_next_scheduled('iyzico_generate_google_products_xml')) {
            wp_schedule_event(time(), 'daily', 'iyzico_generate_google_products_xml');
        }
    }

    /**
     * Unschedule XML generation
     */
    public function unscheduleXmlGeneration()
    {
        wp_clear_scheduled_hook('iyzico_generate_google_products_xml');
    }
} 