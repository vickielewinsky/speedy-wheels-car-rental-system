<?php
class MpesaConfig {
    // YOUR ACTUAL CREDENTIALS FROM THE PORTAL
    const CONSUMER_KEY = 'YUC3ELHtuKHH64mhftrNZj7WOGyCfWuADk3VwJmaA5lPtxZY'; // Use your FULL Consumer Key
    const CONSUMER_SECRET = 'IG7AYLSmRlNAFyridhG1GZhfdDWKIVDwmsQbJKmADuEN52aJMyJqMX31AqABom4C'; // Use your FULL Consumer Secret
    const BUSINESS_SHORTCODE = '174379'; // Sandbox shortcode
    const PASSKEY = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
    const TRANSACTION_TYPE = 'CustomerPayBillOnline';
    const CALLBACK_URL = 'http://localhost/speedy-wheels-car-rental-system/mpesa_callback.php';
    
    // MPESA API endpoints
    const AUTH_URL = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    const STKPUSH_URL = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    
    public static function getAccessToken() {
        $credentials = base64_encode(self::CONSUMER_KEY . ':' . self::CONSUMER_SECRET);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::AUTH_URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response);
        return $result->access_token ?? null;
    }
}
?>