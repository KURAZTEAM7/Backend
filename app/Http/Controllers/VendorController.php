<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Services\Vendor\VendorHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Vendor::all(), 200);
    }

    /**
     * @authenticated
     **/
    public function store(Request $request): JsonResponse
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'store_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'email' => 'email|unique:vendors,email',
            'logo' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'zone' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'google_map_location' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'telegram' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'tin_number' => 'required|string|unique:vendors,tin_number|size:10',
            'license' => 'required|file|mimes:jpg,png|max:2048',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $fields = $validator->validated();

        $user_id = auth()->user()->id;
        $checkIfExists = Vendor::where('user_id', $user_id)->first();

        if ($checkIfExists) {
            return response()->json([
                'message' => 'User already manages a vendor',
            ], 406);
        }

        $fields['user_id'] = $user_id;

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

        // Save vendor data
        $vendor = Vendor::create($fields);

        return response()->json([
            'message' => 'Vendor registered successfully',
            'vendor' => $vendor,
        ], 201);
    }

    /**
     * @authenticated
     **/
    public function storePopulated(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'license_number' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'license' => 'required|file|mimes:jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $fields = $validator->validated();

        $user_id = auth()->user()->id;
        $checkIfExists = Vendor::where('user_id', $user_id)->first();

        if ($checkIfExists) {
            return response()->json([
                'message' => 'User already manages a vendor',
            ], 406);
        }

        $result = VendorHelper::populateWithAPI($fields['license_number']);

        if ($result == false) {
            return response()->json([
                'message' => 'License number does not seem valid',
            ], 422);
        }

        $validator = Validator::make($result, [
            'tin_number' => 'required|string|unique:vendors,tin_number|size:10',
            'store_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'zone' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'tin_number' => 'required|string|unique:vendors,tin_number|size:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $response = $validator->validated();

        $licenseValidation = $this->validateLicense($request->file('license'), $response['tin_number']);

        if (! $licenseValidation['isValid']) {
            return response()->json([
                'message' => 'Invalid License',
                'error' => $licenseValidation['error'],
            ], 422);
        }

        [$response['license'], $response['license_public_id']] = $this->uploadToCloudinary($request->file('license'), 'vendor_licenses');
        if ($request->hasFile('logo')) {
            [$response['logo'], $response['logo_public_id']] = $this->uploadToCloudinary($request->file('logo'), 'vendor_logos');
        }

        $response['user_id'] = $user_id;
        $vendor = Vendor::create($response);

        return response()->json([
            'message' => 'Vendor registered successfully',
            'vendor' => $vendor,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $vendor = Vendor::find($id);

        if (! $vendor) {
            return response()->json([
                'message' => 'Vendor cannot be found',
            ], 422);
        }

        return response()->json($vendor, 200);
    }

    public function search(string $store_name): JsonResponse
    {
        $vendors = Vendor::where('store_name', 'like', '%'.$store_name.'%')->get();

        if (count($vendors) === 0) {
            return response()->json([
                'message' => 'No match found',
            ], 422);
        }

        return response()->json($vendors, 200);
    }

    /**
     * @authenticated
     **/
    public function products(): JsonResponse
    {
        $vendor = auth()->user()->vendor;

        if (! $vendor) {
            return response()->json([
                'message' => 'Vendor cannot be found',
            ], 422);
        }

        return response()->json($vendor->products()->get(), 200);
    }

    /**
     * @authenticated
     **/
    public function orders(): JsonResponse
    {
        $vendor = auth()->user()->vendor;

        if (! $vendor) {
            return response()->json([
                'message' => 'Vendor cannot be found',
            ], 422);
        }

        return response()->json($vendor->orders()->get(), 200);
    }

    /**
     * @authenticated
     **/
    public function destroy(): JsonResponse
    {
        $vendor = auth()->user()->vendor;
        if ($vendor) {
            if ($vendor->delete() == 1) {
                return response()->json([
                    'message' => 'Vendor deleted successfully',
                ], 200);
            }
        }

        return response()->json([
            'message' => 'Nothing to delete',
        ], 200);
    }

    private function validateLicense(UploadedFile $license, string $tinInput): array
    {
        [$tinScanned, $qrUrl] = VendorHelper::extractQrCode($license);

        if (strlen($tinScanned) != 10) {
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

    private function uploadToCloudinary(UploadedFile $file, string $folder): array
    {
        $upload = $file->storeOnCloudinary($folder);

        return [$upload->getSecurePath(), $upload->getPublicId()];
    }
}
