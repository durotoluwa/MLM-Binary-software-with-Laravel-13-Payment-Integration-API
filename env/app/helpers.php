<?php
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use App\Models\Setting;
use App\Models\User;


if (!function_exists('uploadImageToDirectory')) {
    /**
     * Uploads an image to the specified public directory.
     *
     * @param UploadedFile $image
     * @param string $directory  Directory inside the public folder
     * @return string  Relative path to the uploaded image
     *
     * @throws \Exception
     */
    function uploadImageToDirectory(UploadedFile $image, string $directory): string
    {
        try {
            if (!$image->isValid()) {
                throw new \Exception('Invalid image file.');
            }

            $filename = Str::uuid()->toString() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path($directory);

            if (!file_exists($destinationPath)) {
                if (!mkdir($destinationPath, 0755, true) && !is_dir($destinationPath)) {
                    throw new \Exception("Failed to create upload directory: $destinationPath");
                }
            }

            $image->move($destinationPath, $filename);

            return $directory . '/' . $filename;

        } catch (\Exception $e) {
            throw new \Exception('Image upload failed: ' . $e->getMessage());
        }
    }
}

if (!function_exists('uploadImage')) {
    function uploadImage(UploadedFile $image): string
    {
        return uploadImageToDirectory($image, 'package');
    }
}

if (!function_exists('uploadProduct')) {
    function uploadProduct(UploadedFile $image): string
    {
        return uploadImageToDirectory($image, 'product');
    }
}


if (!function_exists('uploadProfileImage')) {
    function uploadProfileImage(UploadedFile $image): string
    {
        return uploadImageToDirectory($image, 'user');
    }
}

 


if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        return Setting::getValue($key, $default);
    }
}

 


function getMatchingSettings($packageName)
{
    // Normalize to lowercase for matching
    $packageName = strtolower(trim($packageName));

    $settings = [
        'standard'  => ['limit' => 20,  'percentage' => 7],
        'basic'     => ['limit' => 30, 'percentage' => 9],
        'classic'   => ['limit' => 40, 'percentage' => 12],
        'premium'   => ['limit' => 60, 'percentage' => 13],
        'executive' => ['limit' => 75, 'percentage' => 14],
        'vip'       => ['limit' => 100, 'percentage' => 15],
    ];

    return $settings[$packageName] ?? ['limit' => 0, 'percentage' => 0];
}



if (!function_exists('addCtpToUplines')) {
    function addCtpToUplines($userId, $ctpToAdd)
    {
        $currentUser = User::find($userId);
        if (!$currentUser || $ctpToAdd <= 0) {
            return;
        }

        $level = 1;
        while ($currentUser->parent_id) {
            $upline = User::find($currentUser->parent_id);

            if (!$upline) break;

            // Add the CTP to upline's total_ctp
            $upline->total_ctp += $ctpToAdd;
            $upline->save();

            \Log::info("Added $ctpToAdd CTP to upline ID {$upline->id} (level $level) from downline ID {$currentUser->id}");

            $currentUser = $upline;
            $level++;
        }
    }

}



 






