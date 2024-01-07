<?php
if (!defined('ABSPATH'))
    exit;


class pennylaneServices
{

    //PENNYLANE_API_TOKEN
    //PENNYLANE_COMPANY_ID

    private $ensuredConnection = false;

    public static $baseApiUrl = "https://app.pennylane.com/api/external/v1/";

    public function __construct()
    {
        $this->ensuredConnection = $this->ensureConnection();
    }

    private function ensureConnection()
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => pennylaneServices::$baseApiUrl . 'me',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'accept: application/json',
                    'authorization: Bearer ' . PENNYLANE_API_TOKEN
                ),
            )
        );
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return $httpCode === 200;
    }

    public function canProceed()
    {
        return $this->ensuredConnection;
    }

    private function sendInvoiceMail($pennylane_invoice_id)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => pennylaneServices::$baseApiUrl . 'customer_invoices/' . $pennylane_invoice_id . '/send_by_email',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'accept: application/json',
                'authorization: Bearer ' . PENNYLANE_API_TOKEN,
            ),
        )
        );

        curl_exec($curl);
    }

    public function clientExist($clientId)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => pennylaneServices::$baseApiUrl . 'customers/'.$clientId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'accept: application/json',
                'authorization: Bearer ' . PENNYLANE_API_TOKEN
            ),
        )
        );

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $httpCode == 200;
    }
    public function createNewBill($params, $sendMail = false)
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => pennylaneServices::$baseApiUrl . 'customer_invoices',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($params),
                CURLOPT_HTTPHEADER => array(
                    'accept: application/json',
                    'authorization: Bearer ' . PENNYLANE_API_TOKEN,
                    'content-type: application/json',
                ),
            )
        );

        $response = curl_exec($curl);
        $responseData = json_decode($response, true);

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($httpCode != 201) {
            throw new Exception($responseData['error']);
        }

        $invoiceId = $responseData['invoice']['id'];

        if ($sendMail) {
            $this->sendInvoiceMail($invoiceId);
        }

        return $responseData;
    }

}