<?php

namespace App\Services\Vendor;

use DateTime;
use Zxing\QrReader;
use Andegna\DateTimeFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class VendorHelper
{
  public static function licenseExpired($renewalDate)
  {
    [$day, $month, $year] = explode('/', $renewalDate);
    $ethiopianDate = DateTimeFactory::of((int) $year, (int) $month, (int) $day);
    $renewalDateGregorian = $ethiopianDate->toGregorian();
    $currentDate = new DateTime();

    return $renewalDateGregorian > $currentDate;
  }

  public static function scanQrCode($imagePath)
  {
    $qrcode = new QrReader($imagePath);
    return $qrcode->text();
  }

  public static function extractQrCode($imagePath)
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

  public static function validateWithAPI($url, $tin)
  {
    try {
      $response = Http::withHeaders([
        'Referer' => 'https://etrade.gov.et/business-license-checker?tin=' . $tin,
      ])->get('https://etrade.gov.et/api/Registration/GetRegistrationInfoByTin/' . $tin . '/am');

      // Check if the response is successful
      if (!$response->successful()) {
        Log::warning('API response was not successful for TIN: ' . $tin);
        return false;
      }

      $vendorDetails = $response->json();

      // Check if 'Businesses' key exists and is an array
      if (!isset($vendorDetails['Businesses']) || !is_array($vendorDetails['Businesses'])) {
        Log::warning('Expected data structure is not present for TIN: $tin');
        return false;
      }

      $hasValidLicense = false;

      foreach ($vendorDetails['Businesses'] as $business) {
        // Check if the license has not expired
        if (!self::licenseExpired($business['RenewedTo'])) {
          $hasValidLicense = true;
          break;
        }
      }

      return $hasValidLicense; // Return true if at least one valid license is found

    } catch (\Exception $e) {
      Log::error('API request failed for TIN: ' . $tin . ' - Error: ' . $e->getMessage());
      return false; // Return false if an exception occurred
    }
  }
}