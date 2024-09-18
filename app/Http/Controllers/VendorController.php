<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Services\Vendor\VendorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'store_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'email' => 'required|email|unique:vendors,email',
            'logo' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'zone' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'google_map_location' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'telegram' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'tin_number' => 'required|string|unique:vendors,tin_number|max:20',
            'license' => 'required|file|mimes:jpg,png,pdf|max:2048',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $fields = $validator->validated();

        // Validate the license
        $licenseValidation = $this->validateLicense($request->file('license'), $fields['tin_number']);

        if (! $licenseValidation['isValid']) {
            return response()->json([
                'message' => 'Invalid License',
                'error' => $licenseValidation['error'],
            ], 422);
        }

        // Store files in Cloudinary
        [$fields['license'], $fields['license_public_id']] = $this->uploadToCloudinary($request->file('license'), 'vendor_licenses');
        if ($request->hasFile('logo')) {
            [$fields['logo'], $fields['logo_public_id']] = $this->uploadToCloudinary($request->file('logo'), 'vendor_logos');
        }

        $fields['user_id'] = auth()->user()->id;

        // Save vendor data
        Vendor::create($fields);

        return response()->json([
            'message' => 'Vendor registered successfully',
            'user' => auth()->user(),
        ], 201);
    }

    private function validateLicense($license, $tinInput)
    {
        [$tinScanned, $qrUrl] = VendorHelper::extractQrCode($license);

        if (strlen($tinScanned) < 10) {
            return [
                'isValid' => false,
                'error' => 'The TIN number extracted from the license is invalid (less than 10 digits).',
            ];
        }

        if ($tinScanned != $tinInput) {
            return [
                'isValid' => false,
                'error' => 'The TIN in the uploaded license does not match the TIN number provided.',
            ];
        }

        if (! VendorHelper::validateWithAPI($qrUrl, $tinScanned)) {
            return [
                'isValid' => false,
                'error' => 'No valid business license was found in the attached document; please check the expiration date.',
            ];
        }

        return ['isValid' => true, 'error' => null];
    }

    private function uploadToCloudinary($file, $folder)
    {
        $upload = $file->storeOnCloudinary($folder);

        return [$upload->getSecurePath(), $upload->getPublicId()];
    }
}
