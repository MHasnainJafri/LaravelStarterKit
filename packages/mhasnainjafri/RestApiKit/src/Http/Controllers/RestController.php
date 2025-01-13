<?php
namespace Mhasnainjafri\RestApiKit\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Controller;
use Mhasnainjafri\RestApiKit\Helpers\FileUploader;
use Mhasnainjafri\RestApiKit\Http\Responses\ResponseBuilder;

class RestController extends Controller
{
    protected function response($data = null, int $status = 200): ResponseBuilder
    {
        return new ResponseBuilder($data, $status);
    }

    protected function errors(array $errors, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'errors' => $errors,
        ], $status);
    }


     /**
     * Upload a file using FileUploader.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string|null $path
     * @param string|null $disk
     * @return string The file path
     * @throws \Exception
     */
    public function upload(UploadedFile $file, ?string $path = null, ?string $disk = null): string
    {
        return FileUploader::upload($file, $path, $disk);
    }

    /**
     * Delete a file.
     *
     * @param string $filePath
     * @param string|null $disk
     * @return bool
     */
    public function deleteFile(string $filePath, ?string $disk = null): bool
    {
        return FileUploader::delete($filePath, $disk);
    }

    /**
     * Get the URL for a stored file.
     *
     * @param string $filePath
     * @param string|null $disk
     * @return string
     */
    public function fileUrl(string $filePath, ?string $disk = null): string
    {
        return FileUploader::url($filePath, $disk);
    }
}
