<?php

namespace App\Services\Vendor;

use Andegna\DateTimeFactory;
use DateTime;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Zxing\QrReader;

class VendorHelper
{
    public static function licenseExpired(string $renewalDate): bool
    {
        [$day, $month, $year] = explode('/', $renewalDate);
        $ethiopianDate = DateTimeFactory::of((int) $year, (int) $month, (int) $day);
        $renewalDateGregorian = $ethiopianDate->toGregorian();
        $currentDate = new DateTime;

        return $renewalDateGregorian > $currentDate;
    }

    public static function scanQrCode(UploadedFile $imagePath): mixed
    {
        $qrcode = new QrReader($imagePath);

        return $qrcode->text();
    }

    public static function extractQrCode(UploadedFile $imagePath): array
    {
        $qrCodeData = self::scanQrCode($imagePath);
        $tin = null;

        if ($qrCodeData) {
            $urlParts = parse_url($qrCodeData);
            parse_str($urlParts['query'], $queryParams);
            $tin = $queryParams['tin'] ?? null;
        }

        return [$tin, $qrCodeData];
    }

    public static function validateWithAPI($url, $tin): bool
    {
        try {
            $response = Http::withHeaders([
                'Referer' => 'https://etrade.gov.et/business-license-checker?tin='.$tin,
            ])->get('https://etrade.gov.et/api/Registration/GetRegistrationInfoByTin/'.$tin.'/am');

            // Check if the response is successful
            if (! $response->successful()) {
                Log::warning('API response was not successful for TIN: '.$tin);

                return false;
            }

            $vendorDetails = $response->json();

            // Check if 'Businesses' key exists and is an array
            if (! isset($vendorDetails['Businesses']) || ! is_array($vendorDetails['Businesses'])) {
                Log::warning('Expected data structure is not present for TIN: $tin');

                return false;
            }

            $hasValidLicense = false;

            foreach ($vendorDetails['Businesses'] as $business) {
                // Check if the license has not expired
                if (! self::licenseExpired($business['RenewedTo'])) {
                    $hasValidLicense = true;
                    break;
                }
            }

            return $hasValidLicense; // Return true if at least one valid license is found

        } catch (\Exception $e) {
            Log::error('API request failed for TIN: '.$tin.' - Error: '.$e->getMessage());

            return false; // Return false if an exception occurred
        }
    }

    public static function populateWithAPI(string $licenseNumber): mixed
    {
        try {
            $response = Http::withHeaders([
                'Referer' => 'https://etrade.gov.et/business-license-checker',
            ])->get('https://etrade.gov.et/api/BusinessMain/GetBusinessByLicenseNo?LicenseNo='.$licenseNumber.'&Tin=null&Lang=en');

            if (! $response->successful()) {
                Log::warning('API response was not successful for Licesnse number: '.$licenseNumber);

                return false;
            }

            $vendorDetails = $response->json();

            $returned = self::mapResponse($vendorDetails);

            if ($returned['store_name']) {
                return $returned;
            }

            $response = Http::withHeaders([
                'Referer' => 'https://etrade.gov.et/business-license-checker',
            ])->get('https://etrade.gov.et/api/Registration/GetRegistrationInfoByTin/'.$returned['tin_number'].'/en');

            // Check if the response is successful
            if (! $response->successful()) {
                Log::warning('API response was not successful for TIN: '.$returned['tin_number']);

                return false;
            }

            $returned['store_name'] = $response->json()['BusinessName'];

            return $returned;

        } catch (\Exception $e) {
            Log::warning('API response failed for Licesnse number: '.$licenseNumber);

            return false; // Return false if an exception occurred
        }
    }

    private static function mapResponse(array $response): array
    {
        return [
            'phone_number' => $response['AddressInfo']['RegularPhone'] ?? null,
            'zone' => $response['AddressInfo']['Zone'] ?? null,
            'region' => $response['AddressInfo']['Region'] ?? null,
            'tin_number' => $response['OwnerTIN'],
            'store_name' => $response['TradeName'],
        ];
    }
}
